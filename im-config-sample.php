<?php
/**
 * File:
 *    im-config-sample.php
 * 
 * Purpose:
 *    sample config file
 *    to deploy: rename to im-config.php 
 * 
 */

 
/*
 * explicitly set the error reporting
 *    commente out to use default
 */
error_reporting(E_ALL);

/*
 * include directory
 *    can be relative or absolute
 *    do not use trailing slash
 */
$cfgIM['includedir'] = 'im-include';

/*
 * display verbose informational messages while parsing
 */
$cfgIM['verbose_parse'] = false;

/*
 * display parsing exceptions
 */
$cfgIM['display_exceptions'] = true;
 
/*
 * mongodb connection settings
 *    modify as needed
 */
$cfgIM['mongohost'] = 'localhost';
$cfgIM['mongoport'] = '27017'; 


/*
 * event callback script directory array
 *    one directory per array member
 *    will call each event once per directory
 *    will expect all event files in directory
 *       if you want the event to do 'nothing' just have a blank php file
 */
$cfgIMEventDir[] = 'im-event-vardump'; 



/*
 * 
 * end of configuration
 *    no need to edit beyond this point
 * 
 */








/*
 * commit configuration into constants
 */
foreach ($cfgIM as $key => $value) {
   if (!defined("IM_".$key)) {
      define("IM_".$key,$value);
   }
} 




