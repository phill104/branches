/**************************************************
  Coppermine 1.5.x Plugin - picture_navigation
  *************************************************
  Copyright (c) 2010-2012 eenemeenemuu
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

$(document).ready(function() {
    if ($('td.display_media').html().search('table-layout:fixed;') == -1) {
        panorama_viewer_active = false;
        width = '50%';
    } else {
        panorama_viewer_active = true;
        width = '50px';
    }

    if ($('.navmenu_pic img[src*=prev]').parent().attr('href') != 'javascript:;') {
        icon_prev = $('.navmenu_pic img[src*=prev]').parent().html().match(/src="(.*?)"/);
        icon_prev = icon_prev[1];
        icon_prev_inactive = icon_prev.replace('prev', 'prev_inactive');
        btn_prev = '<td onclick="window.location = $(\'.navmenu_pic img[src*=prev]\').parent().attr(\'href\');" onmouseover="$(this).css(\'background-image\', \'url(\' + icon_prev + \')\');" onmouseout="$(this).css(\'background-image\', \'url(\' + icon_prev_inactive + \')\');" style="width: ' + width + '; cursor: pointer; background: center center no-repeat url(' + icon_prev_inactive + '); padding-right: 10px;"></td>';
    } else {
        btn_prev = '<td style="width: ' + width + ';"></td>';
    }
    if ($('.navmenu_pic img[src*=next]').parent().attr('href') != 'javascript:;') {
        icon_next = $('.navmenu_pic img[src*=next]').parent().html().match(/src="(.*?)"/);
        icon_next = icon_next[1];
        icon_next_inactive = icon_next.replace('next', 'next_inactive');
        btn_next = '<td onclick="window.location = $(\'.navmenu_pic img[src*=next]\').parent().attr(\'href\');" onmouseover="$(this).css(\'background-image\', \'url(\' + icon_next + \')\');" onmouseout="$(this).css(\'background-image\', \'url(\' + icon_next_inactive + \')\');" style="width: ' + width + '; cursor: pointer; background: center center no-repeat url(' + icon_next_inactive + '); padding-right: 10px;"></td>';
    } else {
        btn_next = '<td style="width: ' + width + ';"></td>';
    }

    if (panorama_viewer_active) {
        $('td.display_media').html($('td.display_media').html().replace('<tr>', '<tr>' + btn_prev).replace(/(<\/div><\/td><\/tr>[\s\S]*)<\/tr>/, '$1' + btn_next + '</tr>'));
    } else {
        $('td.display_media').html($('td.display_media').html().replace('<tr>', '<tr>' + btn_prev).replace('</tr>', btn_next + '</tr>'));
    }
});