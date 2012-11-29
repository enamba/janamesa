<?php

/**
 * @author alex
 * @since 08.06.2011
 */
class Yourdelivery_Model_Servicetype_Printer extends Default_Model_Base {

    /**
     * @var Yourdelivery_Model_Servicetype_Restaurant 
     */
    private $_restaurant;

    /**
     * @var Yourdelivery_Model_Printer_Topup 
     */
    private $_printer;
    
    /**
     * @author alex
     * @since 08.06.2011
     * @return Yourdelivery_Model_DbTable_Restaurant_Printer
     */
    public function getTable() {
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Printer();
        }
        
        return $this->_table;
    }
    
    /**
     * @author vpriem
     * @since 17.11.2011
     * @return Yourdelivery_Model_Servicetype_Restaurant
     */
    public function getRestaurant() {
        
        if ($this->_restaurant instanceof Yourdelivery_Model_Servicetype_Restaurant) {
            return $this->_restaurant;
        }
        
        try {
            return $this->_restaurant = new Yourdelivery_Model_Servicetype_Restaurant($this->getRestaurantId());
        } catch(Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        return null;
    }
    
    /**
     * @author vpriem
     * @since 17.11.2011
     * @return Yourdelivery_Model_Printer_Abstract
     */
    public function getPrinter() {
        
        if ($this->_printer instanceof Yourdelivery_Model_Printer_Abstract) {
            return $this->_printer;
        }
        
        try {
            return $this->_printer = Yourdelivery_Model_Printer_Abstract::factory($this->getPrinterId());
        } catch(Yourdelivery_Exception_Database_Inconsistency $e)  {
        }
        
        return null;
    }
}
