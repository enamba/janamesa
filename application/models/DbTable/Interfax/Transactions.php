<?php
/**
 * Interfax Transaction Db Table
 * @author mlaug
 * @since 15.11.2010
 */
class Yourdelivery_Model_DbTable_Interfax_Transactions extends Zend_Db_Table_Abstract{

    /**
     * Table name
     * @var string
     */
    protected $_name = 'interfax_transactions';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * Get transactions by orderId
     * @author mlaug
     * @since 31.01.2011
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