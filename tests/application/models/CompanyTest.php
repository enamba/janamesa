<?php

/**
 * @runTestsInSeparateProcesses 
 */
class CompanyTest extends Yourdelivery_Test {

    /**
     * @author mlaug
     * @since 02.10.2010
     */
    public function testStaticAllEmployeesEmail(){
         

        $company = new Yourdelivery_Model_Company(1097);
        $result = Yourdelivery_Model_Company::allEmployeesEmail(1097);
        $this->assertTrue(is_array($result));
        $this->assertGreaterThan(0,count($result));
        foreach($result as $employee){
            //all emails must be valid, for sure
            $this->assertTrue(Default_Helper::email_validate($employee['email']));
        }
    }

    /**
     * @author mlaug
     * @since 02.10.2010
     */
    public function testGetEmployees(){
         

        $company = new Yourdelivery_Model_Company(1097);
        $result = Yourdelivery_Model_Company::allEmployeesEmail(1097);
        $this->assertTrue(is_array($result));
        $this->assertEquals($company->getEmployees()->count(),count($result));
        $this->assertEquals($company->getEmployeesCount(),count($result));
    }

    /**
     * @author mlaug
     * @since 02.10.2010
     * @todo not really working
     */
    public function testBudget(){
         
        $company = $this->getRandomCompany();
        while($i++ <= MAX_LOOPS && $company->getLocations()->count() <= 0 ){       
            $company = $this->getRandomCompany();
        }
        $locations = $company->getLocations();
        $this->assertGreaterThan(0,$locations->count());
        $locations->rewind();
        $location = $locations->current();
        
        $budget = new Yourdelivery_Model_Budget();
        $budget->setName(Default_Helper::generateRandomString());
        $budget->setCompany($company);
        $budget->setStatus(0);
        $budget->save();

        $this->assertGreaterThan(0,$company->addBudget($budget,$location));
        $this->assertTrue($company->hasBudgetGroup($budget->getId()));
    }
           
}
?>
