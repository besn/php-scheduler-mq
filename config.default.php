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
