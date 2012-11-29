<?php

/**
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 07.12.2011
 */

require_once(realpath(dirname(__FILE__) . '/../base.php'));
$lock = new ExclusiveLock(basename(__FILE__));
if ( $lock->lock() ){
    $queue = new Yourdelivery_Model_DbTable_Heyho_TicketQueue();
    $queue->updateQueue();
}