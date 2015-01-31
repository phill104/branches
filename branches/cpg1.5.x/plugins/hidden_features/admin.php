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

// List of all possible hidden config option
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
    'batch_add_hide_existing_files' => array(
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

if ($superCage->post->keyExists('update_config')) {
    if (!checkFormToken()) {
        cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
    }

    $updated = array();
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
        if ($value != $CONFIG[$option]) {
            cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$value' WHERE name = '$option'");
            $CONFIG[$option] = $value;
            $updated[] = $option;
        }
    }

    if (count($updated)) {
        $status = '<ul>';
        foreach ($updated as $option) {
            $status .= '<li style="list-style-image:url(images/icons/ok.png)">'.sprintf($lang_admin_php['config_setting_ok'], $lang_plugin_hidden_features[$option]).'</li>';
        }
        $status .= '</ul>';
    } else {
        $status = $lang_admin_php['upd_not_needed'];
    }
    starttable("100%", cpg_fetch_icon('info', 1).$lang_common['information']);
    echo <<< EOT
        <tr>
            <td class="tableb">
                {$status}
            </td>
        </tr>
EOT;
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
    $tableb_alternate = (++$i % 2 == 0) ? ' tableb_alternate' : '';
    echo <<<EOT
        <tr>
            <td class="tableb{$tableb_alternate}">
                {$lang_plugin_hidden_features[$option]}
            </td>
            <td class="tableb{$tableb_alternate}">
                {$input}
            </td>
        </tr>
EOT;
}

list($timestamp, $form_token) = getFormToken();
echo <<< EOT
    <tr>
        <td class="tableb"></td>
        <td class="tableb">
            <input type="hidden" name="form_token" value="{$form_token}" />
            <input type="hidden" name="timestamp" value="{$timestamp}" />
            <button type="submit" class="button" name="update_config" value="{$lang_admin_php['save_cfg']}"><img src="images/icons/ok.png" border="0" alt="" width="16" height="16" class="icon" />{$lang_admin_php['save_cfg']}</button>
        </td>
    </tr>
EOT;

endtable();
echo '</form><br />';
pagefooter();

?>