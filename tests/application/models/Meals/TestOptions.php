<?php
/**
 * @runTestsInSeparateProcesses 
 */
class TestOptions extends Yourdelivery_Test {

    /**
     * @author mlaug
     * @since 27.06.2011
     */
    public function testSetAndGetCost() {
        $optionId = $this->randomOption();
        $this->assertGreaterThan(0,$optionId);
        $option = new Yourdelivery_Model_Meal_Option($optionId);
        $cost = $option->getCost();
        $newcost = $cost + 20;
        $option->setCost($newcost);
        $this->assertEquals($newcost,$option->getCost());
    }
    
    /**
     * @author mlaug
     * @since 27.06.2011
     */
    private function randomOption() {

        $db = Zend_Registry::get('dbAdapter');
        $options = $db->fetchAll('select id from meal_options limit 1000');
        shuffle($options);
        return (integer) $options[0]['id'];
    }

}
