<?php

/**
 * Checks the logs for errors and warnings
 * @author borisIako
 * @since 23.05.2011 
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));

$config = Zend_Registry::get("configuration");

$domain = str_replace(".", "-", $config->domain->base);
$file_name = $domain . '-' . date("d-m-Y", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))) . '.log';

$file = realpath(dirname(__FILE__)).'/../../application/logs/' . $file_name;
$handler = fopen($file, 'r');

if (!$handler) {
    $email = new Yourdelivery_Sender_Email();
    $email->setSubject('Could not open log file!');
    $email->setBodyText('Error opening log file (' . $file . ')');
    $email->addTo('it@lieferando.de');
    $email->send('system');
    exit();
}

$message = '';
$arr = array("ERR", "WARN", "CRIT");

while (!feof($handler)) {
    $line = fgets($handler);
    foreach ($arr as $elem) {
        if (strpos($line, $elem)) {
            $message .= $line;
        }
    }
}


$email = new Yourdelivery_Sender_Email();
$email->setSubject('Log errors');
$email->setBodyText($message);
$email->addTo('it@lieferando.de');

if ($email->send('system')) {
    clog('info', 'Email was send');
} else {
    clog('err', 'Sending Email failed');
}

fclose($handler);
