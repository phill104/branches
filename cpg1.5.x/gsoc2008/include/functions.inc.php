<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2008 Dev Team
  v1.1 originally written by Gregory DEMAR

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 3
  as published by the Free Software Foundation.

  ********************************************
  Coppermine version: 1.5.0
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**********************************************/

/**
* Coppermine Photo Gallery functions.inc.php
*
* This file has almost all the functions of Coppermine
*
* @copyright 2002-2007 Gregory DEMAR, Coppermine Dev Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License V2
* @package Coppermine
* @version  $Id$
*/

/**
* get_meta_album_set_data()
*
* Get the entire album set based on the current category, this function is called recursively.
*
* ** Experimental, may cause sql problems on galleries with large numbers of albums.
*
* @param integer $cid Parent Category
* @param array $meta_album_set_array
* @return void
**/
function get_meta_album_set_data($cid,&$meta_album_set_array) //adapted from index.php get_subcat_data()
{
    global $CONFIG, $cat;

    if ($cid == USER_GAL_CAT) {
       $sql = "SELECT aid FROM {$CONFIG['TABLE_ALBUMS']} as a WHERE category>=" . FIRST_USER_CAT;
       $result = cpg_db_query($sql);
       $album_count = mysql_num_rows($result);
       while ($row = mysql_fetch_array($result)) {
           $meta_album_set_array[] = $row['aid'];
       } // while
       mysql_free_result($result);
    } else {
       $result = cpg_db_query("SELECT aid FROM {$CONFIG['TABLE_ALBUMS']} WHERE category = {$cid}");
       $album_count = mysql_num_rows($result);
       while ($row = mysql_fetch_array($result)) {
           $meta_album_set_array[] = $row['aid'];
       } // while

       mysql_free_result($result);
    }

    $result = cpg_db_query("SELECT cid FROM {$CONFIG['TABLE_CATEGORIES']} WHERE parent = '$cid'");

    if (mysql_num_rows($result) > 0) {
        $rowset = cpg_db_fetch_rowset($result);
        foreach ($rowset as $subcat) {
            if ($subcat['cid']) {
                get_meta_album_set_data($subcat['cid'], $meta_album_set_array);
            }
        }
    }
}

/**
* get_meta_album_set()
*
* Get the entire album set based on the current category.
*
* @param integer $cat Category
* @param array $meta_album_set_array
* @return void
**/
function get_meta_album_set($cat, &$meta_album_set)
{
    global $USER_DATA, $FORBIDDEN_SET_DATA, $CONFIG;

    if ($USER_DATA['can_see_all_albums'] && $cat == 0) {
        $meta_album_set ='';
    } elseif ($cat < 0) {
        $meta_album_set= 'AND aid IN (' . (- $cat) . ') ';
    } elseif ($cat > 0) {
       $meta_album_set_array=array();
        get_meta_album_set_data($cat,$meta_album_set_array);
        $meta_album_set_array = array_diff($meta_album_set_array,$FORBIDDEN_SET_DATA);

        if (count($meta_album_set_array)) {
            $meta_album_set = "AND aid IN (" . implode(',',$meta_album_set_array) . ") ";
        } else {
            $meta_album_set = "AND aid IN (-1) ";
        }
     } else {
      $result = cpg_db_query("SELECT aid FROM {$CONFIG['TABLE_ALBUMS']}");
        $album_count = mysql_num_rows($result);
        while ($row = mysql_fetch_array($result)) {
           $meta_album_set_array[] = $row['aid'];
        }
        mysql_free_result($result);
        $meta_album_set_array = array_diff($meta_album_set_array,$FORBIDDEN_SET_DATA);

        if (count($meta_album_set_array)) {
            $meta_album_set = "AND aid IN (" . implode(',',$meta_album_set_array) . ") ";
        } else {
            $meta_album_set = "AND aid IN (-1) ";
        }
     }
}


/**
 * user_get_profile()
 *
 * Decode the user profile contained in a cookie
 *
 **/

function user_get_profile()
{
        global $CONFIG, $USER;

        $superCage = Inspekt::makeSuperCage();

        /**
         * TODO: Use the md5 # to verify integrity of cookie string
         * At the time of installation we write a randonmly generated secret salt in config.inc
         * This secret salt will be appended to the encoded string and the resulting md5 # of this string will
         * be appended to the encoded string with @ separator
         * e.g. $encoded_string_with_md5 = "asdfkhasdf987we89rfadfjhasdfklj@^@".md5("asdfkhasdf987we89rfadfjhasdfklj".$secret_salt)
         */
        if ($superCage->cookie->keyExists($CONFIG['cookie_name'].'_data')) {
            $USER = @unserialize(@base64_decode($superCage->cookie->getRaw($CONFIG['cookie_name'].'_data')));
        }

        if (!isset($USER['ID']) || strlen($USER['ID']) != 32) {
                list($usec, $sec) = explode(' ', microtime());
                $seed = (float) $sec + ((float) $usec * 100000);
                srand($seed);
                $USER=array('ID' => md5(uniqid(rand(),1)));
        } else {
                $USER['ID'] = addslashes($USER['ID']);
        }

        if (!isset($USER['am'])) {
            $USER['am'] = 1;
        }
}

// Save the user profile in a cookie


/**
 * user_save_profile()
 *
 * Save the user profile in a cookie
 *
 **/

function user_save_profile()
{
        global $CONFIG, $USER;

        /**
         * TODO: Use the md5 # to verify integrity of cookie string
         * At the time of installation we write a randonmly generated secret salt in config.inc
         * This secret salt will be appended to the encoded string and the resulting md5 # of this string will
         * be appended to the encoded string with @ separator
         * e.g. $encoded_string_with_md5 = "asdfkhasdf987we89rfadfjhasdfklj@^@".md5("asdfkhasdf987we89rfadfjhasdfklj".$secret_salt)
         */
        $data = base64_encode(serialize($USER));
        setcookie($CONFIG['cookie_name'].'_data', $data, time()+86400*30, $CONFIG['cookie_path']);
}

/**************************************************************************
   Database functions
 **************************************************************************/

// Connect to the database

/**
 * cpg_db_connect()
 *
 * Connect to the database
 **/

function cpg_db_connect()
{
        global $CONFIG;
        $result = @mysql_connect($CONFIG['dbserver'], $CONFIG['dbuser'], $CONFIG['dbpass']);
        if (!$result) {
                return false;
        }
        if (!mysql_select_db($CONFIG['dbname']))
                return false;
        return $result;
}

// Perform a database query

/**
 * cpg_db_query()
 *
 * Perform a database query
 *
 * @param $query
 * @param integer $link_id
 * @return
 **/

function cpg_db_query($query, $link_id = 0)
{
        global $CONFIG, $query_stats, $queries;

        $query_start = cpgGetMicroTime();

        if ($link_id) {
                                $result = mysql_query($query, $link_id);
        } else {
                                $result = mysql_query($query, $CONFIG['LINK_ID']);
        }
        $query_end = cpgGetMicroTime();
        if (isset($CONFIG['debug_mode']) && (($CONFIG['debug_mode']==1) || ($CONFIG['debug_mode']==2) )) {
                $duration = round($query_end - $query_start, 3);
                $query_stats[] = $duration;
                $queries[] = "$query ({$duration}s)";
        }
        if (!$result) cpg_db_error("While executing query \"$query\" on $link_id");

        return $result;
}

// Error message if a query failed


/**
 * cpg_db_error()
 *
 * Error message if a query failed
 *
 * @param $the_error
 * @return
 **/

function cpg_db_error($the_error)
{
        global $CONFIG,$lang_errors;

        if ($CONFIG['debug_mode'] === '0' || (!GALLERY_ADMIN_MODE)) {
            cpg_die(CRITICAL_ERROR, $lang_errors['database_query'], __FILE__, __LINE__);
        } else {
                $the_error .= "\n\nmySQL error: ".mysql_error()."\n";
                $out = "<br />".$lang_errors['database_query'].".<br /><br/>
                    <form name=\"mysql\" id=\"mysql\"><textarea rows=\"8\" cols=\"60\">".htmlspecialchars($the_error)."</textarea></form>";
            cpg_die(CRITICAL_ERROR, $out, __FILE__, __LINE__);
        }
}

// Fetch all rows in an array

/**
 * cpg_db_fetch_rowset()
 *
 * Fetch all rows in an array
 *
 * @param $result
 * @return
 **/

function cpg_db_fetch_rowset($result)
{
        $rowset = array();

        while ($row = mysql_fetch_array($result)) $rowset[] = $row;

        return $rowset;
}

/**
 * cpg_db_fetch_row()
 *
 * Fetch row in an array
 *
 * @param $result
 * @return
 **/

function cpg_db_fetch_row($result)
{

        $row = mysql_fetch_array($result);

        return $row;
}

/**
 * cpg_db_last_insert_id()
 *
 * Get the last inserted id of a query
 *
 * @return integer $id
 **/

function cpg_db_last_insert_id()
{
        return mysql_insert_id();
}

/**************************************************************************
   Sanitization functions
 **************************************************************************/

/**
 * cpgSanitizeUserTextInput()
 *
 * Function to sanitize the data which cannot be directly sanitized with Inspekt
 *
 * @param string $string
 * @return string Return sanitized data
 */
function cpgSanitizeUserTextInput($string)
{
        //TODO: Add some sanitization code
    return $string;
}

/**************************************************************************
   Utilities functions
 **************************************************************************/

// Replacement for the die function

/**
 * cpg_die()
 *
 * Replacement for the die function
 *
 * @param $msg_code
 * @param $msg_text
 * @param $error_file
 * @param $error_line
 * @param boolean $output_buffer
 * @return
 **/

function cpg_die($msg_code, $msg_text,  $error_file, $error_line, $output_buffer = false)
{
        global $CONFIG, $lang_cpg_die, $template_cpg_die, $lang_common;

        if (defined('API_CALL')) {
                die("<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n" .
                '<api_error>' . $msg_text . '</api_error>');
        }

        // Simple output if theme file is not loaded
        if(!function_exists('pageheader')){
                echo 'Fatal error :<br />'.$msg_text;
                exit;
        }

        $ob = ob_get_contents();
        if ($ob) ob_end_clean();

        if (function_exists('theme_cpg_die'))
        {
            theme_cpg_die($msg_code, $msg_text,  $error_file, $error_line, $output_buffer);
            return;
        }

        $params = array(
                '{MESSAGE}' => $msg_text,
                '{FILE_TXT}' => $lang_cpg_die['file'],
                '{FILE}' => $error_file,
                '{LINE_TXT}' => $lang_cpg_die['line'],
                '{LINE}' => $error_line,
                '{OUTPUT_BUFFER}' => $ob,
        );

        if (!($CONFIG['debug_mode'] == 1 || ($CONFIG['debug_mode'] == 2 && GALLERY_ADMIN_MODE))) template_extract_block($template_cpg_die, 'file_line');
        //if(!$output_buffer && !$CONFIG['debug_mode'])
        template_extract_block($template_cpg_die, 'output_buffer');

        pageheader($lang_cpg_die[$msg_code]);
        starttable(-1, $lang_cpg_die[$msg_code]);echo "<!-- cpg_die -->";
        echo template_eval($template_cpg_die, $params);
        endtable();
        pagefooter();
        exit;
}

// Display a localised date

/**
 * localised_date()
 *
 * Display a localised date
 *
 * @param integer $timestamp
 * @param $datefmt
 * @return
 **/

function localised_date($timestamp = -1, $datefmt)
{
        global $lang_month, $lang_day_of_week, $CONFIG;

        $timestamp = localised_timestamp($timestamp);

    $date = ereg_replace('%[aA]', $lang_day_of_week[(int)strftime('%w', $timestamp)], $datefmt);
    $date = ereg_replace('%[bB]', $lang_month[(int)strftime('%m', $timestamp)-1], $date);

    return strftime($date, $timestamp);
}

/**
 * localised_timestamp()
 *
 * Display a localised timestamp
 *
 * @return
 **/
function localised_timestamp($timestamp = -1)
{
        global $CONFIG;

        if ($timestamp == -1) {
                $timestamp = time();
        }

        $diff_to_GMT = date("O") / 100;

        $timestamp += ($CONFIG['time_offset'] - $diff_to_GMT) * 3600;

        return $timestamp;
}

// Function to create correct URLs for image name with space or exotic characters

/**
 * path2url()
 *
 * Function to create correct URLs for image name with space or exotic characters
 *
 * @param $path
 * @return
 **/

function path2url($path)
{
        return str_replace("%2F","/",rawurlencode($path));
}

// Display a 'message box like' table

/**
 * msg_box()
 *
 * Display a 'message box like' table
 *
 * @param $title
 * @param $msg_text
 * @param string $button_text
 * @param string $button_link
 * @param string $width
 * @return
 **/

function msg_box($title, $msg_text, $button_text="", $button_link="", $width="-1")
{
    if (function_exists('theme_msg_box'))
    {
        theme_msg_box($title, $msg_text, $button_text, $button_link, $width);
        return;
    }
        global $template_msg_box;

        if (!$button_text) {
            template_extract_block($template_msg_box, 'button');
        }

        $params = array(
                '{MESSAGE}' => $msg_text,
                '{LINK}' => $button_link,
                '{TEXT}' => $button_text
        );

        starttable($width, $title);
        echo template_eval($template_msg_box, $params);
        endtable();
}


/**
 * create_tabs()
 *
 * @param $items
 * @param $curr_page
 * @param $total_pages
 * @param $template
 * @return
 **/

function create_tabs($items, $curr_page, $total_pages, $template)
{
        global $CONFIG, $lang_create_tabs;

        // TO-DO: Need to add theme_create_tabs() to sample/theme.php
        // Maybe this function create_tabs() should be moved to themes.inc.php and renamed theme_create_tabs()?
        if (function_exists('theme_create_tabs')) {
            return theme_create_tabs($items, $curr_page, $total_pages, $template);
        }

        // Code for future: to implement 'previous' and 'next' tabs
        // Everything is set - just need to put in correct place and use correct prev & next page numbers
        // $tabs .= strtr( sprintf($template['inactive_prev_tab'],#PREV_PAGE_NUMBER#) , array('{PREV}' => $lang_create_tabs['previous']) );
        // $tabs .= strtr( sprintf($template['inactive_next_tab'],#NEXT_PAGE_NUMBER#) , array('{NEXT}' => $lang_create_tabs['next']) );

        $maxTab = $CONFIG['max_tabs'];

        $tabs = sprintf($template['left_text'], $items, $total_pages);
        if (($total_pages == 1)) return $tabs;

        $tabs .= $template['tab_header'];
        if ($curr_page == 1) {
                $tabs .= sprintf($template['active_tab'], 1);
        } else {
                $tabs .= sprintf($template['inactive_tab'], 1, 1);
        }
        if ($total_pages > $maxTab){
                $start = max(2, $curr_page - floor(($maxTab -2)/2));
                $start = min($start, $total_pages - $maxTab +2);
                $end = $start + $maxTab -3;
        } else {
                $start = 2;
                $end = $total_pages-1;
        }
        for ($page = $start ; $page <= $end; $page++) {
                if ($page == $curr_page) {
                        $tabs .= sprintf($template['active_tab'], $page);
                } else {
                        $tabs .= sprintf($template['inactive_tab'], $page, $page);
                }
        }
        if ($total_pages > 1){
                if ($curr_page == $total_pages) {
                        $tabs .= sprintf($template['active_tab'], $total_pages);
                } else {
                        $tabs .= sprintf($template['inactive_tab'], $total_pages, $total_pages);
                }
        }
        return $tabs.$template['tab_trailer'];
}

/**
 * Rewritten by Nathan Codding - Feb 6, 2001. Taken from phpBB code
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 *         to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 *         to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *                to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 */

/**
 * make_clickable()
 *
 * @param $text
 * @return
 **/

function make_clickable($text)
{
        $ret = ' '.$text;
        $ret = preg_replace("#([\n ])([a-z]+?)://([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)#i", "\\1<a href=\"\\2://\\3\" rel=\"external\">\\2://\\3</a>", $ret);
        $ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]*)?)#i", "\\1<a href=\"http://www.\\2.\\3\\4\" rel=\"external\">www.\\2.\\3\\4</a>", $ret);
        $ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)?[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
        return substr($ret, 1);
}

// Allow the use of a limited set of phpBB bb codes in albums and image descriptions
// Taken from phpBB code

/**
 * bb_decode()
 *
 * @param $text
 * @return
 **/

function bb_decode($text)
{
        $text = nl2br($text);

        static $bbcode_tpl = array();
        static $patterns = array();
        static $replacements = array();

        // First: If there isn't a "[" and a "]" in the message, don't bother.
        if ((strpos($text, "[") === false || strpos($text, "]") === false))
        {
                return $text;
        }

        // [b] and [/b] for bolding text.
        $text = str_replace("[b]", '<b>', $text);
        $text = str_replace("[/b]", '</b>', $text);

        // [u] and [/u] for underlining text.
        $text = str_replace("[u]", '<u>', $text);
        $text = str_replace("[/u]", '</u>', $text);

        // [i] and [/i] for italicizing text.
        $text = str_replace("[i]", '<i>', $text);
        $text = str_replace("[/i]", '</i>', $text);

        // colours
        $text = preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+)\]/", '<span style="color:$1">', $text);
        $text = str_replace("[/color]", '</span>', $text);

        // [i] and [/i] for italicizing text.
        //$text = str_replace("[i:$uid]", $bbcode_tpl['i_open'], $text);
        //$text = str_replace("[/i:$uid]", $bbcode_tpl['i_close'], $text);

        if (!count($bbcode_tpl)) {
                                // We do URLs in several different ways..
                $bbcode_tpl['url']  = '<span class="bblink"><a href="{URL}" %s>{DESCRIPTION}</a></span>';

                $bbcode_tpl['url1'] = str_replace('{URL}', '\\1\\2', $bbcode_tpl['url']);
                $bbcode_tpl['url1'] = str_replace('{DESCRIPTION}', '\\1\\2', $bbcode_tpl['url1']);

                                // [url]xxxx://www.phpbb.com[/url] code..
                $patterns['link'][1] = "#\[url\]([a-z]+?://){1}([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\[/url\]#si";
                $replacements['link'][1] = $bbcode_tpl['url1'];
                                $text = check_link_type_and_replace($patterns['link'][1], $replacements['link'][1], $text, 1);

                $bbcode_tpl['url2'] = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
                $bbcode_tpl['url2'] = str_replace('{DESCRIPTION}', '\\1', $bbcode_tpl['url2']);

                                // [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
                $patterns['link'][2] = "#\[url\]([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\[/url\]#si";
                $replacements['link'][2] = $bbcode_tpl['url2'];
                                $text = check_link_type_and_replace($patterns['link'][2], $replacements['link'][2], $text, 2);

                $bbcode_tpl['url3'] = str_replace('{URL}', '\\1\\2', $bbcode_tpl['url']);
                $bbcode_tpl['url3'] = str_replace('{DESCRIPTION}', '\\3', $bbcode_tpl['url3']);

                                // [url=xxxx://www.phpbb.com]phpBB[/url] code..
                $patterns['link'][3] = "#\[url=([a-z]+?://){1}([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\](.*?)\[/url\]#si";
                $replacements['link'][3] = $bbcode_tpl['url3'];
                                $text = check_link_type_and_replace($patterns['link'][3], $replacements['link'][3], $text, 3);

                $bbcode_tpl['url4'] = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
                $bbcode_tpl['url4'] = str_replace('{DESCRIPTION}', '\\2', $bbcode_tpl['url4']);

                                // [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
                $patterns['link'][4] = "#\[url=([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\](.*?)\[/url\]#si";
                $replacements['link'][4] = $bbcode_tpl['url4'];
                                $text = check_link_type_and_replace($patterns['link'][4], $replacements['link'][4], $text, 4);


                                $bbcode_tpl['email']= '<span class="bblink"><a href="mailto:{EMAIL}">{EMAIL}</a></span>';
                $bbcode_tpl['email'] = str_replace('{EMAIL}', '\\1', $bbcode_tpl['email']);

                                // [email]user@domain.tld[/email] code..
                $patterns['other'][1] = "#\[email\]([a-z0-9\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#si";
                $replacements['other'][1] = $bbcode_tpl['email'];

                // [img]xxxx://www.phpbb.com[/img] code..
                $bbcode_tpl['img']  = '<img src="{URL}" alt="" />';
                $bbcode_tpl['img']  = str_replace('{URL}', '\\1\\2', $bbcode_tpl['img']);

                $patterns['other'][2] = "#\[img\]([a-z]+?://){1}([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\[/img\]#si";
                $replacements['other'][2] = $bbcode_tpl['img'];

        }

        $text = preg_replace($patterns['other'], $replacements['other'], $text);

        return $text;
}

/**
* check_link_type_and_replace()
*
* Checks if the text contains this pattern and replace it accordingly
*
* @param string $pattern
* @param string $replacement
* @param string $text
* @param integer $stage
*
* @return string $text
*/
function check_link_type_and_replace ($pattern, $replacement, $text, $stage) {
        $ext_rel = 'rel="external nofollow" onclick="window.open(this.href); return false;" onkeypress="window.open(this.href); return false;"';
        $int_rel = '';

        if (preg_match($pattern, $text, $url) != 0) {
                switch ($stage) {
                        case 1:
                        case 3:
                                $url = $url[1] . $url[2];
                                break;
                        case 2:
                                $url = $url[1];
                                break;
                        case 4:
                                $url = 'http://' . $url[1];
                }
                if (is_link_local($url)) {
                        //apply regular formatting
                        $replacement_sprintfed = sprintf($replacement, $int_rel);
                } else {
                        //add rel attribute to link
                        $replacement_sprintfed = sprintf($replacement, $ext_rel);
                }

                $text = preg_replace($pattern, $replacement_sprintfed, $text, 1);
                $text = check_link_type_and_replace ($pattern, $replacement, $text, $stage);
        }

        return $text;
}

/**
* is_link_local()
*
* Determines if a URL is local or external. FROM phpBB MOD: prime links (by Ken F. Innes IV)
*
* @param string $url
* @param boolean $cpg_url
*
* @return boolean $is_local
*/
function is_link_local($url, $cpg_url = false)
{
        if ($cpg_url === false)
        {
                $cpg_url = generate_cpg_url();
        }
        $subdomain_remove_regex = '#^(http|https)://[^/]+?\.((?:[a-z0-9-]+\.[a-z]+)|localhost/)#i';
        $cpg_url = preg_replace($subdomain_remove_regex, '$1://$2', $cpg_url);
        $url          = preg_replace($subdomain_remove_regex, '$1://$2', $url);

        $is_local = (strpos($url, $cpg_url) === 0);
        if (!$is_local)
        {
                $protocol = substr($url, 0, strpos($url, ':'));
                $is_local = !$protocol || ($protocol && !in_array($protocol, array('http', 'https', 'mailto', 'ftp', 'gopher')));
        }
        return($is_local);
}


/**
* generate_cpg_url()
*
* Generate board url (example: http://www.foo.bar/cpg) FROM PHPBB 3.0.0
*
* @return string $cpg_url
*/
function generate_cpg_url()
{
                $superCage = Inspekt::makeSuperCage();
                $server_name = $superCage->server->keyExists('server_name') ? $superCage->server->getRaw('server_name') : getenv('SERVER_NAME');
                $server_port = $superCage->server->keyExists('server_port') ? $superCage->server->getRaw('server_port') : getenv('SERVER_PORT');

        // Do not rely on cookie_secure, users seem to think that it means a secured cookie instead of an encrypted connection
        $cookie_secure = ($superCage->server->keyExists('HTTPS') && $superCage->server->getAlpha('HTTPS') == 'on') ? 1 : 0;
        $cpg_url = (($cookie_secure) ? 'https://' : 'http://') . $server_name;


        if ($server_port && $cookie_secure)
        {
                $cpg_url .= ':' . $server_port;
        }

        // Strip / from the end
        if (substr($cpg_url, -1, 1) == '/')
        {
                $cpg_url = substr($cpg_url, 0, -1);
        }

        return $cpg_url;
}

/**************************************************************************
   Template functions
 **************************************************************************/

// Load and parse the template.html file

/**
 * load_template()
 *
 * Load and parse the template.html file
 *
 * @return
 **/

function load_template()
{
        global $THEME_DIR, $CONFIG, $template_header, $template_footer;

        if (file_exists($THEME_DIR . TEMPLATE_FILE)) {
            $template_file = $THEME_DIR . TEMPLATE_FILE;
        } else die("Coppermine critical error:<br />Unable to load template file ".TEMPLATE_FILE."!");

        $template = fread(fopen($template_file, 'r'), filesize($template_file));

        $template = CPGPluginAPI::filter('template_html',$template);

        $gallery_pos = strpos($template, '{LANGUAGE_SELECT_FLAGS}');
        $template = str_replace('{LANGUAGE_SELECT_FLAGS}', languageSelect('flags') ,$template);
        $gallery_pos = strpos($template, '{LANGUAGE_SELECT_LIST}');
        $template = str_replace('{LANGUAGE_SELECT_LIST}', languageSelect('list') ,$template);
        $gallery_pos = strpos($template, '{THEME_DIR}');
        $template = str_replace('{THEME_DIR}', $THEME_DIR ,$template);
        $gallery_pos = strpos($template, '{THEME_SELECT_LIST}');
        $template = str_replace('{THEME_SELECT_LIST}', themeSelect('list') ,$template);
        $gallery_pos = strpos($template, '{SOCIAL_BOOKMARKS}');
        $template = str_replace('{SOCIAL_BOOKMARKS}', cpgSocialBookmark() ,$template);
        $gallery_pos = strpos($template, '{GALLERY}');
        if (!strstr($template, '{CREDITS}')) {
            $template = str_replace('{GALLERY}', '{CREDITS}' ,$template);
        } else {
            $template = str_replace('{GALLERY}', '' ,$template);
        }

        $template_header = substr($template, 0, $gallery_pos);
        $template_header = str_replace('{META}','{META}'.CPGPluginAPI::filter('page_meta',''),$template_header);

        // Filter gallery header and footer
        $template_header .= CPGPluginAPI::filter('gallery_header','');
        $template_footer = CPGPluginAPI::filter('gallery_footer','').substr($template, $gallery_pos);

        $add_version_info = "<!--Coppermine Photo Gallery ".COPPERMINE_VERSION." (".COPPERMINE_VERSION_STATUS.")-->\n</body>";
        $template_footer = ereg_replace("</body[^>]*>",$add_version_info,$template_footer);
}

// Eval a template (substitute vars with values)

/**
 * template_eval()
 *
 * @param $template
 * @param $vars
 * @return
 **/

function template_eval(&$template, &$vars)
{
        return strtr($template, $vars);
}


// Extract and return block '$block_name' from the template, the block is replaced by $subst

/**
 * template_extract_block()
 *
 * @param $template
 * @param $block_name
 * @param string $subst
 * @return
 **/

function template_extract_block(&$template, $block_name, $subst='')
{
        $pattern = "#(<!-- BEGIN $block_name -->)(.*?)(<!-- END $block_name -->)#s";
        if ( !preg_match($pattern, $template, $matches)){
                die('<b>Template error<b><br />Failed to find block \''.$block_name.'\'('.htmlspecialchars($pattern).') in :<br /><pre>'.htmlspecialchars($template).'</pre>');
        }
        $template = str_replace($matches[1].$matches[2].$matches[3], $subst, $template);
        return $matches[2];
}

/**************************************************************************
   Functions for album/picture management
 **************************************************************************/

// Get the list of albums that the current user can't see

/**
 * get_private_album_set()
 *
 * @param string $aid_str
 * @return
 **/

function get_private_album_set($aid_str="")
{
        if (GALLERY_ADMIN_MODE) return;

        global $CONFIG, $ALBUM_SET, $USER_DATA, $FORBIDDEN_SET, $FORBIDDEN_SET_DATA;
        $superCage = Inspekt::makeSuperCage();

        $FORBIDDEN_SET_DATA = array();

        if ($USER_DATA['can_see_all_albums']) return;

        //Stuff for Album level passwords
        if ($superCage->cookie->keyExists($CONFIG['cookie_name']."_albpw") && empty($aid_str)) {

          //Using getRaw(). The data is sanitized in the foreach running just below
          $tmpStr = $superCage->cookie->getRaw($CONFIG['cookie_name']."_albpw");
          $alb_pw = unserialize(stripslashes($tmpStr));

          foreach($alb_pw as $aid => $value) {
            $aid_str .= (int)$aid . ",";
          }

          $aid_str = substr($aid_str, 0, -1);

          $sql = "SELECT aid, MD5(alb_password) as md5_password FROM ".$CONFIG['TABLE_ALBUMS']." WHERE aid IN ($aid_str)";
          $result = cpg_db_query($sql);
          $albpw_db = array();
          if (mysql_num_rows($result)) {
            while ($data = mysql_fetch_array($result)) {
              $albpw_db[$data['aid']] = $data['md5_password'];
            }
          }
          $valid = array_intersect($albpw_db, $alb_pw);
          if (is_array($valid)) {
            $aid_str = implode(",",array_keys($valid));
          } else {
            $aid_str = "";
          }
        }

        $sql = "SELECT aid FROM {$CONFIG['TABLE_ALBUMS']} WHERE visibility != '0' AND visibility !='".(FIRST_USER_CAT + USER_ID)."' AND visibility NOT IN ".USER_GROUP_SET;
        if (!empty($aid_str)) {
          $sql .= " AND aid NOT IN ($aid_str)";
                }

                $result = cpg_db_query($sql);
        if ((mysql_num_rows($result))) {
                $set ='';
            while($album=mysql_fetch_array($result)){
                    $set .= $album['aid'].',';
                    $FORBIDDEN_SET_DATA[] = $album['aid'];
            } // while
                $FORBIDDEN_SET = "p.aid NOT IN (".substr($set, 0, -1).') ';
                $ALBUM_SET = 'AND aid NOT IN ('.substr($set, 0, -1).') ';
        }else{
                  $FORBIDDEN_SET_DATA = array();
                  $FORBIDDEN_SET = "";
                  $ALBUM_SET = "";
        }
        mysql_free_result($result);
}

// Generate the thumbnail caption based on admin preference and thumbnail page requirements

/**
 * build_caption()
 *
 * @param array $rowset by reference
 * @param array $must_have
 **/
function build_caption(&$rowset,$must_have=array())
{
    global $CONFIG, $THEME_DIR;
    global $album_date_fmt, $lastcom_date_fmt, $lastup_date_fmt, $lasthit_date_fmt, $cat;
    global $lang_get_pic_data, $lang_meta_album_names, $lang_errors;

    foreach ($rowset as $key => $row) {
        $caption='';
        if ($CONFIG['display_filename']) {
          $caption .='<span class="thumb_filename">' . $row['filename'] . '</span>';
        }

        $caption .= ($row['title']) ? '<span class="thumb_title">' . $row['title'] . '</span>' : '';

        if ($CONFIG['views_in_thumbview'] || in_array('hits',$must_have)) {
            $caption .= '<span class="thumb_title">' . sprintf($lang_get_pic_data['n_views'], $row['hits']).'</span>';
        }
        if ($CONFIG['caption_in_thumbview']){
            $caption .= $row['caption'] ? "<span class=\"thumb_caption\">".strip_tags(bb_decode($row['caption']))."</span>" : '';
        }
        if ($CONFIG['display_comment_count']) {
            $comments_nr = count_pic_comments($row['pid']);
            if ($comments_nr > 0) {
                $caption .= "<span class=\"thumb_num_comments\">".sprintf($lang_get_pic_data['n_comments'], $comments_nr )."</span>";
            }
        }
        if ($CONFIG['display_uploader'] /*&& !in_array($row['owner_id'],$CONFIG['ADMIN_USERS']) *|| ($CONFIG['display_admin_uploader'] && in_array($row['owner_id'],$CONFIG['ADMIN_USERS']))*/) {
            $caption .= ($row['owner_id'] && $row['owner_name']) ? '<span class="thumb_title"><a href ="profile.php?uid='.$row['owner_id'].'">'.$row['owner_name'].'</a></span>' : '';
        }

        if (in_array('msg_date',$must_have)) {
            $caption .= '<span class="thumb_caption">'.localised_date($row['msg_date'], $lastcom_date_fmt).'</span>';
        }
        if (in_array('msg_body',$must_have)) {
            $msg_body = strip_tags(bb_decode($row['msg_body'])); // I didn't want to fully bb_decode the message where report to admin isn't available. -donnoman
            $msg_body = utf_strlen($msg_body) > 50 ? utf_substr($msg_body,0,50).'...': $msg_body;
            if ($CONFIG['enable_smilies']) $msg_body = process_smilies($msg_body);
            if ($row['author_id']) {
                $caption .= '<span class="thumb_caption"><a href ="profile.php?uid='.$row['author_id'].'">'.$row['msg_author'].'</a>: '.$msg_body.'</span>';
            } else {
                    $caption .= '<span class="thumb_caption">'.$row['msg_author'].': '.$msg_body.'</span>';
            }
        }
        if (in_array('ctime',$must_have)) {
            $caption .= '<span class="thumb_caption">'.localised_date($row['ctime'], $lastup_date_fmt).'</span>';
        }
        if (in_array('pic_rating',$must_have)) {
            if (defined('THEME_HAS_RATING_GRAPHICS')) {
                $prefix= $THEME_DIR;
            } else {
                $prefix= '';
            }
			//calculate required amount of stars in picinfo
			$i = 1;
			$rating = round(($row['pic_rating'] / 2000) / (5/$CONFIG['rating_stars_amount']));
			$rating_images = '';
			while($i <= $CONFIG['rating_stars_amount']){
				$i++;
				if($i <= $rating){
					$rating_images .= '<img src="' . $prefix . 'images/rate_full.gif" alt="' . $rating . '"/>';
				}else{
					$rating_images .= '<img src="' . $prefix . 'images/rate_empty.gif" alt="' . $rating . '"/>';
				}
			}
            $caption .= "<span class=\"thumb_caption\">". $rating_images .'<br />'.sprintf($lang_get_pic_data['n_votes'], $row['votes']).'</span>';
        }
        if (in_array('mtime',$must_have)) {
                $caption .= "<span class=\"thumb_caption\">".localised_date($row['mtime'], $lasthit_date_fmt);
                if (GALLERY_ADMIN_MODE) {
                  $caption .="<br/>".$row['lasthit_ip'];
                }
                $caption .='</span>';
        }

        $rowset[$key]['caption_text'] = $caption;
    }
    $rowset = CPGPluginAPI::filter('thumb_caption',$rowset);
}

// Retrieve the data for a picture or a set of picture

/**
 * get_pic_data()
 *
 * @param $album
 * @param $count
 * @param $album_name
 * @param integer $limit1
 * @param integer $limit2
 * @param boolean $set_caption
 * @return
 **/

function get_pic_data($album, &$count, &$album_name, $limit1=-1, $limit2=-1, $set_caption = true) {
        global $USER, $CONFIG, $ALBUM_SET, $META_ALBUM_SET, $CURRENT_CAT_NAME, $CURRENT_ALBUM_KEYWORD, $HTML_SUBST, $THEME_DIR, $FAVPICS, $FORBIDDEN_SET_DATA, $USER_DATA, $lang_common;
        global $album_date_fmt, $lastcom_date_fmt, $lastup_date_fmt, $lasthit_date_fmt, $cat;
        global $lang_get_pic_data, $lang_meta_album_names, $lang_errors;

        $superCage = Inspekt::makeSuperCage();

        $sort_array = array(
          'na' => 'filename ASC',
          'nd' => 'filename DESC',
          'ta'=>'title ASC',
          'td'=>'title DESC',
          'da' => 'pid ASC',
          'dd' => 'pid DESC',
          'pa' => 'position ASC',
          'pd' => 'position DESC',
        );
		
        $sort_code = isset($USER['sort'])? $USER['sort'] : $CONFIG['default_sort_order'];
        $sort_order = isset($sort_array[$sort_code]) ? $sort_array[$sort_code] : $sort_array[$CONFIG['default_sort_order']];
        $limit = ($limit1 != -1) ? ' LIMIT '. $limit1 : '';
        $limit .= ($limit2 != -1) ? ' ,'. $limit2 : '';


        if ($limit2 == 1) {
            $select_columns = '*';
        } else {
            $select_columns = 'pid, filepath, filename, url_prefix, filesize, pwidth, pheight, ctime, aid, keywords, title';
        }

        if(count($FORBIDDEN_SET_DATA) > 0 ){
            $forbidden_set_string =" AND aid NOT IN (".implode(",", $FORBIDDEN_SET_DATA).")";
        } else {
            $forbidden_set_string = '';
        }

        // Keyword
        if (!empty($CURRENT_ALBUM_KEYWORD)){
                $keyword = "OR (keywords like '%$CURRENT_ALBUM_KEYWORD%' $forbidden_set_string )";
        } else $keyword = '';

        // Regular albums
        if ((is_numeric($album))) {
                $album_name_keyword = get_album_name($album);
                $album_name = $album_name_keyword['title'];
                $album_keyword = addslashes($album_name_keyword['keyword']);

                if (!empty($album_keyword)) {
                        $keyword = "OR (keywords like '%$album_keyword%' $forbidden_set_string )";
                } else {
                  $keyword = '';
                }

                if (is_array($USER_DATA['allowed_albums']) && in_array($album,$USER_DATA['allowed_albums'])) {
                  $approved = '';
                } else {
                  $approved = GALLERY_ADMIN_MODE ? '' : 'AND approved=\'YES\'';
                }

                $approved = GALLERY_ADMIN_MODE ? '' : 'AND approved=\'YES\'';

                $query = "SELECT COUNT(pid) from {$CONFIG['TABLE_PICTURES']} WHERE ((aid='$album' $forbidden_set_string ) $keyword) $approved $ALBUM_SET";
                $result = cpg_db_query($query);
                $nbEnr = mysql_fetch_array($result);
                $count = $nbEnr[0];
                mysql_free_result($result);

                if($select_columns != '*') $select_columns .= ', title, caption,hits,owner_id,owner_name,pic_rating,votes';
                $query = "SELECT $select_columns from {$CONFIG['TABLE_PICTURES']} WHERE ((aid='$album' $forbidden_set_string ) $keyword) $approved $ALBUM_SET ORDER BY $sort_order $limit";
		
                $result = cpg_db_query($query);

                $rowset = cpg_db_fetch_rowset($result);
                mysql_free_result($result);
                // Set picture caption
                if ($CONFIG['display_thumbnail_rating'] == 1) {
                  if ($set_caption) build_caption($rowset, array('pic_rating'));
                } else {
                  if ($set_caption) build_caption($rowset);
                }


        $rowset = CPGPluginAPI::filter('thumb_caption_regular',$rowset);

                return $rowset;
        }


        // Meta albums
        switch($album){
        case 'lastcom': // Last comments
                if ($META_ALBUM_SET && $CURRENT_CAT_NAME) {
                        $album_name = $album_name = $lang_meta_album_names['lastcom'].' - '. $CURRENT_CAT_NAME;
                } else {
                        $album_name = $lang_meta_album_names['lastcom'];
                }

                // Replacing the AND in ALBUM_SET with AND (
                if($META_ALBUM_SET){
                        $TMP_SET = "AND (" . substr($META_ALBUM_SET, 3);
                }else{
                        $TMP_SET = "AND (1";
                }

                $query = "SELECT COUNT({$CONFIG['TABLE_PICTURES']}.pid) from {$CONFIG['TABLE_COMMENTS']}, {$CONFIG['TABLE_PICTURES']}  WHERE {$CONFIG['TABLE_PICTURES']}.approved = 'YES' AND {$CONFIG['TABLE_COMMENTS']}.pid = {$CONFIG['TABLE_PICTURES']}.pid AND {$CONFIG['TABLE_COMMENTS']}.approval = 'YES' $TMP_SET $keyword)";
                $result = cpg_db_query($query);

                $nbEnr = mysql_fetch_array($result);
                $count = $nbEnr[0];
                mysql_free_result($result);
                $select_columns = '*'; //allows building any data into any thumbnail caption
                if($select_columns == '*'){
                  $select_columns = 'p.*, msg_id, author_id, msg_author, UNIX_TIMESTAMP(msg_date) as msg_date, msg_body, aid';
                } else {
                  $select_columns = str_replace('pid', 'c.pid', $select_columns).', msg_id, author_id, msg_author, UNIX_TIMESTAMP(msg_date) as msg_date, msg_body, aid';
                }

                $TMP_SET = str_replace($CONFIG['TABLE_PICTURES'],'p',$TMP_SET);
                $query = "SELECT $select_columns FROM {$CONFIG['TABLE_COMMENTS']} as c, {$CONFIG['TABLE_PICTURES']} as p WHERE approved = 'YES' AND c.pid = p.pid AND c.approval = 'YES' $TMP_SET $keyword) ORDER by msg_id DESC $limit";
                $result = cpg_db_query($query);


                $rowset = cpg_db_fetch_rowset($result);
                mysql_free_result($result);

                if ($set_caption) build_caption($rowset,array('msg_body','msg_date'));

                $rowset = CPGPluginAPI::filter('thumb_caption_lastcom',$rowset);

                return $rowset;
                break;

        case 'lastcomby': // Last comments by a specific user
                if (isset($USER['uid'])) {
                        $uid = (int)$USER['uid'];
                } else {
                        $uid = -1;
                }

                $user_name = get_username($uid);
                if ($META_ALBUM_SET && $CURRENT_CAT_NAME) {
                        $album_name = $album_name = $lang_meta_album_names['lastcom'].' - '. $CURRENT_CAT_NAME .' - '. $user_name;
                } else {
                        $album_name = $lang_meta_album_names['lastcom'].' - '. $user_name;
                }

                $query = "SELECT COUNT({$CONFIG['TABLE_PICTURES']}.pid) from {$CONFIG['TABLE_COMMENTS']}, {$CONFIG['TABLE_PICTURES']}  WHERE approved = 'YES' AND author_id = '$uid' AND {$CONFIG['TABLE_COMMENTS']}.pid = {$CONFIG['TABLE_PICTURES']}.pid $META_ALBUM_SET";
                $result = cpg_db_query($query);
                $nbEnr = mysql_fetch_array($result);
                $count = $nbEnr[0];
                mysql_free_result($result);

                $select_columns = '*, UNIX_TIMESTAMP(msg_date) AS msg_date'; //allows building any data into any thumbnail caption

                $query = "SELECT $select_columns FROM {$CONFIG['TABLE_COMMENTS']} as c, {$CONFIG['TABLE_PICTURES']} as p WHERE approved = 'YES' AND author_id = '$uid' AND c.pid = p.pid $META_ALBUM_SET ORDER by msg_id DESC $limit";
                $result = cpg_db_query($query);
                $rowset = cpg_db_fetch_rowset($result);
                mysql_free_result($result);

                if ($set_caption) build_caption($rowset,array('msg_body','msg_date'));

                $rowset = CPGPluginAPI::filter('thumb_caption_lastcomby',$rowset);

                return $rowset;
                break;

        case 'lastup': // Last uploads
                if ($META_ALBUM_SET && $CURRENT_CAT_NAME) {
                        $album_name = $lang_meta_album_names['lastup'].' - '. $CURRENT_CAT_NAME;
                } else {
                        $album_name = $lang_meta_album_names['lastup'];
                }

                $query = "SELECT COUNT(pid) from {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' $META_ALBUM_SET";
                $result = cpg_db_query($query);
                $nbEnr = mysql_fetch_array($result);
                $count = $nbEnr[0];
                mysql_free_result($result);


                //if($select_columns != '*' ) $select_columns .= ',title, caption, owner_id, owner_name, aid';
                $select_columns = '*'; //allows building any data into any thumbnail caption
                $query = "SELECT $select_columns FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' $META_ALBUM_SET ORDER BY pid DESC $limit";
                $result = cpg_db_query($query);

                $rowset = cpg_db_fetch_rowset($result);
                mysql_free_result($result);

                if ($set_caption) build_caption($rowset,array('ctime'));

                $rowset = CPGPluginAPI::filter('thumb_caption_lastup',$rowset);

                return $rowset;
                break;

        case 'lastupby': // Last uploads by a specific user
                if (isset($USER['uid'])) {
                        $uid = (int)$USER['uid'];
                } else {
                        $uid = -1;
                }

                $user_name = get_username($uid);
                if ($META_ALBUM_SET && $CURRENT_CAT_NAME) {
                        $album_name = $lang_meta_album_names['lastup'].' - '. $CURRENT_CAT_NAME .' - '. $user_name;
                } else {
                        $album_name = $lang_meta_album_names['lastup'] .' - '. $user_name;
                }

                $query = "SELECT COUNT(pid) from {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' AND owner_id = '$uid' $META_ALBUM_SET";
                $result = cpg_db_query($query);
                $nbEnr = mysql_fetch_array($result);
                $count = $nbEnr[0];
                mysql_free_result($result);

                //if($select_columns != '*' ) $select_columns .= ', owner_id, owner_name, aid';
                $select_columns = '*'; //allows building any data into any thumbnail caption

                $query = "SELECT $select_columns FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' AND owner_id = '$uid' $META_ALBUM_SET ORDER BY pid DESC $limit";
                $result = cpg_db_query($query);

                $rowset = cpg_db_fetch_rowset($result);
                mysql_free_result($result);

                if ($set_caption) build_caption($rowset,array('ctime'));

                $rowset = CPGPluginAPI::filter('thumb_caption_lastupby',$rowset);

                return $rowset;
                break;

        case 'topn': // Most viewed pictures
                if ($META_ALBUM_SET && $CURRENT_CAT_NAME) {
                        $album_name = $lang_meta_album_names['topn'].' - '. $CURRENT_CAT_NAME;
                } else {
                        $album_name = $lang_meta_album_names['topn'];
                }

                $query ="SELECT COUNT(pid) from {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' AND hits > 0  $META_ALBUM_SET $keyword";

                $result = cpg_db_query($query);
                $nbEnr = mysql_fetch_array($result);
                $count = $nbEnr[0];
                mysql_free_result($result);

                //if($select_columns != '*') $select_columns .= ', hits, aid, filename, owner_id, owner_name';
                $select_columns = '*'; //allows building any data into any thumbnail caption

                $query = "SELECT $select_columns FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES'AND hits > 0 $META_ALBUM_SET $keyword ORDER BY hits DESC, filename  $limit";
                $result = cpg_db_query($query);

                $rowset = cpg_db_fetch_rowset($result);
                mysql_free_result($result);

                if ($set_caption) build_caption($rowset,array('hits'));

                $rowset = CPGPluginAPI::filter('thumb_caption_topn',$rowset);

                return $rowset;
                break;

        case 'toprated': // Top rated pictures
                if ($META_ALBUM_SET && $CURRENT_CAT_NAME) {
                        $album_name = $lang_meta_album_names['toprated'].' - '. $CURRENT_CAT_NAME;
                } else {
                        $album_name = $lang_meta_album_names['toprated'];
                }
                $query = "SELECT COUNT(pid) from {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' AND votes >= '{$CONFIG['min_votes_for_rating']}' $META_ALBUM_SET";
                $result = cpg_db_query($query);
                $nbEnr = mysql_fetch_array($result);
                $count = $nbEnr[0];
                mysql_free_result($result);

                //if($select_columns != '*') $select_columns .= ', pic_rating, votes, aid, owner_id, owner_name';
                $select_columns = '*'; //allows building any data into any thumbnail caption

                $query = "SELECT $select_columns FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' AND votes >= '{$CONFIG['min_votes_for_rating']}' $META_ALBUM_SET ORDER BY pic_rating DESC, votes DESC, pid DESC $limit";
                $result = cpg_db_query($query);
                $rowset = cpg_db_fetch_rowset($result);
                mysql_free_result($result);

                if ($set_caption) build_caption($rowset,array('pic_rating'));

                $rowset = CPGPluginAPI::filter('thumb_caption_toprated',$rowset);

                return $rowset;
                break;

        case 'lasthits': // Last viewed pictures
                if ($META_ALBUM_SET && $CURRENT_CAT_NAME) {
                        $album_name = $lang_meta_album_names['lasthits'].' - '. $CURRENT_CAT_NAME;
                } else {
                        $album_name = $lang_meta_album_names['lasthits'];
                }
                $query = "SELECT COUNT(pid) from {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' and hits > 0 $META_ALBUM_SET";
                $result = cpg_db_query($query);
                $nbEnr = mysql_fetch_array($result);
                $count = $nbEnr[0];
                mysql_free_result($result);

                //if($select_columns != '*') $select_columns .= ', UNIX_TIMESTAMP(mtime) as mtime, aid, hits, lasthit_ip, owner_id, owner_name';
                $select_columns = '*, UNIX_TIMESTAMP(mtime) as mtime'; //allows building any data into any thumbnail caption

                $query = "SELECT $select_columns FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' and hits > 0 $META_ALBUM_SET ORDER BY mtime DESC $limit";
                $result = cpg_db_query($query);
                $rowset = cpg_db_fetch_rowset($result);
                mysql_free_result($result);

                if ($set_caption) build_caption($rowset,array('mtime','hits'));

                $rowset = CPGPluginAPI::filter('thumb_caption_lasthits',$rowset);

                return $rowset;
                break;

        case 'random': // Random pictures
                if ($META_ALBUM_SET && $CURRENT_CAT_NAME) {
                        $album_name = $lang_meta_album_names['random'].' - '. $CURRENT_CAT_NAME;
                } else {
                        $album_name = $lang_meta_album_names['random'];
                }

                /* Commented out due to image not found bug
                $query = "SELECT COUNT(pid) from {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' $META_ALBUM_SET";
                $result = cpg_db_query($query);
                $nbEnr = mysql_fetch_array($result);
                $pic_count = $nbEnr[0];
                mysql_free_result($result);
                */

                //if($select_columns != '*') $select_columns .= ', aid, owner_id, owner_name';
                $select_columns = '*'; //allows building any data into any thumbnail caption
                // if we have more than 1000 pictures, we limit the number of picture returned
                // by the SELECT statement as ORDER BY RAND() is time consuming
                                /* Commented out due to image not found bug
                if ($pic_count > 1000) {
                    $result = cpg_db_query("SELECT COUNT(*) from {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES'");
                        $nbEnr = mysql_fetch_array($result);
                        $total_count = $nbEnr[0];
                        mysql_free_result($result);

                        $granularity = floor($total_count / RANDPOS_MAX_PIC);
                        $cor_gran = ceil($total_count / $pic_count);
                        srand(time());
                        for ($i=1; $i<= $cor_gran; $i++) $random_num_set =rand(0, $granularity).', ';
                        $random_num_set = substr($random_num_set,0, -2);
                        $result = cpg_db_query("SELECT $select_columns FROM {$CONFIG['TABLE_PICTURES']} WHERE  randpos IN ($random_num_set) AND approved = 'YES' $ALBUM_SET ORDER BY RAND() LIMIT $limit2");
                } else {
                                */
                $query = "SELECT $select_columns FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' $META_ALBUM_SET ORDER BY RAND() LIMIT $limit2";
                $result = cpg_db_query($query);

                $rowset = array();
                while($row = mysql_fetch_array($result)){
                        $rowset[-$row['pid']] = $row;
                }
                mysql_free_result($result);

                if ($set_caption) build_caption($rowset);

                $rowset = CPGPluginAPI::filter('thumb_caption_random',$rowset);

                return $rowset;
                break;

        case 'search': // Search results
                                if (isset($USER['search']['search'])) {
                                        $search_string = $USER['search']['search'];
                } else {
                                        $search_string = '';
                                }

                if ($META_ALBUM_SET && $CURRENT_CAT_NAME) {
                        $album_name = $lang_meta_album_names['search'].' - '. $CURRENT_CAT_NAME;
                } else {
                        $album_name = $lang_meta_album_names['search'].' - "'. strip_tags($search_string) . '"';
                }

                include 'include/search.inc.php';

                $rowset = CPGPluginAPI::filter('thumb_caption_search',$rowset);

                return $rowset;
                break;

        case 'lastalb': // Last albums to which uploads
                if ($META_ALBUM_SET && $CURRENT_CAT_NAME) {
                        $album_name = $lang_meta_album_names['lastalb'].' - '. $CURRENT_CAT_NAME;
                } else {
                        $album_name = $lang_meta_album_names['lastalb'];
                }


                $META_ALBUM_SET = str_replace( "aid", $CONFIG['TABLE_PICTURES'].".aid" , $META_ALBUM_SET );

                $query = "SELECT count({$CONFIG['TABLE_ALBUMS']}.aid) FROM {$CONFIG['TABLE_PICTURES']},{$CONFIG['TABLE_ALBUMS']} WHERE {$CONFIG['TABLE_PICTURES']}.aid = {$CONFIG['TABLE_ALBUMS']}.aid AND approved = 'YES' $META_ALBUM_SET GROUP  BY {$CONFIG['TABLE_PICTURES']}.aid";

                $result = cpg_db_query($query);
                $count = mysql_num_rows($result);
                mysql_free_result($result);

                $query = "SELECT *,{$CONFIG['TABLE_ALBUMS']}.title AS title,{$CONFIG['TABLE_ALBUMS']}.aid AS aid FROM {$CONFIG['TABLE_PICTURES']},{$CONFIG['TABLE_ALBUMS']} WHERE {$CONFIG['TABLE_PICTURES']}.aid = {$CONFIG['TABLE_ALBUMS']}.aid AND approved = 'YES' $META_ALBUM_SET GROUP BY {$CONFIG['TABLE_PICTURES']}.aid ORDER BY {$CONFIG['TABLE_PICTURES']}.ctime DESC $limit";
                $result = cpg_db_query($query);
                $rowset = cpg_db_fetch_rowset($result);
                mysql_free_result($result);

                if ($set_caption) build_caption($rowset,array('ctime'));

                $rowset = CPGPluginAPI::filter('thumb_caption_lastalb',$rowset);

                return $rowset;
                break;

        case 'favpics': // Favourite Pictures

                $album_name = $lang_meta_album_names['favpics'];
                                $rowset = array();
                if (count($FAVPICS)>0){
                        $favs = implode(",",$FAVPICS);
                        $query = "SELECT COUNT(pid) from {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' AND pid IN ($favs) $META_ALBUM_SET";
                        $result = cpg_db_query($query);
                        $nbEnr = mysql_fetch_array($result);
                        $count = $nbEnr[0];
                        mysql_free_result($result);

                        $select_columns = '*';

                        $query = "SELECT $select_columns FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' AND pid IN ($favs) $META_ALBUM_SET $limit";
                        $result = cpg_db_query($query);
                        $rowset = cpg_db_fetch_rowset($result);

                        mysql_free_result($result);

                        if ($set_caption) build_caption($rowset,array('ctime'));
                }

                $rowset = CPGPluginAPI::filter('thumb_caption_favpics',$rowset);

                return $rowset;
                break;

        case 'datebrowse': // Browsing by uploading date
            //Using getRaw(). The date is sanitized in the called function
            $date = $superCage->get->keyExists('date') ? cpgValidateDate($superCage->get->getRaw('date')) : null;
            $album_name = $lang_common['date'] . ': '. $date;
            $rowset = array();
            $query = "SELECT COUNT(pid) from {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' AND substring(from_unixtime(ctime),1,10) = '".substr($date,0,10)."' $META_ALBUM_SET";
            $result = cpg_db_query($query);
            $nbEnr = mysql_fetch_array($result);
            $count = $nbEnr[0];
            mysql_free_result($result);
            $select_columns = '*';
            $query = "SELECT $select_columns FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' AND substring(from_unixtime(ctime),1,10) = '".substr($date,0,10)."'  $META_ALBUM_SET $limit";
            $result = cpg_db_query($query);
            $rowset = cpg_db_fetch_rowset($result);
            mysql_free_result($result);
            if ($set_caption) build_caption($rowset,array('ctime'));
            return $rowset;
            break;
        default : // Invalid meta album
        cpg_die(ERROR, $lang_errors['non_exist_ap']." - $album", __FILE__, __LINE__);
        }
} // End of get_pic_data


// Get the name of an album

/**
 * get_album_name()
 *
 * @param $aid
 * @return
 **/

function get_album_name($aid)
{
        global $CONFIG;
        global $lang_errors;

        $result = cpg_db_query("SELECT title,keyword from {$CONFIG['TABLE_ALBUMS']} WHERE aid='$aid'");
        $count = mysql_num_rows($result);
        if ($count > 0) {
                $row = mysql_fetch_array($result);
                return $row;
        } else {
                cpg_die(ERROR, $lang_errors['non_exist_ap'], __FILE__, __LINE__);
        }
}

// Return the name of a user

/**
 * get_username()
 *
 * @param $uid
 * @return
 **/
function get_username($uid)
{
        global $CONFIG, $cpg_udb;

        $uid = (int)$uid;

        if (!$uid) {
            return 'Anonymous';
        } elseif (defined('UDB_INTEGRATION')) {
           return $cpg_udb->get_user_name($uid);
        /*} else {
                $result = cpg_db_query("SELECT user_name FROM {$CONFIG['TABLE_USERS']} WHERE user_id = '".$uid."'");
                if (mysql_num_rows($result) == 0) return '';
                $row = mysql_fetch_array($result);
                mysql_free_result($result);
                return $row['user_name'];*/
        }
}

// Return the ID of a user

/**
 * get_userid()
 *
 * @param $username
 * @return
 **/
function get_userid($username)
{
        global $CONFIG, $cpg_udb;

        $username = addslashes($username);

        if (!$username) {
            return 0;
        } elseif (defined('UDB_INTEGRATION')) { // (Altered to fix banning w/ bb integration - Nibbler)
           return $cpg_udb->get_user_id($username);
        /*} else {
                $result = cpg_db_query("SELECT user_id FROM {$CONFIG['TABLE_USERS']} WHERE user_name = '".$username."'");
                if (mysql_num_rows($result) == 0) return 0;
                $row = mysql_fetch_array($result);
                mysql_free_result($result);
                return $row['user_id'];*/
        }
}

// get number of pending approvals

/**
 * cpg_get_pending_approvals()
 *
 * @return
 **/
function cpg_get_pending_approvals()
{
    global $CONFIG;
    $result = cpg_db_query("SELECT COUNT(*) FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'NO'");
    if (mysql_num_rows($result) == 0) return 0;
    $row = mysql_fetch_array($result);
    mysql_free_result($result);
    return $row[0];
}

// Return the total number of comments for a certain picture

/**
 * count_pic_comments()
 *
 * @param $pid
 * @param integer $skip
 * @return
 **/
function count_pic_comments($pid, $skip=0)
{
        global $CONFIG;
        $result = cpg_db_query("SELECT count(msg_id) from {$CONFIG['TABLE_COMMENTS']} where pid=$pid and msg_id!=$skip");
        $nbEnr = mysql_fetch_array($result);
        $count = $nbEnr[0];
        mysql_free_result($result);

        return $count;
}

/******************
/**
 * cpg_determine_client()
 *
 * @param
 * @return $return_array
 **/
function cpg_determine_client($pid)
{
        global $CONFIG;

        //Making Cage
        $superCage = Inspekt::makeSuperCage();

        /**
         * Populate the client stats
         */
        // Get the details of user browser, IP, OS, etc
        $os = "Unknown";
        $server_agent = $superCage->server->getRaw('HTTP_USER_AGENT');
        if(eregi("Linux",$server_agent)) {
            $os = "Linux";
        } else if(eregi("Ubuntu",$server_agent)) {
            $os = "Linux Ubuntu";
        } else if(eregi("Debian",$server_agent)) {
            $os = "Linux Debian";
        } else if(eregi("Windows NT 5.0",$server_agent)) {
            $os = "Windows 2000";
        } else if(eregi("win98|Windows 98",$server_agent)) {
            $os = "Windows 98";
        } else if(eregi("Windows NT 5.1",$server_agent)) {
            $os = "Windows XP";
        } else if(eregi("Windows NT 5.2",$server_agent)) {
            $os = "Windows 2003 Server";
        } else if(eregi("Windows NT 6.0",$server_agent)) {
            $os = "Windows Vista";
        } else if(eregi("Windows CE",$server_agent)) {
            $os = "Windows CE";
        } else if(eregi("Windows",$server_agent)) {
            $os = "Windows";
        } else if(eregi("SunOS",$server_agent)) {
            $os = "Sun OS";
        } else if(eregi("Macintosh",$server_agent)) {
            $os = "Macintosh";
        } else if(eregi("Mac_PowerPC",$server_agent)) {
            $os = "Mac OS";
        } else if(eregi("Mac_PPC",$server_agent)) {
            $os = "Macintosh";
        } else if(eregi("OS/2",$server_agent)) {
            $os = "OS/2";
        }

        $browser = 'Unknown';
        if(eregi("MSIE",$server_agent)) {
            if(eregi("MSIE 5.5",$server_agent)) {
                $browser = "IE5.5";
            } else if(eregi("MSIE 6.0",$server_agent)) {
                $browser = "IE6";
            } else if(eregi("MSIE 7.0",$server_agent)) {
                $browser = "IE7";
            } else if(eregi("MSIE 3.0",$server_agent)) {
                $browser = "IE3";
            } else if(eregi("MSIE 4.0",$server_agent)) {
                $browser = "IE4";
            } else if(eregi("MSIE 5.0",$server_agent)) {
                $browser = "IE5.0";
            }
        } else if(eregi("Firebird",$server_agent)) {
            $browser = "Mozilla Firebird";
        } else if(eregi("netscape",$server_agent)) {
            $browser = "Netscape";
        } else if(eregi("Firefox",$server_agent)) {
            $browser = "Firefox";
        } else if(eregi("Galeon",$server_agent)) {
            $browser = "Galeon";
        } else if(eregi("Camino/",$server_agent)) {
            $browser = "Camino/";
        } else if(eregi("Konqueror",$server_agent)) {
            $browser = "Konqueror";
        } else if(eregi("Safari",$server_agent)) {
            $browser = "Safari";
        } else if(eregi("OmniWeb",$server_agent)) {
            $browser = "OmniWeb";
        } else if(eregi("Opera",$server_agent)) {
            $browser = "Opera";
        } else if(eregi("amaya",$server_agent)) {
            $browser = "Amaya";
        } else if(eregi("iCab",$server_agent)) {
            $browser = "iCab";
        } else if(eregi("Lynx",$server_agent)) {
            $browser = "Lynx";
        } else if(eregi("Googlebot",$server_agent)) {
            $browser = "Googlebot";
        } else if(eregi("Lycos_Spider",$server_agent)) {
            $browser = "Lycos Spider";
        } else if(eregi("Firefly",$server_agent)) {
            $browser = "Fireball Spider";
        } else if(eregi("Advanced Browser",$server_agent)) {
            $browser = "Avant";
        } else if(eregi("Amiga-AWeb",$server_agent)) {
            $browser = "AWeb";
        } else if(eregi("Cyberdog",$server_agent)) {
            $browser = "Cyberdog";
        } else if(eregi("Dillo",$server_agent)) {
            $browser = "Dillo";
        } else if(eregi("DreamPassport",$server_agent)) {
            $browser = "DreamCast";
        } else if(eregi("eCatch",$server_agent)) {
            $browser = "eCatch";
        } else if(eregi("ANTFresco",$server_agent)) {
            $browser = "Fresco";
        } else if(eregi("RSS",$server_agent)) {
            $browser = "RSS";
        } else if(eregi("Avant",$server_agent)) {
            $browser = "Avant";
        } else if(eregi("HotJava",$server_agent)) {
            $browser = "HotJava";
        } else if(eregi("W3C-checklink|W3C_Validator|Jigsaw",$server_agent)) {
            $browser = "W3C";
        } else if(eregi("K-Meleon",$server_agent)) {
            $browser = "K-Meleon";
        }

        //Code to get the search string if the referrer is any of the following
        $search_engines = array('google', 'lycos', 'yahoo');

        foreach ($search_engines as $engine) {
          if ( is_referer_search_engine($engine)) {
            $query_terms = get_search_query_terms($engine);
            break;
          }
        }
        $return_array = array('os' => $os, 'browser' => $browser, 'query_term' => $query_terms);
        return $return_array;
}


// Add 1 everytime a picture is viewed.

/**
 * add_hit()
 *
 * @param $pid
 * @return
 **/
function add_hit($pid)
{
        global $CONFIG, $raw_ip, $HTML_SUBST;

		  if ($CONFIG['count_file_hits']) {
          cpg_db_query("UPDATE {$CONFIG['TABLE_PICTURES']} SET hits=hits+1, lasthit_ip='$raw_ip', mtime=CURRENT_TIMESTAMP WHERE pid='$pid'");
		  }
        /**
         * Code to record the details of hits for the picture, if the option is set in CONFIG
         */
        if ($CONFIG['hit_details']) {
        // Get the details of user browser, IP, OS, etc
        $client_details = cpg_determine_client();


        //Making Cage
        $superCage = Inspekt::makeSuperCage();
        
        $time = time();

        //Sanitize the referer
        //Used getRaw() method but sanitized immediately
        if ($superCage->server->keyExists('HTTP_REFERER')) {
            $referer = urlencode(addslashes(htmlentities($superCage->server->getRaw('HTTP_REFERER'))));
        } else {
                $referer= '';
        }

        // Insert the record in database
        $query = "INSERT INTO {$CONFIG['TABLE_HIT_STATS']}
                          SET
                            pid = $pid,
                            search_phrase = '{$client_details['query_term']}',
                            Ip   = '$raw_ip',
                            sdate = '$time',
                            referer='$referer',
                            browser = '{$client_details['browser']}',
                            os = '{$client_details['os']}'";
        cpg_db_query($query);
     }
}

/**
 * add_album_hit()
 * Add a hit to the album.
 * @param $pid
 * @return
 **/
function add_album_hit($aid)
{
        global $CONFIG, $USER;
        
        if ($CONFIG['count_album_hits']) {
          $aid = (int)$aid;
          cpg_db_query("UPDATE {$CONFIG['TABLE_ALBUMS']} SET alb_hits=alb_hits+1 WHERE aid='$aid'");
        }
}
/**
 * breadcrumb()
 *
 * Build the breadcrumb navigation
 *
 * @param integer $cat
 * @param string $breadcrumb
 * @param string $BREADCRUMB_TEXT
 * @return
 **/

function breadcrumb($cat, &$breadcrumb, &$BREADCRUMB_TEXT)
{
        global $album, $lang_errors, $lang_list_categories;
        global $CONFIG,$CURRENT_ALBUM_DATA, $CURRENT_CAT_NAME;

        $category_array = array();

        // first we build the category path: names and id
        if ($cat != 0)
        { //Categories other than 0 need to be selected
                if ($cat >= FIRST_USER_CAT)
                {
                    $user_name = get_username($cat - FIRST_USER_CAT);
                    if (!$user_name) $user_name = 'Mr. X';

                    $category_array[] = array($cat, $user_name);
                    $CURRENT_CAT_NAME = sprintf($lang_list_categories['xx_s_gallery'], $user_name);
                    $row['parent'] = 1;
                }
                else
                {
                    $result = cpg_db_query("SELECT name, parent FROM {$CONFIG['TABLE_CATEGORIES']} WHERE cid = '$cat'");
                    if (mysql_num_rows($result) == 0){
                        cpg_die(CRITICAL_ERROR, $lang_errors['non_exist_cat'], __FILE__, __LINE__);
                                        }
                    $row = mysql_fetch_array($result);

                    $category_array[] = array($cat, $row['name']);
                    $CURRENT_CAT_NAME = $row['name'];
                    mysql_free_result($result);
                }

                while($row['parent'] != 0)
                {
                    $result = cpg_db_query("SELECT cid, name, parent FROM {$CONFIG['TABLE_CATEGORIES']} WHERE cid = '{$row['parent']}'");
                    if (mysql_num_rows($result) == 0){
                        //cpg_die(CRITICAL_ERROR, $lang_errors['orphan_cat'], __FILE__, __LINE__);
                                        }
                    $row = mysql_fetch_array($result);

                    $category_array[] = array($row['cid'], $row['name']);
                    mysql_free_result($result);
                } // while

                $category_array = array_reverse($category_array);
        }

        $breadcrumb_links = array();
        $BREADCRUMB_TEXTS = array();

        // Add the Home link  to breadcrumb
        $breadcrumb_links[0] = '<a href="index.php">'.$lang_list_categories['home'].'</a>';
        $BREADCRUMB_TEXTS[0] = $lang_list_categories['home'];

        $cat_order = 1;
        foreach ($category_array as $category)
        {
            $breadcrumb_links[$cat_order] = "<a href=\"index.php?cat={$category[0]}\">{$category[1]}</a>";
            $BREADCRUMB_TEXTS[$cat_order] = $category[1];
            $cat_order += 1;
        }

        //Add Link for album if aid is set
        if (isset($CURRENT_ALBUM_DATA['aid']))
        {
            $breadcrumb_links[$cat_order] = "<a href=\"thumbnails.php?album=".$CURRENT_ALBUM_DATA['aid']."\">".$CURRENT_ALBUM_DATA['title']."</a>";
            $BREADCRUMB_TEXTS[$cat_order] = $CURRENT_ALBUM_DATA['title'];
        }

        // we check if the theme_breadcrumb exists...
        if (function_exists('theme_breadcrumb'))
        {
            theme_breadcrumb($breadcrumb_links, $BREADCRUMB_TEXTS, $breadcrumb, $BREADCRUMB_TEXT);
            return;
        }
        // otherwise we have a default breadcrumb builder:
        $breadcrumb = '';
        $BREADCRUMB_TEXT = '';
        foreach ($breadcrumb_links as $breadcrumb_link)
        {
            $breadcrumb .= ' > ' . $breadcrumb_link;
        }
        foreach ($BREADCRUMB_TEXTS as $BREADCRUMB_TEXT_elt)
        {
            $BREADCRUMB_TEXT .= ' > ' . $BREADCRUMB_TEXT_elt;
        }
        // We remove the first ' > '
        $breadcrumb = substr_replace($breadcrumb,'', 0, 3);
        $BREADCRUMB_TEXT = substr_replace($BREADCRUMB_TEXT,'', 0, 3);
        //echo $breadcrumb;
}


/**************************************************************************

 **************************************************************************/

// Compute image geometry based on max width / height

/**
 * compute_img_size()
 *
 * Compute image geometry based on max, width / height
 *
 * @param integer $width
 * @param integer $height
 * @param integer $max
 * @return array
 **/
function compute_img_size($width, $height, $max, $system_icon=false, $normal=false)
{
         global $CONFIG;
        $thumb_use=$CONFIG['thumb_use'];
        if($thumb_use=='ht') {
          $ratio = $height / $max;
        } elseif($thumb_use=='wd') {
          $ratio = $width / $max;
        } else {
          $ratio = max($width, $height) / $max;
        }
        if ($ratio > 1.0) {
                $image_size['reduced'] = true;
        }
        $ratio = max($ratio, 1.0);
        $image_size['width'] = ceil($width / $ratio);
        $image_size['height'] = ceil($height / $ratio);
        $image_size['whole'] = 'width="'.$image_size['width'].'" height="'.$image_size['height'].'"';
        if($thumb_use=='ht') {
          $image_size['geom'] = ' height="'.$image_size['height'].'"';
        } elseif($thumb_use=='wd') {
          $image_size['geom'] = 'width="'.$image_size['width'].'"';
        //thumb cropping
                } elseif($thumb_use=='ex') {
                        if ($normal=="normal"){
                        $image_size['geom'] = 'width="'.$image_size['width'].'" height="'.$image_size['height'].'"';
                        }
                        elseif ($normal=="cat_thumb"){
                          $image_size['geom'] = 'width="'.$max.'" height="'.($CONFIG['thumb_height'])*$max/$CONFIG['thumb_width'].'"';
                        }
                        else {
                          $image_size['geom'] = 'width="'.$CONFIG['thumb_width'].'" height="'.$CONFIG['thumb_height'].'"';
                        }
                        //if we have a system icon we override the previous calculation and take 'any' as base for the calc
                        if($system_icon){
                                $image_size['geom'] = 'width="'.$image_size['width'].'" height="'.$image_size['height'].'"';
                        }

        } else {
          $image_size['geom'] = 'width="'.$image_size['width'].'" height="'.$image_size['height'].'"';
        }



        return $image_size;
}

// Prints thumbnails of pictures in an album

/**
 * display_thumbnails()
 *
 * Generates data to display thumbnails of pictures in an album
 *
 * @param mixed $album Either the album ID or the meta album name
 * @param integer $cat Either the category ID or album ID if negative
 * @param integer $page Page number to display
 * @param integer $thumbcols
 * @param integer $thumbrows
 * @param boolean $display_tabs
 **/

function display_thumbnails($album, $cat, $page, $thumbcols, $thumbrows, $display_tabs)
{
        global $CONFIG, $AUTHORIZED, $USER;
        global $album_date_fmt, $lang_display_thumbnails, $lang_errors, $lang_byte_units, $lang_common;

        $superCage = Inspekt::makeSuperCage();

        $thumb_per_page = $thumbcols * $thumbrows;
        $lower_limit = ($page-1) * $thumb_per_page;

        $pic_data = get_pic_data($album, $thumb_count, $album_name, $lower_limit, $thumb_per_page);
        
        $total_pages = ceil($thumb_count / $thumb_per_page);

        $i = 0;
        if (count($pic_data) > 0) {
                foreach ($pic_data as $key => $row) {
                        $i++;

                        $pic_title =$lang_common['filename'].'='.$row['filename']."\n".
                                $lang_common['filesize'].'='.($row['filesize'] >> 10).$lang_byte_units[1]."\n".
                                $lang_display_thumbnails['dimensions'].$row['pwidth']."x".$row['pheight']."\n".
                                $lang_display_thumbnails['date_added'].localised_date($row['ctime'], $album_date_fmt);

                        $pic_url =  get_pic_url($row, 'thumb');
                        if (!is_image($row['filename'])) {
                                $image_info = cpg_getimagesize(urldecode($pic_url));
                                $row['pwidth'] = $image_info[0];
                                $row['pheight'] = $image_info[1];
                        }
                                                //thumb cropping - if we display a system thumb we calculate the dimension by any and not ex
                                                if($row['system_icon']=='true'){
                                $image_size = compute_img_size($row['pwidth'], $row['pheight'], $CONFIG['thumb_width'], true);
                                                } else {
                                $image_size = compute_img_size($row['pwidth'], $row['pheight'], $CONFIG['thumb_width']);
                                                }
                        $thumb_list[$i]['pos'] = $key < 0 ? $key : $i - 1 + $lower_limit;
                        $thumb_list[$i]['pid'] = $row['pid'];;
                        $thumb_list[$i]['image'] = "<img src=\"" . $pic_url . "\" class=\"image\" {$image_size['geom']} border=\"0\" alt=\"{$row['filename']}\" title=\"$pic_title\"/>";
                        $thumb_list[$i]['caption'] = bb_decode($row['caption_text']);
                        $thumb_list[$i]['admin_menu'] = '';
                        $thumb_list[$i]['aid'] = $row['aid'];
                        $thumb_list[$i]['pwidth'] = $row['pwidth'];
                        $thumb_list[$i]['pheight'] = $row['pheight'];
                        
                        if (defined('API_CALL')) {
                            $thumb_list[$i]['title'] = $row['title'];
                            $thumb_list[$i]['filename'] = get_pic_url($row);
                        }
                }

                // Print out XML photo list and exit
                if (defined('API_CALL')) {
                    echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
                    echo $superCage->post->getAlpha('album') ? "<" . $superCage->post->getAlpha('album') . ">\n" : "<api_search>\n";
                    foreach ($thumb_list as $pic) {
                        $file = preg_replace('/%7E/', '~', $pic['filename']);
                        echo " <picture id=\"{$pic['pid']}\">\n";
                        echo "  <title>{$pic['title']}</title>\n";
                        echo "  <file>$file</file>\n";
                        echo "  <width>{$pic['pwidth']}</width>\n";
                        echo "  <height>{$pic['pheight']}</height>\n";
                        echo " </picture>\n";                        
                    }
                    echo $superCage->post->getAlpha('album') ? "</" . $superCage->post->getAlpha('album') . ">" : "</api_search>";
                    exit();
                }                
                
                // Add a hit to album counter if it is a numeric album
                if (is_numeric($album)) {
                        // Create an array to hold the album id for hits (if not created)
                        if (!isset($USER['liv_a']) || !is_array($USER['liv_a'])) {
                                $USER['liv_a'] = array();
                        }
                        // Add 1 to album hit counter
                        if (!USER_IS_ADMIN && !in_array($album, $USER['liv_a']) && $superCage->cookie->keyExists($CONFIG['cookie_name'] . '_data')) {
                                add_album_hit($album);
                                if (count($USER['liv_a']) > 4) array_shift($USER['liv_a']);
                                array_push($USER['liv_a'], $album);
                                user_save_profile();
                        }
                }
                
                //Using getRaw(). The date is sanitized in the called function.
                $date = $superCage->get->keyExists('date') ? cpgValidateDate($superCage->get->getRaw('date')) : null;
                theme_display_thumbnails($thumb_list, $thumb_count, $album_name, $album, $cat, $page, $total_pages, is_numeric($album), $display_tabs, 'thumb', $date);
        } else {
            if (defined('API_CALL')) {
                new OAuthException('No pictures found');
            } else {
                theme_no_img_to_display($album_name);
            }
        }
}

 /**
 * cpg_get_system_thumb_list()
 *
 * Return an array containing the system thumbs in a directory

 * @param string $search_folder
 * @return array
 **/
function cpg_get_system_thumb_list($search_folder = 'images/')
{
        global $CONFIG;
        static $thumbs = array();

        $folder = 'images/';

        $thumb_pfx =& $CONFIG['thumb_pfx'];
        // If thumb array is empty get list from coppermine 'images' folder
        if ((count($thumbs) == 0) && ($folder == $search_folder)) {
                $dir = opendir($folder);
                while (($file = readdir($dir))!==false) {
                        if (is_file($folder . $file) && strpos($file,$thumb_pfx) === 0) {
                                // Store filenames in an array
                                $thumbs[] = array('filename' => $file);
                        }
                }
                closedir($dir);
                return $thumbs;
        } elseif ($folder == $search_folder) {
                // Search folder is the same as coppermine images folder; just return the array
                return $thumbs;
        } else {
                // Search folder is the different; check for files in the given folder
                $results = array();
                foreach ($thumbs as $thumb) {
                        if (is_file($search_folder.$thumb['filename'])) {
                                $results[] = array('filename' => $thumb['filename']);
                        }
                }
                return $results;
        }
}


/**
 * cpg_get_system_thumb()
 *
 * Gets data for system thumbs
 *
 * @param string $filename
 * @param integer $user
 * @return array
 **/

function& cpg_get_system_thumb($filename,$user=10001)
{
        global $CONFIG,$USER_DATA;

        // Correct user_id
        if ($user<10000)
        {
                $user += 10000;
        }

        if ($user==10000) {
                $user = 10001;
        }

        // Get image data for thumb
        $picdata = array('filename'=>$filename,
                         'filepath'=>$CONFIG['userpics'].$user.'/',
                         'url_prefix'=>0);
        $pic_url = get_pic_url($picdata,'thumb',true);
        $picdata['thumb'] = $pic_url;
        $image_info = cpg_getimagesize(urldecode($pic_url));
        $picdata['pwidth'] = $image_info[0];
        $picdata['pheight'] = $image_info[1];
        $image_size = compute_img_size($picdata['pwidth'], $picdata['pheight'], $CONFIG['alb_list_thumb_size']);
        $picdata['whole'] = $image_size['whole'];
        $picdata['reduced'] = (isset($image_size['reduced']) && $image_size['reduced']);
        return $picdata;
}


/**
 * display_film_strip()
 *
 * gets data for thumbnails in an album for the film strip
 *
 * @param integer $album
 * @param integer $cat
 * @param integer $pos
 **/

function display_film_strip($album, $cat, $pos,$ajax_call)
{
	//	print $pos; 
        global $CONFIG, $AUTHORIZED;
        global $album_date_fmt, $lang_display_thumbnails, $lang_errors, $lang_byte_units, $lang_common, $pic_count,$ajax_call,$pos; 

        $superCage = Inspekt::makeSuperCage();
        $max_item= $CONFIG['max_film_strip_items'];
        
		if(($CONFIG['max_film_strip_items']%2)==0){
			 $max_item= $CONFIG['max_film_strip_items']+1;
		}
       	//print $max_item;

       	 $max_item_real = $max_item;
       	 /**check the thumb_per_page variable valid to query database*/
       	 if($pic_count < $max_item_real){
				$max_item_real = $pic_count;
			}
		 //pass the max_items to the dispalyimage.js file
		 set_js_var('max_item',$max_item_real);
       	
		$max_item_to_set_width = $CONFIG['max_film_strip_items'];
        //$thumb_per_page = $pos+$CONFIG['max_film_strip_items'];
        $thumb_per_page = $max_item_real;
        //assign the varible $l_limit diffen
		$l_limit = (int)($max_item_real/2); 
        $l_limit = max(0,$pos-$l_limit);
		//set $l_limit to last images 
		if($l_limit >($pic_count-$max_item_real)){
			$l_limit = $pic_count-$max_item_real;
		}

        $pic_data = get_pic_data($album, $thumb_count, $album_name, $l_limit, $thumb_per_page);

        if (count($pic_data) < $max_item ){
			$max_item = count($pic_data);
        }
        
		$lower_limit = 0;	
		
		if($ajax_call==2){
			$lower_limit = $max_item_real -1; 
			$max_item =1;
		}
		if($ajax_call ==1){
			$lower_limit = 0; 
			$max_item =1; 
		}
		
		$pic_data=array_slice($pic_data,$lower_limit,$max_item);
		$i=$l_limit;
		
		//set javascript count variable:: added by Nuwan Sameera Hettiarachchi
		set_js_var('count', $pic_count);
	

		
	$cat_link = is_numeric($album) ? '' : '&amp;cat=' . $cat;
    $date_link = $date=='' ? '' : '&amp;date=' . $date;

    if ($superCage->get->getInt('uid')) {
        $uid_link = '&amp;uid=' . $superCage->get->getInt('uid');
    } else {
        $uid_link = '';
    }
		if (count($pic_data) > 0){
			
                foreach ($pic_data as $key => $row) {
                        $hi =(($pos==($i + $lower_limit))  ? '1': '');
                        $i++;

                        $pic_title =$lang_common['filename'].'='.$row['filename']."\n".
                                $lang_common['filesize'].'='.($row['filesize'] >> 10).$lang_byte_units[1]."\n".
                                $lang_display_thumbnails['dimensions'].$row['pwidth']."x".$row['pheight']."\n".
                                $lang_display_thumbnails['date_added'].localised_date($row['ctime'], $album_date_fmt);

                        $pic_url =  get_pic_url($row, 'thumb');
						//print $pic_url; 
                        if (!is_image($row['filename'])) {
                                $image_info = cpg_getimagesize(urldecode($pic_url));
                                $row['pwidth'] = $image_info[0];
                                $row['pheight'] = $image_info[1];
                        }

                                                //thumb cropping
                                                if($row['system_icon']=='true'){
                                $image_size = compute_img_size($row['pwidth'], $row['pheight'], $CONFIG['thumb_width'], true);
                                                } else {
                                $image_size = compute_img_size($row['pwidth'], $row['pheight'], $CONFIG['thumb_width']);
                                                }

                        $p=$i - 1 + $lower_limit;
                        $p=($p < 0 ? 0 : $p);
                        $thumb_list[$i]['pos'] = $key < 0 ? $key : $p;
                        $thumb_list[$i]['image'] = "<img src=\"" . $pic_url . "\" class=\"strip_image\"   border=\"0\" alt=\"{$row['filename']}\" title=\"$pic_title\" />";
                        $thumb_list[$i]['caption'] = $CONFIG['display_film_strip_filename'] ? '<span class="thumb_filename">'.$row['filename'].'</span>' : '';
                        $thumb_list[$i]['admin_menu'] = '';
                        ######### Added by Abbas #############
                        $thumb_list[$i]['pid'] = $row['pid'];
                        ######################################
						$target = "displayimage.php?album=$album$cat_link$date_link&amp;pid={$row['pid']}$uid_link";
					
                }

                                // Get the pos for next and prev links in filmstrip navigation
                                $filmstrip_next_pos = $pos + 1;
                                $filmstrip_prev_pos = $pos - 1;
                                // If next pos is greater then total pics then make it pic_count - 1
                                $filmstrip_next_pos = $filmstrip_next_pos >= $pic_count ? $pic_count - 1 : $filmstrip_next_pos;
                                // If prev pos is less than 0 then make it 0
                                $filmstrip_prev_pos = $filmstrip_prev_pos < 0 ? 0 : $filmstrip_prev_pos;

                //Using getRaw(). The date is sanitized in the called function.
                $date = $superCage->get->keyExists('date') ? cpgValidateDate($superCage->get->getRaw('date')) : null;
				
				if($ajax_call==2 || $ajax_call==1){
					$a = array ('url'=>$pic_url,'target'=>$target);
					$a_jons = json_encode($a);
						echo $a_jons;
	}
				else{
					//	print $thumb_count;
					    return theme_display_film_strip($thumb_list, $thumb_count, $album_name, $album, $cat, $pos, is_numeric($album), 'thumb', $date, $filmstrip_prev_pos, $filmstrip_next_pos,$max_item_to_set_width);
					}
        } else {
               theme_no_img_to_display($album_name);
        }
}




/**
 * display_slideshow()
 *
 * gets data for thumbnails in an album for the film stript using Ajax call
 *
 * this added by Nuwan Sameera Hettiarachchi
 *
 * @param integer $album
 * @param integer $cat
 * @param integer $pos
 **/
function& display_slideshow($pos,$ajax_show=0)
{
   global $CONFIG, $lang_display_image_php, $template_display_media, $lang_common, $album, $pid, $slideshow;
   global $cat, $date;

	print $Pid; 
	$Pic = array();
	$Pid = array();
	$Title = array();
	
	$i = 0;
	$j = 0;
	$a =0;
	//$pid = (int)$_GET['pid'];
	$start_img = '';
 
$pic_data = get_pic_data($album, $pic_count, $album_name, -1, -1, false);

foreach ($pic_data as $picture) {
    if($CONFIG['thumb_use']=='ht' && $picture['pheight'] > $CONFIG['picture_width'] ){ // The wierd comparision is because only picture_width is stored
      $condition = true;
    }elseif($CONFIG['thumb_use']=='wd' && $picture['pwidth'] > $CONFIG['picture_width']){
      $condition = true;
    }elseif($CONFIG['thumb_use']=='any' && max($picture['pwidth'], $picture['pheight']) > $CONFIG['picture_width']){
      $condition = true;
        //thumb cropping
    }elseif($CONFIG['thumb_use']=='ex' && max($picture['pwidth'], $picture['pheight']) > $CONFIG['picture_width']){
      $condition = true;
    }else{
     $condition = false;
    }
			
    if (is_image($picture['filename'])) {
        if ($CONFIG['make_intermediate'] && $condition ) {
            $picture_url = get_pic_url($picture, 'normal');
        } else {
            $picture_url = get_pic_url($picture, 'fullsize');
        }

		if ( $picture['title'] ) {
            $Title_get = $picture['title'];
        } else {
            $Title_get = $picture['filename'];
        }
		
        $Pic[$i] =  htmlspecialchars($picture_url, ENT_QUOTES) ;
        $Pid[$i] =  $picture['pid'] ;		
        $Title[$i] = $Title_get;
			
        if ($picture['pid'] == $pid) {
            $j = $i;
            $start_img = $picture_url;
        }
        $i++; 
    }
}

	$Pic_length = count($Pic);
	set_js_var('Time',$slideshow);
	set_js_var('Pic_count',$Pic_length);
	set_js_var('Pid',$pid);
		
	if (!$i) {
	    echo "Pic[0] = 'images/thumb_document.jpg'\n";
	}
	if($ajax_show==0){
		theme_slideshow($Pic[$pos],$Title[$pos]);
	}
	$a = array ('url'=>$Pic[$pos],'title'=>$Title[$pos],'pid'=>$Pid[$pos]);
	$a_jons = json_encode($a);
	if($ajax_show==1){
	echo $a_jons;

	}
	
}


// Return the url for a picture, allows to have pictures spreaded over multiple servers

/**
 * get_pic_url()
 *
 * Return the url for a picture
 *
 * @param array $pic_row
 * @param string $mode
 * @param boolean $system_pic
 * @return string
 **/

function& get_pic_url(&$pic_row, $mode,$system_pic = false, $skip_storage = false)
{
        global $CONFIG,$THEME_DIR;

        static $pic_prefix = array();
        static $url_prefix = array();

        // Storage and API search don't seem to work together -daorange
        if (defined('API_CALL')) {
            $skip_storage = true;        
        }

        if (!count($pic_prefix)) {
                $pic_prefix = array(
                        'thumb' => $CONFIG['thumb_pfx'],
                        'normal' => $CONFIG['normal_pfx'],
                        'orig' => $CONFIG['orig_pfx'],######
                        'fullsize' => ''
                );

                $url_prefix = array(
                        0 => $CONFIG['fullpath'],
                );
        }

        $mime_content = cpg_get_type($pic_row['filename']);
        // If $mime_content is empty there will be errors, so only perform the array_merge if $mime_content is actually an array
        if (is_array($mime_content)) {
            $pic_row = array_merge($pic_row,$mime_content);
        }

        $filepathname = null;

        // Code to handle custom thumbnails
        // If fullsize or normal mode use regular file
        if ($mime_content['content'] != 'image' && $mode== 'normal') {
                $mode = 'fullsize';
        } elseif (($mime_content['content'] != 'image' && $mode == 'thumb') || $system_pic) {
                $thumb_extensions = Array('.gif','.png','.jpg');
                // Check for user-level custom thumbnails
                // Create custom thumb path and erase extension using filename; Erase filename's extension
                $custom_thumb_path = $url_prefix[$pic_row['url_prefix']].$pic_row['filepath'].$pic_prefix[$mode];
                $file_base_name = str_replace('.'.$mime_content['extension'],'',basename($pic_row['filename']));
                // Check for file-specific thumbs
                foreach ($thumb_extensions as $extension) {
                        if (file_exists($custom_thumb_path.$file_base_name.$extension)) {
                                $filepathname = $custom_thumb_path.$file_base_name.$extension;
                                break;
                        }
                }
                if (!$system_pic) {
                        // Check for extension-specific thumbs
                        if (is_null($filepathname)) {
                                foreach ($thumb_extensions as $extension) {
                                        if (file_exists($custom_thumb_path.$mime_content['extension'].$extension)) {
                                                $filepathname = $custom_thumb_path.$mime_content['extension'].$extension;
                                                break;
                                        }
                                }
                        }
                        // Check for content-specific thumbs
                        if (is_null($filepathname)) {
                                foreach ($thumb_extensions as $extension) {
                                        if (file_exists($custom_thumb_path.$mime_content['content'].$extension)) {
                                                $filepathname = $custom_thumb_path.$mime_content['content'].$extension;
                                                break;
                                        }
                                }
                        }
                }
                // Use default thumbs
                if (is_null($filepathname)) {
                               // Check for default theme- and global-level thumbs
                               $thumb_paths[] = $THEME_DIR.'images/';                 // Used for custom theme thumbs
                               $thumb_paths[] = 'images/';                             // Default Coppermine thumbs
                               foreach ($thumb_paths as $default_thumb_path) {
                                       if (is_dir($default_thumb_path)) {
                                               if (!$system_pic) {
                                                       foreach ($thumb_extensions as $extension) {
                                                               // Check for extension-specific thumbs
                                                               if (file_exists($default_thumb_path.$CONFIG['thumb_pfx'].$mime_content['extension'].$extension)) {
                                                                       $filepathname = $default_thumb_path.$CONFIG['thumb_pfx'].$mime_content['extension'].$extension;
                                                                                                                                                //thumb cropping - if we display a system thumb we calculate the dimension by any and not ex
                                                                                                                                           $pic_row['system_icon']=true;
                                                                                                                                           break 2;
                                                               }
                                                       }
                                                       foreach ($thumb_extensions as $extension) {
                                                               // Check for media-specific thumbs (movie,document,audio)
                                                               if (file_exists($default_thumb_path.$CONFIG['thumb_pfx'].$mime_content['content'].$extension)) {
                                                                       $filepathname = $default_thumb_path.$CONFIG['thumb_pfx'].$mime_content['content'].$extension;
                                                                       //thumb cropping
                                                                                                                                           $pic_row['system_icon']=true;
                                                                                                                                           break 2;
                                                               }
                                                       }
                                               } else {
                                                       // Check for file-specific thumbs for system files
                                                       foreach ($thumb_extensions as $extension) {
                                                               if (file_exists($default_thumb_path.$CONFIG['thumb_pfx'].$file_base_name.$extension)) {
                                                                       $filepathname = $default_thumb_path.$CONFIG['thumb_pfx'].$file_base_name.$extension;
                                                                       //thumb cropping
                                                                                                                                           $pic_row['system_icon']=true;
                                                                                                                                           break 2;
                                                               }
                                                       }
                                               }
                                       }
                               }
                }
                $filepathname = path2url($filepathname);
        }

        if (is_null($filepathname)) {
            $filepathname = $url_prefix[$pic_row['url_prefix']]. path2url($pic_row['filepath']. $pic_prefix[$mode]. $pic_row['filename']);
        }

        // Added hack:  "&& !isset($pic_row['mode'])" thumb_data filter isn't executed for the fullsize image
        if ($mode == 'thumb' && !isset($pic_row['mode'])) {
            $pic_row['url'] = $filepathname;
            $pic_row['mode'] = $mode;
            $pic_row = CPGPluginAPI::filter('thumb_data',$pic_row);
        } elseif ($mode != 'thumb') {
            $pic_row['url'] = $filepathname;
            $pic_row['mode'] = $mode;
        } else {
            $pic_row['url'] = $filepathname;
        }
        $pic_row = CPGPluginAPI::filter('picture_url',$pic_row);

        ///// OVI
		if($skip_storage==false)
		{
			$imageContainer = new FileContainer($pic_row['pid'], $pic_row['owner_id'], $pic_row['url']);
			global $storage;
			$pic_row['url'] = $storage->build_url($imageContainer);
		}
        ///// OVI
        return $pic_row['url'];
}


/**
 * cpg_get_default_lang_var()
 *
 * Return a variable from the default language file
 *
 * @param $language_var_name
 * @param unknown $overide_language
 * @return
 **/
function& cpg_get_default_lang_var($language_var_name,$overide_language = null) {
        global $CONFIG;
        if (is_null($overide_language)) {
                if (isset($CONFIG['default_lang'])) {
                        $language = $CONFIG['default_lang'];
                } else {
                               global $$language_var_name;
                               return $$language_var_name;
                }
        } else {
                       $language = $overide_language;
        }
        include('lang/'.$language.'.php');
        return $$language_var_name;
}

// Returns a variable from the current language file
// If variable doesn't exists gets value from english_us lang file

/**
 * cpg_lang_var()
 *
 * @param $varname
 * @param unknown $index
 * @return
 **/

function& cpg_lang_var($varname,$index=null) {
        global $$varname;

        $lang_var =& $$varname;

        if (isset($lang_var)) {
                if (!is_null($index) && !isset($lang_var[$index])) {
                        include('lang/english.php');
                        return $lang_var[$index];
                } elseif (is_null($index)) {
                        return $lang_var;
                } else {
                               return $lang_var[$index];
                }
        } else {
                       include('lang/english.php');
                       return $lang_var;
        }
}


/**
 * cpg_debug_output()
 *
 * defined new debug_output function here in functions.inc.php instead of theme.php with different function names to avoid incompatibilities with users not updating their themes as required. Advanced info is only output if (GALLERY_ADMIN_MODE == TRUE)
 *
 **/

function cpg_debug_output()
{
    global $USER, $USER_DATA, $META_ALBUM_SET, $ALBUM_SET, $CONFIG, $cpg_time_start, $query_stats, $queries, $lang_cpg_debug_output, $CPG_PHP_SELF, $superCage, $CPG_PLUGINS;

        $time_end = cpgGetMicroTime();
        $time = round($time_end - $cpg_time_start, 3);

        $query_count = count($query_stats);
        $total_query_time = array_sum($query_stats);

        $debug_underline = '&#0010;------------------&#0010;';
        $debug_separate = '&#0010;==========================&#0010;';
        $debug_toggle_link = ' <a href="javascript:;" onclick="show_section(\'debug_output_rows\');" class="admin_menu" id="debug_output_toggle" style="display:none;">'.$lang_cpg_debug_output['show_hide'].'</a>';
        echo '<form name="debug" action="'.$CPG_PHP_SELF.'" id="debug">';
        starttable('100%', $lang_cpg_debug_output['debug_info']. $debug_toggle_link,2);
        //echo '<div name="debug_output_rows" id="debug_output_rows" style="display:block;">';
        echo '<tr><td align="center" valign="top" width="100%" colspan="2">';
        echo '<table border="0" cellspacing="0" cellpadding="0" width="100%" id="debug_output_rows">';
        echo '<tr><td align="center" valign="middle" class="tableh2" width="100">';
        echo '<script language="javascript" type="text/javascript">
<!--

// only hide the debug_output if the user is capable to display it, i.e. if JavaScript is enabled. If JavaScript is off, debug_output will be displayed and the toggle will remain invisible (as it would not do anything anyway with JS off)
addonload("document.getElementById(\'debug_output_rows\').style.display = \'none\'");
addonload("document.getElementById(\'debug_output_toggle\').style.display = \'inline\'");
//-->
</script>';
        echo <<< EOT
        <script type="text/javascript">
            document.write('<a href="javascript:HighlightAll(\'debug.debugtext\')" class="admin_menu">');
            document.write("{$lang_cpg_debug_output['select_all']}");
            document.write('</a>');
        </script>
        </td><td align="left" valign="middle" class="tableh2">
EOT;
        if (GALLERY_ADMIN_MODE){echo '<span class="album_stat">'.$lang_cpg_debug_output['copy_and_paste_instructions'].'</span>';}
        echo '</td></tr>';
        echo '<tr><td class="tableb" colspan="2">';
        echo '<textarea  rows="10" cols="60" class="debug_text" name="debugtext">';
        echo "USER: ";
        echo $debug_underline;
        print_r($USER);
        echo $debug_separate;
        echo "USER DATA:";
        echo $debug_underline;
        print_r($USER_DATA);
        echo $debug_separate;
        echo "Queries:";
        echo $debug_underline;
        print_r($queries);
        echo $debug_separate;
        echo "GET :";
        echo $debug_underline;
        print_r($superCage->get->_source);
        echo $debug_separate;
        echo "POST :";
        echo $debug_underline;
        print_r($superCage->post->_source);
        echo $debug_separate;
        echo "COOKIE :";
        echo $debug_underline;
        print_r($superCage->cookie->_source);
        echo $debug_separate;
        if ($superCage->cookie->keyExists('PHPSESSID')){
                echo "SESSION :";
                echo $debug_underline;
                session_id($superCage->cookie->getAlnum('PHPSESSID'));
                     session_start();
                print_r($_SESSION);
                echo $debug_separate;
        }
        if (GALLERY_ADMIN_MODE){
        echo "VERSION INFO :";
        echo $debug_underline;
        $version_comment = ' - OK';
        if (strcmp('4.2.0', phpversion()) == 1) {$version_comment = ' - your PHP version isn\'t good enough! Minimum requirements: 4.2.0';}
        echo 'PHP version: ' . phpversion().$version_comment;
        echo "\n";
        $version_comment = ' - OK';
        $mySqlVersion = cpg_phpinfo_mysql_version();
        if (strcmp('3.23.23', $mySqlVersion) == 1) {$version_comment = ' - your MySQL version isn\'t good enough! Minimum requirements: 3.23.23';}
        echo 'MySQL version: ' . $mySqlVersion . $version_comment;
        echo "\n";
        echo 'Coppermine version: ';
        echo COPPERMINE_VERSION . '(' . COPPERMINE_VERSION_STATUS . ')';
        echo "\n";
        echo $debug_separate;
//        error_reporting  (E_ERROR | E_WARNING | E_PARSE); // New maze's error report system
        if (function_exists('gd_info') == true) {
            echo 'Module: GD';
            echo $debug_underline;
            $gd_array = gd_info();
            foreach ($gd_array as $key => $value) {
                echo $key.': '.$value."\n";
            }
            echo $debug_separate;
        } else {
            echo cpg_phpinfo_mod_output('gd','text');
        }
        echo 'Key config settings';
        echo $debug_underline;
        echo cpg_config_output("allow_private_albums");
        echo cpg_config_output("cookie_name");
        echo cpg_config_output("cookie_path");
        echo cpg_config_output("ecards_more_pic_target");
        echo cpg_config_output("impath");
        echo cpg_config_output("lang");
        echo cpg_config_output("language_fallback");
        echo cpg_config_output("main_page_layout");
        echo cpg_config_output("silly_safe_mode");
        echo cpg_config_output("smtp_host");
        echo cpg_config_output("theme");
        echo cpg_config_output("thumb_method");
                  echo $debug_separate;
                  echo 'Plugins';
                  echo $debug_underline;

                         foreach ($CPG_PLUGINS as $plugin){
                                 echo 'Plugin: ' . $plugin->name . "\n";
                                 echo 'Actions: ' . implode(', ', array_keys($plugin->actions)) . "\n";
                                 echo 'Filters: ' . implode(', ', array_keys($plugin->filters));
                                 echo $debug_underline;
                         }

                  echo $debug_separate;
        echo 'Server restrictions';
        echo $debug_underline;
        echo 'Directive | Local Value | Master Value';
        echo cpg_phpinfo_conf_output("safe_mode");
        echo cpg_phpinfo_conf_output("safe_mode_exec_dir");
        echo cpg_phpinfo_conf_output("safe_mode_gid");
        echo cpg_phpinfo_conf_output("safe_mode_include_dir");
        echo cpg_phpinfo_conf_output("safe_mode_exec_dir");
        echo cpg_phpinfo_conf_output("sql.safe_mode");
        echo cpg_phpinfo_conf_output("disable_functions");
        echo cpg_phpinfo_conf_output("file_uploads");
        echo cpg_phpinfo_conf_output("include_path");
        echo cpg_phpinfo_conf_output("open_basedir");
        echo cpg_phpinfo_conf_output("allow_url_fopen");
        echo "\n$debug_separate";
        echo 'Resource limits';
        echo $debug_underline;
        echo 'Directive | Local Value | Master Value';
        echo cpg_phpinfo_conf_output("max_execution_time");
        echo cpg_phpinfo_conf_output("max_input_time");
        echo cpg_phpinfo_conf_output("upload_max_filesize");
        echo cpg_phpinfo_conf_output("post_max_size");
        echo cpg_phpinfo_conf_output("memory_limit");
        echo "\n$debug_separate";
        }

        echo <<<EOT
Page generated in $time seconds - $query_count queries in $total_query_time seconds - Album set : $ALBUM_SET; Meta set: $META_ALBUM_SET;
EOT;
        echo '</textarea>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</td></tr>';
        if (GALLERY_ADMIN_MODE){
          echo "<tr><td class=\"tablef\" colspan=\"2\">";
          echo "<a href=\"phpinfo.php\" class=\"admin_menu\">".$lang_cpg_debug_output['phpinfo']."</a>";
//          error_reporting  (E_ERROR | E_WARNING | E_PARSE); // New maze's error report system
          echo "</td></tr>";
        }

        // Maze's new error report system
        global $cpgdebugger;
        $report = $cpgdebugger->stop();
        if (is_array($report) && $CONFIG['debug_notice']!= 0) {
            echo '<tr><td class="tableh1" colspan="2">';
            echo '<b>';
            echo $lang_cpg_debug_output['notices'];
            echo '</b>';
            echo '</td></tr>';
            echo '<tr><td class="tableb" colspan="2">';
            foreach($report AS $file => $errors) {
                echo '<b>'.substr($file, $strstart).'</b><ul>';
                foreach($errors AS $error) { echo "<li>$error</li>"; }
                echo '</ul>';
            }
            echo '</td></tr>';
        }
        endtable();
        echo "</form>";

}

/**
 * cpg_phpinfo_mod()
 *
 * phpinfo-related functions:
 *
 * @param $search
 * @return
 **/

function cpg_phpinfo_mod($search)
{
    // this could be done much better with regexpr - anyone who wants to change it: go ahead
    ob_start();
    phpinfo(INFO_MODULES);
    $string = ob_get_contents();
    $module = $string;
    $delimiter = '#cpgdelimiter#';
    ob_end_clean();
    // find out the first occurence of "<h2" and throw the superfluos stuff away
    $string = stristr($string, 'module_' . $search);
    $string = eregi_replace('</table>(.*)', '', $string);
    $string = stristr($string, '<tr');
    $string = str_replace('</td>', '|', $string);
    $string = str_replace('</tr>', $delimiter, $string);
    $string = chop(strip_tags($string));
    $pieces = explode($delimiter, $string);
    foreach($pieces as $key => $val) {
        $bits[$key] = explode("|", $val);
    }
    return $bits;
}

/**
 * cpg_phpinfo_mod_output()
 *
 * @param $search
 * @param $output_type
 * @return
 **/

function cpg_phpinfo_mod_output($search,$output_type)
{
// first parameter is the module name, second parameter is the way you want your output to look like: table or text
    $pieces = cpg_phpinfo_mod($search);
    $summ = '';
    $return = '';
    $debug_underline = '&#0010;------------------&#0010;';
    $debug_separate = '&#0010;==========================&#0010;';

    if ($output_type == 'table')
    {
    ob_start();
    starttable('100%', 'Module: '.$search, 2);
    $return.= ob_get_contents();
    ob_end_clean();
    }
    else
    {
    $return.= 'Module: '.$search.$debug_underline;
    }
    foreach($pieces as $val) {
        if ($output_type == 'table') {$return.= '<tr><td>';}
        $return.= $val[0];
        if ($output_type == 'table') {$return.= '</td><td>';}
        if (isset($val[1])) {$return.= $val[1];}
        if ($output_type == 'table') {$return.= '</td></tr>';}
        $summ .= $val[0];
    }
    if (!$summ) {
        if ($output_type == 'table') {$return.= '<tr><td colspan="2">';}
        $return.= 'module doesn\'t exist';
        if ($output_type == 'table') {$return.= '</td></tr>';}
    }
    if ($output_type == 'table')
    {
    ob_start();
    endtable();
    $return.= ob_get_contents();
    ob_end_clean();
    }
    else
    {
    $return.= $debug_separate;
    }
    return $return;
}

/**
 * cpg_phpinfo_mysql_version()
 *
 * @return
 **/


function cpg_phpinfo_mysql_version()
{
    $result = mysql_query("SELECT VERSION() as version");
    $row = mysql_fetch_row($result);
    return $row[0];
}

/**
 * cpg_phpinfo_conf()
 *
 * @param $search
 * @return
 **/

function cpg_phpinfo_conf($search)
{
    // this could be done much better with regexpr - anyone who wants to change it: go ahead
    $string ='';
    $pieces = '';
    $delimiter = '#cpgdelimiter#';
    $bits = '';

    ob_start();
    phpinfo(INFO_CONFIGURATION);
    $string = ob_get_contents();
    ob_end_clean();
    // find out the first occurence of "</tr" and throw the superfluos stuff in front of it away
    $string = strchr($string, '</tr>');
    $string = str_replace('</td>', '|', $string);
    $string = str_replace('</tr>', $delimiter, $string);
    $string = chop(strip_tags($string));
    $pieces = explode($delimiter, $string);
    foreach($pieces as $val) {
        $bits = explode("|", $val);
        if (strchr($bits[0], $search)) {
            return $bits;
        }
    }
}

/**
 * cpg_phpinfo_conf_output()
 *
 * @param $search
 * @return
 **/

function cpg_phpinfo_conf_output($search)
{
$pieces = cpg_phpinfo_conf($search);
        $return= $pieces[0] . ' | ' . $pieces[1] . ' | ' . $pieces[2];
        return $return;
}

function cpg_config_output($key)
{
        global $CONFIG;

        return "$key: {$CONFIG[$key]}\n";
}

// theme and language selection


/**
 * languageSelect()
 *
 * @param $parameter
 * @return
 **/

function languageSelect($parameter) {
    global $CONFIG, $lang_language_selection, $lang_common, $CPG_PHP_SELF;
    $superCage = Inspekt::makeSuperCage();
    $return= '';
    $lineBreak = "\n";

    //check if language display is enabled
    if ($CONFIG['language_list'] == 0 && $parameter == 'list'){
        return;
    }
    if ($CONFIG['language_flags'] == 0 && $parameter == 'flags'){
        return;
    }

    // get the current language
     //use the default language of the gallery
     $cpgCurrentLanguage = $CONFIG['lang'];

     // is a user logged in?
     //has the user already chosen another language for himself?
     //if($USER['lang']!=""){
     //   $cpgCurrentLanguage = $USER['lang'];
     //   }
     //has the language been set to something else on the previous page?
     /*if (isset($_GET['lang'])){
        $cpgCurrentLanguage = $_GET['lang'];
        }*/
     //get the url and all vars except $lang
     $matches = $superCage->server->getMatched('SCRIPT_NAME', '/^[a-zA-Z0-9_\/.]+$/');

     if ($matches) {
        //$cpgChangeUrl =  _SERVER["SCRIPT_NAME"]."?";
        $cpgChangeUrl = $matches[0] . '?';
     } else {
        $cpgChangeUrl = 'index.php';
     }

     $matches = $superCage->server->getMatched('QUERY_STRING', '/^[a-zA-Z0-9&=_\/.]+$/');
     if ($matches) {
        $queryString = explode('&', $matches[0]);
     } else {
             $queryString = array();
     }

     foreach ($queryString as $val) {
        list($key, $value) = explode('=', $val);
        if ($key != "lang") {
            $cpgChangeUrl .= $key . "=" . $value . "&";
        }
     }

     /*foreach ($_GET as $key => $value) {
        if ($key!="lang"){$cpgChangeUrl.= $key . "=" . $value . "&amp;";}
     }*/

     $cpgChangeUrl .= 'lang=';


    // get an array of english and native language names and flags
    // for now, use a static array definition here - this could later be made into a true database query
    $lang_language_data['albanian'] = array('Albanian','Albanian','al');
    $lang_language_data['amharic'] = array('Amharic','','et');
    $lang_language_data['arabic'] = array('Arabic','&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;','sa');
    $lang_language_data['armenian'] = array('Armenian','','');
    $lang_language_data['azerbaijani'] = array('Azerbaijani','','az');
    $lang_language_data['bengali'] = array('Bengali','','bd');
    $lang_language_data['basque'] = array('Basque','Euskera','baq');
    $lang_language_data['bosnian'] = array('Bosnian','Bosanski','ba');
    $lang_language_data['brazilian_portuguese'] = array('Portuguese [Brazilian]','Portugu&ecirc;s Brasileiro','br');
    $lang_language_data['bulgarian'] = array('Bulgarian','&#1041;&#1098;&#1083;&#1075;&#1072;&#1088;&#1089;&#1082;&#1080;','bg');
    $lang_language_data['byelorussian'] = array('Byelorussian','','by');
    $lang_language_data['catalan'] = array('Catalan','Catal&agrave;','ct');
    $lang_language_data['chamorro'] = array('Chamorro','','gu');
    $lang_language_data['chinese_big5'] = array('Chinese traditional','&#20013;&#25991; - &#32321;&#39636;','tw');
    $lang_language_data['chinese_gb'] = array('Chinese simplified','&#20013;&#25991; - &#31616;&#20307;','cn');
    $lang_language_data['croatian'] = array('Croatian','Hrvatski','hr');
    $lang_language_data['czech'] = array('Czech','&#x010C;esky','cz');
    $lang_language_data['danish'] = array('Danish','Dansk','dk');
    $lang_language_data['dutch'] = array('Dutch','Nederlands','nl');
    $lang_language_data['english'] = array('English [US]','English [US]','us');
    $lang_language_data['english_gb'] = array('English [British]','English [British]','gb');
    $lang_language_data['estonian'] = array('Estonian','Eesti','ee');
    $lang_language_data['filipino'] = array('Filipino Tagalog','','ph');
    $lang_language_data['finnish'] = array('Finnish','Suomea','fi');
    $lang_language_data['french'] = array('French','Fran&ccedil;ais','fr');
    $lang_language_data['galician'] = array('Galician','Galego','es_gln');
    $lang_language_data['georgian'] = array('Georgian','&#4325;&#4304;&#4320;&#4311;&#4323;&#4314;&#4312;','ge');
    $lang_language_data['german'] = array('German','Deutsch','de');
    $lang_language_data['german_sie'] = array('German [formal]','Deutsch [Sie]','de');
    $lang_language_data['greek'] = array('Greek','&#917;&#955;&#955;&#951;&#957;&#953;&#954;&#940;','gr');
    $lang_language_data['hebrew'] = array('Hebrew','&#1506;&#1489;&#1512;&#1497;&#1514;','il');
    $lang_language_data['hindi'] = array('Hindi','&#2361;&#2367;&#2344;&#2381;&#2342;&#2368;','in');
    $lang_language_data['hungarian'] = array('Hungarian','Magyarul','hu');
    $lang_language_data['icelandic'] = array('Icelandic','','is');
    $lang_language_data['indonesian'] = array('Indonesian','Bahasa Indonesia','id');
    $lang_language_data['italian'] = array('Italian','Italiano','it');
    $lang_language_data['japanese'] = array('Japanese','&#26085;&#26412;&#35486;','jp');
    $lang_language_data['kazakh'] = array('Kazakh','','kz');
    $lang_language_data['korean'] = array('Korean','&#54620;&#44397;&#50612;','kr');
    $lang_language_data['kurdish'] = array('Kurdish','&#1603;&#1608;&#1585;&#1583;&#1740;','ku');
    $lang_language_data['kyrgyz'] = array('Kyrgyz','','kg');
    $lang_language_data['laothian'] = array('Laothian ','','la');
    $lang_language_data['latvian'] = array('Latvian','Latvian','lv');
    $lang_language_data['lithuanian'] = array('Lithuanian','Lietuvi&#0353;kai','lt');
    $lang_language_data['macedonian'] = array('Macedonian','&#1052;&#1072;&#1082;&#1077;&#1076;&#1086;&#1085;&#1089;&#1082;&#1080;','mk');
    $lang_language_data['malay'] = array('Malay','Bahasa Melayu','my');
    $lang_language_data['maltese'] = array('Maltese','','mt');
    $lang_language_data['mongolian'] = array('Mongolian','','mn');
    $lang_language_data['nepali'] = array('Nepali','','np');
    $lang_language_data['norwegian'] = array('Norwegian','Norsk','no');
    $lang_language_data['persian'] = array('Persian','&#1601;&#1575;&#1585;&#1587;&#1740;','ir');//modified by B.Mossavari
    $lang_language_data['polish'] = array('Polish','Polski','pl');
    $lang_language_data['portuguese'] = array('Portuguese [Portugal]','Portugu&ecirc;s','pt');
    $lang_language_data['romanian'] = array('Romanian','Rom&acirc;n&atilde;','ro');
    $lang_language_data['russian'] = array('Russian','&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;','ru');
    $lang_language_data['slovak'] = array('Slovak','Slovensky','sk');
    $lang_language_data['slovenian'] = array('Slovenian','Slovensko','si');
    $lang_language_data['spanish'] = array('Spanish','Espa&ntilde;ol','es');
    $lang_language_data['swedish'] = array('Swedish','Svenska','se');
    $lang_language_data['thai'] = array('Thai','&#3652;&#3607;&#3618;','th');
    $lang_language_data['turkish'] = array('Turkish','T&uuml;rk&ccedil;e','tr');
    $lang_language_data['tigrinya'] = array('Tigrinya','','er');
    $lang_language_data['twi'] = array('Twi','','gh');
    $lang_language_data['uighur'] = array('Uighur','Uighur','cn-xj');
    $lang_language_data['ukrainian'] = array('Ukrainian','&#1059;&#1082;&#1088;&#1072;&#1111;&#1085;&#1089;&#1100;&#1082;&#1072;','ua');
    $lang_language_data['uzbek'] = array('Uzbek','','uz');
    $lang_language_data['vietnamese'] = array('Vietnamese','Tieng Viet','vn');

    // get list of available languages
      $value = strtolower($CONFIG['lang']);

      $lang_dir = 'lang/';
      $dir = opendir($lang_dir);
      while ($file = readdir($dir)) {
         if ($file != '.' && $file != '..' && $file !='.svn' ) {
             $lang_array[] = strtolower(substr($file, 0 , -4));
         }
      }
      closedir($dir);
      natcasesort($lang_array);

    //start the output
    switch ($parameter) {
       case 'flags':
           $return.= '<div id="cpgChooseFlags" class="inline">';
           if ($CONFIG['language_flags'] == 2){
               $return.= $lang_language_selection['choose_language'].': ';
           }
           foreach ($lang_array as $language) {
           $cpg_language_name = str_replace('-utf-8','', $language);
                  if (array_key_exists($cpg_language_name, $lang_language_data)){
                  $return.= $lineBreak .  '<a href="' .$cpgChangeUrl. $language . '" rel="nofollow"><img src="images/flags/' . $lang_language_data[$cpg_language_name][2] . '.gif" border="0" width="16" height="10" alt="" title="';
                  $return.= $lang_language_data[$language][0];
                  if ($lang_language_data[$language][1] != $lang_language_data[$language][0]){
                      $return.= ' (' . $lang_language_data[$language][1] . ')';
                      }
                  $return.= '" /></a>' . $lineBreak;
                  }
                  }
              if ($CONFIG['language_reset'] == 1){
                  $return.=  '<a href="' .$cpgChangeUrl. 'xxx" rel="nofollow"><img src="images/flags/reset.gif" border="0" width="16" height="11" alt="" title="';
                  $return.=  $lang_language_selection['reset_language'] . '" /></a>' . $lineBreak;
              }
           $return.= '</div>';
           break;
       case 'table':
           $return = 'not yet implemented';
           break;
       default:
           $return.= $lineBreak . '<div id="cpgChooseLanguageWrapper">' . $lineBreak . '<form name="cpgChooseLanguage" id="cpgChooseLanguage" action="' . $CPG_PHP_SELF . '" method="get" class="inline">' . $lineBreak;
           $return.= '<select name="lang" class="listbox_lang" onchange="if (this.options[this.selectedIndex].value) window.location.href=\'' . $cpgChangeUrl . '\' + this.options[this.selectedIndex].value;">' . $lineBreak;
           $return.='<option selected="selected">' . $lang_language_selection['choose_language'] . '</option>' . $lineBreak;
           foreach ($lang_array as $language) {
              $return.=  '<option value="' . $language  . '" >';
                  if (array_key_exists($language, $lang_language_data)){
                  $return.= $lang_language_data[$language][0];
                  if ($lang_language_data[$language][1] != $lang_language_data[$language][0]){
                      $return.= ' (' . $lang_language_data[$language][1] . ')';
                      }
                  }
                  else{
                      $return.= ucfirst($language);
                      }
                  $return.= ($value == $language ? '*' : '');
                  $return.= '</option>' . $lineBreak;
                  }
              if ($CONFIG['language_reset'] == 1){
                  $return.=  '<option value="xxx">' . $lang_language_selection['reset_language'] . '</option>' . $lineBreak;
              }
              $return.=  '</select>' . $lineBreak;
              $return.=  '<noscript>'. $lineBreak;
              $return.=  '<input type="submit" name="language_submit" value="'.$lang_common['go'].'" class="listbox_lang" />&nbsp;'. $lineBreak;
              $return.=  '</noscript>'. $lineBreak;
              $return.=  '</form>' . $lineBreak;
              $return.=  '</div>' . $lineBreak;
       }

    return $return;
}


/**
 * themeSelect()
 *
 * @param $parameter
 * @return
 **/

function themeSelect($parameter)
{
    global $CONFIG,$lang_theme_selection, $lang_common, $CPG_PHP_SELF;

    $superCage = Inspekt::makeSuperCage();

    $return= '';
    $lineBreak = "\n";

    if ($CONFIG['theme_list'] == 0){
        return;
    }

    // get the current theme
    //get the url and all vars except $theme
    /*$cpgCurrentTheme = $_SERVER["SCRIPT_NAME"]."?";
    foreach ($_GET as $key => $value) {
        if ($key!="theme"){$cpgCurrentTheme.= $key . "=" . $value . "&amp;";}
    }*/

    $matches = $superCage->server->getMatched('SCRIPT_NAME', '/^[a-zA-Z0-9_\/.]+$/');

     if ($matches) {
        //$cpgChangeUrl =  _SERVER["SCRIPT_NAME"]."?";
        $cpgCurrentTheme = $matches[0] . '?';
     } else {
        $cpgCurrentTheme = 'index.php';
     }

     $matches = $superCage->server->getMatched('QUERY_STRING', '/^[a-zA-Z0-9&=_\/.]+$/');
     if ($matches) {
        $queryString = explode('&', $matches[0]);
     } else {
        $queryString = array();
     }

     foreach ($queryString as $val) {
        list($key, $value) = explode('=', $val);
        if ($key != "theme") {
            $cpgCurrentTheme .= $key . "=" . $value . "&";
        }
     }

    $cpgCurrentTheme .= "theme=";

    // get list of available languages
        $value = $CONFIG['theme'];
        $theme_dir = 'themes/';

        $dir = opendir($theme_dir);
        while ($file = readdir($dir)) {
            if (is_dir($theme_dir . $file) && $file != "." && $file != ".." && $file != '.svn' && $file != 'sample') {
                $theme_array[] = $file;
            }
        }
        closedir($dir);

        natcasesort($theme_array);

    //start the output
    switch ($parameter) {
       default:
           $return.= $lineBreak . '<div id="cpgChooseThemeWrapper">' . $lineBreak . '<form name="cpgChooseTheme" id="cpgChooseTheme" action="' . $CPG_PHP_SELF . '" method="get" class="inline">' . $lineBreak;
           $return.= '<select name="theme" class="listbox_lang" onchange="if (this.options[this.selectedIndex].value) window.location.href=\'' . $cpgCurrentTheme . '\' + this.options[this.selectedIndex].value;">' . $lineBreak;
           $return.='<option selected="selected">' . $lang_theme_selection['choose_theme'] . '</option>';
           foreach ($theme_array as $theme) {
               $return.= '<option value="' . $theme . '"'.($value == $theme ? '  selected="selected"' : '').'>' . strtr(ucfirst($theme), '_', ' ') . ($value == $theme ? '  *' : ''). '</option>' . $lineBreak;
           }
              if ($CONFIG['theme_reset'] == 1){
                  $return.=  '<option value="xxx">' . $lang_theme_selection['reset_theme'] . '</option>' . $lineBreak;
              }
              $return.=  '</select>' . $lineBreak;
              $return.=  '<noscript>'. $lineBreak;
              $return.=  '<input type="submit" name="theme_submit" value="'.$lang_common['go'].'" class="listbox_lang" />&nbsp;'. $lineBreak;
              $return.=  '</noscript>'. $lineBreak;
              $return.=  '</form>' . $lineBreak;
              $return.=  '</div>' . $lineBreak;
       }

    return $return;
}

/**
 * cpgSocialBookmark()
 *
 * @return
 **/
function cpgSocialBookmark() {
  global $CONFIG, $lang_social_bookmarks;
  
  $return = '';
  
  if ($CONFIG['display_social_bookmarks'] != 0) {
    $addressParamsToRemove_array = array('message_id', 'theme');
    $url = $CONFIG['ecards_more_pic_target'] . rawurlencode(str_replace('&amp;', '&', rtrim(cpgGetScriptNameParams($addressParamsToRemove_array), '&amp;')));
    $title = rawurlencode($CONFIG['gallery_name']);
    $description = rawurlencode($CONFIG['gallery_description']);
    $socialBookmarks_array = array(
      array(
      'name' => 'digg.com',
      'url' => 'http://www.digg.com/submit?url={URL}',
      'icon' => 'images/bookmarks/digg.gif',
      ),
      array(
      'name' => 'del.icio.us',
      'url' => 'http://del.icio.us/post?url={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/del.icio.us.gif',
      ),
      array(
      'name' => 'Yahoo MyWeb',
      'url' => 'http://myweb2.search.yahoo.com/myresults/bookmarklet?t={TITLE}&u={URL}',
      'icon' => 'images/bookmarks/myweb.yahoo.gif',
      ),
      array(
      'name' => 'technorati',
      'url' => 'http://technorati.com/cosmos/search.html?url={URL}',
      'icon' => 'images/bookmarks/technorati.gif',
      ),
      array(
      'name' => 'Spurl',
      'url' => 'http://www.spurl.net/spurl.php?url={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/spurl.gif',
      ),
      array(
      'name' => 'Furl',
      'url' => 'http://www.furl.net/storeIt.jsp?t={TITLE}&u={URL}',
      'icon' => 'images/bookmarks/furl.gif',
      ),
      array(
      'name' => 'Blinklist',
      'url' => 'http://www.blinklist.com/index.php?Action=Blink/addblink.php&Description={DESCRIPTION}&Url={URL}&Title={TITLE}',
      'icon' => 'images/bookmarks/blinklist.gif',
      ),
      array(
      'name' => 'Fark',
      'url' => 'http://cgi.fark.com/cgi/fark/edit.pl?new_url={URL}&new_comment={TITLE}',
      'icon' => 'images/bookmarks/fark.gif',
      ),
      array(
      'name' => 'Blogmarks',
      'url' => 'http://blogmarks.net/my/new.php?mini=1&simple=1&url={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/blogmarks.gif',
      ),
      array(
      'name' => 'Simpy',
      'url' => 'http://www.simpy.com/simpy/LinkAdd.do?href={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/simpy.gif',
      ),
      array(
      'name' => 'Reddit',
      'url' => 'http://reddit.com/submit?url={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/reddit.gif',
      ),
      array(
      'name' => 'StumbleUpon',
      'url' => 'http://www.stumbleupon.com/submit?url={URL}&newcomment={DESCRIPTION}&title={TITLE}',
      'icon' => 'images/bookmarks/stumbleupon.gif',
      ),
      array(
      'name' => 'Slashdot',
      'url' => 'http://slashdot.org/bookmark.pl?url={URL}&tags={DESCRIPTION}&title={TITLE}',
      'icon' => 'images/bookmarks/slashdot.gif',
      ),
      array(
      'name' => 'Netscape',
      'url' => 'http://www.netscape.com/submit/?U={URL}&storyText={DESCRIPTION}&storyTags=&T={TITLE}',
      'icon' => 'images/bookmarks/netscape.gif',
      ),
      array(
      'name' => 'diigo',
      'url' => 'http://www.diigo.com/post?url={URL}&title={TITLE}&tag=&comments={DESCRIPTION}',
      'icon' => 'images/bookmarks/diigo.gif',
      ),
      array(
      'name' => 'NewsVine',
      'url' => 'http://www.newsvine.com/_wine/save?popoff=1&u={URL}&tags={DESCRIPTION}&blurb={TITLE}',
      'icon' => 'images/bookmarks/newsvine.gif',
      ),
      array(
      'name' => 'ma.gnolia',
      'url' => 'http://ma.gnolia.com/bookmarklet/add?url={URL}&title={TITLE}&description={DESCRIPTION}',
      'icon' => 'images/bookmarks/ma.gnolia.gif',
      ),
      array(
      'name' => 'Google',
      'url' => 'http://www.google.com/bookmarks/mark?op=add&bkmk={URL}&annotation={DESCRIPTION}&labels=&title={TITLE}',
      'icon' => 'images/bookmarks/google.gif',
      ),
      array(
      'name' => 'Mister Wong',
      'url' => 'http://www.mister-wong.de/index.php?action=addurl&bm_url={URL}&bm_description={DESCRIPTION}',
      'icon' => 'images/bookmarks/misterbook.gif',
      ),
      array(
      'name' => 'Linkarena',
      'url' => 'http://www.linkarena.com/bookmarks/addlink/?url={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/linkarena.gif',
      ),
      array(
      'name' => 'Newskick.de',
      'url' => 'http://www.newskick.de/submit.php?url={URL}',
      'icon' => 'images/bookmarks/newskick.gif',
      ),
      array(
      'name' => 'Weblinkr.com',
      'url' => 'http://weblinkr.com/login?action=add&address={URL}&description={DESCRIPTION}',
      'icon' => 'images/bookmarks/weblinkr.gif',
      ),
      array(
      'name' => 'Alltagz',
      'url' => 'http://www.alltagz.de/bookmarks/?action=add&address={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/alltagz.gif',
      ),
      array(
      'name' => 'Webbrille.de',
      'url' => 'http://www.webbrille.de/bookmarks.php/?action=add&address={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/webbrille.gif',
      ),
      array(
      'name' => 'Newstube.de',
      'url' => 'http://newstube.de/submit.php?url={URL}',
      'icon' => 'images/bookmarks/newstube.gif',
      ),
      array(
      'name' => 'Webnews.de',
      'url' => 'http://www.webnews.de/einstellen?url={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/webnews.gif',
      ),
      array(
      'name' => 'Readster.de',
      'url' => 'http://www.readster.de/submit/?url={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/readster.gif',
      ),
      array(
      'name' => 'oneview.de',
      'url' => 'http://www.oneview.de/quickadd/neu/addBookmark.jsf?URL={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/oneview.gif',
      ),
      array(
      'name' => 'Maodi.de',
      'url' => 'http://www.maodi.de/bookmarks/?action=add&address={URL}&title={TITLE}',
      'icon' => 'images/bookmarks/maodi.gif',
      ),
      array(
      'name' => 'Tausendreporter',
      'url' => 'http://tausendreporter.stern.de/submit.php?url={URL}',
      'icon' => 'images/bookmarks/tausendreporter.gif',
      ),
      array(
      'name' => 'Linksilo',
      'url' => 'http://www.linksilo.de/index.php?area=bookmarks&func=bookmark_new&addurl={URL}&addtitle={TITLE}',
      'icon' => 'images/bookmarks/linksilo.gif',
      ),
    );
    $return = '<div id="social_bookmarks_wrapper">';
    $return .= '<div class="social_bookmarks" id="social_bookmarks_text">' . $lang_social_bookmarks['add_this_page_to'].': </div>';
    $countLoop = 0;
    $social_bookmarks_config_array = explode ("|",$CONFIG['display_social_bookmarks']);
    foreach ($socialBookmarks_array as $key) {
      if ($social_bookmarks_config_array[$countLoop] == 1) {
        $key['url'] = str_replace('{URL}', $url, $key['url']);
        $key['url'] = str_replace('{TITLE}', $title, $key['url']);
        $key['url'] = str_replace('{DESCRIPTION}', $description, $key['url']);
        $return .= '<div class="social_bookmarks"><a href="' . $key['url'] . '" rel="external" class="external social_bookmarks2">';
        $return .= '<img src="' . $key['icon'] . '" border="0" alt="" class="social_bookmarks2" title="' . sprintf($lang_social_bookmarks['bookmark_this_page'],$key['name']) . '" />';
        $return .= '</a></div>';
      }
      $countLoop++;
    }
    $return .= '</div>';
    $return = "\r\n" . '<script type="text/javascript">' . "\r\n" . 'document.write(\'' . $return . '\');' . "\r\n" . '</script>' . "\r\n";
  }
  return $return;
}


/**
 * cpg_alert_dev_version()
 *
 * @return
 **/

function cpg_alert_dev_version() {
        global $lang_version_alert, $lang_common, $CONFIG, $REFERER;
        $return = '';
        if (COPPERMINE_VERSION_STATUS != 'stable') {
            ob_start();
            starttable('100%', $lang_version_alert['version_alert']);
            print '<tr><td class="tableb">';
            print sprintf($lang_version_alert['no_stable_version'], COPPERMINE_VERSION, COPPERMINE_VERSION_STATUS);
            print '</td></tr>';
            endtable();
            print '<br />';
            $return = ob_get_contents();
            ob_end_clean();
        }
        // check if gallery is offline
        if ($CONFIG['offline'] == 1 && GALLERY_ADMIN_MODE) {
            ob_start();
            starttable('100%', $lang_common['information']);
            print '<tr><td class="tableb">';
            print '<span style="color:red;font-weight:bold">'.$lang_version_alert['gallery_offline'].'</span>';
            print '</td></tr>';
            endtable();
            print '<br />';
            $return .= ob_get_contents();
            ob_end_clean();
        }
        // display news from coppermine-gallery.net
        if ($CONFIG['display_coppermine_news'] == 1 && GALLERY_ADMIN_MODE) {
            $help_news = '&nbsp;'.cpg_display_help('f=configuration.htm&amp;as=admin_general_coppermine_news&amp;ae=admin_general_coppermine_news_end&amp;top=1', '600', '300');
            ob_start();
            starttable('100%');
            print <<< EOT
            <tr>
              <td>
                <table border="0" cellspacing="0" cellpadding="0" width="100%">
                  <tr>
                    <td class="tableh1">
                      {$lang_version_alert['coppermine_news']}{$help_news}
                    </td>
                    <td class="tableh1" align="right">
                      <a href="mode.php?what=news&amp;referer={$REFERER}" class="admin_menu">{$lang_version_alert['hide']}</a>
                    </td>
                  </tr>
                  <tr>
                    <td class="tableb" colspan="2">
EOT;
            // Try to retrieve the news directly
            $result = cpgGetRemoteFileByURL('http://coppermine-gallery.net/cpg15x_news.htm', 'GET','','200');
            if (strlen($result['body']) < 200) { // retrieving the file failed - let's display it in an iframe then
                    print <<< EOT
                      <iframe src="http://coppermine-gallery.net/cpg15x_news.htm" align="left" frameborder="0" scrolling="auto" marginheight="0" marginwidth="0" width="100%" height="100" name="coppermine_news" id="coppermine_news" class="textinput">
                        {$lang_version_alert['no_iframe']}
                      </iframe>
EOT;
            } else { // we have been able to retrieve the remote URL, let's chop the unneeded data and then display it
                    unset($result['headers']);
                    unset($result['error']);
                    // drop everything before the starting body-tag
                    //$result['body'] = substr($result['body'], strpos($result['body'], '<body>'));
                    $result['body'] = strstr($result['body'], '<body>');
                    // drop the starting body tag itself
                    $result['body'] = str_replace('<body>', '', $result['body']);
                    // drop the ending body tag and everything after it
                    $result['body'] = str_replace(strstr($result['body'], '</body>'), '', $result['body']);
                    // The result should now contain everything between the body tags - let's print it
                    print $result['body'];
            }
            print <<< EOT
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
EOT;
            endtable();
            print '<br />';
            $return .= ob_get_contents();
            ob_end_clean();
        }
        return $return;
}

/**
 * cpg_display_help()
 *
 * @param string $reference
 * @param string $width
 * @param string $height
 * @return
 **/

function cpg_display_help($reference = 'f=index.htm', $width = '500', $height = '200') {
global $CONFIG, $USER, $lang_common ;
if ($reference == '' || $CONFIG['enable_help'] == '0') {return; }
if ($CONFIG['enable_help'] == '2' && GALLERY_ADMIN_MODE == false) {return; }
$help_theme = $CONFIG['theme'];
if (isset($USER['theme'])) {
    $help_theme = $USER['theme'];
	}
	$title_help = 	$lang_common['help'];
	$help_html = "<a class=\"jt\" href='help.php?". $reference."'  rel='help.php?". $reference."' title=".$title_help." ><img src=\"images/help.gif\" width=\"13\" height=\"11\" border=\"0\"  /></a>";
return $help_html;
}

/**
* Multi-dim array sort, with ability to sort by two and more dimensions
* Coded by Ichier2003, available at php.net
* syntax:
* $array = array_csort($array [, 'col1' [, SORT_FLAG [, SORT_FLAG]]]...);
**/
function array_csort() {
   $args = func_get_args();
   $marray = array_shift($args);

   $msortline = "return(array_multisort(";
   foreach ($args as $arg) {
       $i++;
       if (is_string($arg)) {
           foreach ($marray as $row) {
               $sortarr[$i][] = $row[$arg];
           }
       } else {
           $sortarr[$i] = $arg;
       }
       $msortline .= "\$sortarr[".$i."],";
   }
   $msortline .= "\$marray));";

   eval($msortline);
   return $marray;
}

function cpg_get_bridge_db_values() {
global $CONFIG;
// Retrieve DB stored configuration
$results = cpg_db_query("SELECT * FROM {$CONFIG['TABLE_BRIDGE']}");
while ($row = mysql_fetch_array($results)) {
    $BRIDGE[$row['name']] = $row['value'];
} // while
mysql_free_result($results);
return $BRIDGE;
}

function cpg_get_webroot_path() {
    global $CPG_PHP_SELF;

    $superCage = Inspekt::makeSuperCage();
    // get the webroot folder out of a given PHP_SELF of any coppermine page

    // what we have: we can say for sure where we are right now: $PHP_SELF (if the server doesn't even have it, there will be problems everywhere anyway)

    // let's make those into an array:
    if ($matches = $superCage->server->getMatched('SCRIPT_FILENAME', '/^[a-z,A-Z0-9_-\/\\:.]+$/')) {
            $path_from_serverroot[] = $matches[0];
    }
    //$path_from_serverroot[] = $_SERVER["SCRIPT_FILENAME"];
    /*if (isset($_SERVER["PATH_TRANSLATED"])) {
       $path_from_serverroot[] = $_SERVER["PATH_TRANSLATED"];
    }*/
    if ($matches = $superCage->server->getMatched('PATH_TRANSLATED', '/^[a-z,A-Z0-9_-\/\\:.]+$/')) {
        $path_from_serverroot[] = $matches[0];
    }
    //$path_from_serverroot[] = $HTTP_SERVER_VARS["SCRIPT_FILENAME"];
    //$path_from_serverroot[] = $HTTP_SERVER_VARS["PATH_TRANSLATED"];

    // we should be able to tell the current script's filename by removing everything before and including the last slash in $PHP_SELF
    $filename = ltrim(strrchr($CPG_PHP_SELF, '/'), '/');

    // let's eliminate all those vars that don't contain the filename (and replace the funny notation from windows machines)
    foreach($path_from_serverroot as $key) {
        $key = str_replace('\\', '/', $key); // replace the windows notation
        $key = str_replace('//', '/', $key); // replace duplicate forwardslashes
        if(strstr($key, $filename) != FALSE) { // eliminate all that don't contain the filename
            $path_from_serverroot2[] = $key;
        }
    }

    // remove double entries in the array
    $path_from_serverroot3 = array_unique($path_from_serverroot2);

    // in the best of all worlds, the array is not empty
    if (is_array($path_from_serverroot3)) {
        $counter = 0;
        foreach($path_from_serverroot3 as $key) {
            // easiest possible solution: $PHP_SELF is contained in the array - if yes, we're lucky (in fact we could have done this before, but I was going to leave room for other checks to be inserted before this one)
            if(strstr($key, $CPG_PHP_SELF) != FALSE) { // eliminate all that don't contain $PHP_SELF
                $path_from_serverroot4[] = $key;
                $counter++;
            }
        }
    } else {
        // we're f***ed: the array is empty, there's no server var we could actually use
        $return = '';
    }

    if ($counter == 1) { //we have only one entry left - we're happy
        $return = $path_from_serverroot4[0];
    } elseif ($counter == 0) { // we're f***ed: the array is empty, there's no server var we could actually use
        $return = '';
    } else { // there is more than one entry, and they differ. For now, let's use the first one. Maybe we could do some advanced checking later
        $return = $path_from_serverroot4[0];
    }

    // strip the content from $PHP_SELF from the $return var and we should (hopefully) have the absolute path to the webroot
    $return = str_replace($CPG_PHP_SELF, '', $return);

    // the return var should at least contain a slash - if it doesn't, add it (although this is more or less wishfull thinking)
    if ($return == '') {
        $return = '/';
    }

    return $return;
}

/**
 * Function to get the search string if the picture is viewed from google, lucos or yahoo search engine
 */

function get_search_query_terms($engine = 'google') {
  global $s, $s_array;

  $superCage = Inspekt::makeSuperCage();

  //Using getRaw(). $referer is sanitized below wherever needed
  $referer = urldecode($superCage->server->getRaw('HTTP_REFERER'));
  $query_array = array();
  switch ($engine) {
    case 'google':
      // Google query parsing code adapted from Dean Allen's
      // Google Hilite 0.3. http://textism.com
      $query_terms = preg_replace('/^.*q=([^&]+)&?.*$/i','$1', $referer);
      $query_terms = preg_replace('/\'|"/', '', $query_terms);
      $query_array = preg_split ("/[\s,\+\.]+/", $query_terms);
      break;

    case 'lycos':
      $query_terms = preg_replace('/^.*query=([^&]+)&?.*$/i','$1', $referer);
      $query_terms = preg_replace('/\'|"/', '', $query_terms);
      $query_array = preg_split ("/[\s,\+\.]+/", $query_terms);
      break;

    case 'yahoo':
      $query_terms = preg_replace('/^.*p=([^&]+)&?.*$/i','$1', $referer);
      $query_terms = preg_replace('/\'|"/', '', $query_terms);
      $query_array = preg_split ("/[\s,\+\.]+/", $query_terms);
      break;
  }
  return $query_array;
}


function is_referer_search_engine($engine = 'google') {
  //$siteurl = get_settings('home');
  $superCage = Inspekt::makeSuperCage();

  //Using getRaw(). $referer is sanitized below wherever needed
  $referer = urldecode($superCage->server->getRaw('HTTP_REFERER'));
    //echo "referer is: $referer<br />";
  if ( ! $engine ) {
    return 0;
  }

  switch ($engine) {
  case 'google':
    if (preg_match('|^http://(www)?\.?google.*|i', $referer)) {
      return 1;
    }
    break;

    case 'lycos':
    if (preg_match('|^http://search\.lycos.*|i', $referer)) {
      return 1;
    }
        break;

    case 'yahoo':
    if (preg_match('|^http://search\.yahoo.*|i', $referer)) {
      return 1;
    }
    break;
  }
  return 0;
}

/**
 * cpg_get_custom_include()
 *
 * @param string $path
 * @return
 **/
function cpg_get_custom_include($path = '')
{
    global $CONFIG;
    $return = '';
    // check if path is set in config
    if ($path == '')
    {
        return $return;
    }
    // check if the include file exists
    if (!file_exists($path))
    {
        return $return;
    }
    ob_start();
    include($path);
    $return = ob_get_contents();
    ob_end_clean();
    // crude sub-routine to remove the most basic "no-no" stuff from possible includes
    // could need improvement
    $return = str_replace('<html>', '', $return);
    $return = str_replace('<head>', '', $return);
    $return = str_replace('<body>', '', $return);
    $return = str_replace('</html>', '', $return);
    $return = str_replace('</head>', '', $return);
    $return = str_replace('</body>', '', $return);
    return $return;
}

/**
 * filter_content()
 *
 * Replace strings that match badwords with tokens indicating it has been filtered.
 *
 * @param string or array $str
 * @return string or array
 **/
function filter_content($str)
{
    global $lang_bad_words, $CONFIG, $ercp;
    if ($CONFIG['filter_bad_words']) {
        static $ercp=array();
        if (!count($ercp)) foreach($lang_bad_words as $word) {
            $ercp[] = '/' . ($word[0] == '*' ? '': '\b') . str_replace('*', '', $word) . ($word[(strlen($word)-1)] == '*' ? '': '\b') . '/i';
        }
        if (is_array($str)) {
            $new_str=array();
            foreach ($str as $key=>$element) {
                $new_str[$key]=filter_content($element);
            }
            $str=$new_str;
        } else {
        $stripped_str = strip_tags($str);
        $str = preg_replace($ercp, '(...)', $stripped_str);
        }
    }
return $str;
}

/*function get_post_var($name, $default = '')
{
    return isset($_POST[$name]) ? $_POST[$name] : $default;
}*/

function utf_strtolower($str)
{
  if (!function_exists('mb_strtolower')) { require 'include/mb.inc.php'; }
  return mb_strtolower($str);
}
function utf_substr($str, $start, $end=null)
{
  if (!function_exists('mb_substr')) { require 'include/mb.inc.php'; }
  return mb_substr($str, $start, $end);
}
function utf_strlen($str)
{
  if (!function_exists('mb_strlen')) { require 'include/mb.inc.php'; }
  return mb_strlen($str);
}
function utf_ucfirst($str)
{
  if (!function_exists('mb_strtoupper')) { require 'include/mb.inc.php'; }
  return mb_strtoupper(mb_substr($str, 0, 1)).mb_substr($str, 1);
}

/*
  This function replaces special UTF characters to their ANSI equivelant for
  correct processing by MySQL, keywords, search, etc. since a bug has been
  found:  http://coppermine-gallery.net/forum/index.php?topic=17366.0
*/
function utf_replace($str)
{
        # replace unicode spaces
        $str = preg_replace('#[\xC2\xA0]|[\xE3][\x80][\x80]#', ' ', $str);
        return $str;
}

function replace_forbidden($str)
{
  static $forbidden_chars;
  if (!is_array($forbidden_chars)) {
    global $CONFIG, $mb_utf8_regex;
    if (function_exists('html_entity_decode')) {
      $chars = html_entity_decode($CONFIG['forbiden_fname_char'], ENT_QUOTES, 'UTF-8');
    } else {
      $chars = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;', '&nbsp;', '&#39;'), array('&', '"', '<', '>', ' ', "'"), $CONFIG['forbiden_fname_char']);
    }
    preg_match_all("#$mb_utf8_regex".'|[\x00-\x7F]#', $chars, $forbidden_chars);
  }
  /**
   * $str may also come from $_POST, in this case, all &, ", etc will get replaced with entities.
   * Replace them back to normal chars so that the str_replace below can work.
   */
  $str = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $str);;

  $return = str_replace($forbidden_chars[0], '_', $str);

  /**
  * Fix the obscure, misdocumented "feature" in Apache that causes the server
  * to process the last "valid" extension in the filename (rar exploit): replace all
  * dots in the filename except the last one with an underscore.
  */
  // This could be concatenated into a more efficient string later, keeping it in three
  // lines for better readability for now.
  $extension = ltrim(substr($return,strrpos($return,'.')),'.');
  $filenameWithoutExtension = str_replace('.' . $extension, '', $return);
  $return = str_replace('.', '_', $filenameWithoutExtension) . '.' . $extension;

  return $return;
}

/**
 * resetDetailHits()
 *
 * Reset the detailed hits stored in hit_stats table for the given pid
 *
 * @param int or array $pid
 **/
function resetDetailHits($pid)
{
  global $CONFIG;

  if (is_array($pid)) {
    if (!count($pid)) {
      return;
    } else {
      $clause = "pid IN (".implode(',', $pid).")";
    }
  } else {
    $clause = "pid = '$pid'";
  }

  $query = "DELETE FROM {$CONFIG['TABLE_HIT_STATS']} WHERE $clause";
  cpg_db_query($query);
}

/**
 * resetDetailVotes()
 *
 * Reset the detailed votes stored in vote_stats table for the given pid
 *
 * @param int or array $pid
 **/
function resetDetailVotes($pid)
{
  global $CONFIG;

  if (is_array($pid)) {
    if (!count($pid)) {
      return;
    } else {
      $clause = "pid IN (".implode(',', $pid).")";
    }
  } else {
    $clause = "pid = '$pid'";
  }

  $query = "DELETE FROM {$CONFIG['TABLE_VOTE_STATS']} WHERE $clause";
  cpg_db_query($query);
}

/**
* cpgValidateColor()
*
* Validate a string: is a color code in x11 or hex?
*
* Returns the validated color string (hex with a leading #-sign or x11 color-code, or nothing if not valid)
*
* @param string $color
* @return $color
**/
function cpgValidateColor($color) {
  $x11ColorNames = array('white', 'ivory', 'lightyellow', 'yellow', 'snow', 'floralwhite', 'lemonchiffon', 'cornsilk', 'seashell', 'lavenderblush', 'papayawhip', 'blanchedalmond', 'mistyrose', 'bisque', 'moccasin', 'navajowhite', 'peachpuff', 'gold', 'pink', 'lightpink', 'orange', 'lightsalmon', 'darkorange', 'coral', 'hotpink', 'tomato', 'orangered', 'deeppink', 'fuchsia', 'magenta', 'red', 'oldlace', 'lightgoldenrodyellow', 'linen', 'antiquewhite', 'salmon', 'ghostwhite', 'mintcream', 'whitesmoke', 'beige', 'wheat', 'sandybrown', 'azure', 'honeydew', 'aliceblue', 'khaki', 'lightcoral', 'palegoldenrod', 'violet', 'darksalmon', 'lavender', 'lightcyan', 'burlywood', 'plum', 'gainsboro', 'crimson', 'palevioletred', 'goldenrod', 'orchid', 'thistle', 'lightgrey', 'tan', 'chocolate', 'peru', 'indianred', 'mediumvioletred', 'silver', 'darkkhaki', 'rosybrown', 'mediumorchid', 'darkgoldenrod', 'firebrick', 'powderblue', 'lightsteelblue', 'paleturquoise', 'greenyellow', 'lightblue', 'darkgray', 'brown', 'sienna', 'yellowgreen', 'darkorchid', 'palegreen', 'darkviolet', 'mediumpurple', 'lightgreen', 'darkseagreen', 'saddlebrown', 'darkmagenta', 'darkred', 'blueviolet', 'lightskyblue', 'skyblue', 'gray', 'olive', 'purple', 'maroon', 'aquamarine', 'chartreuse', 'lawngreen', 'mediumslateblue', 'lightslategray', 'slategray', 'olivedrab', 'slateblue', 'dimgray', 'mediumaquamarine', 'cornflowerblue', 'cadetblue', 'darkolivegreen', 'indigo', 'mediumturquoise', 'darkslateblue', 'steelblue', 'royalblue', 'turquoise', 'mediumseagreen', 'limegreen', 'darkslategray', 'seagreen', 'forestgreen', 'lightseagreen', 'dodgerblue', 'midnightblue', 'aqua', 'cyan', 'springgreen', 'lime', 'mediumspringgreen', 'darkturquoise', 'deepskyblue', 'darkcyan', 'teal', 'green', 'darkgreen', 'blue', 'mediumblue', 'darkblue', 'navy', 'black');
  if (in_array(strtolower($color), $x11ColorNames) == TRUE) {
    return $color;
  } else {
    $color = ltrim($color, '#'); // strip a leading #-sign if there is one
    if (preg_match('/^[a-f\d]+$/i', strtolower($color)) == TRUE && strlen($color)<=6) {
      $color = '#'.strtoupper($color);
      return $color;
    }
  }
}

/**
* cpgStoreTempMessage()
*
* Store a temporary message to the database to carry over from one
page to the other
*
*
*
* @param string $message
* @return $message_id
**/
function cpgStoreTempMessage($message) {
  global $CONFIG;
  $message = urlencode($message);
  // come up with a unique message id
  $message_id = ereg_replace("[^A-Za-z0-9]", "",
base64_encode(rand(10000,30000).time().USER_ID.md5($message)));
  // write the message to the database
  $user_id = USER_ID;
  $time = time();

  // Insert the record in database
  $query = "INSERT INTO {$CONFIG['TABLE_TEMP_MESSAGES']}
                    SET
                      message_id = '$message_id',
                      user_id = '$user_id',
                      time   = '$time',
                      message = '$message'";
  cpg_db_query($query);
  // return the message_id
  return $message_id;
}

/**
* cpgFetchTempMessage()
*
* Fetch a temporary message from the database and then delete it.
*
*
*
* @param string $message_id
* @return $message
**/
function cpgFetchTempMessage($message_id) {
  global $CONFIG;
  $user_id = USER_ID;
  $time = time();
  $message = '';

  // Read the record in database
  $query = "SELECT message AS message FROM {$CONFIG['TABLE_TEMP_MESSAGES']} WHERE message_id = '$message_id' LIMIT 1";
  $result = cpg_db_query($query);
  if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_row($result);
        $message = urldecode($row[0]);
  }
  // delete the message once fetched
  $query = "DELETE FROM {$CONFIG['TABLE_TEMP_MESSAGES']} WHERE message_id = '$message_id'";
  cpg_db_query($query);
  // return the message
  return $message;
}

/**
* cpgCleanTempMessage()
*
* Clean up the temporary messages table (garbage collection).
*
* @param string $seconds
* @return void
**/
function cpgCleanTempMessage($seconds = 3600) {
  global $CONFIG;
  $time = time() - (int)$seconds;
  // delete the messages older than the specified amount
  $query = "DELETE FROM {$CONFIG['TABLE_TEMP_MESSAGES']} WHERE time < '$time'";
  cpg_db_query($query);
}

/**
* cpgRedirectPage()
*
* Redirect to the target page or display an info screen first and then redirect
*
* @param string $targetAddress
* @param string $caption
* @param string $message
* @param string $countdown
* @return void
**/
function cpgRedirectPage($targetAddress = '', $caption = '', $message = '', $countdown = 0) {
  global $CONFIG, $lang_common;
  if ($CONFIG['display_redirection_page'] == 0) {
    $header_location = (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE'))) ? 'Refresh: 0; URL=' : 'Location: ';
    if (strpos($targetAddress, '?') == FALSE) {
      $separator = '?';
    } else {
      $separator = '&';
    }
    header($header_location . $targetAddress.$separator.'message_id='.cpgStoreTempMessage($message).'#cpgMessageBlock');
    pageheader($caption, "<META http-equiv=\"refresh\" content=\"1;url=$targetAddress\">");
    msg_box($caption, $message, $lang_common['continue'], $location);
    pagefooter();
    ob_end_flush();
    exit;
  } else {
    pageheader($caption, "<META http-equiv=\"refresh\" content=\"1;url=$targetAddress\">");
    msg_box($caption, $message, $lang_common['continue'], $location);
    pagefooter();
    ob_end_flush();
    exit;
  }
}

/**
* cpgGetScriptNameParams()
*
* Returns the script name and all vars except the ones defined in exception (which could be an array or a var).
* Particularly helpfull to create links
*
* @param mixed $exception
* @return $return
**/
function cpgGetScriptNameParams($exception = '') {
    $superCage = Inspekt::makeSuperCage();

    if(!is_array($exception)) {
        $exception = array(0 => $exception);
    }

    // get the file name first
    $match = $superCage->server->getRaw('SCRIPT_NAME'); // We'll sanitize the script path later
    $filename = ltrim(strrchr($match,'/'), '/'); // Drop everything untill (and including) the last slash, this results in the file name only
    if (!preg_match('/^(([a-zA-Z0-9_\-]){1,})((\.){1,1})(([a-zA-Z]){2,6})+$/', $filename)) { // the naming pattern we check against: an infinite number of lower and upper case alphanumerals plus allowed special chars dash and underscore, then one (and only one!) dot, then between two and 6 alphanumerals in lower or upper case
       $filename = 'index.php'; // If this doesn't match, default to the index page
    }
    $return = $filename . '?';

    // Now get the parameters.
    // WARNING: as this function is meant to just return the URL parameters (minus the one mentioned in $exception),
    // neither the parameter names nor the the values should be sanitized, as we simply don't know here against what
    // we're suppossed to sanitize.
    // For now, I have chosen the safe method, sanitizing the parameters. Not sure if this is a bright idea for the
    // future.
    // So, use the parameters returned from this function here with the same caution that applies to anything the
    // user could tamper with. The function is meant to help you generate links (in other words: something the user could
    // come up with by typing them just as well), so don't abuse this function for anything else.
     $matches = $superCage->server->getMatched('QUERY_STRING', '/^[a-zA-Z0-9&=_\/.]+$/');
     if ($matches) {
        $queryString = explode('&', $matches[0]);
     } else {
        $queryString = array();
     }

     foreach ($queryString as $val) {
        list($key, $value) = explode('=', $val);
        if (!in_array($key,$exception)) {
            $return .= $key . "=" . $value . "&";
        }
     }

    return $return;
}

/**
* cpgValidateDate()
*
* Returns $date if $date contains a valid date string representation (yyyy-mm-dd). Returns an empty string if not.
*
* @param mixed $date
* @return $return
**/
function cpgValidateDate($date) {
    $pattern = '^(19|20)([0-9]{2}-((0[13-9]|1[0-2])-(0[1-9]|[12][0-9]|30)|(0[13578]|1[02])-31|02-(0[1-9]|1[0-9]|2[0-8]))|([2468]0|[02468][48]|[13579][26])-02-29)$';
    if (ereg($pattern, $date) == TRUE) {
        $return = $date;
    } else {
        $return = '';
    }
    return $return;
}


/**
* cpgGetRemoteFileByURL()
*
* Returns array that contains content of a file (URL) retrieved by curl, fsockopen or fopen (fallback). Array consists of:
* $return['headers'] = header array,
* $return['error'] = error number and messages array (if error)
* $return['body'] = actual content of the fetched file as string
*
* @param mixed $url, $method, $data, $redirect
* @return array
**/
function cpgGetRemoteFileByURL($remoteURL, $method = "GET", $redirect = 10, $minLength = '0') {
    global $lang_get_remote_File_by_url;
    // FSOCK code snippets taken from http://jeenaparadies.net/weblog/2007/jan/get_remote_file
    $url = parse_url($remoteURL); // chop the URL into protocol, domain, port, folder, file, parameter
    $error = '';
    $lineBreak = "<br />\r\n";
    // Let's try CURL first
    if (function_exists('curl_init') == TRUE) { // don't bother to try curl if it isn't there in the first place
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $remoteURL);
      curl_setopt($curl, CURLOPT_HEADER, 0);
      ob_start();
      curl_exec($curl);
      $body = ob_get_contents();
      ob_end_clean();
      ob_end_flush();
      $headers = curl_getinfo($curl);
      curl_close($curl);
      if (strlen($body) < $minLength ) {
              // Fetching the data by CURL obviously failed
              $error .= sprintf($lang_get_remote_File_by_url['no_data_returned'], $lang_get_remote_File_by_url['curl']) . $lineBreak;
      } else {
              // Fetching the data by CURL was successfull. Let's return the data
              return array("headers" => $headers, "body" => $body);
      }
    } else {
      // Curl is not available
      $error .= $lang_get_remote_File_by_url['curl_not_available'] . $lineBreak;
    }
    // Now let's try FSOCKOPEN
    if ($url['host'] != ''){
      $fp = @fsockopen ($url['host'], (!empty($url['port']) ? (int)$url['port'] : 80), $errno, $errstr, 30);
      if ($fp) { // fsockopen file handle success - start
          $path = (!empty($url['path']) ? $url['path'] : "/").(!empty($url['query']) ? "?".$url['query'] : "");
          $header = "\r\nHost: ".$url['host'];
          fputs ($fp, $method." ".$path." HTTP/1.0".$header."\r\n\r\n".("post" == strtolower($method) ? $data : ""));
          if(!feof($fp)) {
            $scheme = fgets($fp);
            //list(, $code ) = explode(" ", $scheme);
            $headers = explode(" ", $scheme);
            //$headers = array("Scheme" => $scheme);
          }
          while ( !feof($fp) ) {
              $h = fgets($fp);
              if($h == "\r\n" OR $h == "\n") {
                break;
              }
              list($key, $value) = explode(":", $h, 2);
              $key = strtolower($key);
              $value = trim($value);
              if(isset($headers[$key])) {
                $headers[$key] .= ','.trim($value);
              } else {
                $headers[$key] = trim($value);
              }
          }
          $body = '';
          while ( !feof($fp) ) {
            $body .= fgets($fp);
          }
          fclose($fp);
          if (strlen($body) < $minLength) {
                // Fetching the data by FSOCKOPEN obviously failed
                $error .= sprintf($lang_get_remote_File_by_url['no_data_returned'], $lang_get_remote_File_by_url['fsockopen']) . $lineBreak;
          } elseif (in_array('404', $headers) == TRUE) {
                // We got a 404 error
                $error .= sprintf($lang_get_remote_File_by_url['error_number'], '404') . $lineBreak;
          } else {
                // Fetching the data by FSOCKOPEN was successfull. Let's return the data
                return array("headers" => $headers, "body" => $body, "error" => $error);
          }
      } else {  // fsockopen file handle failure - start
              $error .= $lang_get_remote_File_by_url['fsockopen'] . ': ';
              $error .= sprintf($lang_get_remote_File_by_url['error_number'], $errno);
              $error .= sprintf($lang_get_remote_File_by_url['error_message'], $errstr);
      }
    } else {
      //$error .= 'No Hostname set. In other words: we\'re trying to retrieve a local file';
    }
    // Finally, try FOPEN
    @ini_set('allow_url_fopen','1'); // Try to override the existing policy
    if ($url['scheme'] != '') {
      $protocol = $url['scheme'].'://';
    }  else {
      $protocol = '';
    }
    if ($url['port'] != '') {
      $port = ':'.(int)$url['port'];
    }  elseif($url['host'] != '') {
      $port = ':80';
    } else {
      $port = '';
    }
    $handle  = @fopen($protocol.$url['host'].$port.$url['path'], 'r');
    if ($handle) {
      while(!feof($handle)) {
        $body .= fread($handle, 1024);
      }
      fclose($handle);
      if (strlen($body) < $minLength) {
        $error .= sprintf($lang_get_remote_File_by_url['no_data_returned'], $lang_get_remote_File_by_url['fopen']) . $lineBreak;
      } else {
        // Fetching the data by FOPEN was successfull. Let's return the data
        return array("headers" => $headers, "body" => $body, "error" => $error);
      }
    } else { // opening the fopen handle failed as well
      // if the script reaches this stage, all available methods failed, so let's return the error messages and give up
      return array("headers" => $headers, "body" => $body, "error" => $error);
    }
}

/**
* user_is_allowed()
*
* Check if a user is allowed to edit pictures/albums
*
* @return boolean $check_approve
*/
function user_is_allowed () {
        if(GALLERY_ADMIN_MODE) {
                return true;
        }
        $check_approve = false;
        global $USER_DATA, $CONFIG;
        $superCage = Inspekt::makeSuperCage();

        //get albums this user can edit
        if ($superCage->get->keyExists('album')){
            $album_id = $superCage->get->getInt('album');
        } elseif ($superCage->post->keyExists('aid')){
                $album_id = $superCage->post->getInt('aid');
        } else {
                        //workaround when going straight to modifyalb.php and no album is set in superglobals
                        if(defined('MODIFYALB_PHP')){
                                //check if the user has any album available
                                $result = cpg_db_query("SELECT aid FROM {$CONFIG['TABLE_ALBUMS']} WHERE owner = " . $USER_DATA['user_id'] . " LIMIT 1");
                                $temp_album_id = cpg_db_fetch_row($result);
                                $album_id = $temp_album_id['aid'];
                        }else{
                            $album_id = 0;
                        }

        }

        $result = cpg_db_query("SELECT DISTINCT category FROM {$CONFIG['TABLE_ALBUMS']} WHERE owner = '" . $USER_DATA['user_id'] . "' AND aid='$album_id'");
        $allowed_albums = cpg_db_fetch_rowset($result);
        $cat = $allowed_albums[0]['category'];
        if($cat != ''){
                        $check_approve = true;
        }

        //check if admin allows editing after closing category
        if($CONFIG['allow_user_edit_after_cat_close'] == 0){
                        //Disallowed -> Check if album is in such a category
                        $result = cpg_db_query("SELECT DISTINCT aid FROM {$CONFIG['TABLE_ALBUMS']} AS alb INNER JOIN {$CONFIG['TABLE_CATMAP']} AS catm ON alb.category=catm.cid WHERE alb.owner = '" . $USER_DATA['user_id'] . "' AND alb.aid='$album_id' AND catm.group_id='" . $USER_DATA['group_id'] . "'");
                        $allowed_albums = cpg_db_fetch_rowset($result);
                        if($allowed_albums[0]['aid'] == '' && $cat != (FIRST_USER_CAT + USER_ID)){
                                $check_approve = false;
                        } elseif ($cat == (FIRST_USER_CAT + USER_ID)) {
                                $check_approve = true;
                        }
        }
        return $check_approve;
}

/**
 * Function to set/output js files to be included.
 *
 * This function sets a js file to be included in the head section of the html (in theme_javascript_head() function).
 * This function should be called before pageheader function since the js files are included in pageheader.
 * If the optional second paramter is passed as true then instead of setting it for later use the html for
 * js file inclusion is returned right away
 *
 * @param string $filename Relative path, from the root of cpg, to the js file
 * @param boolean $inline If true then the html is returned
 * @return mixed Returns the html for js inclusion or null if inline is false
 */
function js_include($filename, $inline = false) {
        global $JS;

        // Proceed with inclusion only if the file exists
        if (!file_exists($filename)) {
                return;
        }

        // If we need to show the html inline then return the required html
        if ($inline) {
                return '<script type="text/javascript" src="' . $filename . '"></script>';
        } else {
                // Else add the file to js includes array which will later be used in head section
                $JS['includes'][] = $filename;
        }
}

/**
 * Function to set a js var from php
 *
 * This function sets a js var in an array. This array is later converted to json string and outputted
 * in the head section of html (in theme_javascript_head function).
 * All variables which are set using this function can be accessed in js using the json object named js_vars.
 *
 * Ex: If you set a variable: set_js_var('myvar', 'myvalue')
 * then you can access it in js using : js_vars.myvar
 *
 * @param string $var Name of the variable by which the value will be accessed in js
 * @param mixed $val Value which can be string, int, array or boolean
 */
function set_js_var($var, $val) {
        global $JS;

        // Add the variable to global array which will be used in theme_javascript_head() function
        $JS['vars'][$var] = $val;
}

/**
 * Function to convert php array to json
 *
 * This function is equivalent to PHP 5.2 's json_encode. PHP's native function will be used if the version
 * is greater than equal to 5.2
 *
 * @param array $arr Array which is to be converted to json string
 * @return string json string
 */
if (!function_exists('json_encode')) {
	function json_encode($arr) {
		// If the arr is object then gets its variables
		if (is_object($arr)) {
			$arr = get_object_vars($arr);
		}
	
		$out = array();
		$keys = array();
		// If arr is array then get its keys
		if (is_array($arr)) {
			$keys = array_keys($arr);
		}
	
		$numeric = true;
		// Find whether the keys are numeric or not
		if (!empty($keys)) {
			$numeric = (array_values($keys) === array_keys(array_values($keys)));
		}
	
		foreach ($arr as $key => $val) {
			// If the value is array or object then call json_encode recursively
			if (is_array($val) || is_object($val)) {
				$val = json_encode($val);
			} else {
				// If the value is not numeric and boolean then escape and quote it
				if ((!is_numeric($val) && !is_bool($val))) {
					$escape = array("\r\n" => '\n', "\r" => '\n', "\n" => '\n', '"' => '\"', "'" => "\\'");
					$val = str_replace(array_keys($escape), array_values($escape), $val);
					$val = '"' . $val . '"';
				}
				if ($val === null) {
					$val = 'null';
				}
				if (is_bool($val)) {
					$val = $val ? 'true' : 'false';
				}
			}
			// If key is not numeric then quote it
			if (!$numeric) {
				$val = '"' . $key . '"' . ':' . $val;
			}
	
			$out[] = $val;
		}
	
		if (!$numeric) {
			$return = '{' . implode(', ', $out) . '}';
		} else {
			$return = '[' . implode(', ', $out) . ']';
		}
	
		return $return;
	}
}

/**
 * function cpg_getimagesize()
 *
 * Try to get the size of an image, this is custom built as some webhosts disable this function or do weird things with it
 *
 * @param string $image
 * @param boolean $force_cpg_function
 * @return array $size
 */
function cpg_getimagesize($image, $force_cpg_function = false){
        if (!function_exists('getimagesize') || $force_cpg_function){
                //custom function borrowed from http://www.wischik.com/lu/programmer/get-image-size.html
                $f = @fopen($image, 'rb');
                if($f === false){
                        return false;
                }
                fseek($f, 0, SEEK_END);
                $len = ftell($f);
                if ($len < 24) {
                        fclose($f);
                        return false;
                }
                fseek($f, 0);
                $buf = fread($f, 24);
                if($buf === false){
                        fclose($f);
                        return false;
                }
                if(ord($buf[0]) == 255 && ord($buf[1]) == 216 && ord($buf[2]) == 255 && ord($buf[3]) == 224 && $buf[6] == 'J' && $buf[7] == 'F' && $buf[8] == 'I' && $buf[9] == 'F'){
                        $pos=2;
                        while (ord($buf[2]) == 255){
                                if (ord($buf[3]) == 192 || ord($buf[3]) == 193 || ord($buf[3]) == 194 || ord($buf[3]) == 195 || ord($buf[3]) == 201 || ord($buf[3]) == 202 || ord($buf[3]) == 203){
                                        break; // we've found the image frame
                                }
                                $pos += 2 + (ord($buf[4]) << 8) + ord($buf[5]);
                                if ($pos+12>$len){
                                        break; // too far
                                }
                                fseek($f,$pos);
                                $buf = $buf[0] . $buf[1] . fread($f,12);
                        }
                }
                fclose($f);

                // GIF:
                if($buf[0] == 'G' && $buf[1] == 'I' && $buf[2] == 'F'){
                        $x = ord($buf[6]) + (ord($buf[7])<<8);
                        $y = ord($buf[8]) + (ord($buf[9])<<8);
                        $type = 1;
                }

                // JPEG:
                if(ord($buf[0]) == 255 && ord($buf[1]) == 216 && ord($buf[2]) == 255){
                        $y = (ord($buf[7])<<8) + ord($buf[8]);
                        $x = (ord($buf[9])<<8) + ord($buf[10]);
                        $type = 2;
                }

                // PNG:
                if(ord($buf[0]) == 0x89 && $buf[1] == 'P' && $buf[2] == 'N' && $buf[3] == 'G' && ord($buf[4]) == 0x0D && ord($buf[5]) == 0x0A && ord($buf[6]) == 0x1A && ord($buf[7]) == 0x0A && $buf[12] == 'I' && $buf[13] == 'H' && $buf[14] == 'D' && $buf[15] == 'R'){
                        $x = (ord($buf[16])<<24) + (ord($buf[17])<<16) + (ord($buf[18])<<8) + (ord($buf[19])<<0);
                        $y = (ord($buf[20])<<24) + (ord($buf[21])<<16) + (ord($buf[22])<<8) + (ord($buf[23])<<0);
                        $type = 3;
                }

                if (isset($x, $y, $type)){
                        return false;
                }
                return array($x, $y, $type, 'height="' . $x . '" width="' . $y . '"');
        }else{
                $size = getimagesize($image);
                if(!$size){
                        //false was returned
                        return cpg_getimagesize($image, true/*force the use of custom function*/);
                }else if(!isset($size[0]) || !isset($size[1])){
                        //webhost possibly changed getimagesize functionality
                        return cpg_getimagesize($image, true/*force the use of custom function*/);
                }else {
                        //function worked as expected, return the results
                        return $size;
                }
        }
}

/**
 * function pub_user_albums()
 *
 * Get a list of public and user albums in upload.php and the API
 *
 */
function pub_user_albums() {
global $CONFIG, $public_albums_list, $user_albums_list;
	$superCage = Inspekt::makeSuperCage();
	// Filter albums by category
	$api_sql = '';
	$api_sql2 = '';
	if (defined('API_CALL')) {
	    if ($superCage->post->getAlpha('function') == 'alblist' && $catid = $superCage->post->getInt('cat')) {
	        global $CAT_LIST;
	        get_subcategory_data($catid);
	        $api_sql .= " AND (";
	        foreach ($CAT_LIST as $cat) {
	            $api_sql .= "category = {$cat['cid']} OR ";
	        }
	        $api_sql .= "category = $catid)";
	    }
	    if ($superCage->post->getAlpha('function') == 'piclist' && $album = $superCage->post->getInt('album')) {
	        if ($password = $superCage->post->getEscaped('password')) {
	            $sql = "SELECT aid FROM " . $CONFIG['TABLE_ALBUMS'] . " WHERE alb_password='$password' AND aid='$album'";
                    $result = cpg_db_query($sql);
                    if (mysql_num_rows($result)) {
                        $api_sql2 = "aid = $album";
	            }
	            else {
	                throw new OAuthException('Bad album password');
	            }
	        }    
	        else {
	            $api_sql2 = "aid = $album";
	        }
	    }
	}
	
	if (GALLERY_ADMIN_MODE) {
	    if ($api_sql2) {
	        $api_sql2 = " AND " . $api_sql2;
	    }
	    $public_albums = cpg_db_query("SELECT aid, title, cid, name FROM {$CONFIG['TABLE_ALBUMS']} INNER JOIN {$CONFIG['TABLE_CATEGORIES']} ON cid = category WHERE category < " . FIRST_USER_CAT . $api_sql . $api_sql2);
	    //select albums that don't belong to a category
	    $public_albums_no_cat = cpg_db_query("SELECT aid, title FROM {$CONFIG['TABLE_ALBUMS']} WHERE category = 0" . $api_sql2);
	} else {
	    if ($api_sql2) {
	        $api_sql2 = " OR " . $api_sql2 . " AND alb_password = '$password'";
	    }
	    if (defined('API_CALL')) {
	        $public_albums = cpg_db_query("SELECT aid, title, cid, name FROM {$CONFIG['TABLE_ALBUMS']} INNER JOIN {$CONFIG['TABLE_CATEGORIES']} ON cid = category WHERE category < " . FIRST_USER_CAT . " AND ((visibility = '0' OR visibility IN ".USER_GROUP_SET.") OR (owner=".USER_ID."){$api_sql2})" . $api_sql);
	    }
	    else {
	        $public_albums = cpg_db_query("SELECT aid, title, cid, name FROM {$CONFIG['TABLE_ALBUMS']} INNER JOIN {$CONFIG['TABLE_CATEGORIES']} ON cid = category WHERE category < " . FIRST_USER_CAT . " AND (uploads='YES' AND (visibility = '0' OR visibility IN ".USER_GROUP_SET.")) OR (owner=".USER_ID.")");
	    }
	    //select albums that don't belong to a category
	    $public_albums_no_cat = cpg_db_query("SELECT aid, title FROM {$CONFIG['TABLE_ALBUMS']} WHERE category = 0 AND ((uploads='YES' AND (visibility = '0' OR visibility IN ".USER_GROUP_SET.")) OR (owner=".USER_ID."){$api_sql2})");   
	}
	
	if (mysql_num_rows($public_albums)) {
	$public_albums_list = cpg_db_fetch_rowset($public_albums);
	} else {
	$public_albums_list = array();
	}
	
	//do the same for non categorized albums
	if (mysql_num_rows($public_albums_no_cat)) {
	$public_albums_list_no_cat = cpg_db_fetch_rowset($public_albums_no_cat);
	} else {
	$public_albums_list_no_cat = array();
	}
	
	//merge the 2 album arrays
	if (!$api_sql) {
	    $public_albums_list = array_merge($public_albums_list, $public_albums_list_no_cat);
	}
	
	if (USER_ID) {
	    if ($api_sql) {
	        $api_sql = preg_replace('/AND/', '', $api_sql);
	        $api_sql .= " AND ";
	    }
	    if ($api_sql2) {
	        $api_sql = "aid=$album AND ";
	    }
	    
	    $user_albums = cpg_db_query("SELECT aid, title FROM {$CONFIG['TABLE_ALBUMS']} WHERE " . $api_sql . "category='" . (FIRST_USER_CAT + USER_ID) . "' ORDER BY title");
	    if (mysql_num_rows($user_albums)) {
		$user_albums_list = cpg_db_fetch_rowset($user_albums);
	    } else {
		$user_albums_list = array();
	    }
	} else {
	$user_albums_list = array();
	}
}

/**
 * function upload_form_alb_list()
 *
 * Print a list of albums in HTML for upload.php and albums or pictures in XML for the API
 * 
 * @param string $text
 * @param string $name
 *
 */
function upload_form_alb_list($text, $name, $type=0) {
    $superCage = Inspekt::makeSuperCage();
    // Pull the $CONFIG array and the GET array into the function
    global $CONFIG, $lang_upload_php;

    // Also pull the album lists into the function
    global $user_albums_list, $public_albums_list;

    // Check to see if an album has been preselected by URL addition or the last selected album. If so, make $sel_album the album number. Otherwise, make $sel_album 0.
    if ($superCage->get->keyExists('album')) {
      $sel_album = $superCage->get->getInt('album');
    } elseif ($superCage->post->keyExists('album')) {
      $sel_album = $superCage->post->getInt('album');
    } else {
      $sel_album = 0;
    }

    // Get the ancestry of the categories
    $vQuery = "SELECT cid, parent, name FROM " . $CONFIG['TABLE_CATEGORIES'] . " WHERE 1";
    $vResult = cpg_db_query($vQuery);
    $vRes = cpg_db_fetch_rowset($vResult);
    mysql_free_result($vResult);
    foreach ($vRes as $vResI => $vResV) {
        $vResRow = $vRes[$vResI];
        $catParent[$vResRow['cid']] = $vResRow['parent'];
        $catName[$vResRow['cid']] = $vResRow['name'];
    }
    $catAnces = array();
    foreach ($catParent as $cid => $cid_parent) {
        $catAnces[$cid] = '';
        while ($cid_parent != 0) {
            $catAnces[$cid] = $catName[$cid_parent] . ($catAnces[$cid]?' - '.$catAnces[$cid]:'');
            $cid_parent = $catParent[$cid_parent];
        }
    }

    // Reset counter
    $list_count = 0;

    // Cycle through the User albums
    foreach($user_albums_list as $album) {

        // Add to multi-dim array for later sorting
        $listArray[$list_count]['cat'] = $lang_upload_php['personal_albums'];
        $listArray[$list_count]['aid'] = $album['aid'];
        $listArray[$list_count]['title'] = $album['title'];
        $list_count++;
    }

    // Cycle through the public albums
    foreach($public_albums_list as $album) {

        // Set $album_id to the actual album ID
        $album_id = $album['aid'];

        // Add to multi-dim array for sorting later
        if ($album['name']) {
            $listArray[$list_count]['cat'] = $catAnces[$album['cid']] . ($catAnces[$album['cid']]?' - ':'') . $album['name'];
            $listArray[$list_count]['cid'] = $album['cid'];
        } else {
            $listArray[$list_count]['cat'] = $lang_upload_php['albums_no_category'];
            $listArray[$list_count]['cid'] = 0;
        }
        $listArray[$list_count]['aid'] = $album['aid'];
        $listArray[$list_count]['title'] = $album['title'];
        $list_count++;
    }

/* Output XML - for API */
    if (defined('API_CALL')) {
        if ($superCage->post->getAlpha('function') == 'alblist') {
            $listArray = array_csort($listArray,'aid'); // Sort the array alphabetically by aid
            echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n";
            echo "<album_list>\n";
	    foreach ($listArray as $val) {
	        echo ' <album id="' . $val['aid'] . '">' . "\n";
	        echo '  <title>' . $val['title'] . '</title>' . "\n";
                echo '  <cat_id>' . $val['cid'] . '</cat_id>' . "\n";
	        echo ' </album>' . "\n";
	    }
            echo "</album_list>";
        }

	else if ($superCage->post->getAlpha('function') == 'piclist') {
	    $listArray = array_csort($listArray,'cat','title');     // alphabetically by category name
            echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
            echo "<album"  . $superCage->post->getInt('album') . ">\n";
            foreach ($listArray as $val) {

                // Picture list
                $result = cpg_db_query("SELECT pid, title, filepath, filename, pwidth, pheight, caption FROM {$CONFIG['TABLE_PICTURES']} WHERE aid = {$val['aid']} AND approved='YES'");
                while ($row = mysql_fetch_assoc($result)) {
                    echo ' <picture id="' . $row['pid'] . '">' . "\n";
                    echo '  <title>' . $row['title'] . '</title>' . "\n";
                    echo '  <file>' . $row['filepath'] . $row['filename'] . '</file>' . "\n";
                    echo '  <width>' . $row['pwidth'] . '</width>' . "\n";
                    echo '  <height>' . $row['pheight'] . '</height>' . "\n";
                    echo '  <caption>' . $row['caption'] . '</caption>' . "\n";
                    echo ' </picture>' . "\n";                        
                }
                
                mysql_free_result($result);
            }
            
            echo "</album" . $superCage->post->getInt('album') . ">";
        }

	else {
            die('<api_error>Unknown API function.</api_error>');
        }

        header('Content-Type: text/xml');
        return;
    }


    /* Output HTML - for upload.php */

    // Sort the pulldown options by category and album name
    $listArray = array_csort($listArray,'cat','title');     // alphabetically by category name
    // $listArray = array_csort($listArray,'cid','title');  // numerically by category ID
    // print_r($listArray);exit;

	//this will create for normal files 
	if($type == 0){
    // Create the opening of the drop down box
    echo <<<EOT
    <tr>
        <td class="tableb">
            $text
        </td>
        <td class="tableb" valign="top">
            <select name="$name" class="listbox">

EOT;

    // Finally, print out the nicely sorted and formatted drop down list
    $alb_cat = '';
    echo '                <option value="">' . $lang_upload_php['select_album'] . "</option>\n";
    
    foreach ($listArray as $val) {
        //if ($val['cat'] != $alb_cat) {  // old method compared names which might not be unique
        if ($val['cid'] != $alb_cat) {
            if ($alb_cat) echo "                </optgroup>\n";
            echo '                <optgroup label="' . $val['cat'] . '">' . "\n";
            $alb_cat = $val['cid'];
        }
        echo '                <option value="' . $val['aid'] . '"' . ($val['aid'] == $sel_album ? ' selected' : '') . '>   ' . $val['title'] . "</option>\n";
    }
    if ($alb_cat) echo "                </optgroup>\n";

    // Close the drop down
    echo <<<EOT
            </select>
        </td>
    </tr>

EOT;
	} //end
	
	/**this create for set javascript variable*/
	if($type == 1){
		$jDropDwon = '';
		$jDropDwon .=  <<<EOT
    		<div>
        		<lable>
		            $text
        		</lable>
        <div>
            <select name="$name" class="listbox">

EOT;

    // Finally, print out the nicely sorted and formatted drop down list
    $alb_cat = '';
    		$jDropDwon	.=  '<option value="">' . $lang_upload_php['select_album'] . "</option>\n";
    
    foreach ($listArray as $val) {
        //if ($val['cat'] != $alb_cat) {  // old method compared names which might not be unique
        if ($val['cid'] != $alb_cat) {
            if ($alb_cat)
				$jDropDwon .= "</optgroup>\n";
            	$jDropDwon .=  '<optgroup label="' . $val['cat'] . '">' . "\n";
            $alb_cat = $val['cid'];
        }
        $jDropDwon .= '<option value="' . $val['aid'] . '"' . ($val['aid'] == $sel_album ? ' selected' : '') . '>   ' . $val['title'] . "</option>\n";
    }
    if ($alb_cat)
		$jDropDwon .= "</optgroup>\n";

    // Close the drop down
   		$jDropDwon .=  <<<EOT
            </select>
        </div>
    </div>

EOT;
	
	return $jDropDwon;
	}
}

/**
 * function get_subcategory_data()
 *
 * Get a hierarchical list of categories
 * 
 * @param integer $parent
 * @param string $ident
 *
 */
function get_subcategory_data($parent, $ident = '')
{
    global $CONFIG, $CAT_LIST;
    if ($CONFIG['categories_alpha_sort'] == 1) {
    $sort_query = 'name';
    } else {
    $sort_query = 'pos';
    }

    $sql = "SELECT cid, name, description " . "FROM {$CONFIG['TABLE_CATEGORIES']} " . "WHERE parent = '$parent' " . "ORDER BY $sort_query";
    $result = cpg_db_query($sql);

    if (($cat_count = mysql_num_rows($result)) > 0) {
        $rowset = cpg_db_fetch_rowset($result);
        $pos = 0;
        foreach ($rowset as $subcat) {
            if ($pos > 0) {
                $CAT_LIST[$subcat['cid']] = array('cid' => $subcat['cid'],
                    'parent' => $parent,
                    'pos' => $pos++,
                    'prev' => $prev_cid,
                    'cat_count' => $cat_count,
                    'name' => $ident . $subcat['name']);
                $CAT_LIST[$last_index]['next'] = $subcat['cid'];
            } else {
                $CAT_LIST[$subcat['cid']] = array('cid' => $subcat['cid'],
                    'parent' => $parent,
                    'pos' => $pos++,
                    'cat_count' => $cat_count,
                    'name' => $ident . $subcat['name']);
            }
            $last_index = $prev_cid = $subcat['cid'];
           // $last_index = count($CAT_LIST) -1;
            get_subcategory_data($subcat['cid'], $ident . '&nbsp;&nbsp;&nbsp;'); // Note: API category list depends on these &nbsp;'s
        }
    }
}


?>
