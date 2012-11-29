<?php

/**
 * Database interface for Yourdelivery_Models_DbTable_OrderStatus.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
 */
class Yourdelivery_Model_DbTable_Order_Status extends Default_Model_DbTable_Base {

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'order_status';

    /**
     * @var string
     */
    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Order_Status';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    protected $_referenceMap = array(
        'Order' => array(
            'columns' => 'orderId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Order',
            'refColumns' => 'id'
        )
    );

    /**
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public static function edit($id, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('order_status', $data, 'order_status.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('order_status', 'order_status.id = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order = null, $limit = 0, $from = 0) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("%ftable%" => "order_status"));

        if ($order != null) {
            $query->order($order);
        }

        if ($limit != 0) {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
    }

    /**
     * get a rows matching Id by given value
     * @param int $id
     */
    public static function findById($id) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "order_status"))
                ->where("o.id = " . $id);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching OrderId by given value
     * @param int $orderId
     */
    public static function findByOrderId($orderId) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "order_status"))
                ->where("o.orderId = " . $orderId);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Status by given value
     * @param int $status
     */
    public static function findByStatus($status) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "order_status"))
                ->where("o.status = " . $status);

        return $db->fetchRow($query);
    }

}

/**
 * @author  Daniel Hahn <hahn@lieferando.de>
 * @since 13.07.2012
 */
class Yourdelivery_Model_DbTableRow_Order_Status extends Zend_Db_Table_Row_Abstract {

    /**
     * @author  Daniel Hahn <hahn@lieferando.de>
     * @since 13.07.2012
     */
    public function getStatusMessage() {

        if (!is_null($this->message)) {
            return Yourdelivery_Model_Order_StatusMessage::createFromString($this->message)->getTranslateMessage();
        } else {
            return $this->comment;
        }
    }

}
