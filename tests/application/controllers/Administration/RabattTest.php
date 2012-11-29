<?php

/**
 * Description of RabattTest
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Administration_RabattTest extends Yourdelivery_Test {
    
    public function setUp() {
        parent::setUp();
        
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.11.2010
     */
    public function testCreate(){
         
        
        $discount = $this->createDiscount();

        $this->assertTrue($discount instanceof Yourdelivery_Model_Rabatt_Code);
    }
}
?>
