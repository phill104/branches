<?php
/**************************************************
  Coppermine 1.5.x plugin - fit_screen
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

$thisplugin->add_action('page_start','fit_screen_page_start');
function fit_screen_page_start() {
    global $CONFIG, $lang_plugin_fit_screen;

    require_once "./plugins/fit_screen/lang/english.php";
    if ($CONFIG['lang'] != 'english' && file_exists("./plugins/fit_screen/lang/{$CONFIG['lang']}.php")) {
        require_once "./plugins/fit_screen/lang/{$CONFIG['lang']}.php";
    }
}

if (defined('DISPLAYIMAGE_PHP')) {
    $thisplugin->add_filter('page_meta','fit_screen_page_meta');
    $thisplugin->add_filter('html_image','fit_screen_html_image');
    $thisplugin->add_filter('html_image_reduced','fit_screen_html_image');
}

function fit_screen_page_meta($meta) {
    global $JS;
    $JS['includes'][] = 'plugins/fit_screen/resize.js';
    $meta  .= '<link rel="stylesheet" href="plugins/fit_screen/style.css" type="text/css" />';
    return $meta;
}

function fit_screen_html_image($pic_html) {
    $pic_html = preg_replace('/<a.*>/Ui', '', $pic_html);
    $pic_html = str_replace('</a>', '', $pic_html);
    $pic_html = str_replace('<img ', '<img id="thepic" onLoad="scaleImg();" onClick="showOnclick();" ', $pic_html);
    return $pic_html;
}

?>