<?php
/***********************************
  Coppermine reCAPTCHA plugin v2.0
  **********************************
  By: SaWey - Updated by Joe Carver
  Date: 2010-11-10
**********************************************/

if (!defined('IN_COPPERMINE')) {
    die('Not in Coppermine...');
}

// Define the default language array (English)
require_once ("./plugins/recaptcha/lang/english.php");
// submit your lang file for this plugin on the coppermine forums
// plugin will try to use the configured language if it is available.
if (file_exists("./plugins/recaptcha/lang/{$CONFIG['lang']}.php")) {
    require_once ("./plugins/recaptcha/lang/{$CONFIG['lang']}.php");
}

// Determine the help file link
if (file_exists("./plugins/recaptcha/docs/{$CONFIG['lang']}.htm")) {
    $documentation_file = $CONFIG['lang'];
} else {
    $documentation_file = 'english';
}

$recaptcha_icon_array['ok'] = cpg_fetch_icon('ok', 0);
$recaptcha_icon_array['announcement'] = cpg_fetch_icon('announcement', 1);
$recaptcha_icon_array['documentation'] = cpg_fetch_icon('documentation', 1);
$recaptcha_icon_array['configure'] = cpg_fetch_icon('config', 1)
?>