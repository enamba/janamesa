<?php
/**
 * @author mlaug
 * @since 24.08.2010
 */
class HelpersMoneyTest extends Yourdelivery_Test{

    /**
     * @author mlaug
     * @since 28.10.2010
     */
    public function testTaxes(){
        $brutto = 2000;
        $tax20 = 333.33;
        $tax19 = 319.33;
        $tax7 = 130.84;
        $this->assertEquals(round(Default_Helpers_Money::getTax($brutto, 20),2), $tax20);
        $this->assertEquals(round(Default_Helpers_Money::getTax($brutto,19), 2),$tax19);
        $this->assertEquals(round(Default_Helpers_Money::getTax($brutto,7), 2),$tax7);
    }

    /**
     * @author mlaug
     * @since 28.10.2010
     */
    public function testNetto(){
        $brutto = 2000;
        $netto20 = 1666.67;
        $netto19 = 1680.67;
        $netto7 = 1869.16;
        $this->assertEquals(round(Default_Helpers_Money::getNetto($brutto, 20),2), $netto20);
        $this->assertEquals(round(Default_Helpers_Money::getNetto($brutto,19)),round($netto19));
        $this->assertEquals(round(Default_Helpers_Money::getNetto($brutto,7)),round($netto7));
    }


}
