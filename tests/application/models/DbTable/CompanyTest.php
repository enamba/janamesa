<?php
/**
 * Description of CompanyTest
 *
 * @author ydadmin
 */
/**
 * @runTestsInSeparateProcesses 
 */
class DbTableCompanyTest extends Yourdelivery_Test{ 
    
    
    /**
     * @todo REFACTOR - this test does not guarantee, that correct results are found, this test asserts only type of result :(
     */
    public function testFindBy() {
        
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                        ->from(array("c" => "companys"))
                        ->where("c.comment != 0")
                        ->where("c.contactId != 0")
                        ->where("c.customerNr != 0")                        
                        ->where("c.hausnr != 0" )
                        ->where("c.id != 0")
                        ->where("c.industry != ''")
                        ->where("c.ktoBlz != 0" )
                        ->where("c.ktoName != ''")
                        ->where("c.ktoNr != ''" )
                        ->where("c.name != ''")
                        ->where("c.payment != ''" )
                        ->where("c.plz != 0")
                        ->where("c.created != 0")
                        ->where("c.status != 0")                        
                        ->where("c.street != ''")
                        ->where("c.website != ''" );

        $result =  $db->fetchRow($query);
        
        $this->assertTrue(is_array($result));
        
        $comment = Yourdelivery_Model_DbTable_Company::findByComment($result['comment']);
        $this->assertTrue(is_array($comment));
        
        $contactId = Yourdelivery_Model_DbTable_Company::findByContactId($result['contactId']);
        $this->assertTrue(is_array($contactId));
        
        $customerNr = Yourdelivery_Model_DbTable_Company::findByCustomerNr($result['customerNr']);
        $this->assertTrue(is_array($customerNr));
        
        $hausNr =  Yourdelivery_Model_DbTable_Company::findByHausnr($result['hausnr']);
        $this->assertTrue(is_array($hausNr));
        
        $id = Yourdelivery_Model_DbTable_Company::findById($result['id']);
        $this->assertTrue(is_array($id));
        
        $industry =   Yourdelivery_Model_DbTable_Company::findByIndustry($result['industry']);
        $this->assertTrue(is_array($industry));
        
        $ktoBlz   =  Yourdelivery_Model_DbTable_Company::findByKtoBlz($result['ktoBlz']);
        $this->assertTrue(is_array($ktoBlz));
        
        $ktoName  =  Yourdelivery_Model_DbTable_Company::findByKtoName($result['ktoName']);
        $this->assertTrue(is_array($ktoName));
        
        $ktoNr =  Yourdelivery_Model_DbTable_Company::findByKtoNr($result['ktoNr']);
        $this->assertTrue(is_array($ktoNr));      
        
        $name = Yourdelivery_Model_DbTable_Company::findByName($result['name']);
        $this->assertTrue(is_array($name));
        
        $payment = Yourdelivery_Model_DbTable_Company::findByPayment($result['payment']);
        $this->assertTrue(is_array($payment));
        
        $plz = Yourdelivery_Model_DbTable_Company::findByPlz($result['plz']);
        $this->assertTrue(is_array($plz));
        
        $created = Yourdelivery_Model_DbTable_Company::findByRegtime($result['created']);
        $this->assertTrue(is_array($created));
        
        $status = Yourdelivery_Model_DbTable_Company::findByStatus($result['status']);
        $this->assertTrue(is_array($status));
        
        $street = Yourdelivery_Model_DbTable_Company::findByStreet($result['street']);
        $this->assertTrue(is_array($street));
        
        $website = Yourdelivery_Model_DbTable_Company::findByWebsite($result['website']);
        $this->assertTrue(is_array($website));    
   
        $query = $db->select()
                     ->from(array("c" => "companys"))
                     ->where("c.steuerNr != 0" );         
        $result =  $db->fetchRow($query);
        $steuerNr =  Yourdelivery_Model_DbTable_Company::findBySteuerNr($result['steuerNr']);
        $this->assertTrue(is_array($steuerNr));     
        
    }
    
    
    public function testGet() {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->query('select count(id) as num from companys');
        
        $num = $query->fetchAll();
       
        $companies = Yourdelivery_Model_DbTable_Company::get();

        $this->assertEquals(count($companies), $num[0]['num']);
                        
    }
    
    public function testGetOrders() {
        
        $company = $this->getRandomCompany();
        
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->query('SELECT count(orders.id) as num_ids 
            FROM orders LEFT JOIN customer_company ON orders.customerId=customer_company.customerId 
            WHERE customer_company.companyId= ? 
            ORDER BY orders.time DESC', $company->getId());
        $num = $query->fetchAll();
        $company->getTable()->setId($company->getId());
        $orders = $company->getTable()->getOrders();                
        
        $this->assertTrue($orders instanceof SplObjectStorage);
        $this->assertEquals($num[0]['num_ids'], $orders->count());
        
    }
    
}

?>
