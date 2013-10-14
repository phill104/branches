<?php
/**************************************************
  Coppermine 1.5.x Plugin - template
**************************************************/

// create Inspekt supercage
$superCage = Inspekt::makeSuperCage();

// Make sure that this file can't be accessed directly, but only from within the Coppermine user interface
if (!defined('IN_COPPERMINE')) {
    die('Not in Coppermine...');
}

if (!GALLERY_ADMIN_MODE) {
    cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
}

// get sanitized POST parameters
if ($superCage->post->keyExists('submit')) {
    //Check if the form token is valid
    if(!checkFormToken()){
        cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
    }
  // Define the sanitization patterns
  $sanitization_array = array(
      'plugin_template_adminmode' => array('type' => 'checkbox', 'min' => '0', 'max' => '1'),
  );
  $config_changes_counter = 0;
  foreach ($sanitization_array as $san_key => $san_value) {
      if (isset($CONFIG[$san_key]) == TRUE) { // only loop if config value is set --- start
          if ($san_value['type'] == 'checkbox') { // type is checkbox --- start
            if ($superCage->post->getInt($san_key) <= $san_value['max'] && $superCage->post->getInt($san_key) >= $san_value['min'] && $superCage->post->getInt($san_key) != $CONFIG[$san_key]) {
                $CONFIG[$san_key] = $superCage->post->getInt($san_key);
                cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG[$san_key]}' WHERE name='$san_key'");
                $config_changes_counter++;
            } elseif($superCage->post->keyExists($san_key) != TRUE && $CONFIG[$san_key] != '0') {
                $CONFIG[$san_key] = 0;
                cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG[$san_key]}' WHERE name='$san_key'");
                $config_changes_counter++;
            }
          } // type is checkbox --- end
          if ($san_value['type'] == 'int') { // type is integer --- start
              if ($superCage->post->getInt($san_key) <= $san_value['max'] && $superCage->post->getInt($san_key) >= $san_value['min'] && $superCage->post->getInt($san_key) != $CONFIG[$san_key]) {
                  $CONFIG[$san_key] = $superCage->post->getInt($san_key);
                  cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG[$san_key]}' WHERE name='$san_key'");
                  $config_changes_counter++;
              }
          } // type is integer --- end
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
          if ($san_value['type'] == 'array') { // type is array --- start              
          $evaluate_value = $superCage->post->getRaw($san_key);
              if (is_array($evaluate_value) && isset($san_value['regex_ok']) == TRUE && isset($san_value['delimiter']) == TRUE) {
                  $temp = '';
                  for ($i = 0; $i <= count($evaluate_value); $i++) {
                      if (preg_match($san_value['regex_ok'], $evaluate_value[$i])) {
                          $temp .= $evaluate_value[$i] . $san_value['delimiter'];
                      }
                  }
                  unset($evaluate_value);
                  $evaluate_value = rtrim($temp, $san_value['delimiter']);
                  unset($temp);
              }
              if ($evaluate_value != $CONFIG[$san_key]) {
                  $CONFIG[$san_key] = $evaluate_value;
                  cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG[$san_key]}' WHERE name='$san_key'");
                  $config_changes_counter++;
              }
          } // type is array --- end
      } // only loop if config value is set --- end
  }
}


// display config page

// Set the option output stuff 
if ($CONFIG['plugin_template_adminmode'] == '1') {
    $option_output['plugin_template_adminmode'] = 'checked="checked"';
} else { 
    $option_output['plugin_template_adminmode'] = '';
}

pageheader(sprintf($lang_plugin_template['configure_x'], $lang_plugin_template['plugin_name']));
list($timestamp, $form_token) = getFormToken();
echo <<< EOT
<form action="index.php?file=template/admin" method="post" name="template_settings">
EOT;
starttable('100%', sprintf($lang_plugin_template['configure_x'], $lang_plugin_template['plugin_name']), 3, 'cpg_zebra');

if ($superCage->post->keyExists('submit')) {
    if ($config_changes_counter > 0) {
        echo <<< EOT
    <tr>
        <td class="tablef" colspan="2" >
EOT;
        msg_box('', $lang_plugin_template['update_success'], '', '', 'success');
    } else {
        msg_box('', $lang_plugin_template['no_changes'], '', '', 'validation');
    }
        echo <<< EOT
        </td>
    </tr>
EOT;
}
echo <<< EOT
    <!-- insert config option form code start -->
    <!-- insert config option form code end -->
    <tr>
        <td class="tablef" colspan="3">
            <input type="hidden" name="form_token" value="{$form_token}" />
            <input type="hidden" name="timestamp" value="{$timestamp}" />
            <button type="submit" class="button" name="submit" value="{$lang_plugin_template['submit']}">{$lang_plugin_template['submit']}</button>
        </td>
    </tr>
EOT;

endtable();
echo <<< EOT
</form>
EOT;
pagefooter();



?>