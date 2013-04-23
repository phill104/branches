<?php
/***********************************
  Coppermine reCAPTCHA plugin v2.0
  **********************************
  By: SaWey - Updated by Joe Carver
  Date: 2010-11-10
**********************************************/
	if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

	global $CONFIG, $CPG_PHP_SELF;

	// Add install & configure action
	$thisplugin->add_action('plugin_install','sawey_recaptcha_install');
	$thisplugin->add_action('plugin_configure','sawey_recaptcha_configure');
	$thisplugin->add_action('plugin_uninstall','sawey_recaptcha_uninstall');

	// Add filters & actions to display & validate CAPTCHA
	$thisplugin->add_filter('captcha_comment_print', 'add_captcha_comment');
	$thisplugin->add_filter('captcha_contact_print', 'add_captcha_contact');
	$thisplugin->add_filter('captcha_register_print', 'add_captcha_register');

	$thisplugin->add_filter('captcha_register_validate', 'validate_register');
	$thisplugin->add_action('captcha_comment_validate', 'validate_comment');
	$thisplugin->add_action('captcha_contact_validate', 'validate_contact');
	
	// Add plugin action - use a modified comment form
	$thisplugin->add_action('page_start','captcha_comment_form');
	
	function captcha_comment_form()
	{	
	global $CONFIG, $template_add_your_comment;
    // HTML template for comment 

	$spacers_user = '';
    $show_rows = $CONFIG['sawey_recaptcha_rows'];
	// FF workaround for added rows - change value 14 if it does not work with text style of theme
	$rows_height = ($show_rows * 15) . 'px';
	$style_rows = <<<EOT
 style="width: 100%; height: {$rows_height};"  
EOT;

    if (($CONFIG['comment_captcha'] == 1) || ($CONFIG['comment_captcha'] == 2 && !USER_ID)) {
	$show_ok = <<<EOT
	                  <td class="tableb tableb_alternate" colspan="2">
								</td> 
EOT;
    if (USER_ID) {
	    $spacers_user = <<<EOT

                                                        <tr>
                               <td class="tableb tableb_alternate">
                                        <img src="images/spacer.gif" width="5" height="10" border="0" alt="" />
                                </td>
                                <td class="tableb tableb_alternate">
                                        <img src="images/spacer.gif" width="5" height="10" border="0" alt="" />
                                </td>
EOT;
				}
   }  else  {
   $show_ok = <<<EOT
                                <td class="tableb tableb_alternate">
                                <input type="hidden" name="event" value="comment" />
                                <input type="hidden" name="pid" value="{PIC_ID}" />
                                <button type="submit" class="button" name="submit" value="{OK}"  onclick="return notDefaultUsername(this.form, '{DEFAULT_USERNAME}', '{DEFAULT_USERNAME_MESSAGE}');">{OK_ICON}{OK}</button>
                                <input type="hidden" name="form_token" value="{FORM_TOKEN}" />
                                <input type="hidden" name="timestamp" value="{TIMESTAMP}" />
                                </td>
EOT;

   }

$template_add_your_comment = <<<EOT
        <form method="post" name="post" id="post" action="db_input.php">
                <table align="center" width="{WIDTH}" cellspacing="1" cellpadding="0" class="maintable">
                        <tr>
                                        <td width="100%" class="tableh2">{ADD_YOUR_COMMENT}{HELP_ICON}</td>
                        </tr>
                        <tr>
                <td colspan="1">
                        <table width="100%" cellpadding="0" cellspacing="0">

<!-- BEGIN user_name_input -->
                                                        <tr>
                               <td class="tableb tableb_alternate">
                                        {NAME}
                                </td>
                                <td class="tableb tableb_alternate">
                                        <input type="text" class="textinput" name="msg_author" size="18" maxlength="20" value="{USER_NAME}" onclick="if (this.value == '{DEFAULT_USERNAME}') this.value = '';" onkeyup="if (this.value == '{DEFAULT_USERNAME}') this.value = '';" />
                                </td>
<!-- END user_name_input -->
<!-- BEGIN input_box_smilies -->
								{$spacers_user}
                                <td class="tableb tableb_alternate">
                                {COMMENT}
                                </td>
                                <td width="100%" class="tableb tableb_alternate">
                                <textarea class="textinput" id="message" name="msg_body" onselect="storeCaret_post(this);" onclick="storeCaret_post(this);" onkeyup="storeCaret_post(this);" {$style_rows} rows="{$show_rows}" cols= "60"></textarea>
								</td>
<!-- END input_box_smilies -->
<!-- BEGIN input_box_no_smilies -->
								{$spacers_user}
                                <td class="tableb tableb_alternate">
                                {COMMENT}
                                </td>
                                <td width="100%" class="tableb tableb_alternate">
                                <textarea class="textinput" id="message" name="msg_body" {$style_rows} rows="{$show_rows}" cols= "60"></textarea> 
                                </td>
<!-- END input_box_no_smilies -->
<!-- BEGIN submit -->
                                   {$show_ok}
								   
<!-- END submit -->
                                                        </tr>
<!-- BEGIN comment_captcha -->
                                                        <tr>
                                <td class="tableb tableb_alternate" colspan="2">
                                  {CONFIRM}
                                </td>
                                <td class="tableb tableb_alternate" colspan="2">
                                  <input type="text" name="confirmCode" size="5" maxlength="5" class="textinput" />
                                  
                                </td>
                                                        </tr>
<!-- END comment_captcha -->
                        </table>
                </td>
        </tr>
<!-- BEGIN smilies -->
        <tr>
                <td width="100%" class="tableb tableb_alternate">
                        {SMILIES}
                </td>
        </tr>
<!-- END smilies -->
<!-- BEGIN login_to_comment -->
        <tr>
                                <td class="tableb tableb_alternate" colspan="2">
                                  {LOGIN_TO_COMMENT}
                                </td>
        </tr>
<!-- END login_to_comment -->
                </table>
        </form>
EOT;
}

// Form comment end	
	define('RECAPTCHAPATH', $thisplugin->fullpath);
	
	// Add filter for remote .js to page meta
	$recaptcha_pages_array = array('displayimage.php', 'register.php', 'contact.php');
	if (in_array($CPG_PHP_SELF, $recaptcha_pages_array) == TRUE) {
	$thisplugin->add_filter('page_meta','recaptcha_header');
	function recaptcha_header($html) {
	global $CONFIG, $template_header, $LINEBREAK;
		$meta = <<< EOT
<!-- AJAX reCaptcha plugin for Coppermine v2.0 by SaWey and Carver -->
<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
<!-- reCaptcha plugin end -->
EOT;
	return $html . $meta;
    }	
	}

//print the captcha image

	function add_captcha_comment($template){
	global $CONFIG, $lang_common;
if (($CONFIG['comment_captcha'] == 1) || ($CONFIG['comment_captcha'] == 2 && !USER_ID)) {	

	require_once(RECAPTCHAPATH . '/recaptchalib.php');
	//load language
	$file_lang = RECAPTCHAPATH . '/lang/' . $CONFIG['lang'] . '.php';
	include(file_exists($file_lang) ? $file_lang : RECAPTCHAPATH . '/lang/english.php');		
	
	$key_public = $CONFIG['sawey_recaptcha_key'];
	$recapt_style = $CONFIG['sawey_recaptcha_style'];
	$recapt_lang = $CONFIG['sawey_recaptcha_lang'];
	$spacer_width = 80;
	if ($CONFIG['sawey_recaptcha_style'] == 'clean')  {
	$spacer_width = 160;
	}
	$comment_instruct = '';
	if ($CONFIG['sawey_recaptcha_commenthelp'] == 1)  {
	$comment_instruct = $recap_lang['comment_instruct'];
	}
	
	$captcha_html = <<< EOT
	<script type="text/javascript" >
	function showRecaptcha(element, submitButton, themeName) {
   	Recaptcha.create("$key_public", element, {
         theme: '$recapt_style',
         lang: '$recapt_lang',
         callback: Recaptcha.focus_response_field
 });
 hideSubmitButtons();
document.getElementById(submitButton).style.visibility = "visible";
}
function hideSubmitButtons() {
 document.getElementById('submit_button_1').style.visibility = "hidden";
}
function destroyRecaptchaWidget()
 { hideSubmitButtons();
 Recaptcha.destroy();
}
</script>
	<input type="button" class="button" title="{$recap_lang['confirm_title']}"  value="{$recap_lang['enter_confirm']}" onclick="showRecaptcha('dynamic_recaptcha_1', 'submit_button_1');" />
				<div id="submit_button_1" style="visibility: hidden">
							{$comment_instruct}	
				<div id="dynamic_recaptcha_1"></div>
				<img src="images/spacer.gif" width="25" height="1" border="0" alt="" />									
				<input type="hidden" name="event" value="comment" />
				<input type="hidden" name="pid" value="{PIC_ID}" />
				<button type="submit" class="button" name="submit" value="{OK}" title="{$recap_lang['ok_title']}" onclick="return notDefaultUsername(this.form, '{DEFAULT_USERNAME}', '{DEFAULT_USERNAME_MESSAGE}');">{OK_ICON}{OK}</button>
				<input type="hidden" name="form_token" value="{FORM_TOKEN}" />
				<input type="hidden" name="timestamp" value="{TIMESTAMP}" />								
				<img src="images/spacer.gif" width="{$spacer_width}" height="1" border="0" alt="" />								
				<input type="button" class="button" value="{$recap_lang['new_words']}" title="{$recap_lang['new_words_title']}" onclick="Recaptcha.reload();" />								
				<img src="images/spacer.gif" width="20" height="1" border="0" alt="" />								
				<input type="button" class="button" value="{$recap_lang['hide_button']}" title="{$recap_lang['hide_title']}" onclick="destroyRecaptchaWidget();" />
				</div> 	
				
EOT;


	
	
	$new_template = <<< EOT
	<tr>
		<td class="tableb tableb_alternate tableb tableb_alternate_alternate_compact" colspan="3">
		  
		</td>
		<td class="tableb tableb_alternate tableb tableb_alternate_alternate_compact" colspan="3">
		  <div id="captcha_div">{$captcha_html}</div>
		</td>
	</tr>
EOT;
	
	$pattern = "#(<!-- BEGIN comment_captcha -->)(.*?)(<!-- END comment_captcha -->)#s";
	if ( !preg_match($pattern, $template, $matches)) {
			die('<strong>Template error<strong><br />Failed to find block \''.$block_name.'\'('.htmlspecialchars($pattern).') in :<br /><pre>'.htmlspecialchars($template).'</pre>');
	}
	
	$template = str_replace($matches[1].$matches[2].$matches[3], $new_template, $template);
	return $template;
}
}

function add_captcha_contact($captcha_print){
	global $CONFIG, $lang_common, $captcha_remark, $expand_array;
	require_once(RECAPTCHAPATH . '/recaptchalib.php');
	//load language
	$file_lang = RECAPTCHAPATH . '/lang/' . $CONFIG['lang'] . '.php';
	include(file_exists($file_lang) ? $file_lang : RECAPTCHAPATH . '/lang/english.php');	
	
	$visibility = isset($expand_array['captcha_remark']) ? 'block' : 'none';
	
	$key_public = $CONFIG['sawey_recaptcha_key'];
	$recapt_style = $CONFIG['sawey_recaptcha_style'];;
	$recapt_lang = $CONFIG['sawey_recaptcha_lang'];	
	$spacer_width = 130;
	if ($CONFIG['sawey_recaptcha_style'] == 'clean')  {
	$spacer_width = 190;
	}	
	$contact_instruct = '';
	if ($CONFIG['sawey_recaptcha_contacthelp'] == 1)  {
	$contact_instruct = $recap_lang['contact_instruct'];
	}	
	
	if (!$CONFIG['sawey_recaptcha_contact'] == 1 ) {
	$captcha_html = recaptcha_get_html($CONFIG['sawey_recaptcha_key']);
	}
	else {
	$captcha_html = <<< EOT
	<script type="text/javascript" >
	function showRecaptcha(element, submitButton, themeName) {
   	Recaptcha.create("$key_public", element, {
         theme: '$recapt_style',
         lang: '$recapt_lang',
         callback: Recaptcha.focus_response_field
 });
 hideSubmitButtons();
document.getElementById(submitButton).style.visibility = "visible";
}
function hideSubmitButtons() {
 document.getElementById('submit_button_1').style.visibility = "hidden";
}
function destroyRecaptchaWidget()
 { hideSubmitButtons();
 Recaptcha.destroy();
}
</script>
	<input type="button" class="button" title="{$recap_lang['confirm_contact']}"  value="{$recap_lang['enter_confirm']}" onclick="showRecaptcha('dynamic_recaptcha_1', 'submit_button_1');" />
				<div id="submit_button_1" style="visibility: hidden">
				{$contact_instruct}  	
				<div id="dynamic_recaptcha_1"></div>
				<img src="images/spacer.gif" width="{$spacer_width}" height="10" border="0" alt="" />								
				<input type="button" class="button" value="{$recap_lang['new_words']}" title="{$recap_lang['new_words_title']}" onclick="Recaptcha.reload();" />								
				<img src="images/spacer.gif" width="20" height="10" border="0" alt="" />								
				<input type="button" class="button" value="{$recap_lang['hide_button']}" title="{$recap_lang['hide_title']}" onclick="destroyRecaptchaWidget();" />
				</div> 			
EOT;
	}
	
	$captcha_print = <<< EOT
    <tr>
      <td class="tableb" valign="top" align="right">
        {$lang_common['confirm']}
      </td>
      <td class="tableb" valign="top">
        {$captcha_html}
      </td>
	  <td class="tableb">
        <span id="captcha_remark" style="display:{$visibility}">{$captcha_remark}</span>
      </td>
    </tr>
EOT;
	return $captcha_print;
}

function add_captcha_register($captcha_print){
	global $CONFIG, $lang_common;
	require_once(RECAPTCHAPATH . '/recaptchalib.php');
	
	//load language
	$file_lang = RECAPTCHAPATH . '/lang/' . $CONFIG['lang'] . '.php';
	include(file_exists($file_lang) ? $file_lang : RECAPTCHAPATH . '/lang/english.php');
	
	$key_public = $CONFIG['sawey_recaptcha_key'];
	$recapt_style = $CONFIG['sawey_recaptcha_style'];;
	$recapt_lang = $CONFIG['sawey_recaptcha_lang'];
	$spacer_width = 130;
	if ($CONFIG['sawey_recaptcha_style'] == 'clean')  {
	$spacer_width = 190;
	}	
	$reg_instruct = '';	
	if ($CONFIG['sawey_recaptcha_reghelp'] == 1)  {
	$reg_instruct = $recap_lang['reg_instruct'];
	}		
	
	$captcha_html = <<< EOT
	<script type="text/javascript" >
	function showRecaptcha(element, submitButton, themeName) {
   	Recaptcha.create("$key_public", element, {
         theme: '$recapt_style',
         lang: '$recapt_lang',
         callback: Recaptcha.focus_response_field
 });
 hideSubmitButtons();
document.getElementById(submitButton).style.visibility = "visible";
}
function hideSubmitButtons() {
 document.getElementById('submit_button_1').style.visibility = "hidden";
}
function destroyRecaptchaWidget()
 { hideSubmitButtons();
 Recaptcha.destroy();
}
</script>
	<input type="button" class="button" title="{$recap_lang['confirm_reg']}"  value="{$recap_lang['enter_confirm']}" onclick="showRecaptcha('dynamic_recaptcha_1', 'submit_button_1');" />
				<div id="submit_button_1" style="visibility: hidden">
				{$reg_instruct}
				<div id="dynamic_recaptcha_1"></div>  		
				<img src="images/spacer.gif" width="{$spacer_width}" height="10" border="0" alt="" />								
				<input type="button" class="button" value="{$recap_lang['new_words']}" title="{$recap_lang['new_words_title']}" onclick="Recaptcha.reload();" />								
				<img src="images/spacer.gif" width="20" height="10" border="0" alt="" />								
				<input type="button" class="button" value="{$recap_lang['hide_button']}" title="{$recap_lang['hide_title']}" onclick="destroyRecaptchaWidget();" />
				</div> 			
EOT;
	
	$captcha_print = <<< EOT
    <tr>
      <td class="tableb" valign="middle">
       {$lang_common['confirm']}
      </td>
      <td class="tableb" valign="top">
        {$captcha_html}
      </td>
    </tr>
EOT;
	return $captcha_print;
}

//validate captcha entry
function validate_contact(){
	global $CONFIG, $USER_DATA, $hdr_ip;
	$superCage = Inspekt::makeSuperCage();
	require_once(RECAPTCHAPATH . '/recaptchalib.php');
	
	$resp = recaptcha_check_answer ($CONFIG['sawey_recaptcha_privkey'], $superCage->server->getEscaped('REMOTE_ADDR'), $superCage->post->getRaw('recaptcha_challenge_field'), $superCage->post->getRaw('recaptcha_response_field'));

	if (!$resp->is_valid) {
		if ($CONFIG['log_mode'] != 0) {
		log_write('Captcha authentication for Contact failed for user '.$USER_DATA['user_name'].' at ' . $hdr_ip, CPG_SECURITY_LOG);
		}
		//load language
		$file_lang = RECAPTCHAPATH . '/lang/' . $CONFIG['lang'] . '.php';
		include(file_exists($file_lang) ? $file_lang : RECAPTCHAPATH . '/lang/english.php');
		$GLOBALS['captcha_remark'] = $recap_lang['incorrect-captcha-contact'];
		$GLOBALS['expand_array'] = 'captcha_remark';
		$GLOBALS['error']++;
	}
}

function validate_register($error){
	global $CONFIG, $USER_DATA, $hdr_ip;
	$superCage = Inspekt::makeSuperCage();
	require_once(RECAPTCHAPATH . '/recaptchalib.php');
	
	$resp = recaptcha_check_answer ($CONFIG['sawey_recaptcha_privkey'], $superCage->server->getEscaped('REMOTE_ADDR'), $superCage->post->getRaw('recaptcha_challenge_field'), $superCage->post->getRaw('recaptcha_response_field'));

	if (!$resp->is_valid) {
		if ($CONFIG['log_mode'] != 0) {
		log_write('Captcha authentication for Register failed for user '.$USER_DATA['user_name'].' at ' . $hdr_ip, CPG_SECURITY_LOG);
		}
		//load language		
		$file_lang = RECAPTCHAPATH . '/lang/' . $CONFIG['lang'] . '.php';
		include(file_exists($file_lang) ? $file_lang : RECAPTCHAPATH . '/lang/english.php');
		$error .= '<li style="list-style-image:url(images/icons/stop.png)">' . $recap_lang['incorrect-captcha-reg'] . '</li>';
	}
	return $error;
}

function validate_comment(){
	global $CONFIG, $USER_DATA, $hdr_ip;
	$superCage = Inspekt::makeSuperCage();
	require_once(RECAPTCHAPATH . '/recaptchalib.php');
	
	$resp = recaptcha_check_answer ($CONFIG['sawey_recaptcha_privkey'], $superCage->server->getEscaped('REMOTE_ADDR'), $superCage->post->getRaw('recaptcha_challenge_field'), $superCage->post->getRaw('recaptcha_response_field'));

	if (!$resp->is_valid) {
		if ($CONFIG['log_mode'] != 0) {
		log_write('Captcha authentication for Comment failed for user '.$USER_DATA['user_name'].' at ' . $hdr_ip, CPG_SECURITY_LOG);
		}
		//load language
		$file_lang = RECAPTCHAPATH . '/lang/' . $CONFIG['lang'] . '.php';
		include(file_exists($file_lang) ? $file_lang : RECAPTCHAPATH . '/lang/english.php');
		cpg_die(ERROR, $recap_lang['incorrect-captcha-comment'], __FILE__, __LINE__);
	}	
}


// Install function
function sawey_recaptcha_install() {
	global $CONFIG;
	// Create the super cage
	$superCage = Inspekt::makeSuperCage();
	if (!$superCage->post->keyExists('rc_submit')) {
		return 1;
	}else{
		//create a new entry in the configuration table
		$rc_key = $superCage->post->getRaw('rc_key'); //needs sanitisation, but I don't know which chars are currently allowed in the key
		$rc_privkey = $superCage->post->getRaw('rc_privkey');
		if(strlen($rc_key) < 35){
			return 1;
		}
		$rc_style = 'clean';
		$rc_lang = 'en';
		$rc_contact = '1';
		cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('sawey_recaptcha_key', '$rc_key')");
		cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('sawey_recaptcha_privkey', '$rc_privkey')");
		cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('sawey_recaptcha_style', '$rc_style')");
		cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('sawey_recaptcha_lang', '$rc_lang')");
		cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('sawey_recaptcha_contact', '$rc_contact')");
		cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('sawey_recaptcha_commenthelp', '0')");
		cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('sawey_recaptcha_reghelp', '1')");
		cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('sawey_recaptcha_contacthelp', '1')");
        cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('sawey_recaptcha_rows', '2')");
	return true;
	}
	
}

// Configure function
// Displays the form
function sawey_recaptcha_configure() {
	global $CONFIG, $lang_common;
	// Create the super cage
	$superCage = Inspekt::makeSuperCage();
	
	//load language
	$file_lang = RECAPTCHAPATH . '/lang/' . $CONFIG['lang'] . '.php';
	include(file_exists($file_lang) ? $file_lang : RECAPTCHAPATH . '/lang/english.php');
	include_once(RECAPTCHAPATH . '/recaptchalib.php');
	$superCage = Inspekt::makeSuperCage();
	$url = recaptcha_get_signup_url($superCage->server->getRaw('HTTP_HOST'), 'Coppermine Photo Gallery');

	
	$action_uri = $superCage->server->getEscaped('REQUEST_URI');
    echo <<< EOT
    <form name="cpgform" id="cpgform" action="{$action_uri}" method="post">
            <table border="0" cellspacing="0" cellpadding="0" width="100%">
              <tr>
                <td class="tableh2" colspan="2">
                  <h2>{$recap_lang['install_explain']}</h2>
				  <br />
				  <a href="{$url}" rel="external">{$recap_lang['install_click']}</a>
				  <br />
                </td>
              </tr>
              <tr>
                <td class="tableb">
				<br />
                  {$recap_lang['recaptcha_key']}:
                </td>
			  </tr>
			  <tr>
                <td class="tableb">
                  <input type="text" name="rc_key" class="textinput" size="55"/>
                </td>
              </tr>
			  <tr>
                <td class="tableb">
				<br />
                  {$recap_lang['recaptcha_privkey']}:
                </td>
			  </tr>
			  <tr>
                <td class="tableb">
                  <input type="text" name="rc_privkey" class="textinput" size="55"/>
                </td>
              </tr>
              <tr>
                <td class="tablef" colspan="2">
                  <input type="submit" name="rc_submit" value="{$lang_common['go']}" class="button" />
                </td>
              </tr>
            </table>
    </form>
EOT;
}

function sawey_recaptcha_uninstall(){
	global $CONFIG;
	//remove the record from config
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'sawey_recaptcha_key'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'sawey_recaptcha_privkey'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'sawey_recaptcha_style'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'sawey_recaptcha_lang'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'sawey_recaptcha_contact'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'sawey_recaptcha_commenthelp'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'sawey_recaptcha_reghelp'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'sawey_recaptcha_contacthelp'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'sawey_recaptcha_rows'");
	
	
	return true;	
}

?>