<?php
/**************************************************
  Coppermine 1.5.x Plugin - template
**************************************************/

// Make sure that this file can't be accessed directly, but only from within the Coppermine user interfaceif (!defined('IN_COPPERMINE')) {
    die('Not in Coppermine...');
}

// Strings used on the plugin manager screen
$lang_plugin_template['plugin_name'] = 'Template Plugin'; // Your plugin's long name here
$lang_plugin_template['plugin_description'] = 'This is a plugin template'; // Describe what your plugin does here
$lang_plugin_template['install_info'] = 'Install me'; // Before the plugin is installed, this line will show in the plugin manager as additional information
$lang_plugin_template['extra_info'] = ''; // Extra information that shows up in the plugin manager after the plugin is installed
$lang_plugin_template['configure_x'] = 'Configure %s'; // The %s will be replaced with the name of your plugin, so you usually don't have to edit this line

// Strings used on the plugin's configuration panel (admin.php)
$lang_plugin_template['update_success'] = 'Plugin config updated';
$lang_plugin_template['no_changes'] = 'No changes needed (or changes invalid)';
$lang_plugin_template['submit'] = 'Submit';
?>