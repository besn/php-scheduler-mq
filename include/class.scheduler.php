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
 * Scheduler Class
 * 
 * @since 0.1
 */
Class Scheduler
{
  private $scheduler_mode;

  public function getMode()
  {
    return $this->scheduler_mode;
  }

  public function setMode($mode)
  {
    $this->scheduler_mode = $mode;
  }
}
