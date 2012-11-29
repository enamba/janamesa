<?php
/**
 * @author vpriem
 * @since 15.03.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class LocationTest extends Yourdelivery_Test {

    /**
     * @author vpriem
     * @since 15.03.2011
     */
    public function testGetCity(){
         

        $location = new Yourdelivery_Model_Location();
        $location->setData(array(
            'street' => "Paper Street",
            'hausnr' => "1537",
            'cityId' => 774,
            'plz' => "13086",
        ));

        $city = $location->getCity();
        $this->assertTrue($city instanceof Yourdelivery_Model_City);
        $this->assertTrue($city === $location->getCity());
        $this->assertTrue($location->getOrt() instanceof Yourdelivery_Model_City);
        $this->assertTrue($location->getOrt() === $location->getCity());
        $this->assertEquals($location->getPlz(), $location->getCity()->getPlz());

    }

    /**
     * @author vpriem
     * @since 15.03.2011
     */
    public function testAddress(){
         

        $location = new Yourdelivery_Model_Location();
        $location->setData(array(
            'street' => "Paper Street",
            'hausnr' => "1537",
            'cityId' => 774,
            'plz' => "13086",
        ));

        $this->assertEquals($location->getAddress(), "13086 Berlin Paper Street 1537");

    }

    /**
     * @author vpriem
     * @since 15.03.2011
     */
    public function testGetLatLng(){
         

        $location = new Yourdelivery_Model_Location();
        $location->setData(array(
            'street' => "Goethestr.",
            'hausnr' => "49",
            'cityId' => 774,
            'plz' => "13086",
            'latitude' => "52.5375595",
            'longitude' => "13.3740320",
        ));

        $this->assertEquals($location->getLatitude(), "52.5375595");
        $this->assertEquals($location->getLatitude(), $location->getLat());
        $this->assertEquals($location->getLongitude(), "13.3740320");
        $this->assertEquals($location->getLongitude(), $location->getLng());

    }

    /**
     * @author vpriem
     * @since 15.03.2011
     */
    public function testAddition(){
         

        $location = new Yourdelivery_Model_Location();
        $location->setData(array(
            'street' => "Paper Street",
            'hausnr' => "1537",
            'cityId' => 774,
            'plz' => "13086",
        ));

        $this->assertEquals($location->getAddition(), "");

        $location->setEtage("21");
        $this->assertEquals($location->getAddition(), "21");

        $location->setCompanyName("Soap Co.");
        $this->assertEquals($location->getAddition(), "Soap Co., 21");

        $location->setComment("The first rule of Fight Club is you do not talk about ...");
        $this->assertEquals($location->getAddition(), "Soap Co., 21, The first rule of Fight Club is you do not talk about ...");

    }

    /**
     * @author vpriem
     * @since 14.03.2011
     */
    public function testGetTable(){
         

        $location = new Yourdelivery_Model_Location();
        $table = $location->getTable();
        $this->assertTrue($table instanceof Yourdelivery_Model_DbTable_Locations);
        $this->assertTrue($table === $location->getTable());

    }
    
    /**
     * check if this customer has a primary address
     * @author mlaug
     * @since 11.11.2011
     * @return boolean
     */
    public function testPrimaryLocation(){
        $customer = $this->getRandomCustomer();
        $location = $this->getRandomLocation($customer->getId());
                          
        $this->assertEquals($location->getCustomer()->getId(), $customer->getId(),"Location Id: ". $location->getId(). ": Customer Id: ".$customer->getId(). ": Location Customer: ".$location->getCustomer()->getId());
        
        $location->getTable()->resetPrimaryAddress($customer->getId());
        
        $customer = new Yourdelivery_Model_Customer($customer->getId());
        
        $this->assertFalse($customer->hasPrimaryLocation(),"Customer Id: ".$customer->getId());
        $location->setPrimary(true);
        $location->save();
        $this->assertTrue($customer->hasPrimaryLocation());
        $this->assertEquals($customer->getLocations(null, true)->getId(),$location->getId());
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.05.2012
     */
    public function testGetHausnrWithAppartment(){
        $location = new Yourdelivery_Model_Location();
        
        $number = rand(0, 1000);
        $appartment = rand(0, 1000);
        
        //without appartment
        $location->setHausnr($number);
        $location->setStreet('Foobar');
        $this->assertEquals($number, $location->getHausnr());
        $this->assertEquals($number, $location->getHausnr(true));
        $this->assertEquals($number, $location->getHausnr(false));
        
        //with appartment
        $hausnr_complete = $number . '/' . $appartment;
        $location->setHausnr($hausnr_complete);
        $this->assertEquals($hausnr_complete, $location->getHausnr());
        $this->assertEquals($number, $location->getHausnr(true));
        $this->assertEquals($hausnr_complete, $location->getHausnr(false));
        $this->assertEquals($appartment, $location->getAppartment());
    }

    
    /**
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    public function testGetNearAreas(){
        $location = $this->getRandomLocation();
        $areas = $location->getNearAreas(10);
        $this->assertEquals(10, count($areas));
    }
    
    /**
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    public function testGetNearPlzs(){
        $location = $this->getRandomLocation();
        $areas = $location->getNearPlzs(10);
        $this->assertEquals(10, count($areas));
    }
    
    /**
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    public function testGetOtherRegions(){
        $location = $this->getRandomLocation();
        $regions = $location->getOtherRegions(0, 10);
        $this->assertEquals(11, count($regions));
    }
    
    /**
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    public function testGetGeoCoordinates(){
        
        $this->markTestIncomplete('it is needed to set specified route before, because depending on the route, the depth is defined');
        
        $location = $this->getRandomLocation();
        $geoArr = $location->getGeoCoordinates();
        $this->assertArrayHasKey('lat', $geoArr);
    }
}
