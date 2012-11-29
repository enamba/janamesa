<?php
/**
 * Master Payment Notifications Db Table
 * @author vpriem
 * @since 25.02.2011
 */
class Yourdelivery_Model_DbTable_Master_Notifications extends Zend_Db_Table_Abstract{

    /**
     * Table name
     * @var string
     */
    protected $_name = 'master_notifications';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * Get notifications by orderId
     * @author vpriem
     * @since 25.02.2011
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