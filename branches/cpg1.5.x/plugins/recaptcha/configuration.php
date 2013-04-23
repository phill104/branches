<?php
/***********************************
  Coppermine reCAPTCHA plugin v2.0
  **********************************
  By: SaWey - Updated by Joe Carver
  Date: 2010-11-10
**********************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

include_once('plugins/recaptcha/recaptchalib.php');
require_once('plugins/recaptcha/init.inc.php');

$superCage = Inspekt::makeSuperCage();
$url = recaptcha_get_signup_url($superCage->server->getRaw('HTTP_HOST'), 'Coppermine Photo Gallery');

$recaptcha_info = <<<EOT
<a href="{$url}" class="admin_menu">{$recap_lang['link_recaptcha']}</a>
EOT;
	

$name = $recap_lang['display_name'];
$configuration_link = '<a href="index.php?file=recaptcha/admin" class="admin_menu">' . $recaptcha_icon_array['configure'] . sprintf($recap_lang['configure_plugin_x'], $recap_lang['display_name']) . '</a> ';
$documentation_link = '<a href="plugins/recaptcha/docs/' . $documentation_file . '.htm" class="admin_menu">' . $recaptcha_icon_array['documentation'] . $recap_lang['plugin_documentation'] . '</a> ';
$announcement_thread = '<a href="http://forum.coppermine-gallery.net/index.php/topic,57439.html" class="admin_menu">' . $recaptcha_icon_array['announcement'] . $recap_lang['announcement_thread'] . '</a>';
$extra_info = $configuration_link .  $announcement_thread . $documentation_link;
$install_info = $documentation_link . $announcement_thread . $recaptcha_info;

$author  = <<<EOT
    <a href="http://forum.coppermine-gallery.net/index.php?action=profile;u=40798">Sander Weyens aka SaWey.</a> Updated by <a href="http://forum.coppermine-gallery.net/index.php?action=profile;u=65237">Joe Carver aka i-imagine.</a>
EOT;

$version='2.0';
$plugin_cpg_version = array('min' => '1.5');
$description = $recap_lang['description'];	
	
?>
