<?php

/**
 * Javascript packager
 * @author vpriem
 * @since 28.01.2011
 */
// Parse only js files
if (pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION) != "js") {
    header("HTTP/1.0 404 Not Found");
    die();
}

header("Content-Type: application/javascript");

// Define some path
defined('PUBLIC_PATH')
        || define('PUBLIC_PATH', realpath(dirname(__FILE__)));
defined('JAVASCRIPT_PATH')
        || define('JAVASCRIPT_PATH', PUBLIC_PATH . "/media/javascript");

// Define application environment
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(PUBLIC_PATH . '/../library'),
            get_include_path(),
        )));

// Define some version
define('JQUERY_VERSION', "1.7");
define('JQUERY_UI_VERSION', "1.8.7");

// Get package name
$package = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_FILENAME);
$package = explode("-", $package);
$package = $package[0];
if (!is_dir(JAVASCRIPT_PATH . "/" . $package)) {
    header("HTTP/1.0 404 Not Found");
    die();
}

// Load config and add domain specific javascript
require_once 'Zend/Config/Ini.php';
$config = new Zend_Config_Ini(PUBLIC_PATH . '/../application/configs/application.ini', APPLICATION_ENV, true);

defined('DOMAIN_BASE')
        || define('DOMAIN_BASE', $config->domain->base);

if (file_exists(PUBLIC_PATH . '/../application/configs/' . DOMAIN_BASE . '.ini')) {
    $configDomain = new Zend_Config_Ini(PUBLIC_PATH . '/../application/configs/' . DOMAIN_BASE . '.ini', APPLICATION_ENV, true);
    $config->merge($configDomain);
    unset($configDomain);
}

/**
 * List files and directories recursively inside the specified path
 * @param string $dir
 * @return array|boolean
 */
function rscandir($dir) {
    $dir = rtrim($dir, "/");
    if ($files = @scandir($dir)) {
        $found = array();
        foreach ($files as $file) {
            if ($file == "." || $file == "..") {
                continue;
            }
            $file = $dir . "/" . $file;
            if (is_dir($file)) {
                $found = array_merge($found, rscandir($file));
            } elseif (substr($file, -3) == ".js") {
                $found[] = $file;
            }
        }
        return $found;
    }
    return false;
}

// Add jquery
$files = array();
$files[] = JAVASCRIPT_PATH . "/library/jquery/" . JQUERY_VERSION . "/jquery.min.js";
$files = array_merge($files, rscandir(JAVASCRIPT_PATH . "/library/jquery/ui/" . JQUERY_UI_VERSION));
$files = array_merge($files, rscandir(JAVASCRIPT_PATH . "/library/jquery/plugins"));
$files = array_merge($files, rscandir(JAVASCRIPT_PATH . "/library/helpers"));
$files[] = JAVASCRIPT_PATH . "/base.js";
$files = array_merge($files, rscandir(JAVASCRIPT_PATH . "/" . $package));

$hostname = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['SERVER_NAME']);
$hostname = str_replace('staging.','', $hostname); //remove staging from url

$domainPaths = array_unique(array(
    sprintf('%s/www.%s', JAVASCRIPT_PATH, DOMAIN_BASE),
    sprintf('%s/%s', JAVASCRIPT_PATH, DOMAIN_BASE),
    sprintf('%s/%s', JAVASCRIPT_PATH, $hostname)
));

foreach ($domainPaths as $domainPath) {
    if (is_dir($domainPath)) {
        $domainFiles = rscandir($domainPath);
        if (is_array($domainFiles)) {
            $files = array_merge($files, $domainFiles);
        }
    }
}

// Compile
$target = PUBLIC_PATH . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$compile = !file_exists($target);
if (!$compile) {
    $filemtime = filemtime($target);
    foreach ($files as $file) {
        if (file_exists($file) && filemtime($file) > $filemtime) {
            $compile = true;
            break;
        }
    }
}

if ($compile || APPLICATION_ENV != 'production') {

    $piwikId = (integer) $config->piwik->id;
    if ($piwikId <= 0) {
        $piwikId = 2; //default is germany
    }

    $f = fopen($target, "w");
    fwrite($f, "/*! Compiled on " . date("Y-m-d H:i:s") . " */");
    if (APPLICATION_ENV == "production") {
        fwrite($f, "\n/* WE ARE IN PRODUCTION MODE */\nvar production = true; \nvar piwikId = " . $piwikId . ";");
    } else {
        fwrite($f, "\n/* WE ARE IN DEVELOPMENT MODE */\nvar production = false; \nvar piwikId = 1;");
    }
    fwrite($f, "\nvar _piwikUrl = '" . $config->piwik->url . "';");

    $currency = "€";
    switch ($config->locale->name) {
        case "de_CH":
            $currency = "SFr.";
            break;
        case "pl_PL":
            $currency = "zł";
            break;
        case "pt_BR":
            $currency = "R$";
            break;
    }
    fwrite($f, "\nvar CURRENCY = '" . $currency . "';");
    fwrite($f, "\nvar LOCALE = '" . (strstr($config->locale->name,"de_")?'de_DE':$config->locale->name) . "';");

    //config for node modules
    if (isset($config->node->orderticker)) {
        fwrite($f, "\nvar ordertickerUrl = '" . $config->node->orderticker->url . "';");
        fwrite($f, "\nvar ordertickerPort = '" . $config->node->orderticker->port . "';");
    }
    
    // facebook
    if (($config->facebook->connect->enabled || $config->facebook->like->enabled) && isset($config->facebook->id)) {
        fwrite($f, "\nvar FbAppId = '" . $config->facebook->id . "';");
    }
    
    foreach ($files as $file) {
        fwrite($f, "\n/*! include " . basename($file) . " */\n" . trim(file_get_contents($file)));
    }
    fclose($f);
}
readfile($target);
