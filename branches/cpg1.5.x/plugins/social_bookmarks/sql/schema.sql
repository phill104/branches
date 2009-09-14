##  ********************************************
##  social_bookmarks plugin for cpg1.5.x
##  *******************************************
##  Copyright (c) 2003-2009 Coppermine Dev Team
##  
##  This program is free software; you can redistribute it and/or modify
##  it under the terms of the GNU General Public License version 3
##  as published by the Free Software Foundation.
##  
##  ********************************************
##  Coppermine version: 1.5.x
##  $HeadURL: https://coppermine.svn.sourceforge.net/svnroot/coppermine/branches/cpg1.5.x/plugins/mass_import/codebase.php $
##  $Revision: 6497 $
##  $LastChangedBy: gaugau $
##  $Date: 2009-08-19 18:54:16 +0200 (Mi, 19. Aug 2009) $
##  **********************************************

INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_social_bookmarks_position', '2');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_social_bookmarks_visibility', '2');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_social_bookmarks_greyout', '0');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_social_bookmarks_layout', '2');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_social_bookmarks_columns', '5');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_social_bookmarks_favorites', '0');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_social_bookmarks_recommend', '0');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_social_bookmarks_captcha', '1');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_social_bookmarks_smart_language', '1');
INSERT IGNORE INTO CPG_config ( `name` , `value` ) VALUES ('plugin_social_bookmarks_admin_menu', '0');


CREATE TABLE IF NOT EXISTS `CPG_plugin_social_bookmarks_services` (
  service_id int(11) NOT NULL auto_increment,
  service_active enum('YES','NO') NOT NULL default 'NO',
  service_name_short varchar(25) NOT NULL default '',
  service_name_full varchar(25) NOT NULL default '',
  service_url varchar(255) NOT NULL default '',
  service_lang varchar(255) NOT NULL default '',
  icon_filename varchar(40) NOT NULL default '',
  relevance int(4) NOT NULL default 0,
  PRIMARY KEY  (service_id),
  UNIQUE KEY service_name_full (service_name_full),
  UNIQUE KEY service_url (service_url)
) TYPE=MyISAM COMMENT='Contains the social bookmark services';

INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Alltagz', 'alltagz', 'http://www.alltagz.de/bookmarks/?action=add&amp;address={u}&amp;title={t}', 'de', 'alltagz.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Allvoices', 'Allvoices', 'http://www.allvoices.com/post_event?url={u}&amp;title={t}', 'en', 'allvoices.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Amazon', 'Amazon WishList', 'http://www.amazon.com/wishlist/add?u={u}&amp;t={t}', 'en', 'amazon.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('AOL', 'myAOL', 'http://favorites.my.aol.com/ffclient/AddBookmark?url={u}&amp;title={t}', 'en', 'aol.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Arto', 'Arto', 'http://www.arto.com/section/linkshare/?lu={u}&amp;ln={t}', 'en', 'arto.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Ask', 'Ask', 'http://myjeeves.ask.com/mysearch/BookmarkIt?v=1.2&amp;t=webpages&amp;url={u}&amp;title={t}', 'en', 'ask.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Backflip', 'Backflip', 'http://www.backflip.com/add_page_pop.ihtml?url={u}&amp;title={t}', 'en', 'backflip.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('BallHype', 'BallHype', 'http://ballhype.com/post/url/?url={u}&amp;title={t}', 'en', 'ballhype.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Bebo', 'Bebo', 'http://bebo.com/c/share?Url={u}&amp;Title={t}', 'en', 'bebo.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('BibSonomy', 'BibSonomy', 'http://www.bibsonomy.org/BibtexHandler?requTask=upload&amp;url={u}&amp;description={t}', 'en', 'bibsonomy.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('BlinkList', 'BlinkList', 'http://www.blinklist.com/index.php?Action=Blink/addblink.php&amp;Url={u}&amp;Title={t}', 'en', 'blink.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Blogmarks', 'Blogmarks', 'http://blogmarks.net/my/new.php?mini=1&amp;simple=1&amp;url={u}&amp;title={t}', 'en', 'blogmarks.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Bookmarkit', 'bookmark.it', 'http://www.bookmark.it/bookmark.php?url={u}', 'it', 'bookmark.it.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Bookmarks.fr', 'Bookmarks.fr', 'http://www.bookmarks.fr/favoris/AjoutFavori?action=add&amp;address={u}&amp;title={t}', 'fr', 'bookmarks.fr.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('BuddyMarks', 'BuddyMarks', 'http://buddymarks.com/add_bookmark.php?bookmark_url={u}&amp;bookmark_title={t}', 'en', 'buddymarks.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('BX', 'Business Exchange', 'http://bx.businessweek.com/api/add-article-to-bx.tn?url={u}', 'en', 'business_exchange.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Bzzster', 'Bzzster', 'http://bzzster.com/share?v=5;link={u}&amp;subject={t}', 'en', 'bzzster.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Care2', 'Care2', 'http://www.care2.com/news/news_post.html?url={u}&amp;title={t}', 'en', 'care2.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Citeulike', 'Citeulike', 'http://www.citeulike.org/posturl?url={u}&amp;title={t}', 'en', 'citeulike.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('connotea', 'Connotea', 'http://www.connotea.org/add?uri={u}&amp;title={t}', 'en', 'connotea.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Current', 'Current', 'http://current.com/clipper.htm?url={u}&amp;title={t}', 'en', 'current.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Deals plus', 'deals plus', 'http://dealspl.us/add.php?ibm=1&amp;url={u}', 'en', 'deals_plus.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('del.icio.us', 'del.icio.us', 'http://del.icio.us/post?url={u}&amp;title={t}', 'en', 'del.icio.us.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Design Float', 'Design Float', 'http://www.designfloat.com/submit.php?url={u}&amp;title={t}', 'en', 'design_float.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Digg', 'Digg', 'http://digg.com/submit?phase=2&amp;url={u}&amp;title={t}', 'en', 'digg.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Diigo', 'Diigo', 'http://www.diigo.com/post?url={u}&amp;title={t}', 'en', 'diigo.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('DZone', 'DZone', 'http://www.dzone.com/links/add.html?url={u}&amp;title={t}', 'en', 'dzone.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Evernote', 'Evernote', 'http://www.evernote.com/clip.action?url={u}&amp;title={t}', 'en', 'evernote.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Expression', 'Microsoft Expression', 'http://social.expression.microsoft.com/en-US/action/Create/s/E/?url={u}&amp;bm=true&amp;ttl={t}', 'br|cn|cz|de|en|es|fr|it|jp|kr|ru|cn|', 'expression.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`, `relevance`) VALUES ('Facebook', 'Facebook', 'http://www.facebook.com/sharer.php?u={u}&amp;t={t}', 'en', 'facebook.png', 8);
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Fark', 'Fark', 'http://cgi.fark.com/cgi/fark/submit.pl?new_url={u}&amp;new_comment={t}', 'en', 'fark.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Faves', 'Faves', 'http://faves.com/Authoring.aspx?u={u}&amp;t={t}', 'en', 'faves.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Folkd', 'Folkd', 'http://www.folkd.com/submit/{u}', 'en', 'folkd.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('FoxieWire', 'FoxieWire', 'http://www.foxiewire.com/submit?url={u}&amp;title={t}', 'en', 'foxiewire.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Fresqui', 'Fresqui', 'http://ocio.fresqui.com/post?url={u}&amp;title={t}', 'en', 'fresqui.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('FriendFeed', 'FriendFeed', 'http://friendfeed.com/share?url={u}&amp;title={t}', 'en', 'friendfeed.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('FunP', 'funP', 'http://funp.com/pages/submit/add.php?url={u}&amp;title={t}', 'en', 'funp.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Gabbr', 'Gabbr', 'http://www.gabbr.com/submit/?bookurl={u}', 'en', 'gabbr.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Global Grind', 'Global Grind', 'http://globalgrind.com/submission/submit.aspx?url={u}&amp;type=Article&amp;title={t}', 'en', 'global_grind.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Google', 'Google Bookmarks', 'http://www.google.com/bookmarks/mark?op=edit&amp;bkmk={u}&amp;title={t}', 'en', 'google.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Gravee', 'Gravee', 'http://www.gravee.com/account/bookmarkpop?u={u}&amp;t={t}', 'en', 'gravee.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('HealthRanker', 'HealthRanker', 'http://www.healthranker.com/submit.php?url={u}&amp;title={t}', 'en', 'healthranker.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('HemiDemi', 'HEMiDEMi', 'http://www.hemidemi.com/user_bookmark/new?url={u}&amp;title={t}', 'en', 'hemidemi.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Hugg', 'Hugg', 'http://www.hugg.com/submit?url={u}', 'en', 'hugg.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Identi.ca', 'identi.ca', 'http://identi.ca/notice/new?status_textarea={t}%20{u}', 'en', 'identi.ca.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Imera', 'Imera', 'http://www.imera.com.br/post_d.html?linkUrl={u}&amp;linkName={t}', 'en', 'imera.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Instapaper', 'Instapaper', 'http://www.instapaper.com/b?u={u}&amp;t={y}', 'en', 'instapaper.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Jamespot', 'Jamespot', 'http://www.jamespot.com/?action=spotit&amp;url={u}', 'en', 'jamespot.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Jumptags', 'Jumptags', 'http://www.jumptags.com/add/?url={u}&amp;title={t}', 'en', 'jumptags.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Kaboodle', 'Kaboodle', 'http://www.kaboodle.com/grab/addItemWithUrl?url={u}&amp;pidOrRid=pid=&amp;redirectToKPage=true', 'en', 'kaboodle.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Khabbr', 'Khabbr', 'http://www.khabbr.com/submit.php?out=yes&amp;url={u}', 'en', 'khabbr.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Kledy', 'Kledy', 'http://www.kledy.de/submit.php?url={u}', 'de', 'kledy.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Kirtsy', 'Kirtsy', 'http://www.kirtsy.com/submit.php?url={u}', 'en', 'kirtsy.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Kool', 'Koolontheweb', 'http://www.koolontheweb.com/post?url={u}&amp;title={t}', 'en', 'koolontheweb.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Linkarena', 'Linkarena', 'http://linkarena.com/bookmarks/addlink/?url={u}&amp;title={t}&amp;desc=&amp;tags=', 'en', 'linkarena.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Linkagogo', 'LinkaGoGo', 'http://www.linkagogo.com/go/AddNoPopup?url={u}&amp;title={t}', 'en', 'linkagogo.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Linkedin', 'LinkedIn', 'http://www.linkedin.com/shareArticle?mini=true&amp;url={u}&amp;title={t}&amp;ro=false&amp;summary=&amp;source=', 'en', 'linkedin.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('LiveJournal', 'LiveJournal', 'http://www.livejournal.com/update.bml?subject={u}', 'en', 'livejournal.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Maple', 'Maple', 'http://www.maple.nu/bookmarks/bookmarklet?bookmark[url]={u}&amp;bookmark[description]={t}', 'en', 'maple.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Meneame', 'menéame', 'http://meneame.net/submit.php?url={u}', 'en', 'meneame.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('MindBody', 'MindBodyGreen', 'http://www.mindbodygreen.com/passvote.action?u={u}', 'en', 'mindbodygreen.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Mr Wong', 'Mister Wong', 'http://www.mister-wong.com/index.php?action=addurl&amp;bm_url={u}&amp;bm_description={t}', 'cn|de|en|es|fr|ru', 'misterwong.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Mixx', 'Mixx', 'http://www.mixx.com/submit/story?page_url={u}&amp;title={t}', 'en', 'mixx.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Multiply', 'Multiply', 'http://multiply.com/gus/journal/compose/addthis?body=&amp;url={u}&amp;subject={t}', 'en', 'multiply.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('myLinkVault', 'MyLinkVault', 'http://www.mylinkvault.com/link-page.php?u={u}&amp;n={t}', 'en', 'mylinkvault.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('MySpace', 'MySpace', 'http://www.myspace.com/Modules/PostTo/Pages/?u={u}&amp;t={t}', 'en', 'myspace.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('n4g', 'N4G', 'http://www.n4g.com/tips.aspx?url={u}&amp;title={t}', 'en', 'n4g.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('NetLog', 'NetLog', 'http://www.netlog.com/go/manage/links/view=save&amp;origin=external&amp;url={u}&amp;title={t}', 'en', 'netlog.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Netscape', 'Netscape', 'http://www.netscape.com/submit/?U={u}&amp;T={t}', 'en', 'netscape.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Netvibes', 'Netvibes', 'http://www.netvibes.com/share?url={u}&amp;title={t}', 'en', 'netvibes.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Netvouz', 'Netvouz', 'http://netvouz.com/action/submitBookmark?url={u}&amp;title={t}&amp;popup=no', 'en', 'netvouz.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('NewsTrust', 'NewsTrust', 'http://newstrust.net/submit?url={u}&amp;title={t}&amp;ref=addtoany', 'en', 'newstrust.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Newsvine', 'Newsvine', 'http://www.newsvine.com/_wine/save?u={u}&amp;h={t}', 'en', 'newsvine.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('NowPublic', 'NowPublic', 'http://view.nowpublic.com/?src={u}&amp;t={t}', 'en', 'nowpublic.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('OKNOtizie', 'OK NOtizie Italia', 'http://oknotizie.alice.it/post?url={u}&amp;title={t}', 'it', 'oknotozie.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('OneView', 'OneView', 'http://www.oneview.de/quickadd/neu/addBookmark.jsf?URL={u}&amp;title={t}', 'de', 'oneview.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Ping', 'Ping', 'http://ping.fm/ref/?link={u}&amp;title={t}', 'en', 'ping.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Plaxo', 'Plaxo Pulse', 'http://www.plaxo.com/pulse/?share_link={u}', 'en', 'plaxo_pulse.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Propeller', 'Propeller', 'http://www.propeller.com/submit/?U={u}&amp;T={t}', 'en', 'propeller.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Protopage', 'Protopage', 'http://www.protopage.com/add-button-site?url={u}&amp;label={t}&amp;type=page', 'en', 'protopage.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Pusha', 'Pusha', 'http://www.pusha.se/posta?url={u}', 'en', 'pusha.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Reddit', 'reddit', 'http://reddit.com/submit?url={u}&amp;title={t}', 'en', 'reddit.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Scoopeo', 'Scoopeo', 'http://www.scoopeo.com/scoop/new?newurl={u}&amp;title={t}', 'en', 'scoopeo.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Segnalo', 'Segnalo', 'http://segnalo.alice.it/post.html.php?url={u}&amp;title={t}', 'it', 'segnalo.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('ShoutWire', 'ShoutWire', 'http://www.shoutwire.com/?s={u}', 'en', 'shoutwire.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Simpy', 'Simpy', 'http://www.simpy.com/simpy/LinkAdd.do?href={u}&amp;title={t}', 'en', 'simpy.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Sitejot', 'Sitejot', 'http://www.sitejot.com/addform.php?iSiteAdd={u}&amp;iSiteDes={t}', 'en', 'sitejot.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Slashdot', 'Slashdot', 'http://slashdot.org/bookmark.pl?url={u}&amp;title={t}', 'en', 'slashdot.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('SmaknNews', 'SmakNews', 'http://smaknews.com/submit.php?url={u}&amp;title={t}', 'en', 'smaknews.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Sphinn', 'Sphinn', 'http://sphinn.com/submit.php?url={u}&amp;title={t}', 'en', 'sphinn.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Spurl', 'Spurl', 'http://www.spurl.net/spurl.php?url={u}&amp;title={t}', 'en', 'spurl.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Squidoo', 'Squidoo', 'http://www.squidoo.com/lensmaster/bookmark?{u}&amp;title={t}', 'en', 'squidoo.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('StartAid', 'StartAid', 'http://www.startaid.com/index.php?st=AddBrowserLink&amp;type=Detail&amp;v=3&amp;urlname={u}&amp;urltitle={t}', 'en', 'startaid.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Strands', 'Strands', 'http://www.strands.com/tools/share/webpage?url={u}&amp;title={t}', 'en', 'strands.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Stumbleupon', 'StumbleUpon', 'http://www.stumbleupon.com/submit?url={u}&amp;title={t}', 'en', 'stumbleupon.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Stumpedia', 'Stumpedia', 'http://www.stumpedia.com/submit?url={u}&amp;title={t}', 'en', 'stumpedia.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Tagza', 'Tagza', 'http://www.tagza.com/submit.php?url={u}', 'en', 'tagza.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('TechNet', 'Microsoft TechNet', 'http://social.technet.microsoft.com/en-US/action/Create/s/E/?url={u}&amp;bm=true&amp;ttl={t}', 'en', 'technet.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Technorati', 'Technorati', 'http://www.technorati.com/faves?add={u}', 'en', 'technorati.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Technotizie', 'Technotizie', 'http://www.technotizie.it/posta_ok?action=f2&amp;url={u}&amp;title={t}', 'it', 'technotizie.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Thisnext', 'ThisNext', 'http://www.thisnext.com/pick/new/submit/sociable/?url={u}&amp;name={t}', 'en', 'thisnext.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Tipd', 'Tip\'d', 'http://tipd.com/submit.php?url={u}', 'en', 'tipd.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Tumblr', 'tumblr', 'http://www.tumblr.com/share?v=3&amp;u={u}&amp;t={t}', 'en', 'tumblr.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Twitter', 'TwitThis', 'http://twitthis.com/twit?url={u}', 'en', 'twitthis.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Viadeo', 'Viadeo', 'http://www.viadeo.com/shareit/share/?url={u}&amp;title={t}', 'en', 'viadeo.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Vodpod', 'Vodpod', 'http://vodpod.com/account/add_video_page?p={u}', 'en', 'vodpod.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('WebNews', 'WebNews', 'http://www.webnews.de/einstellen?url={u}&amp;title={t}', 'de', 'webnews.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Wikio', 'Wikio', 'http://www.wikio.com/vote?newurl={u}', 'en', 'wikio.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Windows Live', 'Windows Live Favorites', 'https://favorites.live.com/quickadd.aspx?marklet=1&amp;mkt=en-us&amp;url={u}&amp;title={t}', '', 'windows.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Wists', 'Wists', 'http://wists.com/r.php?r={u}&amp;title={t}', 'en', 'wists.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Xanga', 'Xanga', 'http://www.xanga.com/private/editorx.aspx?u={u}&amp;t={t}', 'en', 'xanga.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Xerpi', 'Xerpi', 'http://www.xerpi.com/block/add_link_from_extension?url={u}&amp;title={t}', 'en', 'xerpi.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Yahoo', 'Yahoo Bookmarks', 'http://bookmarks.yahoo.com/toolbar/savebm?opener=tb&amp;u={u}&amp;t={t}', 'en', 'yahoo.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('YahooBuzz', 'Yahoo Buzz', 'http://buzz.yahoo.com/submit?submitUrl={u}&amp;submitHeadline={t}', 'en', 'yahoobuzz.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Yahoo MyWeb', 'Yahoo MyWeb', 'http://myweb2.search.yahoo.com/myresults/bookmarklet?u={u}&amp;t={t}', 'en', 'yahoomyweb.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Yardbarker', 'Yardbarker', 'http://www.yardbarker.com/author/new/?pUrl={u}', 'en', 'yardbarker.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('Yigg', 'Yigg', 'http://www.yigg.de/neu?exturl={u}&amp;exttitle={t}', 'de', 'yigg.png');
INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('yoolink', 'yoolink', 'http://www.yoolink.fr/post/tag?f=aa&amp;url_value={u}&amp;title={t}', 'fr', 'yoolink.png');
## INSERT IGNORE INTO CPG_plugin_social_bookmarks_services ( `service_name_short`, `service_name_full` , `service_url`, `service_lang`, `icon_filename`) VALUES ('', '', '', '', '');


