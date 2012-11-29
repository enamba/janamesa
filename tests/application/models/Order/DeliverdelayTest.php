<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 20.07.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Order_DeliverdelayTest extends Yourdelivery_Test {
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.07.2012
     */
    public function testCompute() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);

        $deliverDelay = $order->getDeliverDelay();
        $this->assertTrue($deliverDelay instanceof Yourdelivery_Model_Order_Deliverdelay);
        $this->assertTrue($deliverDelay->getId() > 0);
        $this->assertEquals($deliverDelay->computeDelay(), $deliverDelay->getServiceDeliverDelay() + $deliverDelay->getCourierDeliverDelay());
        
        $service = $order->getService();
        $location = $order->getLocation();
        $this->assertEquals($deliverDelay->getServiceDeliverDelay(), $service->getRealDeliverTime($location->getCityId()));
        $this->assertEquals($deliverDelay->getCourierDeliverDelay(), 0);
        
        $this->assertEquals($order->computeArrivalTime(), $order->getTime() + $deliverDelay->computeDelay());
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.07.2012
     */
    public function testComputeFormated() {
        
        $deliverDelay = new Yourdelivery_Model_Order_Deliverdelay();
        
        $deliverDelay->setCourierDeliverDelay(0);
        $deliverDelay->setServiceDeliverDelay(1800);
        $this->assertEquals($deliverDelay->computeDelayFormated(), "30 Min.");
        
        $deliverDelay->setServiceDeliverDelay(3600);
        $this->assertEquals($deliverDelay->computeDelayFormated(), "1 Std.");
        
        $deliverDelay->setServiceDeliverDelay(4800);
        $this->assertEquals($deliverDelay->computeDelayFormated(), "1 Std. 20 Min.");
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.07.2012
     */
    public function testComputeWithCourier() {
        
        $orderId = $this->placeOrder(array(
            'service' => $this->getRandomService(array('withCourier' => true, 'premium' => true)),
        ));
        $order = new Yourdelivery_Model_Order($orderId);

        $deliverDelay = $order->getDeliverDelay();
        $this->assertTrue($deliverDelay instanceof Yourdelivery_Model_Order_Deliverdelay);
        $this->assertTrue($deliverDelay->getId() > 0);
        $this->assertEquals($deliverDelay->computeDelay(), $deliverDelay->getServiceDeliverDelay() + $deliverDelay->getCourierDeliverDelay());
        
        $service = $order->getService();
        $location = $order->getLocation();
        $courier = $service->getCourier();
        $this->assertEquals($deliverDelay->getServiceDeliverDelay(), $service->getRealDeliverTime($location->getCityId()));
        $this->assertEquals($deliverDelay->getCourierDeliverDelay(), $courier->getDeliverTime($location->getCityId()));
    }
    
}
