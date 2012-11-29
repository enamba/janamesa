<?php

/**
 * Description of OrderTest
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 */

/**
 * @runTestsInSeparateProcesses 
 */
class DbTableOrderTest extends Yourdelivery_Test {

    public function testStatus() {

        $orderId = $this->placeOrder();

        $stati = array(Yourdelivery_Model_Order::DELIVERED,
            Yourdelivery_Model_Order::AFFIRMED,
            Yourdelivery_Model_Order::NOTAFFIRMED,
            Yourdelivery_Model_Order::DELIVERERROR,
            Yourdelivery_Model_Order::FAX_ERROR_NO_TRAIN,
            Yourdelivery_Model_Order::STORNO,
            Yourdelivery_Model_Order::FAKE,
            Yourdelivery_Model_Order::BILL_NOT_AFFIRMED,
            Yourdelivery_Model_Order::PAYMENT_NOT_AFFIRMED,
            Yourdelivery_Model_Order::REJECTED,
        );

        shuffle($stati);

        $status = $stati[0];

        $order = new Yourdelivery_Model_Order($orderId);

        $comment = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "test Status äüöß");

        $this->assertTrue($order->setStatus($status, $comment));

        $db = Zend_Registry::get('dbAdapter');

        $select = $db->select()->from('orders')->where('id=?', $orderId);

        $result = $db->fetchAll($select);

        $this->assertEquals($result[0]['state'], $status);


        $db = Zend_Registry::get('dbAdapter');

        $select = $db->select()->from('order_status')->where('orderId=?', $orderId)->order('id DESC');

        $result = $db->fetchAll($select);

        $this->assertEquals($result[0]['status'], $status);
        $this->assertEquals($result[0]['comment'], $comment->getRawMessage());
        $this->assertEquals($result[0]['message'], $comment->__toString());
    }

    public function testGetState() {
        $orderId = $this->placeOrder();

        $status = Yourdelivery_Model_Order::DELIVERERROR;

        $order = new Yourdelivery_Model_Order($orderId);

        $order->setStatus($status, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "test Status"));

        $db = Zend_Registry::get('dbAdapter');

        $select = $db->select()->from('orders')->where('id=?', $orderId);

        $result = $db->fetchAll($select);

        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertEquals($order->getState(), $status);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.07.2012
     */
    public function testCheckUniqueNr() {

        $db = $this->_getDbAdapter();
        $select = $db->select()->from('orders', 'id')->order('RAND()')->limit(1);
        $order = new Yourdelivery_Model_Order($db->fetchOne($select));

        $uniqueNumber = time() . rand(9, 999);
        $orderTable = new Yourdelivery_Model_DbTable_Order();
        $this->assertTrue($orderTable->checkUniqueNr($uniqueNumber));
        $this->assertFalse($orderTable->checkUniqueNr($order->getNr()));
    }
    
    /**
     * get all unrated orders within an interval
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.08.2012
     */
    public function testAllUnrated(){
        $unrated = count(Yourdelivery_Model_DbTable_Order::allUnrated(0, null, true));
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT), 'test');
        $this->assertEquals($unrated+1, count(Yourdelivery_Model_DbTable_Order::allUnrated(0, null, true)));
    }

}

?>
