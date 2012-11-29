<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.01.2012
 */
class Yourdelivery_Model_DbTable_Ebanking_Refund_Transactions extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'ebanking_refund_transactions';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

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
     * @var string
     */
    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Ebanking_Refund_Transactions';
    
}

/**
 * Ebanking Transaction Db Table Row
 * @author Vincent Priem <priem@lieferando.de>
 * @since 21.01.2011
 */
class Yourdelivery_Model_DbTableRow_Ebanking_Refund_Transactions extends Zend_Db_Table_Row_Abstract {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return boolean
     */
    public function isStatusOk() {

        return strpos($this->response, "<status>ok</status>") !== false;
    }

}