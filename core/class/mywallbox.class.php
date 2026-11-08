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
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class mywallbox extends eqLogic {
  /*     * *************************Attributs****************************** */
  const DEFAULT_SYNC_CRON = '*/5 * * * *';
  const URL = 'https://api.wall-box.com';
  const FICHIER_LOG = 'mywallbox';
   
  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
    public static $_widgetPossibility = array();
  */
   
  /* * ***********************Methode static*************************** */
   
  // Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron() {
		$eqLogics = ($_eqlogic_id !== null) ? array(eqLogic::byId($_eqlogic_id)) : eqLogic::byType('mywallbox', true);
		foreach ($eqLogics as $eqlogic) {
      if($eqlogic->getIsEnable() == 0) {
        continue;
      }
      $autorefresh = $eqlogic->getConfiguration('autorefresh');
      if($autorefresh != '') {
        try {
          $cron = new Cron\CronExpression(checkAndFixCron($autorefresh), new Cron\FieldFactory);
          if($cron->isDue()) {
            $username = config::byKey("username", "mywallbox");
            $password = config::byKey("password", "mywallbox");
            if($username == null || $password == null){
              throw new Exception("Vous devez configurer le nom d'utilisateur et le mot de passe de votre compte myWallbox pour pouvoir utiliser ce plugin");
            }
            foreach (self::byType('mywallbox') as $wallbox) {   // boucle sur tous les équipements du plugin
              if ($wallbox->getIsEnable() == 1) {               // vérifie que l'équipement est actif
                $refresh = $wallbox->getCmd(null, 'refresh');   // on le met à jour
                if(is_object($refresh)) {
                  log::add('mywallbox', 'info', 'CRON - Equipement ' . $eqlogic->getId() . ' : ' . $eqlogic->getName() . ' | autorefresh = '.$autorefresh);
                  $refresh->execCmd();
                }
              }
            }                        
          }
        } 
        catch (Exception $exc) {
          log::add('mywallbox', 'error', __('Rafraichissement impossible ', __FILE__) . $eqlogic->getHumanName() . ' : ' . $autorefresh);
        }
      }
      else {
        log::add('mywallbox', 'error', __('Expression cron vide!', __FILE__) . $eqlogic->getHumanName());        
      }
		} 
  }
     
  // Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  /*
  public static function cron5() {
  }
  */

  /*
  * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10() {
  }
  */

  /*
  * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15() {
  }
  */

  /*
  * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30() {
  }
  */

  /*
  * Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly($_eqlogic_id = null) {
  }
  */

  /*
  * Fonction exécutée automatiquement tous les jours par Jeedom
  public static function cronDaily() {
  }
  */

  /*
   * Fonction pour récuperer la version actuelle du plugin
  */  
  public static function GetVersionPlugin()
  {
    // Lecture de la version dans la configuration du plugin (BD Jeedom)
    $pluginVersion = config::byKey("pluginVersion", "mywallbox");
    
    if ($pluginVersion) {  
      log::add('mywallbox', 'info', '[VERSION] GetVersionPlugin :: ' . $pluginVersion);    
    }
    else {
      log::add('mywallbox', 'warning', '[VERSION] Impossible de récupérer la version du plugin');
      $pluginVersion = 'introuvable';
    }

    return $pluginVersion;
  } 
   
  /*
   * Fonction pour enregistrer la version actuelle du plugin
  */  
  public static function SetVersionPlugin()
  {
    // Version dans le fichier info du plugin
    $pluginVersion = 'introuvable';
    try {
      if (!file_exists(dirname(__FILE__) . '/../../plugin_info/info.json')) {
        log::add('mywallbox', 'warning', '[VERSION] fichier info.json manquant');
      }
      $data = json_decode(file_get_contents(dirname(__FILE__) . '/../../plugin_info/info.json'), true);
      if (!is_array($data)) {
        log::add('mywallbox', 'warning', '[VERSION] Impossible de décoder le fichier \'info.json\'');
      }
      try {
        $pluginVersion = $data['pluginVersion'];
        // Enregistrement de la version dans la configuration du plugin (BD Jeedom)
        config::save('pluginVersion', $pluginVersion, 'mywallbox');
      }
      catch (\Exception $e) {
        log::add('mywallbox', 'warning', '[VERSION] Impossible de mettre à jour la version du plugin :: ' . $e->getMessage());
      }
    }
    catch (\Exception $e) {
      log::add('mywallbox', 'warning', '[VERSION] SetVersionPlugin ERROR :: ' . $e->getMessage());
    }
    log::add('mywallbox', 'info', '[VERSION] SetVersionPlugin :: ' . $pluginVersion);    

    return $pluginVersion;
  }    
   
  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement 
  public function preInsert() {
    $username = config::byKey("username", "mywallbox");
    $password = config::byKey("password", "mywallbox");
    if($username == null || $password == null){
      throw new Exception("Vous devez configurer le nom d'utilisateur et le mot de passe de votre compte myWallbox pour pouvoir utiliser ce plugin");
    }

    // Define auto refresh
    $this->setConfiguration('autorefresh', self::DEFAULT_SYNC_CRON);
  }

  // Fonction exécutée automatiquement après la création de l'équipement 
  public function postInsert() {
    log::add('mywallbox', 'debug', '******* postInsert() *******'); 
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement 
  public function preUpdate() {
    log::add('mywallbox', 'debug', '******* preUpdate() *******'); 
  }
 
  // Fonction exécutée automatiquement après la mise à jour de l'équipement 
  public function postUpdate() {
    log::add('mywallbox', 'debug', '******* postUpdate() *******'); 
  }
 
  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement 
  public function preSave() {
    log::add('mywallbox', 'debug', '******* preSave() *******');    
  }
 
  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) des commandes de l'équipement 
  public function postSave() {
    log::add('mywallbox', 'debug', '******* postSave() DEBUT *******');
    log::add('mywallbox', 'debug', print_r($this, true));  

    if($this->getConfiguration('chargerid') == ''){
      log::add('mywallbox', 'debug', 'postSave chargerid (null)');       
    }
    else {
      $new_eqLogical = false;
      
      // Refresh action
      $i = 1;
      $refresh = $this->getCmd(null, 'refresh');
      if(!is_object($refresh)) {
        log::add('mywallbox', 'info', 'Création  des commandes...');
        $new_eqLogical = true;
        $refresh = (new mywallboxCmd)
        ->setName(__('Rafraîchir', __FILE__))
        ->setLogicalId('refresh')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setType('action')
        ->setSubType('other')
        ->setConfiguration('options' , array(
                                      'cmd_lier' => 0, 
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0))                
        ->setDisplay('icon', '<i class="fas fa-sync"></i>')        
        ->setEqLogic_id($this->getId());
        $refresh->save();
      }

      // Nom de la borne
      $i++;
      $name = $this->getCmd(null, 'name');
      if(!is_object($name)) {
        $name = (new mywallboxCmd)
        ->setName(__('Nom', __FILE__))
        ->setLogicalId('name')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(0)
        ->setType('info')
        ->setSubType('string')
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0))                
        ->setDisplay('generic_type', 'GENERIC_INFO')
        ->setGeneric_type('GENERIC_INFO')
        ->setEqLogic_id($this->getId());
        $name->save();
      }
      
      // Dernière synchronisation
      $i++;
      $lastsync = $this->getCmd(null, 'lastsync');
      if(!is_object($lastsync)) {
        $lastsync = (new mywallboxCmd)
        ->setName(__('Dernière synchronisation', __FILE__))
        ->setLogicalId('lastsync')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(0)
        ->setType('info')
        ->setSubType('string')
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0))                
        ->setDisplay('generic_type', 'GENERIC_INFO')
        ->setGeneric_type('GENERIC_INFO')
        ->setEqLogic_id($this->getId());
        $lastsync->save();
      }
      
      // Contrôle de la charge : ON / OFF (mode toggle)
      $i++;
      $chargecontrol = $this->getCmd(null, 'chargecontrol');
      if(!is_object($chargecontrol)) {
        $chargecontrol = (new mywallboxCmd)
        ->setName(__('Contrôle de la charge', __FILE__))
        ->setLogicalId('chargecontrol')
        ->setOrder($i)
        ->setType('action')
        ->setSubType('other')
        ->setConfiguration('options' , array(
                                      'cmd_lier' => 0, 
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0))                
        ->setDisplay('icon', '<i class="fa fa-stop"></i>')
        ->setEqLogic_id($this->getId());
        $chargecontrol->save();
      }         

      // Commande de Verrouillage ou Déverrouillage du chargeur
      $i++;
      $lockcontrol = $this->getCmd(null, 'lockcontrol');
      if(!is_object($lockcontrol)) {
        $lockcontrol = (new mywallboxCmd)
        ->setName(__('Verrouillage  Déverrouillage du chargeur', __FILE__))
        ->setLogicalId('lockcontrol')
        ->setOrder($i)
        ->setType('action')
        ->setSubType('other')
        ->setConfiguration('options' , array(
                                      'cmd_lier' => 0, 
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0))                        
        ->setDisplay('icon', '<i class="fa fa-lock"></i>')
        ->setEqLogic_id($this->getId());
        $lockcontrol->save();
      }
      
      // Etat du verrouillage du chargeur
      $i++;
      $lockcontrolid = $this->getCmd(null, 'lockcontrolid');
      if(!is_object($lockcontrolid)) {
        $lockcontrolid = (new mywallboxCmd)
        ->setName(__('Accès borne', __FILE__))
        ->setLogicalId('lockcontrolid')
        ->setOrder($i)
        ->setIsVisible(0)
        ->setIsHistorized(0)
        ->setType('info')
        ->setSubType('numeric')
        ->setUnite('')
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0))                        
        ->setEqLogic_id($this->getId());
        $lockcontrolid->save();
      }
      
      // Statut de la borne
      $i++;
      $status = $this->getCmd(null, 'status');
      if(!is_object($status)) {
        $status = (new mywallboxCmd)
        ->setName(__('État', __FILE__))
        ->setLogicalId('status')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(0)
        ->setType('info')
        ->setSubType('string')
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0))                
        ->setDisplay('generic_type', 'GENERIC_INFO')
        ->setGeneric_type('GENERIC_INFO')
        ->setTemplate('dashboard', 'core::line')             
        ->setEqLogic_id($this->getId());
        $status->save();
      }  

      // Statut numerique de la borne
      $i++;
      $statusid = $this->getCmd(null, 'statusid');
      if(!is_object($statusid)) {
        $statusid = (new mywallboxCmd)
        ->setName(__('Statut ID', __FILE__))
        ->setLogicalId('statusid')
        ->setOrder($i)
        ->setIsVisible(0)
        ->setIsHistorized(0)
        ->setType('info')
        ->setSubType('numeric')
        ->setUnite('')
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0))                        
        ->setEqLogic_id($this->getId());
        $statusid->save();
      }       
                  
      // Energie totale consommée par la recharge
      $i++;      
      $energyconsumed = $this->getCmd(null, 'energyconsumed');
      if(!is_object($energyconsumed)) {
        $energyconsumed = (new mywallboxCmd)
        ->setName(__('Énergie consommée', __FILE__))
        ->setLogicalId('energyconsumed')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(1)
        ->setType('info')
        ->setSubType('numeric')
        ->setUnite('kWh')
        ->setConfiguration('minValue' , '0')
        ->setConfiguration('maxValue' , '60')    
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 1, 
                                      'min' => 1, 
                                      'max' => 1, 
                                      'inverser' => 0, 
                                      'unite' => 1))
        ->setTemplate('dashboard', 'core::line')     
        ->setEqLogic_id($this->getId());
        $energyconsumed->save();
      }   

      
      // Puissance de chargement (en temps réel)
      $i++;
      $power = $this->getCmd(null, 'power');
      if(!is_object($power)) {
        $power = (new mywallboxCmd)
        ->setName(__('Puissance', __FILE__))
        ->setLogicalId('power')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(1)
        ->setType('info')
        ->setSubType('numeric')
        ->setUnite('kW')
        ->setConfiguration('minValue' , '0')
        ->setConfiguration('maxValue' , '32')   
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 1, 
                                      'min' => 1, 
                                      'max' => 1, 
                                      'inverser' => 0, 
                                      'unite' => 1))                
        
        // ->setDisplay('generic_type', 'POWER')
        // ->setGeneric_type('POWER')
        ->setEqLogic_id($this->getId());
        $power->save();
      }  

      // Reglage de la puissance maximal
      $i++;
      $valmaxpower = $this->getCmd(null, 'valmaxpower');
      if(!is_object($valmaxpower)) {
        $valmaxpower = (new mywallboxCmd)
        ->setName(__('Valeur puissance maximum', __FILE__))
        ->setLogicalId('valmaxpower')
        ->setOrder($i)
        ->setIsVisible(0)
        ->setIsHistorized(0)
        ->setType('info')
        ->setSubType('numeric')
        ->setUnite('A')
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 1))                        
        ->setEqLogic_id($this->getId());
        $valmaxpower->save();
      }         
      
      // Reglage de la puissance maximal
      $i++;
      $maxpower = $this->getCmd(null, 'maxpower');
      if(!is_object($maxpower)) {
        $maxpower = (new mywallboxCmd)
        ->setName(__('Puissance maximum', __FILE__))
        ->setLogicalId('maxpower')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(0)
        ->setType('action')
        ->setSubType('slider')
        ->setUnite('A')
        ->setConfiguration('updateCmdId', $valmaxpower->getEqLogic_id())
        ->setConfiguration('step', 1)        
        ->setConfiguration('minValue' , '0') 
        ->setConfiguration('maxValue' , $this->getConfiguration('maxValue'))
        ->setConfiguration('options' , array(
                                      'cmd_lier' => 1, 
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 1, 
                                      'max' => 1, 
                                      'inverser' => 0, 
                                      'unite' => 0))                        
        ->setEqLogic_id($this->getId());
        $maxpower->save();
      }   
      elseif(is_object($maxpower) && ($maxpower->getConfiguration('maxValue')) != $this->getConfiguration('maxValue')) {
        // La valeur de l'amperage MAX par phase a changer, on met à jour
        $maxpower->setConfiguration('maxValue' , $this->getConfiguration('maxValue'));
        $maxpower->save();
      }        

      // Temps de charge
      $i++;
      $chargingtime = $this->getCmd(null, 'chargingtime');
      if(!is_object($chargingtime)) {
        $chargingtime = (new mywallboxCmd)
        ->setName(__('Temps de charge', __FILE__))
        ->setLogicalId('chargingtime')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(0)
        ->setType('info')
        ->setSubType('string')
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0))                
        
        ->setDisplay('generic_type', 'GENERIC_INFO')
        ->setGeneric_type('GENERIC_INFO')
        ->setTemplate('dashboard', 'core::line')             
        ->setEqLogic_id($this->getId());
        $chargingtime->save();
      }      

      // Coût énergétique de la charge
      $i++;
      $energypricetotal = $this->getCmd(null, 'energypricetotal');
      if(!is_object($energypricetotal)) {
        $energypricetotal = (new mywallboxCmd)
        ->setName(__('Coût énergétique', __FILE__))
        ->setLogicalId('energypricetotal')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(1)
        ->setType('info')
        // ->setSubType('other')
        ->setSubType('numeric')
        ->setUnite('€')  
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 1, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 1)) 
        ->setTemplate('dashboard', 'core::line')     
        ->setEqLogic_id($this->getId());
        $energypricetotal->save();
      }            

      // Signal Wifi
      $i++;
      $signalwifi = $this->getCmd(null, 'signalwifi');
      if(!is_object($signalwifi)) {
        $signalwifi = (new mywallboxCmd)
        ->setName(__('Signal WiFi', __FILE__))
        ->setLogicalId('signalwifi')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(1)
        ->setType('info')
        ->setSubType('numeric')
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 1, 
                                      'historiser' => 1, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 1))                        
        ->setDisplay('icon', '<i class="fas fa-wifi"></i>')    
        ->setUnite('%')
        ->setTemplate('dashboard', 'core::line')        
        ->setEqLogic_id($this->getId());
        $signalwifi->save();
      }  
      
      // Nombre total de session de charge
      $i++;
      $totalsessions = $this->getCmd(null, 'totalsessions');
      if(!is_object($totalsessions)) {
        $totalsessions = (new mywallboxCmd)
        ->setName(__('Sessions de charge', __FILE__))
        ->setLogicalId('totalsessions')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(1)
        ->setType('info')
        ->setSubType('numeric')   
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 1, 
                                      'historiser' => 1, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0)) 
        ->setDisplay('generic_type', 'GENERIC_INFO')
        ->setGeneric_type('GENERIC_INFO')
        ->setTemplate('dashboard', 'core::line')        
        ->setEqLogic_id($this->getId());
        $totalsessions->save();
      }
     
      
      // Énergie consommée total
      $i++;
      $totalpuissance = $this->getCmd(null, 'totalpuissance');
      if(!is_object($totalpuissance)) {
        $totalpuissance = (new mywallboxCmd)
        ->setName(__('Énergie consommée totale', __FILE__))
        ->setLogicalId('totalpuissance')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(1)
        ->setType('info')
        ->setSubType('numeric') 
        ->setUnite('Wh')  
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 1, 
                                      'historiser' => 1, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 1))    
        ->setTemplate('dashboard', 'core::line')
        ->setEqLogic_id($this->getId());
        $totalpuissance->save();
      }

      // Temps de charge total
      $i++;
      $totalchargingTime = $this->getCmd(null, 'totalchargingTime');
      if(!is_object($totalchargingTime)) {
        $totalchargingTime = (new mywallboxCmd)
        ->setName(__('Temps de charge total', __FILE__))
        ->setLogicalId('totalchargingTime')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(1)
        ->setType('info')
        ->setSubType('numeric')     
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 1, 
                                      'historiser' => 1, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0)) 
        ->setTemplate('dashboard', 'core::line')                                      
        ->setEqLogic_id($this->getId());
        $totalchargingTime->save();
      }

      // Coût énergétique total des charges
      $i++;
      $totalcout = $this->getCmd(null, 'totalcout');
      if(!is_object($totalcout)) {
        $totalcout = (new mywallboxCmd)
        ->setName(__('Coût énergétique total', __FILE__))
        ->setLogicalId('totalcout')
        ->setOrder($i)
        ->setIsVisible(1)
        ->setIsHistorized(1)
        ->setType('info')
        ->setSubType('numeric')
        ->setUnite('€')  
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 1, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 1))    
        ->setTemplate('dashboard', 'core::line')
        ->setEqLogic_id($this->getId());
        $totalcout->save();
      }

      // Si c'est une nouvelle wallbox on fait un refresh pour avoir toutes ses infos
      if ($new_eqLogical) {
        log::add('mywallbox', 'info', 'Récupération des informations de la Wallbox : ' . $this->getName() . ' (id:' . $this->getId() . ')');
        $refresh = $this->getCmd(null, 'refresh');
        if(is_object($refresh)) {
          $refresh->execCmd();   
        }
      }

      // Identification impossible
      $i++;
      $tokenHS = $this->getCmd(null, 'tokenhs');
      if(!is_object($tokenHS)) {
        $tokenHS = (new mywallboxCmd)
        ->setName(__('Nombre erreur token', __FILE__))
        ->setLogicalId('tokenhs')
        ->setOrder($i)
        ->setIsVisible(0)
        ->setIsHistorized(0)
        ->setType('info')
        ->setSubType('numeric')
        ->setUnite('')
        ->setConfiguration('options' , array(
                                      'type' => 0, 
                                      'afficher' => 0, 
                                      'historiser' => 0, 
                                      'min' => 0, 
                                      'max' => 0, 
                                      'inverser' => 0, 
                                      'unite' => 0))                        
        ->setEqLogic_id($this->getId());
        $tokenHS->save();
      }      
    }    
    log::add('mywallbox', 'debug', '******* postSave() FIN *******');      
  }
 
  // Fonction exécutée automatiquement avant la suppression de l'équipement 
  public function preRemove() {
    log::add('mywallbox', 'info', 'Suppression de la Wallbox : ' . $this->getName() . ' (id:' . $this->getId() . ')');
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement 
  public function postRemove() {
    log::add('mywallbox', 'debug', '******* postRemove() *******');     
  }
   
  // Fonction d'authentification pour obtenir le token de connexion
  public function getWallboxToken(){         
    // Authentification de l'application
    $username = config::byKey("username", "mywallbox");
    $password = config::byKey("password", "mywallbox");

    log::add('mywallbox', 'debug', 'getWallboxToken('.$username.')' );  
    $authenticationencoded = base64_encode($username.":".$password);
    
    // Requete du token
    $opts = array('http' =>
                    array(
                      'method'  => 'GET',
                      'header'  => 'Authorization: Basic '.$authenticationencoded
                      )
                  );
    
    $context = stream_context_create($opts);    
    $result = file_get_contents(self::URL . '/auth/token/user', false, $context);
    $clientHTTP = json_decode($result,true);

    if($clientHTTP['status'] == "200"){
      log::add('mywallbox', 'info', 'Authentification SUCCES');
      $tokenhs = $this->getCmd(null, 'tokenhs');
      if (is_object($tokenhs)) {               
        $this->checkAndUpdateCmd('tokenhs', 0);  
      }        
      return $clientHTTP['jwt'];
    }
    else {
      log::add('mywallbox', 'warning' ,"WallboxToken introuvable! [" . $clientHTTP . "]");      
      // ajax::error("Récupération des données impossible.");
      // throw new Exception($clientHTTP);
      $tokenhs = $this->getCmd(null, 'tokenhs');
      if (is_object($tokenhs)) {        
        $tokenhs = $tokenhs->execCmd();          
        $this->checkAndUpdateCmd('tokenhs', ($tokenhs+1));  
      } 
      return '';
    }
  }

  // Fonction pour recuperer la liste des chargeurs Wallbox
  public function getChargerList(){
    log::add('mywallbox', 'debug', 'getChargerList()');
    $token = $this->getWallboxToken();    
    if($token != null) {
      $opts = array('http' =>
                      array(
                        'method'  => 'GET',
                        'header'  => 'Authorization: Bearer '.$token
                      )
                    );
       
      $context = stream_context_create($opts);       
      $result = file_get_contents(self::URL . '/v3/chargers/groups', false, $context);
      $objectresult = json_decode($result, true);

      // On extrait la liste des chargeurs
      $chargeurs_liste = $objectresult['result']['groups'][0]['chargers'];
   
      // log::add('mywallbox', 'debug', 'Liste des chargeurs  : '. print_r($chargeurs_liste, true));      
      log::add('mywallbox', 'debug', 'Nombre de chargeurs  : '. count($chargeurs_liste));      
    }
    else {
      log::add('mywallbox', 'warning' ,"L'utilisateur n'est pas authentifié");
      // throw new Exception("L'utilisateur n'est pas authentifié");
      $chargeurs_liste['ERREUR'] = 'WallboxToken introuvable';
    }
    return $chargeurs_liste;
  }
   
  // Fonction pour recuperer les donnees des chargeurs
  public function getChargerStatus() {
    $chargerId = $this->getConfiguration("chargerid");
    log::add('mywallbox', 'debug', 'getChargerStatus('. $chargerId.')');
    $token = $this->getWallboxToken();
    
    if($token != null && $chargerId != null){
      $opts = array('http' =>
                      array(
                        'method'  => 'GET',
                        'header'  => 'Authorization: Bearer '.$token
                        )
                    );

      $context  = stream_context_create($opts);         
      $chargeur_status = file_get_contents(self::URL . '/chargers/status/' . $chargerId, false, $context);
      $chargeur_data = file_get_contents(self::URL . '/v2/charger/'.$chargerId, false, $context);      
      $object_chargeur_status = json_decode($chargeur_status,true);
      $object_chargeur_data = json_decode($chargeur_data,true);
      
      $objChargeurData = array_merge_recursive( $object_chargeur_status, $object_chargeur_data );
      
      //log::add('mywallbox', 'debug', 'objChargeurData  : '. print_r($objChargeurData, true));
      return $objChargeurData;
    }
    else{
      // throw new Exception("L'utilisateur n'est pas authentifié");
      $tokenhs = $this->getCmd(null, 'tokenhs');
      if (is_object($tokenhs)) {        
        $tokenhs = $tokenhs->execCmd();          
        log::add('mywallbox', 'debug', 'Connexion au CloudMywallBox impossible : ' . $tokenhs . '/5');    
        if($tokenhs == 5) {
          throw new Exception("Perte de connexion avec le CloudWallBox");
        }
      }
      return '';      
    }
  }

  // Fonction 
  public function defineChargingState($resume)
  {
    $chargerId = $this->getConfiguration("chargerid");
    log::add('mywallbox', 'debug', 'DEBUT defineChargingState(' . $resume . ') : '. $chargerId);
    $token = $this->getWallboxToken();

    if($token != null && $chargerId != null){
      if(!$resume)
      {
        $data = '{"action":2}'; // pause id
      }
      else {
        $data = '{"action":1}'; //resume id
      }

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => self::URL .'/v3/chargers/'.$chargerId.'/remote-action',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>$data,
        CURLOPT_HTTPHEADER => array(
          'Accept: application/json',
          'Content-Type: application/json',
          'Authorization: Bearer '.$token
        ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      log::add('mywallbox', 'debug', 'FIN defineChargingState : '. $response);

      $objectresult = json_decode($response,true);
      return $objectresult;
    }
    else{
      throw new Exception("L'utilisateur n'est pas authentifié");
    }
  }

  // Fonction pour definir l'état du chargeur (déverouiller ou deverouiller) 
  public function defineLockState($locked = "")
  {
    $chargerId = $this->getConfiguration("chargerid");  // Chargeur a déverouiller/deverouiller
    log::add('mywallbox', 'debug', 'DEBUT defineLockState('. $locked . ') : ' . $chargerId);
    $a_status_lock= array();
    
    $token = $this->getWallboxToken();      
    if($token != null && $chargerId != null) {
      if($locked == 1)
      {
        $data = '{"locked":0}'; // déverouiller le chargeur
      }
      elseif($locked == 0) {
        $data = '{"locked":1}'; // vérouiller le chargeur
      }
      else {
        $data = ''; // Oula pas normal !
      }
      
      log::add('mywallbox', 'debug', 'defineLockState  data : '. $data);
         
      if($data != "") {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => self::URL . '/v2/charger/' . $chargerId,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PUT',
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
                                  'Accept: application/json',
                                  'Content-Type: application/json',
                                  'Authorization: Bearer '.$token
                                ),
        ));

        $response = curl_exec($curl); // envoi la commande à l'API de la borne
        curl_close($curl);  // ferme la connexion à l'API
        
        // log::add('mywallbox', 'debug', 'response :' . $response);
        
        $responseobj = json_decode($response,true);
        
        // recupere le retour de la commande sur la borne
        $a_status_lock['ETAT'] = $responseobj['data']['chargerData']['locked'];
        $a_status_lock['TEXT'] = "vérouiller";        
      }
      else {
        $a_status_lock['ETAT'] = "-1";
        $a_status_lock['TEXT'] = "inconnu";   
      }

      log::add('mywallbox', 'debug', 'FIN defineLockState : '. $a_status_lock['ETAT'] . ' -> ' . $a_status_lock['TEXT']);
      return $a_status_lock;
    }
    else{
      throw new Exception("L'utilisateur n'est pas authentifié");
    }
  }

  // function to define max amp of a charge
  public function defineMaxAmp($ampValueMax)
  {
    $chargerId = $this->getConfiguration("chargerid");
    log::add('mywallbox', 'debug', 'DEBUT defineMaxAmp('. $ampValueMax . ') : ' . $chargerId);   
    $a_MaxAmp= array();
    
    $token = $this->getWallboxToken();    
    if($token != null && $chargerId != null){
      $data = '{ "maxChargingCurrent":'.$ampValueMax.'}'; // A value Max

      $curl = curl_init();

      curl_setopt_array($curl, array(
      CURLOPT_URL => self::URL . '/v2/charger/'.$chargerId,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'PUT',
      CURLOPT_POSTFIELDS =>$data,
      CURLOPT_HTTPHEADER => array(
                              'Accept: application/json',
                              'Content-Type: application/json',
                              'Authorization: Bearer '.$token
                            ),
      ));

      $response = curl_exec($curl);
      curl_close($curl);
      // log::add('mywallbox', 'debug', 'defineMaxAmp '. $response);
      $objresponse = json_decode($response,true);
      
      if($objresponse['error']) {
        throw new Exception("Impossible de modifier la puissance de la borne");
      }
      else {
        // recupere le retour de la commande sur la borne
        if($objresponse['data']['chargerData']['maxChargingCurrent'] == $ampValueMax) {
          $a_MaxAmp['ETAT'] = "SUCCES";     
          $a_MaxAmp['TEXT'] = $objresponse['data']['chargerData']['maxChargingCurrent'];
        }
        else {
          $a_MaxAmp['ETAT'] = "ERREUR";     
          $a_MaxAmp['TEXT'] = $objresponse['data']['chargerData']['maxChargingCurrent'];
          throw new Exception("La modification de la puissance a échouée");          
        }
      }
     
      log::add('mywallbox', 'debug', 'FIN defineMaxAmp : maxChargingCurrent = '. $a_MaxAmp['TEXT'] . ' : ' . $a_MaxAmp['ETAT']); 
      $objresponse = null;
      return $a_MaxAmp;
    }
    else{
      throw new Exception("L'utilisateur n'est pas authentifié");
    }
  }

  // Synchronisation de l'heure de la borne avec celle du system local
  public function utcToLocal($date)
  {
     $localtimezone = date_default_timezone_get();
     $tm_tz_to = new DateTimeZone($localtimezone);
     $dt = new DateTime($date, new DateTimeZone('UTC'));
     $dt->setTimeZone(new DateTimeZone($tm_tz_to->getName()));
     $utc_time_from =$dt->format("d-m-Y H:i:s");
     // log::add('mywallbox', 'debug', 'Conversion '.$date.' (UTC) en '.$utc_time_from . ' (' . $localtimezone . ')');

     return $utc_time_from;
  }

  public function toHtml($_version = 'dashboard') {  
    log::add('mywallbox', 'debug', '--DEBUT toHtml('.$this->getConfiguration('widgetTemplate').') : version=' . $_version);

    if ($this->getIsEnable() != 1) {
      return '';
    }
    if (!$this->hasRight('r')) {
      return '';
    }
     
    $replace = $this->preToHtml($_version);
    if (!is_array($replace)) {
      return $replace;
    }
    $version = jeedom::versionAlias($_version);
    
    /******* Widget Wallbox *******/
    if ($this->getConfiguration('widgetTemplate') != 1) {
      // Affiche la tuile Jeedom
      $replace['#width#'] = $this->getDisplay('width');
      $replace['#height#'] = $this->getDisplay('height');
      log::add('mywallbox', 'debug', '--FIN toHtml : core');
      return parent::toHtml($_version);
    } 
    
    /******* Affcihe le widget Wallbox *******/
    // Template DASHBOARD
    if($version == 'dashboard') {
      $replace['#width#'] = '640px';
      $replace['#height#'] = '435px';
    } 
    else {
      // Template MOBILE
      $replace['#width#'] = '350px';
      $replace['#height#'] = '1524px';
    }      
     
    /******* Barre de titre *******/
    // Bouton REFRESH
    $refresh = $this->getCmd(null, 'refresh');
    $replace['#refresh#'] = is_object($refresh) ? $refresh->getId() : 'Introuvable';
    $replace['#refresh_titre_bouton#'] = __('Rafraîchir', __FILE__);  // Bouton pour rafraichir les infos       
    // Nom du chargeur
    $replace['#device_name#'] = $this->getConfiguration('chargerid');      
    // Model du chargeur
    $replace['#model_borne#'] = $this->getConfiguration('chargertype'); 
    // Puissance signal WIFI
    $signalwifi = $this->getCmd(null, 'signalwifi');
    $replace['#valeur_signalwifi#'] = is_object($signalwifi) ? $signalwifi->execCmd() : 'Introuvable';   
    
    
    /******* Slider de reglage manuel de l'amperage *******/
    // Puissance temps réelle consommée
    $power = $this->getCmd(null, 'power');
    $replace['#valeur_temps_reel#'] = is_object($power) ? $power->execCmd() . ' ' . $power->getUnite() : 'Introuvable';
    
    // Bouton control du verrouillage de la borne 
    $lockcontrol = $this->getCmd(null, 'lockcontrol');
    $replace['#lockcontrol#'] = is_object($lockcontrol) ? $lockcontrol->getId() : 'Introuvable';        
    
    // Bouton chargecontrol du verrouillage de la borne 
    $chargecontrol = $this->getCmd(null, 'chargecontrol');
    $replace['#chargecontrol#'] = is_object($chargecontrol) ? $chargecontrol->getId() : 'Introuvable';    
    
    // Bouton maxpower de la borne 
    $maxpower = $this->getCmd(null, 'maxpower');
    $replace['#maxpower#'] = is_object($chargecontrol) ? $maxpower->getId() : 'Introuvable';  
    
    /******* Panel d'état de la borne *******/
    // Etat de vérrouillage de la borne
    $status = $this->getCmd(null, 'status');
    $replace['#status_borne#'] = is_object($status) ? $status->execCmd() : 'Introuvable';
    // Durée de la change      
    $chargingtime = $this->getCmd(null, 'chargingtime');
    // $replace['#valeur_chargingtime#'] = is_object($chargingtime) ? $chargingtime->execCmd() : 'Introuvable';
    $cr_chargingtime = is_object($chargingtime) ? $chargingtime->execCmd() : 'Introuvable'; 
    // log::add('mywallbox', 'debug', 'cr_chargingtime : ' . $cr_chargingtime);
    if ($cr_chargingtime == '' || $cr_chargingtime == 'Introuvable') {
      $replace['#valeur_chargingtime#'] = "00:00:00";        
    }
    else {
      // $replace['#valeur_chargingtime#'] = $this->HHMMSSToSec($cr_chargingtime);  
      $replace['#valeur_chargingtime#'] = $cr_chargingtime;  
    }
    // Énergie consommée
    $energyconsumed = $this->getCmd(null, 'energyconsumed');
    $replace['#valeur_energyconsumed#'] = is_object($energyconsumed) ? $energyconsumed->execCmd() . ' ' . $energyconsumed->getUnite() : 'Introuvable'; 
    
    // Coût énergétique
    $energypricetotal = $this->getCmd(null, 'energypricetotal');
    $replace['#valeur_energypricetotal#'] = is_object($energypricetotal) ? $energypricetotal->execCmd() . ' ' . $energypricetotal->getUnite() : 'Introuvable';      


    /******* Panel info TOTAL *******/
    // Nombre total de session
    $totalsessions = $this->getCmd(null, 'totalsessions');
    $replace['#valeur_totalsessions#'] = is_object($totalsessions) ? $totalsessions->execCmd() : 'Introuvable';  
    // Temps total de charge
    $totalchargingTime = $this->getCmd(null, 'totalchargingTime');
    $replace['#valeur_totalcharge#'] = is_object($totalchargingTime) ? $this->secToHHMM($totalchargingTime->execCmd()) : 'Introuvable'; 
    // Puissance totale consommée
    $totalpuissance = $this->getCmd(null, 'totalpuissance');
    $cr_totalpuissance = is_object($totalpuissance) ? $totalpuissance->execCmd() : 'Introuvable'; 
    $cr_totalpuissance = intval($cr_totalpuissance);
    if ($cr_totalpuissance >= 1000) {
      $replace['#valeur_totalpuissance#'] = ($cr_totalpuissance / 1000) . ' k' . $totalpuissance->getUnite();
    }
    else {
      $replace['#valeur_totalpuissance#'] = $cr_totalpuissance . ' ' . $totalpuissance->getUnite();
    }      
    // Cout total financier
    $totalcout = $this->getCmd(null, 'totalcout');
    $replace['#valeur_totalcout#'] = is_object($totalcout) ? $totalcout->execCmd() . ' ' . $totalcout->getUnite() : 'Introuvable';  
    
    
    /******* Variables JS *******/
    // MAX ampere par phase
    $replace['#max_value_ampere#'] = $this->getConfiguration('maxValue'); // Intensite MAX par phase      
    // MAX ampere reglage manuel
    $valmaxpower = $this->getCmd(null, 'valmaxpower');
    $replace['#max_value_ampere_user#'] = is_object($valmaxpower) ? $valmaxpower->execCmd() : 'Introuvable'; 
    // ID de l'état de la borne
    $statusid = $this->getCmd(null, 'statusid');
    $replace['#valeur_statusid#'] = is_object($statusid) ? $statusid->execCmd() : 'Introuvable';        
    // Acces à la borne 
    $lockcontrolid = $this->getCmd(null, 'lockcontrolid');
    $replace['#valeur_verrouillage#'] = is_object($lockcontrolid) ? $lockcontrolid->execCmd() : 'Introuvable';      
    // Nouveau firmware 
    if($this->getConfiguration('software_currentVersion') != $this->getConfiguration('software_latestVersion')) {
      $replace['#valeur_new_firmware#'] = 1;
    }
    else {      
      $replace['#valeur_new_firmware#'] = 0;           
    }
    

    // Mise à jour du widget
    $html = template_replace($replace, getTemplate('core', $version, 'mywallbox.template', __CLASS__));
    cache::set('mywallboxWidgetdashboard' . $_version . $this->getId(), $html, 0);

    log::add('mywallbox', 'debug', '--FIN toHtml()');
    return $html;
  }
     
  public function secToHHMMSS($seconds)
  {
    $seconds = round(trim($seconds));
    // HH:MM:SS
    return sprintf('%02d:%02d:%02d', ($seconds/ 3600),($seconds/ 60 % 60), $seconds% 60);
  }  
  
  public function secToHHMM($seconds)
  {
    $seconds = round(trim($seconds));
    // HHhMM
    return sprintf('%02dh%02d', ($seconds/ 3600),($seconds/ 60 % 60));
  } 
  
  public function HHMMSSToSec($time)
  {
    $arr = explode(':', $time);
    if (count($arr) === 3) {
        return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
    }
    return $arr[0] * 60 + $arr[1];
  }  
   
   /*
   * Non obligatoire : permet de déclencher une action après modification de variable de configuration
   public static function postConfig_<Variable>() {
   }
   */
   
   /*
   * Non obligatoire : permet de déclencher une action avant modification de variable de configuration
   public static function preConfig_<Variable>() {
   }
   */
   
   /*     * **********************Getteur Setteur*************************** */
}

class mywallboxCmd extends cmd {
   /*     * *************************Attributs****************************** */
   
   /*
   public static $_widgetPossibility = array();
   */
   
   /*     * ***********************Methode static*************************** */
   
   
   /*     * *********************Methode d'instance************************* */
   
   /*
   * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
   public function dontRemoveCmd() {
      return true;
   }
   */   
    
  // Exécution d'une commande sur un équipement
   public function execute($_options = null) {
      log::add('mywallbox', 'info', '***** DEBUT execute(' . $this->getLogicalId() . ') *****');  

      $eqlogic = $this->getEqLogic(); //récupère l'éqlogic de la commande $this
      // log::add('mywallbox', 'debug', ' *** eqlogic  *** : ' . print_r($eqlogic, true));  
      // log::add('mywallbox', 'debug', ' *** CLASS  *** : ' . __CLASS__);  // => mywallboxCmd

      switch ($this->getLogicalId()) {
        case 'refresh':
          $info = $this->getEqLogic()->getChargerStatus(); 
          if ($info != '') {          
            $save=0;
            
            //DEBUG Force la valeur de l'info totalcout
            // $eqlogic->checkAndUpdateCmd('totalcout', '15.24'); // Coût énergétique total          
            
            log::add('mywallbox', 'debug', 'chargerid : [' . $eqlogic->getConfiguration('chargerid') . ']'); 
           
            // Mise à jour des commandes
            $eqlogic->checkAndUpdateCmd('name', $info['name']); // Nom de la borne
            $eqlogic->checkAndUpdateCmd('valmaxpower', $info['config_data']['max_charging_current']);   // Max A par phase (int)
            $eqlogic->checkAndUpdateCmd('lastsync', $this->getEqLogic()->utcToLocal($info['last_sync'])); // Derniere synchronisation avec la borne (str)
            $eqlogic->checkAndUpdateCmd('lockcontrolid', $info['data']['chargerData']['locked']); // verrouillé ou pas ? (int)
            $eqlogic->checkAndUpdateCmd('signalwifi', $info['data']['chargerData']['wifiSignal']); // Force du signal wifi (int)            

            // Mise à jour de la configuration de l'equipement
            // Enregistre le numero de serie, le type et le model de la Wallbox
            if(! $eqlogic->getConfiguration('chargerid')) {                    
              $eqlogic->setConfiguration('chargerid', $info['config_data']['charger_id']);  // SN
              // $eqlogic->setConfiguration('img_chargeur', $info['config_data']['image']);  // Image de la box
              $save++;
            }

            // Enregistre le type et le model de la Wallbox
            if(! $eqlogic->getConfiguration('chargertype')) {                    
              $eqlogic->setConfiguration('chargertype', $info['data']['chargerData']['chargerType']);  // Modele de la Wallbox
              $eqlogic->setConfiguration('part_number', $info['config_data']['part_number']);  // Reference technique de la wallbox  => PLP1-M-2-4-9-001-F
              $save++;
            }

            // Met à jour si le tarif du Kwh si il a changer
            if($eqlogic->getConfiguration('energy_price') != $info['config_data']['energy_price']) {
              $eqlogic->setConfiguration('energy_price', $info['config_data']['energy_price']);  // energy_price
              $save++;
            }         

            // Met à jour si la version logiciel du firmware si il a changer
            if($eqlogic->getConfiguration('software_currentVersion') != $info['config_data']['software']['currentVersion']) {
              $eqlogic->setConfiguration('software_currentVersion', $info['config_data']['software']['currentVersion']);  // currentVersion
              $save++;
            }         

            // Met à jour si la version logiciel du dernier firmware disponible
            if($eqlogic->getConfiguration('software_latestVersion') != $info['config_data']['software']['latestVersion']) {
              $eqlogic->setConfiguration('software_latestVersion', $info['config_data']['software']['latestVersion']);  // latestVersion
              $save++;
            }         

            // Met à jour l'intensite MAX par phase si elle a changer
            if($eqlogic->getConfiguration('maxValue') != $info['config_data']['max_available_current']) {
              $eqlogic->setConfiguration('maxValue' , $info['config_data']['max_available_current']);
              $save++;
            }

            // Enregistre les infos dans Jeedom
            if($save > 0) { 
              log::add('mywallbox', 'info', 'Enregistrement configuration : ' . $info['name']);
              $eqlogic->save(); 
            }

            // Status actuel de la borne         
            $status=$this->wbGetStatus($info['status_id']);
            $eqlogic->checkAndUpdateCmd('status', $status['TEXT']); // (str)
            $eqlogic->checkAndUpdateCmd('statusid', $status['STATUS_ID']);  // (int)
            
            log::add('mywallbox', 'debug', 'status[' . $info['status_id'] . '] = ' . $status['ETAT'] . ' / chargecontrol-icon: ' . $status['ICON'] . ' / text: ' . $status['TEXT'] );
            
            $eqlogic->checkAndUpdateCmd('power', $info['charging_power']);  // kW en temps reel (int)

            // Actualise l'icone d'état de la borne
            $chargecontrol = $eqlogic->getCmd(null, 'chargecontrol');
            if(is_object($chargecontrol)) {
              $chargecontrol->setDisplay('icon', '<i class="fa '.$status['ICON'].'"></i>');
              $chargecontrol->save();
            }
            
            // Actualise les commandes de la borne quand la voiture est branchée
            if (in_array($status['ETAT'], array("PAUSED", "CHARGING", "WAITING"))) {
              // status[194] : ['ETAT']:CHARGING / ['ICON']:chargecontrol-icon: fa-stop / ['TEXT']: En charge          
              // status[181] : ['ETAT']:CHARGING / ['ICON']:chargecontrol-icon: rotate / ['TEXT']: En attente          
              
              // Actualise l'icone d'état de la borne
              // $chargecontrol = $eqlogic->getCmd(null, 'chargecontrol');
              // if(is_object($chargecontrol)) {
                // $chargecontrol->setDisplay('icon', '<i class="fa '.$status['ICON'].'"></i>');
                // $chargecontrol->save();
              // }
              
              // Actualise les valeurs des commandes de la borne lors de la charge              
              $eqlogic->checkAndUpdateCmd('chargingtime', $this->secToHHMMSS($info['charging_time'])); // HH:MM:SS (str)
              $eqlogic->checkAndUpdateCmd('energyconsumed', $info['added_energy']); // kwh de la charge (double)
              $eqlogic->checkAndUpdateCmd('energypricetotal', round($info['config_data']['energy_price'] * $info['added_energy'], 2)); // € (double)             
              
              // Affiche les commandes lors de la charge
              $power = $eqlogic->getCmd(null, 'power');
              if(is_object($power)) {
                $power->setIsVisible(1);
                $power->save();
              }
              $chargingtime = $eqlogic->getCmd(null, 'chargingtime');
              if(is_object($chargingtime)) {
                $chargingtime->setIsVisible(1);
                $chargingtime->save();
              }
              $energyconsumed = $eqlogic->getCmd(null, 'energyconsumed');
              if(is_object($energyconsumed)) {
                $energyconsumed->setIsVisible(1);
                $energyconsumed->save();
              }
              $chargecontrol = $eqlogic->getCmd(null, 'chargecontrol');
              if(is_object($chargecontrol)) {
                $chargecontrol->setIsVisible(1);
                $chargecontrol->save();
              }            
              $maxpower = $eqlogic->getCmd(null, 'maxpower');
              if(is_object($maxpower)) {
                $maxpower->setIsVisible(1);
                $maxpower->save();
              }
              $energypricetotal = $eqlogic->getCmd(null, 'energypricetotal');
              if(is_object($energypricetotal)) {
                $energypricetotal->setIsVisible(1);
                $energypricetotal->save();
              }  
            }
            else {
              // La borne est libre, on masque les commandes sur le template du chargeur
              // status[161] = READY / chargecontrol-icon: fa-plug / text: Disponible
              $power = $eqlogic->getCmd(null, 'power');
              if(is_object($power)) {
                $power->setIsVisible(0);
                $power->save();
              }
              $chargingtime = $eqlogic->getCmd(null, 'chargingtime');
              if(is_object($chargingtime)) {
                $chargingtime->setIsVisible(0);
                $chargingtime->save();
              }
              $energyconsumed = $eqlogic->getCmd(null, 'energyconsumed');
              if(is_object($energyconsumed)) {
                $energyconsumed->setIsVisible(0);
                $energyconsumed->save();
              }
              $chargecontrol = $eqlogic->getCmd(null, 'chargecontrol');
              if(is_object($chargecontrol)) {
                $chargecontrol->setIsVisible(0);
                $chargecontrol->save();
              }            
              $maxpower = $eqlogic->getCmd(null, 'maxpower');
              if(is_object($maxpower)) {
                $maxpower->setIsVisible(0);
                $maxpower->save();
              }   
              $energypricetotal = $eqlogic->getCmd(null, 'energypricetotal');
              if(is_object($energypricetotal)) {
                $energypricetotal->setIsVisible(0);
                $energypricetotal->save();
              }            
            }          
          
          
            // Actualise l'icone de la commande d'état de la borne : Verrouiller / Déverrouiller
            $lockcontrol = $eqlogic->getCmd(null, 'lockcontrol');
            if(is_object($lockcontrol)) {            
              if ($info['config_data']['locked'] == 1) {
                $lockcontrol->setDisplay('icon', '<i class="fa fa-lock"></i>');
                log::add('mywallbox', 'debug', 'état de la borne = Verrouiller' );
              }
              elseif ( $status['TEXT'] === "Attente authentification" ) {
                $lockcontrol->setDisplay('icon', '<i class="fa fa-lock"></i>');
                log::add('mywallbox', 'debug', 'état de la borne = Attente authentification' );
              }
              else 
              {
                $lockcontrol->setDisplay('icon', '<i class="fa fa-lock-open"></i>');
                log::add('mywallbox', 'debug', 'état de la borne = Déverrouiller' );
              }        
              $lockcontrol->save();
            }
           
            $maxAparphase = $info['config_data']['max_charging_current'];
            
            $eqlogic->checkAndUpdateCmd('totalchargingTime', $info['data']['chargerData']['resume']['chargingTime']);  // (str)
            $eqlogic->checkAndUpdateCmd('totalpuissance', $info['data']['chargerData']['resume']['totalEnergy']);     // (str)

            // Total de session du mois en cours
            $totalsessions = $eqlogic->getCmd(null, 'totalsessions');
            $totalsessions = is_object($totalsessions) ? $totalsessions->execCmd() : '';

            $totalcout = $eqlogic->getCmd(null, 'totalcout');
            $totalcout = is_object($totalcout) ? floatval($totalcout->execCmd()) : 0;                 
            
            // C'est un nouveau mois on init le cout mensuel avec le cout de la 1ere charge
            if($info['data']['chargerData']['resume']['totalSessions'] == 1) {
              $energypricetotal = is_object($energypricetotal) ? $energypricetotal->execCmd() : 0; 
              if($energypricetotal != $totalcout) {
                log::add('mywallbox', 'debug', 'Nouveau mois, init le cout avec la 1ere charge');
                $eqlogic->checkAndUpdateCmd('totalcout', $energypricetotal); // Coût énergétique total (double)
              }
              else {
                log::add('mywallbox', 'debug', 'totalcout deja init : ' . $energypricetotal);
              }
            }
            
            if(($info['data']['chargerData']['resume']['totalSessions'] != $totalsessions) && ($info['data']['chargerData']['resume']['totalSessions'] > 0)) {
              // Une session de charge vient de finir, on ajout le cout de la charge a celle du mois en cours
              $energypricetotal = is_object($energypricetotal) ? $energypricetotal->execCmd() : 0; 
              $eqlogic->checkAndUpdateCmd('totalcout', $energypricetotal + $totalcout); // Coût énergétique total (double)
              log::add('mywallbox', 'debug', 'totalcout fin session : ' . $totalcout . '+' . $energypricetotal . '=' . ($energypricetotal + $totalcout));
            }
            else {
              log::add('mywallbox', 'debug', 'totalcout (info) : ' . $totalcout);
            }
            $eqlogic->checkAndUpdateCmd('totalsessions', $info['data']['chargerData']['resume']['totalSessions']); // Nombre de recharge (int)
            
            // $eqlogic->checkAndUpdateCmd('totalcout', '11.09'); // Total au mois
            
            log::add('mywallbox','debug','reglage puissance: '.$maxAparphase . '/' . $info['max_available_power'] . ' A');
            log::add('mywallbox','debug','chargerType: ' . $eqlogic->getConfiguration('chargertype'));
            log::add('mywallbox','debug','charging_time: ' . $this->secToHHMMSS($info['charging_time']) . ' => ' . $info['charging_time'] . 's');
            log::add('mywallbox','debug','total Sessions: ' . $info['data']['chargerData']['resume']['totalSessions']);
            log::add('mywallbox','debug','total chargingTime: ' . $this->secToHHMMSS($info['data']['chargerData']['resume']['chargingTime']));
            log::add('mywallbox','debug','total energy: ' . $info['data']['chargerData']['resume']['totalEnergy']);
            log::add('mywallbox','debug','total cout: ' . $totalcout);
            log::add('mywallbox','debug','Coût énergétique: '. $info['config_data']['energy_price'] . ' * ' . $info['added_energy'] . ' = ' . ($info['config_data']['energy_price'] * $info['added_energy']));         

            // Mise à jour des templates
            $eqlogic->emptyCacheWidget();
            // $cache = cache::byKey('mywallboxWidgetmobile' . $eqlogic->getId());
            // $cache->remove();
            $cache_wallboxWidgetdashboard = cache::byKey('mywallboxWidgetdashboard' . $eqlogic->getId());
            $cache_wallboxWidgetdashboard->remove();
            // $eqlogic->toHtml('mobile');
            // $eqlogic->toHtml('dashboard');
            
            $eqlogic->refreshWidget();
          }
          else {
            $tokenhs = $eqlogic->getCmd(null, 'tokenhs');
            $tokenhs = is_object($tokenhs) ? $tokenhs->execCmd() : '';
            if($tokenhs != '') {
              $eqlogic->checkAndUpdateCmd('tokenhs', ($tokenhs+1));
            }            
          }          
          break;

        case 'chargecontrol' :
          $info = $this->getEqLogic()->getChargerStatus();
          $statusid = $info['status_id'];
          $cr_chargecontrol = '';
          log::add('mywallbox', 'debug', 'statusid: ' . $statusid);

          if($statusid == 194)
          {
            // En charge, on met en pause
            $cr_chargecontrol = $this->getEqLogic()->defineChargingState(false);
          }
          else if($statusid == 182)
          {
            // En pause, on met en charge
            $cr_chargecontrol = $this->getEqLogic()->defineChargingState(true);
          }
          else {
            log::add('mywallbox', 'debug', '  chargecontrol inconnue : ' . $statusid);
          }
          log::add('mywallbox', 'debug', '  cr_chargecontrol : ' .  print_r($cr_chargecontrol, true));
          break;

        case 'lockcontrol' :        
          $log_trace = "";  // action qu'on va faire sur le chargeur
          $icon = "";       // icon du verrouillage du chargeur
          $info = $this->getEqLogic()->getChargerStatus();
          
          // recupere l'etat du chargeur actuel
          $status_locked=$info['config_data']['locked'];          
          
          if ($status_locked == 0) {
            $log_trace = "Chargeur déverrouiller, on le verrouiller : ";  
            $icon = 'fa-lock-open';
          }
          elseif ($status_locked == 1) {
            $log_trace = "Chargeur verrouiller, on le déverrouiller : ";
            $icon = 'fa-lock';
          }
          else {
            log::add('mywallbox', 'warning', '***** Vérouillage actuel inconnu [' . $status_locked . ']');
          }

          if($log_trace != "" && $status_locked !== "" && $icon != "") {   
            // On execute la commande sur la borne
            log::add('mywallbox', 'debug', 'status_locked : ' . $status_locked);             
            
            $status_locked = $this->getEqLogic()->defineLockState($status_locked);
            if(($status_locked['ETAT'] == "0") || ($status_locked['ETAT'] == "1")) {
              // On affiche l'etat actuel de la borne
              log::add('mywallbox', 'debug', $log_trace . 'OK'); 

              // Actualise l'icone de la commande d'état de la borne : Verrouiller / Déverrouiller
              $lockcontrol = $this->getEqLogic()->getCmd(null, 'lockcontrol');
              $lockcontrol->setDisplay('icon', '<i class="fa ' . $icon . '"></i>');            
              $lockcontrol->save();

              // log::add('mywallbox', 'debug', 'lockcontrol() FORCE REFRESH'); 
              // $refresh = $eqlogic->getCmd(null, 'refresh');  // on met à jour les infos
              // if(is_object($refresh)) {
                // $refresh->execCmd();
                // $this->getEqLogic()->toHtml();
              // }
            }
            else {
              log::add('mywallbox', 'warning', 'Etat du vérrouillage : ' . $status_locked['ETAT'] . ' -> ' . $status_locked['TEXT']);                   
            }
          }
          else {
            log::add('mywallbox', 'debug', 'Commande non envoyée[' . $status_locked . '] -> log_trace:' . $log_trace . " / status_locked:" . $status_locked . " / icon:" . $icon );   
            throw new Exception($log_trace . 'ECHEC');
          }
          break;

        case 'maxpower' :
          $valPuissanceMaxUser = $_options['slider'];
          log::add('mywallbox', 'info', 'Réglage puissance max : ' . $valPuissanceMaxUser . 'A');
          if ($valPuissanceMaxUser >= 6) {
            $status_MaxAuser = $this->getEqLogic()->defineMaxAmp($valPuissanceMaxUser);
            if($status_MaxAuser['ETAT'] == "SUCCES") {
              $eqlogic->checkAndUpdateCmd('valmaxpower', $valPuissanceMaxUser);   // Max A par phase
            }
            else {
              log::add('mywallbox', 'warning', 'Réglage puissance max : ' . $status_MaxAuser['ETAT'] . ' -> ' . $status_MaxAuser['TEXT']);                   
            }
          }
          else {
            throw new Exception('Valeur de puissance invalide [' . $valPuissanceMaxUser . ']');
          }
          break;
        
        default:
          log::add('mywallbox', 'warning', '***** commande inconnue : '.$this->getLogicalId() . ') *****');  
      }
      log::add('mywallbox', 'info', '***** FIN execute(' . $this->getLogicalId() . ') *****');        
  }


  /*
   * Fonction pour convertir les secondes au format HH:MM:SS
  */
  public function secToHHMMSS($seconds)
  {
    $seconds = round(trim($seconds));
    return sprintf('%02d:%02d:%02d', ($seconds/ 3600),($seconds/ 60 % 60), $seconds% 60);
  }
  
  /*
   * Fonction pour identifier l'état actuel de la borne
  */  
	public function wbGetStatus($status)
	{
    $a_status= array();
		switch ($status)
		{
			case 164:
        $a_status['ETAT']="LOCKED";
        $a_status['ICON']="fa-lock-open";
        $a_status['TEXT']="Attente authentification"; 
        break;      
			case 181:
        $a_status['ETAT']="READY";
        $a_status['ICON']="fa-plug";
        $a_status['TEXT']="Charge terminée";
        break;      
			case 180:
			case 183:
			case 184:
			case 185:
			case 186:
			case 187:
			case 188:
			case 189: 
        $a_status['ETAT']="WAITING";
        $a_status['ICON']="fa-rotate";
        $a_status['TEXT']="En attente";
        break;
			case 193:
			case 194: // En charge de la voiture et borne déverrouiller ou vérrouiller
			case 195: 
        $a_status['ETAT']="CHARGING";
        $a_status['ICON']="fa-stop";
        $a_status['TEXT']="En charge";       
        break;
			case 161: // En attente de la connexion de la voiture et borne déverrouiller 
			case 162: 
        $a_status['ETAT']="READY";
        $a_status['ICON']="fa-plug";
        $a_status['TEXT']="Disponible";       
        break;
			case 178:
			case 182: 
        $a_status['ETAT']="PAUSED";
        $a_status['ICON']="fa-play";
        $a_status['TEXT']="En pause";
        break;
			case 177:
			case 179: 
        $a_status['ETAT']="SCHEDULED";
        $a_status['ICON']="fa-calendar-check";
        $a_status['TEXT']="Programmé";
        break;
			case 196: 
        $a_status['ETAT']="DISCHARGING";
        $a_status['ICON']="fa-battery-quarter";
        $a_status['TEXT']="Décharge";
        break;
			case 14:
			case 15: 
        $a_status['ETAT']="ERROR";
        $a_status['ICON']="fa-bug";
        $a_status['TEXT']="Erreur";
        break;
			case 0:
			case 163: 
        $a_status['ETAT']="DISCONNECTED";
        $a_status['ICON']="fa-plug-circle-xmark";
        $a_status['TEXT']="Déconnecté";
        break;
			case 209: // En attente de la connexion de la voiture et borne verrouillé
			case 210: // Voiture branchée et borne verrouillé (pas en charge)
			case 165: 
        $a_status['ETAT']="LOCKED";
        $a_status['ICON']="fa-lock-open";
        $a_status['TEXT']="Verrouillé";
        break;
			case 166: 
        $a_status['ETAT']="UPDATING";
        $a_status['ICON']="fa-group-arrows-rotate";
        $a_status['TEXT']="Mise à jour";
        break;
			default: 
        $a_status['ETAT']="UNKNOWN";
        $a_status['ICON']="fa-circle-exclamation";
        $a_status['TEXT']="État inconnu";
		}
    $a_status['STATUS_ID']=$status;
    return $a_status;
	}  

   /*     * **********************Getteur Setteur*************************** */
}