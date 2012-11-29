<?php

/**
 * Description of Order_FavoriteTest
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Order_FavoriteTest extends Yourdelivery_Test {
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     */
    public function testAddWithoutName() {
        $customer = $this->getRandomCustomer();
        $orderId = $this->placeOrder(array('customer' => $customer));
        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertTrue($order->addToFavorite($customer));
        // check last db-entry
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow("SELECT * FROM favourites ORDER BY id DESC LIMIT 1");
        $this->assertEquals($customer->getId(), $row['customerId']);
        $this->assertEquals($order->getId(), $row['orderId']);
        $this->assertEquals($order->getService()->getName() . " " . date("d.m.Y", $order->getTime()), $row['name']);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     */
    public function testAddWithName() {
        $customer = $this->getRandomCustomer();
        $orderId = $this->placeOrder(array('customer' => $customer));
        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertTrue($order->addToFavorite($customer, 'foobar'));

        // check last db-entry
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow("SELECT * FROM favourites ORDER BY id DESC LIMIT 1");
        $this->assertEquals($customer->getId(), $row['customerId']);
        $this->assertEquals($order->getId(), $row['orderId']);
        $this->assertEquals('foobar', $row['name']);
    }

     /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     */
    public function testDontAddFavoriteForUnregisteredCustomer() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertFalse($order->addToFavorite());
        $this->assertFalse($order->isFavourite());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 13.12.2011
     */
    public function testDelete() {
        $customer = $this->getRandomCustomer();
        $orderId = $this->placeOrder(array('customer' => $customer));
        $order = new Yourdelivery_Model_Order($orderId);
        $fav = new Yourdelivery_Model_Order_Favorite();
        
        $this->assertTrue($fav->add($order->getId(), $order->getCustomer()->getId()));
        $this->assertTrue($order->isFavourite());
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $row = $db->fetchRow("SELECT * FROM favourites ORDER BY id DESC LIMIT 1");
        $this->assertEquals($order->getId(), $row['orderId']);
        
        $fav = new Yourdelivery_Model_Order_Favorite($row['id']);
        $this->assertTrue($fav->delete());
        $this->assertFalse($order->isFavourite());
    }
    
    
    /**
     * - place 3 orders; 
     * - 2 at the same restaurant
     * - mark all as favorite
     * - delete all favorites from identical restaurant
     * - third favorite should not be deleted
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 13.12.2011
     */
    public function testDeleteAllFromOneRestaurant(){
       $rest1 = $this->getRandomService();
       $rest2 = $this->getRandomService();
       $customer = $this->getRandomCustomer();
       $this->assertNotEquals($rest1->getId(), $rest2->getId());
       
       $order1 = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer, 'service' => $rest1)));
       $this->assertTrue($order1->addToFavorite($customer));
       $this->assertTrue($order1->isFavourite());
       
       $order2 = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer, 'service' => $rest1)));
       $this->assertTrue($order2->addToFavorite($customer));
       $this->assertTrue($order2->isFavourite());
       
       $order3 = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer, 'service' => $rest2)));
       $this->assertTrue($order3->addToFavorite($customer));
       $this->assertTrue($order3->isFavourite());
       
       Yourdelivery_Model_DbTable_Favourites::removeAll($rest1->getId(), $customer->getId());
       $this->assertFalse($order1->isFavourite());
       $this->assertFalse($order2->isFavourite());
       $this->assertTrue($order3->isFavourite());
       
    }

}

?>
