<?php

/**
 * @author mlaug
 * @since 21.07.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class DbTableRestaurantTest extends Yourdelivery_Test {

    /**
     * @author mlaug
     * @since 21.07.2011
     */
    public function testReachable() {

        $service = $this->getRandomService();

        // assign printer
        // and put it online
        $printer = new Yourdelivery_Model_Printer_Topup();
        $printer->setOnline(1);
        $printer->setUpdated(date("Y-m-d H:i:s"));
        $printer->save();
        $printer->addRestaurant($service);

        //basis check
        $dbTable = new Yourdelivery_Model_DbTable_Restaurant();
        $result = $dbTable->checkForUnreachable(array($service->getId()), null, null, 'rest', 'priv');
        $this->assertTrue(is_array($result));
        $this->assertTrue(is_array($result['notReachable']));
        $this->assertTrue(is_array($result['notAvailable']));
        $this->assertEquals(3, count($result));

        //service must be removed, printer offline
        $notify = $service->getNotify();
        $service->setNotify("sms");
        $service->save();
        $row = $printer->getTable()->getCurrent();
        $row->updated = date("Y-m-d H:i:s", time() - 5 * 60 * 60);
        $row->save();
        $result = $dbTable->checkForUnreachable(array($service->getId()), null, null, 'rest', 'priv');
        $this->assertTrue(in_array($service->getId(), $result['notAvailable']));
        $this->assertFalse(in_array($service->getId(), $result['notReachable']));
        $row->updated = date("Y-m-d H:i:s");
        $row->save();
        $service->setNotify($notify);
        $service->save();

        //service must be removed, if closed
        $service->setIsOnline(false);
        $service->save();
        $result = $dbTable->checkForUnreachable(array($service->getId()), null, null, 'rest', 'priv');
        $this->assertTrue(in_array($service->getId(), $result['notReachable']));
        $service->setIsOnline(true);
        $service->save();

        //service must be removed, if this assigned exclusive to a company
        //but first reset restrictions
        foreach ($service->getCompanyRestrictions() as $comp) {
            $service->removeCompanyRestriction($comp->companyId);
        }

        //should not change anything, becuase not exclusive
        $exceptCompanySpecials = array(1218,1235, 1260, 1673, 1674, 1675);
        $company = $this->getRandomCompany(true, true, false, $exceptCompanySpecials);
        $service->setCompanyRestriction($company->getId(), false);
        $result = $dbTable->checkForUnreachable(array($service->getId()), null, null, 'rest', 'priv');
        $this->assertTrue(count($result['notReachable']) == 0);

        //now we set relation exclusive
        $service->removeCompanyRestriction($company->getId());
        $service->setCompanyRestriction($company->getId(), true);
        $result = $dbTable->checkForUnreachable(array($service->getId()), null, null, 'rest', 'priv');
        $this->assertTrue(in_array($service->getId(), $result['notReachable']));

        //now since we inform the method, that we are the company, it should be available again
        $result = $dbTable->checkForUnreachable(array($service->getId()), null, $company->getId(), 'rest', 'priv');
        $this->assertTrue(in_array($service->getId(), $result['notReachable']));
        $service->setOnlycash(false);
        $service->save();
        $company->setServiceListMode(0);
        $company->save();
        $result = $dbTable->checkForUnreachable(array($service->getId()), null, $company->getId(), 'rest', 'comp');
        $this->assertTrue(count($result['notReachable']) == 0, "Count: " . count($result['notReachable']) . " CompId: " . $company->getId() . " RestId: " . $service->getId() . " Rest: " . print_r($result, true));
        $service->removeCompanyRestriction($company->getId());


        // set service exclusive for company and check if available for non-employees of this company
        $service->setCompanyRestriction($company->getId());
        $result = $dbTable->checkForUnreachable(array($service->getId()), null, null, 'rest', 'priv');
        $this->assertTrue(in_array($service->getId(), $result['notReachable']));

        //service must be removed, if this assigned exclusive to a company
        //but first reset restrictions
        $company->setServiceListMode(1);
        $company->save();
        $result = $dbTable->checkForUnreachable(array(1, 2, 3, $service->getId()), null, $company->getId(), 'rest', 'comp');
        $this->assertEquals($result['notReachable'], array(1, 2, 3));
        $service->removeCompanyRestriction($company->getId());

        //check that only cash restaurants will be removed once this is a company order
        $service->setOnlycash(true);
        $service->save();
        $notReachable = $dbTable->checkForUnreachable(array($service->getId()), null, $company->getId(), 'rest', 'comp');
        $this->assertTrue(in_array($service->getId(), $notReachable['notReachable']));

        //check for preveliges
        $customer = $this->getRandomCustomer(true);
        $this->assertTrue($customer->isEmployee());
        $result = $dbTable->checkForUnreachable(array($service->getId()), $customer->getId(), $customer->getCompany()->getId(), 'rest', 'comp');
        $this->assertEquals($customer->isEmployee(), (boolean) $result['permission']['employee']);
        $this->assertEquals($customer->getCurrentBudget(), (integer) $result['permission']['budget']);
        $this->assertEquals($customer->allowCater(), (boolean) $result['permission']['cater']);
        $this->assertEquals($customer->allowGreat(), (boolean) $result['permission']['great']);
        $result = $dbTable->checkForUnreachable(array($service->getId()), $customer->getId(), $customer->getCompany()->getId(), 'rest', 'priv');
        $this->assertFalse((boolean) $result['permission']['employee']);       
    }
    
    /**
     * test the reachable function that should remove restaurant, that only deliver to childs
     * @author Matthias Laug <laug@lieferando.de> 
     */
    public function testUnreachableWithCityId(){   
        $dbTable = new Yourdelivery_Model_DbTable_Restaurant();    
        $customer = $this->getRandomCustomer(true);
        
        //check for children, that needs to be removed
        $tableRestaurantPlz = new Yourdelivery_Model_DbTable_Restaurant_Plz();
        $service = $this->getRandomService(array('online' => true));
        $parent = (integer) $tableRestaurantPlz->fetchRow('restaurantId='.$service->getId())->cityId;
        $this->assertGreaterThan(0, $parent);
        $city = new Yourdelivery_Model_City();
        
        //remove parent from deliver areas
        $tableRestaurantPlz->delete(sprintf('restaurantId=%d and cityId=%s', $service->getId(), $parent));
        
        //create a child
        $city->setData(array(
            'plz' => '00001',
            'city' => 'Test Stadt ' . Default_Helper::generateRandomString(),
            'state' => 'Samson State',
            'stateId' => 1,
            'parentCityId' => $parent,
            'restUrl' => 'lieferservice-samson-00001-' . Default_Helper::generateRandomString(),
            'caterUrl' => 'catering-samson-00001-' . Default_Helper::generateRandomString(),
            'greatUrl' => 'great-samson-00001' . Default_Helper::generateRandomString()
        ));
        
        $child = $city->save();
        $tableRestaurantPlz->createRow(array(
            'restaurantId' => $service->getId(),
            'plz' => '00001',
            'cityId' => $child,
            'status' => 1,
            'deliverTime' => 1800,
            'delcost' => 0,
            'mincost' => 800
        ))->save();
        $this->assertGreaterThan(0, $child);
        $result = $dbTable->checkForUnreachable(array($service->getId()), $customer->getId(), $customer->getCompany()->getId(), 'rest', 'comp', $parent);
        $this->assertTrue(in_array($service->getId(), $result['notReachable']));   
    }

    /**
     * Test of the getRestaurantsWithSatellite() method
     * @author André Ponert <ponert@lieferando.de>
     * @since 16.04.2012
     */
    public function testRestaurantsWithSatellite() {
        $result = Yourdelivery_Model_DbTable_Restaurant::getRestaurantsWithSatellite();

        // check, if we get data at all
        $this->assertNotEquals(null, $result);

        // check if we get an array as resultset
        $this->assertTrue(is_array($result));

        // checking array integrity
        $this->assertArrayHasKeys(array('restaurantId', 'restaurantName', 'sateliteId', 'domain', 'city'), $result[0]);

        // checking result content
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "select r.id as restaurantId from restaurants r where r.isOnline=1 and r.status=0 and r.deleted=0 order by RAND()";
        $sql_result = $db->query($sql)->fetch();

        // fetching id of the randomly chosen entry
        $sql_result_id = $sql_result['restaurantId'];

        // searching the resultset for randomly chosen entry
        foreach ($result as $restaurant) {
            if ($restaurant['restaurantId'] == $sql_result_id) {
                // assert, that both results are equally by content and type
                $this->assertTrue($sql_result_id === $restaurant['restaurantId']);
            }
        }
    }

    /**
     * Test of the getRestaurantsWithoutSatellite() method
     * @author André Ponert <ponert@lieferando.de>
     * @since 16.04.2012
     */
    public function testRestaurantsWithoutSatellite() {

        $result = Yourdelivery_Model_DbTable_Restaurant::getRestaurantsWithoutSatellite();

        // check, if we get data at all
        $this->assertNotEquals(null, $result);

        // check if we get an array as resultset
        $this->assertTrue(is_array($result));

        // checking array integrity
        $this->assertArrayHasKeys(array('restaurantId', 'restaurantName', 'city'), $result[0]);


        // checking result content
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "select r.id as restaurantId, r.name as restaurantName, c.city as city from restaurants r left join satellites s on r.id=s.restaurantId join city c on c.plz=r.plz where s.id is null and r.isOnline=1 and r.status=0 and r.deleted=0 order by RAND() limit 0, 100";
        $sql_result = $db->query($sql)->fetch();

        // searching the resultset for randomly chosen entry
        foreach ($result as $restaurant) {
            if ($restaurant['restaurantId'] == $sql_result['restaurantId'] &&
                    $restaurant['city'] == $sql_result['city']) {

                // assert, that both results are equally by content and type
                $this->assertTrue($sql_result === $restaurant);
            }
        }
    }

    /**
     * Test of the getRestaurantsWithMissingBankingData() method
     * @author André Ponert <ponert@lieferando.de>
     * @since 16.04.2012
     */
    public function testRestaurantsWithMissingBankingData() {
        $result = Yourdelivery_Model_DbTable_Restaurant::getRestaurantsWithMissingBankingData();

        // check, if we get data at all
        $this->assertNotEquals(null, $result);

        // check if we get an array as resultset
        $this->assertTrue(is_array($result));
        
        $bankColumn = 'Blz';
        if ($this->config->domain->base == 'janamesa.com.br') {
            // There is no "BLZ" in Brasil, but "Bank"
            $bankColumn = 'Bank';
        }

        // checking array integrity
        $this->assertArrayHasKeys(array('restaurantId', 'restaurantName', 'ordersCount', 'kontoName', 'kontoNr', 'konto' . $bankColumn), $result[0]);

        /**
         *  since we handle restaurants without banking data, assert that the
         *  data is really missing at least one field
         * 
         * assert there is no entry in the result that has a kontoName, kontoNr
         * and kontoBlz (kontoBank in BR) set
         * 
         * also assert at least 1 order was done
         */
        foreach ($result as $restaurant) {
            $this->assertFalse(
                    $restaurant['kontoName'] != '' &&
                    $restaurant['kontoNr'] != '' &&
                    $restaurant['konto' . $bankColumn] != ''
            );
            $this->assertTrue($restaurant['ordersCount'] > 0);
        }

        // checking result content
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "SELECT r.id as restaurantId, r.name as restaurantName, COUNT(o.id) AS ordersCount, REPLACE(r.ktoName, '0', '') as kontoName, REPLACE(r.ktoNr, '0', '') as kontoNr, REPLACE(r.kto" . $bankColumn . ", '0', '') as konto" . $bankColumn . " FROM restaurants r LEFT JOIN orders o ON r.id=o.restaurantId WHERE r.deleted=0 GROUP BY r.id HAVING ordersCount>0 AND (LENGTH(kontoName)=0 OR LENGTH(kontoNr)=0 OR LENGTH(konto" . $bankColumn . ")=0) order by RAND(0) limit 0,100";
        $sql_result = $db->query($sql)->fetch();

        // searching the resultset for randomly chosen entry
        foreach ($result as $restaurant) {
            if ($restaurant['restaurantId'] == $sql_result['restaurantId']) {

                // assert, that both results are equally by content and type
                $this->assertTrue($sql_result === $restaurant);
            }
        }
    }

    /**
     * Test of the getRestaurantsWithMissingCategoryPicture() method
     * @author André Ponert <ponert@lieferando.de>
     * @since 16.04.2012
     */
    public function testRestaurantsWithMissingCategoryPicture() {
        $result = Yourdelivery_Model_DbTable_Restaurant::getRestaurantsWithMissingCategoryPicture();

        // check, if we get data at all
        $this->assertNotEquals(null, $result);

        // check if we get an array as resultset
        $this->assertTrue(is_array($result));

        // checking result content
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "select r.* from restaurants r join meal_categories mc on r.id=mc.restaurantId left join category_picture cp on cp.id=mc.categoryPictureId where cp.id is null and r.deleted=0 group by r.id order by RAND() limit 0, 100";
        $sql_result = $db->query($sql)->fetch();

        // fetching id of the randomly chosen entry
        $sql_result_id = $sql_result['id'];

        // searching the resultset for randomly chosen entry
        foreach ($result as $restaurant) {
            if ($restaurant['id'] == $sql_result_id) {
                // assert, that both results are equally by content and type
                $this->assertTrue($sql_result === $restaurant);
            }
        }
    }

    /**
     * Test of the edit method 
     * @author André Ponert <ponert@lieferando.de>
     * @since 16.04.2012
     */
    public function testEdit() {
        // fetching some random data to be updated
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "select r.id, r.name, r.plz from restaurants r join city c on c.plz = r.plz order by RAND() limit 0, 100";
        $sql_result = $db->query($sql)->fetch();

        // temp vars to restore original data
        $id = $sql_result['id'];
        $restore_data = $sql_result;

        // changing values
        $data = $sql_result;
        $data['name'] = 'RestaurantTest';
        Yourdelivery_Model_DbTable_Restaurant::edit($id, $data);

        // fetch data again
        $sql = "select r.id, r.name, r.plz from restaurants r join city c on c.plz = r.plz where r.id = $id limit 0, 100";
        $sql_result = $db->query($sql)->fetch();

        // assert, that data has been updated
        $this->assertEquals($sql_result, $data);

        // take the backed up data and update the table
        Yourdelivery_Model_DbTable_Restaurant::edit($id, $restore_data);

        // fetch data once again
        $sql_result = $db->query($sql)->fetch();

        // assert, that data has been restored correctly
        $this->assertEquals($sql_result, $restore_data);
    }

    /**
     * Test of the remove method 
     * @author André Ponert <ponert@lieferando.de>
     * @since 16.04.2012
     */
    public function testRemove() {
        // fetching some random data to be mark deleted
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "select r.id, r.deleted from restaurants r where r.deleted=0 order by RAND()";
        $sql_result = $db->query($sql)->fetch();

        // assert that restaurant is currently not deleted
        $this->assertEquals($sql_result['deleted'], '0');

        // delete the restaurant
        Yourdelivery_Model_DbTable_Restaurant::remove($sql_result['id']);

        // reload restaurant
        $id = $sql_result['id'];
        $sql = "select r.id, r.deleted from restaurants r where r.id=$id";
        $sql_result = $db->query($sql)->fetch();

        // assert, restaurant is deleted now
        $this->assertEquals($sql_result['deleted'], '1');

        // undo deletion
        Yourdelivery_Model_DbTable_Restaurant::edit($sql_result['id'], array(
            'deleted' => '0'
        ));

        // reload restaurant
        $sql_result = $db->query($sql)->fetch();

        // assert, restaurant is restored now
        $this->assertEquals($sql_result['deleted'], '0');
    }

    /**
     * Test of the get method 
     * @author André Ponert <ponert@lieferando.de>
     * @since 17.04.2012
     */
    public function testGet() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $result = Yourdelivery_Model_DbTable_Restaurant::get();

        // check, if we get data at all
        $this->assertNotEquals(null, $result);

        // check if we get an array as resultset
        $this->assertTrue(is_array($result));

        // ** checking queries and compare them to manual queries **
        // get without parameters
        $compare = $db->query("select `%ftable%`.* from restaurants as `%ftable%`")->fetchAll();
        $this->assertTrue($result === $compare);

        // get with order by restaurant name
        $result = Yourdelivery_Model_DbTable_Restaurant::get('name');
        $compare = $db->query("select `%ftable%`.* from restaurants as `%ftable%` order by name")->fetchAll();
        $this->assertTrue($result === $compare);

        // get with limit
        $result = Yourdelivery_Model_DbTable_Restaurant::get(null, 100);
        $compare = $db->query("select `%ftable%`.* from restaurants as `%ftable%` limit 0, 100")->fetchAll();
        $this->assertTrue($result === $compare);

        // get with limit and offset
        $result = Yourdelivery_Model_DbTable_Restaurant::get(null, 100, 12);
        $compare = $db->query("select `%ftable%`.* from restaurants as `%ftable%` limit 12, 100")->fetchAll();
        $this->assertTrue($result === $compare);

        // ** end of query checks **
    }

    /**
     * Test of the findByEmptyCustomerNr method 
     * @author André Ponert <ponert@lieferando.de>
     * @since 17.04.2012
     */
    public function testFindByEmptyCustomerNr() {
        $result = Yourdelivery_Model_DbTable_Restaurant::findByEmptyCustomerNr();
        // check, if we get data at all
        $this->assertNotEquals(null, $result);

        // check if we get an array as resultset
        $this->assertTrue(is_array($result));

        // checking result content
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "SELECT * FROM restaurants WHERE length(customerNr)=0 order by RAND()";
        $sql_result = $db->query($sql)->fetch();

        // fetching id of the randomly chosen entry
        $sql_result_id = $sql_result['id'];

        // searching the resultset for randomly chosen entry
        foreach ($result as $restaurant) {
            if ($restaurant['id'] == $sql_result_id) {
                // assert, that both results are equally by content and type
                $this->assertTrue($sql_result === $restaurant);
            }
        }
    }

    /**
     * Test of the findByCustomerNr method 
     * @author André Ponert <ponert@lieferando.de>
     * @since 17.04.2012
     */
    public function testFindByCustomerNr() {
        $testCustomerNr = 40449;
        $result = Yourdelivery_Model_DbTable_Restaurant::findByCustomerNr($testCustomerNr);

        // check, if we get data at all
        $this->assertNotEquals(null, $result);

        // check if we get an array as resultset
        $this->assertTrue(is_array($result));

        // manually get the restaurant
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "select * from restaurants r where customerNr=$testCustomerNr";
        $comp_result = $db->query($sql)->fetch();

        // assert, both entries are equal
        $this->assertTrue($result === $comp_result);
    }

    /**
     * Test of the findByName method 
     * @author André Ponert <ponert@lieferando.de>
     * @since 18.04.2012
     */
    public function testFindByName() {
        $testName = "Testrestaurant";
        $result = Yourdelivery_Model_DbTable_Restaurant::findByName($testName);
        // check, if we get data at all
        $this->assertNotEquals(null, $result);

        // check if we get an array as resultset
        $this->assertTrue(is_array($result));

        // manually get the restaurant
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "select * from restaurants r where r.name like '%$testName%' and r.isOnline=0 and deleted=0";
        $comp_result = $db->query($sql)->fetch();

        // assert, both entries are equal
        $this->assertTrue($result === $comp_result);
    }

    /**
     * Test of the findByDirectLink method 
     * @author André Ponert <ponert@lieferando.de>
     * @since 18.04.2012
     */
    public function testFindByDirectLink() {
        // test uris
        $testRestUri = "lieferservice-testrestaurant-berlin";
        $testCaterUri = "catering-testrestaurant-berlin";
        $testGreatUri = "grosshandel-testrestaurant-berlin";

        // get results
        $result_rest = Yourdelivery_Model_DbTable_Restaurant::findByDirectLink($testRestUri);
        $result_cater = Yourdelivery_Model_DbTable_Restaurant::findByDirectLink($testCaterUri);
        $result_great = Yourdelivery_Model_DbTable_Restaurant::findByDirectLink($testGreatUri);

        // assert not all results are null
        $nc = 0;
        foreach (array('rest', 'cater', 'great') as $mode) {
            if (${'result_' . $mode} == 0) {
                $nc++;
            }
        }

        $this->assertLessThan(3, $nc);

        // assert, that every result was filled with data
        $this->assertTrue(
                is_array($result_rest) &&
                is_array($result_cater) &&
                is_array($result_great)
        );

        // assert, that all URLs retrieve the same entry
        $this->assertTrue(
                $result_rest[1] === $result_cater[1] &&
                $result_cater[1] === $result_great[1]
        );

        // checking, if sql statement has changed
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $comp_result = null;

        foreach (array('rest', 'cater', 'great') as $mode) {
            $sql = sprintf("
                SELECT *
                    FROM `restaurants` r
                        WHERE r.deleted = 0
                            AND r.%sUrl = ?", $mode);

            // get the uri form the test uris according to current $mode
            $uri = ${'test' . ucfirst($mode) . 'Uri'};

            // using variable variables to get content
            $row = $db->fetchRow($sql, $uri);
            if ($row) {
                $comp_result = array($mode, $row);
            }
        }

        // assert, that the values are in database
        $this->assertTrue(
                $comp_result === ${'result_' . $mode}
        );
    }

    /**
     * Test of the findByOrt method 
     * @author André Ponert <ponert@lieferando.de>
     * @since 18.04.2012
     */
    public function testFindByOrt() {
        
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.04.2012 
     */
    public function testGetRatingAdvicePercentPositiv() {            
        $service = $this->getRandomService();
        $result = $service->getRatingAdvicePercentPositive();
        $this->assertGreaterThanOrEqual(0, $result);
    }

}