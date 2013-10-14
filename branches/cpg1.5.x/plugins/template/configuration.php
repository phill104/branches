<?php
/**************************************************
  Coppermine 1.5.x Plugin - template
**************************************************/

// Make sure that this file can't be accessed directly, but only from within the Coppermine user interfaceif (!defined('IN_COPPERMINE')) {
    die('Not in Coppermine...');
}

require "./plugins/template/lang/english.php";
if ($CONFIG['lang'] != 'english' && file_exists("./plugins/template/lang/{$CONFIG['lang']}.php")) {
    require "./plugins/template/lang/{$CONFIG['lang']}.php";
}

$name = $lang_plugin_template['plugin_name'];
$description = $lang_plugin_template['plugin_description'];
$author = 'Your name here';
$version = '1.0';
$plugin_cpg_version = array('min' => '1.5');
$install_info = $lang_plugin_template['install_info'];
$extra_info = $lang_plugin_template['extra_info'];
$extra_info .= '<br /><a href="index.php?file=template/admin" class="admin_menu">' . sprintf($lang_plugin_template['configure_x'], $lang_plugin_template['plugin_name']) . '</a>';
?>