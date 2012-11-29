<?php
/**
 * Order Courier State Db Table
 * @author vpriem
 * @since 28.09.2010
 */
class Yourdelivery_Model_DbTable_Order_Courier_State extends Zend_Db_Table_Abstract{

    protected $_defaultSource = self::DEFAULT_DB;

    /**
     * Table name
     */
    protected $_name = 'order_courier_state';

    /**
     * Primary key name
     */
    protected $_primary = 'id';

    /**
     * Get the last state of an order
     * @param int $orderId
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getLastState ($orderId) {

        return $this->fetchRow(
            $this->select()
                 ->where("`orderId` = ?", $orderId)
                 ->order("created DESC")
        );

    }

}