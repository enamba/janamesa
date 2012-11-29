<?php

/**
 * Model for partner data
 * @author Alex Vait
 * @since 31.07.2012
 */
class Yourdelivery_Model_Servicetype_Partner extends Default_Model_Base {
        
    /**
     * Constructor. first parameter is id, the second is restaurantId. One and only one is required.
     * @author Alex Vait
     * @since 31.07.2012
     * @param int $id
     * @param int $restaurantId
     * @return Yourdelivery_Model_Servicetype_Partner
     * @throws Yourdelivery_Exception_Database_Inconsistency
     */
    public function __construct($id = null, $restaurantId = null) {

        if ((integer) $id > 0) {
            parent::__construct($id);
        } 
        elseif ((integer) $restaurantId > 0) {
            $row = Yourdelivery_Model_DbTable_Restaurant_Partner::findByRestaurantId($restaurantId);
            
            if (!is_array($row)) {
                return null;
            }
            
            parent::__construct($row['id']);
        }
    }
    
    /**
     * @author Alex Vait
     * @since 31.07.2012
     * @return Yourdelivery_Model_DbTable_Restaurant_Partner
     */
    public function getTable() {        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Partner();
        }
        
        return $this->_table;
    }
   
}
