<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

// Crée le répertoire s'il n'existe pas déjà
function mkdir_recursive($path)
{
  if (!is_dir($path)) {
    mkdir($path, 0777, true);
  }
}

// Déclaration des variables obligatoires
$plugin = plugin::byId('mywallbox');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
$pluginInit = true;
$deamon_info = 0;

// Plugin actif ?
$pluginActif = $plugin->isActive();
if ($pluginActif == 1) {
  $username = config::byKey("username", "mywallbox");
  $password = config::byKey("password", "mywallbox");
  if($username == null || $password == null){
    echo '<p class="h5 danger" style="font-weight:bold;">{{Le nom d\'utilisateur et le mot de passe de votre compte myWallbox sont requis pour le plugin}}</p>';
  }
}
else {
  echo '<p class="h5 danger" style="font-weight:bold;">{{Le plugin n\'est pas activé.}}</p>';
}

// Liste des équipements wallbox du plugin
if(count($eqLogics) != 0) {
  $pluginInit = false;
	if($username != null || $password != null){
    $liste_chargers = $eqLogics[0]->getChargerList();
    $deamon_info = $eqLogics[0]->getIsEnable();

    // test si l'identification a fonctionné et que la liste des wallbox sont disponibles
    if(isset($liste_chargers['ERREUR'])) {
      echo '<p class="h5 danger" style="font-weight:bold;">{{'. $liste_chargers['ERREUR'] .'}}, vérifier vos identifiants de connexion myWallbox</p>';
      $liste_chargers = [];
    }    
  }
  else{
    log::add('mywallbox', 'error', 'Le plugin n\'est pas configuré avec le nom d\'utilisateur et le mot de passe du compte MyWallbox.' );
    $liste_chargers = [];
  }
}
else {
  // Init le plugin
  $liste_chargers = [];
}

if ($deamon_info == 1 ) { 
  $deamon_info = array("texte"=>"Rafraîchissement actif", "icon"=>'<i class="fas fa-sync-alt logoPrimary nocursor"></i>');  
}
else {
  $deamon_info = array("texte"=>"Rafraîchissement désactivé", "icon"=>'<i class="fas fa-ban warning nocursor"></i>');
}

// Version du plugin
$pluginVersion = mywallbox::GetVersionPlugin();
if ($pluginVersion !== 'introuvable') {
  $pluginVersion = 'v' . $pluginVersion;
}

?>

<style>
.txt-color-goldenrod {
  color: goldenrod;
}
.mr-10 {
  margin-right: 10px;
}
</style>

<script type="text/javascript">
	// Evenenemnt lors de la selection de la borne, on charge la photo correspondante à la wallbox
	$(function() {
		$('#chargerselect').change(function(){
			$("img[id^='img-']").hide();
			$imguri = $('#img-'+this.value).attr('src');
      if(typeof $imguri !== "undefined") {
        // icone de la borne
        $('#img-'+this.value).show();
        $('#chargeruri').val($imguri);
      }
      else {
        // icone par defaut pour la borne
        $("#img-defaut").show();
      }
		});
	});
</script>

<div class="row row-overflow">
  <!-- Page d'accueil du plugin -->
  <div class="col-xs-12 eqLogicThumbnailDisplay">
    <div class="floatingbar">
      <div class="input-group">
        <span class="input-group-btn" id="span_right_button">
          <a class="btn btn-sm roundedLeft bt_refreshPluginInfo"><i class="fas fa-info mr-10"></i><?=$pluginVersion;?></a>
        </span>
      </div>
    </div>  
    <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
    <!-- Boutons de gestion du plugin -->
    <div class="eqLogicThumbnailContainer">
      <?php 
        if (($pluginActif == 1) && ($username != null) && ($password != null) && ((count($liste_chargers) > 0) || ($pluginInit))){
      ?>          
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle"></i>
        <br>
        <span>{{Ajouter Wallbox}}</span>
      </div>
      <?php 
        }
      ?>          
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br>
        <span>{{Configuration}}</span>
      </div>      
      
      <?php
      if($pluginActif == 1) {
      ?>
      <div class="cursor col-xs-offset-1 nocursor">
        <?php echo $deamon_info['icon'];?>
        <br>
        <span class="nocursor">{{<?php echo $deamon_info['texte'];?>}}</span>
      </div>
    <?php 
      }
    ?>        
      <div class="cursor eqLogicAction logoSecondary" data-action="openLocationBlank" data-location="https://community.jeedom.com/tag/plugin-<?= $plugin->getId() ?>">
        <i class="fas fa-comments logoPrimary"></i>
        <br>
        <span>{{Community}}</span>
        <span>myWallbox</span>
      </div>   
    </div>
    
    <legend><i class="fas fa-table"></i> {{Mes équipements Wallbox}}</legend>
    <!-- Champ de recherche -->
    <!--
    <div class="input-group" style="margin:5px;">
      <input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>
      <div class="input-group-btn">
        <a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
      </div>
    </div>
    -->
    <!-- Liste des équipements du plugin -->
    <div class="eqLogicThumbnailContainer">
      <?php
      // print_r($liste_chargers);
      if(count($liste_chargers) > 0) {
        foreach ($eqLogics as $eqLogic) {
          $imgWallBox = '';
          $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
          echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
          foreach ($liste_chargers as $chargeur) {
            if($eqLogic->getConfiguration('chargerid') == $chargeur['id']) {
              if ($chargeur['image'] != '') {
                $imgWallBox = $chargeur['image'];
              }
              break;
            }
          }
          if($imgWallBox) {
            // Telecharge l'image car la "Content Security Policy" de Jeedom bloque sur certaine version
            // Création du répertoire de stockage pour les images Wallbox
            $imgwallboxDir = dirname(__FILE__) . '/../../core/images_wallbox';
            mkdir_recursive($imgwallboxDir);
            
            $fichierImgwallboxDir = $imgwallboxDir . '/' . basename($chargeur['image']);
            
            if ( ! file_exists($fichierImgwallboxDir)) {
              // Telecharge l'image de la Wallbox depuis le site Wallbox
              $image_api = file_get_contents($chargeur['image']);
              
              // Enregistrer l'image dans : mywallbox/core/images_wallbox/
              file_put_contents($fichierImgwallboxDir, $image_api);
              if ( ! file_exists($fichierImgwallboxDir)) {              
                $fichierImgwallboxDir = $plugin->getPathImgIcon(); // image de la Wallbox introuvable
              }
              else {
                $fichierImgwallboxDir = 'plugins/mywallbox/core/images_wallbox/' . basename($chargeur['image']);
              }
            }
            else {
              $fichierImgwallboxDir = 'plugins/mywallbox/core/images_wallbox/' . basename($chargeur['image']);              
            }
            echo '<img src="' . $fichierImgwallboxDir . '"/>';
          }
          else {
            echo '<img src="' . $plugin->getPathImgIcon() . '"/>'; // image de la Wallbox introuvable
          }
          echo '<br>';
          echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
          echo '</div>';
        }
      }
      elseif (($pluginActif == 1) && ($username != null) && ($password != null) && ((count($liste_chargers) > 0) || ($pluginInit))){
        echo '<p class="h5 logoPrimary" style="font-weight:bold; padding-left:20px;">{{Ajouter une Wallbox pour utiliser le plugin}}</p>';        
      }
      ?>
    </div>
  </div>
  <!-- /.eqLogicThumbnailDisplay -->
  
<?php
if(count($liste_chargers) > 0) {
?>
  <!-- Page de présentation de l'équipement -->
  <div class="col-xs-12 eqLogic" style="display: none;">
    <!-- barre de gestion de l'équipement -->
    <div class="input-group pull-right" style="display:inline-flex;">
      <span class="input-group-btn">
        <!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
        <a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
        </a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs">  {{Dupliquer}}</span>
        </a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
        </a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
        </a>
      </span>
    </div>

    <!-- Onglets -->
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i><span class="hidden-xs"> {{Équipement}}</span></a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i><span class="hidden-xs"> {{Commandes}}</span></a></li>
    </ul>

    <div class="tab-content">
      <!-- Onglet de configuration de l'équipement -->
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <!-- Partie gauche de l'onglet "Equipements" -->
        <!-- Paramètres généraux de l'équipement -->
        <form class="form-horizontal">
          <fieldset>
            <!-- Partie gauche de l'onglet "Équipement" -->
            <div class="col-lg-7">
              <!-- Section "Général" -->
              <legend><i class="fas fa-wrench"></i> {{Général}}</legend>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
                <div class="col-sm-7">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;"/>
                  <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                <div class="col-sm-7">
                  <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                    <option value="">{{Aucun}}</option>
                    <?php
                      $options = '';
                      foreach ((jeeObject::buildTree(null, false)) as $object) {
                        $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                      }
                      echo $options;
                      ?>
                  </select>
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
                <label class="col-sm-3 control-label">{{Options}}</label>
                <div class="col-sm-7">
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                </div>
              </div>

              <br>
              <!-- Section "Paramètres" -->
              <legend><i class="fas fa-cogs"></i> {{Paramètres}}</legend>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Chargeur}}</label>
                <div class="col-sm-7">
                  <select class="form-control eqLogicAttr" id="chargerselect" data-l1key="configuration" data-l2key="chargerid" >
                  <option value="">{{Aucun}}</option>
                  <?php
                    foreach ($liste_chargers as $chargeur) {
                      echo '<option value="'.$chargeur['id'].'">'.$chargeur['name'].'</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
              <!-- Champ de saisie du cron d'auto-actualisation + assistant cron -->
              <!-- La fonction cron de la classe du plugin doit contenir le code prévu pour que ce champ soit fonctionnel -->
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Auto-actualisation}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Fréquence de rafraîchissement automatique de l'équipement}}"></i></sup>
                </label>
                <div class="col-sm-7">
                  <div class="input-group">
                    <input type="text" class="eqLogicAttr form-control roundedLeft" data-l1key="configuration" value="*/5 * * * *" data-l2key="autorefresh" placeholder="{{Cliquer sur ? pour afficher l'assistant cron}}"/>
                    <span class="input-group-btn">
                    <a class="btn btn-default cursor jeeHelper roundedRight" data-helper="cron" title="Assistant cron">
                    <i class="fas fa-question-circle"></i>
                    </a>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Partie droite de l'onglet "Équipement" -->
            <div class="col-lg-5">
              <!-- Section "Template de Widget" -->
              <legend><i class="fas fa-tablet-alt"></i> {{Template de Widget}}</legend>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Activer}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case pour utiliser le template du widget représentant la Wallbox}}"></i></sup>
                </label>
                <div class="col-sm-2">
                  <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="widgetTemplate">
                </div>
              </div>

              <br>

              <!-- Section "Informations" -->
              <legend><i class="fas fa-info"></i> {{Informations}}</legend>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Modèle}}</label>
                <div class="col-sm-2">
                  <span class="wallbox label label-info" data-l1key="configuration" data-l2key="chargertype"></span>
                </div>
                <label class="col-sm-3 control-label">{{N° série}}</label>
                <div class="col-sm-2">
                  <span class="wallbox label label-info" data-l1key="configuration" data-l2key="chargerid"></span>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Référence}}</label>
                <div class="col-sm-7">
                  <span class="wallbox label label-info" data-l1key="configuration" data-l2key="part_number"></span>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Version}}</label>
                <div class="col-sm-7">
                  <span class="wallbox label label-info" data-l1key="configuration" data-l2key="software_currentVersion"></span>
                  <span id="software_disponible" class="hidden">
                    <label class="control-label txt-color-goldenrod" style="margin-left: 20px;"><i class="fas fa-upload"></i> {{Version disponible}}</label> 
                    <span class="wallbox label label-info" data-l1key="configuration" data-l2key="software_latestVersion"></span>
                    <sup><i class="fas fa-question-circle tooltips" title="{{Utiliser l'application du constructeur pour faire la mise à jour}}"></i></sup>
                  </span>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Date installation}}</label>
                <div class="col-sm-7">
                  <span class="wallbox label label-info" data-l1key="configuration" data-l2key="createtime"></span>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Signal WiFi}}</label>
                <div class="col-sm-7">
                  <span class="wallbox label label-info" data-l1key="configuration" data-l2key="signalwifi"></span>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Dernière communication}}</label>
                <div class="col-sm-7">
                  <span class="wallbox label label-info" data-l1key="configuration" data-l2key="lastCommunication"></span>
                </div>
              </div>
              <div class="form-group">
                <input type="hidden" id="chargeruri" />
                <div class="text-center">
									<img name="icon_visu_defaut" id="img-defaut" src="<?= $plugin->getPathImgIcon(); ?>" style="max-width:160px;"/>
                  <?php
                    foreach ($liste_chargers as $chargeur) {
                      if($chargeur['image'] != "undefined") {
                        $fichierImgwallboxDir = 'plugins/mywallbox/core/images_wallbox/' . basename($chargeur['image']);              
                        echo '<img name="icon_visu" alt="'.$chargeur['id'].'" id="img-'.$chargeur['id'].'" src="'.$fichierImgwallboxDir.'" style="max-width:200px; display : none;" />';
                      }
                    }
                  ?>
                </div>
              </div>
            </div>
          </fieldset>
        </form>
        <hr>
      </div>
      <!-- /.tabpanel #eqlogictab-->
      <!-- Onglet des commandes de l'équipement -->
      <div role="tabpanel" class="tab-pane" id="commandtab">
        <a class="btn btn-default btn-sm pull-right cmdAction" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a>
        <br/><br/>
        <div class="table-responsive">
          <table id="table_cmd" class="table table-bordered table-condensed">
            <thead>
              <tr>
                <th>{{Id}}</th>
                <th>{{Nom}}</th>
                <!--<th>{{Type}}</th>-->
                <th></th>
                <th>{{Options}}</th>
                <th>{{Etat}}</th>
                <th>{{Action}}</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
      <!-- /.tabpanel #commandtab-->
    </div>
    <!-- /.tab-content -->
  </div>
  <!-- /.eqLogic -->
</div>
<!-- /.row row-overflow -->
<?php
}
?>
<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', $plugin->getId(), 'js', $plugin->getId());?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js');?>
