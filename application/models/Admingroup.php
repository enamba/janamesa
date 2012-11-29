<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * admin group management
 *
 * @author vait
 */

class Yourdelivery_Model_Admingroup extends Default_Model_Base{

    /**
     * resources this group has aces to
     * @var array
     */
    protected $_resources = null;

    public function __construct($id = null) {
        if(is_null($id))
            return $this;
        parent::__construct($id);
    }

    /**
     * test of this group has access to the resource
     * @return bool
     */
    public function hasAccess($rcId) {
        if ($this->_resources == 0) {
            $this->_resources = $this->getTable()->getResources();
        }

        foreach ($this->_resources as $rc) {
            if ($rc['resourceId'] == $rcId) {
                return true;
            }
        }
        return false;
    }

    /**
     * add resource right to this group
     * @return Zend_Db_Table_Row_Abstract
     */
    public function addResource($resId) {
        return $this->getTable()->addResource($resId);
    }

    /**
     * remove all resources right for this group
     * @return Zend_Db_Table_Row_Abstract
     */
    public function clearResources() {
        return $this->getTable()->clearResources();
    }


    /**
     * get related table
     * @return Yourdelivery_Model_DbTable_Admin_Access_Groups
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Admin_Access_Groups();
        }
        return $this->_table;
    }
}
?>