<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 19.12.2011
 */
/**
 * @runTestsInSeparateProcesses
 */
class AutocompleteModelTest extends Yourdelivery_Test {

    public function setUp() {
        
        parent::setUp();
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.12.2011
     * @modified Jens Naie <naie@lieferando.de> 19.06.2012
     */
    public function testPlz() {

        $plz = Yourdelivery_Model_Autocomplete::plz();
        
        $this->assertTrue(is_array($plz));
        
        $this->assertGreaterThan(0, count($plz));
        
        $this->assertTrue(array_key_exists('id', $plz[0]));
        $this->assertTrue(array_key_exists('plz', $plz[0]));
        $this->assertTrue(array_key_exists('city', $plz[0]));
        $this->assertTrue(array_key_exists('value', $plz[0]));
        $this->assertTrue(array_key_exists('restUrl', $plz[0]));

        $service = $this->getRandomService();
        $serviceId = $service->getId();
        
        $plz = Yourdelivery_Model_Autocomplete::plz($serviceId);
        
        $this->assertGreaterThan(0, count($plz));
        
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.12.2011
     */
    public function testEmployees() {

        $employees = Yourdelivery_Model_Autocomplete::employees(9876543210, "samson@tiffy.de");
        $this->assertTrue(is_array($employees));
        $this->assertEquals(count($employees), 0);

        $dbTable = new Yourdelivery_Model_DbTable_Customer();
        $dbRowCust = $dbTable->createRow(array(
            'name' => "Tiffy",
            'prename' => "Samson",
            'email' => "samson@tiffy" . time() . ".de",
        ));
        $dbRowCust->save();
        
        $dbTable = new Yourdelivery_Model_DbTable_Customer_Company();
        $dbRowRel = $dbTable->createRow(array(
            'customerId' => $dbRowCust->id,
            'companyId' => 9999,
        ));
        $dbRowRel->save();
        
        $employees = Yourdelivery_Model_Autocomplete::employees(9999, "samson@tiffy");
        $this->assertTrue(is_array($employees));
        $this->assertEquals(count($employees), 1, "Real count: " . count($employees));
        
        $dbRowCust->delete();
        $dbRowRel->delete();
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     */
    public function testProjectnumbers() {

        $dbTable = new Yourdelivery_Model_DbTable_Projectnumbers();
        $dbRow = $dbTable->createRow(array(
            'number' => "samson" . time(),
            'companyId' => 9999,
        ));
        $dbRow->save();
        
        $nr = Yourdelivery_Model_Autocomplete::projectnumbers(9999, "samson");
        $this->assertTrue(is_array($nr));
        $this->assertEquals(count($nr), 1, "Real count: " . count($nr));
        
        $dbRow->delete();
    }

}
