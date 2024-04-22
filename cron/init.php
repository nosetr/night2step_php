<?php

/**
 * init.php
 * Description of init
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 16.01.2013 14:00:50
 * 
 */
$time = microtime(true);
$memory = memory_get_usage();

defined('BASE_PATH')
    || define('BASE_PATH', realpath(dirname(__FILE__) . '/'));
 
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
 
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', 'production');
 
// Ensure library/ is on include_path
set_include_path(APPLICATION_PATH.'/../library');
 
/** Zend_Application */
require_once 'Zend/Application.php';
 
// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();
 
register_shutdown_function('__shutdown');
 
function __shutdown() {
global $time, $memory;
$endTime = microtime(true);
$endMemory = memory_get_usage();
 
echo '
Time [' . ($endTime - $time) . '] Memory [' . number_format(( $endMemory - $memory) / 1024) . 'Kb]';
}