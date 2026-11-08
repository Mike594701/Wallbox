
var DEBUG=0;  // Affiche le DEBUG du Widget
// 1 : en temps reel
// 2 : en charge
// 3 : disponible (pas de voiture)
// 4 : en attente (voiture brancher et 100%)
// 5 : en pause   (voiture brancher)

if(DEBUG) {
  var DEBUG_VERROUILLAGE = 0;   // 0: borne déverrouiller; 1: borne vérrouiller
  var DEBUG_STATUS = [
      ['PAS DE TRACE DE DEBUG'],
      ['DEBUG EN TEMPS REEL'],
      ['194', 'CHARGING', 'fa-stop' , 'en charge'],   // 2
      ['161', 'READY', 'fa-plug' , 'disponible'],     // 3
      ['180', 'WAITING', 'fa-rotate' , 'en attente'], // 4
      ['182', 'PAUSED', 'fa-play' , 'en pause'],      // 5
      ['181', 'READY', 'fa-plug' , 'charge terminée'], // 6
      ['164', 'LOCKED', 'fa-lock-open' , 'attente authentification'] // 7
    ];

  // Affiche les variables JS
  document.getElementById('variablesJS').classList.remove("hidden");
  
  // Affiche les bordures des grids
  document.getElementsByClassName('wallbox_row')[0].classList.add("debug");   
}

var max_value_ampere = $('#max_value_ampere').text();
if(max_value_ampere == '') {
  max_value_ampere = 32;
}

var max_value_ampere_user = $('#max_value_ampere_user').text();
if(max_value_ampere_user == '') {
  max_value_ampere_user = 6;
}

// Etat de la borne au format numeric
var valeur_statusid = $('#valeur_statusid').text();
var valeur_verrouillage = $('#valeur_verrouillage').text();

// Durée de la charge en seconde
// var valeur_chargingtime = $('#valeur_chargingtime').text();

// Nouveau firmware de disponible
var valeur_new_firmware = $('#valeur_new_firmware').text();

function log_debug(trace="", val="") {
  if(DEBUG >= 1) { console.info(trace + ' : ' + val); }
}

// initialisation du widget graphique
$("#slider").roundSlider({
	circleShape: "half-top",
  sliderType: "min-range",
	min: 6,
	max: max_value_ampere,
	radius: 72,   // The height and width of the control considered as (2 * radius)
	mouseScrollAction: true,
  editableTooltip: false,
  keyboardAction: false,
  startValue: max_value_ampere_user,
  step: 1,
  handleShape: "dot",
	handleSize: "+5"
});

// Mise en couleur du slider de la borne
var status_borne = $('.status_borne').text().toLowerCase();

// Affichage du DEBUG
if (DEBUG_STATUS) {
  if(DEBUG > 1) { 
    console.warn('FORCE AFFICHAGE'); 

    status_borne=DEBUG_STATUS[DEBUG][3];
    if ((status_borne).length > 15) {        
      text_slider=status_borne.toLowerCase().substr(0,15) + ".";
    }
    else {
      text_slider=status_borne.toLowerCase();
    }
    $('.status_borne').text(text_slider);
    $('#info_status_borne').value(status_borne[0].toUpperCase() + status_borne.slice(1) + '<div id="info_status_borne_action"></div><div class="WallBox-bar-status"><span id="anim-WallBox-bar-status"></span></div>');
    console.log($('#info_status_borne').value());

    valeur_statusid=DEBUG_STATUS[DEBUG][0];
    $('#valeur_statusid').text(valeur_statusid);
    
    valeur_verrouillage = DEBUG_VERROUILLAGE;    
  }
  log_debug('Réglage borne', max_value_ampere_user + '/' + max_value_ampere + ' A');
  log_debug('valeur_verrouillage', valeur_verrouillage);

}

// Icone etat actuel de la borne (verrouillé/déverrouiller)
var iconLock = 'fa-question';
if(valeur_verrouillage == 1) {
  iconLock = 'fa-lock';
}    
else if(valeur_verrouillage == 0) {
  iconLock = 'fa-unlock';
}

// Init icone et couleur des boutons
var iconCharge = 'fa-question';
var couleurLock = 'color-gris-clair';
var couleurCharge = 'color-gris-clair';

log_debug('status_borne', status_borne);
switch(status_borne) {
  case 'verrouillé':
    // Slider réglage de la puissance de la borne
    document.getElementsByClassName('rs-handle')[0].style.backgroundImage = 'linear-gradient(to right, #f9c857, #fac348, #fcbd38, #fdb824, #ffb202)';
    document.getElementsByClassName('ring')[0].style.backgroundImage = 'linear-gradient(to right, #eaff00, #f4eb00, #fbd800, #fec400, #ffb100)';
    document.getElementsByClassName('control')[0].style.backgroundImage = 'linear-gradient(to right, #f9c857, #fac348, #fcbd38, #fdb824, #ffb202)';
    document.getElementsByClassName('control')[0].style.color = '#000000';  // Slider écriture en noir   
    document.getElementsByClassName('rs-tooltip rs-tooltip-text')[0].style.color = '#000000';  // Slider écriture en noir   
    // Panel Info de la borne
    document.getElementById('info_status_borne_action').innerHTML = "Déverrouiller le chargeur pour pouvoir l'utiliser";
    document.getElementsByClassName('WallBox-bar-status')[0].style.background = '#ffcc00';  // Barre Orange
    // Le panel info de charge est caché    
    break;
    
  case 'en charge':
    // Slider réglage de la puissance de la borne
    document.getElementsByClassName('rs-handle')[0].style.backgroundImage = 'linear-gradient(to right, #3045ff, #2836df, #1d28c0, #111ba2, #000d85)';
    document.getElementsByClassName('ring')[0].style.backgroundImage = 'linear-gradient(to right, #acb5ff, #909aff, #737fff, #5563ff, #3045ff)';
    document.getElementsByClassName('control')[0].style.backgroundImage = 'linear-gradient(to right, #3045ff, #3549ff, #3b4dff, #3f51fe, #4455fe)';
    document.getElementsByClassName('control')[0].style.color = '#ffffff';  // Slider écriture en blanc   
    document.getElementsByClassName('rs-tooltip rs-tooltip-text')[0].style.color = '#ffffff';  // Slider écriture en blanc   
    // Panel Info de la borne
    document.getElementById('info_status_borne_action').innerHTML = "Voiture branchée et en charge";
    document.getElementsByClassName('WallBox-bar-status')[0].style.background = '#4052fe';  // Barre Bleu   
    // Le panel info de charge est visible et actualisé    
    break;

  case 'disponible':
    // Slider réglage de la puissance de la borne
    document.getElementsByClassName('rs-handle')[0].style.backgroundImage = 'linear-gradient(to right, #bae3d8, #a2d8ca, #89cebb, #6fc3ad, #52b89e)';
    document.getElementsByClassName('ring')[0].style.backgroundImage = 'linear-gradient(to right, #bae3d8, #a2d8ca, #89cebb, #6fc3ad, #52b89e)';
    document.getElementsByClassName('control')[0].style.backgroundImage = 'linear-gradient(to right, #6ccdb3, #66c8ae, #5fc2a8, #59bda3, #52b89e)';
    document.getElementsByClassName('control')[0].style.color = '#ffffff';  // Slider écriture en blanc   
    document.getElementsByClassName('rs-tooltip rs-tooltip-text')[0].style.color = '#ffffff';  // Slider écriture en blanc   
    // Panel Info de la borne
    document.getElementById('info_status_borne_action').innerHTML = "En attente de la connexion de la voiture";
    document.getElementsByClassName('WallBox-bar-status')[0].style.background = '#52b89e';  // Barre Verte    
    // Le panel info de charge est caché
    break; 
    
  case 'en pause':
  case 'en attente':
    // Slider réglage de la puissance de la borne
    document.getElementsByClassName('rs-handle')[0].style.backgroundImage = 'linear-gradient(to right, #0ca7d6, #15addc, #1db3e2, #23b9e8, #29bfee)'; // Bouton
    document.getElementsByClassName('ring')[0].style.backgroundImage = 'linear-gradient(to right, #30cdff, #5cd5fe, #7bdcfe, #95e4fe, #acebff)';  // Contour
    document.getElementsByClassName('control')[0].style.backgroundImage = 'linear-gradient(to right, #30cdff, #2ec9fb, #2dc6f6, #2bc2f2, #29bfee)';  // Centre info
    document.getElementsByClassName('control')[0].style.color = '#ffffff';  // Slider écriture en blanc   
    document.getElementsByClassName('rs-tooltip rs-tooltip-text')[0].style.color = '#ffffff';  // Slider écriture en blanc   
    // Panel Info de la borne
    if(valeur_statusid == 182) {
      document.getElementById('info_status_borne_action').innerHTML = "Mis en pause par l'utilisateur";
    }
    else {
      document.getElementById('info_status_borne_action').innerHTML = "En attente de la demande de voiture ou dans la file d'attente";      
    }
    document.getElementsByClassName('WallBox-bar-status')[0].style.background = '#30cdff';  // Barre Bleu clair 
    // Le panel info de charge est visible et actualisé    
    break; 
    
  case 'charge terminée':
    // Slider réglage de la puissance de la borne
    document.getElementsByClassName('rs-handle')[0].style.backgroundImage = 'linear-gradient(to right, #bae3d8, #a2d8ca, #89cebb, #6fc3ad, #52b89e)'; // Bouton
    document.getElementsByClassName('ring')[0].style.backgroundImage = 'linear-gradient(90deg, #97FF95 0%, #4CAF50 100%)'; // Contour
    document.getElementsByClassName('control')[0].style.backgroundImage = 'linear-gradient(to right, #97FF95 0%, #4CAF50 100%)'; // Centre info
    document.getElementsByClassName('control')[0].style.color = '#ffffff';  // Slider écriture en blanc   
    document.getElementsByClassName('rs-tooltip rs-tooltip-text')[0].style.color = '#ffffff';  // Slider écriture en blanc  
    // Panel Info de la borne
    document.getElementById('info_status_borne_action').innerHTML = "Voiture branchée et en attente";
    document.getElementsByClassName('WallBox-bar-status')[0].style.background = '#30cdff';  // Barre Bleu clair 
    // Le panel info de charge est visible et actualisé    
    break;    
    
  default:
    log_debug('status_borne', status_borne + ' non traité');  
    break;
}

log_debug('valeur_statusid', valeur_statusid);
switch(valeur_statusid) {
  // On masque le panel info de charge 
  case '209' :  // En attente de la connexion de la voiture et borne verrouillé
  case '210' :  // On vient de brancher la voiture avec la borne verrouillé (ne charge pas)
  case '161' :  // En attente de la connexion de la voiture et borne déverrouiller   
    document.getElementById('info_chargingtime').classList.add("hidden");
    document.getElementById('info_energyconsumed').classList.add("hidden");
    document.getElementById('info_energypricetotal').classList.add("hidden");   
    document.getElementById('anim-WallBox-bar-status').classList.add("hidden"); 
    document.getElementById('valeur_temps_reel').classList.add("hidden"); 
    
    // Boutons de control de la borne
    if((valeur_statusid == 209) || (valeur_statusid == 210)) {  // Borne verrouillée
      // document.getElementsByClassName('lockUnlockButton')[0].innerHTML = '<i class="fa fa-lock cercle color-verrouille"></i></label>';    
      // document.getElementsByClassName('chargePauseButton')[0].innerHTML = '<i class="fa fa-play cercle color-gris-clair"></i></label>';
      couleurLock = 'color-verrouille';
      couleurCharge = 'color-gris-clair';
      iconCharge = 'fa-play';        
    }
    else if(valeur_statusid == 161) { // Borne déverrouillée et libre
      // document.getElementsByClassName('lockUnlockButton')[0].innerHTML = '<i class="fa fa-unlock cercle color-verte"></i></label>';        
      // document.getElementsByClassName('chargePauseButton')[0].innerHTML = '<i class="fa fa-play cercle color-verte"></i></label>';        
      couleurLock = 'color-verte';
      couleurCharge = 'color-verte';
      iconCharge = 'fa-play';    
    }
    else {
      log_debug('Boutons de control', 'non modifier');
    }
    break;  
    
  case '194':   // En charge de la voiture    
  case '182':   // En pause (avec voiture branchée)    
  case '181':   // Charge terminée (avec voiture branchée et chargée à 100%)    
    document.getElementById('info_chargingtime').classList.remove("hidden");
    document.getElementById('info_energyconsumed').classList.remove("hidden");
    document.getElementById('info_energypricetotal').classList.remove("hidden"); 
    document.getElementById('valeur_temps_reel').classList.remove("hidden");    

    if(valeur_statusid == 194) {  // En charge
      couleurLock = 'color-bleu';
      couleurCharge = 'color-bleu';
      iconCharge = 'fa-pause';    
    }
    else if (valeur_statusid == 182) {  // En pause
      couleurLock = 'color-bleu-clair';
      couleurCharge = 'color-bleu-clair';
      iconCharge = 'fa-play';    
    }
    else if (valeur_statusid == 181) { // Charge à 100%
      couleurLock = 'color-vert-fonce';
      couleurCharge = 'color-vert-fonce';
      iconCharge = 'fa-pause';    
    }
    break;
  
  default :
    log_debug('valeur_statusid', valeur_statusid + ' non traité');  
    break;    
}

// Boutons de control de la borne
if(iconLock && couleurLock && iconCharge && couleurCharge) {
  // Etat actuel de la borne (verrouillé/déverrouiller)
  document.getElementsByClassName('lockUnlockButton')[0].innerHTML = '<i class="fa cercle ' + iconLock + ' ' + couleurLock + '"></i></label>';         
  
  // Commande actuel de la borne (Pause/Marche)
  document.getElementsByClassName('chargePauseButton')[0].innerHTML = '<i class="fa cercle ' + iconCharge + ' ' + couleurCharge + '"></i></label>';             
}
else {
  log_debug('Boutons de control', ' non traité');
}

// Affiche la disponibilite d'un nouveau firmware
if(valeur_new_firmware == 1) {
  document.getElementById('new_firmware').classList.remove("hidden"); 
}
else if (! document.getElementById('new_firmware').classList.contains('hidden')) {
  document.getElementById('new_firmware').classList.add("hidden");
}

/** Affichage de la durée en temps reel **/
/*
 function SecToHHMMSS(){
    DureeEnSeconde = parseInt(DureeEnSeconde) + 1;
    
    // HH:MM:SS
    hours = (Math.floor(DureeEnSeconde / 3600) < 10 ? '0' : '') + Math.floor(DureeEnSeconde / 3600);
    minutes = (Math.floor(DureeEnSeconde/ 60 % 60) < 10 ? '0' : '') + Math.floor(DureeEnSeconde/ 60 % 60);
    seconds = (Math.floor(DureeEnSeconde% 60) < 10 ? '0' : '') + Math.floor(DureeEnSeconde% 60);

    $('#valeur_chargingtime_tps_reel').text(hours + ":" + minutes + ":" + seconds);    
  }
  
if(! document.getElementById('info_chargingtime').classList.contains('hidden')) {
  var DureeEnSeconde = valeur_chargingtime;
  setInterval(SecToHHMMSS, 1000);    
}
else {
  // Stop le timer auto de charge
}
*/


/*********** L'affichage du widget est pret ***********/
$(document).ready(function () {
  // P'tit bug d'initialisation sur la valeur_amperage_max
  const valeur_amperage_max = document.getElementsByClassName("rs-tooltip-text")[0];
  valeur_amperage_max.style.marginLeft = "-22.5px";

});
