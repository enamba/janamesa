<?php

/**
 * Description of Request_UserControllerTest
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Request_UserControllerTest extends Yourdelivery_Test {

    /**
     * try to register with existing user should not throw error
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.01.2012
     */
    public function testRegisterfidelityFail() {
        $this->markTestSkipped('not implemented completly');
        
        $customer = $this->getRandomCustomer();
        $location = $this->getRandomLocation();
        $this->assertFalse($customer->isDeleted());
        #$customer->delete();
        #$this->assertTrue($customer->isDeleted());
        $orderId = $this->placeOrder();

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertNotEquals($customer->getId(), $order->getCustomer()->getId());
        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'orderId' => $order->getId(),
            'sex' => 'n',
            'prename' => $customer->getPrename(),
            'name' => $customer->getName(),
            'street' => $location->getStreet(),
            'hausnr' => $location->getHausnr(),
            'plz' => $location->getPlz(),
            'cityId' => $location->getCityId(),
            'email' => $customer->getEmail(),
            'tel' => $location->getTel(),
            'etage' => $location->getEtage(),
            'comment' => $location->getComment(),
            'password' => '98765432',
            'agb' => '1',
        );
        
        $request->setPost($post);
        $this->dispatch('/request_user/registerfidelity');
        $json = json_decode($this->getResponse()->getBody(), true);
        $this->assertTrue(is_array($json));
        $this->assertTrue(isset($json['error']), implode(',',$json));
    }

}

