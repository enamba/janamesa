<?php

/**
 * MockObject for SoapClient for Retarus
 * @author daniel
 * @since 17.04.2012
 * 
 * @runTestsInSeparateProcesses
 */
class MockClient extends SoapClient {

    public $test = null;
    public $type = null;
    public $orderId = null;
    public $state = null;
    public $uniqueid = null;

    public function __construct($test, $config) {
        $this->test = $test;
        $this->config = $config;
        $this->tel = "004980020207702";
    }

    public function sendFaxJob($JobRequest) {
        $this->uniqueid = $JobRequest->options->jobReference;
        $this->test->assertEquals($JobRequest->userName, $this->config->sender->fax->username);
        $this->test->assertEquals($JobRequest->faxNumbers->number, $this->tel);
        $this->test->assertFalse(is_null($JobRequest->documents->name));
        $this->test->assertFalse(is_null($JobRequest->documents->data));
        $this->test->assertTrue(strstr($JobRequest->options->jobReference, $this->type) == 0);
    }

    public function getListOfAvailableFaxReports($AvailableReportsRequest) {
        $this->test->assertEquals($AvailableReportsRequest->userName, $this->config->sender->fax->username);

        $AvailableReportsResponse = new stdClass();
        $AvailableReportsResponse->availableReports->jobId = 'TestJobId';
        return $AvailableReportsResponse;
    }

    public function getFaxReport($ReportRequest) {
        $this->test->assertEquals($ReportRequest->jobId, 'TestJobId');
        $ReportResponse = new stdClass();
        $ReportResponse->avajobId = 'TestJobId';
        $ReportResponse->faxNumbers->number = $this->tel;
        $ReportResponse->faxNumbers->status = $this->state;
        $ReportResponse->documents->name = "Test.pdf";
        $ReportResponse->options->jobReference = $this->type . "-" . $this->orderId . "-" . ($this->uniqueid) ? $this->uniqueid : time() . "-" . $this->config->domain->base;

        return $ReportResponse;
    }

    public function deleteFaxReport() {
        
    }

}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RetarusTest
 *
 * @author daniel
 * @since 17.04.2012
 */
class RetarusTest extends Yourdelivery_Test {

    /**
     * MockObject for SoapClient for Retarus
     * @author daniel
     * @since 17.04.2012
     */
    public function testSendByApi() {
        $orderId = $this->placeOrder();

        $config = Zend_Registry::get('configuration');

        $client = new MockClient($this, $config);
        $client->type = 'order';
        $retarus = new Yourdelivery_Sender_Fax_Retarus('api');

        $retarus->setMockObject($client);

        $file = APPLICATION_PATH . '/../tests/data/rechnung1.pdf';

        $this->assertTrue($retarus->send("", $file, 'order', $orderId));
    }

    /**
     * MockObject for SoapClient for Retarus
     * @author daniel
     * @since 17.04.2012
     */
    public function testProcessReportsApi() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT,"testProcessReportsApi"));

        $config = Zend_Registry::get('configuration');

        $client = new MockClient($this, $config);
        $client->type = 'order';
        $client->orderId = $orderId;
        $client->state = 'OK';
        $retarus = new Yourdelivery_Sender_Fax_Retarus('api');

        $retarus->setMockObject($client);
        $file = APPLICATION_PATH . '/../tests/data/rechnung1.pdf';
        $retarus->send("", $file, 'order', $orderId);

        $retarus->processReports();

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order_Abstract::AFFIRMED);
    }

    /**
     * MockObject for SoapClient for Retarus
     * @author daniel
     * @since 17.04.2012
     */
    public function testProcessReportsApiNoTrain() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT,"testProcessReportsApi"));
        $config = Zend_Registry::get('configuration');

        $client = new MockClient($this, $config);
        $client->type = 'order';
        $client->orderId = $orderId;
        $client->state = 'NO_TRAIN';
        $retarus = new Yourdelivery_Sender_Fax_Retarus('api');

        $retarus->setMockObject($client);
        $file = APPLICATION_PATH . '/../tests/data/rechnung1.pdf';
        $retarus->send("", $file, 'order', $orderId);
        $retarus->processReports();

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order::FAX_ERROR_NO_TRAIN);
    }

    /**
     * MockObject for SoapClient for Retarus
     * @author daniel
     * @since 17.04.2012
     */
    public function testProcessReportsApiError() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT,"testProcessReportsApi"));

        $config = Zend_Registry::get('configuration');

        $client = new MockClient($this, $config);
        $client->type = 'order';
        $client->orderId = $orderId;
        $client->state = 'RING_TO';
        $retarus = new Yourdelivery_Sender_Fax_Retarus('api');
        $retarus->setMockObject($client);

        $file = APPLICATION_PATH . '/../tests/data/rechnung1.pdf';
        $retarus->send("", $file, 'order', $orderId);

        $retarus->processReports();

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getStatus(), Yourdelivery_Model_Order::DELIVERERROR);
    }

}
