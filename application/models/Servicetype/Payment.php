<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 14.08.2012
 */
class Yourdelivery_Model_Servicetype_Payment extends Default_Model_Base {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.08.2012
     * @return Yourdelivery_Model_DbTable_Restaurant_Payments
     */
    public function getTable(){
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Payments();
        }
        
        return $this->_table;
    }
}
