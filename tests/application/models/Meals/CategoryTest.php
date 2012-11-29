<?php
/**
 * @author Alex Vait <vait@lieferando.de>
 * @since 24.07.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class CategoryTest extends Yourdelivery_Test {
    /**
     * @author Alex Vait
     * @since 24.07.2012
     */
    public function testDuplicate() {
        $service = $this->getRandomService();
        
        // category data to test
        $data = array(
            'name' => "CategoryName-" . Default_Helper::generateRandomString(10),
            'description' => "CategoryDesc-" . Default_Helper::generateRandomString(10),
            'restaurantId' => $service->getId(),
            'mwst' => rand(1,10),
            'categoryPictureId' => rand(1,100),
            'parentMealCategoryId' => rand(1.100),
            'excludeFromMinCost' => 1,
            'hasPfand' => 1,
            'from' => '12:22:33',
            'to' => '22:11:55',
            'weekdays' => rand(1,127),
            'main' => 1
        );
        
        $srcCategory = new Yourdelivery_Model_Meal_Category();
        $srcCategory->setData($data);
        $srcCategory->save();
        
        // add some servicetypes, some illegal - they will not be set, this is checked in Yourdelivery_Model_Servicetype_MealCategorysNn->save()
        for ($ti = 0; $ti < 10; $ti++) {
            $servicetype_cat = new Yourdelivery_Model_Servicetype_MealCategorysNn();
            $servicetype_cat->setServicetypeId($ti);
            $servicetype_cat->setMealCategoryId($srcCategory->getId());
            $servicetype_cat->save();            
        }
        
        // create some sizes for category
        $srcSizesDataArray = array();

        // this will be the size with some assotiation to meals
        $testSize = null;
        
        for ($sizeInd = 0; $sizeInd < 3; $sizeInd++) {
            $size = new Yourdelivery_Model_Meal_Sizes();
            $sizeName = "SizeName-" . microtime() . Default_Helper::generateRandomString(5);
            $sizeData = array(
              'name' => $sizeName,
              'description' => "SizeDescr-" . Default_Helper::generateRandomString(10),
              'categoryId' => $srcCategory->getId(),                
            );
            $size->setData($sizeData);
            $size->save();
         
            // at the end it will be the last size
            $testSize = $size;
            $srcSizesDataArray[$sizeName] = $sizeData;
        }
        
        // create some meals
        $srcMealDataArray = array();

        // costs of the meals for the test size
        $srcMealSizesNnDataArray = array();
        
        // this will be the meal with some extras
        $mealForExtras = null;
        
        for ($index = 1; $index < 5; $index++) {
            $meal = new Yourdelivery_Model_Meals();
            $mealName = "MealName-" . microtime() . Default_Helper::generateRandomString(5);
            
            $mealData = array(
              'categoryId' => $srcCategory->getId(),
              'name' => $mealName,
              'description' => "MealDescr-" . Default_Helper::generateRandomString(10),
              'restaurantId' => $srcCategory->getRestaurantId(),
              'nr' => rand(1, 20),
              'status' => rand(1, 10),
              'mwst' => rand(1,100),
              'attributes' => "MealAttributes-" . Default_Helper::generateRandomString(10),
              'tabaco' => 1,
              'minAmount' => rand(1, 10),
              'excludeFromMinCost' => 1,
              'alcohol' => 1,
              'hasPicture' => 1
            );

            $meal->setData($mealData);
            $meal->save();

            $mealSizeNn = new Yourdelivery_Model_Meal_SizesNn();
            $mealSizeNnData = array(
                'mealId' => $meal->getId(),
                'sizeId' => $testSize->getId(),
                'cost' => rand(100, 1000),
                'pfand' => rand(100, 1000),
                'nr' => Default_Helper::generateRandomString(10)
            );
            $mealSizeNn->setData($mealSizeNnData);
            $mealSizeNn->save();
            
            $srcMealSizesNnDataArray[$mealName] = $mealSizeNnData;
            
            // at the end it will be the last meal
            $mealForExtras = $meal;
            $srcMealDataArray[$mealName] = $mealData;            
        }
        
        // create options group for the whole category
        $srcOptionsRow = new Yourdelivery_Model_Meal_OptionRow();
        $srcOptionsRow->setData(
                array(
                    'name' => "OptGroup-" . Default_Helper::generateRandomString(10),
                    'description' => "OptGroupDesc-" . Default_Helper::generateRandomString(10),
                    'choices' => 2,
                    'categoryId' => $srcCategory->getId(),
                    'restaurantId' => $srcCategory->getRestaurantId()
                )
        );
        $srcOptionsRow->save();

        $srcOptionsData = array();
        
        // create some options
        for ($i = 0; $i < 5; $i++) {
            $name = 'testOptName' . rand(1, 10) . microtime();

            $optData = array(
                        'name' => $name,
                        'cost' => rand(100, 1000),
                        'mwst' => rand(1, 100)
                    );
            
            $option = new Yourdelivery_Model_Meal_Option();
            $option->setData($optData);
            $option->save();

            $srcOptionsData[$name] = $optData;

            $option_nn = new Yourdelivery_Model_Meal_OptionsNn();
            $option_nn->setOptionId($option->getId());
            $option_nn->setOptionRowId($srcOptionsRow->getId());
            $option_nn->save();
        }                
        
        // create extras group 
        $srcExtrasGroup = new Yourdelivery_Model_Meal_ExtrasGroups();
        $srcExtrasGroup->setData(
                array(
                    'name' => "ExtrasGroup-" . Default_Helper::generateRandomString(10),
                    'internalName' => "ExtrasGroupInternalName-" . Default_Helper::generateRandomString(10),
                    'restaurantId' => $srcCategory->getRestaurantId()
                )
        );
        $srcExtrasGroup->save();

        // extras relations as array 'extras name'=>cost for our test size we use to set the extras
        $srcExtrasRelData = array();

        // add some extras for the group
        for ($i = 0; $i < 5; $i++) {
            $extraName = 'testExtrasName' . rand(1, 10) . microtime();

            $extrasData = array(
                        'name' => $extraName,
                        'mwst' => rand(1, 100),
                        'groupId' => $srcExtrasGroup->getId()
                    );
            
            $extra = new Yourdelivery_Model_Meal_Extra();
            $extra->setData($extrasData);
            $extra->save();

            $cost = rand(1, 100);
            $srcExtrasRelData[$extraName] = $cost;
            
            // assign extras to the whole category size, i.e. to all meals of category having association with this size
            $rel = new Yourdelivery_Model_Meal_ExtrasRelations();            
            $rel->setData(
                array(
                    'extraId' => $extra->getId(),
                    'categoryId' => $srcCategory->getId(),
                    'mealId' => 0,
                    'sizeId' => $testSize->getId(),
                    'cost' => $cost
                )
            );
            $rel->save();
        }

        
        // create extras group only for single meal association
        $srcExtrasGroupForMeal = new Yourdelivery_Model_Meal_ExtrasGroups();
        $srcExtrasGroupForMeal->setData(
                array(
                    'name' => "ExtrasGroupForMeal-" . Default_Helper::generateRandomString(10),
                    'internalName' => "ExtrasGroupforMealInternalName-" . Default_Helper::generateRandomString(10),
                    'restaurantId' => $srcCategory->getRestaurantId()
                )
        );
        $srcExtrasGroupForMeal->save();

        // extras relations as array 'extras name'=>cost for our test meal we use to set the extras
        $srcMealExtrasRelData = array();

        // add some extras for the group
        for ($i = 0; $i < 5; $i++) {
            $extraName = 'testMealExtrasName' . rand(1, 10) . microtime();

            $extrasData = array(
                        'name' => $extraName,
                        'groupId' => $srcExtrasGroupForMeal->getId()
                    );
            
            $extra = new Yourdelivery_Model_Meal_Extra();
            $extra->setData($extrasData);
            $extra->save();

            $cost = rand(1, 100);
            $srcMealExtrasRelData[$extraName] = $cost;
            
            // assign extras to the whole category size, i.e. to all meals of category having association with this size
            $rel = new Yourdelivery_Model_Meal_ExtrasRelations();            
            $rel->setData(
                array(
                    'extraId' => $extra->getId(),
                    'categoryId' => 0,
                    'mealId' => $mealForExtras->getId(),
                    'sizeId' => $testSize->getId(),
                    'cost' => $cost
                )
            );
            $rel->save();
        }
        
        
        
        /*
         * create clone category
         */
        $cloneCategory = $srcCategory->duplicate();
        
        
        /*
         * ***************************************************************************
         * now test the stuff !!!
         * ***************************************************************************
         */
        
        // all category data must be equal
        $cloneData = $cloneCategory->getData();
        foreach ($data as $k => $v) {            
            $this->assertEquals($cloneData[$k], $v);
        }
        
        // test cloned types - the count must be the same 
        $srcTypes = $srcCategory->getServiceTypes();
        $cloneTypes = $cloneCategory->getServiceTypes();
        $this->assertEquals(count($srcTypes), count($cloneTypes));
        
        // there must be no abnother types but only the one form the source category
        foreach ($srcTypes as $st) {
            $this->assertTrue($cloneCategory->hasServiceType($st['id'])>0);
        }
        
        // the cloned test size, must be found at the end
       $clonedTestSize = null;
        
        // test data for all sizes of this category
        foreach ($cloneCategory->getSizes() as $size) {
            $srcSizeData = $srcSizesDataArray[$size['name']];
            
            // only change the category, the other data must be same
            $srcSizeData['categoryId'] = $cloneCategory->getId();
            
            foreach ($srcSizeData as $k => $v) {            
                $this->assertEquals($size[$k], $v);
            }
            
            if(strcmp($size['name'], $testSize->getName()) == 0) {
                // we found the size that correcponds to the source test size
                $clonedTestSize = new Yourdelivery_Model_Meal_Sizes($size['id']);
            }
        }
        
        // at the end a corresponding test size must be found 
        $this->assertNotNull($clonedTestSize);
        
        $clonedMeals = $cloneCategory->getMealsAsObjects();
        $this->assertTrue(count($clonedMeals)>0);
        
        
        // the cloned test meal, must be found at the end
        $clonedTestMeal = null;
        
        // to be sure that we found some extras associations at all
        $extrasForSizeFound = false;
        $extrasForMealFound = false;
        
        // test data for all contained meals
        foreach ($clonedMeals as $meal) {
            $srcMealData = $srcMealDataArray[$meal->getName()];
            $clonedMealData = $meal->getData();
            
            if(strcmp($meal->getName(), $mealForExtras->getName()) == 0) {
                // we found the test meal that correcponds to the source test meal
                $clonedTestMeal = $meal;
            }
            
            // only change the category, the other data must be same
            $srcMealData['categoryId'] = $cloneCategory->getId();
            
            foreach ($srcMealData as $k => $v) {            
                $this->assertEquals($clonedMealData[$k], $v);
            }
            
            // set the test size for the meal and test the associations with the test size
            $meal->setCurrentSize($clonedTestSize->getId());
            
            $srcMealSizeNnData = $srcMealSizesNnDataArray[$meal->getName()];
            $this->assertEquals($srcMealSizeNnData['cost'], $meal->getCost());
            $this->assertEquals($srcMealSizeNnData['pfand'], $meal->getPfand());
            $this->assertEquals($srcMealSizeNnData['nr'], $meal->getNr());
            
            // test the costs of extras for this meal and the test size
            $extras = $meal->getExtrasForCopying();
            foreach ($extras as $extraGroup) {
                foreach ($extraGroup as $extra) {
                    // test extras only for the test meal
                    if ($extra['mealId']>0) {
                        $extrasForMealFound = true;
                        $srcRelCost = $srcMealExtrasRelData[$extra['name']];
                        $this->assertEquals($srcRelCost, $extra['cost']);
                        // this must eb valid only for the test meal
                        $this->assertEquals($extra['mealId'], $clonedTestMeal->getId());
                    }
                    // test extras for the whole size
                    else {
                        $extrasForSizeFound = true;
                        $srcRelCost = $srcExtrasRelData[$extra['name']];
                        $this->assertEquals($srcRelCost, $extra['cost']);                        
                    }                    
                }
            }
        }
                
        // at the end a corresponding test meal must be found 
        $this->assertNotNull($clonedTestMeal);

        // we found extras, so the loop was not skipped
        $this->assertTrue($extrasForSizeFound);
        $this->assertTrue($extrasForMealFound);
        
        // test option groups, must be only one
        $optionsGroups = $cloneCategory->getOptionRows();
        $this->assertEquals(count($optionsGroups), 1);
        $optionsGroup = $optionsGroups[0];

        $this->assertEquals($srcOptionsRow->getName(), $optionsGroup->getName());
        $this->assertEquals($srcOptionsRow->getDescription(), $optionsGroup->getDescription());
        $this->assertEquals($srcOptionsRow->getChoices(), $optionsGroup->getChoices());
        $this->assertEquals($srcOptionsRow->getRestaurantId(), $optionsGroup->getRestaurantId());
        $this->assertEquals($cloneCategory->getId(), $optionsGroup->getCategoryId());
        
        // test each option
        foreach ($optionsGroup->getOptions() as $opt) {
            $srcOptionData = $srcOptionsData[$opt->getName()];
            $clonedOptionData = $opt->getData();
            
            foreach ($srcOptionData as $k => $v) {
                $this->assertEquals($clonedOptionData[$k], $v);
            }
        }
        
        // test extras
        
        
        // cleanup
        $service->deleteMealCategory($srcCategory->getId());
        $service->deleteMealCategory($cloneCategory->getId());
        
    }    
}
