<?php
/**
 * @author vpriem
 * @since 01.07.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class GoodsTest extends Yourdelivery_Test {

    /**
     * @author vpriem
     * @since 01.07.2011
     */
    public function testGetTable() {
        
        $upsellingGoods = new Yourdelivery_Model_Upselling_Goods();
        $this->assertTrue($upsellingGoods->getTable() instanceof Yourdelivery_Model_DbTable_Upselling_Goods);
    }
    
    /**
     * @author vpriem
     * @since 01.07.2011
     */
    public function testCalculate() {
        
        $inventory = new Yourdelivery_Model_Upselling_Goods();
        $inventory->setCountCanton2626(1);
        $inventory->setCostCanton2626(4900);
        $inventory->setCountCanton2828(1);
        $inventory->setCostCanton2828(6300);
        $inventory->setCountCanton3232(1);
        $inventory->setCostCanton3232(7000);
        $inventory->setCountServicing(1);
        $inventory->setCostServicing(1500);
        $inventory->setCountBags(1);
        $inventory->setCostBags(1250);
        $inventory->setCountSticks(1);
        $inventory->setCostSticks(2100);
        $inventory->save();
        
        $netto = 23050;
        $this->assertGreaterThan(0, $inventory->calculateNetto());
        $this->assertEquals($inventory->calculateNetto(), $netto);
        $this->assertEquals($inventory->calculateTax(), $tax = $netto * 0.19);
        $this->assertEquals($inventory->calculateBrutto(), $netto + $tax);
    }

    /**
     * @author vpriem
     * @since 01.07.2011
     */
    public function testGetBilling() {
        
        $inventory = new Yourdelivery_Model_Upselling_Goods();
        $this->assertEquals($inventory->getBilling(), null);
    }
    
}
