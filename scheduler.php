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

try {

  require_once(dirname(__FILE__) . '/include/include.php');

  $request = array ();
  $tmp = '';
  if ($argc > 0)
  {
    for ($i = 1; $i < $argc; $i++)
    {
      parse_str($argv[$i], $tmp);
      $request = array_merge($request, $tmp);
    }
  }
  unset($tmp, $i);

  // set debug config variable
  Config::set('app.debug', ((array_key_exists('-v', $request) || array_key_exists('--verbose', $request)) ? true : false));

  // show the version
  if(array_key_exists('-V', $request) || array_key_exists('--version', $request))
  {
    echo Config::get('app.progname').' v'.Config::get('app.version.complete').chr(13).chr(10);
    exit(0);
  }

  // show the help screen
  if(array_key_exists('-h', $request) || array_key_exists('--help', $request))
  {
    // print version banner
    echo chr(13).chr(10);
    echo '     '.Config::get('app.progname').' v'.Config::get('app.version.complete').chr(13).chr(10);
    echo chr(13).chr(10);

    // print help message
?>
Usage:

  <?php echo ((isset($_SERVER['_']) && $_SERVER['_'] != '/usr/bin/php') ? $_SERVER['_'] : '/usr/bin/php'); ?> <?php echo $argv[0]; ?> [options]

Options:

  -h , --help
      Prints a brief help message and exits.
  -V, --version
      Prints version information and exits.
  -v, --verbose
      Show more information about whats going on.
<?php
    exit (0);
  }

  // load modules
  if(is_dir(dirname(__FILE__) . '/modules/')) {
    foreach(glob(dirname(__FILE__) . '/modules/*.php' ) as $module_file ) {
      require_once $module_file;
    }
  }

  // set the working mode
  if(array_key_exists('--worker', $request))
  {
    Config::set('scheduler.workingmode', 'worker', true);
    $pidfile = sprintf(Config::get('scheduler.varpath', '/var/tmp').'/'.Config::get('scheduler.worker.pidfile', 'scheduler_worker-%s.pid'), getmypid());
  }
  else if(array_key_exists('--watcher', $request))
  {
    Config::set('scheduler.workingmode', 'watcher', true);
    $pidfile = sprintf(Config::get('scheduler.varpath', '/var/tmp').'/'.Config::get('scheduler.worker.pidfile', 'scheduler_watcher-%s.pid'), getmypid());
  }

  $scheduler->setMode(Config::get('scheduler.workingmode'));

  if(Config::get('app.debug', false) == true)
  {
    syslog(LOG_DEBUG, 'Working Mode: '.$scheduler->getMode());
  }

  // Check if we have a pidfile
  if(isset($pidfile))
  {
    // Check the pidfile
    if(!check_pidfile($pidfile))
    {
      // Remove the old pidfile
      if(file_exists($pidfile))
      {
        syslog(LOG_DEBUG, 'Removing old pidfile.');
        unlink($pidfile);
      }
    }

    // Create the pidfile
    if(!$handle = fopen($pidfile, 'a'))
    {
      syslog(LOG_DEBUG, 'Cannot open pidfile "'.$pidfile.'".');
      exit;
    }
    if(!fwrite($handle, getmypid()))
    {
      syslog(LOG_DEBUG, 'Cannot write my pid to pidfile "'.$pidfile.'".');
      exit;
    }
    fclose($handle);
    unset($handle);
  }

  switch(Config::get('scheduler.workingmode'))
  {
    case 'worker':
      $loopNum = 0;
      $jobsDone = 0;
      $workersSleepTime = Config::get('scheduler.workersleeptime', 1);
      
      while (Config::get('scheduler.doloop', true) == true)
      {
        // get the next message from the queue
        $message = $queue->get();

        // acknowledge the message
        $queue->ack($message);

        // Check if the message is an AMQPEnvelope
        if($message instanceof AMQPEnvelope)
        {
          $message_body = $message->getBody();
          $job = json_decode($message_body, true);
          if(is_array($job))
          {
            switch($job['type'])
            {
              case 'function':
                if(function_exists($job['function_name']))
                {
                  call_user_func($job['function_name'], $job['function_args']);
                }
                else
                {
                  syslog(LOG_ERR, 'unknown job function name: '.$job['function_name']);
                }
                break;

              case 'shell':
                exec($job['command'].' 2>&1', $output, $return_val);
                if($return_val != 0) {
                  syslog(LOG_ERR, 'error running command "'.$job['command'].'"');
                }
                foreach($output as $line) {
                  syslog(LOG_DEBUG, "out: ".$line);
                }
                unset($output, $return_val, $line);
                break;

              case 'action':
                if($action->action_exists($job['action_name']))
                {
                  $action->do_action($job['action_name'], $job['action_args']);
                }
                else
                {
                  syslog(LOG_ERR, 'unknown job action name: '.$job['action_name']);
                }
                break;

              default:
                syslog(LOG_ERR, 'invalid job type: '.$job['type']);
                break;
            }
            $jobsDone++;
          }
          else
          {
            syslog(LOG_ERR, 'invalid job data: '.var_export($message_body, true));
          }
          if(Config::get('app.debug', false) == true)
          {
            syslog(LOG_DEBUG, var_export($message_body, true));
          }
          $workersSleepTime = Config::get('scheduler.workersleeptime', 1);
          unset($message_body, $job);
        }
        else
        {
          sleep(rand(1, 2 + $workersSleepTime++));
        }
        unset($message);
        $loopNum++;
        if($jobsDone >= Config::get('scheduler.maxjobs', 1000) || $loopNum >= Config::get('scheduler.maxloops', 1000))
        {
          syslog(LOG_INFO, 'finished doing my work. did '.$jobsDone.'/'.Config::get('scheduler.maxjobs', 1000).' jobs and '.$loopNum.'/'.Config::get('scheduler.maxloops', 1000).' loops');
          system($_SERVER['_'].' '.$_SERVER['SCRIPT_FILENAME'].' -- --worker >/dev/null 2>&1 &');
          Config::set('scheduler.doloop', false);
        }
      }
      unset($loopNum, $jobsDone, $workersSleepTime);
      break;

    case 'watcher':
      exec('/usr/bin/find '.Config::get('scheduler.varpath', '/var/tmp').' -type f -name '.sprintf(Config::get('scheduler.worker.pidfile', 'scheduler_worker-%s.pid'), '*'), $output, $ret_var);
      if($ret_var == 0)
      {
        if(isset($output) && is_array($output))
        {
          foreach($output as $k => $pidf)
          {
            if(check_pidfile($pidf) == false)
            {
              syslog(LOG_DEBUG, 'Removing stale pidfile '.$pidf);
              unlink($pidf);
              unset($output[$k]);
            }
          }

          // Check if we have enough workers running
          if(count($output) < Config::get('scheduler.maxworkers', 1))
          {
            // Tell the user that we need to start $n workers
            syslog(LOG_DEBUG, 'Need to start '.(Config::get('scheduler.maxworkers', 1) - count($output)).' workers');

            // Start the workers
            for($i = count($output); $i < Config::get('scheduler.maxworkers', 1); $i++)
            {
              system($_SERVER['_'].' '.$_SERVER['SCRIPT_FILENAME'].' -- --worker >/dev/null 2>&1 &');
            }
          }
        }
      }
      break;
  }

  // Check if we have a pidfile
  if(isset($pidfile))
  {
    // Delete the pidfile
    unlink($pidfile);
    unset($pidfile);
  }
}
catch (Exception $ex)
{
  syslog(LOG_ERR, $ex->getMessage());
  syslog(LOG_ERR, $ex->getTraceAsString());
}
