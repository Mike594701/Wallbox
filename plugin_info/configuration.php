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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
$username = config::byKey("username", "monwallbox");
$password = config::byKey("password", "monwallbox");
$pluginVersion = monwallbox::GetVersionPlugin();
if($username == null || $password == null){
  ?>
  <p style="color:red;text-align: center;font-weight: bold;">{{Le nom d'utilisateur et le mot de passe sont requis pour le plugin.}}</p>
  <?php 
} ?>
<form class="form-horizontal">
  <fieldset>
    <legend><i class="fas fa-key"></i>{{Identifiant du compte monwallbox :}}</legend>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Nom d'utilisateur}}</label>
      <div class="col-sm-7">
        <input class="configKey form-control" data-l1key="username" required/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Mot de passe}}</label>
      <div class="col-sm-7">
        <input class="configKey form-control" data-l1key="password" type="password" required/>
      </div>
    </div>
  </fieldset>
</form>

<script>
// Surcharge le panel ETAT du core de JEEDOM pour mettre le n° de version du plugin
$(document).ready(function () {
  var divState = document.getElementById("div_state");

  var secondForm = divState.querySelectorAll("form.form-horizontal")[1]; // 1 pour sélectionner le deuxième formulaire le INFO PLUGIN

  // Créer un nouvel élément div avec la classe "form-group"
  var newFormGroup = document.createElement("div");
  newFormGroup.className = "form-group";

  // Créer les éléments à l'intérieur de la nouvelle ligne form-group
  var label1 = document.createElement("label");
  label1.className = "col-sm-2 control-label";
  label1.textContent = "";

  var div1 = document.createElement("div");
  div1.className = "col-sm-4";
  var span1 = document.createElement("span");
  span1.textContent = "";
  div1.appendChild(span1);

  var label2 = document.createElement("label");
  label2.className = "col-sm-2 control-label";
  label2.textContent = "Version plugin";

  var div2 = document.createElement("div");
  div2.className = "col-sm-4";
  var span2 = document.createElement("span");
  span2.textContent = "<?=$pluginVersion;?>";
  div2.appendChild(span2);

  // Ajouter les éléments créés à l'intérieur de la nouvelle ligne form-group
  newFormGroup.appendChild(label1);
  newFormGroup.appendChild(div1);
  newFormGroup.appendChild(label2);
  newFormGroup.appendChild(div2);

  // Ecrit la ligne à la fin de l'ETAT
  secondForm.appendChild(newFormGroup);
});
</script>
