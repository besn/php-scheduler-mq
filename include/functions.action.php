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
function add_action($action_name, $function_to_call, $priority = 10) {
	global $config;
	$config['ACTIONS'][$action_name][$priority][] = $function_to_call;
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
	global $config;

  $args = array();
  if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) // array(&$this)
    $args[] =& $arg[0];
  else
    $args[] = $arg;
  for ( $a = 2; $a < func_num_args(); $a++ )
    $args[] = func_get_arg($a);

	if(isset($config['ACTIONS'][$action_name]) && is_array($config['ACTIONS'][$action_name])) {
	  foreach($config['ACTIONS'][$action_name] as $priority => $functions) {
	    foreach($functions as $k => $function) {
	      if(function_exists($function)) {
				  call_user_func($function, $args);
			  }
	    }
	  }
	}
}

function action_exists($action_name) {
  global $config;
  return isset($config['ACTIONS'][$action_name]);
}
