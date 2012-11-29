<?php
/**
 * Paypal Payment Notifications Db Table
 * @author vpriem
 * @since 24.03.2011
 */
class Yourdelivery_Model_DbTable_Paypal_Notifications extends Zend_Db_Table_Abstract{

    /**
     * Table name
     * @var string
     */
    protected $_name = 'paypal_notifications';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * Get notifications by orderId
     * @author vpriem
     * @since 24.03.2011
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