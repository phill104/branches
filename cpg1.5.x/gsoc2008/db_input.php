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

define('IN_COPPERMINE', true);
define('DB_INPUT_PHP', true);
define('DISPLAYIMAGE_PHP', true);

require('include/init.inc.php');
require('include/picmgmt.inc.php');
require('include/mailer.inc.php');
require('include/smilies.inc.php');

/*known issue: code was edited to not count URL in comment character count. However
this resulted in the character count not being respected at all.

With the new code, the long urls don't affect the display of the hyperlinked word.
However, I can't figure out how to make the code respect the max comment word length and max comment length.
Formatted and unformatted words that are longer than the allowed setting do not get truncated. -Thu */

function check_comment(&$str)
{
    global $CONFIG, $lang_bad_words, $queries;
    // Added according to Andi's proposal: optimization of strip-Tags and max. comment length
    // convert some entities
    $str = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;', '&nbsp;', '&#39;'), array('&', '"', '<', '>', ' ', "'"), $str);
    // strip tags and cut to max allowed length
    $str = trim(substr(strip_tags($str), 0, $CONFIG['max_com_size']));
    // re convert some entities
    $str = str_replace(array('"', '<', '>', "'"), array('&quot;', '&lt;', '&gt;', '&#39;'), $str);

    if ($CONFIG['filter_bad_words']) {
        $ercp = array();
        foreach($lang_bad_words as $word) {
            $ercp[] = '/' . ($word[0] == '*' ? '': '\b') . str_replace('*', '', $word) . ($word[(strlen($word)-1)] == '*' ? '': '\b') . '/i';
        }
        $str = preg_replace($ercp, '(...)', $str);
    }

    $com_words=explode(' ',strip_tags(bb_decode($str)));
    $replacements=array();
    foreach($com_words as $key => $word) {
if (utf_strlen($word) > $CONFIG['max_com_wlength'] ) {
          $replacements[] = $word;
       }
    }
    $str=str_replace($replacements,'(...)',$str);
}

if (!$superCage->get->keyExists('event') && !$superCage->post->keyExists('event')) {
    cpg_die(CRITICAL_ERROR, $lang_errors['param_missing'], __FILE__, __LINE__);
}

if ($matches = $superCage->post->getMatched('event', '/^[a-z_]+$/')) {
	$event = $matches[0];
} elseif ($matches = $superCage->get->getMatched('event', '/^[a-z_]+$/')) {
    $event = $matches[0];
} else {
	$event = '';
}

switch ($event) {

    // Comment update

    case 'comment_update':
        if (!(USER_CAN_POST_COMMENTS)) {
            cpg_die(ERROR, $lang_errors['perm_denied'], __FILE__, __LINE__);
        }

        /**
         * Using getRaw() for the following two statements since the data is sanitized/filtered in the
         * function they are calling.
         */
        
        $msg_body = $superCage->post->getRaw('msg_body');
        $msg_id = $superCage->post->getInt('msg_id');
				check_comment($msg_body);
        if (empty($msg_body)) {
            cpg_die(ERROR, $lang_db_input_php['err_comment_empty'], __FILE__, __LINE__);
        }

        /**
         * Why is this here ???
         */
        if ($CONFIG['comment_approval'] != 0) {
        } else {
        }

        if (GALLERY_ADMIN_MODE) {
            $update = cpg_db_query("UPDATE {$CONFIG['TABLE_COMMENTS']} SET msg_body='$msg_body' WHERE msg_id='$msg_id'");
        } elseif (USER_ID) {
            if ($CONFIG['comment_approval'] == 2) {
                $update = cpg_db_query("UPDATE {$CONFIG['TABLE_COMMENTS']} SET msg_body='$msg_body', approval='NO' WHERE msg_id='$msg_id' AND author_id ='" . USER_ID . "' LIMIT 1");
            } else {
                $update = cpg_db_query("UPDATE {$CONFIG['TABLE_COMMENTS']} SET msg_body='$msg_body' WHERE msg_id='$msg_id' AND author_id ='" . USER_ID . "' LIMIT 1");
            }
        } else {
            if ($CONFIG['comment_approval'] != 0) {
                $update = cpg_db_query("UPDATE {$CONFIG['TABLE_COMMENTS']} SET msg_body='$msg_body', approval='NO' WHERE msg_id='$msg_id' AND author_md5_id ='{$USER['ID']}' AND author_id = '0' LIMIT 1");
            } else {
                $update = cpg_db_query("UPDATE {$CONFIG['TABLE_COMMENTS']} SET msg_body='$msg_body' WHERE msg_id='$msg_id' AND author_md5_id ='{$USER['ID']}' AND author_id = '0' LIMIT 1");
            }
        }

        $header_location = (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE'))) ? 'Refresh: 0; URL=' : 'Location: ';

        $result = cpg_db_query("SELECT pid FROM {$CONFIG['TABLE_COMMENTS']} WHERE msg_id='$msg_id'");
        if (!mysql_num_rows($result)) {
            mysql_free_result($result);
            cpgRedirectPage('index.php', $lang_common['information'], $lang_db_input_php['com_updated'], 1);
        } else {
            $comment_data = mysql_fetch_array($result);
            mysql_free_result($result);
            $redirect = "displayimage.php?pid=" . $comment_data['pid'];
            cpgRedirectPage($redirect, $lang_common['information'], $lang_db_input_php['com_updated'], 1);
        }
        break;

    // Comment

    case 'comment':
        if (!(USER_CAN_POST_COMMENTS)) {
            cpg_die(ERROR, $lang_errors['perm_denied'], __FILE__, __LINE__);
        }
        if (($CONFIG['comment_captcha'] > 0 && !USER_ID) || ($CONFIG['comment_captcha'] == 2 && USER_ID)) {
            require("include/captcha.inc.php");
            $matches = $superCage->post->getMatched('confirmCode', '/^[a-zA-Z0-9]+$/');
            if ($matches[0] && !PhpCaptcha::Validate($matches[0])) {
              //msg_box($lang_common['error'], $lang_errors['captcha_error'], $lang_common['back'], 'javascript:history.back()');
              cpg_die(ERROR, $lang_errors['captcha_error'], __FILE__, __LINE__);
            }
        }

        
        $msg_author = $superCage->post->getEscaped('msg_author');
        $msg_body = $superCage->post->getEscaped('msg_body');
        $pid = $superCage->post->getInt('pid');
				check_comment($msg_body);
        check_comment($msg_author);
        if (empty($msg_author) || empty($msg_body)) {
            cpg_die(ERROR, $lang_db_input_php['empty_name_or_com'], __FILE__, __LINE__);
        }

        $result = cpg_db_query("SELECT comments FROM {$CONFIG['TABLE_PICTURES']}, {$CONFIG['TABLE_ALBUMS']} WHERE {$CONFIG['TABLE_PICTURES']}.aid = {$CONFIG['TABLE_ALBUMS']}.aid AND pid='$pid'");

        if (!mysql_num_rows($result)) {
            cpg_die(ERROR, $lang_errors['non_exist_ap'], __FILE__, __LINE__);
        }

        $album_data = mysql_fetch_array($result);
        mysql_free_result($result);

        if ($album_data['comments'] != 'YES') {
            cpg_die(ERROR, $lang_errors['perm_denied'], __FILE__, __LINE__);
        }

        if (!$CONFIG['disable_comment_flood_protect']) {
          $result = cpg_db_query("SELECT author_md5_id, author_id FROM {$CONFIG['TABLE_COMMENTS']} WHERE pid = '$pid' ORDER BY msg_id DESC LIMIT 1");
          if (mysql_num_rows($result)) {
              $last_com_data = mysql_fetch_array($result);
              if ((USER_ID && $last_com_data['author_id'] == USER_ID) || (!USER_ID && $last_com_data['author_md5_id'] == $USER['ID'])) {
                  cpg_die(ERROR, $lang_db_input_php['no_flood'], __FILE__, __LINE__);
              }
          }
        }

        if (!USER_ID) { // Anonymous users, we need to use META refresh to save the cookie
            if (mysql_result(cpg_db_query("select count(user_id) from {$CONFIG['TABLE_USERS']} where UPPER(user_name) = UPPER('$msg_author')"),0,0))
            {
              cpg_die($lang_common['error'], $lang_db_input_php['com_author_error'],__FILE__,__LINE__);
            }

            // If username for comment is same as default username then display error message
            if ($msg_author == $lang_display_comments['your_name']) {
                cpg_die(ERROR, $lang_display_comments['default_username_message'], __FILE__, __LINE__);
            }

            if ($CONFIG['comment_approval'] != 0) { // comments need approval, set approval status to "no"
                $insert = cpg_db_query("INSERT INTO {$CONFIG['TABLE_COMMENTS']} (pid, msg_author, msg_body, msg_date, author_md5_id, author_id, msg_raw_ip, msg_hdr_ip, approval) VALUES ('$pid', '{$CONFIG['comments_anon_pfx']}$msg_author', '$msg_body', NOW(), '{$USER['ID']}', '0', '$raw_ip', '$hdr_ip', 'NO')");
            } else { //comments do not need approval, we can set approval status to "yes"
                $insert = cpg_db_query("INSERT INTO {$CONFIG['TABLE_COMMENTS']} (pid, msg_author, msg_body, msg_date, author_md5_id, author_id, msg_raw_ip, msg_hdr_ip, approval) VALUES ('$pid', '{$CONFIG['comments_anon_pfx']}$msg_author', '$msg_body', NOW(), '{$USER['ID']}', '0', '$raw_ip', '$hdr_ip', 'YES')");
            }

            $USER['name'] = $msg_author;
            $redirect = "displayimage.php?pid=$pid";
            if ($CONFIG['email_comment_notification']) {
                $mail_body = "<p>" . bb_decode(process_smilies($msg_body, $CONFIG['ecards_more_pic_target'])) . "</p>\n\r ".$lang_db_input_php['email_comment_body'] . " " . $CONFIG['ecards_more_pic_target'].(substr($CONFIG["ecards_more_pic_target"], -1) == '/' ? '' : '/').$redirect;
                cpg_mail('admin', $lang_db_input_php['email_comment_subject'], make_clickable($mail_body));
            }
            pageheader($lang_db_input_php['com_added'], "<meta http-equiv=\"refresh\" content=\"1;url=$redirect\" />");
            msg_box($lang_db_input_php['info'], $lang_db_input_php['com_added'], $lang_common['continue'], $redirect);
            pagefooter();
            ob_end_flush();
            exit;
        } else { // Registered users, we can use Location to redirect
            if ($CONFIG['comment_approval'] == 2 && !USER_IS_ADMIN) { // comments need approval, set approval status to "no"
                $insert = cpg_db_query("INSERT INTO {$CONFIG['TABLE_COMMENTS']} (pid, msg_author, msg_body, msg_date, author_md5_id, author_id, msg_raw_ip, msg_hdr_ip, approval) VALUES ('$pid', '" . addslashes(USER_NAME) . "', '$msg_body', NOW(), '', '" . USER_ID . "', '$raw_ip', '$hdr_ip', 'NO')");
            } else { //comments do not need approval, we can set approval status to "yes"
                $insert = cpg_db_query("INSERT INTO {$CONFIG['TABLE_COMMENTS']} (pid, msg_author, msg_body, msg_date, author_md5_id, author_id, msg_raw_ip, msg_hdr_ip, approval) VALUES ('$pid', '" . addslashes(USER_NAME) . "', '$msg_body', NOW(), '', '" . USER_ID . "', '$raw_ip', '$hdr_ip', 'YES')");
            }
            $redirect = "displayimage.php?pid=$pid";
            if ($CONFIG['email_comment_notification'] && !USER_IS_ADMIN ) {
                $mail_body = "<p>" . bb_decode(process_smilies($msg_body, $CONFIG['ecards_more_pic_target'])) . "</p>\n\r ".$lang_db_input_php['email_comment_body'] . " " . $CONFIG['ecards_more_pic_target'] . (substr($CONFIG["ecards_more_pic_target"], -1) == '/' ? '' : '/') . $redirect;
                cpg_mail('admin', $lang_db_input_php['email_comment_subject'], make_clickable($mail_body));
            }
            cpgRedirectPage($redirect, $lang_db_input_php['info'], $lang_db_input_php['com_added'], 1);
        }
        break;

    // Update album

    case 'album_update':
        if (!(user_is_allowed() || GALLERY_ADMIN_MODE)) {
			cpg_die(ERROR, $lang_errors['perm_denied'], __FILE__, __LINE__);
		}

        $aid = $superCage->post->getInt('aid');
        $title = $superCage->post->getEscaped('title');
        $category = $superCage->post->getInt('category');
        $description = $superCage->post->getEscaped('description');
        $keyword = $superCage->post->getEscaped('keyword');
        $thumb = $superCage->post->getInt('thumb');
        $visibility = $superCage->post->getInt('visibility');

        /**
         * getRaw() is used in the following statements for comparison only. We are not taking anything
         * from _POST
         */
        $uploads = $superCage->post->getRaw('uploads') == 'YES' ? 'YES' : 'NO';
        $comments = $superCage->post->getRaw('comments') == 'YES' ? 'YES' : 'NO';
        $votes = $superCage->post->getRaw('votes') == 'YES' ? 'YES' : 'NO';

        $password = $superCage->post->getEscaped('alb_password');
        $password_hint = $superCage->post->getEscaped('alb_password_hint');
        $visibility = !empty($password) ? FIRST_USER_CAT + USER_ID : $visibility;

        if (!$title) {
            cpg_die(ERROR, $lang_db_input_php['alb_need_title'], __FILE__, __LINE__);
        }

        if (GALLERY_ADMIN_MODE) {
            $moderator_group = $superCage->post->getInt('moderator_group');
            $query = "UPDATE {$CONFIG['TABLE_ALBUMS']} SET title='$title', description='$description', category='$category', thumb='$thumb', uploads='$uploads', comments='$comments', votes='$votes', visibility='$visibility', alb_password='$password', alb_password_hint='$password_hint', keyword='$keyword', moderator_group='$moderator_group' WHERE aid='$aid' LIMIT 1";
        } else {
            $query = "UPDATE {$CONFIG['TABLE_ALBUMS']} SET title='$title', description='$description', category='$category', thumb='$thumb',  comments='$comments', votes='$votes', visibility='$visibility', alb_password='$password', alb_password_hint='$password_hint',keyword='$keyword' WHERE aid='$aid' LIMIT 1";

		}

        $update = cpg_db_query($query);

        if (!mysql_affected_rows()) {
          cpgRedirectPage('modifyalb.php?album='.$aid, $lang_db_input_php['info'], $lang_db_input_php['no_udp_needed'], 0);
          //$target_url = 'modifyalb.php?album='.$aid.'&message_id='.cpgStoreTempMessage($lang_db_input_php['no_udp_needed']);
        } else {
          cpgRedirectPage('modifyalb.php?album='.$aid, $lang_db_input_php['info'], $lang_db_input_php['alb_updated'], 0);
          //$target_url = 'modifyalb.php?album='.$aid.'&message_id='.cpgStoreTempMessage($lang_db_input_php['alb_updated']);
        }
        //header('Location: '.$target_url); /* Redirect browser */
        //exit;
        break;

    // Reset album

    case 'album_reset':
        if (!GALLERY_ADMIN_MODE) {
            cpg_die(ERROR, $lang_errors['perm_denied'], __FILE__, __LINE__);
        }

        $counter_affected_rows = 0;
        $aid = $superCage->post->getInt('aid');
        $reset_views = $superCage->post->getInt('reset_views');
        $reset_rating = $superCage->post->getInt('reset_rating');
        $delete_comments = $superCage->post->getInt('delete_comments');
        $delete_files = $superCage->post->getInt('delete_files');

        if ($reset_views || $reset_rating) {
            // Get all pids for this album to reset their detail hits/vote stats
            $query = "SELECT pid FROM {$CONFIG['TABLE_PICTURES']} WHERE aid = '$aid'";
            $result = cpg_db_query($query);
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
              $pid[] = $row['pid'];
            }
        }

        if ($reset_views) { // if reset_views start
            $query = "UPDATE {$CONFIG['TABLE_PICTURES']} SET hits='0' WHERE aid='$aid'";
                $update = cpg_db_query($query);
            if (isset($CONFIG['debug_mode']) && ($CONFIG['debug_mode'] == 1)) {
                $queries[] = $query;
            }
            if (mysql_affected_rows()) {
                $counter_affected_rows++;
            }
            resetDetailHits($pid);
        } // if reset_views end

        if ($reset_rating) { // if reset_rating start
            $query = "UPDATE {$CONFIG['TABLE_PICTURES']} SET  pic_rating='0',  votes='0' WHERE aid='$aid'";
                $update = cpg_db_query($query);
            if (isset($CONFIG['debug_mode']) && ($CONFIG['debug_mode'] == 1)) {
                $queries[] = $query;
            }
            if (mysql_affected_rows()) {
                $counter_affected_rows++;
            }
            resetDetailVotes($pid);
        } // if reset_rating end

        if ($delete_files) { // if delete_files start
            $query = "DELETE FROM {$CONFIG['TABLE_PICTURES']} WHERE aid='$aid'";
                $update = cpg_db_query($query);
            if (isset($CONFIG['debug_mode']) && ($CONFIG['debug_mode'] == 1)) {
                $queries[] = $query;
            }
            if (mysql_affected_rows()) {
                $counter_affected_rows++;
            }
        } // if delete_files end


        if ($counter_affected_rows == 0) {
            cpg_die(INFORMATION, $lang_db_input_php['no_udp_needed'], __FILE__, __LINE__);
        }

        pageheader($lang_db_input_php['alb_updated'], "<meta http-equiv=\"refresh\" content=\"1;url=modifyalb.php?album=$aid\" />");
        msg_box($lang_db_input_php['info'], $lang_db_input_php['alb_updated'], $lang_common['continue'], "modifyalb.php?album=$aid");
        pagefooter();
        ob_end_flush();
        exit;
        break;

    // Picture upload


    case 'picture':
        if (!USER_CAN_UPLOAD_PICTURES) {
            cpg_die(ERROR, $lang_errors['perm_denied'], __FILE__, __LINE__);
        }

        $album = $superCage->post->getInt('album');
        $title = $superCage->post->getEscaped('title');
        $caption = $superCage->post->getEscaped('caption');
        $keywords = $superCage->post->getEscaped('keywords');
        $user1 = $superCage->post->getEscaped('user1');
        $user2 = $superCage->post->getEscaped('user2');
        $user3 = $superCage->post->getEscaped('user3');
        $user4 = $superCage->post->getEscaped('user4');

        // Check if the album id provided is valid
        if (!(GALLERY_ADMIN_MODE || user_is_allowed())) {
            $result = cpg_db_query("SELECT category FROM {$CONFIG['TABLE_ALBUMS']} WHERE aid='$album' and (uploads = 'YES' OR category = '" . (USER_ID + FIRST_USER_CAT) . "')");
            if (mysql_num_rows($result) == 0) {
                cpg_die(ERROR, $lang_db_input_php['unknown_album'], __FILE__, __LINE__);
            }
            $row = mysql_fetch_array($result);
            mysql_free_result($result);
            $category = $row['category'];
        } else {
            $result = cpg_db_query("SELECT category FROM {$CONFIG['TABLE_ALBUMS']} WHERE aid='$album'");
            if (mysql_num_rows($result) == 0) {
                cpg_die(ERROR, $lang_db_input_php['unknown_album'], __FILE__, __LINE__);
            }
            $row = mysql_fetch_array($result);
            mysql_free_result($result);
            $category = $row['category'];
        }

        // Test if the filename of the temporary uploaded picture is empty
        if ($superCage->files->getRaw("/userpicture/tmp_name") == '') {
            cpg_die(ERROR, $lang_db_input_php['no_pic_uploaded'], __FILE__, __LINE__);
        }

        // Pictures are moved in a directory named 10000 + USER_ID
        if (USER_ID && $CONFIG['silly_safe_mode'] != 1) {
            $filepath = $CONFIG['userpics'] . (USER_ID + FIRST_USER_CAT);
            $dest_dir = $CONFIG['fullpath'] . $filepath;
            if (!is_dir($dest_dir)) {
                mkdir($dest_dir, octdec($CONFIG['default_dir_mode']));
                if (!is_dir($dest_dir)) {
                    cpg_die(CRITICAL_ERROR, sprintf($lang_db_input_php['err_mkdir'], $dest_dir), __FILE__, __LINE__, true);
                }
                chmod($dest_dir, octdec($CONFIG['default_dir_mode']));
                $fp = fopen($dest_dir . '/index.html', 'w');
                fwrite($fp, ' ');
                fclose($fp);
            }
            $dest_dir .= '/';
            $filepath .= '/';
        } else {
            $filepath = $CONFIG['userpics'];
            $dest_dir = $CONFIG['fullpath'] . $filepath;
        }

        // Check that target dir is writable
        if (!is_writable($dest_dir)) {
            cpg_die(CRITICAL_ERROR, sprintf($lang_db_input_php['dest_dir_ro'], $dest_dir), __FILE__, __LINE__, true);
        }

        if (get_magic_quotes_gpc()) {
            //Using getRaw() as we have custom sanitization code below
            $picture_name = stripslashes($superCage->files->getRaw("/userpicture/name"));
        } else {
            $picture_name = $superCage->files->getRaw("/userpicture/name");
        }

        // Replace forbidden chars with underscores
        $picture_name = replace_forbidden($picture_name);

        // Check that the file uploaded has a valid extension
        $matches = array();
        if (!preg_match("/(.+)\.(.*?)\Z/", $picture_name, $matches)) {
            $matches[1] = 'invalid_fname';
            $matches[2] = 'xxx';
        }

        if ($matches[2] == '' || !is_known_filetype($matches)) {
            cpg_die(ERROR, sprintf($lang_db_input_php['err_invalid_fext'], $CONFIG['allowed_file_extensions']), __FILE__, __LINE__);
        }

        // Create a unique name for the uploaded file
        $nr = 0;
        $picture_name = $matches[1] . '.' . $matches[2];
        while (file_exists($dest_dir . $picture_name)) {
            $picture_name = $matches[1] . '~' . $nr++ . '.' . $matches[2];
        }
        $uploaded_pic = $dest_dir . $picture_name;

        // Move the picture into its final location
        if (!move_uploaded_file($superCage->files->getRaw("/userpicture/tmp_name"), $uploaded_pic)) {
            cpg_die(CRITICAL_ERROR, sprintf($lang_db_input_php['err_move'], $picture_name, $dest_dir), __FILE__, __LINE__, true);
        }

        // Change file permission
        chmod($uploaded_pic, octdec($CONFIG['default_file_mode']));

        // Get picture information
        // Check that picture file size is lower than the maximum allowed
        if (filesize($uploaded_pic) > ($CONFIG['max_upl_size'] << 10)) {
            @unlink($uploaded_pic);
            cpg_die(ERROR, sprintf($lang_db_input_php['err_imgsize_too_large'], $CONFIG['max_upl_size']), __FILE__, __LINE__);
        } elseif (is_image($picture_name)) {
            $imginfo = cpg_getimagesize($uploaded_pic);
            // getimagesize does not recognize the file as a picture
            if ($imginfo == null) {
                @unlink($uploaded_pic);
                cpg_die(ERROR, $lang_db_input_php['err_invalid_img'], __FILE__, __LINE__, true);
            // JPEG and PNG only are allowed with GD
            } elseif ($imginfo[2] != GIS_JPG && $imginfo[2] != GIS_PNG && $CONFIG['GIF_support'] == 0) {
                @unlink($uploaded_pic);
                cpg_die(ERROR, $lang_errors['gd_file_type_err'], __FILE__, __LINE__, true);
            // Check that picture size (in pixels) is lower than the maximum allowed
            } elseif (max($imginfo[0], $imginfo[1]) > $CONFIG['max_upl_width_height']) {
              if ((USER_IS_ADMIN && $CONFIG['auto_resize'] == 1) || (!USER_IS_ADMIN && $CONFIG['auto_resize'] > 0)) {
                //resize_image($uploaded_pic, $uploaded_pic, $CONFIG['max_upl_width_height'], $CONFIG['thumb_method'], $imginfo[0] > $CONFIG['max_upl_width_height'] ? 'wd' : 'ht');
                resize_image($uploaded_pic, $uploaded_pic, $CONFIG['max_upl_width_height'], $CONFIG['thumb_method'], $CONFIG['thumb_use']);
              }
              else {
                @unlink($uploaded_pic);
                cpg_die(ERROR, sprintf($lang_db_input_php['err_fsize_too_large'], $CONFIG['max_upl_width_height'], $CONFIG['max_upl_width_height']), __FILE__, __LINE__);
              }
            } // Image is ok
        }

        // Upload is ok
        // Create thumbnail and internediate image and add the image into the DB
        $result = add_picture($album, $filepath, $picture_name, 0, $title, $caption, $keywords, $user1, $user2, $user3, $user4, $category, $raw_ip, $hdr_ip, $superCage->post->getInt('width'), $superCage->post->getInt('height'));

        if (!$result) {
            @unlink($uploaded_pic);
            cpg_die(CRITICAL_ERROR, sprintf($lang_db_input_php['err_insert_pic'], $uploaded_pic) . '<br /><br />' . $ERROR, __FILE__, __LINE__, true);
        } elseif ($PIC_NEED_APPROVAL) {
            pageheader($lang_common['information']);
            msg_box($lang_common['information'], $lang_db_input_php['upload_success'], $lang_common['continue'], 'index.php');
            // start: send admin approval mail
            if ($CONFIG['upl_notify_admin_email'])
            {
                include_once('include/mailer.inc.php');
                cpg_mail('admin', sprintf($lang_db_input_php['notify_admin_email_subject'], $CONFIG['gallery_name']), sprintf($lang_db_input_php['notify_admin_email_body'], USER_NAME,  $CONFIG['ecards_more_pic_target'].(substr($CONFIG["ecards_more_pic_target"], -1) == '/' ? '' : '/') .'editpics.php?mode=upload_approval' ));
            }
            // end: send admin approval mail
            ob_end_flush();
        } else {
            //$header_location = (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE'))) ? 'Refresh: 0; URL=' : 'Location: ';

            // start daorange
            if (defined('API_CALL')) {
                api_message('Picture "' . $title . '" uploaded successfully via the Coppermine API');
            } // end daorange

            $redirect = "displayimage.php?pid=" . mysql_insert_id($CONFIG['LINK_ID']);
            cpgRedirectPage($redirect, $lang_common['information'], $lang_db_input_php['upl_success'], 1);
        }
        break;

    // Unknown event

    default:
        cpg_die(CRITICAL_ERROR, $lang_errors['param_missing'], __FILE__, __LINE__);
}
?>
