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

if (!GALLERY_ADMIN_MODE) {
    cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
}

$superCage = Inspekt::makeSuperCage();

if ($superCage->post->keyExists('add')) {
    if (!checkFormToken()) {
        cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
    }

    if (trim($superCage->post->getRaw('question')) && trim($superCage->post->getRaw('answer'))) {
        $existing_ids = psc_get_question_ids();
        for ($i = 1; $i <= count($existing_ids) + 1; $i++) {
            if (!in_array($i, $existing_ids)) {
                break;
            }
        }
        cpg_db_query("INSERT INTO {$CONFIG['TABLE_CONFIG']} (name, value) VALUES('".PSC_QUESTION_PREFIX.$i."', '".trim($superCage->post->getEscaped('question'))."')");
        cpg_db_query("INSERT INTO {$CONFIG['TABLE_CONFIG']} (name, value) VALUES('".PSC_ANSWER_PREFIX.$i."', '".trim($superCage->post->getEscaped('answer'))."')");
        header("Location: index.php?file=problem_solving_captcha/admin");
    }
}

if ($superCage->get->keyExists('delete')) {
    if (!checkFormToken()) {
        cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
    }
    cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = '".PSC_QUESTION_PREFIX.$superCage->get->getInt('id')."'");
    cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = '".PSC_ANSWER_PREFIX.$superCage->get->getInt('id')."'");
    header("Location: index.php?file=problem_solving_captcha/admin");
}

require_once "./plugins/problem_solving_captcha/lang/english.php";
if ($CONFIG['lang'] != 'english' && file_exists("./plugins/problem_solving_captcha/lang/{$CONFIG['lang']}.php")) {
    require_once "./plugins/problem_solving_captcha/lang/{$CONFIG['lang']}.php";
}

pageheader($lang_plugin_problem_solving_captcha['problem_solving_captcha'].' - '.$lang_gallery_admin_menu['admin_lnk']);

list($timestamp, $form_token) = getFormToken();
echo "<form action=\"index.php?file=problem_solving_captcha/admin\" method=\"post\" name=\"custform\">";
starttable("100%", $lang_plugin_problem_solving_captcha['problem_solving_captcha'].' - '.$lang_gallery_admin_menu['admin_lnk'], 3);
echo <<< EOT
    <tr>
        <td class="tableb">
            <strong>Question</strong>
        </td>
        <td class="tableb">
            <strong>Answer</strong>
        </td>
    </tr>
    <tr>
        <td class="tableb">
            <button type="submit" class="button" name="add"><img src="images/icons/add.png" /></button>
            <input type="hidden" name="form_token" value="{$form_token}" />
            <input type="hidden" name="timestamp" value="{$timestamp}" />
            <input type="text" class="textinput" name="question" value="" />
        </td>
        <td class="tableb">
            <input type="text" class="textinput" name="answer" value="" />
        </td>
    </tr>
EOT;
foreach (psc_get_question_ids() as $id) {
    echo <<< EOT
        <tr>
            <td class="tableb">
                <a href="index.php?file=problem_solving_captcha/admin&amp;delete&amp;id={$id}&amp;form_token={$form_token}&amp;timestamp={$timestamp}"><img src="images/icons/delete.png" border="0" /></a>
                <a href="index.php?file=problem_solving_captcha/admin&amp;edit&amp;id={$id}&amp;form_token={$form_token}&amp;timestamp={$timestamp}"><img src="images/icons/edit.png" border="0" /></a>
                {$CONFIG[PSC_QUESTION_PREFIX.$id]}
            </td>
            <td class="tableb">
                {$CONFIG[PSC_ANSWER_PREFIX.$id]}
            </td>
        </tr>
EOT;
}
endtable();
echo "</form>";

pagefooter();

?>