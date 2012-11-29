<?php

/**
 * @author Vincent Priem <priem@lieferando.de>, Alex Vait <vait@lieferando.de>
 * @since 10.07.2012
 * @modified 29.08.2012
 */
class Yourdelivery_Model_Admin_Access_Group extends Default_Model_Base {

    protected $_resources = null;
    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 29.08.2012
     */
    public function __construct($id = null) {
        if(is_null($id))
            return $this;
        parent::__construct($id);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 10.07.2012
     * @return Yourdelivery_Model_DbTable_Admin_Access_Groups
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Admin_Access_Groups();
        }

        return $this->_table;
    }
    
    /**
     * test of this group has access to the resource
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 29.08.2012
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
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 29.08.2012
     */
    public function addResource($resId) {
        $this->getTable()->addResource($resId);
    }

    /**
     * remove all resources right for this group
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 29.08.2012
     */
    public function clearResources() {
        $this->getTable()->clearResources();
    }
    

}