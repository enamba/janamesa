<?php

require_once('lock.php');

// Define start time
define('START_TIME', microtime(true));

// Define default timezone
date_default_timezone_set('Europe/Berlin');

// Define locales
setlocale(LC_ALL, "de_DE.UTF-8");

// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));


//get options and check for given environment
$option = getopt("e:");
$env = "development";
if (isset($option['e'])) {
    switch ($option['e']) {
        default:
            $env = 'development';
            break;

        case 'production':
            $env = 'production';
            break;

        case 'console':
            $env = 'console';
            break;
    }
}

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', $env);
defined('HOSTNAME') || define('HOSTNAME', 'console');
defined('IS_PRODUCTION') || define('IS_PRODUCTION', APPLICATION_ENV == 'production');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH . '/../library'),
            get_include_path(),
        )));

require_once(APPLICATION_PATH . '/functions/functions.php');

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Zend/Config.php';

$configs = array(APPLICATION_PATH . '/configs/application.ini');
$domain_config = APPLICATION_PATH . '/configs/%%DOMAIN_NAME%%.ini';
if (file_exists($domain_config)) {
    $configs[] = $domain_config;
}

// Create application, bootstrap, and run
$application = new Zend_Application(
                APPLICATION_ENV,
                array(
                    'config' => $configs
        ));

$application->bootstrap('locale');
$application->bootstrap('autoload');
$application->bootstrap('constants');
$application->bootstrap('registry');
$application->bootstrap('constants');
$application->bootstrap('logging');
$application->bootstrap('caching');
$application->bootstrap('transport');
$application->bootstrap('view');

$view = Zend_Registry::get('view');

/**
 * Close database at the end of the script
 * @author vpriem
 * @since 01.09.2011
 */
function close_db_on_shutdown() {
    $db = Zend_Registry::get('dbAdapter');
    $db->closeConnection();
}

register_shutdown_function("close_db_on_shutdown");

/**
 * @author mlaug
 * @since 03.05.2011
 * @return boolean
 */
function isBaseUrl() {
    return true;
}

// execution time to unlimit
ini_set('max_execution_time', 900);
ini_set('memory_limit', '2684M');
gc_enable();

function clog($type, $log) {
    $message = 'CRONJOB : ' . $log;
    $logger = Zend_Registry::get('logger');
    switch ($type) {
        default:
        case 'info':
            $logger->info($message);
            break;

        case 'warn':
            $logger->warn($message);
            break;

        case 'debug':
            $logger->debug($message);
            break;

        case 'err':
            $logger->err($message);
            break;

        case 'crit':
            $logger->crit($message);
            break;
    }
}

require_once APPLICATION_PATH . '/functions/errors.php';

function runAt(array $domainsTime) {
    // until we switch crontab, we return true
    return true;
}

$lock = null;

function unlock() {
    global $lock;
    if (is_object($lock)) {
        $lock->unlock();
    }
}

register_shutdown_function('unlock');