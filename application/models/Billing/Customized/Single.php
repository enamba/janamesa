<?php

/**
 * Description of Company
 * @package billing
 * @subpackage customization
 * @author mlaug
 */
class Yourdelivery_Model_Billing_Customized_Single extends Default_Model_Base{

    /**
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Billing_Customized
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Billing_Customized_Single();
        }
        return $this->_table;
    }
}
?>
