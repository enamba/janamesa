<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DominosTest
 *
 * @author mlaug
 */
class YourdeliveryApiDominosTest extends Yourdelivery_Test {
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.03.2012 
     */
    public function testPlaceOrder(){
        $this->markTestIncomplete('Not implemented yet - http://ticket/browse/YD-2830');        
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $api = new Yourdelivery_Api_Dominos_Rest();
        $result = $api->doOrder($order);
        $this->assertTrue($result, $result);
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.03.2012 
     */
    public function testIsOnline(){
        $this->markTestIncomplete('Not implemented yet - http://ticket/browse/YD-2830');
        $api = new Yourdelivery_Api_Dominos_Rest();
        $this->assertTrue($api->Online());
    }
    
}

?>
