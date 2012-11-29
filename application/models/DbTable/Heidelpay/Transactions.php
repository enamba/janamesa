<?php
/**
 * Heidelpay Transaction Db Table
 * @author vpriem
 * @since 15.11.2010
 * @deprecated
 */
class Yourdelivery_Model_DbTable_Heidelpay_Transactions extends Default_Model_DbTable_Base{

    /**
     * Table name
     * @var string
     */
    protected $_name = 'heidelpay_transactions';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * Get transactions by orderId
     * @author vpriem
     * @since 15.11.2010
     * @param int $orderId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getByOrder ($orderId) {

        return $this->fetchAll(
            $this->select()
                 ->where("`orderId` = ?", $orderId)
        );

    }

}