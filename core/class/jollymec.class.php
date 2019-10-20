<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';


class jollymec extends eqLogic {
    /*     * *************************Attributs****************************** */
    const BASE_URL   = 'http://jollymec.efesto.web2app.it/';
    const LOGIN_URL  = 'http://jollymec.efesto.web2app.it/fr/login/';
    const ADD_URL    = 'http://jollymec.efesto.web2app.it/fr/heaters/action/add/';
    const MANAGE_URL = 'http://jollymec.efesto.web2app.it/fr/heaters/action/manage/heater/{MAC_ADDRESS}/';
    const REMOVE_URL = 'http://jollymec.efesto.web2app.it/fr/heaters/action/remove/heater/{MAC_ADDRESS}/';
    const AJAX_URL   = 'http://jollymec.efesto.web2app.it/fr/ajax/action/frontend/response/ajax/';
    const STATUS_TRANSLATION = array('OFF', 'Allumage', 'Allumage', 'Allumage', 'Allumage', 'Allumage', 'Allumage', 'ON', 'ON', 'Nettoyage Final', 'Stand-by', 'Stand-by');
    const REAL_POWER_TRANSLATION = array('ECO', 'ECO', 'SIL', 'P1', 'P2', 'P3', 'P4', 'P5');

    /*private $articleCode = '';
    private $serialNumber = '';
    private $macAddress = '';
    private $registrationCode = '';
    private $name = '';
    private $country = '';
    private $county = '';
    private $city = '';*/

    /*     * ***********************Methode static*************************** */

    public static function cron5() {
        foreach (self::byType('jollymec') as $jollymec) {//parcours tous les équipements du plugin vdm
            if ($jollymec->getIsEnable() == 1) {//vérifie que l'équipement est actif
                $cmd = $jollymec->getCmd(null, 'refresh');//retourne la commande "refresh si elle existe
                if (!is_object($cmd)) {//Si la commande n'existe pas
                    continue; //continue la boucle
                }
                $cmd->execCmd(); // la commande existe on la lance
            }
        }
    }

    /*public static function cronDaily() {
    }*/

    public static function efesto_logout() {
        if (!file_exists(jeedom::getTmpFolder('jollymec').'/cookies.txt')) {
            return;
        }
        unlink(jeedom::getTmpFolder('jollymec').'/cookies.txt');
    }

    public static function efesto_curl($url, $post_data = array(), $http_headers = array(), $nobody = false, $header_out = true, $header = true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_COOKIEJAR, jeedom::getTmpFolder('jollymec').'/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, jeedom::getTmpFolder('jollymec').'/cookies.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_NOBODY, $nobody);
        curl_setopt($ch, CURLINFO_HEADER_OUT, $header_out);
        if ($post_data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        if ($http_headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public static function efesto_connect($connect_only = true) {
        self::efesto_logout();
        $data = array();
        log::add('jollymec', 'debug', __('Etape 1 : Connexion à Efesto', __FILE__));
        self::efesto_curl(self::LOGIN_URL, array(), array(), true, true, true);
        log::add('jollymec', 'debug', __('Etape 2 : Connexion à Efesto', __FILE__));
        $postfields = array(
            'login[username]' => config::byKey('email', 'jollymec'),
            'login[password]' => config::byKey('password', 'jollymec')
        );
        $http_headers = array(
            'Upgrade-Insecure-Requests: 1',
            'Referer: '.self::LOGIN_URL,
            'Connection: keep-alive',
        );
        $response = self::efesto_curl(self::LOGIN_URL, $postfields, $http_headers, $connect_only, true, true);
        return $response;
    }

    public static function efesto_get_heaters() {
        $content = self::efesto_connect(false);
        $doc = new DOMDocument();
        @$doc->loadHTML($content);
        $xpath = new DOMXPath($doc);
        $mac_query = '//ul[@class="registered-heaters"]//div[@class="row"]//div[contains(@class,"heater-type")]//text()';
        $name_query = '//ul[@class="registered-heaters"]//div[@class="row"]//div[contains(@class,"heater-name")]//text()';
        $mac_addresses = $xpath->query($mac_query);
        log::add('jollymec', 'debug', trim($mac_addresses));
        $names = $xpath->query($name_query);
        if ($mac_addresses->length > 0 && $names->length > 0) {
            $ret = array();
            foreach ($mac_addresses as $key => $mac_address) {
                log::add('jollymec', 'debug', print_r($mac_address->nodeValue, true));
                $ret[strtolower(trim($mac_address->nodeValue))] = trim($names[$key]->nodeValue);
            }
            return $ret;
        }
        else {
            return false;
        }
    }

    public static function efesto_ajax($method, $params, $mac_address) {
        if (!file_exists(jeedom::getTmpFolder('jollymec').'/cookies.txt')) {
            self::efesto_connect(true);
        }
        log::add('jollymec', 'debug', __("AJAX avec méthode : ".$method." => ".$params, __FILE__));
        $postfields = array(
            'method' => $method,
            'params' => $params,
            'device' => strtoupper($mac_address)
        );
        $http_headers = array(
            'Upgrade-Insecure-Requests: 1',
            'Referer: '.str_replace('{MAC_ADDRESS}', strtoupper($mac_address), self::MANAGE_URL),
            'Connection: keep-alive',
        );
        $response = self::efesto_curl(self::AJAX_URL, $postfields, $http_headers, false, false, false);
        /*if ($method == 'get-state') {
            $response = '{"status":0,"message":{"contactStatus":0,"deviceStatus":0,"airTemperature":26,"smokeTemperature":30,"waterTemperature":255,"lastSetAirTemperature":15,"lastSetPower":1,"lastSetWaterTemperature":255,"chronoEnabled":0,"hasRemoteRoomProbe":255,"realPower":5,"canGetMainVent":0,"canSetMainVent":0,"mainVentValue":0,"mainVentMin":0,"mainVentMax":255,"mainVentAutoValue":255,"mainVentOffValue":255,"canGetSxCanalization":0,"canSetSxCanalization":0,"sxCanalizationValue":0,"sxCanalizationMin":0,"sxCanalizationMax":255,"sxCanalizationAutoValue":255,"sxCanalizationOffValue":255,"canGetDxCanalization":0,"canSetDxCanalization":0,"dxCanalizationValue":40,"dxCanalizationMin":0,"dxCanalizationMax":255,"dxCanalizationAutoValue":255,"dxCanalizationOffValue":255,"canGetWaterTemperature":0,"canSetWaterTemperature":0,"isDeviceInAlarm":0,"pufferWaterTemperature":255,"boilerWaterTemperature":255,"kProbeH":255,"kProbeL":255,"kProbe":65535,"computedAirTemperature":26},"method":"get-state","idle":null}';
        }*/
        $response = json_decode($response);
        if (!is_null($response)) {
            $message = $response->message;
            log::add('jollymec', 'debug', __("Response : ".print_r($message, true), __FILE__));
            return $message;
        }
        else {
            log::add('jollymec', 'debug', __("Incorrect Response", __FILE__));
            return null;
        }
    }

    public static function efesto_get_state($mac_address) {
        return self::efesto_ajax('get-state', 1, $mac_address);
    }

    public static function efesto_heater_on($mac_address) {
        return self::efesto_ajax('heater-on', 1, $mac_address);
    }

    public static function efesto_heater_off($mac_address) {
        return self::efesto_ajax('heater-off', 1, $mac_address);
    }

    public static function efesto_order($value, $mac_address) {
        return self::efesto_ajax('write-parameters-queue', 'set-air-temperature='.$value, $mac_address);
    }

    public static function efesto_power($value, $mac_address) {
        return self::efesto_ajax('write-parameters-queue', 'set-power='.$value, $mac_address);
    }

    public static function createFromDef($_def) {
        event::add('jeedom::alert', array(
            'level' => 'warning',
            'page' => 'jollymec',
            'message' => __('Nouveau poêle Jolly Mec detecté', __FILE__),
        ));
        if (!isset($_def['macaddress'])) {
            log::add('jollymec', 'error', 'Information manquante pour ajouter l\'équipement : ' . print_r($_def, true));
            event::add('jeedom::alert', array(
                'level' => 'danger',
                'page' => 'jollymec',
                'message' => __('Information manquante pour ajouter l\'équipement. Inclusion impossible', __FILE__),
            ));
            return false;
        }
        $eqLogic = jollymec::byLogicalId($_def['macaddress'], 'jollymec');
        if (!is_object($eqLogic)) {
            $eqLogic = new jollymec();
            $eqLogic->setName($_def['friendlyname']);
        }
        $eqLogic->setLogicalId($_def['macaddress']);
        $eqLogic->setEqType_name('jollymec');
        $eqLogic->setIsEnable(1);
        $eqLogic->setIsVisible(1);
        $eqLogic->save();

        event::add('jeedom::alert', array(
                'level' => 'warning',
                'page' => 'jollymec',
                'message' => __('Module inclu avec succès ' .$_def['friendly_name'], __FILE__),
        ));
        return $eqLogic;
    }

    public static function syncHeaters() {
        $added = false;
        $efesto_heaters = self::efesto_get_heaters();
        foreach ($efesto_heaters as $mac_address => $heater_name) {
            $jollymec = jollymec::byLogicalId($mac_address, 'jollymec');
            if (!is_object($jollymec)) {
                event::add('jeedom::alert', array(
                    'level' => 'warning',
                    'page' => 'jollymec',
                    'message' => __('Nouveau poêle Jolly Mec detecté "'.$heater_name.'""', __FILE__),
                ));
                $eqLogic = new jollymec();
                $eqLogic->setName($heater_name);
                $eqLogic->setLogicalId($mac_address);
                $eqLogic->setEqType_name('jollymec');
                $eqLogic->setIsEnable(1);
                $eqLogic->setIsVisible(1);
                $eqLogic->save();
                $added = true;
                log::add('jollymec', 'error', 'Poêle '.$mac_address.' ajouté');
            }
        }
        if (!$added) {
            event::add('jeedom::alert', array(
                'level' => 'warning',
                'page' => 'jollymec',
                'message' => __('Aucun nouveau poêle Jolly Mec detecté', __FILE__),
            ));
        }
    }

    /*public static function postConfig_Machin() {
    }

    public static function preConfig_Machin() {
    }*/

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
    }

    public function postInsert() {
    }

    public function preSave() {
    }

    public function postSave() {
        // Rafraichir
        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new jollymecCmd();
            $refresh->setName(__('Rafraichir', __FILE__));
        }
        $refresh->setEqLogic_id($this->getId());
        $refresh->setLogicalId('refresh');
        $refresh->setType('action');
        $refresh->setSubType('other');
        $refresh->save();

        // Puissance réelle 1:Eco
        $realPower = $this->getCmd(null, 'realPower');
        if (!is_object($realPower)) {
            $realPower = new jollymecCmd();
            $realPower->setName(__('Puissance réelle', __FILE__));
            $realPower->setTemplate('dashboard', 'line');
            $realPower->setTemplate('mobile', 'line');
        }
        $realPower->setEqLogic_id($this->getId());
        $realPower->setLogicalId('realPower');
        $realPower->setType('info');
        $realPower->setSubType('string');
        $realPower->save();

        // Température de la fumée
        $smokeTemp = $this->getCmd(null, 'smokeTemp');
        if (!is_object($smokeTemp)) {
            $smokeTemp = new jollymecCmd();
            $smokeTemp->setName(__('Fumées', __FILE__));
            $smokeTemp->setTemplate('dashboard', 'line');
            $smokeTemp->setTemplate('mobile', 'line');
            $smokeTemp->setUnite('°C');
        }
        $smokeTemp->setEqLogic_id($this->getId());
        $smokeTemp->setLogicalId('smokeTemp');
        $smokeTemp->setType('info');
        $smokeTemp->setSubType('numeric');
        $smokeTemp->save();

        // Température
        $airTemp = $this->getCmd(null, 'airTemp');
        if (!is_object($airTemp)) {
            $airTemp = new jollymecCmd();
            $airTemp->setName(__('Température', __FILE__));
            $airTemp->setGeneric_type('TEMPERATURE');
            $airTemp->setTemplate('dashboard', 'badge');
            $airTemp->setTemplate('mobile', 'badge');
            $airTemp->setUnite('°C');
        }
        $airTemp->setEqLogic_id($this->getId());
        $airTemp->setLogicalId('airTemp');
        $airTemp->setType('info');
        $airTemp->setSubType('numeric');
        $airTemp->save();

        // Allumer
        $on = $this->getCmd(null, 'on');
        if (!is_object($on)) {
            $on = new jollymecCmd();
            $on->setName(__('On', __FILE__));
            $on->setGeneric_type('ENERGY_ON');
        }
        $on->setEqLogic_id($this->getId());
        $on->setLogicalId('on');
        $on->setType('action');
        $on->setSubType('other');
        $on->save();

        // Éteindre
        $off = $this->getCmd(null, 'off');
        if (!is_object($off)) {
            $off = new jollymecCmd();
            $off->setName(__('Off', __FILE__));
            $off->setGeneric_type('ENERGY_OFF');
        }
        $off->setEqLogic_id($this->getId());
        $off->setLogicalId('off');
        $off->setType('action');
        $off->setSubType('other');
        $off->save();

        // Status du Poêle Éteint ou Allumé
        /*
         0 OFF
         1-6 Allumage
         7 ON
         9 Nettoyage Final
         10-11 Stand-by
        */
        $status = $this->getCmd(null, 'status');
        if (!is_object($status)) {
            $status = new jollymecCmd();
            $status->setName(__('Statut', __FILE__));
            $status->setGeneric_type('ENERGY_STATE');
        }
        $status->setLogicalId('status');
        $status->setEqLogic_id($this->getId());
        $status->setType('info');
        $status->setSubType('string');
        $status->save();

        // Consigne
        $order = $this->getCmd(null, 'order');
        if (!is_object($order)) {
            $order = new jollymecCmd();
            $order->setName(__('Consigne', __FILE__));
            $order->setTemplate('dashboard', 'badge');
            $order->setTemplate('mobile', 'badge');
            $order->setGeneric_type('GENERIC_INFO');
            $order->setUnite('°C');
            $order->setIsVisible(0);
        }
        $order->setLogicalId('order');
        $order->setEqLogic_id($this->getId());
        $order->setType('info');
        $order->setSubType('numeric');
        $order->setConfiguration('minValue', '7');
        $order->setConfiguration('maxValue', '40');
        $order->save();

        // Puissance
        $power = $this->getCmd(null, 'power');
        if (!is_object($power)) {
            $power = new jollymecCmd();
            $power->setName(__('Puissance', __FILE__));
            $power->setTemplate('dashboard', 'badge');
            $power->setTemplate('mobile', 'badge');
            $power->setGeneric_type('GENERIC_INFO');
            $power->setIsVisible(0);
        }
        $power->setLogicalId('power');
        $power->setEqLogic_id($this->getId());
        $power->setType('info');
        $power->setSubType('numeric');
        $power->setConfiguration('minValue', '0');
        $power->setConfiguration('maxValue', '5');
        $power->save();

        // Thermostat
        $thermostat = $this->getCmd(null, 'thermostat');
        if (!is_object($thermostat)) {
            $thermostat = new jollymecCmd();
            $thermostat->setName(__('Thermostat', __FILE__));
            $thermostat->setTemplate('dashboard', 'button');
            $thermostat->setTemplate('mobile', 'button');
            $thermostat->setGeneric_type('GENERIC_ACTION');
            $thermostat->setUnite('°C');
        }
        $thermostat->setEqLogic_id($this->getId());
        $thermostat->setLogicalId('thermostat');
        $thermostat->setType('action');
        $thermostat->setSubType('slider');
        $thermostat->setValue($order->getId());
        $thermostat->setConfiguration('minValue', '7');
        $thermostat->setConfiguration('maxValue', '40');
        $thermostat->save();

        // Réglage de puissance
        $setPower = $this->getCmd(null, 'setPower');
        if (!is_object($setPower)) {
            $setPower = new jollymecCmd();
            $setPower->setName(__('Réglage Puissance', __FILE__));
            $setPower->setTemplate('dashboard', 'button');
            $setPower->setTemplate('mobile', 'button');
            $setPower->setGeneric_type('GENERIC_ACTION');
        }
        $setPower->setEqLogic_id($this->getId());
        $setPower->setLogicalId('setPower');
        $setPower->setType('action');
        $setPower->setSubType('slider');
        $setPower->setValue($power->getId());
        $setPower->setConfiguration('minValue', '0');
        $setPower->setConfiguration('maxValue', '5');
        $setPower->save();
    }

    public function preUpdate() {
    }

    public function postUpdate() {
    }

    public function preRemove() {
    }

    public function postRemove() {
    }

    /*public function toHtml($_version = 'dashboard') {
    }*/

    public function updateHeaterData() {

    }

    /*     * **********************Getteur Setteur*************************** */
}

class jollymecCmd extends cmd {
    // commandes info : statut (éteint ou allumé), puissance, température de consigne
    // commandes action : éteindre, allumer, puissanceUp, puissanceDown, temperatureUp, temperatureDown
    public function execute($_options = array()) {
        $eqLogic = $this->getEqLogic();
        switch ($this->getLogicalId()) {
            case 'refresh':
                //$eqLogic->updateHeaterData();
                $message = jollymec::efesto_get_state($eqLogic->getLogicalId());
                if (isset($message->deviceStatus)) {
                    $eqLogic->checkAndUpdateCmd('status', jollymec::STATUS_TRANSLATION[$message->deviceStatus]);
                }
                if (isset($message->lastSetAirTemperature)) {
                    $eqLogic->checkAndUpdateCmd('order', $message->lastSetAirTemperature);
                }
                if (isset($message->lastSetPower)) {
                    $eqLogic->checkAndUpdateCmd('power', $message->lastSetPower);
                }
                if (isset($message->airTemperature)) {
                    $eqLogic->checkAndUpdateCmd('airTemp', $message->airTemperature);
                }
                if (isset($message->smokeTemperature)) {
                    $eqLogic->checkAndUpdateCmd('smokeTemp', $message->smokeTemperature);
                }
                if (isset($message->realPower)) {
                    $eqLogic->checkAndUpdateCmd('realPower', jollymec::REAL_POWER_TRANSLATION[$message->realPower]);
                }
                break;
            case 'status':
                $message = jollymec::efesto_get_state($eqLogic->getLogicalId());
                if (isset($message->deviceStatus)) {
                    $eqLogic->checkAndUpdateCmd('status', jollymec::STATUS_TRANSLATION[$message->deviceStatus]);
                }
                break;
            case 'order':
                $message = jollymec::efesto_get_state($eqLogic->getLogicalId());
                if (isset($message->lastSetAirTemperature)) {
                    $eqLogic->checkAndUpdateCmd('order', $message->lastSetAirTemperature);
                }
                break;
            case 'power':
                $message = jollymec::efesto_get_state($eqLogic->getLogicalId());
                if (isset($message->lastSetPower)) {
                    $eqLogic->checkAndUpdateCmd('power', $message->lastSetPower);
                }
                break;
            case 'airTemp':
                $message = jollymec::efesto_get_state($eqLogic->getLogicalId());
                if (isset($message->airTemperature)) {
                    $eqLogic->checkAndUpdateCmd('airTemp', $message->airTemperature);
                }
                break;
            case 'smokeTemp':
                $message = jollymec::efesto_get_state($eqLogic->getLogicalId());
                if (isset($message->smokeTemperature)) {
                    $eqLogic->checkAndUpdateCmd('smokeTemp', $message->smokeTemperature);
                }
                break;
            case 'realPower':
                $message = jollymec::efesto_get_state($eqLogic->getLogicalId());
                if (isset($message->realPower)) {
                    $eqLogic->checkAndUpdateCmd('realPower', jollymec::REAL_POWER_TRANSLATION[$message->realPower]);
                }
                break;
            case 'on':
                jollymec::efesto_heater_on($eqLogic->getLogicalId());
                $cmd = $eqLogic->getCmd(null, 'refresh');
                if (!is_object($cmd)) {
                    return;
                }
                $cmd->execCmd();
                break;
            case 'off':
                jollymec::efesto_heater_off($eqLogic->getLogicalId());
                $cmd = $eqLogic->getCmd(null, 'refresh');
                if (!is_object($cmd)) {
                    return;
                }
                $cmd->execCmd();
                break;
            case 'thermostat':
                if (!isset($_options['slider']) || $_options['slider'] == '' || !is_numeric(intval($_options['slider']))) {
                    return;
                }
                jollymec::efesto_order(intval($_options['slider']), $eqLogic->getLogicalId());
                $cmd = $eqLogic->getCmd(null, 'refresh');
                if (!is_object($cmd)) {
                    return;
                }
                $cmd->execCmd();
                break;
            case 'setPower':
                if (!isset($_options['slider']) || $_options['slider'] == '' || !is_numeric(intval($_options['slider']))) {
                    return;
                }
                jollymec::efesto_power(intval($_options['slider']), $eqLogic->getLogicalId());
                $cmd = $eqLogic->getCmd(null, 'refresh');
                if (!is_object($cmd)) {
                    return;
                }
                $cmd->execCmd();
                break;
        }
    }
}
