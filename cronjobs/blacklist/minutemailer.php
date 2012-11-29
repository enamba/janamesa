<?php

/**
 * A cron job script updating 10minute e-mail blacklist
 *
 * @author Marek Hejduk <m.hejduk@pyszne.pl>
 * @since 11-07-2012
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));

define('DATA_SOURCE', 'http://www.mogelmail.de/export_txt.php');

try {
    // Retrieving and parsing 10minute e-mail domain list
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, DATA_SOURCE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $rawData = curl_exec($curl);
    curl_close($curl);
    clog('debug', 'Raw data retrieved from ' . DATA_SOURCE);

    if ($rawData === false) {
        throw new ErrorException('Could not read contents from ' . DATA_SOURCE);
    }
    $domains = preg_split('/\s+/', $rawData, null, PREG_SPLIT_NO_EMPTY);
    if (!is_array($domains) || empty($domains)) {
        throw new UnexpectedValueException(sprintf(
            'Data retrieved from %s are empty or invalid', DATA_SOURCE
        ));
    }
    clog('debug', sprintf('Domain list successfully parsed - contains %d entry(s)', count($domains)));

    // Retrieving current e-mail blacklist and filtering out already existing domains
    $list = Yourdelivery_Model_Support_Blacklist::getList(array('email'));
    foreach ($list as $entry) {
        if ($entry->type == Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL_MINUTEMAILER && !$entry->isDeprecated()) {
            if (($index = array_search($entry->value, $domains)) !== false) {
                unset($domains[$index]);
                clog('debug', sprintf('Domain: %s filtered out (already exists)', $entry->value));
            }
        }
    }
    clog('debug', sprintf('Already existing domains filtered out - now list contains %d entry(s)', count($domains)));

    $date = date('Y-m-d H:i');
    $savedCount = 0;
    foreach ($domains as $domain) {
        // New blacklist model instance creating for each domain
        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $blacklist->setAdminId(0);
        $blacklist->setComment('10minute e-mail blacklist updating cron job - ' . $date);
        $blacklist->setOrderId(0);
        $blacklist->addValue(
            Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL_MINUTEMAILER,
            'whatever@' . trim($domain)
        );
        if (($blackListId = $blacklist->save()) !== false) {
            clog('debug', sprintf(
                'New blacklist model instance created for domain: %s with id: %d',
                $domain, $blackListId
            ));
            $savedCount++;
        } else {
            clog('warn', 'New blacklist model instance could not be created for domain: ' . $domain);
        }
    }

    clog('info', sprintf(
        '%d new 10minute e-mail blacklist entry(s) have been created', $savedCount
    ));

} catch (Exception $ex) {
    // Shit happened...
    die(sprintf('An error occured during 10minute e-mail blacklist update: %s %s', $ex->getMessage(), $ex->getTraceAsString()));
}
