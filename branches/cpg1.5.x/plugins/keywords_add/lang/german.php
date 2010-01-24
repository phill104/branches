<?php
/**************************************************
  Coppermine 1.5.x Plugin - keywords_add
  *************************************************
  Copyright (c) coppermine dev team
  *************************************************
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.
  ********************************************
  $HeadURL: https://coppermine.svn.sourceforge.net/svnroot/coppermine/branches/cpg1.5.x/plugins/mirror/codebase.php $
  $Revision: 7039 $
  $LastChangedBy: gaugau $
  $Date: 2010-01-11 09:55:27 +0100 (Mo, 11. Jan 2010) $
  **************************************************/

if (!defined('IN_COPPERMINE')) { die('Not in Coppermine...'); }

$lang_plugin_keywords_add = array(
  'display_name'  => 'Keywords add',			// Display Name
  'config_title'  => 'Verwendung von Keywords add',			// Title of the button on the gallery config menu
  'config_button' => 'Keywords add',				// Label of the button on the gallery config menu
  'install_note'  => 'Um das Plugin zu verwenden nutze den Button in der Admin Toolbar.',	// Note about configuring plugin
  'install_click' => 'Klicke den Button um das PlugIn zu installieren.',	// Message to install plugin
  'version'       => 'Ver 1.1',
  'album_name'    => 'W&auml;hle das Album aus, in welches Du die Stichworte hinzuf&uuml;gen willst',
  'add_info'      => 'Stichworte hinzuf&uuml;gen',
  'keyword'	  => 'Stichworte',
  'caution'       => 'Vorsicht: Bei Titel, Beschreibung und benutzerdefinierten Feldern werden Informationen, welche bereits eingegeben wurden, <br> 
  durch die hier eingegebenen neuen Informationen ersetzt.<br>
  Diese Felder deshalb frei lassen, um den bestehenden Inhalt zu erhalten.',
  'title'	    => 'Titel',
  'description'      => 'Beschreibung',
);

$lang_plugin_keywords_add_config = array(
  'status'        => 'Status des Plugins',
  'button_install'=> 'Installation',
  'button_submit' => 'Absenden',
);

// Delete
$lang_plugin_keywords_add_delete= array(
  'success'       => 'Die Verwendung von Keywords add war erfolgreich',
 );
?>