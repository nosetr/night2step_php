<?php
$_SERVER["REQUEST_URI"] = str_replace('index.php', '/Error/notfound', $_SERVER["REQUEST_URI"]);
defined('BASE_PATH')
    || define('BASE_PATH', realpath(dirname(__FILE__) . '/'));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
if(!defined('DONT_RUN_APP') || DONT_RUN_APP == false)
    $application->bootstrap()->run();
else
    $application->bootstrap();
