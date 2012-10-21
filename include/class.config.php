<?php

/**
 * projectname: php-scheduler-mq
 *
 * PHP version 5.0
 *
 * LICENSE:
 *
 * Copyright (c) 2012 Andreas Mery
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * o Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * o Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * o The names of the authors may not be used to endorse or promote
 *   products derived from this software without specific prior written
 *   permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @package php-scheduler-mq
 * @author  Andi 'besn' Mery <besn@besn.at>
 * @version 0.2
 * @license http://opensource.org/licenses/bsd-license.php New BSD License
 * @link    https://github.com/besn/php-scheduler-mq
 */

/**
 * Configuration Class
 * 
 * @since 0.1
 */
Class Config
{
  /**************************************************************************
      Class Configuration
 
      This section contains configuration data and initialization for the
      class.
  /**************************************************************************/
  private static $config = array();
  private static $config_protected = array();

  public static function set($key, $val, $protected = false)
  {
    if(self::isProtected($key))
    {
      return;
    }
    self::$config[$key] = $val;
    if($protected == true)
    {
      self::$config_protected [$key] = 1;
    }
  }

  /**
   *  get
   *
   *  retrieves an item from configuration arrays and returns the result.
   *
   *  @access public
   *  @param  string  $key     name of the key to retrieve the value of.
   *  @param  string  $default default value in case we dont have a value.
   *  @return mixed            returns a result based on the key requested.
   */
  public static function get($key, $default = "")
  {
    if(self::exists($key))
    {
      return self::$config[$key];
    }
    else if(isset($default))
    {
      return $default;
    }
    else
    {
      throw new Exception("config key $key does not exists");
    }
  }

  public static function exists($key)
  {
    return isset(self::$config[$key]);
  }

  public static function isProtected($key)
  {
    return isset(self::$config_protected[$key]);
  }
}
