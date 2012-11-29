<?php

/**
 * Description of TransactionsTest
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Yourdelivery_Model_DbTable_Paypal_TransactionsTest extends Yourdelivery_Test {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.10.2011
     */
    public function testGetPayerId() {


        $orderId = $this->placeOrder(array('payment' => "paypal"));


        $db = Zend_Registry::get('dbAdapter');

        $payerId = "TESTINGPAYERID" . Default_Helpers_Random::color();

        $data = array('orderId' => $orderId,
            'params' => serialize(array('METHOD' => "TESTING")),
            'response' => serialize(array('TOKEN' => 'TESTING_TOKEN')),
            'payerId' => $payerId
        );

        $db->insert('paypal_transactions', $data);

        $t = new Yourdelivery_Model_DbTable_Paypal_Transactions();

        $result = $t->getPayerId($orderId);

        $this->assertEquals($payerId, $result);
    }

}

?>
