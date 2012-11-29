<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 23.12.2011
 */
class YourdeliveryApiInterkepTest extends Yourdelivery_Test {
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 23.12.2011
     * TODO: modify placeOrder to return an order with a courier
     */
    public function testImportFile() {
        
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        
        $api = new Yourdelivery_Api_Interkep($order);
        $this->assertEquals(substr($api->getImportFileName(), -4), ".imp");
        
//        $file = file_get_contents($api->getImportFile());
//        $values = explode(";", $file);
//        $this->assertEquals(count($values), 68);
//        $this->assertEquals($values[0], "S");
//        $this->assertEquals($values[1], "25");
//        $this->assertEquals($values[17], 51870);
    }
    
}
