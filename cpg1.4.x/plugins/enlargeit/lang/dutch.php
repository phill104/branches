<?php
/**************************************************
  Coppermine 1.4.x Plugin - EnlargeIt! $VERSION$=2.15
  *************************************************
  Copyright (c) 2008 Timos-Welt (www.timos-welt.de)
  *************************************************
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.
  ***************************************************/

if (!defined('IN_COPPERMINE')) { die('Not in Coppermine...'); }
//language variables
$lang_enlargeit = array(
'display_name' => 'EnlargeIt! PlugIn',
'update_success' => 'Waarden zijn succesvol opgeslagen',
'main_title' => 'EnlargeIt! PlugIn',
'version' => 'v2.15',
'pluginmanager' => 'Plugin Manager',
'enl_yes' => 'ja',
'enl_no' => 'nee',
'submit_button' => 'Opslaan',
'enl_pictype' => 'Vergroten naar',
'enl_normalsize' => 'Half formaat',
'enl_fullsize' => 'Volledig formaat',
'enl_forcenormal' => 'forceer half formaat',
'enl_ani' => 'Animatie',
'noani' => 'geen',
'fade' => 'vaag in/uit',
'glide' => 'glijden',
'bumpglide' => 'Hard glijden',
'smoothglide' => 'Zacht glijden',
'expglide' => 'hard glide',
'enl_speed' => 'Tijd tussen animatie stappen',
'enl_maxstep' => 'Animatie stappen',
'enl_brd' => 'Gebruik een rand',
'enl_brdsize' => 'Rand dikte',
'enl_brdbck' => 'Rand textur',
'enl_brdcolor' => 'Rand kleur',
'enl_brdround' => 'Ronde rand (alleen in Mozilla/Safari)',
'enl_shadow' => 'Gebruik rand schaduw',
'enl_shadowsize' => 'Schaduw grootte (rechts/onder)',
'enl_shadowcolor' => 'Shadow color (usually black)',
'enl_shadowintens' => 'Schaduw dikte',
'enl_titlebar' => 'Laat titelbalk zien als geen knoppen actief zijn',
'enl_titletxtcol' => 'Titelbalk kleur',
'enl_ajaxcolor' => 'Achtergrond kleur AJAX gedeelte',
'enl_center' => 'Centreer vergrote afbeelding',
'enl_dark' => 'Verduister scherm tijdens vergroting (1 afbeelding per keer)',
'enl_darkprct' => 'Verduistering sterkte',
'enl_buttonpic' => 'Laat knop zien \'Laat afbeelding zien\'',
'enl_tooltippic' => 'Laat afbeelding zien',
'enl_buttoninfo' => 'Laat knop zien \'Info\'',
'enl_buttoninfoyes1' => 'ja (open AJAX info pagina)',
'enl_buttoninfoyes2' => 'ja (open half formaat pagina)',
'enl_tooltipinfo' => 'Laat info zien',
'enl_buttonfav' => 'Laat knop zien \'Favorieten\'',
'enl_tooltipfav' => 'Favorieten',
'enl_buttoncomment' => 'Laat knop zien \'Commentaar\'',
'enl_tooltipcomment' => 'Commentaar',
'enl_buttondownload' => 'Show button \'Download\'',
'enl_tooltipdownload' => 'Download this file',
'enl_clickdownload' => 'Click here to download this file',
'enl_buttonecard' => 'Show button \'E-Card\'',
'enl_tooltipecard' => 'E-Card',
'enl_buttonhist' => 'Laat knop zien \'Historie\'',
'enl_tooltiphist' => 'Historie',
'enl_buttonvote' => 'Laat knop zien \'Stem\'',
'enl_tooltipvote' => 'Stem',
'enl_buttonmax' => 'Laat knop zien \'Maximaliseer\'',
'enl_tooltipmax' => 'Maximaliseer',
'enl_maxforreg' => 'Ja, maar niet voor anonieme bezoekers',
'enl_maxpopup' => 'ja, als pop-up scherm',
'enl_maxpopupforreg' => 'ja, als pop-up maar niet voor anonieme bezoekers',
'enl_buttonclose' => 'Laat knop zien \'Sluiten\'',
'enl_tooltipclose' => 'Sluiten',
'enl_buttonnav' => 'Laat knoppen zien \'Navigatie\'',
'enl_tooltipprev' => 'Vorige afbeelding',
'enl_tooltipnext' => 'Volgende afbeelding',
'enl_adminmode' => 'EnlargeIt! actief wanneer in admin mode',
'enl_registeredmode' => 'EnlargeIt! actief voor geregistreerde gebruikers',
'enl_guestmode' => 'EnlargeIt! actief voor gasten',
'enl_sefmode' => 'SEF support aan',
'enl_addedtofav' => 'De afbeelding is toegevoegd aan je favorieten.',
'enl_removedfromfav' => 'De afbeelding is verwijderd uit je favorieten.',
'enl_showfav' => 'Laat mijn favorieten zien',
'enl_dragdrop' => 'Sleep & Plaats voor vergrote afbeeldingen',
'enl_darkensteps' => 'Verduistering stappen (1 = onmiddelijk)',
'enl_cantcomment' => 'There are no comments yet, and you are not allowed to add one!',
'enl_cantecard' => 'Sorry, you are not allowed to send eCards!',
'enl_wheelnav' => 'Mouse wheel navigation',
'enl_canceltext' => 'Click to cancel loading of this picture',
'enl_noflashfound' => 'To watch this file, you need the browser plugin Adobe Flash Player!',
'enl_flvplayer' => 'Use which FLV player software',
'enl_buttonbbcode' => 'Show button \'BBCode\'',
'enl_tooltipbbcode' => 'BBCode',
'enl_copytoclipbrd' => 'Copy to clipboard',
'enl_opaglide' => 'Transparency effect for glide animation',
'enl_mousecursors' => 'Use hourglass mouse cursors if browser supports it',
);
?>