<?php
/**************************************************
  Picture Annotation (annotate) plugin for cpg1.5.x
  *************************************************
  Copyright (c) 2003-2009 Coppermine Dev Team

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 3
  as published by the Free Software Foundation.

  *************************************************
  Coppermine version: 1.5.x
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**************************************************/

if (!defined('IN_COPPERMINE')) {
	die('Not in Coppermine...');
}

function annotate_initialize() {
	global $CONFIG, $JS, $lang_plugin_annotate, $annotate_icon_array;
	require_once "./plugins/annotate/lang/english.php";
	if ($CONFIG['lang'] != 'english' && file_exists("./plugins/annotate/lang/{$CONFIG['lang']}.php")) {
	    require_once "./plugins/annotate/lang/{$CONFIG['lang']}.php";
	}
	
	if ($CONFIG['enable_menu_icons'] >= 1) {
	    $annotate_icon_array['announcement'] = '<img src="./plugins/annotate/images/icons/announcement.png" border="0" width="16" height="16" alt="" class="icon" />';
	    $annotate_icon_array['configure'] = '<img src="./plugins/annotate/images/icons/configure.png" border="0" width="16" height="16" alt="" class="icon" />';
	} else {
	    $annotate_icon_array['announcement'] = '';
	    $annotate_icon_array['configure'] = '';
	}
	if ($CONFIG['enable_menu_icons'] == 2) {
	    $annotate_icon_array['annotate'] = '<img src="./plugins/annotate/images/icons/annotate.png" border="0" width="16" height="16" alt="" class="icon" />';
	} else {
	    $annotate_icon_array['annotate'] = '';
	}
	$annotate_icon_array['ok'] = cpg_fetch_icon('ok', 2, '', '', 'png', '1');
	$annotate_icon_array['cancel'] = cpg_fetch_icon('cancel', 2, '', '', 'png', '1');
	$annotate_icon_array['delete'] = cpg_fetch_icon('delete', 2, '', '', 'png', '1');
	$return['language'] = $lang_plugin_annotate;
	$return['icon'] = $annotate_icon_array;
	return $return;
}

?>