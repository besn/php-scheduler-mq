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

Config::set('app.progname', 'php-scheduler-mq', true);
Config::set('app.author', 'besn', true);
Config::set('app.version.major', 0, true);
Config::set('app.version.minor', 1, true);
Config::set('app.version.complete', Config::get('app.version.major', 0).'.'.Config::get('app.version.minor', 0), true);
Config::set('app.encoding', 'UTF-8');
Config::set('app.debug', false);

Config::set('scheduler.doloop', true);
Config::set('scheduler.maxloops', 1000);
Config::set('scheduler.maxjobs', 1000);
Config::set('scheduler.workersleeptime', 1);

Config::set('memcached.enabled', true);
Config::set('memcached.host', '127.0.0.1');
Config::set('memcached.port', '11211');

Config::set('amqp.enabled', true);
Config::set('amqp.host', '127.0.0.1');
Config::set('amqp.user', 'scheduler');
Config::set('amqp.pass', 'scheduler');
Config::set('amqp.exchange', 'schedulerExchange');
Config::set('amqp.queue', 'schedulerQueue');
Config::get('amqp.routing_key', 'scheduler.key');
