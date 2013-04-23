<?php
/***********************************
  Coppermine reCAPTCHA plugin v2.0
  **********************************
  By: SaWey - Updated by Joe Carver
  Date: 2010-11-10
**********************************************/

// language file for plugin - submit changes to the Support thread for inclusion in  
// the next release
$recap_lang['install_explain'] = 'Insert your keys below and click go. Use copy + paste.';
$recap_lang['recaptcha_key'] = 'reCAPTCHA public key';
$recap_lang['recaptcha_privkey'] = 'reCAPTCHA private key';
$recap_lang['recaptcha_link'] = 'if you don\'t have a reCAPTCHA key yet, you can obtain one for free at <a href="http://reCAPTCHA.net">Google for reCaptcha</a>';
$recap_lang['link_recaptcha'] = 'Click to get free reCAPTCHA key from Google.';

$recap_lang['unknown'] = 'Unknown error.';
$recap_lang['invalid-site-public-key'] = 'We weren\'t able to verify the public key.';
$recap_lang['invalid-site-private-key'] = 'We weren\'t able to verify the private key.';
$recap_lang['invalid-request-cookie'] = 'The challenge parameter of the verify script was incorrect.';
$recap_lang['verify-params-incorrect'] = 'The parameters to verify were incorrect, make sure you are passing all the required parameters.';
$recap_lang['invalid-referrer'] = 'reCAPTCHA API keys are tied to a specific domain name for security reasons. See above for tips on this matter.';
$recap_lang['recaptcha-not-reachable'] = 'Could not connect to reCAPTCHA server.';
$recap_lang['install_click'] = 'CLICK HERE to get your free reCAPTCHA key from Google if you don\'t have one already.';

$recap_lang['display_name'] = 'reCAPTCHA for Coppermine 1.5';
$recap_lang['configure_plugin_x'] = 'Configure Plugin';
$recap_lang['plugin_documentation'] = 'Plugin Documentation';
$recap_lang['announcement_thread'] = 'Plugin Support Thread';
$recap_lang['aka'] = 'aka';
$recap_lang['description'] = 'reCAPTCHA for Coppermine Photo Gallery<br /><br />
				reCAPTCHA is a free CAPTCHA service that helps to digitize books, newspapers and old time radio shows. <br />
				reCAPTCHA improves the process of digitizing books by sending words that cannot be read by computers to the Web 
				in the form of CAPTCHAs for humans to decipher. More specifically, each word that cannot be read correctly by OCR 
				is placed on an image and used as a CAPTCHA. This is possible because most OCR programs alert you when a word cannot be read correctly.';

// admin + confguation				
$recap_lang['display_name'] = 'AJAX reCaptcha';				
$recap_lang['configure_plugin_x'] = 'Configure plugin %s';				
$recap_lang['page_heading'] = 'Configure the settings of your AJAX reCaptcha plugin.';
$recap_lang['update_lang'] = 'Select language for reCaptcha tool-tips and help pop-up';
$recap_lang['update_style'] = 'Select style/color for reCaptcha';
$recap_lang['update_contact'] = 'Use AJAX reCaptcha on Contact form';
$recap_lang['commenthelp'] = 'Show help text with Comment Captcha';
$recap_lang['contacthelp'] = 'Show help text with Contact Captcha';
$recap_lang['reghelp'] = 'Show help text with Registration Captcha';
$recap_lang['recaptcha_rows'] = 'Number of rows for comments';
$recap_lang['rows'] = 'Rows';
$recap_lang['submit_change'] = 'Submit Changes';
$recap_lang['update_success'] = 'Update Success';
$recap_lang['no_changes'] = 'No changes made';

// what the visitor will see on the buttons
$recap_lang['enter_confirm'] = 'Enter Confirmation';
$recap_lang['confirm_title'] = 'Click to open reCaptcha. Type the two words + click OK. We use reCaptcha to stop spam';
$recap_lang['ok_title'] = 'Click OK only after typing the two words above';
$recap_lang['hide_button'] = 'Hide/Cancel';
$recap_lang['hide_title'] = 'Hide Confirmation Window';
$recap_lang['new_words'] = 'New Words';
$recap_lang['new_words_title'] = 'Get new words if you can not read these words';
$recap_lang['confirm_contact']  = 'Click to open reCaptcha. Type the two words + click Go. We use reCaptcha to stop spam';
$recap_lang['confirm_reg']  = 'Click to open reCaptcha. Type the two words + click Submit Registration. We use reCaptcha to stop spam';

// Help Text
$recap_lang['comment_instruct'] = <<<EOT
    <div id="instruct"><div id="instruct"><br />1- Type the two words. 2- Click OK. </div>
EOT;

$recap_lang['reg_instruct'] = <<<EOT
    <div id="instruct"><br />1- Type the two words. 2- Click Submit registration. </div>
EOT;

$recap_lang['contact_instruct'] = <<<EOT
    <div id="instruct"><br />1- Type the two words. 2- Click Go. </div>  
EOT;

// error messages for wrong input to recaptcha confirmation
$recap_lang['incorrect-captcha-sol'] = 'The CAPTCHA confirmation was incorrect. Go back and try again';
$recap_lang['incorrect-captcha-comment'] = <<<EOT
<script type="text/javascript">
	document.write('<span><a href="#" onclick="history.go(-1);return false;"><h2>The reCaptcha confirmation was incorrect.<br>Click here to Go Back and try again.</h2></span></a>');
</script>
EOT;
$recap_lang['incorrect-captcha-reg'] = 'The CAPTCHA confirmation was incorrect. Re-enter your password and try again';
$recap_lang['incorrect-captcha-contact'] = 'The CAPTCHA confirmation was incorrect. Please try again';
?>