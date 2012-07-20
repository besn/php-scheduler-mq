<?php

/**
 * projectname: php-scheduler-mq
 *
 * PHP version 5.0
 *  
 * LICENSE: "THE BEER-WARE LICENSE"
 * 
 * Andi 'besn' Mery wrote this crap ;) As long as you retain this notice you
 * can do whatever (teh hell) you want with this stuff. If we meet some day, 
 * and you think this stuff is worth it, you can buy me a beer in return.
 * ----------------------------------------------------------------------------
 *
 * @package php-scheduler-mq
 * @version 0.1
 * @license http://en.wikipedia.org/wiki/Beerware Beerware Licence
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
