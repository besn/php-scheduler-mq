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
 * Action Class
 *
 * @since 0.2
 */
Class Action
{
  private $actions;

  /**
   * Hooks a function on to a specific action.
   *
   * Inspired by Wordpress' add_action() function.
   *
   * @author besn
   * @since 0.1
   *
   * @param string $tag The name of the action to which the $function_to_add is hooked.
   * @param callback $function_to_call The name of the function you wish to be called.
   * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
   */
  function add($action_name, $function_to_call, $priority = 10) {
    $this->actions[$action_name][$priority][] = $function_to_call;
    return true;
  }

  /**
   * Execute functions hooked on a specific action hook.
   *
   * Inspired by Wordpress' do_action() function.
   *
   * @author besn
   * @since 0.1
   *
   * @param string $tag The name of the action to be executed.
   * @return null
   */
  function do_action($action_name, $arg = '') {
    $args = array();
    if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) // array(&$this)
      $args[] =& $arg[0];
    else
      $args[] = $arg;
    for ( $a = 2; $a < func_num_args(); $a++ )
      $args[] = func_get_arg($a);

    if(isset($this->actions[$action_name]) && is_array($this->actions[$action_name])) {
      foreach($this->actions[$action_name] as $priority => $functions) {
        foreach($functions as $k => $function) {
          if(function_exists($function)) {
            call_user_func($function, $args);
          }
        }
      }
    }
  }

  function action_exists($action_name) {
    return isset($this->actions[$action_name]);
  }
}
