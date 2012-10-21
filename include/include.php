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

// load the config class
require_once(dirname(__FILE__) . '/class.config.php');
// load the config defaults
require_once(dirname(__FILE__) . '/../config.default.php');
// load the user config
require_once(dirname(__FILE__) . '/../config.php');

// load the cache class
require_once(dirname(__FILE__) . '/class.cache.php');

// load the queue class
require_once(dirname(__FILE__) . '/class.queue.php');
$queue = new Queue();

// load the scheduler class
require_once(dirname(__FILE__) . '/class.scheduler.php');
$scheduler = new Scheduler();

// load the functions
require_once(dirname(__FILE__) . '/functions.php');
require_once(dirname(__FILE__) . '/functions.action.php');

// set the error level
error_reporting( ((Config::get('app.debug', false) == true) ? E_ALL :  E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING ) );

// open a connection to syslog
openlog(Config::get('app.progname'), LOG_PID | LOG_PERROR, LOG_LOCAL0);

// set the encoding
if(function_exists('mb_internal_encoding')) {
  mb_internal_encoding(Config::get('app.encoding', 'UTF-8'));
}

// set the timezone
date_default_timezone_set(Config::get('app.timezone', 'Europe/Vienna'));

// initialize the cache
Cache::init();
