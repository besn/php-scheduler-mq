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
 * Prints a Log Message
 *
 * @since 0.1
 */
function printLog($message, $type = '') {
  syslog(LOG_INFO, $message);
}

/**
 * Prints a Debug Message
 * 
 * @since 0.1
 */
function printDebug($message) {
  global $config;
  if($config['DEBUG']) {
    syslog(LOG_DEBUG, $message);
  }
}

/**
 * Prints a Error Message
 * 
 * @since 0.1
 */
function printError($message) {
  syslog(LOG_ERR, $message);
}

function check_pidfile($pidfile, $timeout = 3600)
{
  // Check the status of the pidfile
  if(file_exists($pidfile))
  {
    $pidfilestat = stat($pidfile);
    $pid = file_get_contents($pidfile);
    if(isset($pidfilestat) && is_array($pidfilestat))
    {
      if(!file_exists('/proc/'.$pid.'/')) // || realpath('/proc/'.$pid.'/cwd') != dirname(__FILE__))
      {
        return false;
      }
      if(time() - $pidfilestat['mtime'] < $timeout)
      {
        return true;
      }
    }
    unset($pidfilestat, $pid);
  }

  return false;
}
