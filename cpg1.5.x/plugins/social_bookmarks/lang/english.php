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

$lang_plugin_social_bookmarks['name'] = 'Social Bookmarks';
$lang_plugin_social_bookmarks['description'] = 'Adds links to social bookmark services';
$lang_plugin_social_bookmarks['menu_name'] = 'Social Bookmarks';
$lang_plugin_social_bookmarks['menu_title'] = 'Add this page to your favorite social bookmark service';
$lang_plugin_social_bookmarks['announcement_thread'] = 'Announcement thread';
$lang_plugin_social_bookmarks['cleanup'] = 'Social Bookmarks Cleanup';
$lang_plugin_social_bookmarks['config'] = 'Social Bookmarks Configuration';
$lang_plugin_social_bookmarks['remove_services'] = 'Delete database records?';
$lang_plugin_social_bookmarks['remove_services_explanation'] = 'This will permanently delete the social bookmarks\' plugin database records for the services that are available including services that you may have added manually.';
$lang_plugin_social_bookmarks['drop_table_warning'] = 'Deleting database tables can not be undone (that\'s what the above option does)! If you\'re not sure, don\'t tick the checkbox that corresponds to the deleting of the table.';
$lang_plugin_social_bookmarks['submit_to_uninstall'] = 'The plugin isn\'t uninstalled yet. Submit the form to actually uninstall the plugin.';
$lang_plugin_social_bookmarks['no_service_activated'] = 'No social bookmark service is activated';
// Configuration --- start
$lang_plugin_social_bookmarks['site_integration'] = 'Site integration';
$lang_plugin_social_bookmarks['position_of_button'] = 'Position of Social Bookmark button';
$lang_plugin_social_bookmarks['placeholder_token'] = 'Placeholder Token';
$lang_plugin_social_bookmarks['placeholder_token_explain1'] = 'on each gallery page';
$lang_plugin_social_bookmarks['placeholder_token_explain2'] = 'If you pick this option you need to edit your custom theme and add a placeholder token {SOCIAL_BOOKMARKS} to the file template.html';
$lang_plugin_social_bookmarks['content_of_main_page'] = 'Content of the Main Page';
$lang_plugin_social_bookmarks['content_of_main_page_explain1'] = 'only on gallery index pages';
$lang_plugin_social_bookmarks['content_of_main_page_explain2'] = 'If you enable this option you need to add an item named socialbookmarks (mind the correct spelling) to the config option "the content of the main page"';
$lang_plugin_social_bookmarks['sys_menu'] = 'Sys-Menu';
$lang_plugin_social_bookmarks['menu_explain1'] = 'inside the regular coppermine menu';
$lang_plugin_social_bookmarks['sub_menu'] = 'Sub-Menu';
$lang_plugin_social_bookmarks['design'] = 'Design';
$lang_plugin_social_bookmarks['visibility_of_details'] = 'Visibility of Social Bookmarks details';
$lang_plugin_social_bookmarks['always_visible'] = 'always expanded';
$lang_plugin_social_bookmarks['expand_on_click'] = 'expand on click';
$lang_plugin_social_bookmarks['expand_on_mouseover'] = 'expand on mouse over';
$lang_plugin_social_bookmarks['display_popup'] = 'display pop-up';
$lang_plugin_social_bookmarks['grey_out'] = 'Grey out';
$lang_plugin_social_bookmarks['grey_out_explain1'] = 'Grey out rest of screen when focus is on social bookmark details';
$lang_plugin_social_bookmarks['layout'] = 'Layout';
$lang_plugin_social_bookmarks['simple_list'] = 'Simple List';
$lang_plugin_social_bookmarks['simple_list_explain1'] = 'service names only';
$lang_plugin_social_bookmarks['advanced_list'] = 'Advanced List';
$lang_plugin_social_bookmarks['advanced_list_explain1'] = 'icons and service names';
$lang_plugin_social_bookmarks['icons_only'] = 'Icons only';
$lang_plugin_social_bookmarks['number_of_columns'] = 'Number of columns';
$lang_plugin_social_bookmarks['services'] = 'Services';
$lang_plugin_social_bookmarks['available_services'] = 'Available services';
$lang_plugin_social_bookmarks['active'] = 'Active';
$lang_plugin_social_bookmarks['service_name'] = 'Service name';
$lang_plugin_social_bookmarks['link'] = 'Link';
$lang_plugin_social_bookmarks['go_to_servicename'] = 'Go to %s';
$lang_plugin_social_bookmarks['languages'] = 'Languages';
$lang_plugin_social_bookmarks['relevance'] = 'Relevance';
$lang_plugin_social_bookmarks['options'] = 'Options';
$lang_plugin_social_bookmarks['smart_language'] = 'Smart language';
$lang_plugin_social_bookmarks['smart_language_explain1'] = 'Only display social bookmark services that are available in the visitor\'s prefered language';
$lang_plugin_social_bookmarks['admin_menu_item'] = 'Admin menu item';
$lang_plugin_social_bookmarks['admin_menu_item_explain1'] = 'Add a link to the admin menu for easy access of the plugin\'s config page (this screen)';
$lang_plugin_social_bookmarks['recommended'] = 'recommended';
$lang_plugin_social_bookmarks['not_recommended'] = 'not recommended';
$lang_plugin_social_bookmarks['changes_saved'] = 'Your changes have been saved';
$lang_plugin_social_bookmarks['no_changes'] = 'There have been no changes or the values you entered where invalid';
$lang_plugin_social_bookmarks['submit_to_install'] = 'Submit this form to install the plugin';
// Configuration --- end
// Languages & Countries --- start
$lang_plugin_social_bookmarks['br'] = 'Brazilian Portuguese';
$lang_plugin_social_bookmarks['cn'] = 'Chinese';
$lang_plugin_social_bookmarks['de'] = 'German';
$lang_plugin_social_bookmarks['en'] = 'English';
$lang_plugin_social_bookmarks['es'] = 'Spanish';
$lang_plugin_social_bookmarks['fr'] = 'French';
$lang_plugin_social_bookmarks['hr'] = 'Kroatian';
$lang_plugin_social_bookmarks['hu'] = 'Hungarian';
$lang_plugin_social_bookmarks['it'] = 'Italian';
$lang_plugin_social_bookmarks['jp'] = 'Japanese';
$lang_plugin_social_bookmarks['kr'] = 'Korean';
$lang_plugin_social_bookmarks['lt'] = 'Lithuanian';
$lang_plugin_social_bookmarks['nl'] = 'Dutch';
$lang_plugin_social_bookmarks['ir'] = 'Persian';
$lang_plugin_social_bookmarks['pl'] = 'Polish';
$lang_plugin_social_bookmarks['ru'] = 'Russian';
$lang_plugin_social_bookmarks['se'] = 'Swedish';
$lang_plugin_social_bookmarks['tr'] = 'Turkish';
$lang_plugin_social_bookmarks['tw'] = 'Taiwanese (Chinese)';
$lang_plugin_social_bookmarks['multi'] = 'Language-independant';
// Languages & Countries --- end

?>