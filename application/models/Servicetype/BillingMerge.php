<?php
/**
 * Yourdelivery Model Servicetype BillingMerge
 * @author alex
 * @since 28.09.2010
 */
class Yourdelivery_Model_Servicetype_BillingMerge extends Default_Model_Base {

    /**
     * Get associated table
     * @author alex
     * @since 28.09.2010
     * @return Yourdelivery_Model_DbTable_Restaurant_BillingMerge
     */
    public function getTable(){

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_BillingMerge();
        }
        return $this->_table;

    }
}
