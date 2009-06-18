<?php
/**************************
  Plugin "External Edit"
  *************************
  Copyright (c) Joachim Müller
  For Coppermine Photo Gallery cpg1.5.x

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 3
  as published by the Free Software Foundation.
    
  ********************************************
  $HeadURL: https://coppermine.svn.sourceforge.net/svnroot/coppermine/trunk/cpg1.5.x/banning.php $
  $Revision: 6131 $
  $LastChangedBy: gaugau $
  $Date: 2009-06-10 08:42:56 +0200 (Mi, 10 Jun 2009) $


**********************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

function external_edit_init() {
	global $CONFIG, $lang_plugin_external_edit, $external_edit_icon_array;
	require "./plugins/external_edit/lang/english.php";
	if ($CONFIG['lang'] != 'english' && file_exists("./plugins/external_edit/lang/{$CONFIG['lang']}.php")) {
	    require "./plugins/external_edit/lang/{$CONFIG['lang']}.php";
	}
	if ($CONFIG['enable_menu_icons'] > 0) {
		$external_edit_icon_array['announcement'] = '<img src="./plugins/external_edit/images/icons/announcement.png" width="16" height="16" border="0" alt="" class="icon" />';
		$external_edit_icon_array['fotoflexer'] = '<img src="./plugins/external_edit/images/icons/fotoflexer.png" width="16" height="16" border="0" alt="" class="icon" />';
		 
	} else {
		$external_edit_icon_array['announcement'] = '';
		$external_edit_icon_array['fotoflexer'] = '';
	}
	$external_edit_icon_array['ok'] = cpg_fetch_icon('ok',2);
	$external_edit_icon_array['cancel'] = cpg_fetch_icon('cancel',2);
	$external_edit_icon_array['none'] = cpg_fetch_icon('cancel',2);
	$external_edit_icon_array['ignore'] = cpg_fetch_icon('ignore',2);
	$return['language'] = $lang_plugin_external_edit;
	$return['icon'] = $external_edit_icon_array;
	return $return;
}
?>