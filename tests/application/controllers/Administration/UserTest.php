<?php

/**
 * Description of UserTest
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Administration_UserTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     *
     */
    public function testCreate() {

        if(HOSTNAME != 'lieferando.de'){
            $this->markTestSkipped("in AT, CH, PL we don't have companies yet");
        }
        
        $city = $this->getRandomCityId();
        $company = $this->getRandomCompany();
        $rabattcodes = $this->getCustomerRabattCodes();

        $email = Default_Helper::generateRandomString(8) . "@lfd.de";
        $customer = "Customer_" . Default_Helper::generateRandomString(4);

        $rest_admins = $this->getRandomService();

        $post = array('budgetId' => 651,
            'cityId' => $city,
            'comment' => '',
            'company' => $company->getId(),
            'company_admin' => 1,
            'discount' => $rabattcodes[0]['rid'],
            'email' => $email,
            'hausnr' => 23,
            'name' => $customer,
            'password' => 'passiert',
            'prename' => 'Tester',
            'service_admin' => $rest_admins->getId(),
            'sex' => 'm',
            'street' => 'teststr',
            'tel' => '');

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('administration_user/create');

        $this->assertRedirect('/administration/users');

        $customer_db = Yourdelivery_Model_DbTable_Customer::findByEmail($email);
        $this->assertEquals($customer_db['name'], $customer);

        $cust2comp = Yourdelivery_Model_DbTable_Customer_Company::findByCustomerId($customer_db['id']);
        $this->assertEquals($cust2comp['companyId'], $company->getId());

        $customerObj = new Yourdelivery_Model_Customer($customer_db['id']);
        $this->assertTrue($customerObj->isAdmin($company));

        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($rest_admins->getId());
        $this->assertTrue($customerObj->isAdmin($restaurant));
    }

    /**
     * @depend  testCreate
     * @author Daniel Hahn <hahn@lieferando.de>
     * @modified fhaferkorn, 07.11.2011
     */
    public function testDelete() {
        $customer = $this->getRandomCustomer();
        $url = "/administration_user/delete/id/" . $customer->getId();

        $this->dispatch($url);
        $this->assertRedirectTo('/administration/users');

        $deletedCustomer = Yourdelivery_Model_DbTable_Customer::findById($customer->getId());

        $this->assertTrue($deletedCustomer['deleted'] == $customer->getId());

        $customer = new Yourdelivery_Model_Customer($customer->getId());
        $this->assertTrue($customer->isDeleted());

        // check db
        $db = Zend_Registry::get('dbAdapter');
        $rowCustomerCompany = $db->fetchRow(sprintf("SELECT count(*) as count FROM customer_company cc WHERE cc.customerId = %d", $customer->getId()));
        $this->assertEquals(0, $rowCustomerCompany['count'], sprintf('fail: customerId = %s', $customer->getId()));

        $rowUserRights = $db->fetchRow(sprintf("SELECT count(*) as count FROM user_rights ur WHERE ur.kind = 'c' AND ur.customerId = %d", $customer->getId()));
        $this->assertEquals(0, $rowUserRights['count'], sprintf('fail: customerId = %s', $customer->getId()));
    }

}