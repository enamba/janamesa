<?php

/**
 * Description of EmailTest
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */

/**
 * @runTestsInSeparateProcesses 
 */
class EmailTest extends Yourdelivery_Test {

    public function tesTemplateBill() {

        $email = new Yourdelivery_Sender_Email_Template('billr');
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());

        $email = new Yourdelivery_Sender_Email_Template('billc');
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateBudgetsharing() {

        $email = new Yourdelivery_Sender_Email_Template('budgetsharing');
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->assign('order', new Yourdelivery_Model_Order($this->placeOrder()));
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateFaxdevel() {


        $email = new Yourdelivery_Sender_Email_Template('faxdevel.txt');
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateFeedback() {

        $email = new Yourdelivery_Sender_Email_Template('feedback');
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->assign('msg', 'message');
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateForgotpw() {

        $email = new Yourdelivery_Sender_Email_Template('forgotpw');
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->assign('pass', 'pass');
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateInformadmin() {

        $email = new Yourdelivery_Sender_Email_Template('informadmin');
        $email->assign('order', new Yourdelivery_Model_Order($this->placeOrder()));
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateOrderiPhone() {

        $email = new Yourdelivery_Sender_Email_Template('iphone');
        $email->assign('code', 'fubar');
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateLoginchanged() {

        $email = new Yourdelivery_Sender_Email_Template('loginchanged');
        $email->assign('customer', $this->getRandomCustomerCompany());
        $email->assign('pass', 'pass');
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateNewpwadmin() {

        $email = new Yourdelivery_Sender_Email_Template('newpwadmin');
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->assign('pass', 'pass');
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateOrder() {

        $email = new Yourdelivery_Sender_Email_Template('order');
        $email->assign('order', new Yourdelivery_Model_Order($this->placeOrder()));
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    /**
     * @todo refactor with dynamic courier order
     */
    public function testTemplateOrdercourier() {
        $this->markTestIncomplete('todo: refactor with dynamic courier');

        $email = new Yourdelivery_Sender_Email_Template('ordercourier');
        $email->assign('order', new Yourdelivery_Model_Order($this->placeOrder()));
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateOrderrest() {

        $email = new Yourdelivery_Sender_Email_Template('orderrest');
        $email->assign('order', new Yourdelivery_Model_Order($this->placeOrder()));
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateProposal() {

        $email = new Yourdelivery_Sender_Email_Template('proposal');
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->assign('msg', 'message');
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateRating() {

        $email = new Yourdelivery_Sender_Email_Template('rating');
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->assign('order', new Yourdelivery_Model_Order($this->placeOrder()));
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateRegister() {

        $email = new Yourdelivery_Sender_Email_Template('register');
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateRegistercompany() {

        $email = new Yourdelivery_Sender_Email_Template('registercompany');
        $email->assign('cust', $this->getRandomCustomerCompany());
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateDiscountCode() {

        $email = new Yourdelivery_Sender_Email_Template('discountcode');
        $email->assign('code', 'fubar');
        $email->assign('discountPath', 'discount');
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateDiscountVerify() {

        $email = new Yourdelivery_Sender_Email_Template('discountverify');
        $email->assign('code', 'fubar');
        $email->assign('discountPath', 'discount');
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }

    public function testTemplateNewsletter() {
        $email = new Yourdelivery_Sender_Email_Template('newsletter_ask_for_confirm');
        $email->assign('cust', $this->getRandomCustomer());
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());

        $email = null;
        $email = new Yourdelivery_Sender_Email_Template('newsletter_confirmed');
        $email->assign('cust', $this->getRandomCustomer());
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.08.2012 
     */
    public function testTemplateStornoNC(){
        $email = new Yourdelivery_Sender_Email_Template('storno_nc.txt');
        $discount = $this->createDiscount();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('discount' => $discount)));
        $email->assign('order', $order);
        $email->assign('absTotal', $order->getAbsTotal());
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
        
        // get latest email entry and check correct content
        $db = $this->_getDbAdapter();
        $latestEmailId = $db->fetchOne('SELECT max(id) from emails');
        $this->assertGreaterThan(0, $latestEmailId);
        
        $this->_loginAdminBackend();
        $this->getRequest();
        $this->dispatch('/administration_email/show/id/'.$latestEmailId);
        $body = $this->getResponse()->getBody();
        
        $orderRow = $this->_getDbAdapter()->fetchRow('SELECT * FROM orders ORDER BY id DESC LIMIT 1');
        $total = $orderRow['total'];
        $discount = $orderRow['discountAmount'];
        $deliverCost = $orderRow['serviceDeliverCost'];
        $courierCost = $orderRow['courierDeliverCost'];
        
        $this->assertTrue(strpos($body, intToPrice($total+$deliverCost-$courierCost-$discount))!==false, sprintf('could not find correct value %s in storno email body %s',intToPrice($total-$discount), $body));
    }
    
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.08.2012 
     */
    public function testTemplateStornoDiscount(){
        $email = new Yourdelivery_Sender_Email_Template('storno_discount.txt');
        $discount = $this->createDiscount();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('discount' => $discount)));
        $email->assign('order', $order);
        $email->assign('absTotal', intToPrice($order->getAbsTotal()));
        $email->addTo('mymail@yourdomain.com');
        $this->assertTrue($email->send());
        
        // get latest email entry and check correct content
        $db = $this->_getDbAdapter();
        $latestEmailId = $db->fetchOne('SELECT max(id) from emails');
        $this->assertGreaterThan(0, $latestEmailId);
        
        $this->_loginAdminBackend();
        $this->getRequest();
        $this->dispatch('/administration_email/show/id/'.$latestEmailId);
        $body = $this->getResponse()->getBody();
        
        $orderRow = $this->_getDbAdapter()->fetchRow('SELECT * FROM orders ORDER BY id DESC LIMIT 1');
        $total = $orderRow['total'];
        $discount = $orderRow['discountAmount'];
        $deliverCost = $orderRow['serviceDeliverCost'];
        $courierCost = $orderRow['courierDeliverCost'];
        
        $this->assertTrue(strpos($body, intToPrice($total+$deliverCost-$courierCost-$discount))!==false, sprintf('could not find correct value %s in storno email body %s',intToPrice($total-$discount), $body));
    }
    
    
    
    private function _loginAdminBackend(){
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
    }

}