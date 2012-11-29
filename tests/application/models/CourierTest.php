<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 10.03.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class CourierTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 10.03.2011
     */
    public function testAll() {

        $couriers = Yourdelivery_Model_Courier::all();
        $this->assertTrue($couriers instanceof SplObjectStorage);
        foreach ($couriers as $c) {
            $this->assertTrue($c instanceof Yourdelivery_Model_Courier);
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.09.2011
     */
    public function testGetDeliverTime() {

        $cityId = $this->getRandomCityId();
        
        $courier = $this->getRandomCourier();
        $courier->removeRange(null, $cityId);
        $courier->addRange($cityId, 20, 500, 1000);

        $this->assertEquals(20 * 60, $courier->getDeliverTime($cityId), 'CourierId: ' . $courier->getId() . ' RandomCityId: ' . $cityId . ' DeliverTime: ' . 20 * 60);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.09.2011
     */
    public function testGetDeliverCost() {

        $cityId = $this->getRandomCityId();
        
        $courier = $this->getRandomCourier();
        $courier->removeRange(null, $cityId);
        $courier->addRange($cityId, 20, 5, 1000);

        $this->assertEquals(500, $courier->getDeliverCost($cityId), 'CourierId: ' . $courier->getId() . ' RandomCityId: ' . $cityId . ' DeliverCost: ' . 500);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.09.2011
     */
    public function testAssignCourierToService() {

        // get random courier
        $courier = $this->getRandomCourier();
        
        // get random service
        $restaurant = $this->getRandomService();
        $ranges = count($restaurant->getRanges());

        // remove all association
        $courier->removeService($restaurant);
        
        // try to add empty model
        $this->assertFalse($courier->addService(new Yourdelivery_Model_Servicetype_Restaurant()));
        
        // try to remove not associated service should return false
        $this->assertFalse($courier->removeService($restaurant));
        
        $this->assertTrue($courier->addService($restaurant));
        $this->assertTrue($restaurant->hasCourier());
        $this->assertNotEquals($ranges, count($restaurant->getRanges(10000, true)));
        
        // remove associated service
        $this->assertTrue($courier->removeService($restaurant));
        $restaurantId = $restaurant->getId();
        $restaurant = null;
        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        $this->assertFalse($restaurant->hasCourier());
        $this->assertEquals(0, count($restaurant->getRanges(10000, true)));
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 10.03.2011
     */
    public function testAddRemoveRange() {

        // get random courier
        $courier = $this->getRandomCourier();

        // get random service and assign to courier
        $restaurant = $this->getRandomService();
        $this->assertTrue($courier->addService($restaurant));
        
        // 
        $this->assertGreaterThanOrEqual(count($courier->getRanges()), count($restaurant->getTable()->getRanges()));
        
        // unassign
        $this->assertTrue($courier->removeService($restaurant));
        $this->assertEquals(count($restaurant->getTable()->getRanges()), 0);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.03.2011
     * 
     * @todo make this test abstract for random courier
     */
    public function testGetDiscount() {


        // use prompt
        $courier = new Yourdelivery_Model_Courier(4);
        $this->assertEquals($courier->getDiscount(), 0);

        // use go
        $courier = new Yourdelivery_Model_Courier(3);
        $this->assertEquals($courier->getDiscount(), 300);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.03.2011
     * 
     * @todo make this test abstract for random courier
     */
    public function testGetTable() {


        // use prompt
        $courier = new Yourdelivery_Model_Courier(4);
        $table = $courier->getTable();
        $this->assertTrue($table instanceof Yourdelivery_Model_DbTable_Courier);
        $this->assertTrue($table === $courier->getTable());
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.03.2011
     * 
     * @todo make this test abstract for random courier
     */
    public function testGetContact() {


        // use prompt
        $courier = new Yourdelivery_Model_Courier(4);
        $this->assertTrue($courier->getContact() instanceof Yourdelivery_Model_Contact);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.03.2011
     * 
     * @todo make this test abstract for random courier
     */
    public function testGetCity() {


        // use prompt
        $courier = new Yourdelivery_Model_Courier(4);
        $this->assertTrue($courier->getCity() instanceof Yourdelivery_Model_City);
        $this->assertTrue($courier->getOrt() instanceof Yourdelivery_Model_City);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.03.2011
     * 
     * @todo make this test abstract for random courier
     */
    public function testGetCurrentService() {


        // use prompt
        $courier = new Yourdelivery_Model_Courier(4);
        $this->assertNull($courier->getCurrentService());

        $this->assertFalse($courier->setCurrentService());
        $this->assertNull($courier->getCurrentService());

        $this->assertFalse($courier->setCurrentService(new stdClass()));
        $this->assertNull($courier->getCurrentService());

        $this->assertTrue($courier->setCurrentService(new Yourdelivery_Model_Servicetype_Restaurant()));
        $this->assertTrue($courier->getCurrentService() instanceof Yourdelivery_Model_Servicetype_Restaurant);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.03.2011
     * 
     * @todo make this test abstract for random courier
     */
    public function testGetCurrentLocation() {


        // use prompt
        $courier = new Yourdelivery_Model_Courier(4);
        $this->assertNull($courier->getCurrentLocation());

        $this->assertFalse($courier->setCurrentLocation());
        $this->assertNull($courier->getCurrentLocation());

        $this->assertFalse($courier->setCurrentLocation(new stdClass()));
        $this->assertNull($courier->getCurrentLocation());

        $this->assertTrue($courier->setCurrentLocation(new Yourdelivery_Model_Location()));
        $this->assertTrue($courier->getCurrentLocation() instanceof Yourdelivery_Model_Location);
    }

}
