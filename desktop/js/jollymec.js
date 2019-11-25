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

$('.jollymecSync').on('click', function () {
    jollymecSync();
});


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" /> {{Historiser}}</label></span> ';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" /> {{Affichage}}</label></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
/*
function prePrintEqLogic(_eqLogic_id) {
    console.log('prePrintEqLogic');
    console.log(_eqLogic_id);
}*/

function printEqLogic(_eqLogic) {
    _eqLogic.logicalId;
    if ($('#chronotab').length) {
        $('#chronotab form fieldset').prop('disabled', true);
        $.ajax({
            async: false,
            type: "POST",
            url: "plugins/jollymec/core/ajax/jollymec.ajax.php",
            data: {
                action: "get_chrono",
                id: _eqLogic.id,
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
                if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
                if (data.result != false) {
                    console.log(data.result);
                    console.log(data.result['enableChrono']);
                    console.log(data.result['programs']);
                    //console.log(data.result[1]);
                    console.log(data.result[1]['gg']);
                    console.log(data.result[1]['start']);
                    console.log(data.result[1]['stop']);
                    console.log(data.result[1]['setPower']);
                    console.log(data.result[1]['setTemp']);
                    console.log(data.result[2]['gg']);
                    console.log(data.result[2]['start']);
                    console.log(data.result[2]['stop']);
                    console.log(data.result[2]['setPower']);
                    console.log(data.result[2]['setTemp']);
                    //$('#chronotab').append('<div>Test ! ' + JSON.stringify(data.result) + '</div>');
                    $('#chronotab *[name="chrono[enableChrono]"]').prop('checked', parseInt(data.result['enableChrono']));
                    $('#chronotab *[name="chrono[programs]"]')[0].value = data.result['programs'];
                    $('#chronotab *[name="chrono[1][gg][0]"]').prop('checked', parseInt(data.result[1]['gg'][0]));
                    $('#chronotab *[name="chrono[1][gg][1]"]').prop('checked', parseInt(data.result[1]['gg'][1]));
                    $('#chronotab *[name="chrono[1][gg][2]"]').prop('checked', parseInt(data.result[1]['gg'][2]));
                    $('#chronotab *[name="chrono[1][gg][3]"]').prop('checked', parseInt(data.result[1]['gg'][3]));
                    $('#chronotab *[name="chrono[1][gg][4]"]').prop('checked', parseInt(data.result[1]['gg'][4]));
                    $('#chronotab *[name="chrono[1][gg][5]"]').prop('checked', parseInt(data.result[1]['gg'][5]));
                    $('#chronotab *[name="chrono[1][gg][6]"]').prop('checked', parseInt(data.result[1]['gg'][6]));
                    $('#chronotab *[name="chrono[1][start]"]').val(data.result[1]['start']);
                    $('#chronotab *[name="chrono[1][stop]"]').val(data.result[1]['stop']);
                    $('#chronotab *[name="chrono[1][setPower]"]').val(data.result[1]['setPower']);
                    $('#chronotab *[name="chrono[1][setTemp]"]').val(data.result[1]['setTemp']);

                    $('#chronotab *[name="chrono[2][gg][0]"]').prop('checked', parseInt(data.result[2]['gg'][0]));
                    $('#chronotab *[name="chrono[2][gg][1]"]').prop('checked', parseInt(data.result[2]['gg'][1]));
                    $('#chronotab *[name="chrono[2][gg][2]"]').prop('checked', parseInt(data.result[2]['gg'][2]));
                    $('#chronotab *[name="chrono[2][gg][3]"]').prop('checked', parseInt(data.result[2]['gg'][3]));
                    $('#chronotab *[name="chrono[2][gg][4]"]').prop('checked', parseInt(data.result[2]['gg'][4]));
                    $('#chronotab *[name="chrono[2][gg][5]"]').prop('checked', parseInt(data.result[2]['gg'][5]));
                    $('#chronotab *[name="chrono[2][gg][6]"]').prop('checked', parseInt(data.result[2]['gg'][6]));
                    $('#chronotab *[name="chrono[2][start]"]').val(data.result[2]['start']);
                    $('#chronotab *[name="chrono[2][stop]"]').val(data.result[2]['stop']);
                    $('#chronotab *[name="chrono[2][setPower]"]').val(data.result[2]['setPower']);
                    $('#chronotab *[name="chrono[2][setTemp]"]').val(data.result[2]['setTemp']);

                    $('#chronotab *[name="chrono[3][gg][0]"]').prop('checked', parseInt(data.result[3]['gg'][0]));
                    $('#chronotab *[name="chrono[3][gg][1]"]').prop('checked', parseInt(data.result[3]['gg'][1]));
                    $('#chronotab *[name="chrono[3][gg][2]"]').prop('checked', parseInt(data.result[3]['gg'][2]));
                    $('#chronotab *[name="chrono[3][gg][3]"]').prop('checked', parseInt(data.result[3]['gg'][3]));
                    $('#chronotab *[name="chrono[3][gg][4]"]').prop('checked', parseInt(data.result[3]['gg'][4]));
                    $('#chronotab *[name="chrono[3][gg][5]"]').prop('checked', parseInt(data.result[3]['gg'][5]));
                    $('#chronotab *[name="chrono[3][gg][6]"]').prop('checked', parseInt(data.result[3]['gg'][6]));
                    $('#chronotab *[name="chrono[3][start]"]').val(data.result[3]['start']);
                    $('#chronotab *[name="chrono[3][stop]"]').val(data.result[3]['stop']);
                    $('#chronotab *[name="chrono[3][setPower]"]').val(data.result[3]['setPower']);
                    $('#chronotab *[name="chrono[3][setTemp]"]').val(data.result[3]['setTemp']);

                    $('#chronotab *[name="chrono[4][gg][0]"]').prop('checked', parseInt(data.result[4]['gg'][0]));
                    $('#chronotab *[name="chrono[4][gg][1]"]').prop('checked', parseInt(data.result[4]['gg'][1]));
                    $('#chronotab *[name="chrono[4][gg][2]"]').prop('checked', parseInt(data.result[4]['gg'][2]));
                    $('#chronotab *[name="chrono[4][gg][3]"]').prop('checked', parseInt(data.result[4]['gg'][3]));
                    $('#chronotab *[name="chrono[4][gg][4]"]').prop('checked', parseInt(data.result[4]['gg'][4]));
                    $('#chronotab *[name="chrono[4][gg][5]"]').prop('checked', parseInt(data.result[4]['gg'][5]));
                    $('#chronotab *[name="chrono[4][gg][6]"]').prop('checked', parseInt(data.result[4]['gg'][6]));
                    $('#chronotab *[name="chrono[4][start]"]').val(data.result[4]['start']);
                    $('#chronotab *[name="chrono[4][stop]"]').val(data.result[4]['stop']);
                    $('#chronotab *[name="chrono[4][setPower]"]').val(data.result[4]['setPower']);
                    $('#chronotab *[name="chrono[4][setTemp]"]').val(data.result[4]['setTemp']);
                    //$('#chronotab form').serialize()
                    $('#chronotab form fieldset').prop('disabled', false);
                    return;
                }
                else {
                    $('#div_alert').showAlert({message: 'Impossible de récupérer les données du ChronoThermostat', level: 'danger'});
                    return;
                }
            }
        });
    }
}

function saveEqLogic(_eqLogic) {
    if (!$('#chronotab').length) {
        return _eqLogic;
    }
    var chrono_values = $('#chronotab form').serialize();
    $.ajax({
        type: "POST",
        url: "plugins/jollymec/core/ajax/jollymec.ajax.php",
        data: {
            action: "set_chrono",
            id: _eqLogic.id,
            chrono_values: chrono_values,
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { // si l'appel a bien fonctionné
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
        }
    });
    return _eqLogic;
}

function jollymecSync() {
    $.ajax({
        type: "POST",
        url: "plugins/jollymec/core/ajax/jollymec.ajax.php",
        data: {
            action: "sync",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { // si l'appel a bien fonctionné
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
        }
    });
}