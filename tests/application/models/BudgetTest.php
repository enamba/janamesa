<?php

/**
 * @runTestsInSeparateProcesses 
 */
class BudgetTest extends Yourdelivery_Test {

    /**
     * create budget
     * adding non-employee should fail
     * adding employee should succeed
     * @author mlaug, fhaferkorn
     * @since 29.03.2011
     */
    public function testCreate() {
         
        $company = $this->getRandomCompany();

        $budget = new Yourdelivery_Model_Budget();
        $budget->setName('PenisPersonen');
        $budget->setCompany($company);
        $budget->setStatus(0);
        $this->assertGreaterThan(0, $budget->save());
        $this->assertGreaterThan(0, $budget->addBudgetTime(0, '14:00', '15:00', 100));

        /**
         * @todo: add the budget to the company, create an location before
         */
        $customer1 = $this->createCustomer();
        
        //this should fail, since the customer is not in the company
        $this->assertFalse($budget->addMember($customer1->getId()), sprintf('succeeded to add wrong customer #%d to budget #%d', $customer1->getId(), $budget->getId()));
        
        /**
         * @todo: create an employee and add again, should succeed
         */
        $customer2 = $this->createCustomer();
        $custCompRelation = new Yourdelivery_Model_DbTable_Customer_Company();
        $custCompRelation->insert(
                array(
                    'budgetId' => $budget->getId(),
                    'customerId' => $customer2->getId(),
                    'companyId' => $company->getId()
        ));
        $this->assertTrue($budget->addMember($customer2->getId()));

        return $budget->getId();
    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.03.2011
     */
    public function testHasAddress(){
         

        $budgetId = $this->testCreate();
        $budget = new Yourdelivery_Model_Budget($budgetId);

        $db = Zend_Registry::get('dbAdapter');

        $relSql = sprintf('SELECT id FROM company_locations WHERE budgetId = %d',$budgetId);
        $relIds = $db->query($relSql)->fetchAll();

        $this->assertTrue(count($relIds) == 0);

        $location = $this->createLocation();
        $location->setCompany($budget->getCompany());
        
        $table = new Yourdelivery_Model_DbTable_Company_Locations();
        $id = $table->insert(array(
            'budgetId' => $budget->getId(),
            'locationId' => $location->getId()
        ));

        $relSql = sprintf('SELECT id FROM company_locations WHERE budgetId = %d LIMIT 1',$budgetId);
        $relId = $db->query($relSql)->fetchColumn();
        $this->assertEquals($relId, $id);
        $this->assertTrue($budget->hasLocation($location->getId()));

    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.03.2011
     */
    public function testDelete(){
         

        $budgetId = $this->testCreate();

        $budget = new Yourdelivery_Model_Budget($budgetId);

        $db = Zend_Registry::get('dbAdapter');

        $sqlCustCompBefore = sprintf('SELECT count(id) AS `count` FROM customer_company WHERE budgetId = %d',$budgetId);
        $resultCustCompBefore = $db->query($sqlCustCompBefore)->fetchColumn();
        $this->assertEquals($resultCustCompBefore,1);

        $this->assertTrue($budget->delete());

        
        $sqlCustComp = sprintf('SELECT count(id) AS `count` FROM customer_company WHERE budgetId = %d',$budgetId);
        $resultCustComp = $db->query($sqlCustComp)->fetchColumn();
        $this->assertEquals($resultCustComp,0);

        $sqlCompBudTimes = sprintf('SELECT count(id) AS `count` FROM company_budgets_times WHERE budgetId = %d',$budgetId);
        $resultCompBudTimes = $db->query($sqlCompBudTimes)->fetchColumn();
        $this->assertEquals($resultCompBudTimes,0);

        $sqlCompBud = sprintf('SELECT count(id) AS `count` FROM company_budgets WHERE id = %d',$budgetId);
        $resultCompBud = $db->query($sqlCompBud)->fetchColumn();
        $this->assertEquals($resultCompBud,0);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.03.2011
     */
    public function testAddLocation(){
         

        $location = $this->createLocation();
        $budgetId = $this->testCreate();

        $budget = new Yourdelivery_Model_Budget($budgetId);

        $this->assertFalse($budget->hasLocation($location->getId()));
        // location is not associated to any company, so it has to fail
        $this->assertFalse($budget->addLocation($location->getId()));
        $this->assertFalse($budget->hasLocation($location->getId()));

        $location->setCompanyId(9999999);
        $location->save();
        // location is not associated to this company, so it has to fail too
        $this->assertFalse($budget->addLocation($location->getId()));
        $this->assertFalse($budget->hasLocation($location->getId()));


        $location->setCompanyId($budget->getCompany()->getId());
        $location->save();
        // location is not associated to this company, so it has to fail too
        $this->assertTrue($budget->addLocation($location->getId()));
        $this->assertTrue($budget->hasLocation($location->getId()));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.03.2011
     */
    public function testRemoveLocation(){
         

        $budgetId = $this->testCreate();
        $budget = new Yourdelivery_Model_Budget($budgetId);

        $location = $this->createLocation();
        $location->setCompany($budget->getCompany());
        $location->save();
        
        $this->assertTrue($budget->addLocation($location->getId()));
        
        $this->assertTrue($budget->hasLocation($location->getId()));
        $this->assertTrue($budget->removeLocation($location->getId()));

        $this->assertFalse($budget->hasLocation($location->getId()));
    }


    /**
     * test adding and removing budget times
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.04.2011
     */
    public function testAddAndRemoveBudgetTime(){
         

        $amount = 1232;
        
        $budgetId = $this->testCreate();
        $budget = new Yourdelivery_Model_Budget($budgetId);
        
        $budgetTimes = $budget->getBudgetTimes();
        $countBefore = count($budgetTimes);
        
        $budget->addBudgetTime(1, '10:00','21:13', $amount);

        $budgetTimes = $budget->getBudgetTimes();
        $countAfterAdding = count($budgetTimes);
        
        $this->assertEquals($countBefore+1, $countAfterAdding);
        
        $this->assertEquals($budgetTimes[1][$countAfterAdding-1]['from'], mktime(10, 00, 00));
        $this->assertEquals($budgetTimes[1][$countAfterAdding-1]['until'], mktime(21, 13, 00));
        $this->assertEquals($budgetTimes[1][$countAfterAdding-1]['amount'], $amount);

        $this->assertTrue($budget->removeBudgetTime($budgetTimes[1][$countAfterAdding-1]['id']));
        
        $budgetTimes = $budget->getBudgetTimes();
        $countAfterRemoving = count($budgetTimes);
        $this->assertNotEquals($countAfterAdding, $countAfterRemoving);
        $this->assertEquals($countBefore, $countAfterRemoving);
    }    
}

?>
