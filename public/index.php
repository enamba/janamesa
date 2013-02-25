<?php

/**
 * Yourdelivery Freaking Shop System
 * Copyright 2010
 * Yourdelivery GmbH
 * Matthias Laug
 */
// Define start time
define('START_TIME', microtime(true));

// Define default timezone
date_default_timezone_set('America/Sao_Paulo');

// Define locales
setlocale(LC_ALL, "pt_BR.UTF-8");

// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

defined('IS_PRODUCTION')
        || define('IS_PRODUCTION', APPLICATION_ENV == 'production');

// Define hostname
defined('HOSTNAME')
        || define('HOSTNAME', (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['SERVER_NAME']));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH . '/../library'),
            get_include_path(),
        )));

require_once APPLICATION_PATH . '/functions/errors.php';
require_once APPLICATION_PATH . '/functions/functions.php';
require_once APPLICATION_PATH . '/functions/grid.function.php';

/**
 * @author mlaug
 */
function isIndex($checkForParameters = true) {
    $uri = @parse_url($_SERVER['REQUEST_URI']);
    if (is_array($uri) && $uri['path'] == '/') {
        if (strstr(HOSTNAME, ".janamesa")) {
            if ($checkForParameters && count($_GET) == 0 && count($_POST) == 0) {
                return true;
            } elseif ($checkForParameters === false) {
                return true;
            }
        }
    }
    return false;
}

/**
 * @author mlaug
 * @since 03.05.2011
 * @return boolean
 */
function isBaseUrl() {
    
    if (HOSTNAME == 'janamesa.com.br' || 
            HOSTNAME == 'www.janamesa.com.br' ||
            HOSTNAME == 'www.janamesa.com' ||
            HOSTNAME == 'janamesa.com'
            ){
        return true;
    } else {
        return false;
    }
    
    if ( strstr(HOSTNAME,'gelbeseiten') ){
        return false;
    }
    
    $config = Zend_Registry::get('configuration');
    $domain = $config->domain->base;
    if ($config->domain->www_redirect->enabled == 1) {
        return strpos(HOSTNAME, $domain) || strpos(HOSTNAME, '.yourdelivery');
    } else {
        return strstr(HOSTNAME, $domain) || strstr(HOSTNAME, 'yourdelivery');
    }
}

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Zend/Config.php';

$configs = array(APPLICATION_PATH . '/configs/application.ini');
$domain_config = APPLICATION_PATH . '/configs/janamesa.com.br.ini';
if (file_exists($domain_config)) {
    $configs[] = $domain_config;
}

// Create application, bootstrap, and run
$application = new Zend_Application(
                APPLICATION_ENV,
                array(
                    'config' => $configs
        ));

//make sure, those are called first
$application->bootstrap('registry');
$application->bootstrap('constants');
$application->bootstrap('autoload');
$application->bootstrap('session');
$application->bootstrap()->run();