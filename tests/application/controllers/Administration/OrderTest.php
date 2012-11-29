<?php

/**
 * Description of OrderTest
 *
 * @author Allen Frank <frank@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Administration_OrderTest extends Yourdelivery_Test {

    protected static $admin = null;
    protected static $db;

    public function setUp() {
        parent::setUp();
        self::$db = Zend_Registry::get('dbAdapter');
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        self::$admin = $session->admin;
        $this->getRequest()->setHeader('Authorization', 'Basic ' . base64_encode('gf:thisishell'));
    }

    /**
     * Test for a mass cancelation containing different orders
     * @author Allen Frank <frank@lieferando.de>
     * @since 29-02-2012
     */
    public function testMassStornoWithoutDiscount() {
        $orderIds = array();
        $orders = array();
        $mixedArray = array();
        $limit = rand(2, 5);
        for ($i = 0; $i < $limit; $i++) {
            $onlinePayment = true;
            switch($i % 4){
                case '0':
                    $payment = 'bar';
                    break;
                case '1':
                    $payment = 'paypal';
                    break;
                case '2':
                    $payment = 'ebanking';
                    break;
                case '3':
                    $payment = 'credit';
                    break;
                default :
                    $payment = 'bar';
            }

            $orderIds[] = $this->placeOrder(array('payment' => $payment));
            $orders[] = new Yourdelivery_Model_Order($orderIds[$i]);
            $this->assertNotEquals(-2, $orders[$i]->getState());
            
            $this->assertEquals(null, $orders[$i]->getRabattCodeId());
            if($i % 2 == 0) {
                $mixedArray[] = $orders[$i]->getNr();
            } else {
                $mixedArray[] = $orders[$i]->getId();
            }
        }
        $post = array(
            'searchfor' => implode("\n", $mixedArray)
        );
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);
        $this->dispatch('/administration_order/massstorno');
        foreach($orders as $key => $order){
            $this->assertEquals(-2, $orders[$key]->getTable()->getState()->status);
        }
    }

    /**
     * Test for a mass cancelation containing different orders
     * @author Allen Frank <frank@lieferando.de>
     * @since 29-02-2012
     */
    public function testMassStornoWithDiscount() {
        $orderIds = array();
        $orders = array();
        $mixedArray = array();
        $limit = rand(2, 5);

        for ($i = 0; $i < $limit; $i++) {
            switch($i % 3){
                case '0':
                    $payment = 'paypal';
                    break;
                case '1':
                    $payment = 'ebanking';
                    break;
                case '2':
                    $payment = 'credit';
                    break;
                default :
                    $payment = 'paypal';
            }

            $orderIds[] = $this->placeOrder(array('payment' => $payment, 'discount' => true));
            $orders[] = new Yourdelivery_Model_Order($orderIds[$i]);
            $this->assertNotEquals(-2, $orders[$i]->getState());
            
            $this->assertNotEquals(null, $orders[$i]->getRabattCodeId());
            if($i % 2 == 0) {
                $mixedArray[] = $orders[$i]->getNr();
            } else {
                $mixedArray[] = $orders[$i]->getId();
            }
        }
        
        $post = array(
            'searchfor' => implode("\n", $mixedArray)
        );
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);
        $this->dispatch('/administration_order/massstorno');
        foreach($orders as $key => $order){
            $this->assertNotEquals(-2, $orders[$key]->getTable()->getState()->status);
        }
        
        $post = array(
            'searchfor' => implode("\n", $mixedArray),
            'cancel'    => true
        );
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);
        $this->dispatch('/administration_order/massstorno');
        foreach($orders as $key => $order){
            $this->assertEquals(-2, $orders[$key]->getTable()->getState()->status);
        }
    }
 
}

?>
