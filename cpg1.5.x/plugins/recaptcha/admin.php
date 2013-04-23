<?php
/***********************************
  Coppermine reCAPTCHA plugin v2.0
  **********************************
  By: SaWey - Updated by Joe Carver
  Date: 2010-11-10
**********************************************/

require_once('./plugins/recaptcha/init.inc.php');

if (!GALLERY_ADMIN_MODE) {
    cpg_die(ERROR, $lang_errors['perm_denied'], __FILE__, __LINE__);
}

global $lang_plugin_php, $CONFIG, $lang_common, $lang_pluginmgr_php, $lang_admin_php, $icon_array;


if (in_array('js/jquery.spinbutton.js', $JS['includes']) != TRUE) {
    $JS['includes'][] = 'js/jquery.spinbutton.js';
}
if (in_array('plugins/recaptcha/js/admin.js', $JS['includes']) != TRUE) {
    $JS['includes'][] = 'plugins/recaptcha/js/admin.js';
}

list($timestamp, $form_token) = getFormToken();
pageheader(sprintf($recap_lang['configure_plugin_x'], $recap_lang['display_name']));

// get sanitized POST parameters
if ($superCage->post->keyExists('submit')) {
    //Check if the form token is valid
    if(!checkFormToken()){
        cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
    }

    // Define the sanitization patterns
    $sanitization_array = array(
        'sawey_recaptcha_lang' => array('type' => 'raw', 'regex_ok' => '/^[a-z_]+$/'),
        'sawey_recaptcha_style' => array('type' => 'raw', 'regex_ok' => '/^[a-z_]+$/'),
        'sawey_recaptcha_contact' => array('type' => 'checkbox', 'min' => '0', 'max' => '1'),
        'sawey_recaptcha_commenthelp' => array('type' => 'checkbox', 'min' => '0', 'max' => '1'),
        'sawey_recaptcha_contacthelp' => array('type' => 'checkbox', 'min' => '0', 'max' => '1'),
        'sawey_recaptcha_reghelp' => array('type' => 'checkbox', 'min' => '0', 'max' => '1'),
        'sawey_recaptcha_rows' => array('type' => 'int', 'min' => '1', 'max' => '9'),
        'sawey_recaptcha_key' => array('type' => 'raw'),
        'sawey_recaptcha_privkey' => array('type' => 'raw')
    );

    $config_changes_counter = 0;
    foreach ($sanitization_array as $san_key => $san_value) {
        if (isset($CONFIG[$san_key]) == TRUE) { // only loop if config value is set --- start
            if ($san_value['type'] == 'checkbox') { // type is checkbox --- start
                if ($superCage->post->getInt($san_key) == $san_value['max'] && $CONFIG[$san_key] != $san_value['max']) {
                    $CONFIG[$san_key] = $san_value['max'];
                    cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG[$san_key]}' WHERE name='$san_key'");
                    $config_changes_counter++;
                } elseif($superCage->post->getInt($san_key) == $san_value['min'] && $CONFIG[$san_key] != $san_value['min']) {
                    $CONFIG[$san_key] = $san_value['min'];
                    cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG[$san_key]}' WHERE name='$san_key'");
                    $config_changes_counter++;
                } elseif($superCage->post->keyExists($san_key) != TRUE && $CONFIG[$san_key] != '0') {
                    $CONFIG[$san_key] = 0;
                    cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG[$san_key]}' WHERE name='$san_key'");
                    $config_changes_counter++;
                }
            } // type is checkbox --- end
            if ($san_value['type'] == 'raw') { // type is raw --- start
                if (isset($san_value['regex_ok']) == TRUE && preg_match($san_value['regex_ok'], $superCage->post->getRaw($san_key)) && $superCage->post->getRaw($san_key) != $CONFIG[$san_key]) {
                    $CONFIG[$san_key] = $superCage->post->getRaw($san_key);
                    if ($superCage->post->getRaw($san_key) == 'none') {
                        $CONFIG[$san_key] = '';
                    }
                    cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG[$san_key]}' WHERE name='$san_key'");
                    $config_changes_counter++;
                }
            } // type is raw --- end
            if ($san_value['type'] == 'int') { // type is integer --- start
                if ($superCage->post->getInt($san_key) <= $san_value['max'] && $superCage->post->getInt($san_key) >= $san_value['min'] && $superCage->post->getInt($san_key) != $CONFIG[$san_key]) {
                    $CONFIG[$san_key] = $superCage->post->getInt($san_key);
                    cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG[$san_key]}' WHERE name='$san_key'");
                    $config_changes_counter++;
                }
            } // type is integer --- end
        } // only loop if config value is set --- end
    }
}

// vars for form display
if ($CONFIG['sawey_recaptcha_contact'] == '1') {
    $option_output['recapt_caption'] = 'checked="checked"';
} else {
    $option_output['recapt_caption'] = '';
}

if ($CONFIG['sawey_recaptcha_commenthelp'] == '1') {
    $option_output['recapt_commenthelp'] = 'checked="checked"';
} else {
    $option_output['recapt_commenthelp'] = '';
}

    if ($CONFIG['sawey_recaptcha_contacthelp'] == '1') {
    $option_output['recapt_contacthelp'] = 'checked="checked"';
} else {
    $option_output['recapt_contacthelp'] = '';
}

if ($CONFIG['sawey_recaptcha_reghelp'] == '1') {
    $option_output['recapt_reghelp'] = 'checked="checked"';
} else {
    $option_output['recapt_reghelp'] = '';
}

$option_output['recapt_rows'] = $CONFIG['sawey_recaptcha_rows'];
$option_output['recapt_key'] = $CONFIG['sawey_recaptcha_key'];
$option_output['recapt_privkey'] = $CONFIG['sawey_recaptcha_privkey'];



$lang_en = '';
if ($CONFIG['sawey_recaptcha_lang']  == 'en') {
    $lang_en = 'selected="selected"';
}
$lang_de = '';
if ($CONFIG['sawey_recaptcha_lang']  == 'de') {
    $lang_de = 'selected="selected"';
}
$lang_nl = '';
if ($CONFIG['sawey_recaptcha_lang']  == 'nl') {
    $lang_nl = 'selected="selected"';
}
$lang_fr = '';
if ($CONFIG['sawey_recaptcha_lang']  == 'fr') {
    $lang_fr = 'selected="selected"';
}
$lang_pt = '';
if ($CONFIG['sawey_recaptcha_lang']  == 'pt') {
    $lang_pt = 'selected="selected"';
}
$lang_ru = '';
if ($CONFIG['sawey_recaptcha_lang']  == 'ru') {
    $lang_ru = 'selected="selected"';
}
$lang_es = '';
if ($CONFIG['sawey_recaptcha_lang'] == 'es') {
    $lang_es = 'selected="selected"';
}
$lang_tr = '';
if ($CONFIG['sawey_recaptcha_lang']  == 'tr') {
    $lang_tr = 'selected="selected"';
}
$style_white = '';
if ($CONFIG['sawey_recaptcha_style']  == 'white') {
    $style_white = 'selected="selected"';
}
$style_red = '';
if ($CONFIG['sawey_recaptcha_style']  == 'red') {
    $style_red = 'selected="selected"';
}
$style_blackglass = '';
if ($CONFIG['sawey_recaptcha_style']  == 'blackglass') {
    $style_blackglass = 'selected="selected"';
}
$style_clean = '';
if ($CONFIG['sawey_recaptcha_style']  == 'clean') {
    $style_clean = 'selected="selected"';
}

// reply with success to changes or no changes made
if ($superCage->post->keyExists('submit')) {
    if ($config_changes_counter > 0) {
        msg_box('', $recap_lang['update_success'], '', '', 'success');
    } else {
        msg_box('', $recap_lang['no_changes'], '', '', 'validation');
    }
}

// start form
$superCage = Inspekt::makeSuperCage();
echo <<< EOT
<form name="cpgform" id="cpgform" action="{$_SERVER['REQUEST_URI']}" method="post">
EOT;

starttable('100%',  sprintf($recap_lang['configure_plugin_x'], $recap_lang['display_name']), 2, 'cpg_zebra');


// complete form
echo <<< EOT
        <tr>
        <td class="tableb" colspan="2">
        <b>{$recap_lang['page_heading']}</b>
        </td>
        </tr>
        <tr>
        <td class="tableb tableb_alternate">
        {$recap_lang['update_lang']}
        <a href="plugins/recaptcha/docs/{$documentation_file}.htm#set_lang" class="greybox" title="{$recap_lang['update_lang']}"><img src="images/help.gif" width="13" height="11" border="0" alt="" /></a>
        </td>
        <td class="tableb tableb_alternate">
        <select name="sawey_recaptcha_lang" id="sawey_recaptcha_lang" class="listbox">
             <option value="en" $lang_en>English</option>
             <option value="de" $lang_de>German</option>
             <option value="nl" $lang_nl>Dutch</option>
             <option value="fr" $lang_fr>French</option>
             <option value="pt" $lang_pt>Portuguese</option>
             <option value="ru" $lang_ru>Russian</option>
             <option value="es" $lang_es>Spanish</option>
             <option value="tr" $lang_tr>Turkish</option>
        </select>
        </td>
        </tr>
        <tr>
        <td class="tableb">{$recap_lang['update_style']}
        <a href="plugins/recaptcha/docs/{$documentation_file}.htm#set_style" class="greybox" title="{$recap_lang['update_style']}"><img src="images/help.gif" width="13" height="11" border="0" alt="" /></a>
        </td>
        <td class="tableb">
        <select name="sawey_recaptcha_style" id="sawey_recaptcha_style" class="listbox">
            <option value="white" $style_white>Style white</option>
            <option value="red" $style_red>Style red</option>
            <option value="blackglass" $style_blackglass>Style blackglass</option>
            <option value="clean" $style_clean>Style clean</option>
        </select>
        </td>
        </tr>
        <tr>
        <td class="tableb tableb_alternate">{$recap_lang['update_contact']}
        <a href="plugins/recaptcha/docs/{$documentation_file}.htm#ajax_contact" class="greybox" title="{$recap_lang['update_contact']}"><img src="images/help.gif" width="13" height="11" border="0" alt="" /></a>
        </td>
        <td class="tableb tableb_alternate">
        <input type="checkbox" name="sawey_recaptcha_contact" id="sawey_recaptcha_contact" class="checkbox" value="1" {$option_output['recapt_caption']} />
        <label for="recapt_caption" class="clickable_option">{$lang_common['yes']}</label>
        </td>
        </tr>
        <tr>
        <td class="tableb">{$recap_lang['commenthelp']}
        <a href="plugins/recaptcha/docs/{$documentation_file}.htm#ajax_commenthelp" class="greybox" title="{$recap_lang['commenthelp']}"><img src="images/help.gif" width="13" height="11" border="0" alt="" /></a>
        </td>
        <td class="tableb">
        <input type="checkbox" name="sawey_recaptcha_commenthelp" id="sawey_recaptcha_commenthelp" class="checkbox" value="1" {$option_output['recapt_commenthelp']} />
        <label for="recapt_commenthelp" class="clickable_option">{$lang_common['yes']}</label>
        </td>
        </tr>
        <tr>
        <td class="tableb tableb_alternate">{$recap_lang['contacthelp']}
        <a href="plugins/recaptcha/docs/{$documentation_file}.htm#ajax_contacthelp" class="greybox" title="{$recap_lang['contacthelp']}"><img src="images/help.gif" width="13" height="11" border="0" alt="" /></a>
        </td>
        <td class="tableb tableb_alternate">
        <input type="checkbox" name="sawey_recaptcha_contacthelp" id="sawey_recaptcha_contacthelp" class="checkbox" value="1" {$option_output['recapt_contacthelp']} />
        <label for="recapt_contacthelp" class="clickable_option">{$lang_common['yes']}</label>
        </td>
        </tr>
        <tr>
        <td class="tableb">{$recap_lang['reghelp']}
        <a href="plugins/recaptcha/docs/{$documentation_file}.htm#ajax_reghelp" class="greybox" title="{$recap_lang['reghelp']}"><img src="images/help.gif" width="13" height="11" border="0" alt="" /></a>
        </td>
        <td class="tableb">
        <input type="checkbox" name="sawey_recaptcha_reghelp" id="sawey_recaptcha_reghelp" class="checkbox" value="1" {$option_output['recapt_reghelp']} />
        <label for="recapt_reghelp" class="clickable_option">{$lang_common['yes']}</label>
        </td>
        </tr>
        <tr>
        <td class="tableb tableb_alternate">{$recap_lang['recaptcha_rows']}
        <a href="plugins/recaptcha/docs/{$documentation_file}.htm#ajax_rows" class="greybox" title="{$recap_lang['rows']}"><img src="images/help.gif" width="13" height="11" border="0" alt="" /></a>
        </td>
        <td class="tableb tableb_alternate">
        <input type="text" name="sawey_recaptcha_rows" id="sawey_recaptcha_rows" class="textinput" size="1" maxlength="1" value="{$option_output['recapt_rows']}" style="text-align:right;" /> {$recap_lang['rows']}
        </td>
        </tr>
        <tr>
        <td class="tableb">{$recap_lang['recaptcha_key']} <a href="plugins/recaptcha/docs/{$documentation_file}.htm#recaptcha_key" class="greybox" title="{$recap_lang['recaptcha_key']}"><img src="images/help.gif" width="13" height="11" border="0" alt="" /></a>
        </td>
        <td class="tableb">
        <input type="text" size="30" maxlength="250" class="textinput" style="width:90%" name="sawey_recaptcha_key" id="sawey_recaptcha_key"  value="{$option_output['recapt_key']}" />
        </td>
        </tr>
        <tr>
        <td class="tableb tableb_alternate">{$recap_lang['recaptcha_privkey']} <a href="plugins/recaptcha/docs/{$documentation_file}.htm#recaptcha_privkey" class="greybox" title="{$recap_lang['recaptcha_privkey']}"><img src="images/help.gif" width="13" height="11" border="0" alt="" /></a>
        </td>
        <td class="tableb tableb_alternate">
        <input type="text" size="30" maxlength="250" class="textinput" style="width:90%" name="sawey_recaptcha_privkey" id="sawey_recaptcha_privkey"  value="{$option_output['recapt_privkey']}" />
        </td>
        </tr>
        <tr>
        <td class="tableb" colspan="2" align="center">
        <input type="hidden" name="form_token" value="{$form_token}" />
        <input type="hidden" name="timestamp" value="{$timestamp}" />

        <button value="{$recap_lang['submit_change']}" name="submit" class="button" type="submit"><img width="16" height="16" border="0" class="icon" alt="" src="images/icons/ok.png">{$recap_lang['submit_change']}</button>
        </td>
        </tr>
EOT;

endtable();
echo <<< EOT
</form>
EOT;

pagefooter();
ob_end_flush();

?>