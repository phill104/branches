CREATE TABLE IF NOT EXISTS `CPG_plugin_enlargeit` (
  `enl_brd` int(2) NOT NULL default '1',
  `enl_brdsize` int(2) NOT NULL default '10',
  `enl_brdround` int(2) NOT NULL default '1',
  `enl_brdcolor` varchar(7) default '#FFFFFF',
  `enl_shadow` int(2) NOT NULL default '1',
  `enl_shadowsize` int(2) NOT NULL default '1',
  `enl_shadowintens` int(2) NOT NULL default '20',
  `enl_ani` int(2) NOT NULL default '3',
  `enl_maxstep` int(2) NOT NULL default '21',
  `enl_speed` int(2) NOT NULL default '12',
  `enl_titlebar` int(2) NOT NULL default '1',
  `enl_titletxtcol` varchar(7) default '#999999',
  `enl_ajaxcolor` varchar(7) default '#DDDDDD',
  `enl_center` int(2) NOT NULL default '1',
  `enl_dark` int(2) NOT NULL default '0',
  `enl_darkprct` int(2) NOT NULL default '50',
  `enl_buttonpic` int(2) NOT NULL default '0',
  `enl_buttoninfo` int(2) NOT NULL default '0',
  `enl_buttonfav` int(2) NOT NULL default '0',
  `enl_buttoncomment` int(2) NOT NULL default '0',
  `enl_buttondownload` int(2) NOT NULL default '0',
  `enl_buttonbbcode` int(2) NOT NULL default '0',
  `enl_buttonhist` int(2) NOT NULL default '0',
  `enl_buttonvote` int(2) NOT NULL default '0',
  `enl_buttonmax` int(2) NOT NULL default '0',
  `enl_buttonclose` int(2) NOT NULL default '0',
  `enl_buttonnav` int(2) NOT NULL default '0',
  `enl_adminmode` int(2) NOT NULL default '1',
  `enl_registeredmode` int(2) NOT NULL default '1',
  `enl_guestmode` int(2) NOT NULL default '1',
  `enl_sefmode` int(2) NOT NULL default '0',
  `enl_pictype` int(2) NOT NULL default '0',
  `enl_dragdrop` int(2) NOT NULL default '1',
  `enl_wheelnav` int(2) NOT NULL default '1',
  `enl_flvplayer` int(2) NOT NULL default '1',
  `enl_opaglide` int(2) NOT NULL default '1',
  `enl_brdbck` varchar(20) default '',
  `enl_darkensteps` int(2) NOT NULL default '15'
);