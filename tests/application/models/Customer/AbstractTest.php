<?php
/**
 * @runTestsInSeparateProcesses 
 */
class CustomerAbstractTest extends Yourdelivery_Test {

    /**
     * Testing whether isPremium function works correctly or not.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 03.04.2012                                                                                      
     */
    public function testIsPremium() {
        $customer = $this->getRandomCustomer();
        $this->assertTrue($customer->isPremium());
    }

    /**
     * Testing whether getFidelity() function works correctly or not.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 03.04.2012                                                                                      
     */
    public function testGetFidelity() {
        $customer = $this->getRandomCustomer();
        $this->assertInstanceof('Yourdelivery_Model_Customer_Fidelity', $customer->getFidelity());
    }

    /**
     * Testing if getFidelity() function returns Null when email is Null.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 03.04.2012                                                                                  
     */
    public function testGetFidelityNoemail() {
        $customer = $this->getRandomCustomer();
        $customer->setData(array('email' => NULL));
        $this->assertNull($customer->getFidelity());
    }

    /**
     * Testing if getCustomerNr() function returns flase when name is empty.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 03.04.2012                                                                                  
     */
    public function testgetCustomerNr() {
        $customer = $this->createCustomer();
        $customer->setData(array('name' => ""));
        $this->assertEquals(0, $customer->getCustomerNr());
    }

    /**
     * Testing if forgottenPass($email) function returns 2 when customer is deleted.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 03.04.2012                                                                                  
     */
    public function testforgottenPassDeleted() {
        $customer = $this->getRandomCustomer();
        $email = $customer->getEmail();
        $customer->delete();
        $this->assertEquals(2, $customer->forgottenPass($email));
    }

    /**
     * Testing if isInNewsletterRecipients() function returns false when no email is inserted.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 03.04.2012                                                                                  
     */
    public function testisInNewsletterRecipientsNoemail() {
        $customer = $this->getRandomCustomer();
        $customer->setData(array('email' => ''));
        $this->assertEquals(0, $customer->isInNewsletterRecipients());
    }

    /**
     * Testing if getNewsletter() function returns false when no email is inserted.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 03.04.2012                                                                                  
     */
    public function testgetNewsletterNoemail() {
        $customer = $this->getRandomCustomer();
        $customer->setData(array('email' => ''));
        $this->assertEquals(0, $customer->getNewsletter());
    }

    /**
     * Testing if setNewsletter() function returns true when $reason is set.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 03.04.2012                                                                                  
     */
    public function testsetNewsletterWithReason() {
        $customer = $this->getRandomCustomer();
        $this->assertTrue($customer->setNewsletter(true, true, true));
    }

    /**
     * Testing if getFullname() function returns full name.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 03.04.2012                                                                                  
     */
    Public function testgetFullname() {
        $customer = $this->getRandomCustomer();
        $name = $customer->getPrename() . ' ' . $customer->getName();
        $this->assertEquals($name, $customer->getFullname());
    }

    /**
     * Testing if getFullname() function returns Unbekannt when name is empty.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 03.04.2012                                                                                  
     */
    public function testgetFullnameEmpty() {
        $customer = $this->getRandomCustomer();
        $customer->setData(array('name' => '', 'prename' => ''));
        $this->assertEquals(__('Unbekannt'), $customer->getFullname());
    }

    /**
     * Testing if getShortedName() function returns shorted full name and if it get 3 chars from lastname if its long.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 03.04.2012                                                                                  
     */
    public function testgetShortedName() {
        $customer = $this->getRandomCustomer();
        $name = $customer->getPrename() . ' ' . (strlen($customer->getName()) > 3 ? substr($customer->getName(), 0, 3) . '.' : $customer->getName());
        $this->assertEquals($name, $customer->getShortedName());
    }

    /**
     * Testing if getNickname() function returns Nickname or Prename when Nickname is empty.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 04.04.2012                                                                                  
     */
    public function testgetNickname() {
        $customer = $this->getRandomCustomer();
        $data = $customer->getData();
        $nick = $data['nickname'];
        $nick == '' ? $nick = $data['prename'] : $nick;
        $this->assertEquals($nick, $customer->getNickname());
    }

    /**
     * Testing if getFirstAndLastAndCountOrders() function returns First, Last, and Count of orders.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>                                                      
     * @since 04.04.2012                                                                                  
     */
    public function testgetFirstAndLastAndCountOrders() {
        $customer = $this->getRandomCustomer();
        $email = $customer->getEmail();
        $expected = array();
        $res = 0;
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()->from(
                        array('o' => 'orders'), array(
                    'countOrders' => new Zend_Db_Expr('COUNT(o.id)'),
                    'deliverTimeFirstOrder' => new Zend_Db_Expr('MIN(o.deliverTime)'),
                    'deliverTimeLastOrder' => new Zend_Db_Expr('MAX(o.deliverTime)')
                ))
                ->join(array('oc' => 'orders_customer'), 'oc.orderId = o.id', array())
                ->join(array('ol' => 'orders_location'), "ol.orderId = o.id", array())
                ->where('o.state > 0')
                ->where('o.kind = "priv"')
                ->where('o.mode = "rest"')
                ->where('oc.email = ?', $email);

        $data = $db->fetchRow($select);
        $expected = $customer->getFirstAndLastAndCountOrders($email);
        $data[1] === $expected[1] && $data[2] === $expected[2] && $data[3] === $expected[3] ? $res = 1 : $res;
        $this->assertEquals(1, $res);
    }

}

?>
