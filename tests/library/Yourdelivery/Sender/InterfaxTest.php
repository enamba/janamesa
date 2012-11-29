<?php


/**
 * @author Matthias Laug <laug@lieferando.de>
 * 
 * @runTestsInSeparateProcesses
 */
class YourdeliverySenderInterFaxTest extends Yourdelivery_Test {

    /**
     * @author mlaug
     * @since 24.11.2010
     */
    public function testSendOutFaxWithInterfax() {

        $config = Zend_Registry::get("configuration");
        $testfax = APPLICATION_PATH . '/templates/fax/testfax/ydtestfax-' . $config->domain->base . '.pdf';
        $this->assertTrue(file_exists($testfax));

        $fax = new Yourdelivery_Sender_Fax_Interfax();
        $transactionId = $fax->send('03049914022', $testfax, 'order');
        $this->assertNotNull($transactionId);
        $this->assertGreaterThan(0, $transactionId);
    }

    /**
     * @author mlaug
     * @since 02.01.2011
     */
    public function testProcessFaxWithInterfax() {

        // this is an old transaction id send out some time ago (01.09.2011)
        $transactionId = 227719658;

        $table = new Yourdelivery_Model_DbTable_Interfax_Transactions();
        $table->delete(); //remove all entries

        $orderId = (integer) $this->placeOrder();
        $this->assertGreaterThan(0, $orderId);
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'Edited by TestCase'));

        $table->createRow(array(
            'transactionId' => $transactionId,
            'orderId' => $orderId,
        ))->save();

        $fax = new Yourdelivery_Sender_Fax_Interfax();
        $fax->setTestingObject($this->loadMockObject("interfax-test-result-ok.xml"));
        $fax->processReports();

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getState(), 1, Default_Helpers_Log::getLastLog());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.01.2011
     */
    public function testProcessFaxWithInterfaxErrors() {

        // this is an old transaction id send out some time ago (01.09.2011)
        $transactionId = '231829332';
        $table = new Yourdelivery_Model_DbTable_Interfax_Transactions();
        $table->delete(); //remove all entries

        $orderId = (integer) $this->placeOrder();
        $this->assertGreaterThan(0, $orderId);
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'Edited by TestCase'));

        $table->createRow(array(
            'transactionId' => $transactionId,
            'orderId' => $orderId,
        ))->save();

        $fax = new Yourdelivery_Sender_Fax_Interfax();
        $fax->setTestingObject($this->loadMockObject("interfax-test-result-error.xml"));
        $fax->processReports();

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getState(), -1, Default_Helpers_Log::getLastLog());
    }

    public function testProcessFaxWithInterfaxPending() {
        $transactionId = '231829332';
        $table = new Yourdelivery_Model_DbTable_Interfax_Transactions();
        $table->delete(); //remove all entries

        $orderId = (integer) $this->placeOrder();
        $this->assertGreaterThan(0, $orderId);
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'Edited by TestCase'));

        $table->createRow(array(
            'transactionId' => $transactionId,
            'orderId' => $orderId,
        ))->save();

        $fax = new Yourdelivery_Sender_Fax_Interfax();
        $fax->setTestingObject($this->loadMockObject("interfax-test-result-pending.xml"));
        $fax->processReports();

        $transactions = $table->fetchAll("`currentStatus` = 'unconfirmed'");
        $this->assertEquals($order->getState(), 0, Default_Helpers_Log::getLastLog());
        $this->assertEquals($transactions[0]['processCount'], 1);
        $this->assertEquals($transactions[0]['currentStatus'], 'unconfirmed');

        for ($i = 0; $i < 8; $i++) {
            $fax->processReports();
        }



        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getState(), -15, Default_Helpers_Log::getLastLog());
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 05.04.2012
     * @see http://ticket/browse/SP-4496
     */
    public function testProcessWithCancellation() {

        $db = Zend_Registry::get('dbAdapter');
        $config = Zend_Registry::get("configuration");

        $orderId = $this->placeOrder(array('notify' => 'fax', 'faxService' => 'interfax', 'checkForFraud' => false));

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals('fax', $order->getService()->getNotify());
        $this->assertEquals('interfax', $order->getService()->getFaxService());

        $order->setStatus(Yourdelivery_Model_Order_Abstract::STORNO, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'set by testSendStornoNotificationToRestaurant'));
        $order->sendStornoNotificationToRestaurant();
        $row = $db->fetchRow($db->quoteInto('select * from order_sendby where orderId = ?', $orderId));
        $this->assertEquals('fax', $row['sendBy']);
        $this->assertEquals($order->getId(), $row['orderId']);
        $this->assertFileExists(sprintf('%s/../storage/stornos/%s/%s-stornosheet-restaurant.pdf'
                        , APPLICATION_PATH, date('d-m-Y', time()), $order->getId()));

        $fax = new Yourdelivery_Sender_Fax_Interfax();
        $fax->setTestingObject($this->loadMockObject("interfax-test-result-ok.xml"));
        $fax->processReports();

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getState(), -2, Default_Helpers_Log::getLastLog());
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 05.04.2012
     */
    public function loadMockObject($file) {

        $file = simplexml_load_file(APPLICATION_PATH . "/../tests/data/" . $file);

        $file = $file->xpath("//soap:Envelope/soap:Body");

        return $file[0]->FaxQueryResponse;
    }

}
