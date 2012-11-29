<?php
/**
 * @author vpriem
 * @since 02.11.2010
 */
/**
 * @runTestsInSeparateProcesses 
 */
class ApiPromptTest extends Yourdelivery_Test{

    public function setUp() {
        parent::setUp();
    }

    /**
     * @author vpriem
     * @since 02.11.2010
     */
    public function testRatesBook(){
         

        $order = new Yourdelivery_Model_Order(68886);
        $api   = new Yourdelivery_Model_Api_Prompt($order);

        $rateId = $api->rates();
        $this->assertTrue($rateId !== false);

        $trackingId = $api->book($rateId);
        $this->assertTrue($trackingId !== false);

    }

}
