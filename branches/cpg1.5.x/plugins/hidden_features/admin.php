<?php
/**************************************************
  Coppermine 1.5.x plugin - hidden_features
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

if (!GALLERY_ADMIN_MODE) {
    cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
}

/*
$CONFIG['link_last_upload']                 2014-01-21 [A] Added hidden feature to regard upload time of linked files in album info (thread ID 77021) {eenemeenemuu}
$CONFIG['editpics_ignore_newer_than']       2012-11-28 [A] Added hidden feature to display all files after flash upload (thread ID 75588) {eenemeenemuu}
$CONFIG['custom_sortorder_thumbs']          2012-07-06 [A] Added hidden feature to toggle the display of the sort buttons on the thumbnail page {eenemeenemuu}
$CONFIG['album_sort_order']                 2012-06-29 [A] Added hidden feature to set sort order of albums (thread ID 75112) {eenemeenemuu}
$CONFIG['upload_create_album_directory']    2011-11-17 [A] Added hidden feature "Create sub-directory named according to the album ID in users' upload directories during HTTP upload" {eenemeenemuu}
$CONFIG['allow_guests_enter_file_details']  2010-07-13 [A] Disable the possibility for guests to enter file details by default (thread ID 62522) {eenemeenemuu}
*/

// List of all possible hidden config option - will be checked later if available at used gallery version
$hidden_features_config_options = array(
    'allow_guests_enter_file_details' => array(
        'type' => 'checkbox',
    ),
    'upload_create_album_directory' => array(
        'type' => 'checkbox',
    ),
    'album_sort_order' => array(
        'type' => 'select',
        'options' => array('ta', 'td', 'da', 'dd', 'pa', 'pd'),
    ),
    'custom_sortorder_thumbs' => array(
        'type' => 'checkbox',
    ),
    'editpics_ignore_newer_than' => array(
        'type' => 'checkbox',
    ),
    'link_last_upload' => array(
        'type' => 'checkbox',
    ),
);

// Remove non-available options for currently used gallery version
foreach ($hidden_features_config_options as $option => $data) {
    if (!array_key_exists($option, $CONFIG)) {
        unset($hidden_features_config_options[$option]);
    }
}

pageheader($lang_plugin_hidden_features['hidden_features'].' - '.$lang_gallery_admin_menu['admin_lnk']);
$superCage = Inspekt::makeSuperCage();

if ($superCage->post->keyExists('submit')) {
    if (!checkFormToken()) {
        cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
    }

    foreach ($hidden_features_config_options as $option => $data) {
        if ($data['type'] == 'checkbox') {
            $value = $superCage->post->keyExists($option) ? '1' : '0';
        } elseif ($data['type'] == 'select') {
            $value = false;
            if ($superCage->post->keyExists($option)) {
                foreach ($data['options'] as $select_option) {
                    if ($superCage->post->getAlNum($option) == $select_option) {
                        $value = $superCage->post->getAlNum($option);
                        break;
                    }
                }
            }
            if ($value === false) {
                continue;
            }
        } else {
            continue;
        }
        $CONFIG[$option] = $value;
        cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$value' WHERE name = '$option'");
    }

    starttable("100%", $lang_common['information']);
    echo "
        <tr>
            <td class=\"tableb\" width=\"200\">
                {$lang_plugin_hidden_features['saved']}
            </td>
        </tr>
    ";
    endtable();
    echo "<br />";
}

echo '<form action="index.php?file=hidden_features/admin" method="post">';
starttable("100%", $lang_plugin_hidden_features['hidden_features'].' - '.$lang_gallery_admin_menu['admin_lnk'], 3);
foreach ($hidden_features_config_options as $option => $data) {
    if ($data['type'] == 'checkbox') {
        $checked = $CONFIG[$option] ? ' checked="checked"' : '';
        $input = '<input class="checkbox" type="checkbox" name="'.$option.'"'.$checked.' />';
    } elseif ($data['type'] == 'select') {
        $input = '<select class="listbox" name="'.$option.'">';
        foreach ($data['options'] as $select_option) {
            $selected = $CONFIG[$option] == $select_option ? ' selected="selected"': '';
            $input .= '<option value="'.$select_option.'"'.$selected.'>'.$lang_thumb_view['sort_'.$select_option].'</option>';
        }
        $input .= '</select>';
    } else {
        continue;
    }
    echo <<<EOT
        <tr>
            <td class="tableb">
                {$lang_plugin_hidden_features[$option]}
            </td>
            <td class="tableb">
                $input
            </td>
        </tr>
EOT;
}
endtable();

list($timestamp, $form_token) = getFormToken();
echo "<input type=\"hidden\" name=\"form_token\" value=\"{$form_token}\" />";
echo "<input type=\"hidden\" name=\"timestamp\" value=\"{$timestamp}\" />";
echo "<input type=\"submit\" value=\"{$lang_common['apply_changes']}\" name=\"submit\" class=\"button\" /> ";
echo "<input type=\"reset\" value=\"reset\" name=\"reset\" class=\"button\" /> </form>";
pagefooter();

?>