<?php

/**
 * @author vpriem
 * @since 03.11.2011
 */
class Yourdelivery_Model_Customer_Creditcard extends Default_Model_Base {

    /**
     * @author vpriem
     * @since 03.11.2011
     * @return 
     */
    public function getTable() {
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Customer_Creditcard();
        }
        
        return $this->_table;
    }

    /**
     * @author vpriem
     * @since 03.11.2011
     * @param int $customerId
     * @return boolean 
     */
    public function isOwner($customerId) {
        
        return $this->getCustomerId() == $customerId;
    }
}
