<?php
if (!isConnect('admin')) {
    throw new Exception('Error 401 Unauthorized');
}
$plugin = plugin::byId('jollymec');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>
<div class="row row-overflow">
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
        <div class="eqLogicThumbnailContainer">
            <div class="cursor jollymecSync logoPrimary" data-action="sync">
                <i class="fas fa-sign-in-alt fa-rotate-90"></i>
                <br/>
                <span>{{Synchroniser}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                <i class="fas fa-wrench"></i>
                <br/>
                <span>{{Configuration}}</span>
            </div>
        </div>
        <legend><i class="fas fa-table"></i>  {{Mes poêles Jolly Mec}}</legend>
        <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
        <div class="eqLogicThumbnailContainer">
            <?php
            foreach ($eqLogics as $eqLogic) {
                $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
                echo '<img src="plugins/jollymec/desktop/images/jollymec_icon.svg"/>';
                echo '<br>';
                echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <div class="col-xs-12 eqLogic" style="display: none;">
        <div class="input-group pull-right" style="display:inline-flex">
            <span class="input-group-btn">
                <a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
                <a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a>
                <a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
                <a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
            </span>
        </div>
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a class="eqLogicAction cursor" aria-controls="home" role="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Poêle}}</a></li>
            <?php
            if (config::byKey('handle_chronothermostat', 'jollymec')) {
            ?>
            <li role="presentation"><a href="#chronotab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-clock"></i> {{ChronoThermostat}}</a></li>
            <?php
            }
            ?>
            <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
        </ul>
        <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <br />
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Nom du poêle}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du poêle}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                            <div class="col-sm-3">
                                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                    <option value="">{{Aucun}}</option>
                                    <?php
                                    foreach (jeeObject::all() as $object) {
                                        echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"></label>
                            <div class="col-sm-9">
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Catégorie}}</label>
                            <div class="col-sm-9">
                                <?php
                                foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                    echo '<label class="checkbox-inline">';
                                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                                    echo '</label>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Adresse MAC}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="logicalId" placeholder="Logical ID" readonly />
                            </div>
                        </div>
                        <?php

                        ?>
                    </fieldset>
                </form>
            </div>
            <?php
            if (config::byKey('handle_chronothermostat', 'jollymec')) {
            ?>
            <div role="tabpanel" class="tab-pane" id="chronotab">
                <br />
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Activer}}</label>
                            <div class="col-sm-9">
                                <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[enableChrono]" />{{Activer}}</label>
                                <input type="hidden" name="chrono[programs]" value="4" />
                            </div>
                        </div>
                    </fieldset>
                    <div id="chronoprograms" class="panel-group">
                        <div class="mode panel panel-default">
                            <div class="panel-heading"><h3 class="panel-title"><a href="#collapsechonoprogram1" class="accordion-toggle" data-toggle="collapse" data-parent="#chronoprograms" aria-expanded="true">{{Programme 1}}</a></h3></div>
                            <div id="collapsechonoprogram1" class="panel-collapse collapse in" aria-expanded="true" style="">
                                <div class="panel-body">
                                    <div class="well">
                                        <fieldset>
                                            <div class="form-group">
                                                <div class="col-sm-9">
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[1][gg][0]" />{{Lundi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[1][gg][1]" />{{Mardi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[1][gg][2]" />{{Mercredi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[1][gg][3]" />{{Jeudi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[1][gg][4]" />{{Vendredi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[1][gg][5]" />{{Samedi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[1][gg][6]" />{{Dimanche}}</label>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-1 control-label">{{Allumage}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[1][start]">
                                                        <option value="144">{{OFF}}</option>
                                                        <?php
                                                            for ($i = 0; $i < 144; $i++) {
                                                                echo '<option value="' . $i . '">' . date('H:i', mktime('0', '0', $i*10*60, '1', '1', '1970')) . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Arrêt}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[1][stop]">
                                                        <option value="144">{{OFF}}</option>
                                                        <?php
                                                            for ($i = 0; $i < 144; $i++) {
                                                                echo '<option value="' . $i . '">' . date('H:i', mktime('0', '0', $i*10*60, '1', '1', '1970')) . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Puissance}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[1][setPower]">
                                                        <?php
                                                            for ($i = 0; $i < 6; $i++) {
                                                                echo '<option value="' . $i . '">' . $i . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Thermostat}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[1][setTemp]">
                                                        <?php
                                                            for ($i = 7; $i < 41; $i++) {
                                                                echo '<option value="' . $i . '">' . $i . '°</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mode panel panel-default">
                            <div class="panel-heading"><h3 class="panel-title"><a href="#collapsechonoprogram2" class="accordion-toggle" data-toggle="collapse" data-parent="#chronoprograms" aria-expanded="true">{{Programme 2}}</a></h3></div>
                            <div id="collapsechonoprogram2" class="panel-collapse collapse in" aria-expanded="true" style="">
                                <div class="panel-body">
                                    <div class="well">
                                        <fieldset>
                                            <div class="form-group">
                                                <div class="col-sm-9">
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[2][gg][0]" />{{Lundi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[2][gg][1]" />{{Mardi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[2][gg][2]" />{{Mercredi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[2][gg][3]" />{{Jeudi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[2][gg][4]" />{{Vendredi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[2][gg][5]" />{{Samedi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[2][gg][6]" />{{Dimanche}}</label>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-1 control-label">{{Allumage}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[2][start]">
                                                        <option value="144">{{OFF}}</option>
                                                        <?php
                                                            for ($i = 0; $i < 144; $i++) {
                                                                echo '<option value="' . $i . '">' . date('H:i', mktime('0', '0', $i*10*60, '1', '1', '1970')) . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Arrêt}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[2][stop]">
                                                        <option value="144">{{OFF}}</option>
                                                        <?php
                                                            for ($i = 0; $i < 144; $i++) {
                                                                echo '<option value="' . $i . '">' . date('H:i', mktime('0', '0', $i*10*60, '1', '1', '1970')) . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Puissance}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[2][setPower]">
                                                        <?php
                                                            for ($i = 0; $i < 6; $i++) {
                                                                echo '<option value="' . $i . '">' . $i . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Thermostat}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[2][setTemp]">
                                                        <?php
                                                            for ($i = 7; $i < 41; $i++) {
                                                                echo '<option value="' . $i . '">' . $i . '°</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mode panel panel-default">
                            <div class="panel-heading"><h3 class="panel-title"><a href="#collapsechonoprogram3" class="accordion-toggle" data-toggle="collapse" data-parent="#chronoprograms" aria-expanded="true">{{Programme 3}}</a></h3></div>
                            <div id="collapsechonoprogram3" class="panel-collapse collapse in" aria-expanded="true" style="">
                                <div class="panel-body">
                                    <div class="well">
                                        <fieldset>
                                            <div class="form-group">
                                                <div class="col-sm-9">
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[3][gg][0]" />{{Lundi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[3][gg][1]" />{{Mardi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[3][gg][2]" />{{Mercredi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[3][gg][3]" />{{Jeudi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[3][gg][4]" />{{Vendredi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[3][gg][5]" />{{Samedi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[3][gg][6]" />{{Dimanche}}</label>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-1 control-label">{{Allumage}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[3][start]">
                                                        <option value="144">{{OFF}}</option>
                                                        <?php
                                                            for ($i = 0; $i < 144; $i++) {
                                                                echo '<option value="' . $i . '">' . date('H:i', mktime('0', '0', $i*10*60, '1', '1', '1970')) . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Arrêt}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[3][stop]">
                                                        <option value="144">{{OFF}}</option>
                                                        <?php
                                                            for ($i = 0; $i < 144; $i++) {
                                                                echo '<option value="' . $i . '">' . date('H:i', mktime('0', '0', $i*10*60, '1', '1', '1970')) . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Puissance}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[3][setPower]">
                                                        <?php
                                                            for ($i = 0; $i < 6; $i++) {
                                                                echo '<option value="' . $i . '">' . $i . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Thermostat}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[3][setTemp]">
                                                        <?php
                                                            for ($i = 7; $i < 41; $i++) {
                                                                echo '<option value="' . $i . '">' . $i . '°</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mode panel panel-default">
                            <div class="panel-heading"><h3 class="panel-title"><a href="#collapsechonoprogram4" class="accordion-toggle" data-toggle="collapse" data-parent="#chronoprograms" aria-expanded="true">{{Programme 4}}</a></h3></div>
                            <div id="collapsechonoprogram4" class="panel-collapse collapse in" aria-expanded="true" style="">
                                <div class="panel-body">
                                    <div class="well">
                                        <fieldset>
                                            <div class="form-group">
                                                <div class="col-sm-9">
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[4][gg][0]" />{{Lundi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[4][gg][1]" />{{Mardi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[4][gg][2]" />{{Mercredi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[4][gg][3]" />{{Jeudi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[4][gg][4]" />{{Vendredi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[4][gg][5]" />{{Samedi}}</label>
                                                    <label class="checkbox-inline"><input type="checkbox" class="" name="chrono[4][gg][6]" />{{Dimanche}}</label>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-1 control-label">{{Allumage}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[4][start]">
                                                        <option value="144">{{OFF}}</option>
                                                        <?php
                                                            for ($i = 0; $i < 144; $i++) {
                                                                echo '<option value="' . $i . '">' . date('H:i', mktime('0', '0', $i*10*60, '1', '1', '1970')) . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Arrêt}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[4][stop]">
                                                        <option value="144">{{OFF}}</option>
                                                        <?php
                                                            for ($i = 0; $i < 144; $i++) {
                                                                echo '<option value="' . $i . '">' . date('H:i', mktime('0', '0', $i*10*60, '1', '1', '1970')) . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Puissance}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[4][setPower]">
                                                        <?php
                                                            for ($i = 0; $i < 6; $i++) {
                                                                echo '<option value="' . $i . '">' . $i . '</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <label class="col-sm-1 control-label">{{Thermostat}}</label>
                                                <div class="col-sm-1">
                                                    <select class=" form-control input-sm" name="chrono[4][setTemp]">
                                                        <?php
                                                            for ($i = 7; $i < 41; $i++) {
                                                                echo '<option value="' . $i . '">' . $i . '°</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <?php
            }
            ?>
            <div role="tabpanel" class="tab-pane" id="commandtab">
                <table id="table_cmd" class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th>{{Nom}}</th>
                            <th>{{Type}}</th>
                            <th>{{Configuration}}</th>
                            <th>{{Etat}}</th>
                            <th>{{Action}}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_file('desktop', 'jollymec', 'js', 'jollymec');?>
<?php include_file('core', 'plugin.template', 'js');?>