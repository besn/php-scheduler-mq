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
 * Caching Class
 * 
 * @since 0.1
 */
Class Cache
{
  public static $memcached = null;

  public static function init()
  {
    if(Config::get('memcached.enabled', false) == true) {
      if(Config::get('app.debug', false) == true) {
        syslog(LOG_DEBUG, 'Connecting to Memcached ('.Config::get('memcached.host').':'.Config::get('memcached.port').')');
      }
      self::$memcached = new Memcached;
      self::$memcached->addServer(Config::get('memcached.host'), Config::get('memcached.port'));
      if(Config::get('app.debug', false) == true) {
        syslog(LOG_DEBUG, 'Connection to Memcached established.');
      }
    }
  }
}
