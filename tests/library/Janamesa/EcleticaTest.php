<?php


/**
 * @author Matthias Laug <laug@lieferando.de>
 * @runTestsInSeparateProcesses
 */
class EcleticaTest extends Yourdelivery_Test {

    public function testSend() {
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('checkForFraud' => false)));
        $ecletica = new Janamesa_Api_Ecletica();
        $this->assertTrue($ecletica->send($order));
    }

    public function testProcess() {
        //failure
        $service = $this->getRandomService();
        $notify = $service->getNotify();

        $service->setNotify('ecletica');
        $service->save();

        $ecletica = new Janamesa_Api_Ecletica();
        $ecletica->processReports();

        $orderId = $this->placeOrder(array('service' => $service, 'checkForFraud' => false));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'by EcleticaTest::testProcess'));
        $this->assertTrue($ecletica->send($order));
        $falha = APPLICATION_PATH . '/../storage/ecletica/' . $order->getService()->getId() . '/falha';
        $this->assertTrue(file_exists($falha));
        copy(APPLICATION_PATH . '/../storage/ecletica/' . $order->getService()->getId() . '/' . $order->getId() . '_jnm.txt', $falha . '/' . $order->getId() . '_jnm.txt');
        $this->assertTrue(file_exists($falha . '/' . $order->getId() . '_jnm.txt'));
        $ecletica->processReports();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(-1, $order->getStatus());
        $this->assertTrue(file_exists($falha . '/' . $order->getId() . '_jnm.txt'));
        $count = count($order->getStateHistory());
        $ecletica->processReports();
        $this->assertEquals($count, count($order->getStateHistory()));

        //success      
        $orderId = $this->placeOrder(array('service' => $service, 'checkForFraud' => false));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertTrue($ecletica->send($order));
        $sucesso = APPLICATION_PATH . '/../storage/ecletica/' . $order->getService()->getId() . '/sucesso';
        $this->assertTrue(file_exists($sucesso));
        copy(APPLICATION_PATH . '/../storage/ecletica/' . $order->getService()->getId() . '/' . $order->getId() . '_jnm.txt', $sucesso . '/' . $order->getId() . '_jnm.txt');
        $this->assertTrue(file_exists($sucesso . '/' . $order->getId() . '_jnm.txt'));
        $ecletica->processReports();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(1, $order->getStatus());
        $this->assertTrue(file_exists($sucesso . '/' . $order->getId() . '_jnm.txt'));
        $count = count($order->getStateHistory());
        $ecletica->processReports();
        $this->assertEquals($count, count($order->getStateHistory()));

        //reset to notify of fax        
        if (is_null($notify)) {
            $notify = 'fax';
        }
        $service->setNotify($notify);
        $service->save();
    }

}

?>
