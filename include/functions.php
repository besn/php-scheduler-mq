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
