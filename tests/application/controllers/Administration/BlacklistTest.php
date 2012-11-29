<?php

/**
 * Blacklist backend management test suite
 *
 * @author Matthias Laug <laug@lieferando.de>
 * 
 * @runTestsInSeparateProcesses
 */
class Administration_BlacklistTest extends Yourdelivery_Test {
    /**
     * Logs in to backend for each test
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 17.07.2012
     */
    public function setUp() {
        parent::setUp();
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $this->getRequest()->setHeader('Authorization', 'Basic ' . base64_encode('gf:thisishell'));

    }

    /**
     * Checks whether adding a new blacklist value works
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 17.07.2012
     */
    public function testBlacklistEmailAction() {

        //truncate blacklist values
        $db = Zend_Registry::get('dbAdapter');
        $db->query('TRUNCATE blacklist');
        $db->query('TRUNCATE blacklist_values');
        
        $orderId = $this->placeOrder();
        $this->assertGreaterThan(0, $orderId);
        $valid = array(
            'bl_email' => 'matthias.laug@gmail.com',
            'bl_orderId' => $orderId,
            'bl_comment' => 'my personal blacklist comment',
            'bl_minutemailer' => 1,
            'bl_cancelorder' => 1
        );

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($valid);
        $this->dispatch('/administration_blacklist/email');
        $this->assertRedirect('/administration_blacklist/email');

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getState(), Yourdelivery_Model_Order_Abstract::FAKE_STORNO);

        $blacklist = new Yourdelivery_Model_Support_Blacklist(1); //must be one since we truncated the table before :)
        $resultingTypes = array(
            Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL,
            Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL_MINUTEMAILER
        );
        $this->assertEquals(count($resultingTypes), count($blacklist->getValues()));
        foreach ($blacklist->getValues() as $type => $value) {
            $this->assertTrue(in_array($type, $resultingTypes));
            if ( $type == Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_EMAIL ){
                $this->assertEquals($value->getValue(), 'matthias.laug@gmail.com');
            }
            if ( $type == Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL_MINUTEMAILER ){
                $this->assertEquals($value->getValue(), 'gmail.com');
            }
            
        }
    }
    
    /**
     * Checks whether adding an existing blacklist value does not cause an error
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 17.07.2012
     * 
     * @depends testBlacklistEmailAction
     */
    public function testBlacklistEmailActionDuplicateEntry() {
        
        //testcase before is testBlacklistEmailAction and therefore this request should fail
        
        $orderId = $this->placeOrder();
        $this->assertGreaterThan(0, $orderId);
        $valid = array(
            'bl_email' => 'matthias.laug@gmail.com',
            'bl_orderId' => $orderId,
            'bl_comment' => 'my personal blacklist comment',
            'bl_minutemailer' => 1,
            'bl_cancelorder' => 1
        );

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($valid);
        $this->dispatch('/administration_blacklist/email');
        $this->assertRedirect('/administration_blacklist/email');

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getState(), Yourdelivery_Model_Order_Abstract::FAKE_STORNO);
        
        //count in db should still be 2
        $db = Zend_Registry::get('dbAdapter');
        $this->assertEquals(2, current($db->fetchCol('select count(*) from blacklist_values')));
        
        // cleanup - truncate blacklist values
        $db = Zend_Registry::get('dbAdapter');
        $db->query('TRUNCATE blacklist');
        $db->query('TRUNCATE blacklist_values');
    }

}
