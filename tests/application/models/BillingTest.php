<?php
/**
 * @author vpriem
 * @since 02.11.2010
 */
/**
 * @runTestsInSeparateProcesses 
 */
class BillingTest extends Yourdelivery_Test{

    public function setUp() {
        parent::setUp();
    }

    /**
     * @author vpriem
     * @since 02.11.2010
     */
    public function testCreateFromHash(){
        $bill = Yourdelivery_Model_Billing::createFromHash(9149);
        $this->assertFalse($bill);

        $bill = Yourdelivery_Model_Billing::createFromHash("18c2f2438fc778dae75c9703319b6b07");
        $this->assertTrue($bill instanceof Yourdelivery_Model_Billing);
    }

    /**
     * check if we can reset a bill
     * @author mlaug
     * @since 24.03.2011
     */
    public function testResetBill(){
        $db = Zend_Registry::get('dbAdapter');

        $row = $db->fetchOne('select id from billing where mode="rest" and voucher < 100 and amount = 0 order by RAND() desc limit 1');
        $this->assertTrue(Yourdelivery_Model_Billing::rebuild($row));
        unset($row);

        $row = $db->fetchOne('select id from billing where mode="rest" and voucher = 0 and amount < 100 order by RAND() desc limit 1');
        $this->assertTrue(Yourdelivery_Model_Billing::rebuild($row));
        unset($row);

        $row = $db->fetchOne('select id from billing where mode="company" and refId not in (1260,1271,1270,1295,1443) and amount < 250 order by RAND() desc limit 1');
        $this->assertTrue(Yourdelivery_Model_Billing::rebuild($row));
        unset($row);

        $row = $db->fetchOne('select id from orders where kind="priv" and state>0 order by RAND() desc limit 1');
        $order = new Yourdelivery_Model_Order($row);
        $bill = new Yourdelivery_Model_Billing_Order($order->getCustomer());
        $bill->addOrder($order);
        $this->assertTrue($bill->create());
        unset($row);

        $this->markTestIncomplete('We ave to refactor here');
        
        $row = $db->fetchOne('select id from billing where mode="courier" order by RAND() desc limit 1');
        $this->assertTrue(Yourdelivery_Model_Billing::rebuild($row));
        unset($row);
    }

    public function testSendBillViaEmail(){
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchOne('select id from billing where mode="company" and refId not in (1260,1271,1270,1295,1443) and amount < 250 order by RAND() desc limit 1');
        $this->assertTrue(Yourdelivery_Model_Billing::rebuild($row));
        $bill = new Yourdelivery_Model_Billing($row);
        $this->assertTrue($bill->sendViaEmail('testing@lieferando.de'));

        unset($bill);
        $bill = new Yourdelivery_Model_Billing($row);
        $this->assertGreaterThan(0,$bill->getStatus());
    }

    public function testSendBillViaPost(){
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchOne('select id from billing where mode="company" and refId not in (1260,1271,1270,1295,1443) and amount < 250 order by RAND() desc limit 1');
        $this->assertTrue(Yourdelivery_Model_Billing::rebuild($row));
        $bill = new Yourdelivery_Model_Billing($row);
        $this->assertTrue($bill->sendViaPost());

        unset($bill);
        $bill = new Yourdelivery_Model_Billing($row);
        $this->assertGreaterThan(0,$bill->getStatus());
    }

    public function testSendBillViaFax(){
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchOne('select id from billing where mode="company" and refId not in (1260,1271,1270,1295,1443) and amount < 250 order by RAND() desc limit 1');
        $this->assertTrue(Yourdelivery_Model_Billing::rebuild($row));
        $bill = new Yourdelivery_Model_Billing($row);
        $this->assertTrue($bill->sendViaFax('123123123'));

        unset($bill);
        $bill = new Yourdelivery_Model_Billing($row);
        $this->assertGreaterThan(0,$bill->getStatus());
    }

    public function testGetVoucherAmounts(){
        $service = $this->getRandomService();
        $this->assertNotNull(Yourdelivery_Model_Billing::getVoucherAmounts($service->getId(),2));
    }

    /**
     * @author Alex Vait
     * @since 12.07.2012
     */
    public function testGetHistory(){        
        $this->markTestIncomplete('We ave to refactor here');
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchOne('select id from billing order by RAND() desc limit 1');
        $billing = new Yourdelivery_Model_Billing($row);

        $status = array_rand(Yourdelivery_Model_Billing_Abstract::getStatusse());
        $adminId= rand(10,10000);
        
        $billing->setStatus($status, $adminId);
        
        // reverse the array so we have the newest element first
        $stateHistory = array_reverse($billing->getStateHistory());
        $actualState = $stateHistory[0];
        $this->assertEquals($actualState['status'], $status);
        $this->assertEquals($actualState['adminId'], $adminId);
    }    
}
