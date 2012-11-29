<?php

/**
 * Database interface for Yourdelivery_Models_DbTable_BlockedUuid
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Model_DbTable_BlockedUuid extends Default_Model_DbTable_Base {

    /**
     * table name
     * @var string
     */
    protected $_name = 'blocked_uuid';

    /**
     * primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Check if an UUID is blocked
     * 
     * @param string $uuid uuid to ckeck wheather is blocked or not
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 13.09.2010
     * 
     * @return boolean 
     */
    public function isBlocked($uuid) {
        // check
        $rows = $this->fetchAll();
        foreach ($rows as $row) {
            if ($row->uuid == $uuid) {
                return true;
            }
        }
        return false;
    }

    /**
     * Block an UUID
     * 
     * @param string $uuid uuid to set blocked
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.09.2010
     * 
     * @return boolean
     */
    public function block($uuid) {
        $this->createRow(array(
            'uuid' => $uuid
        ))->save();
        
        return true;
    }

}