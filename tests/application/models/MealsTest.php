<?php

/**
 * @runTestsInSeparateProcesses 
 */
class MealsTest extends Yourdelivery_Test {

    /**
     * @author mlaug
     * @modified daniel 
     * @since 18.11.2011
     */
    public function testAddRating() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $ids = $order->getMealIds();
        foreach ($ids as $mealId) {
            $meal = new Yourdelivery_Model_Meals($mealId);
            $this->assertEquals($mealId, $meal->getRatings()->getMeal()->getId());
            $this->assertFalse($meal->getRatings()->isRated($order));
            $this->assertTrue($meal->getRatings()->addRating($order, 10, 'no comment, sucks'));
            //update
            $this->assertTrue($meal->getRatings()->addRating($order, 10, 'no comment, sucks'));
            $this->assertTrue($meal->getRatings()->isRated($order));
            $this->assertGreaterThan(0, $meal->getRatings()->getCount());
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 11.08.2011
     */
    public function testHasSpecials() {
        $service = $this->getRandomService();
        $mealObj = $this->getRandomMealFromService($service);

        $size = current($mealObj->getSizes());
        $mealObj->setCurrentSize($size['id']);

        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('UPDATE meal_sizes_nn SET hasSpecials = 1 WHERE mealId = %d', $mealObj->getId());
        $db->query($sql);
        $this->assertTrue($mealObj->hasSpecials());

        $sql = sprintf('UPDATE meal_sizes_nn SET hasSpecials = 0 WHERE mealId = %d', $mealObj->getId());
        $db->query($sql);

        $this->assertFalse($mealObj->hasSpecials());

        $mealObj->setHasSpecials(1);
        $this->assertTrue($mealObj->hasSpecials());
    }

    public function testMealsGetOptionsCount() {
        $service = $this->getRandomService();
        $mealObj = $this->getRandomMealFromService($service);
        $options = $mealObj->getOptions();
        $count = 0;
        foreach ($options as $option) {
            $count += $option->getChoices();
        }

        $this->assertEquals($mealObj->getOptionsChoicesCount(), $count);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 11.01.2012
     */
    public function testSetAndRemoveImage() {
        $file = APPLICATION_PATH_TESTING . '/../data/meals/samson.jpg';
        $service = $this->getRandomService();
        $meal = $this->getRandomMealFromService($service);
        $this->assertTrue($meal->setImg($file));
        $this->assertTrue((boolean) $meal->getHasPicture());
        $this->assertTrue($meal->setImg($file));
        $this->assertFalse($meal->setImg($file, true));
        $this->assertTrue($meal->removeImg());
        $this->assertFalse($meal->removeImg());
        $this->assertFalse((boolean) $meal->getHasPicture());
    }

    /**
     * @author Mohammad RAWAQA <rawaqa@lieferando.de>
     * @since 13.04.2012
     */
    public function testExcludeFromMinCost() {
        $service = $this->getRandomService();
        $meal = $this->getRandomMealFromService($service);
        $category = $meal->getCategory()->getData();
        $isExcluded = $category['excludeFromMinCost'];
        $this->assertEquals($isExcluded, $meal->getExcludeFromMinCost());
    }

    /**
     * @author Allen Frank <frank@lieferando.de> 
     * @since 26-06-12
     */
    public function testGetExtras() {
        $db = Zend_Registry::get('dbAdapter');
        $service = $this->getRandomService();
        $meal = $this->getRandomMealFromServiceWithExtras($service, true);
        if (!is_null($meal)) {
            $this->assertTrue(is_array($meal->getExtras()));
            $extra = array_pop(array_pop(array_pop($meal->getExtras())));
            $infos = '['.$extra['id'] . '|' . $extra['cost'] . '|' . $meal->getId() . '|' . $meal->getCategoryId() . '|' . $meal->getCurrentSize().']';
            try {
                $select = $db->select()
                        ->from(array('mer' => 'meal_extras_relations'))
                        ->where('mer.extraId = ?', $extra['id'])
                        ->where('mer.cost = ?', $extra['cost'])
                        ->where('mer.sizeId = ?', $meal->getCurrentSize())
                        ->where(sprintf('mer.mealId = %s or mer.categoryId = %s', $meal->getId(), $meal->getCategoryId()));
                $count = count($db->fetchAll($select));
            } catch (Zend_Db_Statement_Exception $e) {
                $this->assertFalse(TRUE, sprintf('Some arguments are missing: %s',$infos));
            }
            $this->assertGreaterThanOrEqual(1, $count, $infos);
        } else {
            $this->markTestSkipped("Couldn't create meal with extras");
        }
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 09.07.2012
     */
    public function testHasAttribute(){
        $service = $this->getRandomService();
        $meal = $this->getRandomMealFromService($service);
        
        $meal->setAttributes(implode(',', array('bio', 'garlic')));
        $this->assertTrue($meal->hasAttribute('bio'));
        $this->assertTrue($meal->hasAttribute('garlic'));
        $this->assertFalse($meal->hasAttribute('vodka'));
        
        $this->assertEquals($meal->getAtributesAsString(), "Bio,Knoblauch");
    }
    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 12.07.2012
     */
    public function testIngredient(){
        $service = $this->getRandomService();
        $meal = $this->getRandomMealFromService($service);
        
        $meal->removeIngredients();
        $this->assertEquals(count($meal->getIngredients()), 0);

        $ingredient1 = new Yourdelivery_Model_Meal_Ingredients();
        $ingredient1->setName($name1 = 'ztestingredient' . rand(10, 1000));
        $ingredient1->setGroupId(1);
        $ingredient1->save();
        
        $this->assertFalse($meal->hasIngredientId($ingredient1->getId()));
        $meal->addIngredient($ingredient1);
        $this->assertTrue($meal->hasIngredientId($ingredient1->getId()));
        $this->assertEquals(count($meal->getIngredients()), 1);
        $meal->addIngredient($ingredient1);
        $this->assertEquals(count($meal->getIngredients()), 1);
        
        $ingredient2 = new Yourdelivery_Model_Meal_Ingredients();
        $ingredient2->setName($name2 = 'btestingredient' . rand(10, 1000));
        $ingredient2->setGroupId(1);
        $ingredient2->save();
        $meal->addIngredient($ingredient2);
        $this->assertEquals(count($meal->getIngredients()), 2);
        
        $this->assertEquals($meal->getIngredientsAsString(), implode(", ", array($name2, $name1)));
        $meal->removeIngredients();
        $this->assertEquals(count($meal->getIngredients()), 0);
    }    
    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 12.07.2012
     */
    public function testType(){
        $service = $this->getRandomService();
        $meal = $this->getRandomMealFromService($service);

        $meal->removeTypes();
        $this->assertEquals(count($meal->getTypes()), 0);
        
        $type1 = new Yourdelivery_Model_Meal_Type();
        $type1->setName($name1 = "TestType" . rand(10, 1000));
        $type1->setParentId(0);
        $type1->save();

        $this->assertFalse($meal->hasTypeId($type1->getId()));
        $meal->addType($type1);
        $this->assertTrue($meal->hasTypeId($type1->getId()));
        $this->assertEquals(count($meal->getTypes()), 1);
        $meal->addType($type1);
        $this->assertEquals(count($meal->getTypes()), 1);
        
        $type2 = new Yourdelivery_Model_Meal_Type();
        $type2->setName($name2 = "TestType" . rand(10, 1000));
        $type2->setParentId(0);
        $type2->save();
        $meal->addType($type2);
        $this->assertEquals(count($meal->getTypes()), 2);
        
        $this->assertEquals($meal->getTypesAsString(), implode(", ", array($name1, $name2)));
        $meal->removeType($type2);
        $this->assertEquals(count($meal->getTypes()), 1);
        $this->assertEquals($meal->getTypesAsString(), implode(", ", array($name1)));
    }
    
    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 18.07.2012
     */
    public function testConditionalCount(){
        $service = $this->getRandomService();
        $baseMeal = $this->getRandomMealFromService($service);        
        
        $mealsCount = Yourdelivery_Model_Meals::getConditionalCount();
        
        $meal = new Yourdelivery_Model_Meals();
        $meal->setData($baseMeal->getData());
        $meal->setName('Meal_' . Default_Helper::generateRandomString(10));
        $meal->save();
        
        $mealsCount2 = Yourdelivery_Model_Meals::getConditionalCount();
        
        $this->assertEquals($mealsCount+1, $mealsCount2);
        
        // get count of meals with ingredients
        $mealsWihtIngredientsCount = Yourdelivery_Model_Meals::getConditionalCount(array('ingredients' => true));            

        // create ingredient
        $ingredient = new Yourdelivery_Model_Meal_Ingredients();
        $ingredient->setName('Ingredient_' . Default_Helper::generateRandomString(10));
        $ingredient->setGroupId(1);
        $ingredient->save();
        
        // add ingredient to the meal
        $meal->addIngredient($ingredient);
        $mealsWihtIngredientsCount2 = Yourdelivery_Model_Meals::getConditionalCount(array('ingredients' => true));            
        
        $this->assertEquals($mealsWihtIngredientsCount+1, $mealsWihtIngredientsCount2);
        
        // remove the ingredient from meal
        $meal->removeIngredients();
        $mealsWihtIngredientsCount3 = Yourdelivery_Model_Meals::getConditionalCount(array('ingredients' => true));
        
        $this->assertEquals($mealsWihtIngredientsCount, $mealsWihtIngredientsCount3);

        // get count of meals with type        
        $mealsWihtIngredientsCount = Yourdelivery_Model_Meals::getConditionalCount(array('types' => true));
        
        // add type to the meal
        $typeId = $this->getRandomMealType();
        $type = new Yourdelivery_Model_Meal_Type($typeId);
        $meal->addType($type);

        $mealsWihtIngredientsCount2 = Yourdelivery_Model_Meals::getConditionalCount(array('types' => true));
        $this->assertEquals($mealsWihtIngredientsCount+1, $mealsWihtIngredientsCount2);
                
        // remove type from the meal
        $meal->removeTypes();
        $mealsWihtIngredientsCount3 = Yourdelivery_Model_Meals::getConditionalCount(array('types' => true));
        $this->assertEquals($mealsWihtIngredientsCount, $mealsWihtIngredientsCount3);
                
        // remove meal
        $meal->setDeleted(1);
        $meal->save();
        
        $mealsCount3 = Yourdelivery_Model_Meals::getConditionalCount();
        
        $this->assertEquals($mealsCount, $mealsCount3);
    }
}
