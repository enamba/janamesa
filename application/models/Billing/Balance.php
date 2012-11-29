<?php
/**
 * @author mlaug
 */
class Yourdelivery_Model_Billing_Balance extends Default_Model_Base {

    /**
     * @var Default_Model_Base
     */
    protected $_obj = null;

    /**
     * @author mlaug
     */
    public function setObject(Default_Model_Base $obj) {
        $this->_obj = $obj;
    }

    /**
     * add a balance (positiv/negative) to the account
     * @author mlaug
     * @since 10.06.2011
     * @param integer $amount
     */
    public function addBalance($amount, $comment = null, $zeroBorder = false, $billingId = null, $maxDate = null, $affects = null) {

        if (!is_object($this->_obj)) {
            throw new Yourdelivery_Exception_Balance_NoObjectGiven();
        }
        
        if ( $affects === null ){
            $affects = time();
        }
        
        //do not allow to step over zero
        /*if ( $zeroBorder ){
            $currentAmount = abs($this->getAmount($maxDate));
            $changeAmount = abs($amount);
            if ( $currentAmount < $changeAmount ){
                $amount = (-1) * $this->getAmount($maxDate);
            }
        }*/

        $data = array(
            'amount' => (integer) $amount,
            'comment' => $comment,
            'billingId' => $billingId,
            'affects' => date(DATETIME_DB, $affects),
            'restaurantId' => $this->_obj instanceof Yourdelivery_Model_Servicetype_Abstract ? $this->_obj->getId() : null,
            'companyId' => $this->_obj instanceof Yourdelivery_Model_Company ? $this->_obj->getId() : null
        );

        return $this->getTable()
                ->createRow($data)
                ->save();
    }
    
    /**
     * reset balance
     * @author mlaug
     * @since 12.06.2011
     */
    public function resetAmount(){     
        $this->_checkForObject();
        $this->addBalance($this->getAmount() * (-1));
    }

    /**
     * get current balance of object
     * @author mlaug
     * @since 10.06.2011
     * @param timestamp $maxDate
     * @return integer
     */
    public function getAmount($maxDate = null) {

        $this->_checkForObject();

        if ($this->_obj instanceof Yourdelivery_Model_Company) {
            return (integer) $this->getTable()
                    ->getBalanceOfCompany($this->_obj->getId(), $maxDate);
        }

        if ($this->_obj instanceof Yourdelivery_Model_Servicetype_Abstract) {
            return (integer) $this->getTable()
                    ->getBalanceOfService($this->_obj->getId(), $maxDate);
        }
    }

    /**
     * @author mlaug
     * @since 10.06.2011
     * @return array
     */
    public function getList($maxDate = null) {
        $this->_checkForObject();
        
        if ($this->_obj instanceof Yourdelivery_Model_Company) {
            return (array) $this->getTable()
                    ->getListOfTransactionsOfCompany($this->_obj->getId(), $maxDate);
        }

        if ($this->_obj instanceof Yourdelivery_Model_Servicetype_Abstract) {
            return (array) $this->getTable()
                    ->getListOfTransactionsOfService($this->_obj->getId(), $maxDate);
        }
    }

    /**
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Billing_Balance
     */
    public function getTable() {
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Billing_Balance();
        }
        return $this->_table;
    }

    private function _checkForObject() {
        if (!is_object($this->_obj)) {
            throw new Yourdelivery_Exception_Balance_NoObjectGiven();
        }

        if (!$this->_obj instanceof Yourdelivery_Model_Servicetype_Abstract && !$this->_obj instanceof Yourdelivery_Model_Company) {
            throw new Yourdelivery_Exception_Balance_WrongObjectGiven();
        }
    }

}

class Yourdelivery_Exception_Balance_NoObjectGiven extends Zend_Exception {
}

class Yourdelivery_Exception_Balance_WrongObjectGiven extends Zend_Exception {
}
