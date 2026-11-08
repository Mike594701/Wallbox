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

// Fonction exécutée automatiquement à l'installation du plugin
  function wallbox_install() {
    log::add('wallbox', 'debug', 'wallbox_install() :: vide');
  }

// Fonction exécutée automatiquement après la mise à jour du plugin
  function wallbox_update() {
    log::add('wallbox', 'debug', 'wallbox_update()');

    // Mise à jour du numero de version du plugin dans la base Jeedom
    $pluginVersion = wallbox::SetVersionPlugin(); 
  }

// Fonction exécutée automatiquement après la suppression du plugin
  function wallbox_remove() {
    log::add('wallbox', 'debug', 'wallbox_remove() :: vide');
  }

?>
