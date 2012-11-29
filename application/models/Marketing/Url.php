<?php

/**
 *
 * @author mlaug
 */
class Yourdelivery_Model_Marketing_Url extends Default_Model_Base{

    /**
     * get related table
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Rabatt
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Marketing_Url();
        }
        return $this->_table;
    }

}