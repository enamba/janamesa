<?php
/**
 * @runTestsInSeparateProcesses 
 */
class CityDbTableTest extends Yourdelivery_Test {

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 18.04.2012 
     */
    public function testGetCityByPrio() {
        $cities = Yourdelivery_Model_DbTable_City::getAllCitiesByPriority(5);
        $this->assertEquals(5, count($cities));
        $cities = Yourdelivery_Model_DbTable_City::getAllCitiesByPriority(15);
        $this->assertEquals(15, count($cities));
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 18.04.2012 
     */
    public function testGetCities() {
        $cities = Yourdelivery_Model_DbTable_City::getAllCities();
        $this->assertGreaterThan(0, count($cities));
    }


}