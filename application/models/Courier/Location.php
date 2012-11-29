<?php
/**
 * Courier Location Model
 * @package courier
 * @package location
 * @author mlaug
 * @since 01.08.2010
 */
class Yourdelivery_Model_Courier_Location extends Default_Model_Base{

    /**
     * Get table
     * @author mlaug
     * @since 01.08.2010
     * @return Yourdelivery_Model_DbTable_Courier_Location
     */
    public function getTable(){
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Courier_Location();
        }
        return $this->_table;

    }

}
