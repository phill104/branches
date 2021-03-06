<?php
/**************************************************
  Coppermine 1.5.x Plugin - social_bookmarks
  *************************************************
  Copyright (c) 2003-2009 Coppermine Dev Team
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

if (!defined('IN_COPPERMINE')) {
    die('Not in Coppermine...');
}

// Set up the plugin actions and filters
//$thisplugin->add_action('page_start','social_bookmarks_initialize');
$thisplugin->add_filter('theme_pageheader_params','social_bookmarks_title_get');
$thisplugin->add_action('plugin_install','social_bookmarks_install');
$thisplugin->add_action('plugin_uninstall','social_bookmarks_uninstall');
$thisplugin->add_action('plugin_cleanup','social_bookmarks_cleanup');
$thisplugin->add_action('plugin_configure','social_bookmarks_configure');
$thisplugin->add_filter('admin_menu','social_bookmarks_admin_menu_button');
if ($CONFIG['plugin_social_bookmarks_position'] == 2) {
    $thisplugin->add_filter('sys_menu','social_bookmarks_menu_button');
} elseif ($CONFIG['plugin_social_bookmarks_position'] == 3) {
    $thisplugin->add_filter('sub_menu','social_bookmarks_menu_button');
}
$thisplugin->add_filter('page_meta','social_bookmarks_page_meta');
$thisplugin->add_filter('plugin_block','social_bookmarks_mainpage');
$thisplugin->add_filter('template_html','social_bookmarks_template');


function social_bookmarks_install() {
    global $CONFIG, $social_bookmarks_installation, $thisplugin, $USER_DATA, $lang_plugin_social_bookmarks;
	// Create the super cage
	$superCage = Inspekt::makeSuperCage();
	$social_bookmarks_installation = 1;
	require 'include/sql_parse.php';
	
    // Perform the database changes
    $db_schema = $thisplugin->fullpath . '/schema.sql';
    $sql_query = fread(fopen($db_schema, 'r'), filesize($db_schema));
    $sql_query = preg_replace('/CPG_/', $CONFIG['TABLE_PREFIX'], $sql_query);
    $sql_query = remove_remarks($sql_query);
    $sql_query = split_sql_file($sql_query, ';');
    foreach($sql_query as $q) {
    	cpg_db_query($q);
    }
	// Set the plugin config defaults
	$plugin_config_defaults = array(
	                                'plugin_social_bookmarks_position' => '2',
	                                'plugin_social_bookmarks_visibility' => '2',
	                                'plugin_social_bookmarks_greyout' => '0',
	                                'plugin_social_bookmarks_layout' => '2',
	                                'plugin_social_bookmarks_columns' => '5',
	                                'plugin_social_bookmarks_smart_language' => '1',
	                                'plugin_social_bookmarks_admin_menu' => '0',
	                                );
	foreach ($plugin_config_defaults as $key => $value) {
	    if (!$CONFIG[$key]) {
	        $CONFIG[$key] = $value;
	    }
	}
	
	if ($superCage->post->keyExists('submit')) {
		social_bookmarks_configuration_submit();
		return true;
	} else {
		return 1;
	}
}

function social_bookmarks_uninstall() {
	global $CONFIG;
	$superCage = Inspekt::makeSuperCage();
	if (!$superCage->post->keyExists('submit')) {
		return 1;
	}
	// Drop the database records
	if ($superCage->post->keyExists('drop_config') && $superCage->post->getInt('drop_config') == 1) {
		cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_social_bookmarks_position'");
		cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_social_bookmarks_visibility'");
		cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_social_bookmarks_greyout'");
		cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_social_bookmarks_layout'");
		cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_social_bookmarks_columns'");
		cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_social_bookmarks_smart_language'");
		cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_social_bookmarks_admin_menu'");
	}
	if ($superCage->post->keyExists('drop_services') && $superCage->post->getInt('drop_services') == 1) {
		cpg_db_query("DROP TABLE IF EXISTS {$CONFIG['TABLE_PREFIX']}plugin_social_bookmarks_services");
	}
	return true;
}

// Ask if we want to drop the table
function social_bookmarks_cleanup($action) {
    global $CONFIG, $LINEBREAK, $lang_plugin_social_bookmarks, $lang_common, $social_bookmarks_icon_array;
	// Initialize language and icons
	require_once './plugins/social_bookmarks/init.inc.php';
	$social_bookmarks_init_array = social_bookmarks_initialize();
	$lang_plugin_social_bookmarks = $social_bookmarks_init_array['language']; 
	$social_bookmarks_icon_array = $social_bookmarks_init_array['icon'];
    $superCage = Inspekt::makeSuperCage();
    $cleanup = $superCage->server->getEscaped('REQUEST_URI');
    if ($action===1) {
        echo <<< EOT
    <form action="{$cleanup}" method="post">
EOT;
    starttable('100%', '', 2);

        echo <<< EOT
            <tr>
                <td class="tableb">
                    {$lang_plugin_social_bookmarks['cleanup']}
                </td>
                <td class="tableb">
                    <input type="checkbox" class="checkbox" name="drop_services" id="drop_services" value="1" />
                    <label for="drop_services" class="clickable_option">{$lang_plugin_social_bookmarks['remove_services']}<br />({$lang_plugin_social_bookmarks['remove_services_explanation']})</label>
                    <input type="hidden" name="drop_config" value="1" />
                </td>
            </tr>
EOT;
        echo <<< EOT
            <tr>
                <td class="tablef">
					<button type="submit" class="button" name="submit" value="{$lang_common['go']}">{$social_bookmarks_icon_array['ok']}{$lang_common['go']}</button>
				</td>
				<td class="tablef">
				    {$lang_plugin_social_bookmarks['drop_table_warning']}<br />
                </td>
            </tr>
			<tr>
				<td class="tablef" colspan="2">
					<div class="cpg_message_info">
						{$lang_plugin_social_bookmarks['submit_to_uninstall']}
					</div>
				</td>
			</tr>
EOT;
	    endtable();
        echo <<< EOT
    </form>
EOT;
    }
}

// Configure function: displays the configuration form
function social_bookmarks_configure() {
    global $CONFIG, $THEME_DIR, $thisplugin, $lang_plugin_social_bookmarks, $lang_common, $social_bookmarks_icon_array, $lang_errors, $social_bookmarks_installation, $socialBookmarks_title;
    $superCage = Inspekt::makeSuperCage();
    if (!GALLERY_ADMIN_MODE) {
    	cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
    }
    
    // Form submit?
    if ($superCage->post->keyExists('submit') == TRUE) {
        //Check if the form token is valid
        if(!checkFormToken()){
            cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
        }
		$config_changes_counter = social_bookmarks_configuration_submit();
    	if ($config_changes_counter > 0) {
    		$additional_submit_information = '<div class="cpg_message_success">' . $lang_plugin_social_bookmarks['changes_saved'] . '</div>';
    	} else {
    		$additional_submit_information = '<div class="cpg_message_validation">' . $lang_plugin_social_bookmarks['no_changes'] . '</div>';
    	}
    }
	
    // Set the option output stuff 
	if ($CONFIG['plugin_social_bookmarks_position'] == '0') {
    	$option_output['plugin_social_bookmarks_position_placeholder_token']    = 'checked="checked"';
    	$option_output['plugin_social_bookmarks_position_content_of_main_page'] = '';
    	$option_output['plugin_social_bookmarks_position_sys_menu']             = '';
    	$option_output['plugin_social_bookmarks_position_sub_menu']             = '';
    } elseif ($CONFIG['plugin_social_bookmarks_position'] == '1') { // 
    	$option_output['plugin_social_bookmarks_position_placeholder_token']    = '';
    	$option_output['plugin_social_bookmarks_position_content_of_main_page'] = 'checked="checked"';
    	$option_output['plugin_social_bookmarks_position_sys_menu']             = '';
    	$option_output['plugin_social_bookmarks_position_sub_menu']             = '';
    } elseif ($CONFIG['plugin_social_bookmarks_position'] == '2') { // 
    	$option_output['plugin_social_bookmarks_position_placeholder_token']    = '';
    	$option_output['plugin_social_bookmarks_position_content_of_main_page'] = '';
    	$option_output['plugin_social_bookmarks_position_sys_menu']             = 'checked="checked"';
    	$option_output['plugin_social_bookmarks_position_sub_menu']             = '';
    } elseif ($CONFIG['plugin_social_bookmarks_position'] == '3') { // 
    	$option_output['plugin_social_bookmarks_position_placeholder_token']    = '';
    	$option_output['plugin_social_bookmarks_position_content_of_main_page'] = '';
    	$option_output['plugin_social_bookmarks_position_sys_menu']             = '';
    	$option_output['plugin_social_bookmarks_position_sub_menu']             = 'checked="checked"';
    }
    
	if ($CONFIG['plugin_social_bookmarks_visibility'] == '0') {
    	$option_output['plugin_social_bookmarks_visibility_always_visible']      = 'checked="checked"';
    	$option_output['plugin_social_bookmarks_visibility_expand_on_click']     = '';
    	$option_output['plugin_social_bookmarks_visibility_expand_on_mouseover'] = '';
    	$option_output['plugin_social_bookmarks_visibility_display_popup']       = '';
    } elseif ($CONFIG['plugin_social_bookmarks_visibility'] == '1') { // 
    	$option_output['plugin_social_bookmarks_visibility_always_visible']      = '';
    	$option_output['plugin_social_bookmarks_visibility_expand_on_click']     = 'checked="checked"';
    	$option_output['plugin_social_bookmarks_visibility_expand_on_mouseover'] = '';
    	$option_output['plugin_social_bookmarks_visibility_display_popup']       = '';
    } elseif ($CONFIG['plugin_social_bookmarks_visibility'] == '2') { // 
    	$option_output['plugin_social_bookmarks_visibility_always_visible']      = '';
    	$option_output['plugin_social_bookmarks_visibility_expand_on_click']     = '';
    	$option_output['plugin_social_bookmarks_visibility_expand_on_mouseover'] = 'checked="checked"';
    	$option_output['plugin_social_bookmarks_visibility_display_popup']       = '';
    } elseif ($CONFIG['plugin_social_bookmarks_visibility'] == '3') { // 
    	$option_output['plugin_social_bookmarks_visibility_always_visible']      = '';
    	$option_output['plugin_social_bookmarks_visibility_expand_on_click']     = '';
    	$option_output['plugin_social_bookmarks_visibility_expand_on_mouseover'] = '';
    	$option_output['plugin_social_bookmarks_visibility_display_popup']       = 'checked="checked"';
    }
    
    if ($CONFIG['plugin_social_bookmarks_greyout'] == '0') {
        $option_output['plugin_social_bookmarks_greyout']   = '';
    }  else {
        $option_output['plugin_social_bookmarks_greyout']   = 'checked="checked"';
    }
    
	if ($CONFIG['plugin_social_bookmarks_layout'] == '0') {
    	$option_output['plugin_social_bookmarks_layout_simple_list']   = 'checked="checked"';
    	$option_output['plugin_social_bookmarks_layout_advanced_list'] = '';
    	$option_output['plugin_social_bookmarks_layout_icons_only']    = '';
    } elseif ($CONFIG['plugin_social_bookmarks_layout'] == '1') { // 
    	$option_output['plugin_social_bookmarks_layout_simple_list']   = '';
    	$option_output['plugin_social_bookmarks_layout_advanced_list'] = 'checked="checked"';
    	$option_output['plugin_social_bookmarks_layout_icons_only']    = '';
    } elseif ($CONFIG['plugin_social_bookmarks_layout'] == '2') { // 
    	$option_output['plugin_social_bookmarks_layout_simple_list']   = '';
    	$option_output['plugin_social_bookmarks_layout_advanced_list'] = '';
    	$option_output['plugin_social_bookmarks_layout_icons_only']    = 'checked="checked"';
    }
    
    if ($CONFIG['plugin_social_bookmarks_smart_language'] == '0') {
        $option_output['plugin_social_bookmarks_smart_language']   = '';
    }  else {
        $option_output['plugin_social_bookmarks_smart_language']   = 'checked="checked"';
    }
    
    if ($CONFIG['plugin_social_bookmarks_admin_menu'] == '0') {
        $option_output['plugin_social_bookmarks_admin_menu']   = '';
    }  else {
        $option_output['plugin_social_bookmarks_admin_menu']   = 'checked="checked"';
    }


	// Create the table row that is displayed during initial install
	if ($social_bookmarks_installation == 1) {
		$additional_submit_information = '<div class="cpg_message_info">' . $lang_plugin_social_bookmarks['submit_to_install'] . '</div>';
	}
	
	list($timestamp, $form_token) = getFormToken();
	
	// Start the actual output
    echo <<< EOT
            <form action="" method="post" name="social_bookmarks_config" id="social_bookmarks_config">
EOT;

    starttable('100%', $social_bookmarks_icon_array['configure'] . $lang_plugin_social_bookmarks['config'], 3);
    echo <<< EOT
                    <tr>
                        <td valign="top" class="tableh2" colspan="3">
                            {$lang_plugin_social_bookmarks['site_integration']}
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableb" rowspan="4">
                            {$lang_plugin_social_bookmarks['position_of_button']}
                        </td>
                        <td valign="top" class="tableb">
                            <input type="radio" name="plugin_social_bookmarks_position" id="plugin_social_bookmarks_position_placeholder_token" class="radio" value="0" {$option_output['plugin_social_bookmarks_position_placeholder_token']} /> 
                        </td>
                        <td valign="top" class="tableb">
                        	<label for="plugin_social_bookmarks_position_placeholder_token" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['placeholder_token']} ({$lang_plugin_social_bookmarks['placeholder_token_explain1']})
                        	    <br />
                        	    <span class="album_stat">
                        	        {$lang_plugin_social_bookmarks['placeholder_token_explain2']}
                        	    </span>
                        	</label>
                        </td>
                    </tr>
                    <tr>
                          <td valign="top" class="tableb">
                            <input type="radio" name="plugin_social_bookmarks_position" id="plugin_social_bookmarks_position_content_of_main_page" class="radio" value="1" {$option_output['plugin_social_bookmarks_position_content_of_main_page']} /> 
                        </td>
                        <td valign="top" class="tableb">
                        	<label for="plugin_social_bookmarks_position_content_of_main_page" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['content_of_main_page']} ({$lang_plugin_social_bookmarks['content_of_main_page_explain1']})
                        	    <br />
                        	    <span class="album_stat">
                        	        {$lang_plugin_social_bookmarks['content_of_main_page_explain2']}
                        	    </span>
                        	</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableb">
                            <input type="radio" name="plugin_social_bookmarks_position" id="plugin_social_bookmarks_position_sys_menu" class="radio" value="2" {$option_output['plugin_social_bookmarks_position_sys_menu']} /> 
                        </td>
                        <td valign="top" class="tableb">
                        	<label for="plugin_social_bookmarks_position_sys_menu" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['sys_menu']} ({$lang_plugin_social_bookmarks['menu_explain1']})
                        	</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableb">
                            <input type="radio" name="plugin_social_bookmarks_position" id="plugin_social_bookmarks_position_sub_menu" class="radio" value="3" {$option_output['plugin_social_bookmarks_position_sub_menu']} /> 
                        </td>
                        <td valign="top" class="tableb">
                        	<label for="plugin_social_bookmarks_position_sub_menu" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['sub_menu']} ({$lang_plugin_social_bookmarks['menu_explain1']})
                        	</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableh2" colspan="3">
                            {$lang_plugin_social_bookmarks['design']}
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableb">
                            {$lang_plugin_social_bookmarks['visibility_of_details']}
                        </td>
                        <td valign="top" class="tableb" colspan="2">
                            <input type="radio" name="plugin_social_bookmarks_visibility" id="plugin_social_bookmarks_visibility_always_visible" class="radio" value="0" {$option_output['plugin_social_bookmarks_visibility_always_visible']} /> 
                        	<label for="plugin_social_bookmarks_visibility_always_visible" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['always_visible']} ({$lang_plugin_social_bookmarks['not_recommended']})
                        	</label>
                            <br />
                            <input type="radio" name="plugin_social_bookmarks_visibility" id="plugin_social_bookmarks_visibility_expand_on_click" class="radio" value="1" {$option_output['plugin_social_bookmarks_visibility_expand_on_click']} /> 
                            <label for="plugin_social_bookmarks_visibility_expand_on_click" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['expand_on_click']} ({$lang_plugin_social_bookmarks['recommended']})
                        	</label>
                            <br />   
                            <input type="radio" name="plugin_social_bookmarks_visibility" id="plugin_social_bookmarks_visibility_expand_on_mouseover" class="radio" value="2" {$option_output['plugin_social_bookmarks_visibility_expand_on_mouseover']} /> 
                        	<label for="plugin_social_bookmarks_visibility_expand_on_mouseover" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['expand_on_mouseover']}
                        	</label>
                            <br />
                            <input type="radio" name="plugin_social_bookmarks_visibility" id="plugin_social_bookmarks_visibility_display_popup" class="radio" value="3" {$option_output['plugin_social_bookmarks_visibility_display_popup']} disabled="disabled" /> 
                        	<label for="plugin_social_bookmarks_visibility_display_popup" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['display_popup']}
                        	</label>
                        </td>
                    </tr>
					<tr>
                        <td valign="top" class="tableb tableb_alternate">
                            <label for="plugin_social_bookmarks_greyout" class="clickable_option">
                                {$lang_plugin_social_bookmarks['grey_out']}
                            </label>
                        </td>
                        <td valign="top" class="tableb tableb_alternate" colspan="2">
							<input type="checkbox" name="plugin_social_bookmarks_greyout" id="plugin_social_bookmarks_greyout" class="checkbox" value="1" {$option_output['plugin_social_bookmarks_greyout']} />
							<label for="plugin_social_bookmarks_greyout" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['grey_out_explain1']}
                        	</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableb">
                            {$lang_plugin_social_bookmarks['layout']}
                        </td>
                        <td valign="top" class="tableb" colspan="2">
                            <input type="radio" name="plugin_social_bookmarks_layout" id="plugin_social_bookmarks_layout_simple_list" class="radio" value="0" {$option_output['plugin_social_bookmarks_layout_simple_list']} /> 
                        	<label for="plugin_social_bookmarks_layout_simple_list" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['simple_list']} ({$lang_plugin_social_bookmarks['simple_list_explain1']})
                        	</label>
                            <br />
                            <input type="radio" name="plugin_social_bookmarks_layout" id="plugin_social_bookmarks_layout_advanced_list" class="radio" value="1" {$option_output['plugin_social_bookmarks_layout_advanced_list']} /> 
                            <label for="plugin_social_bookmarks_layout_advanced_list" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['advanced_list']} ({$lang_plugin_social_bookmarks['advanced_list_explain1']})
                        	</label>
                            <br />   
                            <input type="radio" name="plugin_social_bookmarks_layout" id="plugin_social_bookmarks_layout_icons_only" class="radio" value="2" {$option_output['plugin_social_bookmarks_layout_icons_only']} />
                        	<label for="plugin_social_bookmarks_layout_icons_only" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['icons_only']} ({$lang_plugin_social_bookmarks['recommended']})
                        	</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableb tableb_alternate">
                            {$lang_plugin_social_bookmarks['number_of_columns']}
                        </td>
                        <td valign="top" class="tableb tableb_alternate" colspan="2">
                        	<input type="text" name="plugin_social_bookmarks_columns" id="plugin_social_bookmarks_columns" class="textinput spin-button" size="2" maxlength="2" value="{$CONFIG['plugin_social_bookmarks_columns']}" />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableh2" colspan="3">
                            {$lang_plugin_social_bookmarks['options']}
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableb">
                            <label for="plugin_social_bookmarks_smart_language" class="clickable_option">
                                {$lang_plugin_social_bookmarks['smart_language']}
                            </label>
                        </td>
                        <td valign="top" class="tableb" colspan="2">
                            <input type="checkbox" name="plugin_social_bookmarks_smart_language" id="plugin_social_bookmarks_smart_language" class="checkbox" value="1" {$option_output['plugin_social_bookmarks_smart_language']} /> 
                        	<label for="plugin_social_bookmarks_smart_language" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['smart_language_explain1']} ({$lang_plugin_social_bookmarks['recommended']})
                        	</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableb tableb_alternate">
                            <label for="plugin_social_bookmarks_admin_menu" class="clickable_option">
                                {$lang_plugin_social_bookmarks['admin_menu_item']}
                            </label>
                        </td>
                        <td valign="top" class="tableb tableb_alternate" colspan="2">
                            <input type="checkbox" name="plugin_social_bookmarks_admin_menu" id="plugin_social_bookmarks_admin_menu" class="checkbox" value="1" {$option_output['plugin_social_bookmarks_admin_menu']} /> 
                        	<label for="plugin_social_bookmarks_admin_menu" class="clickable_option">
                        	    {$lang_plugin_social_bookmarks['admin_menu_item_explain1']}
                        	</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableh2" colspan="3">
                            {$lang_plugin_social_bookmarks['services']}
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableb">
                            {$lang_plugin_social_bookmarks['available_services']}
                        </td>
                        <td valign="top" class="tableb" colspan="2">
EOT;
    starttable('100%');
    echo <<< EOT
                            <tr>
                                <th valign="top" class="tableh1">
                                    {$lang_plugin_social_bookmarks['active']}
                                </th>
                                <th valign="top" class="tableh1">
                                    {$lang_plugin_social_bookmarks['service_name']}
                                </th>
								<th valign="top" class="tableh1">
                                    {$lang_plugin_social_bookmarks['link']}
                                </th>
                                <th valign="top" class="tableh1">
                                    {$lang_plugin_social_bookmarks['relevance']}
                                </th>
                                <th valign="top" class="tableh1">
                                    {$lang_plugin_social_bookmarks['languages']}
                                </th>
                            </tr>
EOT;
    $result = cpg_db_query("SELECT * FROM {$CONFIG['TABLE_PREFIX']}plugin_social_bookmarks_services");
    $loopCounter = 0;
    while ($row = mysql_fetch_assoc($result)) {
        if ($row['icon_filename'] != '' && file_exists('plugins/social_bookmarks/images/services/'.$row['icon_filename']) == TRUE) {
            $service_icon = '<img src="plugins/social_bookmarks/images/services/'.$row['icon_filename'].'" border="0" width="16" height="16" alt="" align="left" class="icon" />';
        } else {
            $service_icon = '<img src="images/spacer.gif" border="0" width="16" height="16" alt="" align="left" class="icon" />';
        }
        if ($loopCounter/2 == floor($loopCounter/2)) {
            $tableCellStyle = 'tableb tableb_alternate';
        } else {
            $tableCellStyle = 'tableb';
        }
        $row['service_url'] = str_replace('{u}', urlencode(social_bookmarks_pagelink()) , $row['service_url']);
        $row['service_url'] = str_replace('{t}', urlencode($socialBookmarks_title) , $row['service_url']);
        unset($service_language);
        $service_language = explode('|', $row['service_lang']);
        $languageFlagString = '';

        foreach ($service_language as $countryLanguage) {
            if ($countryLanguage == 'en') {
				$countryLanguage = 'us';
			}
			if (is_file('images/flags/' . $countryLanguage . '.png') == TRUE) {
				$languageFlagString .= '<img src="images/flags/'.$countryLanguage.'.png" border="0" width="16" height="11" alt="" title="'.$lang_plugin_social_bookmarks[$countryLanguage].'" /> ';
			} elseif ($countryLanguage == 'multi') {
				$languageFlagString .= cpg_fetch_icon('babelfish', 0, $lang_plugin_social_bookmarks[$countryLanguage]);
			}
        }
        if ($row['service_active'] == 'YES') {
            $option_output['service'] = 'checked="checked"';
        } else {
            $option_output['service'] = '';
        }
		$link_title = sprintf($lang_plugin_social_bookmarks['go_to_servicename'], $row['service_name_full']);
        $relevance = '';
        if ($row['relevance'] < 1 || $row['relevance'] > 10) {
            $row['relevance'] = 0;
        }
        if ($row['relevance'] != 0) {
            $relevance = theme_display_bar($row['relevance'], 10, 150, 'lightsteelblue', '', '', 'lightsteelblue', '');
        }
        $loopCounter++;
        echo <<< EOT
                            <tr>
                                <td valign="top" align="center" class="{$tableCellStyle}">
                                    <input type="checkbox" class="checkbox" name="service_active[{$row['service_id']}]" id="service_active_{$row['service_id']}" value="1" {$option_output['service']} />
                                </td>
                                <td valign="top" class="{$tableCellStyle}">
                                    <label for="service_active_{$row['service_id']}" class="clickable_option">
                                    {$service_icon}
                                    {$row['service_name_full']}
                                    </label>
                                </td>
								<td valign="top" class="{$tableCellStyle}">
                                    <a href="{$row['service_url']}" rel="external">
                                    <img src="images/link.gif" border="0" width="16" height="16" alt="" title="{$link_title}" />
                                    </a>
                                </td>
                                <td valign="top" class="{$tableCellStyle}">
                                    <div title="{$lang_plugin_social_bookmarks['relevance']}: {$row['relevance']}" class="social_bookmarks_relevance">{$relevance}</div>
                                </td>
                                <td valign="top" class="{$tableCellStyle}">
                                    <span class="album_stat">
                                        {$languageFlagString}
                                    </span>
                                </td>
                            </tr>
EOT;
    }
    mysql_free_result($result);
    endtable();
    echo <<< EOT
                        </td>
                    </tr>
                    <tr>
                        <td valign="middle" class="tablef">
                        </td>
                        <td valign="middle" class="tablef" colspan="2">
                            <input type="hidden" name="form_token" value="{$form_token}" />
                            <input type="hidden" name="timestamp" value="{$timestamp}" />
                            <button type="submit" class="button" name="submit" value="{$lang_common['ok']}">{$social_bookmarks_icon_array['ok']}{$lang_common['ok']}</button>
                        </td>
                    </tr>
EOT;
    endtable();
    echo <<< EOT
            {$additional_submit_information}
            </form>

EOT;

}

function social_bookmarks_configuration_submit() {
	global $CONFIG;
	$superCage = Inspekt::makeSuperCage();
	// Populate the form fields and run the queries for the submit form
    $config_changes_counter = 0;
    if (!GALLERY_ADMIN_MODE) {
    	cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
    }
	
	// plugin_social_bookmarks_position (radio)
    if ($superCage->post->keyExists('plugin_social_bookmarks_position') == TRUE && ($superCage->post->getInt('plugin_social_bookmarks_position') >= '0'  && $superCage->post->getInt('plugin_social_bookmarks_position') <= '3') ) {
        if ($superCage->post->getInt('plugin_social_bookmarks_position') != $CONFIG['plugin_social_bookmarks_position']) {
        	$CONFIG['plugin_social_bookmarks_position'] = $superCage->post->getInt('plugin_social_bookmarks_position');
        	$query = "UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG['plugin_social_bookmarks_position']}' WHERE name='plugin_social_bookmarks_position'";
        	cpg_db_query($query);
        	$config_changes_counter++;
    	}
    }
    
    // plugin_social_bookmarks_visibility (radio)
    if ($superCage->post->keyExists('plugin_social_bookmarks_visibility') == TRUE && ($superCage->post->getInt('plugin_social_bookmarks_visibility') >= '0'  && $superCage->post->getInt('plugin_social_bookmarks_visibility') <= '3') ) {
        if ($superCage->post->getInt('plugin_social_bookmarks_visibility') != $CONFIG['plugin_social_bookmarks_visibility']) {
        	$CONFIG['plugin_social_bookmarks_visibility'] = $superCage->post->getInt('plugin_social_bookmarks_visibility');
        	$query = "UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG['plugin_social_bookmarks_visibility']}' WHERE name='plugin_social_bookmarks_visibility'";
        	cpg_db_query($query);
        	$config_changes_counter++;
    	}
    }
    
    // plugin_social_bookmarks_greyout (radio)
    if ($superCage->post->keyExists('plugin_social_bookmarks_greyout') == TRUE && $superCage->post->getInt('plugin_social_bookmarks_greyout') == '1' ) {
        if ($superCage->post->getInt('plugin_social_bookmarks_greyout') != $CONFIG['plugin_social_bookmarks_greyout']) {
        	$CONFIG['plugin_social_bookmarks_greyout'] = $superCage->post->getInt('plugin_social_bookmarks_greyout');
        	$query = "UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG['plugin_social_bookmarks_greyout']}' WHERE name='plugin_social_bookmarks_greyout'";
        	cpg_db_query($query);
        	$config_changes_counter++;
    	}
    } elseif ($superCage->post->keyExists('plugin_social_bookmarks_greyout') != TRUE && $CONFIG['plugin_social_bookmarks_greyout'] != '0') {
        	$CONFIG['plugin_social_bookmarks_greyout'] = '0';
        	$query = "UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG['plugin_social_bookmarks_greyout']}' WHERE name='plugin_social_bookmarks_greyout'";
        	cpg_db_query($query);
        	$config_changes_counter++;    
    }
    
    // plugin_social_bookmarks_layout (radio)
    if ($superCage->post->keyExists('plugin_social_bookmarks_layout') == TRUE && ($superCage->post->getInt('plugin_social_bookmarks_layout') >= '0'  && $superCage->post->getInt('plugin_social_bookmarks_layout') <= '2') ) {
        if ($superCage->post->getInt('plugin_social_bookmarks_layout') != $CONFIG['plugin_social_bookmarks_layout']) {
        	$CONFIG['plugin_social_bookmarks_layout'] = $superCage->post->getInt('plugin_social_bookmarks_layout');
        	$query = "UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG['plugin_social_bookmarks_layout']}' WHERE name='plugin_social_bookmarks_layout'";
        	cpg_db_query($query);
        	$config_changes_counter++;
    	}
    }
    
    // plugin_social_bookmarks_columns (text-numeric)
    if ($superCage->post->keyExists('plugin_social_bookmarks_columns') == TRUE) {
        if ($superCage->post->getInt('plugin_social_bookmarks_columns') >= 1 && $superCage->post->getInt('plugin_social_bookmarks_columns') <= 50 && $CONFIG['plugin_social_bookmarks_columns'] != $superCage->post->getInt('plugin_social_bookmarks_columns')) {
            $CONFIG['plugin_social_bookmarks_columns'] = $superCage->post->getInt('plugin_social_bookmarks_columns');
            $query = "UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG['plugin_social_bookmarks_columns']}' WHERE name='plugin_social_bookmarks_columns'";
            cpg_db_query($query);
            $config_changes_counter++;
        }
    }
	
    // plugin_social_bookmarks_smart_language (checkbox)
    if ($superCage->post->keyExists('plugin_social_bookmarks_smart_language') == TRUE && $superCage->post->getInt('plugin_social_bookmarks_smart_language') == '1' ) {
        if ($superCage->post->getInt('plugin_social_bookmarks_smart_language') != $CONFIG['plugin_social_bookmarks_smart_language']) {
        	$CONFIG['plugin_social_bookmarks_smart_language'] = $superCage->post->getInt('plugin_social_bookmarks_smart_language');
        	$query = "UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG['plugin_social_bookmarks_smart_language']}' WHERE name='plugin_social_bookmarks_smart_language'";
        	cpg_db_query($query);
        	$config_changes_counter++;
    	}
    } elseif ($superCage->post->keyExists('plugin_social_bookmarks_smart_language') != TRUE && $CONFIG['plugin_social_bookmarks_smart_language'] != '0') {
        	$CONFIG['plugin_social_bookmarks_smart_language'] = '0';
        	$query = "UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG['plugin_social_bookmarks_smart_language']}' WHERE name='plugin_social_bookmarks_smart_language'";
        	cpg_db_query($query);
        	$config_changes_counter++;    
    }
    
    // plugin_social_bookmarks_admin_menu (radio)
    if ($superCage->post->keyExists('plugin_social_bookmarks_admin_menu') == TRUE && $superCage->post->getInt('plugin_social_bookmarks_admin_menu') == '1' ) {
        if ($superCage->post->getInt('plugin_social_bookmarks_admin_menu') != $CONFIG['plugin_social_bookmarks_admin_menu']) {
        	$CONFIG['plugin_social_bookmarks_admin_menu'] = $superCage->post->getInt('plugin_social_bookmarks_admin_menu');
        	$query = "UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG['plugin_social_bookmarks_admin_menu']}' WHERE name='plugin_social_bookmarks_admin_menu'";
        	cpg_db_query($query);
        	$config_changes_counter++;
    	}
    } elseif ($superCage->post->keyExists('plugin_social_bookmarks_admin_menu') != TRUE && $CONFIG['plugin_social_bookmarks_admin_menu'] != '0') {
        	$CONFIG['plugin_social_bookmarks_admin_menu'] = '0';
        	$query = "UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG['plugin_social_bookmarks_admin_menu']}' WHERE name='plugin_social_bookmarks_admin_menu'";
        	cpg_db_query($query);
        	$config_changes_counter++;    
    }
    
    // service_active (checkboxes) 
    $service_active_array = $superCage->post->getRaw('service_active'); // We have an array, so we'll need to use getRaw and sanitize later
    $result = cpg_db_query("SELECT service_id, service_active FROM {$CONFIG['TABLE_PREFIX']}plugin_social_bookmarks_services");
    //$query = "UPDATE {$CONFIG['TABLE_PREFIX']}plugin_social_bookmarks_services SET value='{$CONFIG['plugin_social_bookmarks_admin_menu']}' WHERE name='plugin_social_bookmarks_admin_menu'";
    $query_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        if ($service_active_array[$row['service_id']] == 1 && $row['service_active'] == 'NO') {
            $query_array[] = "UPDATE {$CONFIG['TABLE_PREFIX']}plugin_social_bookmarks_services SET service_active='YES' WHERE service_id='{$row['service_id']}'";
            $config_changes_counter++;
        } elseif (isset($service_active_array[$row['service_id']]) == FALSE && $row['service_active'] == 'YES') {
            $query_array[] = "UPDATE {$CONFIG['TABLE_PREFIX']}plugin_social_bookmarks_services SET service_active='YES' WHERE service_id='{$row['service_id']}'";
            $config_changes_counter++;
        }
    } // end while loop
    mysql_free_result($result);
    if (isset($query_array) == TRUE) {
        foreach ($query_array as $query) {
            cpg_db_query($query);
        }
    }
    unset($query_array);
	return $config_changes_counter;
}

function social_bookmarks_admin_menu_button($admin_menu){
    global $CONFIG;
    if ($CONFIG['plugin_social_bookmarks_admin_menu'] == 1) {
    	// Initialize language and icons
    	require_once './plugins/social_bookmarks/init.inc.php';
    	$social_bookmarks_init_array = social_bookmarks_initialize();
    	$lang_plugin_social_bookmarks = $social_bookmarks_init_array['language']; 
    	$social_bookmarks_icon_array = $social_bookmarks_init_array['icon'];
        $new_button = '<div class="admin_menu admin_float"><a href="index.php?file=social_bookmarks/admin" title="' . $lang_plugin_social_bookmarks['config'] . '">'. $social_bookmarks_icon_array['configure'] . $lang_plugin_social_bookmarks['config'] . '</a></div>';
        $look_for = '<!-- END plugin_manager -->';
        $admin_menu = str_replace($look_for, $look_for . $new_button, $admin_menu);
    }
    return $admin_menu;
}

function social_bookmarks_menu_button($menu) {
    global $CONFIG, $LINEBREAK, $lang_plugin_social_bookmarks, $template_sys_menu_spacer, $template_sub_menu_spacer;
	// Initialize language and icons
	require_once './plugins/social_bookmarks/init.inc.php';
	$social_bookmarks_init_array = social_bookmarks_initialize();
	$lang_plugin_social_bookmarks = $social_bookmarks_init_array['language']; 
    $new_button = array();
    $new_button[0][0] = $lang_plugin_social_bookmarks['menu_name'];
    $new_button[0][1] = $lang_plugin_social_bookmarks['menu_title'];
    $new_button[0][2] = 'index.php?file=social_bookmarks/index';
    $new_button[0][3] = 'social_bookmarks';
    if ($CONFIG['plugin_social_bookmarks_position'] == 2) {
        $new_button[0][4] = $template_sys_menu_spacer;
    } elseif ($CONFIG['plugin_social_bookmarks_position'] == 3) {
        $new_button[0][4] = $template_sub_menu_spacer;
    } 
    $new_button[0][5] = 'id="social_bookmarks_menu_link" rel="nofollow"'; // Additional parameters for the <a href>-tag
    if ($CONFIG['plugin_social_bookmarks_greyout'] && $CONFIG['plugin_social_bookmarks_visibility'] != '0') {
        $new_button[0][5] .= ' class="greybox"';
    }
    array_splice($menu, count($menu)-1, 0, $new_button);
    return $menu;
}

function social_bookmarks_page_meta($var) {
	global $CONFIG, $JS, $lang_plugin_social_bookmarks, $LINEBREAK;
	require_once './plugins/social_bookmarks/init.inc.php';
    $var = '<link rel="stylesheet" href="plugins/social_bookmarks/style.css" type="text/css" />' . $LINEBREAK . $var;
    if ($CONFIG['plugin_social_bookmarks_position'] == '2' || $CONFIG['plugin_social_bookmarks_position'] == '3') {
        // define some vars that need to exist in JS
        set_js_var('bookmarks_position', $CONFIG['plugin_social_bookmarks_position']);
        set_js_var('bookmarks_visibility', $CONFIG['plugin_social_bookmarks_visibility']);
        set_js_var('bookmarks_layout', $CONFIG['plugin_social_bookmarks_layout']);
        set_js_var('bookmarks_greyout', $CONFIG['plugin_social_bookmarks_greyout']);
        set_js_var('bookmarks_content', social_bookmarks_content());
    }
    return $var;
}

function social_bookmarks_title_get($template_vars) {
	global $socialBookmarks_title;
	$socialBookmarks_title = $template_vars['{TITLE}'];
	return $template_vars;
}

function social_bookmarks_mainpage() {
    global $CONFIG, $matches, $lang_plugin_social_bookmarks, $social_bookmarks_icon_array, $LINEBREAK;
    if ($CONFIG['plugin_social_bookmarks_position'] != 1) { // If the plugin config option hasn't been enabled, return without actually outputting anything
        return $matches;
    }	
	// If there is no record "socialbookmarks" for the coppermine config option "the content of the mainpage", let's add it to the very start
	$contentOfTheMainpage_array = explode('/',$CONFIG['main_page_layout']);
	if (in_array('socialbookmarks', $contentOfTheMainpage_array) != TRUE && 1==2){
		$CONFIG['main_page_layout'] = 'socialbookmarks/' . $CONFIG['main_page_layout'];
		$query = "UPDATE {$CONFIG['TABLE_CONFIG']} SET value='{$CONFIG['main_page_layout']}' WHERE name='main_page_layout'";
        cpg_db_query($query);
	}
	
	if($matches[1] != 'socialbookmarks') {
        return $matches;
    }
	echo social_bookmarks_block();
}

function social_bookmarks_template($template) {
    global $CONFIG;
	$gallery_pos = strpos($template, '{SOCIAL_BOOKMARKS}');
    if ($gallery_pos) {
        if ($CONFIG['plugin_social_bookmarks_position'] != '0') {
			$template    = str_replace('{SOCIAL_BOOKMARKS}', '', $template); // Remove the token from the output if the corresponding plugin config option hasn't been enabled
		} else {
			$template    = str_replace('{SOCIAL_BOOKMARKS}', social_bookmarks_block(), $template);
		}
    } else { // There's no placeholder token {SOCIAL_BOOKMARKS} inside $template
		if ($CONFIG['plugin_social_bookmarks_position'] != '0') {
			return $template;
		} else {
			$template = str_replace('{GALLERY}', social_bookmarks_block() . '{GALLERY}', $template);
		}
	}
	return $template;
}

function social_bookmarks_block() {
	global $CONFIG, $lang_plugin_social_bookmarks, $social_bookmarks_icon_array, $LINEBREAK;
	// Initialize language and icons
	require_once './plugins/social_bookmarks/init.inc.php';
	$social_bookmarks_init_array = social_bookmarks_initialize();
	$lang_plugin_social_bookmarks = $social_bookmarks_init_array['language']; 
	$social_bookmarks_icon_array = $social_bookmarks_init_array['icon'];
	// Echo everything into a buffer
	ob_start();
   
    if ($CONFIG['plugin_social_bookmarks_greyout'] && $CONFIG['plugin_social_bookmarks_visibility'] != '0') {
        $css_class = ' class="greybox"';
    } else {
        $css_class = '';
    }
	$clickable_table_header = $social_bookmarks_icon_array['page'] . $lang_plugin_social_bookmarks['menu_name'];
    starttable("100%", $clickable_table_header);
    $main_output = social_bookmarks_content();
    // Visibility options need to be applied!
    echo <<< EOT
    <tr>
        <td class="tableb">
			{$main_output}
        </td>
    </tr>
EOT;
    endtable();
	echo '<br />' . $LINEBREAK;
	$return = ob_get_contents();
	ob_end_clean();
	return $return;
}
?>