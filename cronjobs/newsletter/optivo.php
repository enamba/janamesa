<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 02.09.2011
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));
gc_enable();
$memLimit = 6096;

ini_set('memory_limit', $memLimit . 'M');
ini_set('max_execution_time', 0);


$db = Zend_Registry::get('dbAdapterReadOnly');
$config = Zend_Registry::get('configuration');

################################################################################
clog('info', sprintf('OPTIVO EXPORT - %s: starting blacklist export', $config->domain->base));
$select = $db->select()->from(array('nr' => 'newsletter_recipients'), 'email')->where('status = 0')->orWhere('affirmed = 0');
$nr_rows = $db->fetchAll($select);

$select_bv = $db->select()->from(array('bv' => 'blacklist_values'),'value')->where("bv.type = 'email'")->where('bv.deleted = 0');

$bv_rows = $db->fetchAll($select_bv);

$rows = array_merge($nr_rows, $bv_rows);

$csv = new Default_Exporter_Csv();
$csv->addCol('dummy@dummmy.ru');
foreach ($rows as $row) {
    $csv->addRow($row);
}
$file = $csv->save();
clog('info', sprintf('OPTIVO EXPORT - %s: finally saved csv with name "%s"', $config->domain->base, $file));

$publicKey = APPLICATION_PATH . '/templates/keys/id_rsa.pub';
$privateKey = APPLICATION_PATH . '/templates/keys/id_rsa';
$server = 'r_yourdelivery@ftpapi.broadmail.de:newsletter/';

if (!chmod($publicKey, 0600)) {
    die('OPTIVO UPLOAD - could not change permissions for public key');
}

if (!chmod($privateKey, 0600)) {
    die('OPTIVO UPLOAD - could not change permissions for privat key');
}

if (!file_exists($publicKey) || !file_exists($privateKey)) {
    die('OPTIVO UPLOAD - could not find public key file');
}

//upload trigger to optivo server
$domainParts = explode(".", $config->domain->base);
$domainShort = $domainParts[count($domainParts) - 1];

if (IS_PRODUCTION) {
    system(sprintf('scp -i %s %s %s', $privateKey, $file, $server . 'blacklist_' . $domainShort . '.csv'));
}

clog('info', sprintf('OPTIVO EXPORT - %s: finishing blacklist export', $config->domain->base));

################################################################################

clog('info', sprintf('OPTIVO EXPORT - %s: starting cronjob for optivo export', $config->domain->base));


$outfile = null;
/**
 * we will try creating outfile 3 times
 * @author Felix Haferkorn
 * @since 19.06.2012
 */
for ($i = 0; $i <= 10; $i++) {
    $outfile = '/tmp/export-optivo-' . $config->domain->base . '-' . time() . '-'.$i.'.csv';

    try {
        $db->query(sprintf("SELECT * INTO OUTFILE '%s' FIELDS TERMINATED BY ';' ESCAPED BY '' ENCLOSED BY '' LINES TERMINATED BY '\n' FROM data_view_optivo_export_complete", $outfile));
    } catch (Exception $e) {
        die(sprintf('Exception while writing result into outfile: %s', $e->getMessage()));
    }

    // sleep 2 seconds each loop
    sleep(60);

    if (!file_exists($outfile)) {
        //echo sprintf('outfile does not exist in %s. loop - trying again', ($i+1))."\n";
        continue;
    } else {
        //echo sprintf('outfile exists in %s. loop - going on', ($i+1))."\n";
        break;
    }
}

if (!file_exists($outfile)) {
    die(sprintf('outfile "%s" does not exist - dieing !', $outfile));
}

clog('info', sprintf('OPTIVO EXPORT - %s: finally wrote csv into outfile "%s"', $config->domain->base, $outfile));

$publicKey = APPLICATION_PATH . '/templates/keys/id_rsa.pub';
$privateKey = APPLICATION_PATH . '/templates/keys/id_rsa';
$server = 'r_yourdelivery@ftpapi.broadmail.de:newsletter/';

if (!chmod($publicKey, 0600)) {
    die('OPTIVO UPLOAD - could not change permissions for public key');
}

if (!chmod($privateKey, 0600)) {
    die('OPTIVO UPLOAD - could not change permissions for private key');
}

if (!file_exists($publicKey) || !file_exists($privateKey)) {
    die('OPTIVO UPLOAD - could not find public key file');
}

//upload trigger to optivo server
if (IS_PRODUCTION) {

    if (in_array($config->domain->base, array('smakuje.pl', 'pyszne.pl'))) {
        $name = 'yourdelivery_pl';
    } else {
        $name = str_replace(".", "_", $config->domain->base);
    }
    system(sprintf('scp -i %s %s %s', $privateKey, $outfile, $server . $name . '.csv'));
}
