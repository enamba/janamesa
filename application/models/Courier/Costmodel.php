<?php
/**
 * Courier Costmodel Model
 * @author alex
 * @since 24.05.2011
 */
class Yourdelivery_Model_Courier_Costmodel extends Default_Model_Base{

    /**
     * Get table
     * @author alex
     * @since 24.05.2011
     * @return Yourdelivery_Model_DbTable_Courier_Costmodel
     */
    public function getTable(){
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Courier_Costmodel();
        }
        return $this->_table;

    }

}
