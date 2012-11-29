<?php

/**
 * @runTestsInSeparateProcesses 
 */
class OptionsTest extends Yourdelivery_Test {

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 10.07.2012
     */
    public function testDuplicate() {

        $optRowName = 'testOptionRowName_' . time();
        $optRowDesc = 'testOptionRowDesc_' . time();
        $restaurantId = rand(100, 1000);
        $categoryId = rand(100, 1000);
        $newCategoryId = rand(100, 1000);

        // create options group
        $optionsRow = new Yourdelivery_Model_Meal_OptionRow();
        $optionsRow->setData(
                array(
                    'name' => $optRowName,
                    'description' => $optRowDesc,
                    'choices' => 2,
                    'categoryId' => $categoryId,
                    'restaurantId' => $restaurantId
                )
        );
        $optionsRow->save();

        $optionsToSet = array();

        for ($i = 0; $i < 5; $i++) {
            $name = 'testOptName' . rand(100, 1000) . microtime();
            $cost = rand(100, 1000);

            $optionsToSet[$name] = $cost;

            $option = new Yourdelivery_Model_Meal_Option();
            $option->setData(
                    array(
                        'name' => $name,
                        'optRow' => $optionsRow->getId(),
                        'restaurantId' => $restaurantId,
                        'cost' => $cost
                    )
            );
            $option->save();

            $option_nn = new Yourdelivery_Model_Meal_OptionsNn();
            $option_nn->setOptionId($option->getId());
            $option_nn->setOptionRowId($optionsRow->getId());
            $option_nn->save();
        }

        $newOptRowId = $optionsRow->duplicate($newCategoryId);

        $this->assertGreaterThan(0, $newOptRowId);

        $optionsRowNew = new Yourdelivery_Model_Meal_OptionRow($newOptRowId);
        $this->assertGreaterThan(0, $optionsRowNew->getId());

        $this->assertEquals($optionsRowNew->getCategoryId(), $newCategoryId);
        $this->assertEquals($optionsRowNew->getRestaurantId(), $optionsRow->getRestaurantId());
        $this->assertEquals($optionsRowNew->getChoices(), $optionsRow->getChoices());
        $this->assertEquals($optionsRowNew->getMinChoices(), $optionsRow->getMinChoices());
        $this->assertEquals($optionsRowNew->getName(), $optionsRow->getName());
        $this->assertEquals($optionsRowNew->getInternalName(), $optionsRow->getInternalName());
        $this->assertEquals($optionsRowNew->getDescription(), $optionsRow->getDescription());

        $this->assertEquals(count($optionsRowNew->getOptions()), 5);

        foreach ($optionsRowNew->getOptions() as $opt) {
            $this->assertEquals($optionsToSet[$opt->getName()], $opt->getCost());
        }
    }

    /**
     * @author mlaug
     * @since 27.06.2011
     */
    public function testSetAndGetCost() {
        $optionId = $this->getRandomOptionId();
        $this->assertGreaterThan(0, $optionId);
        $option = new Yourdelivery_Model_Meal_Option($optionId);
        $cost = $option->getCost();
        $newcost = $cost + 20;
        $option->setCost($newcost);
        $this->assertEquals($newcost, $option->getCost());
    }

    /**
     * @author mlaug
     * @since 27.06.2011
     * 
     * @return integer OptionId
     */
    private function getRandomOptionId() {

        $db = Zend_Registry::get('dbAdapter');
        $options = $db->fetchAll('select id from meal_options limit 1000');
        shuffle($options);
        return (integer) $options[0]['id'];
    }

}
