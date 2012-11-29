<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 01.02.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Servicetype_Abstract_DeliverTimeTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 01.02.2012
     */
    public function testGetRealDeliverTimeFormated() {

        $r = $this->getRandomService();
        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($r->getId());

        $this->assertFalse($restaurant->getRealDeliverTime());

        $ranges = $restaurant->getRanges(1);
        $range = array_shift($ranges);

        $this->assertEquals($restaurant->getRealDeliverTime($range['cityId']), $range['deliverTime']);
        $restaurant->setCurrentCityId($range['cityId']);
        $this->assertEquals($restaurant->getRealDeliverTime($range['cityId']), $restaurant->getRealDeliverTime());
    }
}
