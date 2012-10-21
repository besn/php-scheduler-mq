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
 * Queuing Class
 *
 * @since 0.1
 */
Class Queue
{
  private $amqp_connection = null;
  private $amqp_channel = null;
  private $amqp_exchange = null;
  private $amqp_queue = null;

  public function connect()
  {
    if(Config::get('amqp.enabled', false) == true)
    {
      if(Config::get('app.debug', false) == true) {
        syslog(LOG_DEBUG, 'Connecting to AMQP broker ('.Config::get('amqp.host').')');
      }

      // connect to the amqp server
      $this->amqp_connection = new AMQPConnection();
      $this->amqp_connection->setHost(Config::get('amqp.host', '127.0.0.1'));
      $this->amqp_connection->setVhost(Config::get('amqp.vhost', '/'));
      if(Config::exists('amqp.user'))
      {
        $this->amqp_connection->setLogin(Config::get('amqp.user'));
      }
      if(Config::exists('amqp.pass'))
      {
        $this->amqp_connection->setPassword(Config::get('amqp.pass'));
      }
      $this->amqp_connection->connect();
      if($this->amqp_connection->isConnected())
      {
        if(Config::get('app.debug', false) == true) {
          syslog(LOG_DEBUG, 'Connection to AMQP broker established.');
        }

        // Create a channel
        $this->amqp_channel = new AMQPChannel($this->amqp_connection);

        // Create a new exchange
        $this->amqp_exchange = new AMQPExchange($this->amqp_channel);
        $this->amqp_exchange->setName(Config::get('amqp.exchange', 'exchange1'));
        $this->amqp_exchange->setType(AMQP_EX_TYPE_TOPIC);
        $this->amqp_exchange->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
        $this->amqp_exchange->declare();

        // Create a new queue
        $this->amqp_queue = new AMQPQueue($this->amqp_channel);
        $this->amqp_queue->setName(Config::get('amqp.queue', 'queue1'));
        $this->amqp_queue->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
        $this->amqp_queue->bind(Config::get('amqp.exchange', 'exchange1'), Config::get('amqp.routing_key', 'routing.key'));
        $this->amqp_queue->declare();
      }
    }
  }

  public function disconnect()
  {
    if(Config::get('amqp.enabled', false) == true)
    {
      if($this->amqp_connection->isConnected())
      {
        if(Config::get('app.debug', false) == true) {
          syslog(LOG_DEBUG, 'Disconnecting from AMQP broker.');
        }

        // Disconnect from the broker
        $this->amqp_connection->disconnect();
      }
    }
  }

  public function add($text)
  {
    if(Config::get('amqp.enabled', false) == true)
    {
      if(!isset($this->amqp_connection) || !$this->amqp_connection->isConnected())
      {
        $this->connect();
      }
      if($this->amqp_connection->isConnected())
      {
        // Start a transaction
        $this->amqp_channel->startTransaction();

        // Publish the message
        $this->amqp_exchange->publish($text, Config::get('amqp.routing_key', 'routing.key'));

        // Commit the transaction
        $this->amqp_channel->commitTransaction();
      }
    }
  }

  public function get()
  {
    if(Config::get('amqp.enabled', false) == true)
    {
      if(!isset($this->amqp_connection) || !$this->amqp_connection->isConnected())
      {
        $this->connect();
      }
      if($this->amqp_connection->isConnected())
      {
        // Get a message from the queue
        $amqp_message = $this->amqp_queue->get(AMQP_NOPARAM);
        if(isset($amqp_message) && $amqp_message instanceof AMQPEnvelope)
        {
          // Return the message
          return $amqp_message;
        }
      }
    }
    return false;
  }

  public function ack($amqp_message)
  {
    if(Config::get('amqp.enabled', false) == true)
    {
      if(!isset($this->amqp_connection) || !$this->amqp_connection->isConnected())
      {
        $this->connect();
      }
      if($this->amqp_connection->isConnected())
      {
        if(isset($amqp_message) && $amqp_message instanceof AMQPEnvelope)
        {
          // Acknowledge the message
          $this->amqp_queue->ack($amqp_message->getDeliveryTag());
        }
      }
    }
    return false;
  }
}
