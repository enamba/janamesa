<?php
/**
 *
 * @package billing
 * @author Alex Vait <vait@lieferando.de>
 */
class Yourdelivery_Model_Billing_Status extends Default_Model_Base {
    /**
     * Get related table
     * @author Alex Vait <vait@lieferando.de>
     * @since 19.06.2012
     * @return Yourdelivery_Model_DbTable_Admin_Access_Users
     */
    public function getTable(){

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Billing_Status();
        }
        return $this->_table;
    }


}
