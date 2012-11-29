<?php

/**
 *
 * @author Alex Vait <vait@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Administration_CityTest extends Yourdelivery_Test {

    protected static $db;
    
    public function setUp() {
        parent::setUp();
        self::$db = Zend_Registry::get('dbAdapter');
        
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 08.05.2012
     */
    public function testCreateSuccess() {
        $city = new Yourdelivery_Model_City($this->getRandomCityId());
        
        $mictime = microtime();
        $testPlz = str_replace('.', '', $mictime);
        $testPlz = str_replace(' ', '', $testPlz);
        $testPlz = substr($testPlz, 0, 14);
        $testCity = Default_Helper::generateRandomString();
        $testState = $city->getState();
        $testStateId = $city->getStateId();        
        
        $post = array(
            'plz' => $testPlz,
            'city' => $testCity,
            'state_stateId' => $testState . '_' . $testStateId
        );

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('administration_city/create');
        
        $cityId = (integer) self::$db->fetchOne('SELECT MAX(`id`) FROM `city`');
        
        $this->assertRedirect('administration_city/edit/cityId/' . $cityId);
        
        $createdCity = new Yourdelivery_Model_City($cityId);
        $this->assertEquals($testPlz, $createdCity->getPlz());
        $this->assertEquals($testCity, $createdCity->getCity());
        $this->assertEquals($testState, $createdCity->getState());
        $this->assertEquals($testStateId, $createdCity->getStateId());
    }
    
    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 08.05.2012
     */
    public function testCreateFailure() {
        $cityId = (integer) self::$db->fetchOne('SELECT MAX(`id`) FROM `city`');
        $oldCity = new Yourdelivery_Model_City($cityId);

        $testPlz = $oldCity->getPlz();
        $testCity = $oldCity->getCity();
        $testState = $oldCity->getState() . 'X';
        $testStateId = $oldCity->getStateId() + 1;        
        
        $post = array(
            'plz' => $testPlz,
            'city' => $testCity,
            'state_stateId' => $testState . '_' . $testStateId
        );

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('administration_city/create');
        
        $cityId = (integer) self::$db->fetchOne('SELECT MAX(`id`) FROM `city`');
        
        $createdCity = new Yourdelivery_Model_City($cityId);
        $this->assertEquals($testPlz, $createdCity->getPlz());
        $this->assertEquals($testCity, $createdCity->getCity());
        $this->assertNotEquals($testState, $createdCity->getState());
        $this->assertNotEquals($testStateId, $createdCity->getStateId());
    }    

}