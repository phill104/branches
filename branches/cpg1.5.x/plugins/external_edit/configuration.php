<?php
/**************************
  Plugin "External Edit"
  *************************
  Copyright (c) Joachim Müller
  For Coppermine Photo Gallery cpg1.5.x

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 3
  as published by the Free Software Foundation.
    
  ********************************************
  $HeadURL: https://coppermine.svn.sourceforge.net/svnroot/coppermine/trunk/cpg1.5.x/banning.php $
  $Revision: 6131 $
  $LastChangedBy: gaugau $
  $Date: 2009-06-10 08:42:56 +0200 (Mi, 10 Jun 2009) $


**********************************************/

require_once "./plugins/external_edit/init.inc.php";
$external_edit_init_array = external_edit_init();
$lang_plugin_external_edit = $external_edit_init_array['language']; 
$external_edit_icon_array = $external_edit_init_array['icon'];
$name = $lang_plugin_external_edit['plugin_name'];
$description = sprintf($lang_plugin_external_edit['plugin_description'], '<a href="http://fotoflexer.com/" rel="external" class="external">Fotoflexer.com</a>');
$author = sprintf($lang_plugin_external_edit['author'], 
                  '<a href="http://forum.coppermine-gallery.net/index.php?action=profile;u=2" rel="external" class="external">Joachim Müller</a>');
$version = '2.3';
$extra_info = <<<EOT
    <a href="http://forum.coppermine-gallery.net/index.php/topic,60173.0.html" title="&laquo;{$lang_plugin_external_edit['plugin_name']}&raquo; - {$lang_plugin_external_edit['announcement_thread']}" class="admin_menu">{$external_edit_icon_array['announcement']}{$lang_plugin_external_edit['announcement_thread']}</a>
EOT;

$install_info = <<<EOT
    {$lang_plugin_external_edit['plugin_description_detail']}<br />&nbsp;<br />
    <a href="http://forum.coppermine-gallery.net/index.php/topic,60173.0.html" title="&laquo;{$lang_plugin_external_edit['plugin_name']}&raquo; - {$lang_plugin_external_edit['announcement_thread']}" class="admin_menu">{$external_edit_icon_array['announcement']}{$lang_plugin_external_edit['announcement_thread']}</a>
EOT;
?>
