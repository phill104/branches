<?php
/**************************************************
  Coppermine 1.5.x Plugin - quick_tag
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

if (GALLERY_ADMIN_MODE && ($CPG_PHP_SELF == 'edit_one_pic.php' || $CPG_PHP_SELF == 'editpics.php')) {
    $thisplugin->add_filter('page_html', 'quick_tag_page_html');
}

function quick_tag_page_html($html) {
    global $CONFIG;

    $num_keywords = 10;

    $keywords_array = array();
    $keyword_count = array();
    $result = cpg_db_query("SELECT keywords FROM {$CONFIG['TABLE_PICTURES']} WHERE keywords <> ''");
    if (mysql_num_rows($result)) {
        while (list($keywords) = mysql_fetch_row($result)) {
            $array = explode($CONFIG['keyword_separator'], html_entity_decode($keywords));
            foreach($array as $word) {
                if (!trim($word)) {
                    continue;
                }
                if (!in_array($word = utf_strtolower($word), $keywords_array)) {
                    $keywords_array[] = $word;
                    $keyword_count[$word] = 1;
                } else {
                    $keyword_count[$word]++;
                }
            }
        }
        arsort($keyword_count);
    }

    $i = 0;
    $buttons = '';
    foreach ($keyword_count as $keyword => $count) {
        if ($i++ >= $num_keywords) {
            break;
        }
        $buttons .= "<span class=\"admin_menu\" style=\"white-space: nowrap;\" onclick=\"jQuery('#keywords\\1').focus(); jQuery('#keywords\\1').val(jQuery('#keywords\\1').val() + '{$CONFIG['keyword_separator']}$keyword{$CONFIG['keyword_separator']}');\">$keyword</span> ";
    }

    $html = preg_replace('/<input type="text" style="width: 100%" name="keywords([0-9]+)?".* \/>/U', "\\0<p></p>".$buttons, $html);

    return $html;
}

?>