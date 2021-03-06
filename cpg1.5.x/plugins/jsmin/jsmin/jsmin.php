<?php
/**************************************************
  Coppermine 1.5.x Plugin - JSMin
  *************************************************
  Copyright (c) 2010 Timos-Welt (www.timos-welt.de)
  *************************************************
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.
  ********************************************
  $HeadURL: https://coppermine.svn.sourceforge.net/svnroot/coppermine/branches/cpg1.5.x/plugins/jsmin/configuration.php $
  $Revision: 7094 $
  $LastChangedBy: timoswelt $
  $Date: 2010-01-19 14:20:17 +0100 (Di, 19. Jan 2010) $
  **************************************************/

/**
 * This is simply jsmin.php backported to PHP4.
 *
 * jsmin.php is available at:
 * http://code.google.com/p/jsmin-php/
 */

/**
 * jsmin.php - PHP implementation of Douglas Crockford's JSMin.
 *
 * This is pretty much a direct port of jsmin.c to PHP with just a few
 * PHP-specific performance tweaks. Also, whereas jsmin.c reads from stdin and
 * outputs to stdout, this library accepts a string as input and returns another
 * string as output.
 *
 * PHP 5 or higher is required.
 *
 * Permission is hereby granted to use this version of the library under the
 * same terms as jsmin.c, which has the following license:
 *
 * --
 * Copyright (c) 2002 Douglas Crockford  (www.crockford.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 * @package JSMin
 * @author Ryan Grove <ryan@wonko.com>
 * @copyright 2002 Douglas Crockford <douglas@crockford.com> (jsmin.c)
 * @copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @version 1.1.1 (2008-03-02)
 * @link http://code.google.com/p/jsmin-php/
 */

class JSMin {
  var $ORD_LF    = 10;
  var $ORD_SPACE = 32;

  var $a           = '';
  var $b           = '';
  var $input       = '';
  var $inputIndex  = 0;
  var $inputLength = 0;
  var $lookAhead   = null;
  var $output      = '';

  // -- Public Static Methods --------------------------------------------------

  function minify($js) {
    $jsmin = new JSMin($js);
    $output = $jsmin->min();
    if ($output === FALSE) {
      return $js;
    }
    return $output;
  }

  // -- Public Instance Methods ------------------------------------------------

  function JSMin($input) {
    $this->input       = str_replace("\r\n", "\n", $input);
    $this->inputLength = strlen($this->input);
  }

  // -- Protected Instance Methods ---------------------------------------------

  function action($d) {
    switch($d) {
      case 1:
        $this->output .= $this->a;

      case 2:
        $this->a = $this->b;

        if ($this->a === "'" || $this->a === '"') {
          for (;;) {
            $this->output .= $this->a;
            $this->a       = $this->get();

            if ($this->a === $this->b) {
              break;
            }

            if (ord($this->a) <= $this->ORD_LF) {
              // throw new JSMinException('Unterminated string literal.');
              return FALSE;
            }

            if ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            }
          }
        }

      case 3:
        $this->b = $this->next();
        if ($this->b === FALSE) {
          return FALSE;
        }

        if ($this->b === '/' && (
            $this->a === '(' || $this->a === ',' || $this->a === '=' ||
            $this->a === ':' || $this->a === '[' || $this->a === '!' ||
            $this->a === '&' || $this->a === '|' || $this->a === '?')) {

          $this->output .= $this->a . $this->b;

          for (;;) {
            $this->a = $this->get();

            if ($this->a === '/') {
              break;
            } elseif ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            } elseif (ord($this->a) <= $this->ORD_LF) {
              // throw new JSMinException('Unterminated regular expression '.
              //     'literal.');
              return FALSE;
            }

            $this->output .= $this->a;
          }

          $this->b = $this->next();
          if ($this->b === FALSE) {
            return FALSE;
          }
        }
    }
  }

  function get() {
    $c = $this->lookAhead;
    $this->lookAhead = null;

    if ($c === null) {
      if ($this->inputIndex < $this->inputLength) {
        $c = $this->input[$this->inputIndex];
        $this->inputIndex += 1;
      } else {
        $c = null;
      }
    }

    if ($c === "\r") {
      return "\n";
    }

    if ($c === null || $c === "\n" || ord($c) >= $this->ORD_SPACE) {
      return $c;
    }

    return ' ';
  }

  function isAlphaNum($c) {
    return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
  }

  function min() {
    $this->a = "\n";
    if ($this->action(3) === FALSE) {
      return FALSE;
    }

    while ($this->a !== null) {
      switch ($this->a) {
        case ' ':
          if ($this->isAlphaNum($this->b)) {
            if ($this->action(1) === FALSE) {
              return FALSE;
            }
          } else {
            if ($this->action(2) === FALSE) {
              return FALSE;
            }
          }
          break;

        case "\n":
          switch ($this->b) {
            case '{':
            case '[':
            case '(':
            case '+':
            case '-':
              if ($this->action(1) === FALSE) {
                return FALSE;
              }
              break;

            case ' ':
              if ($this->action(3) === FALSE) {
                return FALSE;
              }
              break;

            default:
              if ($this->isAlphaNum($this->b)) {
                if ($this->action(1) === FALSE) {
                  return FALSE;
                }
              }
              else {
                if ($this->action(2) === FALSE) {
                  return FALSE;
                }
              }
          }
          break;

        default:
          switch ($this->b) {
            case ' ':
              if ($this->isAlphaNum($this->a)) {
                if ($this->action(1) === FALSE) {
                  return FALSE;
                }
                break;
              }

              if ($this->action(3) === FALSE) {
                return FALSE;
              }
              break;

            case "\n":
              switch ($this->a) {
                case '}':
                case ']':
                case ')':
                case '+':
                case '-':
                case '"':
                case "'":
                  if ($this->action(1) === FALSE) {
                    return FALSE;
                  }
                  break;

                default:
                  if ($this->isAlphaNum($this->a)) {
                    if ($this->action(1) === FALSE) {
                      return FALSE;
                    }
                  }
                  else {
                    if ($this->action(3) === FALSE) {
                      return FALSE;
                    }
                  }
              }
              break;

            default:
              if ($this->action(1) === FALSE) {
                return FALSE;
              }
              break;
          }
      }
    }

    return $this->output;
  }

  function next() {
    $c = $this->get();

    if ($c === '/') {
      switch($this->peek()) {
        case '/':
          for (;;) {
            $c = $this->get();

            if (ord($c) <= $this->ORD_LF) {
              return $c;
            }
          }

        case '*':
          $this->get();

          for (;;) {
            switch($this->get()) {
              case '*':
                if ($this->peek() === '/') {
                  $this->get();
                  return ' ';
                }
                break;

              case null:
                // throw new JSMinException('Unterminated comment.');
                return FALSE;
            }
          }

        default:
          return $c;
      }
    }

    return $c;
  }

  function peek() {
    $this->lookAhead = $this->get();
    return $this->lookAhead;
  }
}
