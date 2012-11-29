<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

set_time_limit(0);
ini_set('memory_limit', '4096M');

$lock = new ExclusiveLock(basename(__FILE__));
if ($lock->lock()) {

    $jobs = Yourdelivery_Model_DbTable_Rabatt_Jobs::getJobs();

    foreach ($jobs as $job) {

        $rabattId = (integer) $job['rabattId']; // rabatt aktion id
        $codeNumber = (integer) $job['count']; // numbers of code to be generated

        try {
            $discount = new Yourdelivery_Model_Rabatt($rabattId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            die("Rabatt id " . $rabattId . " not found." . LF);
        }

        if ($discount->getType() == Yourdelivery_Model_Rabatt::TYPE_VERIFICATION_SINGLE) {
            clog('info', sprintf('DISCOUNT JOB: #%d is of type 3, there should not be a job for this case', $codeNumber, $rabattId));
            Yourdelivery_Model_DbTable_Rabatt_jobs::finishJob($rabattId, -1);
            continue;
        }

        //send out email, to inform user, that this job just started
        clog('info', sprintf('DISCOUNT JOB: starting to generate %d codes for #%d', $codeNumber, $rabattId));
        Yourdelivery_Sender_Email::quickSend(sprintf('Starte Discount Job: %d Rabattcodes für Aktion #%d (%s) werden generiert', $codeNumber, $rabattId, $discount->getName()), 'started', null, $job['email'], 'it@lieferando.de');

        $discount->generateCodes($codeNumber);
        try {
            $codeFile = $discount->getZipFile();

            if (!is_file($codeFile)) {
                clog("err", "DISCOUNT JOB: File could not be created!");
            }

            //send out email, to inform user, that this job has just been finished
            clog('info', sprintf('DISCOUNT JOB: finished to generate %d codes for #%d, sending out email to %s', $codeNumber, $rabattId, $job['email']));
            Yourdelivery_Sender_Email::quickSend(sprintf('%d Rabattcodes für Aktion #%d (%s) wurden generiert', $codeNumber, $rabattId, $discount->getName()), 'finished', $codeFile, $job['email'], 'it@lieferando.de');

            Yourdelivery_Model_DbTable_Rabatt_jobs::finishJob($rabattId);
        } catch (Yourdelivery_Exception_FileWrite $e) {
            clog("err", $e->getMessage());
            Yourdelivery_Sender_Email::quickSend(sprintf('%d Rabattcodes für Aktion #%d wurden generiert, Versand fehlgeschlagen: %s', $codeNumber, $rabattId, $e->getMessage()), 'failed', null,  'it@lieferando.de');
        }
    }
} else {
    clog("warn", "could not get lock for " . __FILE__);
}
