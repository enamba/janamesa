<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 24.11.2011
 */
class Yourdelivery_Model_Order_Sendby extends Default_Model_Base {
    
    /**
     * Get table
     * @author Vincent Priem <priem@lieferando.de>
     * @since 24.11.2011
     * @return Yourdelivery_Model_DbTable_Order_Sendby
     */
    public function getTable() {
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Order_Sendby();
        }
        
        return $this->_table;
    }
}
