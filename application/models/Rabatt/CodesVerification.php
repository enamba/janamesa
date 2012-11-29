<?php

/**
 * @author Alex Vait <vait@lieferando.de>
 * @since 17.01.2012
 * 
 */
class Yourdelivery_Model_Rabatt_CodesVerification extends Default_Model_Base {
    
    /**
     * get table class
     * @author Alex Vait <vait@lieferando.de>
     * @since 17.01.2012
     * @return Yourdelivery_Model_DbTable_RabattCodesVerification
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_RabattCodesVerification();
        }
        return $this->_table;
    }
    
    /**
     * get parent discount
     * @author Alex Vait <vait@lieferando.de>
     * @return Yourdelivery_Model_Rabatt
     */
    public function getParent() {
        if ($this->_parent === null) {
            $parentTableRow = $this->getTable()->getParent();
            try {
                $this->_parent = new Yourdelivery_Model_Rabatt($parentTableRow->id);
            } 
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {                
            }
        }
        return $this->_parent;
    }
   
}
