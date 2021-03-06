#/**************************************************
#  Coppermine 1.5.x Plugin - thumb_rotate
#  *************************************************
#  Copyright (c) 2010 Timos-Welt (www.timos-welt.de)
#  *************************************************
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 3 of the License, or
#  (at your option) any later version.
#  ********************************************
#  $HeadURL$
#  $Revision$
#  $LastChangedBy$
#  $Date$
#  **************************************************/
  
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_radius', '20');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_maxrotation', '15');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_bgcolor', '#EFEFEF');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_borderwidth', '10');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_bordercolor', '#FFFFFF');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_delete_cache', '1');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_thumb_pfx', 'rotate_');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_timestamp', UNIX_TIMESTAMP());
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_timelimit', '0');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_admin_only', '0');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_filetype', 'png');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_thumb_rotate_rotation_method', 'random');

CREATE TABLE IF NOT EXISTS `CPG_plugin_thumb_rotate` (
  `pid` mediumint(10) NOT NULL default '0',
  `filepath` varchar(255) NOT NULL default '',
  `width` smallint(6) NOT NULL default '0',
  `height` smallint(6) NOT NULL default '0'
) TYPE=MyISAM COMMENT='Contains the rotated thumbnail cache settings';