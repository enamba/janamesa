<?php
/**
 * @runTestsInSeparateProcesses 
 */
class CustomerCompanyTest extends Yourdelivery_Test {

    /**
     * @author mlaug
     * @since 28.10.2010
     */
    public function testCustomerCompany() {

        $custComp = $this->getRandomCustomerCompany();
        $compId = $custComp->getCompany()->getId();
        //creation using id
        $customer = new Yourdelivery_Model_Customer($custComp->getId(), $compId);
        $this->assertTrue($customer->isLoggedIn());
        //must be this company
        $this->assertEquals($customer->getCompany()->getId(), $compId);

        $company = new Yourdelivery_Model_Company($compId);
    }

    /**
     * check, that only employee can be made to admin of a company
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.04.2011
     */
    public function testMakeAdminNotEmployeeSuccess() {

        $cust = $this->getRandomCustomer(false);

        $this->assertTrue($cust->isPersistent());
        $this->assertTrue($cust instanceof Yourdelivery_Model_Customer);
        $this->assertFalse($cust->isEmployee());
        $comp = $this->getRandomCompany();

        $this->assertEquals(1, $comp->getStatus());
        /**
         * customer is not employee of ANY company
         * can't make him admin
         */
        $this->assertFalse($cust->makeAdmin($comp), Default_Helpers_Log::getLastLog());
        /**
         * add customer to company
         */
        $values = array(
            "prename" => $cust->getPrename(),
            "name" => $cust->getName(),
            "email" => $cust->getEmail(),
            "personalnumber" => rand(12345, 98765),
            "costcenter" => 0,
            "budget" => 0
        );
        $this->assertFalse($cust->isAdmin($comp));
        $custComp = Yourdelivery_Model_Customer_Company::add($values, $comp->getId(), false);
        $this->assertInstanceof(Yourdelivery_Model_Customer_Company, $custComp);
        $this->assertFalse($custComp->isAdmin($comp));
        // make admin
        $this->assertTrue($custComp->makeAdmin($comp), 'LastLog: ' . Default_Helpers_Log::getLastLog());
        /**
         * check in db, if there is a correct entry
         */
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->fetchRow(sprintf(
                        'SELECT count(id) AS count
                    FROM user_rights
                    WHERE customerId = %d
                        AND kind = "c"
                        AND refId = %d', $cust->getId(), $comp->getId()));
        $this->assertEquals(1, $result['count']);
        $this->assertTrue($cust->isAdmin($comp));

        // cleanup
        $this->assertTrue($cust->removeAdmin($comp));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 13.09.2011
     */
    public function testMakeAdminEmployeeWrongCompanyFail() {
        $cust = $this->getRandomCustomerCompany();
        $this->assertTrue($cust instanceof Yourdelivery_Model_Customer_Company);
        $this->assertTrue($cust->isPersistent());

        $company = $cust->getCompany();
        $otherCompany = $this->getRandomCompany();

        $this->assertTrue($company instanceof Yourdelivery_Model_Company);

        while ($i++ <= MAX_LOOPS && $otherCompany->getId() == $company->getId()) {
            $otherCompany = $this->getRandomCompany();
        }

        $this->assertTrue($otherCompany instanceof Yourdelivery_Model_Company);

        $this->assertFalse($cust->isAdmin($otherCompany));

        $this->assertFalse($cust->makeAdmin($otherCompany));
    }

    /**
     * @author fhaferkron
     * @since 12.04.2011
     */
    public function testRemoveAdmin() {

        $comp = $this->getRandomCompany();
        $admins = $comp->getAdmins();

        // get company with at least 1 admin
        while($i++ <= MAX_LOOPS && $admins->count() <= 0){
            $comp = $this->getRandomCompany();
            $admins = $comp->getAdmins();
        }

        $customer = null;
        foreach ($admins as $admin) {
            $customer = $admin;
            break;
        }

        $this->assertInstanceof(Yourdelivery_Model_Customer_Company,$customer);
        $this->assertTrue($customer->isAdmin($comp));
        $customer->removeAdmin($comp);
        $this->assertFalse($customer->isAdmin($comp));
    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.03.2011
     */
    public function testCustomerIsEmployee() {


        $customer = $this->createCustomer();
        $company = $this->getRandomCompany();

        $values = array(
            "prename" => $customer->getPrename(),
            "name" => $customer->getName(),
            "email" => $customer->getEmail(),
            "budget" => 1
        );

        $customer = Yourdelivery_Model_Customer_Company::add(
                        $values, $company->getId(), false
        );

        $this->assertTrue($customer->getCompany() instanceof Yourdelivery_Model_Company);
        $this->assertTrue($customer->isEmployee());
    }

}

?>
