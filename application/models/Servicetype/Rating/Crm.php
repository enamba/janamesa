<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 17.04.2012
 */
class Yourdelivery_Model_Servicetype_Rating_Crm extends Default_Model_Base {

    /**
     * @var Yourdelivery_Model_Admin 
     */
    protected $_admin;
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.04.2012
     * @return Yourdelivery_Model_Admin 
     */
    public function getAdmin() {
        
        if ($this->_admin === null) {
            try {
                $this->_admin = new Yourdelivery_Model_Admin($this->getAdminId());
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->_admin = new Yourdelivery_Model_Admin();
            }
        }
        
        return $this->_admin;
    }
    
    /**
     * Get associated table of model
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.04.2012
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Ratings_Crm();
        }
        return $this->_table;
    }

}
