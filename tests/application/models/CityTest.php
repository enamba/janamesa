<?php

/**
 * Description of CityTest
 *
 * @author alex
 */
/**
 * @runTestsInSeparateProcesses 
 */
class CityTest extends Yourdelivery_Test {

    private $_cityId1 = null;
    private $_cityId2 = null;

    public function setUp() {
        parent::setUp(false);

        $values = Yourdelivery_Model_City::getByPlz('99999');

        // delete all test values
        foreach ($values as $c) {
            Yourdelivery_Model_DbTable_City::remove($c['id']);
        }

        $timestamp = date('Y-m-d H:i:s');

        if ($this->_cityId1 === null) {
            //create test city entry
            $city1 = new Yourdelivery_Model_City();
            $city1->setData(array(
                'plz' => '99999',
                'city' => 'City 1',
                'state' => 'Teststate' . time(),
                'stateId' => '666',
                'created' => $timestamp
            ));
            $this->_cityId1 = $city1->save();
        }

        if ($this->_cityId2 === null) {
            //create second test city entry
            $city2 = new Yourdelivery_Model_City();
            $city2->setData(array(
                'plz' => '99999',
                'city' => 'City 2',
                'state' => 'Teststate' . time(),
                'stateId' => '666',
                'created' => date('Y-m-d H:i:s')
            ));
            $this->_cityId2 = $city2->save();
        }
    }

    /**
     * Create city 1 for testing
     * @author alex
     * @since 09.02.2011
     * @return Yourdelivery_Model_City
     */
    private function _createTestCity1() {
        return new Yourdelivery_Model_City($this->_cityId1);
    }

    /**
     * Create city 2 for testing
     * @author alex
     * @since 09.02.2011
     * @return Yourdelivery_Model_City
     */
    private function _createTestCity2() {
        return new Yourdelivery_Model_City($this->_cityId2);
    }

    /**
     * test object creation
     * @author alex
     * @since 09.03.2011
     */
    public function testCreate() {
        // this should succed
        $city1 = $this->_createTestCity1();
        $this->assertNotNull($city1);
        $this->assertNotNull($city1->getId());

        // this should succed
        $city2 = $this->_createTestCity2();
        $this->assertNotNull($city2);
        $this->assertNotNull($city1->getId());
    }

    /**
     * test object values
     * @author alex
     * @since 09.03.2011
     */
    public function testValues() {
        // the values must be equal
        $city1 = $this->_createTestCity1();

        $this->assertEquals($city1->getPlz(), '99999');
        $this->assertEquals($city1->getCity(), 'City 1');
        $this->assertEquals($city1->getStateId(), '666');

        // the values must be equal
        $city2 = $this->_createTestCity2();

        $this->assertEquals($city2->getPlz(), '99999');
        $this->assertEquals($city2->getCity(), 'City 2');
        $this->assertEquals($city2->getStateId(), '666');
    }

    /**
     * test plz values by city
     * @author alex
     * @since 09.03.2011
     */
    public function testGetByCity() {
        $values = Yourdelivery_Model_City::getByCity('City 1');

        $found1 = false;
        $found2 = false;

        // test the plz that must be in and an empty plz value
        foreach ($values as $c) {
            if ($c['plz'] == '99999') {
                $found1 = true;
            } else if ($c['plz'] == '') {
                $found2 = true;
            }
        }

        $this->assertTrue($found1);
        $this->assertFalse($found2);
    }

    /**
     * test cities values by plz
     * @author alex
     * @since 09.03.2011
     */
    public function testGetByPlz() {


        $values = Yourdelivery_Model_City::getByPlz('99999');

        $found1 = false;
        $found2 = false;
        $found3 = false;

        // test the two cities that must be in and an empty value
        foreach ($values as $c) {
            if (strcmp($c['city'], 'City 1') == 0) {
                $found1 = true;
            } else if (strcmp($c['city'], 'City 2') == 0) {
                $found2 = true;
            } else if (strcmp($c['city'], '') == 0) {
                $found3 = true;
            }
        }

        $this->assertTrue($found1);
        $this->assertTrue($found2);
        $this->assertFalse($found3);
    }

    /**
     * @author vpriem
     * @since 14.03.2011
     */
    public function testGetTable() {


        // use prompt
        $city1 = $this->_createTestCity1();
        $table = $city1->getTable();
        $this->assertTrue($table instanceof Yourdelivery_Model_DbTable_City);
        $this->assertTrue($table === $city1->getTable());
    }

    /**
     * @author vpriem
     * @since 14.03.2011
     */
    public function testGetOrt() {


        // use prompt
        $city1 = $this->_createTestCity1();
        $this->assertEquals($city1->getOrt(), $city1->getCity());
        $this->assertEquals($city1->getOrt(), "City 1");
        $this->assertEquals($city1->getCity(), "City 1");
    }

    /**
     * @author vpriem
     * @since 26.04.2011
     */
    public function testToString() {


        // use prompt
        $city = new Yourdelivery_Model_City();
        $this->assertTrue($city->__toString() === "");

        $city1 = $this->_createTestCity1();
        $this->assertEquals($city1->__toString(), "City 1");
    }

    /**
     * @author vpriem
     * @since 18.08.2011
     */
    public function testGetFullName() {

        $city1 = $this->_createTestCity1();
        $this->assertEquals($city1->getFullName(), "City 1");

        $city2 = $this->_createTestCity2();
        $city2->setParentCityId($city1->getId());
        $city2->save();
        $this->assertEquals($city2->getFullName(), "City 1 (City 2)");
    }

    /**
     * test found plz by starting number
     * @author alex
     * @since 03.05.2011
     */
    public function testStartingAt() {


        for ($testingPlz = 0; $testingPlz < 10; $testingPlz++) {
            $plzs = Yourdelivery_Model_City::allStartingAt($testingPlz);
            foreach ($plzs as $value) {
                $this->assertEquals(substr($value['plz'], 0, 1), $testingPlz);
            }
        }
    }
    
}
