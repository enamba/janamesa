<?php

/**
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses 
 */
class BalanceTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();
        $db = Zend_Registry::get('dbAdapter');
        $db->query('truncate billing_balance');
    }
    
    /**
     * @expectedException Yourdelivery_Exception_Balance_NoObjectGiven
     * @expectedException Yourdelivery_Exception_Balance_WrongObjectGiven
     */
    public function testExceptions(){
        $balance = new Yourdelivery_Model_Billing_Balance();
        $balance->getAmount();
        $customer = $this->getRandomCustomer();
        $balance->setObject($customer);
        $balance->getAmount();
    }
    
    public function testAddBalanceToService(){
        $service = $this->getRandomService();
        $balance = new Yourdelivery_Model_Billing_Balance();
        $balance->setObject($service);
        $this->assertGreaterThan(0,$balance->addBalance(10, 'test1'));
        $this->assertGreaterThan(0,$balance->addBalance(-10, 'test2'));
        $this->assertEquals(0,$balance->getAmount());     
        $this->assertGreaterThan(0,$balance->addBalance(10)); 
        $this->assertEquals(10,$balance->getAmount());  
        $this->assertEquals(3,count($balance->getList())); 
        $this->assertGreaterThan(0,$balance->addBalance(10)); 
        $this->assertEquals(4,count($balance->getList())); 
        
        $this->assertEquals($balance->getAmount(),$service->getBalance()->getAmount());
        $this->assertEquals($balance->getList(),$service->getBalance()->getList());
        
    }
    
    public function testAddBalanceToCompany(){
        $company = $this->getRandomCompany(true);
        $balance = new Yourdelivery_Model_Billing_Balance();
        $balance->setObject($company);
        $this->assertGreaterThan(0,$balance->addBalance(10, 'test3'));
        $this->assertGreaterThan(0,$balance->addBalance(-10, 'test4'));
        $this->assertEquals(0,$balance->getAmount());     
        $this->assertGreaterThan(0,$balance->addBalance(10)); 
        $this->assertEquals(10,$balance->getAmount()); 
        $this->assertEquals(3,count($balance->getList())); 
        $this->assertGreaterThan(0,$balance->addBalance(10)); 
        $this->assertEquals(4,count($balance->getList()));    
        
        $this->assertEquals($balance->getAmount(),$company->getBalance()->getAmount());
        $this->assertEquals($balance->getList(),$company->getBalance()->getList());
    }
    
    public function testResetAmount(){
        $company = $this->getRandomCompany(true);
        $balance = new Yourdelivery_Model_Billing_Balance();
        $balance->setObject($company);
        $this->assertGreaterThan(0,$balance->addBalance(10, 'test3'));
        $this->assertGreaterThan(0,$balance->addBalance(-10, 'test4'));
        $this->assertEquals(0,$balance->getAmount());     
        $this->assertGreaterThan(0,$balance->addBalance(10)); 
        $this->assertEquals(10,$balance->getAmount()); 
        $this->assertEquals(3,count($balance->getList())); 
        $this->assertGreaterThan(0,$balance->addBalance(10)); 
        $this->assertEquals(4,count($balance->getList()));    
        $balance->resetAmount();
        $this->assertEquals(0,$balance->getAmount());
        $this->assertEquals(5,count($balance->getList()));
    }
    
    public function testZeroBorderWithPositiveBalance(){
        
        $this->markTestSkipped('needs to be refactored in new billing system');
        
        $company = $this->getRandomCompany(true);
        $balance = new Yourdelivery_Model_Billing_Balance();
        $balance->setObject($company);
        $this->assertGreaterThan(0,$balance->addBalance(1000, 'test3'));
        $this->assertGreaterThan(0,$balance->addBalance(-10000000, 'zero balance', true));
        $this->assertEquals(0,$balance->getAmount());
    }
    
    public function testZeroBorderWithNegativeBalance(){
        $this->markTestSkipped('needs to be refactored in new billing system');
        
        $company = $this->getRandomCompany(true);
        $balance = new Yourdelivery_Model_Billing_Balance();
        $balance->setObject($company);
        $this->assertGreaterThan(0,$balance->addBalance(-1000, 'test3'));
        $this->assertGreaterThan(0,$balance->addBalance(10000000, 'zero balance', true));
        $this->assertEquals(0,$balance->getAmount());
    }
    
    public function testUppderDateBound(){       
        $company = $this->getRandomCompany(true);
        $balance = new Yourdelivery_Model_Billing_Balance();
        $balance->setObject($company);
        $balance->resetAmount();
        $this->assertGreaterThan(0,$balance->addBalance(10, 'test upper bound'));
        $this->assertEquals(10,$balance->getAmount());
        $this->assertEquals(0,$balance->getAmount(time() - 100000));
        
        $service = $this->getRandomService();
        $balance = new Yourdelivery_Model_Billing_Balance();
        $balance->setObject($service);
        $balance->resetAmount();
        $this->assertGreaterThan(0,$balance->addBalance(10, 'test upper bound'));
        $this->assertEquals(10,$balance->getAmount());
        $this->assertEquals(0,$balance->getAmount(time() - 100000));        
    }
    
}
