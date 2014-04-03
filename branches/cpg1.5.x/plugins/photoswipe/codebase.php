<?php
/**************************************************
  Coppermine 1.5.x plugin - photoswipe
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

$thisplugin->add_action('page_start','photoswipe_page_start');
function photoswipe_page_start() {
    global $CONFIG, $lang_plugin_photoswipe;

    require_once "./plugins/photoswipe/lang/english.php";
    if ($CONFIG['lang'] != 'english' && file_exists("./plugins/photoswipe/lang/{$CONFIG['lang']}.php")) {
        require_once "./plugins/photoswipe/lang/{$CONFIG['lang']}.php";
    }

    global $JS;
    $JS['includes'][] = 'plugins/photoswipe/js/simple-inheritance.min.js';
    $JS['includes'][] = 'plugins/photoswipe/js/code-photoswipe-1.0.11.min.js';
}

if (defined('THUMBNAILS_PHP')) {
    $thisplugin->add_action('theme_thumbnails_wrapper_start','photoswipe_theme_thumbnails_wrapper_start');
    $thisplugin->add_action('theme_thumbnails_wrapper_end','photoswipe_theme_thumbnails_wrapper_end');
    $thisplugin->add_filter('theme_display_thumbnails_params','photoswipe_theme_display_thumbnails_params');
    $thisplugin->add_filter('page_html','photoswipe_page_html');
}

function photoswipe_theme_thumbnails_wrapper_start() {
    echo '<script type="text/javascript">document.addEventListener("DOMContentLoaded", function() { Code.photoSwipe("a", "#Gallery"); }, false);</script>';
    echo '<div id="Gallery">';
}

function photoswipe_theme_thumbnails_wrapper_end() {
    echo '</div>';
}

function photoswipe_theme_display_thumbnails_params($params) {
    if (preg_match('/<img.*src="(.*)"/U', $params['{THUMB}'], $matches)) {
        global $CONFIG;
        $path_parts = pathinfo($matches[1]);
        $image = $path_parts['dirname'].'/'.preg_replace('/^'.$CONFIG['thumb_pfx'].'/', '', $path_parts['basename']);
        $params['{LINK_TGT}'] = '#photoswipe_removeme';
        $params['{THUMB}'] = '<a href="'.$image.'">'.$params['{THUMB}'].'</a>';
        $params['{CAPTION}'] = preg_replace('/<a.*>(.*)<\/a>/U', '\\1', $params['{CAPTION}']);
    }
    return $params;
}

function photoswipe_page_html($html) {
    $html = preg_replace('/<a href="#photoswipe_removeme">(.*<br \/>)<\/a>/U', '\\1', $html);
    return $html;
}

?>