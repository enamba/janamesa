<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 04.04.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Servicetype_Abstract_HasSmsNotifyTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 04.04.2012
     */
    public function testHasSmsNotify() {

        $service = $this->getRandomService();
        
        $service->setNotify("sms");
        $this->assertTrue($service->hasSmsNotify());
        
        $service->setNotify("smsemail");
        $this->assertTrue($service->hasSmsNotify());
        
        $service->setNotify("fax");
        $this->assertFalse($service->hasSmsNotify());
        
        $service->setNotify("email");
        $this->assertFalse($service->hasSmsNotify());
    }
}
