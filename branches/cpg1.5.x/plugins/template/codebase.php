<?php
/**************************************************
  Coppermine 1.5.x Plugin - template
**************************************************/

// Make sure that this file can't be accessed directly, but only from within the Coppermine user interface
if (!defined('IN_COPPERMINE')) {
    die('Not in Coppermine...');
}

// Define plugin actions here
$thisplugin->add_action('plugin_install','template_install');
$thisplugin->add_action('plugin_uninstall','template_uninstall');

// Define plugin filters here

// Define the plugin's functions here
function template_install() {
    global $CONFIG;
	// Add the config options for the plugin	    return true;
}

function template_uninstall() {
    global $CONFIG, $lang_errors;
	$superCage = Inspekt::makeSuperCage();
    if (!checkFormToken()) {
        cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
    }
    // Delete the plugin config records	    return true;
}
?>