<?php

/**
 * @author mlaug
 */

/**
 * @runTestsInSeparateProcesses 
 */
class CustomerTest extends Yourdelivery_Test {

    /**
     * @author mpantar
     */
    public function testAnonymous() {
        $customer = new Yourdelivery_Model_Anonym();
        $this->assertFalse($customer->isLoggedIn());
        $this->assertNull($customer->getLocations());
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.01.2012
     */
    public function testGetLastOrder() {
        // privat
        $customer = $this->getRandomCustomer(false, true);
        $orderId = $this->placeOrder(array('customer' => $customer));
        $order = new Yourdelivery_Model_Order($orderId);
        $lastOrder = $customer->getLastOrder();
        $this->assertTrue($lastOrder instanceof Yourdelivery_Model_Order);
        $this->assertEquals($order->getId(), $lastOrder->getId());

        $lastOrders = $customer->getLastOrder(25);
        $this->assertTrue(is_array($lastOrders));
        $this->assertTrue($lastOrders[0] instanceof Yourdelivery_Model_Order);
        $this->assertEquals($order->getId(), $lastOrders[0]->getId(), count($lastOrders));

        // company
        $customer = $this->getRandomCustomer(true, true);
        $orderId = $this->placeOrder(array('kind' => "comp", 'customer' => $customer));
        $order = new Yourdelivery_Model_Order($orderId);
        $lastOrder = $customer->getLastOrder(1, 'rest', 'comp');
        $this->assertTrue($lastOrder instanceof Yourdelivery_Model_Order);
        $this->assertEquals($order->getId(), $lastOrder->getId());

        $lastOrders = $customer->getLastOrder(25, 'rest', 'comp');
        $this->assertTrue(is_array($lastOrders));
        $this->assertTrue($lastOrders[0] instanceof Yourdelivery_Model_Order);
        $this->assertEquals($order->getId(), $lastOrders[0]->getId(), count($lastOrders));
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.01.2012
     */
    public function testEditAdress() {
        $customer = $this->getRandomCustomer(false, true);
        $this->assertFalse($customer->editAddress(array('street' => "Paperstreet"), 999999999999));

        $location = $this->getRandomLocation();
        $this->assertFalse($customer->editAddress(array('street' => "Paperstreet"), $location->getId()));

        $location = $this->getRandomLocation($customer->getId());
        $this->assertTrue($customer->editAddress(array('street' => "Paperstreet"), $location->getId()));

        $location = new Yourdelivery_Model_Location($location->getId());
        $this->assertEquals($location->getStreet(), "Paperstreet");
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 14.04.2011
     */
    public function testEmailSalutation() {
        $anonym = new Yourdelivery_Model_Anonym();

        $anonym->setPrename('Felix');
        $anonym->setName('Haferkorn');

        // no sex
        $this->assertEquals($anonym->getEmailSalutation(), __('Sehr geehrte(r)') . ' Felix Haferkorn');
        $this->assertEquals($anonym->getPersonalEmailSalutation(), __('Liebe(r)') . ' Felix Haferkorn');

        // male
        $anonym->setSex('m');
        $this->assertEquals($anonym->getEmailSalutation(), __('Sehr geehrter Herr') . ' Felix Haferkorn');
        $this->assertEquals($anonym->getPersonalEmailSalutation(), __('Lieber Herr') . ' Felix Haferkorn');

        // female
        $anonym->setSex('w');
        $anonym->setPrename('Mareike');
        $anonym->setName('Hat die Hosen an');

        $this->assertEquals($anonym->getEmailSalutation(), __('Sehr geehrte Frau') . ' Mareike Hat die Hosen an');
        $this->assertEquals($anonym->getPersonalEmailSalutation(), __('Liebe Frau') . ' Mareike Hat die Hosen an');
    }

    /**
     * @author mlaug
     * @since 26.10.2010
     */
    public function testCustomer() {
        //creation using id
        $customer = $this->getRandomCustomer();
        $this->assertTrue($customer->isLoggedIn());
        unset($customer);

        //creating using email
        $customer = new Yourdelivery_Model_Customer(null, $this->getRandomCustomer()->getEmail());
        $this->assertTrue($customer->isLoggedIn());

        //create using salt (api)
        $customer_sec = new Yourdelivery_Model_Customer(null, null, $customer->getSalt());
        $this->assertTrue($customer_sec->isLoggedIn());
        $this->assertEquals($customer->getId(), $customer_sec->getId());
    }

    /**
     * @author mlaug
     * @since 02.10.2010
     */
    public function testForgottenPassword() {
        $customer = $this->getRandomCustomer();
        $oldPass = $customer->getPassword();
        $customer->resetPassword();
        $this->assertNotEquals($oldPass, $customer->getPassword());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.03.2012
     */
    public function testNewsletter() {
        $config = Zend_Registry::get('configuration');
        $config->newsletter->method = 'singleoptin';
        Zend_Registry::set('configuration', $config);

        $customer = $this->createCustomer();
        $customer->setNewsletter(false, false);
        $this->assertFalse($customer->getNewsletter());
        $customer->setNewsletter(true, false);
        $this->assertTrue($customer->getNewsletter());
        $customer->setNewsletter(false);
        $this->assertFalse($customer->getNewsletter());

        $config->newsletter->method = 'doubleoptin';
        Zend_Registry::set('configuration', $config);
        $customer = $this->createCustomer();
        $customer->setNewsletter(false, false);
        $this->assertFalse($customer->getNewsletter());
        $customer->setNewsletter(true, false);
        $this->assertFalse($customer->getNewsletter());
        $customer->setNewsletter(true, true);
        $this->assertTrue($customer->getNewsletter());
        $customer->setNewsletter(false);
        $this->assertFalse($customer->getNewsletter());

        $config->newsletter->method = 'singleoptin';
        Zend_Registry::set('configuration', $config);

        $customer = $this->getRandomCustomer();
        $customer->setNewsletter(false, false);
        $this->assertFalse($customer->getNewsletter());
        $customer->setNewsletter(true, false);
        $this->assertTrue($customer->getNewsletter());
        $customer->setNewsletter(false);
        $this->assertFalse($customer->getNewsletter());

        $config->newsletter->method = 'doubleoptin';
        Zend_Registry::set('configuration', $config);
        $row = Yourdelivery_Model_DbTable_Newsletterrecipients::findByEmail($customer->getEmail());
        $currentAffirmed = (boolean) $row['affirmed'];
        $customer = $this->getRandomCustomer();
        $customer->setNewsletter(false, false);
        $this->assertFalse($customer->getNewsletter());
        $customer->setNewsletter(true, false);
        $this->assertFalse($customer->getNewsletter() && !$currentAffirmed);
        $customer->setNewsletter(true, true);
        $this->assertTrue($customer->getNewsletter());
        $customer->setNewsletter(false);
        $this->assertFalse($customer->getNewsletter());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de> 
     */
    public function testIsInNewsletterRecipients() {
        $customer = $this->createCustomer();
        $this->assertFalse($customer->isInNewsletterRecipients());
        $customer->setNewsletter(true);
        $this->assertTrue($customer->isInNewsletterRecipients());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.02.2011
     */
    public function testAllowNoDebitForNotRegistered() {
        $anonym = new Yourdelivery_Model_Anonym();
        $this->assertFalse($anonym->allowDebit());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.02.2011
     */
    public function testCustomerSetPermanentDiscount() {
        // create permanent discount
        $discount = $this->createDiscount(false, 0, 10, false, false, true);
        $customer = $this->createCustomer();
        $this->assertTrue($customer->setDiscount($discount) > 0);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.02.2011
     */
    public function testCustomerGetPermanentDiscount() {
        // create permanent discount
        $discount = $this->createDiscount(false, 0, 10, false, false, true);
        $customer = $this->createCustomer();
        $customer->setDiscount($discount);

        $this->assertTrue($customer->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.02.2011
     */
    public function testCustomerGetNotUsablePermanentDiscount() {
        // create permanent discount
        $code = $this->createDiscount(false, 0, 10, false, false, true);
        $customer = $this->createCustomer();
        $customer->setDiscount($code);

        $rabattCode = $customer->getDiscount();
        $this->assertTrue($rabattCode->isUsable());

        $code->getParent()->setData(array(
            'start' => date('Y-m-d H:i:s', strtotime('last year')),
            'end' => date('Y-m-d H:i:s', strtotime('yesterday'))
        ))->save();

        $this->assertTrue($rabattCode instanceof Yourdelivery_Model_Rabatt_Code);

        $this->assertFalse($customer->getDiscount()->isUsable());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.02.2011
     */
    public function testCustomerResetPassword() {
        $customer = $this->createCustomer();
        $pass = $customer->getPassword();
        $newPass = $customer->resetPassword();

        $this->assertTrue(is_string($newPass));
        $this->assertNotEquals($newPass, $pass);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.03.2011
     */
    public function testCustomerIsNotEmployee() {
        $customer = $this->createCustomer();

        $this->assertFalse($customer->isEmployee());
        $this->assertEquals($customer->getCompany(), null);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 14.11.2011
     */
    public function testCustomerIsEmployee() {
        $customerCompany = $this->getRandomCustomerCompany();

        $this->assertTrue($customerCompany->isEmployee());
        $this->assertInstanceof(Yourdelivery_Model_Company, $customerCompany->getCompany());
        $this->assertGreaterThan(0, (integer) $customerCompany->getCompany()->getId());

        $customer = $this->getRandomCustomer(false);
        $this->assertFalse($customer->isEmployee());
        $this->assertNull($customer->getCompany());
    }

    /**
     * check if the customerNr is correctly generated
     * @author mlaug
     * @since 23.03.2011
     */
    public function testCutomerNumber() {
        $customer = new Yourdelivery_Model_Customer();
        $customer->setName('aXXX');
        $customer->assertEquals('aXXX', $customer->getName());
        $customer->assertEquals('10000', $customer->getCustomerNr());
        $customer->setName('bXXX');
        $customer->assertEquals('bXXX', $customer->getName());
        $customer->assertEquals('10001', $customer->getCustomerNr());
    }

    public function testStartUrl() {
        // private
        $customer = $this->createCustomer();
        $this->assertEquals('/order_private/start?mode=1', $customer->getStartUrl(), sprintf('did not get correct startUrl of costomer #%d %s', $customer->getId(), $customer->getFullname()));

        $customer->setStart(2)->save();
        $this->assertEquals('/order_private/start?mode=2', $customer->getStartUrl(), sprintf('did not get correct startUrl of costomer #%d %s', $customer->getId(), $customer->getFullname()));

        $customer->setStart(3)->save();
        $this->assertEquals('/order_private/start?mode=3', $customer->getStartUrl(), sprintf('did not get correct startUrl of costomer #%d %s', $customer->getId(), $customer->getFullname()));

        $customer->setStart(4)->save();
        $this->assertEquals('/order_private/start?mode=4', $customer->getStartUrl(), sprintf('did not get correct startUrl of costomer #%d %s', $customer->getId(), $customer->getFullname()));

        $customer->setStart(1)->save();
        $this->assertEquals('/order_private/start?mode=1', $customer->getStartUrl(), sprintf('did not get correct startUrl of costomer #%d %s', $customer->getId(), $customer->getFullname()));

        $customer->setStart(52)->save();
        $this->assertEquals('/order_private/start?mode=52', $customer->getStartUrl(), sprintf('did not get correct startUrl of costomer #%d %s', $customer->getId(), $customer->getFullname()));

        // company
        $custComp = $this->getRandomCustomerCompany();

        $custComp->setStart(2)->save();
        $this->assertEquals('/order_company/start?mode=2', $custComp->getStartUrl());

        $custComp->setStart(3)->save();
        $this->assertEquals('/order_company/start?mode=3', $custComp->getStartUrl());

        $custComp->setStart(4)->save();
        $this->assertEquals('/order_company/start?mode=4', $custComp->getStartUrl());

        $custComp->setStart(1)->save();
        $this->assertEquals('/order_company/start?mode=1', $custComp->getStartUrl());

        $custComp->setStart(43)->save();
        $this->assertEquals('/order_company/start?mode=43', $custComp->getStartUrl());
    }

    /**
     * delete a customer
     * @author mlaug
     * @since 28.10.2011
     */
    public function testDelete() {
        $customer = $this->getRandomCustomer();
        $this->assertFalse($customer->isDeleted());
        $this->assertTrue($customer->delete());
        $this->assertTrue($customer->isDeleted());
        $this->assertEquals($customer->getId(), $customer->getDeleted());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 11.11.2011
     */
    public function testDontShowDeletedCompanyAddresses() {
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow('SELECT c.id as customerId, l.id as locationId FROM customer_company cc
                JOIN customers c ON cc.customerId = c.id
                JOIN companys co ON co.id = cc.companyId
                JOIN locations l ON co.id = l.companyId
                JOIN company_locations cl ON cl.locationId = l.id
            WHERE l.deleted = 0
            ORDER BY RAND() LIMIT 1');

        $cust = new Yourdelivery_Model_Customer($row['customerId']);
        $location = new Yourdelivery_Model_Location($row['locationId']);

        $compLocations = $cust->getCompanyLocations();
        $this->assertInstanceof(Yourdelivery_Model_Company, $cust->getCompany());
        $this->assertGreaterThan(0, count($compLocations), sprintf('customerCompany #%s not associated with any companyAddress - company #%s', $cust->getId(), $cust->getCompany()->getId()));

        $compLocations->rewind();
        $compLocation = $compLocations->current();
        $compLocation->setDeleted(1);
        $compLocation->save();

        foreach ($cust->getCompanyLocations() as $compLoc) {
            // every shown address should not be marked as deleted
            $this->assertEquals(0, $compLoc->getDeleted(), 'got deleted address #' . $compLoc->getId());
        }

        // clean
        $compLocation->setDeleted(0);
        $compLocation->save();
    }

    /**
     * test delete a customer-address and don't get them when calling all its addresses
     *
     * @author Felix Haferkorn
     * @since 15.11.2011
     */
    public function testDontShowDeletedAddresses() {
        $cust = $this->getRandomCustomer(null, true);
        $addresses = $cust->getLocations();
        $addresses->rewind();
        $address = $addresses->current();
        $this->assertInstanceOf('Yourdelivery_Model_Location', $address);
        $countBeforeDeleteOne = $addresses->count();
        $address->setDeleted(1);
        $address->save();

        $addresses = null;
        $addresses = $cust->getLocations();
        $countAfterDeleteOne = $addresses->count();
        foreach ($addresses as $loc) {
            $this->assertEquals(0, $loc->getDeleted(), 'got deleted address #' . $loc->getId());
        }
        $this->assertEquals($countBeforeDeleteOne - 1, $countAfterDeleteOne);

        // undo delete
        $address->setDeleted(0);
        $address->save();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.01.2012
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 18.01.2012
     * RandomCustomer angepasst, dass nur nach Customers ohne profilbild gesucht wird
     */
    public function testAddGetAndDeleteImage() {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow(sprintf('SELECT c.id FROM customers c
                            WHERE c.deleted = 0 AND profileImage IS NULL
                            ORDER BY RAND() LIMIT 1'));
        $customer = new Yourdelivery_Model_Customer($row['id']);
        $this->assertFalse($customer->hasProfileImage());
        
        $points = $customer->getFidelityPoints()->getPoints();
        $image = APPLICATION_PATH_TESTING . '/../data/samson.jpg';

        //add
        $this->assertEquals(0, $customer->addImage('adslkjdsajkhdas'));
        $this->assertEquals(0, $customer->addImage($image));
        $this->assertEquals($points, $customer->getFidelityPoints()->getPoints());
        $this->assertEquals($points + $config->fidelity->points->accountimage, $customer->addImage($image, true));
        $this->assertEquals($points + $config->fidelity->points->accountimage, $customer->getFidelityPoints()->getPoints());

        //get
        $this->assertGreaterThan(0, strlen($customer->getProfileImage()));
        
        $width = $this->config->timthumb->customer->normal->width;
        $height = $this->config->timthumb->customer->normal->height;
        
        $profileImageTestingUrl = $customer->getProfileImage();
        
        $this->assertTrue(strpos($profileImageTestingUrl, $width . '-' . $height) !== false, sprintf('did not get correct dimension for customer profileImage - get "%s" - but expected to get "%s"', $profileImageTestingUrl, $width . '-' . $height));
        $profileImageTestingUrl = str_replace(sprintf('%s/customer', $config->domain->base), sprintf('%s.testing/customer', $config->domain->base), $profileImageTestingUrl);
        $this->assertTrue(Default_Helpers_Web::url_exists($profileImageTestingUrl), sprintf('profileImage for customer #%d %s does not exist at url "%s"', $customer->getId(), $customer->getFullname()));
        //delete
        $customer->deleteProfileImage();
        $this->assertEquals($points, $customer->getFidelityPoints()->getPoints());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.01.2012
     */
    public function testPersistentMessages() {
        $customer = $this->getRandomCustomer();
        $messageCount = $customer->getTable()->getPersistentMessages()->count();
        $customer->createPersistentMessage('warn', 'samson tiffy');
        $this->assertEquals($messageCount + 1, $customer->getTable()->getPersistentMessages()->count());
        $customer->setPersistentNotfication();
        $this->assertEquals(0, $customer->getTable()->getPersistentMessages()->count());
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.02.2012
     */
    public function testCreateFromContact() {
        $name = 'TestName_' . time();
        $prename = 'TestPrename_' . time();
        $email = 'TestEmail_' . microtime() . '@test.de';
        $tel = Default_Helper::generateRandomString(8, array(1, 2, 3, 4, 5, 6, 7, 8, 9));

        $contact = new Yourdelivery_Model_Contact();
        $contact->setData(array('name' => $name, 'prename' => $prename, 'email' => $email, 'tel' => $tel));
        $contact->save();

        $customer = Yourdelivery_Model_Customer::createFromContact($contact);

        $this->assertEquals($customer->getName(), $name);
        $this->assertEquals($customer->getPrename(), $prename);
        $this->assertEquals($customer->getEmail(), $email);
        $this->assertEquals($customer->getTel(), $tel);
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     */
    public function testGetEmployee() {
        $customerCompany = $this->createCustomerCompanyRelation();

        // create customer from relation
        $customer = new Yourdelivery_Model_Customer($customerCompany->getId());

        //get the customer company relation of this customer
        $empl = $customer->getEmployee();

        $this->assertNotNull($empl);
        $this->assertEquals($customerCompany->getCompany(), $empl->getCompany());
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     */
    public function testGetFavouriteRestaurants() {
        $customer = null;
        
        // finde a customer with ate least 3 orders
        $customer = $this->getRandomCustomer(null, true, null, null, 3);       

        // make sure we have no favourites
        foreach ($customer->getOrders() as $order) {
            $order->deleteFromFavorite();
        }

        $restIds = array();
        // add all to favourites randomly
        foreach ($customer->getOrders() as $order) {
            if (rand() % 2 == 0) {
                // add only the restaurant is online and not deleted
                $restaurant = $order->getService();
                if (($restaurant->getDeleted() == 0) && (in_array($restaurant->getStatus(), array(0, 2, 3, 4, 5, 6, 7, 10, 14, 15, 16, 17, 18, 20, 21, 24)))) {
                    $restIds[] = $order->getRestaurantId();
                    $order->addToFavorite($customer);
                }
            }
        }

        $favRestaurants = $customer->getFavouriteRestaurants();

        $favRestArray = array();
        foreach ($favRestaurants as $favRestaurant) {
            //test if this retaurant was set
            $this->assertTrue(in_array($favRestaurant->getId(), $restIds));
            $favRestArray[] = $favRestaurant->getId();
        }

        // count of restaurants where favourite order were made must be the same as the count of favourite restaurants
        $this->assertEquals(count(array_unique($restIds)), count(array_unique($favRestArray)));
    }

    /**
     * delete a customer
     * @author mlaug
     * @since 28.10.2011
     */
    public function testDeleteEmployee() {
        $customer = $this->getRandomCustomer(true);
        $this->assertFalse($customer->isDeleted());

        $ccArr = Yourdelivery_Model_DbTable_Customer_Company::findByCustomerId($customer->getId());
        $this->assertTrue(is_array($ccArr));

        $cc = new Yourdelivery_Model_Customer_Company($ccArr['customerId'], $ccArr['companyId']);
        $this->assertTrue($cc instanceof Yourdelivery_Model_Customer_Company);

        $this->assertTrue($customer->delete());

        // no relation to companys
        $this->assertFalse(Yourdelivery_Model_DbTable_Customer_Company::findByCustomerId($customer->getId()));

        $this->assertTrue($customer->isDeleted());

        // no newsletter
        $this->assertFalse($customer->getNewsletter());

        // no entries in user access rights on restaurants/companys
        $this->assertFalse(Yourdelivery_Model_DbTable_UserRights::findByCustomerId($customer->getId()));
    }

    /**
     * delete a customer
     * @author mlaug
     * @since 28.10.2011
     */
    public function testDeleteEmployeeWithRights() {
        $customer = $this->getRandomCustomer(true, null, false, true);
        $this->assertFalse($customer->isDeleted());
        $this->assertTrue(is_array(Yourdelivery_Model_DbTable_UserRights::findByCustomerId($customer->getId())));

        $ccArr = Yourdelivery_Model_DbTable_Customer_Company::findByCustomerId($customer->getId());
        $this->assertTrue(is_array($ccArr));

        $cc = new Yourdelivery_Model_Customer_Company($ccArr['customerId'], $ccArr['companyId']);
        $this->assertTrue($cc instanceof Yourdelivery_Model_Customer_Company);

        $this->assertTrue($customer->delete());

        // no relation to companys
        $this->assertFalse(Yourdelivery_Model_DbTable_Customer_Company::findByCustomerId($customer->getId()));

        $this->assertTrue($customer->isDeleted());

        // no newsletter
        $this->assertFalse($customer->getNewsletter());

        // no entries in user access rights on restaurants/companys
        $this->assertFalse(Yourdelivery_Model_DbTable_UserRights::findByCustomerId($customer->getId()));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 08.02.2012
     */
    public function testGetUnratedOrderCount() {
        $customer = $this->getRandomCustomer();
        $count = $customer->getUnratedOrdersCount();

        $this->assertTrue(is_numeric($count));

        $orderId = $this->placeOrder(array("payment" => "bar", "customer" => $customer));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::DELIVERED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT,"testCase testGetUnratedOrderCount"));

        //set back time to include order
        $row = $order->getTable()->getCurrent();
        $row->time = date(DATETIME_DB, strtotime($row->time) - 3660);
        $row->deliverTime = date(DATETIME_DB, strtotime($row->deliverTime) - 3660);
        $row->save();

        $customer->clearCache();

        $newCount = $customer->getUnratedOrdersCount();
        $this->assertTrue(is_numeric($newCount));
        $this->assertEquals($count + 1, $newCount);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 08.02.2012
     */
    public function testGetRatedOrderCount() {
        $customer = $this->getRandomCustomer();
        $count = $customer->getRatedOrdersCount();

        $this->assertTrue(is_numeric($count));

        $orderId = $this->placeOrder(array("payment" => "bar", "customer" => $customer));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::DELIVERED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT,"testCase testRatedOrderCount"));

        //set back time to include order
        $row = $order->getTable()->getCurrent();
        $row->time = date(DATETIME_DB, strtotime($row->time) - 3660);
        $row->deliverTime = date(DATETIME_DB, strtotime($row->deliverTime) - 3660);
        $row->save();

        //add Rating to Order
        $order->rate($customer->getId(), 3, 3, "test case", "test", 1, "deine Mudda riecht komisch");

        $rating = $order->getRating()->current();
        $rating->status = 1;
        $rating->save();

        $customer->clearCache();

        $newCount = $customer->getRatedOrdersCount();
        $this->assertTrue(is_numeric($newCount));
        $this->assertEquals($count + 1, $newCount);
    }

    
    /**
     * register customer and test normalizing the email, containing non-ascii characters
     * @author Alex Vait <vait@lieferando.de>
     * @since 17.07.2012
     */
    public function testRegister() {
        $city = new Yourdelivery_Model_City($this->getRandomCityId());

        $emailBase = time() . rand(1, 1000);
        $data = array();        
        $data['email'] =  $emailBase . "@testmail.de";
        $data['prename'] = Default_Helper::generateRandomString(20);
        $data['name'] = Default_Helper::generateRandomString(20);
        $data['street'] = Default_Helper::generateRandomString(10) . "str.";
        $data['hausnr'] = rand(1, 100);
        $data['cityId'] = $city->getId();
        $data['plz'] = $city->getPlz();
        $data['tel'] = rand(10000, 5000);
        $data['password'] = Default_Helper::generateRandomString(20);
        
        $customerId = Yourdelivery_Model_Customer::add($data);
        $this->assertGreaterThan(0, $customerId);
        
        // the same data must not create the new entry, but return the already existing customer
        $newCustomerId = Yourdelivery_Model_Customer::add($data);
        $this->assertEquals($newCustomerId, $customerId);

        // create an array of illegal ascii chars
        $illegalChars = "";
        for ($i = 0; $i <=31; $i++) {
            $illegalChars .= chr($i);
        }
        
        for ($i = 128; $i <=254; $i++) {
            $illegalChars .= chr($i);
        }
        
        
        // create the email with illegal chars in it
        $illegalString = Default_Helper::generateRandomString(50, $illegalChars);
        $data['email'] =  $emailBase . $illegalString. "@testmail.de";

        // the same data must againg not create the new entry, but return the already existing customer
        $newCustomerId2 = Yourdelivery_Model_Customer::add($data);
        $this->assertEquals($newCustomerId2, $customerId);
    }    
}
