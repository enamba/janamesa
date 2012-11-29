<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 17.07.2012
 */

/**
 * @runTestsInSeparateProcesses
 */
class Servicetype_Abstract_FranchiseTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.07.2012
     */
    public function testGetHas() {

        $service = $this->getRandomService();
        
        $service->setFranchiseTypeId(0);
        $this->assertFalse($service->hasFranchise());
        
        $service->setFranchiseTypeId(1);
        $this->assertTrue($service->hasFranchise());
        $this->assertTrue($service->getFranchise() instanceof Yourdelivery_Model_Servicetype_Franchise);
        $this->assertEquals(1, $service->getFranchise()->getId());
        $this->assertTrue($service->hasFranchise($service->getFranchise()->getName()));
        $this->assertFalse($service->hasFranchise(uniqid()));
    }

}
