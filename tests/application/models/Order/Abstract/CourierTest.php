<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 02.04.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Order_Abstract_CourierTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 02.04.2012
     */
    public function testComputePickUpTime() {

        $service = $this->getRandomService(array('withCourier' => true, 'premium' => true));
        $this->assertTrue($service->hasCourier());
        $courier = $service->getCourier();
        $this->assertTrue($courier instanceof Yourdelivery_Model_Courier);

        $orderId = $this->placeOrder(array('service' => $service));
        $order = new Yourdelivery_Model_Order($orderId);
        $cityId = $order->getLocation()->getCityId();
        $service->setCurrentCityId($cityId);
        $serviceDeliverTime = $service->getRealDeliverTime();
        $this->assertGreaterThan(0, $serviceDeliverTime);
        $courierDeliverTime = $courier->getDeliverTime($cityId);
        $this->assertGreaterThanOrEqual(0, $courierDeliverTime);

        $time = mktime(6, 0, 0);
        $day = date("w", $time);
        $order->setTime(date("Y-m-d H:i:s", $time));
        $order->save();
        
        $dbTable = new Yourdelivery_Model_DbTable_Restaurant_Openings();
        $dbRow = $dbTable->createRow(array(
            'restaurantId' => $service->getId(),
            'day' => $day,
            'from' => date("H:i:s", $time - 60 * 60),
            'until' => date("H:i:s", $time + 60 * 60),
        ));
        $dbRow->save();

        $order->setDeliverTime(date("Y-m-d H:i:s", $time));
        $this->assertEquals(date("Y-m-d H:i:s", $order->computePickUpTime()), date("Y-m-d H:i:s", $order->getTime() + $serviceDeliverTime));

        $order->setDeliverTime(date("Y-m-d H:i:s", $time + $serviceDeliverTime));
        $this->assertEquals(date("Y-m-d H:i:s", $order->computePickUpTime()), date("Y-m-d H:i:s", $order->getTime() + $serviceDeliverTime));

        $order->setDeliverTime(date("Y-m-d H:i:s", $time + $courierDeliverTime));
        $this->assertEquals(date("Y-m-d H:i:s", $order->computePickUpTime()), date("Y-m-d H:i:s", $order->getTime() + $serviceDeliverTime));

        $order->setDeliverTime(date("Y-m-d H:i:s", $time + $serviceDeliverTime + $courierDeliverTime + 600));
        $this->assertEquals(date("Y-m-d H:i:s", $order->computePickUpTime()), date("Y-m-d H:i:s", $order->getDeliverTime() - $courierDeliverTime));

        $order->setDeliverTime(date("Y-m-d H:i:s", $time + $serviceDeliverTime + $courierDeliverTime));
        $this->assertEquals(date("Y-m-d H:i:s", $order->computePickUpTime()), date("Y-m-d H:i:s", $order->getDeliverTime() - $courierDeliverTime));
        
        // pre-order test
        $order->setTime(date("Y-m-d H:i:s", $time - 2 * 60 * 60));
        $order->setDeliverTime(date("Y-m-d H:i:s", $time - 60 * 60));
        $this->assertEquals(date("Y-m-d H:i:s", $order->computePickUpTime()), date("Y-m-d H:i:s", $time - 60 * 60 + $serviceDeliverTime));
        
        $order->setTime(date("Y-m-d H:i:s", $time - 60 * 60 - 15));
        $order->setDeliverTime(date("Y-m-d H:i:s", $time - 60 * 60));
        $this->assertEquals(date("Y-m-d H:i:s", $order->computePickUpTime()), date("Y-m-d H:i:s", $time - 60 * 60 + $serviceDeliverTime));
        
        $dbRow->delete();
    }

}
