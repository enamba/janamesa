<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$lock = new ExclusiveLock(basename(__FILE__));
if ($lock->lock()) {
    clog('debug', 'Starting to check for fax reports');
    $fax = new Yourdelivery_Sender_Fax();
    $fax->processReports(Yourdelivery_Sender_Fax::INTERFAX);
} else {
    clog("warn", "could not get lock for " . __FILE__);
}