<?php

/**
 * @author vpriem
 * @since 03.11.2011
 */
class Yourdelivery_Model_DbTable_Customer_Creditcard extends Default_Model_DbTable_Base {

    /**
     * The table name
     * @param string
     */
    protected $_name = 'customer_creditcards';

    /**
     * The primary key name
     * @param string
     */
    protected $_primary = 'id';
    
    /**
     * @author vpriem
     * @since 03.11.2011
     * @param int $customerId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findFromCustomerId($customerId) {
        
        return $this->fetchAll(
            $this->select()
                 ->where("`customerId` = ?", $customerId));
    }
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 15.12.2011
     */
    public  function fincByUniqueId($uniqueId) {
        
        return $this->fetchAll($this->select()->where("`uniqueId` = ? ", $uniqueId))->current();
        
    }
}
