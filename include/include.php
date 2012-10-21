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
