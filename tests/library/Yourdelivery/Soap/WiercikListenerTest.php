<?php

/**
 * Description of WiercikListener
 *
 * @author daniel
 * 
 * @runTestsInSeparateProcesses
 */
class YourdeliverySoapWiercikListenerTest extends Yourdelivery_Test {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.05.2012
     */
    public function testOrderReceived() {

        $db = Zend_Registry::get('dbAdapter');
        $orderId = $this->placeOrder();

        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "test"));
        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order_Abstract::NOTAFFIRMED);

        $select = $db->select()->from('order_status')->where('orderId=?', $orderId);
        $result = $db->fetchAll($select);
        $resultOld = count($result);


        $listener = new Yourdelivery_Soap_WiercikListener(Zend_Registry::get('logger'));
        $listener->orderReceived($orderId);

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order_Abstract::NOTAFFIRMED);

        $select = $db->select()->from('order_status')->where('orderId=?', $orderId);
        $result = $db->fetchAll($select);
        $resultNew = count($result);
        $this->assertEquals($resultOld + 1, $resultNew);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.05.2012
     */
    public function testOrderLost() {
        $db = Zend_Registry::get('dbAdapter');
        $orderId = $this->placeOrder();

        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "test"));
        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order_Abstract::NOTAFFIRMED);

        $select = $db->select()->from('order_status')->where('orderId=?', $orderId);
        $result = $db->fetchAll($select);
        $resultOld = count($result);


        $listener = new Yourdelivery_Soap_WiercikListener(Zend_Registry::get('logger'));
        $listener->orderLost($orderId);

        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order_Abstract::DELIVERERROR);

        $select = $db->select()->from('order_status')->where('orderId=?', $orderId);
        $result = $db->fetchAll($select);
        $resultNew = count($result);
        $this->assertEquals($resultOld + 1, $resultNew);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 02.05.2012
     */
    public function testOrderAcceptedWithDeliverTime() {
        $db = Zend_Registry::get('dbAdapter');
        $orderId = $this->placeOrder();

        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "test"));
        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order_Abstract::NOTAFFIRMED);

        $select = $db->select()->from('order_status')->where('orderId=?', $orderId);
        $result = $db->fetchAll($select);
        $resultOld = count($result);


        $listener = new Yourdelivery_Soap_WiercikListener(Zend_Registry::get('logger'));
        $listener->orderAccepted($orderId, 0);

        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order_Abstract::AFFIRMED);
        $this->assertEquals($order->getTimestamp() == $order->getDeliverTimestamp());

        $select = $db->select()->from('order_status')->where('orderId=?', $orderId);
        $result = $db->fetchAll($select);
        $resultNew = count($result);
        $this->assertEquals($resultOld + 1, $resultNew);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 28.05.2012
     */
    public function testOrderAcceptedWithoutDeliverTime() {
        $db = Zend_Registry::get('dbAdapter');
        $orderId = $this->placeOrder();

        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "test"));
        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order_Abstract::NOTAFFIRMED);

        $select = $db->select()->from('order_status')->where('orderId=?', $orderId);
        $result = $db->fetchAll($select);
        $resultOld = count($result);

        $deliverDelay = rand(10, 240) * 60;
        $listener = new Yourdelivery_Soap_WiercikListener(Zend_Registry::get('logger'));
        $listener->orderAccepted($orderId, $deliverDelay);

        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order_Abstract::AFFIRMED);
        $this->assertEquals($order->getTimestamp() + $deliverDelay == $order->getDeliverTimestamp());

        $select = $db->select()->from('order_status')->where('orderId=?', $orderId);
        $result = $db->fetchAll($select);
        $resultNew = count($result);
        $this->assertEquals($resultOld + 1, $resultNew);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.05.2012
     */
    public function testOrderRejected() {
        $db = Zend_Registry::get('dbAdapter');
        $orderId = $this->placeOrder();

        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "test"));
        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order_Abstract::NOTAFFIRMED);

        $select = $db->select()->from('order_status')->where('orderId=?', $orderId);
        $result = $db->fetchAll($select);
        $resultOld = count($result);


        $listener = new Yourdelivery_Soap_WiercikListener(Zend_Registry::get('logger'));
        $listener->orderRejected($orderId, 0);

        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order_Abstract::REJECTED);

        $select = $db->select()->from('order_status')->where('orderId=?', $orderId);
        $result = $db->fetchAll($select);
        $resultNew = count($result);
        $this->assertEquals($resultOld + 1, $resultNew);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.05.2012
     */
    public function testPrinterStatuses() {

        $db = Zend_Registry::get('dbAdapter');

        $result = $db->fetchRow("Select id, type from printer_topup order by rand() limit 1");

        $type = $result['type'];

        $db->update('printer_topup', array('type' => 'wiercik'), "id=" . $result['id']);
        sleep(1);
        $printer = new Yourdelivery_Model_Printer_Wiercik($result['id']);

        $printer->setOnline(0);
        $printer->save();
        $this->assertEquals($printer->getOnline(), 0);

        $listener = new Yourdelivery_Soap_WiercikListener(Zend_Registry::get('logger'));
        $listener->printersStatuses(array($result['id'] => 1));

        $printer = new Yourdelivery_Model_Printer_Wiercik($result['id']);

        $this->assertEquals($printer->getOnline(), 1);

        $db->update('printer_topup', array('type' => $type), "id=" . $result['id']);
    }

}

