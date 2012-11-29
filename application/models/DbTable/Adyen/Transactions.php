<?php

/**
 * Adyen Transaction Db Table
 * @author Matthias Laug <laug@lieferando.de> 2012-03-22
 * @since 15.11.2010
 * @deprecated
 */
class Yourdelivery_Model_DbTable_Adyen_Transactions extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'adyen_transactions';

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
     * Get transaction by transactionId
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.03.2012
     * @param integer $transactionId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getByTransactionId($transactionId) {

        return $this->fetchRow(
                        $this->select()
                                ->where("`transactionId` = ?", $transactionId)
        );
    }

    /**
     * Get transactions by orderId
     *
     * @author vpriem
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