<?php
/**************************************************
  Coppermine 1.5.x Plugin - edit_pic_views
  *************************************************
  Copyright (c) 2014 eenemeenemuu
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

global $CPG_PHP_SELF;
if (GALLERY_ADMIN_MODE && $CPG_PHP_SELF == 'editpics.php') {
    $thisplugin->add_filter('page_html', 'edit_pic_views_page_html');
    $thisplugin->add_action('after_edit_file', 'edit_pic_views_after_edit_file');
}

function edit_pic_views_page_html($html) {
    global $lang_editpics_php, $lang_common;

    // we need to replace the HTML code of video files first
    $replace = '<input type="hidden" name="pid[]" value="'."\\1".'" />';
    $replace .= sprintf($lang_editpics_php['pic_info_str'], '<input type="text" name="pwidth'."\\1".'" value="'."\\2".'" size="5" maxlength="5" class="textinput" />', '<input type="text" name="pheight'."\\1".'" value="'."\\3".'" size="5" maxlength="5" class="textinput" />', "\\4", '<input type="text" name="hits'."\\1".'" value="'."\\5".'" size="8" class="textinput" />', "\\6");
    $html = preg_replace('/<input type="hidden" name="pid\[\]" value="([0-9]+)" \/>[\s]*'.str_replace('%s', '([0-9]+)', str_replace('%s &times; %s', '<input type="text" name="pwidth[0-9]+" value="%s".*\/> &times; <input type="text" name="pheight[0-9]+" value="%s".*\/>', $lang_editpics_php['pic_info_str'])).'[\s]*<\/td>/Ui', $replace, $html);

    // non-movie files
    $replace = '<input type="hidden" name="pid[]" value="'."\\1".'" />';
    $replace .= sprintf($lang_editpics_php['pic_info_str'], "\\2", "\\3", "\\4", '<input type="text" name="hits'."\\1".'" value="'."\\5".'" size="8" class="textinput" />', "\\6");
    $html = preg_replace('/<input type="hidden" name="pid\[\]" value="([0-9]+)" \/>[\s]*'.str_replace('%s', '([0-9]+)', $lang_editpics_php['pic_info_str']).'[\s]*<\/td>/Ui', $replace, $html);

    return $html;
}

function edit_pic_views_after_edit_file($pid) {
    global $CONFIG;

    $hits = get_post_var('hits', $pid);
    if (is_numeric($hits) && $hits >= 0) {
        cpg_db_query("UPDATE {$CONFIG['TABLE_PICTURES']} SET hits = '$hits' WHERE pid = $pid LIMIT 1");
    }
}

?>