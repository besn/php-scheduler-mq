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

        // Declare a new exchange
        $this->amqp_exchange = new AMQPExchange($this->amqp_channel);
        $this->amqp_exchange->setName(Config::get('amqp.exchange', 'exchange1'));

        // Create a new queue
        $this->amqp_queue = new AMQPQueue($this->amqp_channel);
        $this->amqp_queue->setName(Config::get('amqp.queue', 'queue1'));
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

  public function send($text)
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

  public function receive()
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
        $amqp_message = $this->amqp_queue->get();
        if(isset($amqp_message) && $amqp_message instanceof AMQPEnvelope)
        {
          // Acknowledge the message
          $this->amqp_queue->ack($amqp_message->getDeliveryTag());
          
          // Return the message
          return $amqp_message;
        }
      }
    }
    return false;
  }
}
