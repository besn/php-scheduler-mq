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
 * @author Andi 'besn' Mery
 * @version 0.1
 * @license http://en.wikipedia.org/wiki/Beerware Beerware Licence
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

  // set the working mode
  if(array_key_exists('--feeder', $request))
  {
    Config::set('scheduler.workingmode', 'feeder', true);
    $pidfile = Config::get('scheduler.varpath', '/var/tmp').'/'.Config::get('scheduler.feeder.pidfile', 'scheduler_feeder.pid');
  }
  else if(array_key_exists('--worker', $request))
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

  /* else if(array_key_exists('--quit', $request)) {
    Config::set('scheduler.workingmode', 'quitter', true);
  } else if(array_key_exists('--watch', $request)) {
    Config::set('scheduler.workingmode', 'watchdog', true);
  } else if(array_key_exists('--notify', $request)) {
    Config::set('scheduler.workingmode', 'notify', true);
  } else if(array_key_exists('--cleanup', $request)) {
    Config::set('scheduler.workingmode', 'cleanup', true);
  } */

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
    case 'feeder':
      for($i=0;$i<=10;$i++)
      {
        $arr = array(
          'rand' => rand(0,1000),
        );
        $queue->send(json_encode($arr));
        unset($arr);
      }
      unset($i);
      break;

    case 'worker':
      // define some variables
      $do_loop = true;
      $loops_num = 0;
      $loops_max = Config::get('scheduler.worker.loops.max', 1000);
      $jobs_done = 0;
      $jobs_max = Config::get('scheduler.worker.jobs.max', 200);
      $worker_sleeptime = Config::get('scheduler.worker.sleeptime', 5);

      // the job loop
      while($do_loop != false)
      {
        // increase the loop counter
        $loops_num++;

        // get a job from the message queue
        $message = $queue->receive();
        if($message instanceof AMQPEnvelope)
        {
          syslog(LOG_DEBUG, $message->getBody());
          $worker_sleeptime = Config::get('scheduler.worker.sleeptime', 5);
        }
        else
        {
          sleep(rand(1, 2 + $worker_sleeptime++));
        }
        unset($message);

        // when the maximum number of jobs or loops is reached
        if($jobs_done >= $jobs_max || $loops_num >= $loops_max)
        {
          syslog(LOG_DEBUG, 'finished doing my work. did '.$config['SCHEDULER']['jobsDone'].'/'.$config['SCHEDULER']['workersMaxJobs'].' jobs and '.$config['SCHEDULER']['loopNum'].'/'.$config['SCHEDULER']['workersMaxLoops'].' loops');
          $do_loop = false;
        }
      }

      unset($do_loop, $loops_num, $loops_max, $jobs_done, $jobs_max, $worker_sleeptime);
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
          if(count($output) < Config::get('scheduler.worker.max_workers', 1))
          {
            // Tell the user that we need to start $n workers
            syslog(LOG_DEBUG, 'Need to start '.(Config::get('scheduler.worker.max_workers', 1) - count($output)).' workers');

            // Start the workers
            for($i = count($output); $i < Config::get('scheduler.worker.max_workers', 1); $i++)
            {
              system($_SERVER['_'].' '.$_SERVER['SCRIPT_FILENAME'].' -- --worker >/dev/null 2>&1 &');
            }
          }
        }
      }
      break;
  }

  shutdown();

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
