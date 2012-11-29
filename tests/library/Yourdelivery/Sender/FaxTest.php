<?php

/**
 * @author mlaug
 * @since 03.11.2010
 * 
 * @runTestsInSeparateProcesses
 */
class YourdeliverySenderFaxTest extends Yourdelivery_Test {

    /**
     * place an order and provide a report xml from retarus, with status OK
     * order should be affirmed after reading this report
     * @author mlaug
     * @since 24.11.2010
     */
    public function testProcessSuccessfulReports() {
        
        //create dir first, if not exists
        @mkdir(APPLICATION_PATH . sprintf('/../storage/fax/reports/done'), 0755, true);
        
        $config = Zend_Registry::get('configuration');
        $config->sender->fax->method = "ftp";
        $domain = $config->domain->base;
        
        $orderId = (integer) $this->placeOrder(array('checkForFraud' => false));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(0,(integer) $order->getState(), sprintf('state of order #%d is != 0. Here is last log: ', $order->getId(), Default_Helpers_Log::getLastLog()));
        $this->assertGreaterThan(0,$orderId);
        
        $faxReport = APPLICATION_PATH . '/../tests/data/faxreport.xml';
        $faxReportForOrder = APPLICATION_PATH . sprintf('/../storage/fax/reports/rep-order-%d-1290604452-%s.xml', $orderId, $domain);
        $faxReportForOrderDone = APPLICATION_PATH . sprintf('/../storage/fax/reports/done/%s/rep-order-%d-1290604452-%s.xml', date('d-m-Y'), $orderId, $domain);
        
        $this->assertTrue(file_exists($faxReport));
        copy($faxReport, $faxReportForOrder);
        $this->assertTrue(file_exists($faxReportForOrder));
        $this->assertEquals(md5(file_get_contents($faxReport)), md5(file_get_contents($faxReportForOrder)));
        
        unset($order);
        $fax = new Yourdelivery_Sender_Fax();
        $fax->processReports();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(1,$order->getState());        
        $this->assertTrue(file_exists($faxReportForOrderDone));
        $this->assertFalse(file_exists($faxReportForOrder));
        
        //check it for the wrong domain
        $wrong_domain = 'wrong.de';
        
        $faxReport = APPLICATION_PATH . '/../tests/data/faxreport.xml';
        $faxReportForOrder = APPLICATION_PATH . sprintf('/../storage/fax/reports/rep-order-%d-1290604452-%s.xml', $orderId, $wrong_domain);
        $faxReportForOrderDone = APPLICATION_PATH . sprintf('/../storage/fax/reports/done/%s/rep-order-%d-1290604452-%s.xml', date('d-m-Y'), $orderId, $wrong_domain);
        
        $this->assertTrue(file_exists($faxReport));
        copy($faxReport, $faxReportForOrder);
        $this->assertTrue(file_exists($faxReportForOrder));
        $this->assertEquals(md5(file_get_contents($faxReport)), md5(file_get_contents($faxReportForOrder)));
        
        $fax = new Yourdelivery_Sender_Fax();
        $fax->processReports();
        $this->assertFalse(file_exists($faxReportForOrderDone));
        $this->assertFalse(file_exists($faxReportForOrder));
    }
    
    /**
     * we place an order and check that the status is 0. Then we provide an 
     * retarus fax report, which holds a status != OK. This should add the error
     * state to the order
     * @author mlaug
     * @since 24.11.2010
     */
    public function testProcessErrorReports() {
        
        $config = Zend_Registry::get('configuration');
        $domain = $config->domain->base;
        $config->sender->fax->method = "ftp";
        $orderId = (integer) $this->placeOrder(array('checkForFraud' => false));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(0,$order->getState());
        $this->assertGreaterThan(0,$orderId);
        
        $faxReport = APPLICATION_PATH . '/../tests/data/faxreport_with_error.xml';
        $faxReportForOrder = APPLICATION_PATH . sprintf('/../storage/fax/reports/rep-order-%d-1290604452-%s.xml', $orderId, $domain);
        $faxReportForOrderDone = APPLICATION_PATH . sprintf('/../storage/fax/reports/done/%s/rep-order-%d-1290604452-%s.xml', date('d-m-Y'), $orderId, $domain);
        
        $this->assertTrue(file_exists($faxReport));
        copy($faxReport, $faxReportForOrder);
        $this->assertTrue(file_exists($faxReportForOrder));
        $this->assertEquals(md5(file_get_contents($faxReport)), md5(file_get_contents($faxReportForOrder)));
        
        unset($order);
        $fax = new Yourdelivery_Sender_Fax();
        $fax->processReports();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(-1,$order->getState());        
        $this->assertTrue(file_exists($faxReportForOrderDone));
        $this->assertFalse(file_exists($faxReportForOrder));
        
        
        //check it for the wrong domain
        $wrong_domain = 'wrong.de';
        $faxReport = APPLICATION_PATH . '/../tests/data/faxreport_with_error.xml';
        $faxReportForOrder = APPLICATION_PATH . sprintf('/../storage/fax/reports/rep-order-%d-1290604452-%s.xml', $orderId, $wrong_domain);
        $faxReportForOrderDone = APPLICATION_PATH . sprintf('/../storage/fax/reports/done/%s/rep-order-%d-1290604452-%s.xml', date('d-m-Y'), $orderId, $wrong_domain);
        
        $this->assertTrue(file_exists($faxReport));
        copy($faxReport, $faxReportForOrder);
        $this->assertTrue(file_exists($faxReportForOrder));
        $this->assertEquals(md5(file_get_contents($faxReport)), md5(file_get_contents($faxReportForOrder)));
        
        $fax = new Yourdelivery_Sender_Fax();
        $fax->processReports(); 
        $this->assertFalse(file_exists($faxReportForOrderDone));
        $this->assertFAlse(file_exists($faxReportForOrder));
        
    }
    
    /**
     * we place an order and check that the status is 0. Then we provide an 
     * retarus fax report, which holds a status = NO_TRAIN. This should add the error
     * state to the order
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 14.09.2011
     */
    public function testProcessNoTrainReports() {
        
        $config = Zend_Registry::get('configuration');
        $domain = $config->domain->base;
        $config->sender->fax->method = "ftp";
        $orderId = (integer) $this->placeOrder(array('checkForFraud' => false));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(0,$order->getState());
        $this->assertGreaterThan(0,$orderId);
        
        $faxReport = APPLICATION_PATH . '/../tests/data/faxreport_no_train.xml';
        $faxReportForOrder = APPLICATION_PATH . sprintf('/../storage/fax/reports/rep-order-%d-1290604452-%s.xml', $orderId, $domain);
        $faxReportForOrderDone = APPLICATION_PATH . sprintf('/../storage/fax/reports/done/%s/rep-order-%d-1290604452-%s.xml', date('d-m-Y'), $orderId, $domain);
        
        $this->assertTrue(file_exists($faxReport));
        copy($faxReport, $faxReportForOrder);
        $this->assertTrue(file_exists($faxReportForOrder));
        $this->assertEquals(md5(file_get_contents($faxReport)), md5(file_get_contents($faxReportForOrder)));
        
        unset($order);
        $fax = new Yourdelivery_Sender_Fax();
        $fax->processReports();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(-15,$order->getState());        
        $this->assertTrue(file_exists($faxReportForOrderDone));
        $this->assertFalse(file_exists($faxReportForOrder));
               
        
    }
}
