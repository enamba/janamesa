<?php

/**
 * common used functions and error reporting
 * @author Daniel Hahn <hahn@lieferando.de>
 */
// Error reporting
ini_set('log_errors', 'On');
ini_set('output_handler', 'ob_gzhandler');
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
if (APPLICATION_ENV == "production") {
    ini_set('error_reporting', E_ALL ^ E_NOTICE);
    ini_set('display_errors', FALSE);
    ini_set('display_startup_errors', FALSE);
    ini_set('error_log', APPLICATION_PATH . sprintf('/logs/phplog-%s.log', date('d-m-Y')));
} elseif (APPLICATION_ENV == "testing") {
    ini_set('error_reporting', E_ALL);
    ini_set('error_log', APPLICATION_PATH . sprintf('/logs/phplog-testing-%s.log', date('d-m-Y')));
} else {
    ini_set('error_reporting', E_ALL);
    ini_set('error_log', APPLICATION_PATH . sprintf('/logs/phplog-devel-%s.log', date('d-m-Y')));
}

/**
 * @author vpriem
 * @since 19.04.2011
 * @return array
 */
function get_error_source() {
    $error = array();
    if (isset($_SERVER['SERVER_NAME'])) {
        $error[] = "Server: " . $_SERVER['SERVER_NAME'];
    }
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $error[] = "User agent: " . $_SERVER['HTTP_USER_AGENT'];
    }
    if (isset($_SERVER['REQUEST_URI'])) {
        $error[] = "Url: " . $_SERVER['REQUEST_URI'];
    }
    if (isset($_SERVER['HTTP_REFERER'])) {
        $error[] = "Referer: " . $_SERVER['HTTP_REFERER'];
    }
    return $error;
}

/**
 * @author vpriem
 * @since 03.11.2010
 */
function handle_fatal_error() {
    $error = error_get_last();
 
    if (is_array($error)) {
        list($errno, $errstr, $errfile, $errline) = array_values($error);  
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_WARNING:
            case E_USER_WARNING:
            case E_STRICT:
                return;

            default:
                $error = array();
                $error[] = get_error_name($errno) . ": " . $errstr . " in " . $errfile . " on line " . $errline;
                $error = array_merge($error, get_error_source());
                $error = implode("\n", $error);
                
                mail("error@lieferando.de", "Yourdelivery Developer: Error", $error, 'From: noreply@lieferando.de');
                if (PHP_SAPI != 'cli') {
                    header('location: /error/throw');
                }
                die();
        }
    }
}

if (IS_PRODUCTION) {
    register_shutdown_function('handle_fatal_error');
}

/**
 * @author vpriem
 * @since 03.11.2010
 * @param integer $errno
 * @return string
 */
function get_error_name($errno) {
    $e = array(
        E_ERROR => "Error",
        E_WARNING => "Warning",
        E_PARSE => "Parse",
        E_NOTICE => "Notice",
        E_CORE_ERROR => "Core Error",
        E_CORE_WARNING => "Core Warning",
        E_COMPILE_ERROR => "Compile Error",
        E_COMPILE_WARNING => "Compile Warning",
        E_USER_ERROR => "User Error",
        E_USER_WARNING => "User Warning",
        E_USER_NOTICE => "User Notice",
        E_STRICT => "Strict",
        E_RECOVERABLE_ERROR => "Recoverable Error",
        E_DEPRECATED => "Deprecated",
        E_USER_DEPRECATED => "User Deprecated",
        E_ALL => "All",
    );
    return $e[$errno];
}

/**
 * @author mlaug
 * @since 26.10.2010
 * @param integer $errno
 * @param string $errstr
 * @param string $errfile
 * @param string $errline
 * @return mixed
 */
function handle_error($errno, $errstr, $errfile, $errline) {
    if (!error_reporting()) { // error reporting is currently turned off with @
        return true;
    }

    $error = array();
    $error[] = get_error_name($errno) . ": " . $errstr . " in " . $errfile . " on line " . $errline;

    $traces = debug_backtrace();
    foreach ($traces as $i => $trace) {
        $error[] = " " . $i . ". " .
                (isset($trace['class']) ? $trace['class'] . $trace['type'] : "") .
                (isset($trace['function']) ? $trace['function'] . "() " : "") .
                (isset($trace['file']) ? $trace['file'] . ":" . $trace['line'] : "");
    }

    $error = array_merge($error, get_error_source());
    $error = implode("\n", $error);

    switch ($errno) {
        case E_NOTICE:
        case E_USER_NOTICE:
        case E_STRICT:
            if (!IS_PRODUCTION) {
                error_log($error);
            }
            return true;

        case E_WARNING:
        case E_USER_WARNING:
            error_log($error);
            return true;

        default:
            error_log($error);
            if (IS_PRODUCTION) {
                Yourdelivery_Sender_Email::error($error, true);
                if (PHP_SAPI != 'cli') {
                    header('location: /error/throw');
                }
                die();
            }
    }
    
    return false;
}

set_error_handler("handle_error");
