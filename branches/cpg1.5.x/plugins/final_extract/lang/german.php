﻿<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2005 Coppermine Dev Team
  v1.1 originaly written by Gregory DEMAR

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  ********************************************
  Coppermine version: 1.4.8
  $Source: /cvsroot/cpg-contrib/master_template/codebase.php,v $
  $Revision: 1.3 $
  $Author: donnoman $
  $Date: 2005/12/08 05:46:49 $
**********************************************/
/**********************************************
Modified By BMossavari 
- Add menu option to remove each part
- Add config page
**********************************************/
/**********************************************
German translation by AlexL
**********************************************/

if (!defined('IN_COPPERMINE')) { die('Not in Coppermine...'); }

$lang_plugin_final_extract = array(
  'display_name'  => 'Final Extract',			// Display Name
  'config_title'  => 'Konfiguration von Final Extract',			// Title of the button on the gallery config menu
  'config_button' => 'Final Extract',				// Label of the button on the gallery config menu
  'page_success'  => 'Konfigurationseinstellungen ge&auml;ndert.',		// Page success message
  'page_failure'  => 'Unm&ouml;glich diese Einstellungen zu &auml;ndern.',		// Page failure message
  'install_note'  => 'Zum Konfigurieren des PlugIns nutze den Button in der Admintoolbar.',	// Note about configuring plugin
  'install_click' => 'Klicke den Button um das PlugIn zu installieren.',	// Message to install plugin
  'version'       => 'Ver 2.3',
  'group_name'      => 'W&auml;hle die Nutzergruppe',
  'home_block'      => 'Home',
  'login_block'     => 'Login',
  'my_galery_block' =>'Meine Galerie',
  'upload_pic_block'=>'Datei hochladen',
  'album_list_block'=>'Alben',
  'lastup_block'    =>'zuletzt hinzugef&uuml;gt',
  'lastcom_block'   =>'Letzte Kommentare',
  'topn_block'      =>'Oft angesehen',
  'toprated_block'  =>'Am besten bewertet',
  'favpics_block'   =>'Meine Favoriten',
  'search_block'    =>'Suche',
);

$lang_plugin_final_extract_config = array(
  'status'        => 'Status des PlugIns',
  'button_install'=> 'Installation',
  'button_submit' => 'Senden',
  'button_cancel' => 'Abbrechen',
  'button_done'   => 'Erledigt',
  'cleanup_question' => 'Entferne die Tabelle, welche die Einstellungen enth&auml;lt?',
  'expand_all'    => 'Erweitere Alle',
);
// Banner Management
$lang_plugin_final_extract_manage= array(
	'list_name'   => 'Name',
	'list_submit' => 'Sichere die neue Konfiguration',
	'list_restore'=> 'Standards wiederherstellen',
	'list_stat'   => 'Entfernen', 
	'list_chstat' => 'Sicher die &auml;nderungen',
	'list_chkall' => 'Pr&uuml;fe alle', // CPA 1.2.2
	'list_check'  => 'Auswahl',
);
// Delete
$lang_plugin_final_extract_delete= array(
  'nothing_do'    => 'Da ist nichts zu erledigen!',
  'nothing_changed' => 'Alle Bl&ouml;cke sind aktiv ...',
  'success'       => 'Final Extract Konfiguration erfolgreich',
 );
?>