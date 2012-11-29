<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 02.04.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Servicetype_CourierTest extends Yourdelivery_Test {
    
    /**
    * @author Vincent Priem <priem@lieferando.de>
    * @since 02.04.2012
    */
    public function testDeliverTime() {
        
        $service = $this->getRandomService(array('withCourier' => true, 'premium' => true));
        $this->assertTrue($service->hasCourier());
        $courier = $service->getCourier();
        $this->assertTrue($courier instanceof Yourdelivery_Model_Courier);
        
        $ranges = $service->getRanges(20);
        shuffle($ranges);
        $range = array_shift($ranges);
        
        $service->setCurrentCityId($range['cityId']);
        $this->assertEquals($service->getCurrentCityId(), $range['cityId']);
        
        $serviceDeliverTime = $service->getRealDeliverTime();
        $this->assertEquals($serviceDeliverTime, $service->getRealDeliverTime($range['cityId']));
        
        $courierDeliverTime = $courier->getDeliverTime($range['cityId']);
        $this->assertEquals($service->getDeliverTime(), $serviceDeliverTime + $courierDeliverTime);
    }
    
}
