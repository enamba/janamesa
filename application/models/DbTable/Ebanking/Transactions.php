<?php

/**
 * Ebanking Transaction Db Table
 * @author Vincent Priem <priem@lieferando.de>
 * @since 04.10.2011
 */
class Yourdelivery_Model_DbTable_Ebanking_Transactions extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'ebanking_transactions';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * @var string
     */
    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Ebanking_Transactions';
    
    /**
     * @var array 
     */
    protected $_referenceMap = array(
        'Order' => array(
            'columns' => 'orderId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Order',
            'refColumns' => 'id'
        )
    );
    
    /**
     * Get transactions by orderId
     * @author Vincent Priem <priem@lieferando.de>
     * @since 15.11.2010
     * @param int $orderId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getByOrder($orderId) {

        return $this->fetchAll(
            $this->select()
                 ->where("`orderId` = ?", $orderId)
        );
    }

}

/**
 * Ebanking Transaction Db Table Row
 * @author Vincent Priem <priem@lieferando.de>
 * @since 21.01.2011
 */
class Yourdelivery_Model_DbTableRow_Ebanking_Transactions extends Zend_Db_Table_Row_Abstract {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.02.2012
     */
    protected function _insert() {
        
        parent::_insert();
        
        if (empty($this->payerId)) {
            $this->payerId = $this->generatePayerId();
        }
    }
    
    /**
     * Get dara as array
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return array
     */
    public function getData() {

        return unserialize($this->data);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.02.2012
     * @return string
     */
    public function generatePayerId() {

        $data = $this->getData();
        
        if (!array_key_exists("transaction", $data)) {
            return null;
        }
        
        return sha1(
            $data['sender_holder'] . 
            $data['sender_account_number'] . 
            $data['sender_bank_code'] . 
            $data['sender_bank_name'] . 
            $data['sender_bank_bic'] . 
            $data['sender_iban']);
    }
    
}