<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * admin resource management
 *
 * @author vait
 */

class Yourdelivery_Model_Admin_Resource extends Default_Model_Base{

    public function __construct($id = null) {
        if(is_null($id))
            return $this;
        parent::__construct($id);
    }

    /**
     * get related table
     * @return Yourdelivery_Model_DbTable_Admin_Access_Resources
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Admin_Access_Resources();
        }
        return $this->_table;
    }
}
?>