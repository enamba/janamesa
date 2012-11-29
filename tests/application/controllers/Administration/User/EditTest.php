<?php

/**
 * @author Alex Vait <vait@lieferando.de> 
 */

/**
 * @runTestsInSeparateProcesses 
 */
class Administration_User_EditTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();

        // set admin auth
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $this->getRequest()->setHeader('Authorization', 'Basic ' . base64_encode('gf:thisishell'));
    }

    /**
     * @author Alex Vait <vait@lieferando.de> 
     */
    public function testIndex() {

        $customer = $this->getRandomCustomer();

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/administration_user_edit/index/userid/' . $customer->getId());
        $this->assertResponseCode('200');
        $this->assertQuery('form[action="' . '/administration_user_edit/index/userid/' . $customer->getId() . '"]');
    }

    /**
     * @author Alex Vait <vait@lieferando.de> 
     */
    public function testIndexPost() {
        $customer = $this->getRandomCustomer();
        $nameChanged = $customer->getName() . '-' . time() . rand(1, 9);

        $post = array(
            'birthday' => $customer->getBirthday(),
            'email' => $customer->getEmail(),
            'name' => $nameChanged,
            'newpass' => "",
            'prename' => $customer->getPrename(),
            'sex' => $customer->getSex(),
            'tel' => $customer->getTel()
        );

        foreach ($post as &$value) {
            if (is_null($value)) {
                $value = false;
            }
        }

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/administration_user_edit/index/userid/' . $customer->getId());
        $this->assertRedirect('/administration_user_edit/index/userid/' . $customer->getId());

        // check the change has been writen to database
        $new_customer = new Yourdelivery_Model_Customer($customer->getId());

        $this->assertEquals($nameChanged, $new_customer->getName());
    }

    /**
     * @author Alex Vait <vait@lieferando.de> 
     */
    public function testAssoc() {
        $customer = $this->getRandomCustomer();

        $request = $this->getRequest();
        $request->setMethod('GET');

        $this->dispatch('/administration_user_edit/assoc/userid/' . $customer->getId());
        $this->assertResponseCode('200');
    }

    /**
     * @author Alex Vait <vait@lieferando.de> 
     */
    public function testLocationPost() {
        $customer = $this->getRandomCustomer();
        $city = $this->getRandomCityId();
        $street = 'TestStreetLocationsPost';
        $post = array('addadress' => 'Adresse hinzufügen',
            'cityId' => $city,
            'comment' => '',
            'hausnr' => '12',
            'street' => $street
        );
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);
        $this->dispatch("/administration_user_edit/location/add/1/userid/" . $customer->getId());

        $testLocation = false;
        foreach ($customer->getLocations() as $location) {
            if (strcmp($location->getStreet(), $street) == 0) {
                $testLocation = true;
            }
        }
        $this->assertTrue($testLocation);
    }

    /**
     * @author Alex Vait <vait@lieferando.de> 
     */
    public function testEditRestaurantsAdd() {

        $customer = $this->getRandomCustomer();
        $restaurant = $this->getRandomService();

        $post = array(
            'assignadmin' => 'Zuweisen',
            'restCheckbox' => array($restaurant->getId() => 'on'),
            'userid' => $customer->getId()
        );

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);
        $this->dispatch("/administration_user_edit/restaurant");

        $this->assertRedirect('/administration_user_edit/assoc/userid/' . $customer->getId());

        $restObj = new Yourdelivery_Model_Servicetype_Restaurant($restaurant->getId());
        $this->assertTrue($customer->isAdmin($restObj));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de> 
     */
    public function testEditRestaurantsDelete() {

        $restaurant = $this->getRandomService(array('withAdmin' => true));
        $admins = $restaurant->getAdmins();
        $admins->rewind();
        $admin = $admins->current();
        $this->assertInstanceof('Yourdelivery_Model_Customer', $admin);
        $url = "/administration_user_edit/restaurant/userid/" . $admin->getId() . "/delrest/" . $restaurant->getId();
        $this->dispatch($url);
        $this->assertRedirect('/administration_user_edit/assoc/userid/' . $admin->getId());

        $this->assertFalse($admin->isAdmin($restaurant));
    }

    /**
     * @author Alex Vait <vait@lieferando.de> 
     */
    public function testEditDiscountAdd() {

        $customer = $this->getRandomCustomer();
        // create customer discount first
        $code = $this->createDiscount(1, 0, 10, true, false, true);

        $post = array('adddiscount' => 'Hinzufügen',
            'discountId' => $code->getId(),
            'userid' => $customer->getId());

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch("/administration_user_edit/discount");
        $this->assertRedirect('/administration_user_edit/assoc/userid/' . $customer->getId() . '/#discount');

        $this->assertNotNull($customer->getDiscount());

        if (!is_null($customer->getDiscount())) {
            $this->assertEquals($customer->getDiscount()->getId(), $code->getId());
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function testEditDiscountDelete() {
        $customer = $this->getRandomCustomer(null, null, true);
        $code = new Yourdelivery_Model_Rabatt_Code(null, $customer->getPermanentDiscountId());
        $this->assertIsPersistent($code);

        $this->dispatch("/administration_user_edit/discount/userid/" . $customer->getId() . "/deldiscount/" . $code->getCode());

        $this->assertRedirect('/administration_user_edit/assoc/userid/' . $customer->getId() . '/#discount');
        $this->assertNull($customer->getDiscount());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testEditCompanyAdd() {

        if (HOSTNAME == 'lieferando.at' || HOSTNAME == 'lieferando.ch' || HOSTNAME == 'smakuje.pl') {
            $this->markTestSkipped("in AT, CH, PL we don't have companies yet");
        }

        $db = $this->_getDbAdapter();
        $select = $db->select()->from(array('c' => 'customer_company'));
        $res = $db->fetchAll($select);

        $customerIds = array();
        foreach ($res as $entry) {
            $customerIds[] = $entry['customerId'];
        }

        do {
            $customer = $this->getRandomCustomer();
        } while ($i++ <= MAX_LOOPS && in_array($customer->getId(), $customerIds));

        $company = $this->getRandomCompany(true, true);

        $budgets = $company->getBudgets();
        $budgets->rewind();

        if ($budgets->current()) {
            $budgetId = $budgets->current()->getId();
        } else {
            $budgetId = 0;
        }

        $post = array('adddcompany' => 'Hinzufügen',
            'budgetId' => $budgetId,
            'company' => $company->getId(),
            'company_admin' => 1,
            'userid' => $customer->getId());

        $this->dispatchPost("/administration_user_edit/company", $post);
        $this->assertRedirect('/administration_user_edit/assoc/userid/' . $customer->getId());

        $assoc = Yourdelivery_Model_DbTable_Customer_Company::findByCustomerId($customer->getId());

        $this->assertEquals($customer->getId(), $assoc['customerId']);
        $this->assertEquals($company->getId(), $assoc['companyId']);
        $this->assertTrue($customer->isAdmin($company));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testEditCompanyDelete() {

        if (HOSTNAME == 'lieferando.at' || HOSTNAME == 'lieferando.ch' || HOSTNAME == 'smakuje.pl') {
            $this->markTestSkipped("in AT, CH, PL we don't have companies yet");
        }

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from(array('c' => 'customer_company'))->where('companyId IS NOT NULL and customerId IS NOT NULL');
        $res = $db->fetchAll($select);
        shuffle($res);

        $customer = new Yourdelivery_Model_Customer($res[0]['customerId']);
        $company = new Yourdelivery_Model_Company($res[0]['companyId']);

        $this->dispatch("/administration_user_edit/company/userid/" . $customer->getId() . "/company/" . $company->getId() . "/delcomp/1");
        $this->assertRedirect('/administration_user_edit/assoc/userid/' . $customer->getId());

        $assoc = Yourdelivery_Model_DbTable_Customer_Company::findByCustomerId($customer->getId());
        $this->assertFalse($assoc);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 04.07.2012
     */
    public function testChangeCustomerEmailWithFidelityMigrate() {
        $cust = $this->getRandomCustomer();

        // add some points
        $cust->getFidelity()->addTransaction('manual', 'testdata', 10);
        $points = $cust->getFidelity()->getPoints();
        $this->assertGreaterThanOrEqual(10, $points, sprintf('custoemr should have at least 10 points, because we added 10, but get %s as fidelity points', $points));
        $origEmail = $cust->getEmail();
        // modify email and post it
        $uniqueEmail = sprintf('my-unique-email@%s-%s.com', time(), rand(1,99));
        $post = array('name' => $cust->getName(), 'prename' => $cust->getPrename(), 'email' => $uniqueEmail);
        
        $this->dispatchPost("/administration_user_edit/index/userid/".$cust->getId(), $post);
        $this->assertRedirect('/administration_user_edit/index/userid/' . $cust->getId());
        $custCheck = new Yourdelivery_Model_Customer($cust->getId());
        $this->assertNotEquals($origEmail, $custCheck->getEmail(), sprintf('failed to get different emails after editing customer #%s %s', $cust->getId(), $cust->getFullname()));
        $this->assertEquals($points, $custCheck->getFidelity()->getPoints(), sprintf('failed to get identical count of fidelity points after changing email. Old email (points): %s (%s) - new email (points): %s (%s)', $origEmail, $points, $custCheck->getEmail(), $cust->getFidelity()->getPoints()));
    }

}

?>
