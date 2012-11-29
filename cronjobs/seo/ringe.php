<?php

require_once('../base.php');
ini_set('max_execution_time', 0);
$db = Zend_Registry::get('dbAdapter');

if (!file_exists('ftp.csv')) {
    die('CSV file is missing');
}

$template = 'standard';
$config = Zend_Registry::get('configuration');
$host = 'http://www.lieferando.de';
$formater = new Default_View_Helper_Openings_Format();
$cityIds = $db->fetchAll('select id, plz from city');
foreach ($cityIds as $city) {
    $cityId = $city['id'];
    $plz = $city['plz'];
    __con('Processing ' . $cityId);
    $services = Yourdelivery_Model_Servicetype_Restaurant::getByCityId($cityId);

    if (!is_dir('plz/' . $plz)) {
        mkdir('plz/' . $plz);
    }
    
    if (count($services) == 0) {
        $servceHTML = 'Leider sind hier keine Dienstleister verfügbar';
    } else {
        $serviceHTML = "";
        foreach ($services as $service) {

            if (!$service->isOnline()) {
                continue;
            }
            
            $timthumb = sprintf('http://image.yourdelivery.de/lieferando.de/service/%d/%s-155-100.jpg', $service->getId(), urlencode($service->getName()));

            $service->setCurrentPlz($cityId);
            $serviceHTML .= file_get_contents('templates/' . $template . '/service.htm');
            $serviceHTML = str_replace('##IMAGE##', $timthumb, $serviceHTML);
            $serviceHTML = str_replace('##NAME##', $service->getName(), $serviceHTML);
            $serviceHTML = str_replace('##MINAMOUNT##', intToPrice($service->getMinCost($cityId)), $serviceHTML);
            $serviceHTML = str_replace('##OPENING##', $formater->formatOpenings($service->getOpening()->getIntervalOfDay()), $serviceHTML);
            $serviceHTML = str_replace('##DILI##', $host . '/' . $service->getDirectLink(), $serviceHTML);
            $serviceHTML = str_replace('##LOCATION##', $service->getStreet() . ' ' . $service->getHausnr() . ' ' . $service->getPlz() . ' ' . $service->getOrt()->getOrt(), $serviceHTML);
        }
    }

    $page = str_replace('##PARSE_IN_SERVICES##', $serviceHTML, file_get_contents('templates/' . $template . '/template.htm'));
    $page = str_replace('##PLZ##', $cityId, $page);
    $page = str_replace('##CITY##', $db->fetchOne('select city from city where id=' . $cityId), $page);
    $page = str_replace('##COUNT##', count($services), $page);
    $index = 'plz/' . $plz . '/index.html';
    if (file_exists($index)) {
        unlink($index);
    }
    $fp = fopen($index, 'a+');
    fwrite($fp, $page);
    fclose($fp);
}

$csv = fopen('ftp.csv', 'r');
while (($data = fgetcsv($csv, 4096, ";")) !== FALSE) {
    $domain = $data[0];
    $account = $data[1];
    $host = $data[2];
    $user = $data[3];
    $pass = $data[4];
    $title = $data[6];
    $desc = $data[7];
    $keywords = $data[8];
    $impressum = $data[9];

    __con('Processing domain ' . $domain);
    $indexFile = 'index/' . str_replace('.', '_', $domain) . '.html';
    if (file_exists($indexFile)) {
        unlink($indexFile);
    }

    $indexTextFile = 'texts/' . str_replace('.', '_', $domain) . '.txt';
    if (!file_exists($indexTextFile)) {
        __con('No Text found for ' . $domain . ' in ' . $indexTextFile);
        continue;
    }
    $indexText = file_get_contents($indexTextFile);

    $indexPage = file_get_contents('templates/standard/index.html');
    $indexPage = str_replace('##REL##', $impressum, $indexPage);
    $indexPage = str_replace('##TITLE##', $title, $indexPage);
    $indexPage = str_replace('##DESCRIPTION##', $desc, $indexPage);
    $indexPage = str_replace('##KEYWORDS##', $keywords, $indexPage);
    $indexPage = str_replace('##TEXT##', $indexText, $indexPage);
    $fp = fopen($indexFile, 'a+');
    fwrite($fp, $indexPage);
    fclose($fp);
}