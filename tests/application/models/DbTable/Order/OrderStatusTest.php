<?php

/**
 * @runTestsInSeparateProcesses 
 */
class DbOrderStatusTest extends Yourdelivery_Test {

    /**
     * testcases for Order Status DbTable Model.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 21.05.2012 
     */
    public function testAllOrderStatus() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $orderId = $order->getId();

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('order_status')->where('orderId=' . $orderId);
        $result = $db->fetchRow($query);

        $id = $result['id'];
        $order_status = new Yourdelivery_Model_DbTable_Order_Status();

        $message = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'test edit');


        $data = array('status' => 1, 'comment' => $message->getRawMessage(), 'message' => $message->__toString());
        $order_status->edit($id, $data);
        $result = $db->fetchRow($query);
        $this->assertEquals($data['status'], $result['status']);
        $this->assertEquals($data['comment'], $result['comment']);
        $this->assertEquals($data['message'], $result['message']);

        $this->assertEquals(1, count($order_status->get('id', 1, 'order_status')));
        $ResById = $order_status->findById($id);
        $this->assertEquals($result, $ResById);

        $this->assertEquals($result, $order_status->findByOrderId($orderId));

        $order_status->remove($id);
        $this->assertFalse($db->fetchRow($db->select()->from('order_status')->where('id = ?', $id)));
    }

}

?>
