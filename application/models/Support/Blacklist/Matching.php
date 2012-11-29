<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 19.06.2012
 */
class Yourdelivery_Model_Support_Blacklist_Matching extends Default_Model_Base {
   
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.06.2012
     * @return Yourdelivery_Model_DbTable_Blacklist_Values
     */
    public function getTable() {
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Blacklist_Matching();
        }
        
        return $this->_table;
    }
    
}
