<?php
/**************************************************
  Coppermine 1.5.x Plugin - Problem Solving CAPTCHA
  *************************************************
  Copyright (c) 2013 eenemeenemuu
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

define('PSC_QUESTION_PREFIX', 'plugin_psc_q_');
define('PSC_ANSWER_PREFIX', 'plugin_psc_a_');


function psc_get_question_ids() {
    global $CONFIG;
    foreach ($CONFIG as $key => $value) {
        if (strpos($key, PSC_QUESTION_PREFIX) === 0) {
            $psc_question_ids[] = substr($key, strlen(PSC_QUESTION_PREFIX));
        }
    }
    return $psc_question_ids;
}


function psc_get_random_question() {
    global $CONFIG;
    $psc_question_ids = psc_get_question_ids();
    $rand_id = $psc_question_ids[mt_rand() % count($psc_question_ids)];
    return array('id' => $rand_id, 'text' => $CONFIG[PSC_QUESTION_PREFIX . $rand_id]);
}


function psc_check_captcha() {
    global $CONFIG;
    $superCage = Inspekt::makeSuperCage();
    if (strtolower(trim($superCage->post->getRaw('captcha'))) == strtolower(trim($CONFIG[PSC_ANSWER_PREFIX . $superCage->post->getInt('captcha_id')]))) {
        return true;
    } else {
        return false;
    }
}


$thisplugin->add_filter('captcha_contact_print', 'psc_captcha_contact_print');
function psc_captcha_contact_print($captcha_print) {
    global $expand_array, $captcha_remark;
    $question = psc_get_random_question();
    $visibility = $expand_array['captcha_remark'] ? 'block' : 'none';
    $captcha_print = <<< EOT
    <tr>
        <td class="tableb" valign="top" align="right">
            {$question['text']}
        </td>
        <td class="tableb" valign="top">
            <input type="text" class="textinput" name="captcha" value="" />
            <input type="hidden" name="captcha_id" value="{$question['id']}" />
        </td>
        <td class="tableb">
            <span id="captcha_remark" style="display:{$visibility}">{$captcha_remark}</span>
        </td>
    </tr>
EOT;
    return $captcha_print;
}


$thisplugin->add_action('captcha_contact_validate', 'psc_captcha_contact_validate');
function psc_captcha_contact_validate() {
    if (!psc_check_captcha()) {
        global $CONFIG;
        require_once "./plugins/problem_solving_captcha/lang/english.php";
        if ($CONFIG['lang'] != 'english' && file_exists("./plugins/problem_solving_captcha/lang/{$CONFIG['lang']}.php")) {
            require_once "./plugins/problem_solving_captcha/lang/{$CONFIG['lang']}.php";
        }
        $GLOBALS['captcha_remark'] = $lang_plugin_problem_solving_captcha['incorrect_captcha'];
        $GLOBALS['expand_array'] = 'captcha_remark';
        $GLOBALS['error']++;
    }
}


//$thisplugin->add_filter('captcha_register_print', 'psc_captcha_register_print');
//$thisplugin->add_filter('captcha_comment_print', 'psc_captcha_comment_print');
//$thisplugin->add_filter('captcha_register_validate', 'psc_captcha_register_validate');
//$thisplugin->add_action('captcha_comment_validate', 'psc_captcha_comment_validate');

?>