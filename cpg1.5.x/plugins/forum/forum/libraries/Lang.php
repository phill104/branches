<?php
/**************************************************
  Coppermine 1.5.x Plugin - forum
  *************************************************
  Copyright (c) 2010 foulu (Le Hoai Phuong), eenemeenemuu
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

class Lang {
    function item($items) {
        static $instance = array();
        $item = explode('.', $items, 2);
        $lg = $item[0];$it = $item[1];
        if (!file_exists(BASE_DIR.'plugins'.DS.'forum'.DS.'forum'.DS.'languages'.DS.Config::item('lang').DS.$lg.'.php')) {
            $lang_file = BASE_DIR.'plugins'.DS.'forum'.DS.'forum'.DS.'languages'.DS.'english'.DS.$lg.'.php';
        } else {
            $lang_file = BASE_DIR.'plugins'.DS.'forum'.DS.'forum'.DS.'languages'.DS.DS.Config::item('lang').DS.$lg.'.php';
        }
        if (!isset($instance[$lg])) {
            include($lang_file);
            $instance[$lg] = $lang;
        }
        if (isset($instance[$lg][$it])) {
            if ($it) {
                return $instance[$lg][$it];
            } else {
                return $instance[$lg];
            }
        } else {
            return $items;
        }
    }
}
