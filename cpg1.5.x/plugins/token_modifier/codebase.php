<?php
/**************************************************
  Coppermine 1.5.x Plugin - Form token modifier
  *************************************************
  Copyright (c) 2010 eenemeenemuu
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

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

$thisplugin->add_filter('token_criteria', 'token_modifier_criteria');

function token_modifier_criteria($criteria) {
    global $raw_ip;

    $superCage = Inspekt::makeSuperCage();

    $criteria['ip_addr'] = $raw_ip;
    $criteria['browser'] = $superCage->server->getRaw('HTTP_USER_AGENT');

    return $criteria;
}

?>