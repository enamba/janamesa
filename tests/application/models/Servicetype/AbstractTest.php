<?php

/**
 * Description of UserTest
 *
 * @author alex
 * @since 18.08.2010
 */

/**
 * @runTestsInSeparateProcesses
 */
class Servicetype_AbstractTest extends Yourdelivery_Test {

    /**
     * test object creation
     * @author alex
     * @since 18.08.2010
     */
    public function testCreate() {

        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant();
        $values = array(
            'name' => 'Test Restaurant',
            'street' => 'Teststrasse',
            'hausnr' => '123',
            'plz' => '10115',
            'cityId' => '644',
            'isOnline' => 0,
            'status' => 8, // Pipeline
            'description' => 'Test Beschreibung',
            'tel' => '030-1234567',
            'fax' => '030-9876543',
            'customerNr' => microtime(),
            'created' => date('Y-m-d H:i:s', time())
        );
        $restaurant->setData($values);
        $restaurant->save();

        // create test location if not exists
        if (is_null(Yourdelivery_Model_DbTable_City::findByPlz('10115'))) {
            $testCity = new Yourdelivery_Model_City();
            $ortValues = array(
                'plz' => '10115',
                'city' => 'Berlin',
                'stateId' => '1',
                'state' => 'Berlin, Stadt'
            );
            $testCity->setData($ortValues);
            $testCity->save();
        }

        $this->assertNotNull($restaurant);
        $this->assertIsPersistent($restaurant);
    }

    /**
     * test if restaurant is new, i.e. newer than 10 days
     * @author alex
     * @since 18.08.2010
     */
    public function testIsNew() {

        // get Random Restaurant
        $restaurant = $this->getRandomService();
        $restaurant->setCreated(date('Y-m-d H:i:s', strtotime('-2 month')));
        $restaurant->save();
        $this->assertFalse($restaurant->isNew(), $restaurant->getId());

        $restaurant->setCreated(date('Y-m-d H:i:s'));
        $restaurant->save();

        $this->assertTrue($restaurant->isNew(), $restaurant->getId());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.01.2012
     */
    public function testIsOnline() {

        //get restaurant
        $service = $this->getRandomService();
        $customer = $this->getRandomCustomer(true);
        $company = $customer->getCompany();
        $customer2 = $this->getRandomCustomer();
        $this->assertNotEquals($customer->getId(), $customer2->getId());

        //reset all company restrictions if any
        foreach ($service->getCompanyRestrictions() as $rest) {
            $service->removeCompanyRestriction($rest->companyId);
        }

        // mark online
        $service->setIsOnline(false);
        $service->save();
        $this->assertFalse($service->isOnline());
        $service->setIsOnline(true);
        $this->assertTrue($service->isOnline());

        $service->setCompanyRestriction($company->getId(), true);
        $this->assertFalse($service->isOnline($customer, 'priv'));
        $this->assertTrue($service->isOnline($customer, 'comp'));
        $this->assertFalse($service->isOnline($customer2, 'priv'));
        $this->assertFalse($service->isOnline($customer2, 'comp'));
    }

    /**
     * test if restaurant is of restaurant service type
     * @author alex
     * @since 18.08.2010
     */
    public function testIsRestaurant() {

        //create restaurant
        $restaurant = $this->getRandomService();
        $serviceTypeTable = new Yourdelivery_Model_DbTable_Restaurant_Servicetype();
        $serviceTypeTable->delete('restaurantId = ' . $restaurant->getId());

        $this->assertFalse($restaurant->isRestaurant());

        // create new meal category of type 'restaurant'
        $category = new Yourdelivery_Model_Meal_Category();
        $category->setData(
                array(
                    'name' => 'Test Restaurant Meal Category',
                    'restaurantId' => $restaurant->getId()
        ));
        $category->save();

        $servicetype_cat = new Yourdelivery_Model_Servicetype_MealCategorysNn();
        $servicetype_cat->setServicetypeId(1); // Restaurant
        $servicetype_cat->setMealCategoryId($category->getid());
        $servicetype_cat->save();

        /**
         * this should succeed
         */
        $this->assertTrue($restaurant->isRestaurant());
    }

    /**
     * test if restaurant is caterer
     * @author alex
     * @since 18.08.2010
     */
    public function testIsCatering() {

        //create restaurant
        $restaurant = $this->getRandomService();
        $serviceTypeTable = new Yourdelivery_Model_DbTable_Restaurant_Servicetype();
        $serviceTypeTable->delete('restaurantId = ' . $restaurant->getId());

        /**
         * this should fail, no catering categories yet
         */
        $this->assertFalse($restaurant->isCatering());

        // create new meal category of type 'caterer'
        $category = new Yourdelivery_Model_Meal_Category();
        $category->setData(
                array(
                    'name' => 'Test Caterer Meal Category',
                    'restaurantId' => $restaurant->getId()
        ));
        $category->save();

        $servicetype_cat = new Yourdelivery_Model_Servicetype_MealCategorysNn();
        $servicetype_cat->setServicetypeId(2); // Caterer
        $servicetype_cat->setMealCategoryId($category->getid());
        $servicetype_cat->save();

        /**
         * this should succeed
         */
        $this->assertTrue($restaurant->isCatering());
    }

    /**
     * test if restaurant is 'Großhändler'
     * @author alex
     * @since 18.08.2010
     */
    public function testIsGreat() {

        //create restaurant
        $restaurant = $this->getRandomService();
        $serviceTypeTable = new Yourdelivery_Model_DbTable_Restaurant_Servicetype();
        $serviceTypeTable->delete('restaurantId = ' . $restaurant->getId());

        /**
         * this should fail, no 'Großhändler' categories yet
         */
        $this->assertFalse($restaurant->isGreat());

        // create new meal category of type 'Großhändler'
        $category = new Yourdelivery_Model_Meal_Category();
        $category->setData(
                array(
                    'name' => 'Test Great Meal Category',
                    'restaurantId' => $restaurant->getId()
        ));
        $category->save();

        $servicetype_cat = new Yourdelivery_Model_Servicetype_MealCategorysNn();
        $servicetype_cat->setServicetypeId(3); // Großhändler
        $servicetype_cat->setMealCategoryId($category->getid());
        $servicetype_cat->save();

        /**
         * this should succeed
         */
        $this->assertTrue($restaurant->isGreat());
    }

    /**
     * test location creation
     * @author alex
     * @since 18.08.2010
     */
    public function testCreateLocation() {

        //create restaurant
        $restaurant = $this->getRandomService();

        $cities = Yourdelivery_Model_City::getByPlz(10115);
        $testCityId = $cities[0]['id'];

        // delete deliver location if some exists
        $locationId = $restaurant->getTable()->getDeliverRangeId($testCityId);
        $restaurant->deleteRange($locationId);

        $result = $restaurant->createLocation($testCityId, '20,00', '3,00', 60, 0);

        // this should succeed
        $this->assertGreaterThan(0, $result);
    }

    /**
     * test if minimal cost was set correctly
     * @author alex
     * @since 18.08.2010
     */
    public function testGetMinCostDeliverTimeDeliverCostDeliverTime() {

        $cityId = $this->getRandomCityId();
        $restaurant = $this->getRandomService(array('cityId' => $cityId));

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf('SELECT * FROM restaurant_plz WHERE restaurantId = %d AND cityId = %d', $restaurant->getId(), $cityId);

        $result = $db->fetchRow($sql);

        $this->assertEquals($result['delcost'], (integer) $restaurant->getDeliverCost($cityId));
        $this->assertEquals($result['mincost'], $restaurant->getMincost($cityId));
        $this->assertEquals($result['deliverTime'], $restaurant->getDeliverTime($cityId));
    }

    /**
     * test if deliver cost is correct after editing through Yourdelivery_Model_Servicetype_Plz
     * @author alex
     * @since 18.08.2010
     */
    public function testSetDeliverCost() {

        // change deliver cost for this plz
        $restaurant = $this->getRandomService();
        $cityId = $this->getRandomCityId();

        $db = $this->_getDbAdapter();
        $db->query(sprintf('delete from restaurant_plz WHERE cityId = %d AND restaurantId = %d', $cityId, $restaurant->getId()));

        $deliverLocation = new Yourdelivery_Model_Servicetype_Plz();
        $deliverLocation->setData(
                array(
                    'restaurantId' => $restaurant->getId(),
                    'plz' => $restaurant->getCity()->getPlz(),
                    'cityId' => $cityId,
                    'status' => 1,
                    'delcost' => 666
                )
        )->save();

        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurant->getId());

        // this should not be equal
        $this->assertEquals(666, $restaurant->getDeliverCost($cityId), sprintf('service #%d - city #%d', $restaurant->getId(), $cityId));
    }

    /**
     * test getting opening model
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 15.04.2012
     */
    public function testOpening() {
        // get random service
        $restaurant = $this->getRandomService();
        $this->assertInstanceof(Yourdelivery_Model_Servicetype_Openings, $restaurant->getOpening());
    }

    /**
     * test deleting
     * @author alex
     * @since 18.08.2010
     */
    public function testDelete() {


        //create restaurant
        $restaurant = $this->getRandomService();

        // this should succeed
        $this->assertEquals($restaurant->getDeleted(), 0);

        $restaurant->delete();

        // this should succeed
        $this->assertEquals($restaurant->getDeleted(), 1);
    }

    public function testRanges() {

        $service = $this->getRandomService();
        $serviceId = $service->getId();

        $parentCityId = $this->createCity();
        $cityId = $this->createCity($parentCityId);

        $this->assertNotEquals($cityId, $parentCityId);

        $locations = $service->getRanges();
        $this->assertGreaterThan(0, count($locations));

        //since we added a parent to the child we should get two more ranges
        $this->assertTrue($service->createLocation($parentCityId, 0, 0, 600, 0) !== false);

        $service->clearCachedVars();
        $this->assertEquals(count($locations) + 2, count($service->getRanges()));
    }

    public function testChildRangesDeliverInfo() {
        $service = $this->getRandomService();
        $ranges = $service->getRanges();

        //create a range and add
        $parentCityId = $this->createCity();
        $this->assertGreaterThan(0, $service->createLocation($parentCityId, 0, 6, 0, 0));
        $this->assertEquals($service->getDeliverCost($parentCityId), 600);

        //now once a child has been added, this should
        //pop up on this servic too, with the same deliver cost as parent
        $childCityId = $this->createCity($parentCityId);
        $this->assertNotEquals($cityId, $parentCityId);
        $this->assertEquals($service->getDeliverCost($childCityId), 600);

        //but once we add the child with another service cost directly
        //the deliver cost of the child should be returned
        $this->assertGreaterThan(0, $service->createLocation($childCityId, 0, 10, 0, 0));
        $service = new Yourdelivery_Model_Servicetype_Restaurant($service->getId());
        $this->assertEquals($service->getDeliverCost($childCityId), 1000);
    }

    /**
     * test if the returned ranges straing representations are unique for this restaurant
     * @author alex
     * @since 27.06.2011
     */
    public function testRangesUnique() {

        $this->markTestSkipped('Really must be unique? See YD-987');

        $service = $this->getRandomService();

        $test = array();
        foreach ($service->getRanges() as $c) {
            if (isset($test[$c['cityname']]))
                $test[$c['cityname']]++;
            else
                $test[$c['cityname']] = 1;
        }

        $this->assertTrue(max(array_values($test)) == 1);
    }

    /**
     * get correct deliver cost depending on total
     * @author mlaug
     * @since 08.09.2011
     */
    public function testNoDeliverCostAbove() {
        $service = $this->getRandomService();
        $cityId = $this->getRandomCityId();
        $service->deleteRangeByCityId($cityId);
        $service->createLocation($cityId, 10, 10, 3600, 10);
        $service->setCurrentCityId($cityId);
        $this->assertEquals(1000, $service->getDeliverCost($cityId, 500), Default_Helpers_Log::getLastLog());
        $this->assertEquals(0, $service->getDeliverCost($cityId, 1000), Default_Helpers_Log::getLastLog());
    }

    /**
     * get the list of bestseller meals
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 04.10.2011
     */
    public function testBestseller() {
        $service = $this->getRandomService();
        $this->assertTrue(is_array($service->getBestSeller(10)));
        $this->assertTrue(count($service->getBestSeller(10)) <= 10);
    }

    /**
     * check basis fee with calculation based on bill interval
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.01.2012
     */
    public function testBaseFee() {
        $service = $this->getRandomService();
        $service->setBasefee(300);
        $service->setBillInterval(0);
        $this->assertEquals(300, $service->getBasefee());
        $service->setBillInterval(1);
        $this->assertEquals(150, $service->getBasefee());
        $service->setBillInterval(2);
        $this->assertEquals(10, $service->getBasefee());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.01.2012
     */
    public function testGetCategories() {
        $service = $this->getRandomService();
        $this->assertGreaterThan(0, $service->getCategories());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.01.2012
     */
    public function testGetDirectLink() {
        $service = $this->getRandomService();
        $restUrl = $service->getRestUrl();
        $caterUrl = $service->getCaterUrl();
        $greatUrl = $service->getGreatUrl();
        $this->assertEquals($restUrl, $service->getDirectLink());
        $this->assertEquals($restUrl, $service->getDirectLink('rest'));
        $this->assertEquals($caterUrl, $service->getDirectLink('cater'));
        $this->assertEquals($greatUrl, $service->getDirectLink('great'));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.01.2012
     * @return boolean
     */
    private function hasRange($service, $cityId) {
        $cityId = (integer) $cityId;
        $ranges = $service->getRanges();
        foreach ($ranges as $range) {
            if ((integeR) $range['cityId'] == $cityId) {
                // hooray, we can get fat in this street as well
                return true;
            }
        }

        //copied from the the model, to get all children and parents as well
        $db = Zend_Registry::get('dbAdapter');
        $parentCityId = (integer) $db->fetchOne('SELECT `parentCityId` FROM `city` WHERE `id` = ?', $cityId);
        $childrens = (array) $db->fetchCol('SELECT `id` FROM `city` WHERE parentCityId = ?', $cityId);

        // get all ids in a unique array
        $in = array($cityId);
        if ($parentCityId) {
            $in[] = $parentCityId;
        }
        $in = array_merge($in, $childrens);
        foreach ($in as $i) {
            if ($i == $cityId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.01.2012
     */
    public function testGetByCityId() {
        $cityId = $this->getRandomCityId();
        $services = Yourdelivery_Model_Servicetype_Abstract::getByCityId($cityId);
        $this->assertGreaterThan(0, count($services));
        foreach ($services as $service) {
            $this->assertTrue($this->hasRange($service, $cityId));
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.01.2012
     */
    public function testGetSmallMenu() {
        $service = $this->getRandomService();
        $menu = $service->getSmallMenu();
        $this->assertTrue(is_array($menu));
        $meal = array_pop($menu);
        $name = $meal['name'];
        $search_menu = $service->getSmallMenu($name);
        $this->assertTrue(is_array($search_menu));
        $this->assertGreaterThan(0, count($search_menu));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 13.01.2012
     */
    public function testGetRatingAdvise() {
        $rest = $this->getRandomService();

        $adviseBefore = $rest->getRating()->getAverageAdvise();
        $this->assertTrue(is_numeric($adviseBefore));

        $order1 = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $rest)));
        $order2 = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $rest)));

        $this->assertFalse($order1->isRated(), 'OrderId #' . $order1->getId());
        $this->assertFalse($order2->isRated(), 'OrderId #' . $order2->getId());
        // rate orders
        $this->assertTrue($order1->rate(null, 4, 5, 'testcase rate order with positive advise', 'test title', 1));
        $this->assertTrue($order2->rate(null, 1, 2, 'testcase rate order with positive advise', 'test title', 1));

        $this->assertTrue($order1->isRated());
        $this->assertTrue($order2->isRated());

        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow("SELECT count(id) count FROM restaurant_ratings WHERE orderId IN (" . $order1->getId() . "," . $order2->getId() . ")");
        $this->assertEquals(2, $row['count'], $rest->getId() . " " . $order1->getId() . " " . $order2->getId());

        $adviseAfterRatingOffline = $rest->getRating()->getAverageAdvise();
        $this->assertEquals($adviseBefore, $adviseAfterRatingOffline);

        // set ratings online
        $db->query("UPDATE restaurant_ratings SET status = 1 WHERE orderId IN (" . $order1->getId() . "," . $order2->getId() . ")");
        $rest->getRating()->clearCache();

        $adviseAfterRatingOnline = $rest->getRating()->getAverageAdvise();
        if ($adviseAfterRatingOnline !== 0 && $adviseBefore !== 100) {
            $this->assertNotEquals($adviseBefore, $adviseAfterRatingOnline, sprintf('restaurant #%d', $rest->getId()));
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.01.2012
     */
    public function testGetDocumentStorage() {

        $service = $this->getRandomService();
        $this->assertNotNull($service->getDocumentsStorage());
        $this->assertTrue(file_exists($service->getDocumentsStorage()->getCurrentFolder()));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.01.2012
     */
    public function testGetNextBill() {
        $service = $this->getRandomService();
        $nextBill = null;
        $nextBill = $service->getNextBill();
        $this->assertNotNull($nextBill);
        $this->assertInstanceof(Yourdelivery_Model_Billing_Restaurant, $nextBill);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.01.2012
     */
    public function testGetBillingCustomizedData() {
        $service = $this->getRandomService();
        $contact = $service->getBillingContact();
        $data = $service->getBillingCustomizedData();
        $this->assertTrue(is_array($data));

        $dataCustom = $service->getBillingCustomized()->getData();
        if (isset($dataCustom['name']) && strlen($dataCustom['name']) > 0) {
            $this->assertEquals($dataCustom['name'], $data['heading'], $service->getId());
        } else {
            $this->assertEquals($service->getName(), $data['heading']);
        }

        if ($contact instanceof Yourdelivery_Model_Contact && strlen($contact->getStreet()) > 0) {
            $this->assertEquals($contact->getStreet(), $data['street'], $service->getId());
        } else {
            $this->assertEquals($service->getStreet(), $data['street'], $service->getId());
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.01.2012
     */
    public function testIsInCity() {
        $db = Zend_Registry::get('dbAdapter');

        $cityId = $this->getRandomCityId();
        $select = $db->select()->from('restaurants')->where('cityId = ?', $cityId);
        $results = $db->fetchAll($select);

        while (count($results) == 0) {
            $cityId = $this->getRandomCityId();
            $select = $db->select()->from('restaurants')->where('cityId = ?', $cityId);
            $results = $db->fetchAll($select);
        }

        $this->assertFalse(empty($results[0]));

        $service = new Yourdelivery_Model_Servicetype_Restaurant($results[0]['id']);

        $city = new Yourdelivery_Model_City($cityId);

        $this->assertTrue($service->IsInCity($city->getCity()));
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.01.2012
     */
    public function testGetTopRatings() {
        $service = $this->getRandomService();
        $topRatings = $service->getTopRatings();
        foreach ($topRatings as $topRating) {
            $this->assertEquals(1, $topRating['status']);
            $this->assertEquals(1, $topRating['topRating']);
        }

        $topRatingLimit = $service->getTopRatings(3);
        $this->assertTrue(count($topRatingLimit) <= 3);
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 31.01.2012
     */
    public function testDeleteMealExtra() {
        $service = $this->getRandomService();

        $extrasGroup = $service->addMealExtraGroup('testDeleteMealExtra');

        $extra = new Yourdelivery_Model_Meal_Extra();
        $extra->setData(array('name' => 'testExtra', 'nr' => '666', 'status' => 1, 'groupId' => $extrasGroup->getId(), 'mwst' => '33'));
        $extra->setRestaurantId($service->getId());
        $extra->save();

        $this->assertEquals($extrasGroup->getExtras()->count(), 1);
        $service->deleteMealExtra($extra->getId());
        $this->assertEquals($extrasGroup->getExtras()->count(), 0);
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 31.01.2012
     */
    public function testAddMealExtrasGroup() {
        $service = $this->getRandomService();

        $extrasGroups = $service->getMealExtrasGroups();
        $egCount = $extrasGroups->count();

        $extrasGroup = $service->addMealExtraGroup('testAddMealExtraGroup');

        $this->assertEquals($service->getMealExtrasGroups()->count(), $egCount + 1);

        $service->deleteMealExtraGroup($extrasGroup->getId());

        $this->assertEquals($service->getMealExtrasGroups()->count(), $egCount);
    }

    /**
     *
     * Create and delete meal category
     * @author Alex Vait <vait@lieferando.de>
     * @since 31.01.2012
     */
    public function testAddAndDeleteMealCategory() {
        $maxDeadlock = 30;

        // find a customer with at least 3 meal categories
        do {
            $service = $this->getRandomService();
            $cats = $service->getMealCategoriesSorted();
            $deadlockPreventer++;
        } while ((count($cats) < 2) && ($deadlockPreventer < $maxDeadlock));

        if (is_null($cats) || !is_array($cats)) {
            $this->markTestSkipped('The restaurant has no meal categories');
        }

        $catsCount = count($cats);

        $testName = 'testAddMealCategory_' . time();
        $testDescription = 'testDesc_' . time();

        // part 2 - create the category
        $category = new Yourdelivery_Model_Meal_Category();
        $category->setData(array('name' => $testName, 'description' => $testDescription, 'mwst' => '666', 'restaurantId' => $service->getId()));
        $category->save();

        $this->assertEquals(count($service->getMealCategoriesSorted()), $catsCount + 1);

        $categories = $service->getMealCategoriesSorted();

        // new created category shall be in the list of catgegories
        $found = false;
        foreach ($categories as $c) {
            if (strcmp($c, $testName) == 0) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);

        // part 2 - delete the category
        $service->deleteMealCategory($category->getId());

        $categories_past_delete = $service->getMealCategoriesSorted();

        // new created category is no more in the list of categories
        $found = false;
        foreach ($categories_past_delete as $c) {
            if (strcmp($c, $testName) == 0) {
                $found = true;
            }
        }

        $this->assertFalse($found);
        $this->assertEquals(count($service->getMealCategoriesSorted()), $catsCount);
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 31.01.2012
     */
    public function testMealOptionsWhole() {
        $service = $this->getRandomService();

        $optRowName = 'testOptionRowName_' . time();
        $optRowDesc = 'testOptionRowDesc_' . time();

        $optName = 'testOptionName_' . time();
        $optCost = time() % 500;

        $optRowCount = $service->getMealOptionsRows()->count();

        // create meal category first
        $category = new Yourdelivery_Model_Meal_Category();
        $category->setData(array('name' => 'testMealCategoryName' . time(), 'description' => 'testMealCategoryDescription' . time(), 'mwst' => '666', 'restaurantId' => $service->getId()));
        $category->save();

        // create options group
        $optionsRow = new Yourdelivery_Model_Meal_OptionRow();
        $optionsRow->setData(
                array(
                    'name' => $optRowName,
                    'description' => $optRowDesc,
                    'choices' => 2,
                    'categoryId' => $category->getId(),
                    'restaurantId' => $service->getId()
                )
        );
        $optionsRow->save();

        // create option
        $option = new Yourdelivery_Model_Meal_Option();
        $option->setData(
                array(
                    'name' => $optName,
                    'optRow' => $optionsRow->getId(),
                    'restaurantId' => $service->getId(),
                    'cost' => $optCost
                )
        );
        $option->save();

        $option_nn = new Yourdelivery_Model_Meal_OptionsNn();
        $option_nn->setOptionId($option->getId());
        $option_nn->setOptionRowId($optionsRow->getId());
        $option_nn->save();

        $this->assertEquals($service->getMealOptionsRows()->count(), $optRowCount + 1);

        $foundRow = false;
        $foundOpt = false;
        $optionStillHere = false;

        // find the created option row and test it's description, after that find the newly created option and test it's cost
        foreach ($service->getMealOptionsRows() as $optRow) {
            if (strcmp($optRow->getName(), $optRowName) == 0) {
                $foundRow = true;
                $this->assertEquals($optRow->getDescription(), $optRowDesc);

                foreach ($optRow->getOptions() as $opt) {
                    if (strcmp($opt->getName(), $optName) == 0) {
                        $foundOpt = true;
                        $this->assertEquals($opt->getCost(), $optCost);
                        break;
                    }
                }

                // delete meal option
                $service->deleteMealOption($option->getId());

                foreach ($optRow->getOptions() as $opt) {
                    if (strcmp($opt->getName(), $optName) == 0) {
                        $optionStillHere = true;
                        break;
                    }
                }
                break;
            }
        }

        $this->assertTrue($foundRow);
        $this->assertTrue($foundOpt);
        $this->assertFalse($optionStillHere);

        Yourdelivery_Model_DbTable_Meal_OptionsRows::removeById($optionsRow->getId());

        $rowStillHere = false;

        //test if the group was deleted
        foreach ($service->getMealOptionsRows() as $optRow) {
            if (strcmp($optRow->getName(), $optRowName) == 0) {
                $foundRow = true;
            }
        }

        $this->assertFalse($rowStillHere);
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.02.2012
     */
    public function testGetSalesperson() {
        $service = $this->getRandomService();

        // to be sure we have no salesperson for this restaurant
        Yourdelivery_Model_DbTable_Salesperson_Restaurant::removeByRestaurant($service->getId());

        $name = 'SalespersonName_' . time();
        $prename = 'SalespersonPrename_' . time();
        $email = 'SalespersonEmail_' . microtime();

        $saler = new Yourdelivery_Model_Salesperson();
        $saler->setData(array('name' => $name, 'prename' => $prename, 'email' => $email));
        $saler->save();

        $salespersonRelation = new Yourdelivery_Model_Salesperson_Restaurant();
        $salespersonRelation->add($saler->getId(), $service->getId(), $signed);

        $salespersonTest = $service->getSalesperson();

        $this->assertEquals($salespersonTest->getName(), $name);
        $this->assertEquals($salespersonTest->getPrename(), $prename);
        $this->assertEquals($salespersonTest->getEmail(), $email);
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     */
    public function testAddTag() {
        $service = $this->getRandomService();

        $service->removeAllTags();
        $this->assertEquals(0, count($service->getTable()->getTags()), sprintf('failed to get 0 tags for service #%s after deleting all', $service->getId()));
        
        $uniqueTagName = 'TestTag'.time().rand(1,9);
        
        $newTag = new Yourdelivery_Model_DbTable_Tag();
        $newTagId = $newTag->insert(array('name' => $uniqueTagName));
        $this->assertGreaterThan(0, $newTagId, sprintf('failed to get insert ID greater than 0'));
        $service->addTag($newTagId);
        
        $serviceCheck = new Yourdelivery_Model_Servicetype_Restaurant($service->getId());
        $tags = $serviceCheck->getTable()->getTags();
        
        $this->assertEquals(1, count($tags), sprintf('failed to get correct count of tags for service #%s - %s', $serviceCheck->getId(), print_r($tags, true)));

        $this->assertEquals($uniqueTagName, $tags[0]['tag']);
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     */
    public function testCreateCityRange() {
        $service = $this->getRandomService();
        $contact = $this->getRandomContact();

        $service->setContactId($contact->getId());
        $service->save();

        $testContact = $service->getContact();
        $this->assertEquals($contact, $testContact);
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     */
    public function testBillingContact() {
        $service = $this->getRandomService();
        $contact = $this->getRandomContact();

        $service->setBillingContactId($contact->getId());
        $service->save();

        $testContact = $service->getBillingContact();
        $this->assertEquals($contact, $testContact);
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     */
    public function testDownMealCategory() {
        $service = null;
        $deadlockPreventer = 0;
        $maxDeadlock = 30;

        // find a customer with at least 3 meal categories
        do {
            $service = $this->getRandomService();
            $deadlockPreventer++;
        } while ((count($service->getMealCategories()) < 2) && ($deadlockPreventer < $maxDeadlock));

        if ($deadlockPreventer == $maxDeadlock) {
            $this->assertEquals('Deadlock! No service with enough meal categories found', '');
        }

        // select random category
        $catArr = array();
        foreach ($service->getMealCategories() as $c) {
            $catArr[] = $c;
        }

        $category = $catArr[rand(0, count($catArr) - 1)];

        //move category down
        $service->downMealCategory($category->getId());

        $updatedCategory = new Yourdelivery_Model_Meal_Category($category->getId());
        // if it was the lowest category, no changes must be made
        if ($category->getRank() == Yourdelivery_Model_DbTable_Meal_Categories::getMaxRank($service->getId())) {
            $this->assertEquals($category->getRank(), $updatedCategory->getRank());
        } else {
            $this->assertEquals($category->getRank() + 1, $updatedCategory->getRank());
        }
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     */
    public function testUpMealCategory() {
        $service = null;
        $deadlockPreventer = 0;
        $maxDeadlock = 30;

        // find a customer with at least 3 meal categories
        do {
            $service = $this->getRandomService();
            $deadlockPreventer++;
        } while ((count($service->getMealCategories()) < 2) && ($deadlockPreventer < $maxDeadlock));

        if ($deadlockPreventer == $maxDeadlock) {
            $this->assertEquals('Deadlock! No service with enough meal categories found', '');
        }

        // select random category
        $catArr = array();
        foreach ($service->getMealCategories() as $c) {
            $catArr[] = $c;
        }

        $category = $catArr[rand(0, count($catArr) - 1)];

        //move category up
        $service->upMealCategory($category->getId());

        $updatedCategory = new Yourdelivery_Model_Meal_Category($category->getId());

        // if it was the highest category, no changes must be made
        if ($category->getRank() == Yourdelivery_Model_DbTable_Meal_Categories::getMinRank($service->getId())) {
            $this->assertEquals($category->getRank(), $updatedCategory->getRank());
        } else {
            $this->assertEquals($category->getRank() - 1, $updatedCategory->getRank());
        }
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     */
    public function testMoveMealSizeRight() {
        $service = null;
        $deadlockPreventer = 0;
        $maxDeadlock = 30;

        // find a customer with at least 3 meal categories
        do {
            $service = $this->getRandomService();
            $deadlockPreventer++;
        } while ((count($service->getMeals()) < 2) && ($deadlockPreventer < $maxDeadlock));

        if ($deadlockPreventer == $maxDeadlock) {
            $this->assertEquals('Deadlock! No service with enough meal categories found', '');
        }

        // select random category
        $catArr = array();
        foreach ($service->getMealCategories() as $c) {
            $catArr[] = $c;
        }

        $category = $catArr[rand(0, count($catArr) - 1)];

        // select random meal size
        $sizesArr = array();
        foreach ($category->getSizes() as $s) {
            $sizesArr[] = $s;
        }

        $size = $sizesArr[rand(0, count($sizesArr) - 1)];
        $s = new Yourdelivery_Model_Meal_Sizes($size['id']);

        //move size right
        $service->moveMealSizeRight($s->getId());

        $updatedSize = new Yourdelivery_Model_Meal_Sizes($s->getId());

        // if it was the rightest size, no changes must be made
        if ($s->getRank() == Yourdelivery_Model_DbTable_Meal_Sizes::getMaxRank($category->getId())) {
            $this->assertEquals($s->getRank(), $updatedSize->getRank());
        } else {
            $this->assertEquals($s->getRank() + 1, $updatedSize->getRank());
        }
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     */
    public function testMoveMealSizeLeft() {
        $service = null;
        $deadlockPreventer = 0;
        $maxDeadlock = 30;

        // find a customer with at least 3 meal categories
        do {
            $service = $this->getRandomService();
            $deadlockPreventer++;
        } while ((count($service->getMeals()) < 2) && ($deadlockPreventer < $maxDeadlock));

        if ($deadlockPreventer == $maxDeadlock) {
            $this->assertEquals('Deadlock! No service with enough meal categories found', '');
        }

        // select random category
        $catArr = array();
        foreach ($service->getMealCategories() as $c) {
            $catArr[] = $c;
        }

        $category = $catArr[rand(0, count($catArr) - 1)];

        // select random meal size
        $sizesArr = array();
        foreach ($category->getSizes() as $s) {
            $sizesArr[] = $s;
        }

        $size = $sizesArr[rand(0, count($sizesArr) - 1)];
        $s = new Yourdelivery_Model_Meal_Sizes($size['id']);

        //move size right
        $service->moveMealSizeLeft($s->getId());

        $updatedSize = new Yourdelivery_Model_Meal_Sizes($s->getId());

        // if it was the rightest size, no changes must be made
        if ($s->getRank() == Yourdelivery_Model_DbTable_Meal_Sizes::getMinRank($category->getId())) {
            $this->assertEquals($s->getRank(), $updatedSize->getRank());
        } else {
            $this->assertEquals($s->getRank() - 1, $updatedSize->getRank());
        }
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     */
    public function testArrangeCategories() {
        $service = null;
        $deadlockPreventer = 0;
        $maxDeadlock = 30;

        // find a customer with at least 3 meal categories
        do {
            $service = $this->getRandomService();
            $deadlockPreventer++;
        } while ((count($service->getMealCategories()) < 2) && ($deadlockPreventer < $maxDeadlock));

        if ($deadlockPreventer == $maxDeadlock) {
            $this->assertEquals('Deadlock! No service with enough meal categories found', '');
        }

        $catArr = array();
        $rank = 0;
        foreach ($service->getMealCategories() as $c) {
            $catArr[$rank] = $c->getId();
            $rank++;
        }

        $service->arrangeCategories($catArr, $service->getId());

        foreach ($catArr as $rank => $catId) {
            $category = new Yourdelivery_Model_Meal_Category($catId);
            $this->assertEquals($category->getRank(), $rank);
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.02.2012
     */
    public function testIsRangeOnline() {
        $db = $this->_getDbAdapter();
        // check 30 random offline ranges
        $rangesOfflineQuery = $db->select()->from(array('rp' => 'restaurant_plz'))->where('rp.status = 0')->order('RAND()')->limit(30);

        $rangesOffline = $db->fetchAll($rangesOfflineQuery);
        foreach ($rangesOffline as $rangeOffline) {
            $service = null;
            $service = new Yourdelivery_Model_Servicetype_Restaurant($rangeOffline['restaurantId']);
            $this->assertFalse($service->isRangeOnline($rangeOffline['cityId']));
        }

        // check 30 random online ranges
        $rangesOnlineQuery = $db->select()->from(array('rp' => 'restaurant_plz'))->where('rp.status = 1')->order('RAND()')->limit(30);

        $rangesOnline = $db->fetchAll($rangesOnlineQuery);
        foreach ($rangesOnline as $rangeOnline) {
            $service = null;
            $service = new Yourdelivery_Model_Servicetype_Restaurant($rangeOnline['restaurantId']);
            $this->assertTrue($service->isRangeOnline($rangeOnline['cityId']));
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since21.02.2012
     */
    public function testGetTicketComments() {

        $service = $this->getRandomService();

        $return = $service->getTicketComments();
        $this->assertInternalType("string", $return);
        $count = substr_count($return, "<br />");

        $comment = "testcomment " . Default_Helper::generateRandomString(5);

        $serviceComment = new Yourdelivery_Model_DbTable_Restaurant_Notepad_Ticket();
        $serviceComment->insert(array(
            'restaurantId' => $service->getId(),
            'comment' => $comment,
            'adminId' => 0,
            'allwaysCall' => 0
        ));

        //wait one second because result is ordered by date
        sleep(1);
        $returnNext = $service->getTicketComments();

        $countNext = substr_count($returnNext, "<br />");

        if ($count == 0) {
            $this->assertEquals($count + 1, $countNext);
        } else {
            $this->assertEquals($count, $countNext);
        }


        $this->assertRegExp("/" . $comment . "/", $returnNext);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since21.02.2012
     */
    public function testCreatePlzByPrefixRange() {

        $service = $this->getRandomService();
        $plzprefix = Default_Helper::generateRandomString(2, "1234567890");

        $cityIds = array();
        $cities = Yourdelivery_Model_City::getPlzByPrefix($plzprefix);
        if (count($cities) > 0) {
            foreach ($cities as $city) {
                $cityIds[] = $city->id;
            }

            $service->createPlzByPrefixRange($plzprefix, 1234, 0, 10, 0);

            $db = $this->_getDbAdapter();
            $select = $db->select()->from('restaurant_plz')->where('restaurantId = ?', $service->getId())->where('cityId IN (?)', $cityIds);
            $results = $db->fetchAll($select);

            $this->assertEquals(count($cityIds), count($results));

            foreach ($results as $result) {

                $this->assertEquals($result['mincost'], '123400');
                $this->assertEquals($result['deliverTime'], '10');
            }
        } else {
            $this->markTestSkipped('No Cities found for Prefix ' . $plzprefix);
        }
    }

    /**
     * Store, get and remove documents
     * @author Alex Vait <vait@lieferando.de>
     * @since 08.02.2012
     */
    public function testDocument() {
        $service = $this->getRandomService();
        $DOC_COUNT = 5;

        //create test documents
        $setDocuments = array();

        for ($i = 0; $i < $DOC_COUNT; $i++) {
            $testFile = "testDocument-" . $i . "-" . Default_Helper::generateRandomString() . ".txt";
            $fh = fopen($testFile, 'w');

            // can't use assertTrue directly because the result is flase or resource id
            $this->assertFalse($fh === false);

            fwrite($fh, Default_Helper::generateRandomString());
            fclose($fh);

            $savedName = "restaurantDocument-" . Default_Helper::generateRandomString() . ".txt";
            $data = file_get_contents($testFile);
            $service->getDocumentsStorage()->store($savedName, $data);

            $setDocuments[] = $savedName;

            // remove the file so we have no trash in the directory
            unlink($testFile);
        }

        $savedDocuments = $service->getDocuments();
        $this->assertEquals(count($savedDocuments), $DOC_COUNT);

        foreach ($savedDocuments as $d) {
            $this->assertTrue(in_array($d, $setDocuments));
        }

        //remove all documents
        foreach ($savedDocuments as $d) {
            $service->removeDocument($d);
        }

        // create new object because the old ranges are saved in the former object
        //there must be no documents
        $savedDocuments = $service->getDocuments();
        $this->assertEquals(count($savedDocuments), 0);
        foreach ($savedDocuments as $d) {
            $this->assertFalse(in_array($d, $setDocuments));
        }
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 08.02.2012
     */
    public function testGetSalesCalendar() {
        $service = $this->getRandomService();

        $cityId = $this->getRandomCityId();
        $clRes = $service->createLocation($cityId, 0, 0, 10, 0);
        $this->assertTrue($clRes !== false);

        $location = $this->createLocation(null, $cityId);

        $oldSumOrders = $service->getSalesCalendar();

        $orderId = $this->placeOrder(array('service' => $service, 'location' => $location));
        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertEquals($service->getSalesCalendar(), $oldSumOrders + $order->getTotal());
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 08.02.2012
     */
    public function testGetOrdersCalendar() {
        $service = $this->getRandomService();

        $cityId = $this->getRandomCityId();
        $clRes = $service->createLocation($cityId, 0, 0, 10, 0);
        $this->assertTrue($clRes !== false);

        $location = $this->createLocation(null, $cityId);

        $oldCountOrders = $service->getOrdersCalendar();

        // make a new order
        $orderId = $this->placeOrder(array('service' => $service, 'location' => $location));
        $this->assertTrue($orderId > 0);

        $this->assertEquals($service->getOrdersCalendar(), $oldCountOrders + 1);
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 08.02.2012
     */
    public function testGetOrdersCount() {
        $service = $this->getRandomService();

        $cityId = $this->getRandomCityId();
        $clRes = $service->createLocation($cityId, 0, 0, 10, 0);
        $this->assertTrue($clRes !== false);

        $location = $this->createLocation(null, $cityId);

        $oldOrdersCount = $service->getOrdersCount();

        // make a new order
        $orderId = $this->placeOrder(array('service' => $service, 'location' => $location));
        $this->assertTrue($orderId > 0);

        // create new object because the old ranges are saved in the former object
        $updatedService = new Yourdelivery_Model_Servicetype_Restaurant($service->getId());

        $this->assertEquals($service->getOrdersCount(), $oldOrdersCount + 1);
    }

    /**
     * test calculation of online charge
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.02.2012
     */
    public function testTransactionCost() {

        $this->markTestSkipped('needs to be refactored');

        $service = $this->getRandomService(array('onlinePayment' => true));
        $service->setChargeStart(null);
        $service->setChargeFix(500);
        $service->save();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $service, 'payment' => 'paypal')));
        $this->assertEquals('paypal', $order->getPayment());
        $this->assertEquals($order->getCharge(), 500, $order->getId());
        $order->setPayment('bar');
        $this->assertEquals($order->getCharge(), 0, $order->getId());

        unset($order);
        $service->setChargePercentage(10);
        $service->save();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $service, 'payment' => 'paypal')));
        $this->assertEquals('paypal', $order->getPayment());

        $amount = $order->getAbsTotal(false);
        $percAmount = round(($amount / 100) * 10);

        $this->assertEquals($order->getCharge(), 500 + $percAmount, $order->getId());
        $order->setPayment('bar');
        $this->assertEquals($order->getCharge(), 0, $order->getId());

        //test no charge before
        $service->setChargeStart(date(DATE_DB, strtotime('+1 day')));
        $service->save();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $service, 'payment' => 'paypal')));
        $this->assertEquals('paypal', $order->getPayment());
        $this->assertEquals($order->getCharge(), 0, $service->getId());
    }

    /**
     * check onlycash flag with change of according paymentbar
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.03.2011
     */
    public function testSetOnlycash() {
        $service = $this->getRandomService();
        $service->setPaymentbar(false);
        $service->setOnlycash(true);
        $this->assertTrue($service->getOnlycash());
        $this->assertTrue($service->getPaymentbar());
    }

    /**
     * check paymentbar flag with change of according onlycash
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.03.2011
     */
    public function testSetPaymentbar() {
        $service = $this->getRandomService();
        $service->setOnlycash(true);
        $service->setPaymentbar(false);
        $this->assertFalse($service->getPaymentbar());
        $this->assertFalse($service->getOnlycash());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 03.05.2012
     */
    public function testGetSmsPrinter() {

        $service = $this->getRandomService();

        // clear current associations
        $printer = $service->getSmsPrinter();
        if ($printer instanceof Yourdelivery_Model_Printer_Abstract) {
            $assoc = $printer->getRestaurantAssociations();
            if ($assoc->count() > 0) {
                foreach ($assoc as $a) {
                    $a->getTable()->getCurrent()->delete();
                }
            }
        }

        $service->clearCachedVars();
        $this->assertNull($service->getSmsPrinter());

        $printer = $this->_getRandomPrinter();
        $printer->addRestaurant($service);

        $this->assertEquals($service->getSmsPrinter()->getId(), $printer->getId());
    }

}
