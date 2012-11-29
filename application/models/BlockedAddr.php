<?php

/**
 * store all blocked ips
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */

class Yourdelivery_Model_BlockedAddr extends Default_Model_Base{

    /**
     * related base table
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @var Yourdelivery_Model_DbTable_Customer
     */
    protected $_table = null;

    /**
     * get associated table
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @return Yourdelivery_Model_DbTable_BlockedAddr
     */
    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_BlockedAddr();
        }
        return $this->_table;
    }
}