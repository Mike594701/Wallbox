
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


/*
* Permet la réorganisation des commandes dans l'équipement
*/
$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

$('.eqLogicAction[data-action=openLocationBlank]').on('click', function () {
	window.open($(this).attr("data-location"), "_blank", null);
});

/*
* Permet d'afficher les informations de équipement lors de son edition
*/
function printEqLogic(_eqLogic) {
  // console.table(_eqLogic); 
  // Lors de l'ajout de la borne, les informations ne sont pas encore disponible
  if (typeof _eqLogic.configuration.chargerid != "undefined") {
    $('.monmonwallbox[data-l1key=configuration][data-l2key=chargerid]').value(_eqLogic.configuration.chargerid);
    $('.monmonwallbox[data-l1key=configuration][data-l2key=chargertype]').value(_eqLogic.configuration.chargertype);
    $('.monmonwallbox[data-l1key=configuration][data-l2key=part_number]').value(_eqLogic.configuration.part_number);
    $('.monmonwallbox[data-l1key=configuration][data-l2key=software_currentVersion]').value(_eqLogic.configuration.software_currentVersion);
    $('.monmonwallbox[data-l1key=configuration][data-l2key=software_latestVersion]').value(_eqLogic.configuration.software_latestVersion);
    $('.monmonwallbox[data-l1key=configuration][data-l2key=createtime]').value(_eqLogic.configuration.createtime);
    $('.monmonwallbox[data-l1key=configuration][data-l2key=lastCommunication]').value(_eqLogic.status.lastCommunication);
    
    // Affiche les infos des commandes de la borne
    _eqLogic.cmd.forEach(function (arrayCmd) {
      // Affiche la puissance du signal wifi
      if(arrayCmd.logicalId == 'signalwifi') {
        $('.monmonwallbox[data-l1key=configuration][data-l2key=signalwifi]').value(arrayCmd.state + arrayCmd.unite);
      }
    });
    
    // Affiche la disponibilité d'un nouveau firmware
    if(_eqLogic.configuration.software_currentVersion != _eqLogic.configuration.software_latestVersion) {
      $('#software_disponible').removeClass('hidden') ;
    }
    else {
      $('#software_disponible').addClass('hidden') ;
    }

    // Affiche la photo de la borne
    $("img[id^='img-']").hide();
    $('#img-'+_eqLogic.configuration.chargerid).show();
  }
  else {
    // Le panel de droite avec les informations de équipement sont vide
    // console.log('new');
  }

}

/*
* Fonction permettant l'affichage des commandes dans l'équipement
*/
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = {configuration: {}}
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {}
  }
  
  var custom_affichage = '';
  if (typeof _cmd.configuration.options !== 'undefined') {  
    // Une configuration d'affichage existe pour la commande
    custom_affichage = _cmd.configuration.options;
  }
  // console.log(init(_cmd.id) + " : ");
  // console.log(custom_affichage);
  
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="id"></span>'
  tr += '</td>'
  tr += '<td>'  // Colonne Nom, on affiche les options de la commande liee
  tr += '<div class="input-group">'
  tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
  tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
  tr += '</div>'
  if((typeof custom_affichage === "object") && (custom_affichage !== null)){
    if((isset(custom_affichage.cmd_lier)) && (custom_affichage.cmd_lier == 1)) {
      cmd_lier_status = '';
    }
    else {
      cmd_lier_status = 'hidden';    
    }      
    tr += '<select class="cmdAttr form-control input-sm ' + cmd_lier_status + '" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
    tr += '<option value="">{{Aucune}}</option>'
    tr += '</select>'
  }
  else {
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
    tr += '<option value="">{{Aucune}}</option>'
    tr += '</select>'
  }
  tr += '</td>'
  tr += '<td>'  // Colonne Type, on affiche les options
  if((typeof custom_affichage === "object") && (custom_affichage !== null)){
    if((isset(custom_affichage.type)) && (custom_affichage.type == 1)) {
      type_status = '';
    }
    else {
      type_status = 'hidden';  
      // tr += '<span class="cmdAttr">'+init(_cmd.type)+'</span>';  // Type de la commande      
    }
    tr += '<span class="type ' + type_status + '" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
    tr += '<span class="subType ' + type_status + '" subType="' + init(_cmd.subType) + '"></span>'
  }
  else {
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'    
  }
  tr += '</td>'
  tr += '<td>'  // Colonne options, on affiche les options de la commande
  if((typeof custom_affichage === "object") && (custom_affichage !== null)){
    // option : Visible
    if((isset(custom_affichage.afficher)) && (custom_affichage.afficher == 1)) {
      afficher_status = '';
    }
    else {
      afficher_status = 'hidden';    
    }
    tr += '<label class="checkbox-inline ' + afficher_status + '"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '

    // option : Historiser
    if((isset(custom_affichage.historiser)) && (custom_affichage.historiser == 1)) {
      historiser_status = '';
    }
    else {
      historiser_status = 'hidden';    
    }
    tr += '<label class="checkbox-inline ' + historiser_status + '"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '

    // option : Inverser
    if((isset(custom_affichage.inverser)) && (custom_affichage.inverser == 1)) {
      inverser_status = '';
    }
    else {
      inverser_status = 'hidden';    
    }
    tr += '<label class="checkbox-inline ' + inverser_status + '"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '

    // option : Min
    if((isset(custom_affichage.min)) && (custom_affichage.min == 1)) {
      min_status = '';
    }
    else {
      min_status = 'hidden';    
    }
    tr += '<div style="margin-top:7px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm ' + min_status + '" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'

    // option : Max
    if((isset(custom_affichage.max)) && (custom_affichage.max == 1)) {
      max_status = '';
    }
    else {
      max_status = 'hidden';    
    }
    tr += '<input class="tooltips cmdAttr form-control input-sm ' + max_status + '" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'

    // option : Unite
    if((isset(custom_affichage.unite)) && (custom_affichage.unite == 1)) {
      unite_status = '';
    }
    else {
      unite_status = 'hidden';    
    }
    tr += '<input class="tooltips cmdAttr form-control input-sm ' + unite_status + '" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'

  }
  else {
    // Y a pas de configuration d'options, on affiche tout par defaut
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
    tr += '<div style="margin-top:7px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  }
  tr += '</div>'
  tr += '</td>'
  tr += '<td>'; // Colonne Etat
  tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>'; 
  tr += '</td>';
  tr += '<td>'
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> Tester</a>'
  }
  tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
  tr += '</tr>';
  
  $('#table_cmd tbody').append(tr);
  var tr = $('#table_cmd tbody tr').last();
  jeedom.eqLogic.builSelectCmd({
    id: $('.eqLogicAttr[data-l1key=id]').value(),
    filter: { type: 'info' },
    error: function (error) {
      $('#div_alert').showAlert({ message: error.message, level: 'danger' });
    },
    success: function (result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result);
      tr.setValues(_cmd, '.cmdAttr');
      jeedom.cmd.changeType(tr, init(_cmd.subType));
    }
  });
}
