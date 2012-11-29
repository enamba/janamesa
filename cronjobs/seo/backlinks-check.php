<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
* Check the backlinks
* @author vpriem
* @since 21.01.2011
*/
clog('info', 'checking backlinks for the seo team');
$failed = array();

// load table
$backlinkTable = new Yourdelivery_Model_DbTable_Backlink();

// get all
$rows = $backlinkTable->fetchAll();
foreach ($rows as $row) {
    if (!$row->check()) {
        $failed[] = '<a href="http://' . $row->url . '">' . $row->url . '</a>';
    }
}

if (count($failed)) {
    Yourdelivery_Sender_Email::quickSend("Warning", "The following Backlinks are invalid:<br />" . implode("<br />", $failed), null, "seo");
}
