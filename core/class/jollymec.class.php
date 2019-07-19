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
    }

    public static function cronDaily() {
    }

    public static function efesto_logout() {
        if (!file_exists(jeedom::getTmpFolder('jollymec').'/cookies.txt')) {
            return;
        }
        unlink(jeedom::getTmpFolder('jollymec').'/cookies.txt');
    }

    public static function efesto_connect() {
        self::efesto_logout();
        $data = array();
        log::add('jollymec', 'debug', __('Etape 1 : Connexion à Efesto', __FILE__));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::LOGIN_URL);
        curl_setopt($ch, CURLOPT_COOKIEJAR, jeedom::getTmpFolder('jollymec').'/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, jeedom::getTmpFolder('jollymec').'/cookies.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        log::add('jollymec', 'debug', __('Etape 2 : Connexion à Efesto', __FILE__));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::LOGIN_URL);
        curl_setopt($ch, CURLOPT_COOKIEJAR, jeedom::getTmpFolder('jollymec').'/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, jeedom::getTmpFolder('jollymec').'/cookies.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        $postfields = array(
            'login[username]' => config::byKey('email', 'jollymec'),
            'login[password]=' => config::byKey('password', 'jollymec')
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Upgrade-Insecure-Requests: 1',
            'Referer: '.self::LOGIN_URL,
            'Connection: keep-alive',
        ));
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $response;
    }

    public static function efesto_get_heaters() {
        $content = self::efesto_connect();
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
        log::add('jollymec', 'debug', __("AJAX avec méthode : ".$method, __FILE__));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::AJAX_URL);
        curl_setopt($ch, CURLOPT_COOKIEJAR, jeedom::getTmpFolder('jollymec').'/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, jeedom::getTmpFolder('jollymec').'/cookies.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        $postfields = array(
            'method' => $method,
            'params' => $params,
            'device' => strtoupper($mac_address)
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Upgrade-Insecure-Requests: 1',
            'Referer: '.str_replace('{MAC_ADDRESS}', strtoupper($mac_address), self::MANAGE_URL),
            'Connection: keep-alive',
        ));
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ch);
        log::add('jollymec', 'debug', __(print_r($response, true), __FILE__));
        $info = curl_getinfo($ch);
        log::add('jollymec', 'debug', __(print_r($info, true), __FILE__));
        curl_close($ch);
        return $response;
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
        $jollymec = jollymec::byLogicalId($_def['macaddress'], 'jollymec');
        if (!is_object($jollymec)) {
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
        // Éteint ou Allumé
        $status = $this->getCmd(null, 'status');
        if (!is_object($status)) {
            $status = new jollymecCmd();
            $status->setName(__('Etat', __FILE__));
        }
        $status->setLogicalId('status');
        $status->setEqLogic_id($this->getId());
        $status->setType('info');
        $status->setSubType('binary');
        $status->save();

        // Consigne
        $order = $this->getCmd(null, 'order');
        if (!is_object($order)) {
            $order = new jollymecCmd();
            $order->setName(__('Consigne', __FILE__));
            $order->setGeneric_type('THERMOSTAT_SETPOINT');
            $order->setUnite('°C');
        }
        $order->setLogicalId('order');
        $order->setEqLogic_id($this->getId());
        $order->setType('info');
        $order->setSubType('numeric');
        $order->setConfiguration('minValue', 7);
        $order->setConfiguration('maxValue', 40);
        $order->save();

        // Puissance
        $power = $this->getCmd(null, 'power');
        if (!is_object($power)) {
            $power = new jollymecCmd();
            $power->setName(__('Puissance', __FILE__));
        }
        $power->setLogicalId('power');
        $power->setEqLogic_id($this->getId());
        $power->setType('info');
        $power->setSubType('numeric');
        $power->setConfiguration('minValue', 0);
        $power->setConfiguration('maxValue', 5);
        $power->save();

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

        // Allumer
        $on = $this->getCmd(null, 'on');
        if (!is_object($on)) {
            $on = new jollymecCmd();
            $on->setName(__('On', __FILE__));
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
        }
        $off->setEqLogic_id($this->getId());
        $off->setLogicalId('off');
        $off->setType('action');
        $off->setSubType('other');
        $off->save();

        // Thermostat
        $thermostat = $this->getCmd(null, 'thermostat');
        if (!is_object($thermostat)) {
            $thermostat = new jollymecCmd();
            $thermostat->setName(__('Thermostat', __FILE__));
            $thermostat->setTemplate('dashboard', 'button');
            $thermostat->setTemplate('mobile', 'button');
            $thermostat->setGeneric_type('THERMOSTAT_SET_SETPOINT');
            $thermostat->setUnite('°C');
        }
        $thermostat->setEqLogic_id($this->getId());
        $thermostat->setLogicalId('thermostat');
        $thermostat->setType('action');
        $thermostat->setSubType('slider');
        $thermostat->setValue($order->getId());
        $thermostat->setConfiguration('minValue', 7);
        $thermostat->setConfiguration('maxValue', 40);
        $thermostat->save();

        // Réglage de puissance
        $setPower = $this->getCmd(null, 'setPower');
        if (!is_object($setPower)) {
            $setPower = new jollymecCmd();
            $setPower->setName(__('Réglage Puissance', __FILE__));
            $setPower->setTemplate('dashboard', 'button');
            $setPower->setTemplate('mobile', 'button');
        }
        $setPower->setEqLogic_id($this->getId());
        $setPower->setLogicalId('setPower');
        $setPower->setType('action');
        $setPower->setSubType('slider');
        $setPower->setValue($power->getId());
        $setPower->setConfiguration('minValue', 0);
        $setPower->setConfiguration('maxValue', 5);
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
                $eqLogic->updateHeaterData();
            case 'status':
                jollymec::efesto_get_state($eqLogic->getLogicalId());
                break;
            case 'order':
                jollymec::efesto_get_state($eqLogic->getLogicalId());
                break;
            case 'power':
                jollymec::efesto_get_state($eqLogic->getLogicalId());
                break;
            case 'on':
                jollymec::efesto_heater_on($eqLogic->getLogicalId());
                break;
            case 'off':
                jollymec::efesto_heater_off($eqLogic->getLogicalId());
                break;
            case 'setOrder':
                if (!isset($_options['slider']) || $_options['slider'] == '' || !is_numeric(intval($_options['slider']))) {
                        return;
                }
                jollymec::efesto_order(intval($_options['slider']), $eqLogic->getLogicalId());
                break;
            case 'setPower':
                if (!isset($_options['slider']) || $_options['slider'] == '' || !is_numeric(intval($_options['slider']))) {
                        return;
                }
                jollymec::efesto_power(intval($_options['slider']), $eqLogic->getLogicalId());
                break;
        }
    }
}

/*
it.omniaweb.methods.getDataToggleInfo = function( toggle, update )
{
	var
		  parent = toggle.parent()
		, target = parent.find('.current-value')
		, currentValue = parseInt( target.attr('data-current'), 10 )
		, max = parent.attr( 'data-max' )
		, min = parent.attr( 'data-min' )
		, newValue = update ? it.omniaweb.forceBetween( currentValue += toggle.attr( 'data-todo' ) === 'up' ? 1 : -1, min, max ) : currentValue
	;

	return {
		  parent: parent
		, method: parent.attr( 'data-method' )
		, currentValue: currentValue
		, newValue: newValue
		, target: target
		, hidden: jQ( '.store-hidden-value', parent )
	};
};
set-power =>

Actions
envoi vers l'url AJAX :
method (heater-off, heater-on)
params (1)
device (adresse mac)

Actions
envoi vers l'url AJAX :
method (write-parameters-queue)
params (set-power=newValue|set-air-temperature=newValue)
device (adresse mac)

Infos
envoi vers l'url AJAX :
method (get-state)
params (1)
device (adresse mac)
