<?php

/**
 * @author mpantar
 * @since 03.03.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class AnonymTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();
    }

    /**
     * test if get the full name
     * @author mpantar
     * @since 03.03.2011
     */
    public function testGetFullname() {
         

        $anonym = new Yourdelivery_Model_Anonym();
        $anonym->setData(array(
            'prename' => 'Matej',
            'name' => 'Pantar'
        ));
        $this->assertEquals('Matej Pantar', $anonym->getFullname());

        $anonymEmpty = new Yourdelivery_Model_Anonym();
        $this->assertEquals('Unbekannt', $anonymEmpty->getFullname());
    }

    /**
     *@author mpantar
     * @since 07.03.2011
     */
    public function testGetShortedName() {
         

        $anonym = new Yourdelivery_Model_Anonym();
        $anonym->setData(array(
            'prename' => 'Matej',
            'name' => 'Pantar'
        ));

        $this->assertTrue('Matej Pan.' == $anonym->getShortedName());
        $this->assertTrue('Matej Pantar' != $anonym->getShortedName());
        #$this->assertTrue('Matej Pantar' != $anonym->getShortedName());
    }

    /**
     *@author mpantar
     * @since 07.03.2011
     */
    public function testGetSalutation() {
         

        $anonym = new Yourdelivery_Model_Anonym();

        $this->assertEquals(__('Sehr geehrte(r) ').__('Unbekannt'), $anonym->getEmailSalutation());
        $this->assertEquals(__('Liebe(r) ').__('Unbekannt'), $anonym->getPersonalEmailSalutation());

        $anonym->setData(array('sex' => 'm'));

        $this->assertEquals(__('Sehr geehrter Herr ').__('Unbekannt'), $anonym->getEmailSalutation());
        $this->assertEquals(__('Lieber Herr ').__('Unbekannt'), $anonym->getPersonalEmailSalutation());

        $anonym->setData(array('sex' => 'w'));

        $this->assertEquals(__('Sehr geehrte Frau ').__('Unbekannt'), $anonym->getEmailSalutation());
        $this->assertEquals(__('Liebe Frau ').__('Unbekannt'), $anonym->getPersonalEmailSalutation());

    }

    /**
     * @author mpantar
     * @since 07.03.2011
     */
    public function testGetId() {

        $anonym = new Yourdelivery_Model_Anonym();
        $time = time();

        $this->assertEquals($time, $anonym->getId());
    }

    /**
     *@author mpantar
     * @since 07.03.2011
     */
    public function testGetTable() {
         

        $anonym = new Yourdelivery_Model_Anonym();
        $this->assertNull($anonym->getTable());
    }

    /**
     *@author mpantar
     * @since 07.03.2011
     */
    public function testIsLoggedIn() {
         

        $anonym = new Yourdelivery_Model_Anonym();
        $this->assertFalse($anonym->isLoggedIn());
    }

    public function testSetPersistentNotfication() {
         

        $anonym = new Yourdelivery_Model_Anonym();
        $this->assertNull($anonym->setPersistentNotfication());
    }

    /**
     * @author mpantar
     * @since 07.03.2011
     */
    public function testForgottenPass() {
         

        $anonym = new Yourdelivery_Model_Anonym();


        $this->assertEquals(1, $anonym->forgottenPass(null));
        $this->assertTrue(2 != $anonym->forgottenPass(null));

        $anonym = $this->createCustomer();

        $pass = $anonym->getPassword();
        $email = $anonym->getEmail();

        $anonym->forgottenPass($email);

        $customer = new Yourdelivery_Model_Customer(null, $email);

        $this->assertFalse($pass == $customer->getPassword());


        // check if email was sent to customer
        // TODO
    }

    /**
     *@author mpantar
     * @since 07.03.2011
     */
    public function testNewPassAdmin() {
         

        $anonym = new Yourdelivery_Model_Anonym();

        $anonym->setData(array(
            'email' => ''
        ));
        $this->assertEquals(1, $anonym->newPassAdmin());

        $anonym->setData(array(
            'email' => 'testasd.de'
        ));
        $this->assertEquals(1, $anonym->newPassAdmin());

        $anonym = $this->createCustomer();

        $pass = $anonym->getPassword();
        $email = $anonym->getEmail();
        $anonym->forgottenPass($email);

        $customer = new Yourdelivery_Model_Customer(null, $email);

        $this->assertNotEquals($pass, $customer->getPassword());
    }

    /**
     *@author mpantar
     * @since 07.03.2011
     */
    public function testIsRegistered() {
         

        $anonym = new Yourdelivery_Model_Anonym();
        $this->assertFalse($anonym->isRegistered());
    }

    /**
     *@author mpantar
     * @since 07.03.2011
     */
    public function testIsPremium() {
         
        
        $anonym = new Yourdelivery_Model_Anonym();
        $this->assertTrue($anonym->isPremium());
    }
}