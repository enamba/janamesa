<?php

/**
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses 
 */
class HelperMoneyTest extends Yourdelivery_Test{

    /**
     * @author mlaug
     * @since 25.10.2010
     */
    public function testInt2Price(){
         

        $price = 2000;
        $this->assertEquals(Default_Helpers_Money::priceToInt($price),'20,00');
        $this->assertEquals(Default_Helpers_Money::priceToInt($price,2,'.'),'20.00');

        $price /= 60; // = 33,33333...
        $this->assertEquals(Default_Helpers_Money::priceToInt($price),'0,33');
        $this->assertEquals(Default_Helpers_Money::priceToInt($price,2,'.'),'0.33');
        $this->assertEquals(Default_Helpers_Money::priceToInt($price,3),'0,333');

    }

}
