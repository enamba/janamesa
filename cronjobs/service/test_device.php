<?php

/**
 * Send an empty page via fax to check the fax device
 * @author Vincent Priem <priem@lieferando.de>
 * @since 16.05.2012
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));

set_time_limit(0);
define('SLEEP', 5); // delay execution in seconds

// pdf
$pdfFile = APPLICATION_PATH . "/templates/fax/testfax/blank.pdf";
if (!file_exists($pdfFile)) {
    clog('err', 'TESTDEVICE: PDF not found');
    die();
}

$db = Zend_Registry::get('dbAdapter');
$db->setFetchMode(Zend_Db::FETCH_OBJ);
/**
 * Get offfline restaurants with status
 * - 7 defizientes Faxgerät
 * - 14 Inhaberwechsel
 * - 19 Betrieb aufgegeben
 */
$services = $db->fetchAll(
    "SELECT r.* 
    FROM restaurants r
    WHERE r.deleted = 0 
        AND r.status IN (2, 7)
        AND franchiseTypeId <> 2
        AND isOnline = 0"
);

$faxNumbers = array(); // we send only one fax to a number

foreach ($services as $service) {
    try {
        if ($service->notify == "fax") {
            if (empty($service->fax)) {
                clog('err', sprintf("TESTDEVICE: Cannot send fax to #%d, no number provided", $service->id));
                continue;
            }
            
            if (in_array($service->fax, $faxNumbers)) {
                clog('info', sprintf("TESTDEVICE: Ignoring #%d, fax already send to number %s", $service->id, $service->fax));
                continue;
            }

            $faxNumbers[] = $service->fax;

            $fax = new Yourdelivery_Sender_Fax_Retarus();
            if ($fax->send($service->fax, $pdfFile, 'testdevice', $service->id)) {
                clog('info', sprintf("TESTDEVICE: Succesfully send fax to #%d (%s)", $service->id, $service->fax));
            } else {
                clog('err', sprintf("TESTDEVICE: Failed to send fax to #%d (%s)", $service->id, $service->fax));
            }            

            sleep(SLEEP);
        }
        
    } catch (Exception $e) {
        clog('err', sprintf('TESTDEVICE: Failed to send fax to #%d because %s', $service->id, $e->getMessage()));
    }
}
