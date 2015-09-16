<?php
/**************************************************
  Coppermine 1.5.x plugin - hidden_features
  *************************************************
  Copyright (c) 2014 eenemeenemuu
  *************************************************
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.
  ********************************************
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**************************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

define('ADMIN_PHP', true);

$thisplugin->add_action('page_start', 'hidden_features_page_start');
function hidden_features_page_start() {
    global $CONFIG, $lang_plugin_hidden_features;

    require_once "./plugins/hidden_features/lang/english.php";
    if ($CONFIG['lang'] != 'english' && file_exists("./plugins/hidden_features/lang/{$CONFIG['lang']}.php")) {
        require_once "./plugins/hidden_features/lang/{$CONFIG['lang']}.php";
    }
}


function hidden_features_only_empty_albums_button() {
    global $CONFIG, $CPG_PHP_SELF, $lang_plugin_hidden_features;
    $superCage = Inspekt::makeSuperCage();

    if ($CONFIG['only_empty_albums'] == 1 || ($CONFIG['only_empty_albums'] == 2 && GALLERY_ADMIN_MODE)) {
        $sep = strpos($superCage->server->getRaw('REQUEST_URI'), '?') ? '&amp;' : '?';
        if ($superCage->get->keyExists('only_empty_albums')) {
            $only_empty_albums = '<a href="'.preg_replace('/[\?&]only_empty_albums/', '', $superCage->server->getRaw('REQUEST_URI')).'" class="button">'.$lang_plugin_hidden_features['only_empty_albums_button_all'].'</a>';
        } else {
            $only_empty_albums = '<a href="'.$superCage->server->getRaw('REQUEST_URI').$sep.'only_empty_albums" class="button">'.$lang_plugin_hidden_features['only_empty_albums_button_empty'].'</a>';
        }
    } else {
        $only_empty_albums = '';
    }

    return $only_empty_albums;
}


?>
