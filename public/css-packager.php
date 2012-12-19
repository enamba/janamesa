<?php

header("Content-Type: text/css");
/**
 * Javascript packager
 * @author vpriem
 * @since 28.01.2011
 */
// Parse only js files
if (pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION) != "css") {
    header("HTTP/1.0 404 Not Found");
    die();
}

// Define some path
defined('PUBLIC_PATH')
        || define('PUBLIC_PATH', realpath(dirname(__FILE__)));

// Define application environment
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(PUBLIC_PATH . '/../library'),
            get_include_path(),
        )));

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

$domainPath = sprintf('%s/media/css/www.%s', PUBLIC_PATH, DOMAIN_BASE);
if (is_dir($domainPath)) {
    $cssFiles = glob($domainPath . '/*.css');
    if (count($cssFiles)) {
        // use files under that folder
        defined('CSS_PATH')
                || define('CSS_PATH', $domainPath);
    }
}

// if not yet defined with the domain path, use that one
defined('CSS_PATH')
        || define('CSS_PATH', sprintf('%s/media/css/www.lieferando.de', PUBLIC_PATH));

$package = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_FILENAME);
$package = explode("-", $package);
$compiletype = $package[0];
$package = $package[1];

if ($compiletype == 'satellite') {

// Add css satellite
    $files = array();
    $files[] = PUBLIC_PATH . '/media/css/satellites/yd-satellite-core.css';
    if (strlen($package) > 3) {
        $files[] = sprintf(PUBLIC_PATH . '/media/css/satellites/yd-satellite-%s.css', $package);
    }
    $files[] = PUBLIC_PATH . '/media/css/satellites/yd-satellite-premium.css';
    $files[] = PUBLIC_PATH . '/media/css/satellites/yd-satellite-jquery.css';
    $files[] = PUBLIC_PATH . '/media/css/satellites/yd-satellite-ie.css';
} elseif ($config->domain->base == 'pyszne.pl') {

// Add css frontend
    $files = array();
    $files[] = CSS_PATH . '/yd-frontend-core.css';
    $files[] = CSS_PATH . '/yd-frontend-autocomplete.css';
    if (strlen($package) > 3) {
        $files[] = CSS_PATH . sprintf('/yd-frontend-%s.css', $package);
    }
    $files[] = CSS_PATH . '/yd-frontend-expansions.css';
    $files[] = CSS_PATH . '/yd-frontend-jquery.css';
    $files[] = CSS_PATH . '/yd-frontend-ie.css';
    $files[] = CSS_PATH . '/yd-frontend-hidden.css';
} elseif ($compiletype == 'gelbeseiten' || $compiletype == 'gelbeseiten.staging') {
    // Add css gelbeseiten
    $files = array();
    $files[] = sprintf('%s/media/css', PUBLIC_PATH) . '/gelbeseiten/gelbeseiten-core.css';
    if (strlen($package) > 3) {
        $files[] = sprintf('%s/media/css', PUBLIC_PATH) . sprintf('/gelbeseiten/gelbeseiten-frontend-%s.css', $package);
    }
    $files[] = sprintf('%s/media/css', PUBLIC_PATH) . '/gelbeseiten/gelbeseiten-frontend-expansions.css';
    $files[] = sprintf('%s/media/css', PUBLIC_PATH) . '/gelbeseiten/gelbeseiten-frontend-jquery.css';
    $files[] = sprintf('%s/media/css', PUBLIC_PATH) . '/gelbeseiten/gelbeseiten-frontend-ie.css';
    $files[] = sprintf('%s/media/css', PUBLIC_PATH) . '/gelbeseiten/gelbeseiten-frontend-hidden.css';
    
} else {
    // Add css frontend
    $files = array();
    $files[] = CSS_PATH . '/yd-frontend-core.css';
    if (strlen($package) > 3) {
        $files[] = CSS_PATH . sprintf('/yd-frontend-%s.css', $package);
    }
    $files[] = CSS_PATH . '/yd-frontend-expansions.css';
    $files[] = CSS_PATH . '/yd-frontend-jquery.css';
    $files[] = CSS_PATH . '/yd-frontend-ie.css';
    $files[] = CSS_PATH . '/yd-frontend-hidden.css';
    $files[] = CSS_PATH . '/jnm-banner.css';
    $files[] = CSS_PATH . '/jnm-category.css';
    $files[] = CSS_PATH . '/jnm-district.css';
    $files[] = CSS_PATH . '/jnm-frontend.css';
    $files[] = CSS_PATH . '/jnm-stepbystep.css';
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
    $f = fopen($target, "w");
    fwrite($f, "/* Compiled on " . date("Y-m-d H:i:s") . " */");
    foreach ($files as $file) {
        if (!file_exists($file)) {
            continue;
        }
        fwrite($f, "\n/* include " . basename($file) . " */\n" . trim(file_get_contents($file)));
    }
    fclose($f);
}

/**
 * don't output file content in testcases
 * 
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 03.04.2012  
 */
if (APPLICATION_ENV != 'testing') {
    readfile($target);
}
