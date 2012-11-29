<?php
/**
 * Courier PLZ Model
 * @author alex
 * @since 22.02.2011
 */
class Yourdelivery_Model_Courier_Plz extends Default_Model_Base{

    /**
     * Get table
     * @author alex
     * @since 22.02.2011
     * @return Yourdelivery_Model_DbTable_Courier_Plz
     */
    public function getTable(){
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Courier_Plz();
        }
        return $this->_table;

    }

}
