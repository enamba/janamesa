<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * Creates the sitemap
 * @author vpriem
 * @since 21.01.2011
 */
$config = Zend_Registry::get('configuration');

$hostname = $config->domain->base;
if ($config->domain->www_redirect->enabled) {
    $hostname = 'www.' . $hostname;
}

clog('info', sprintf('SEO: update sitemap for %s', $hostname));

$file = APPLICATION_PATH . "/../storage/public/sitemap-" . $hostname . ".xml";
if (!is_dir(dirname($file))) {
    mkdir(dirname($file));
}
if (file_exists($file)) {
    unlink($file);
}
$sitemap = new Default_Writer_Sitemap($file);
$sitemap->add("http://" . $hostname . "/");

// add all services
$dbTable = new Yourdelivery_Model_DbTable_Restaurant();
$services = $dbTable->getAllDirectLinks();
foreach ($services as $service) {

    if ($service['bloomsburys'] == 1 && $hostname == 'www.eat-star.de') {
        continue;
    }
    
    if ($service['metaRobots'] != 'index,follow'){
        continue;
    }

    if (!empty($service['restUrl'])) {
        $sitemap->add("http://" . $hostname . "/" . $service['restUrl'], null, "weekly", "0.5");
    }
}

// add all internal links
$dbTable = new Yourdelivery_Model_DbTable_Link();
$links = $dbTable->getAll();
foreach ($links as $link) {
    if ($link->domain != $hostname || $link->robots == "none") {
        continue;
    }
    $sitemap->add("http://" . $link->getAbsoluteUrl());
}

// save the sitemap
$sitemap->save();