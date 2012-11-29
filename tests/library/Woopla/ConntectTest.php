<?php

/**
 * @author mlaug
 */
class WooplaConnectTest extends Yourdelivery_Test {

    public function testWooplaCall(){
        $this->markTestSkipped('Matthias Laug <matthias.laug@gmail.com> 12.05.2012 auskommentieren von woopla, nervt der scheiß');        
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $woopla = new Woopla_Connect();
        $woopla->setOrder($order);
        $this->assertTrue($woopla->call());
    }
    

}
