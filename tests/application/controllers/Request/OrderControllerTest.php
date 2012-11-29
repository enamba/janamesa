<?php

/**
 * Description of OrderRequestTest
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */

/**
 * @runTestsInSeparateProcesses
 */
class Request_OrderControllerTest extends Yourdelivery_Test {

    protected static $serviceUrl;
    protected static $cityUrl;

    public function setUp() {
        parent::setUp();
        $this->_initRoutes();
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddDiscountWithoutServiceFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/samsonHatGarKeinenGutschein/service/");
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertFalse($json->result);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddDiscountWithInvalidCodeFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $service = $this->getRandomService(array('onlinePayment' => true));
        $serviceId = $service->getId();

        $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/samsonHatGarKeinenGutschein/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertFalse($json->result);
        $this->assertEquals($json->msg, __("Leider kein gültiger Gutschein. Vielleicht haben Sie sich vertippt."));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddValidDiscountSuccess() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $discount = $this->createDiscount();
        $code = $discount->getCode();
        $service = $this->getRandomService(array('onlinePayment' => true));
        $serviceId = $service->getId();

        $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/" . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertTrue($json->result);
        $this->assertRegExp('/valid-discount/', $json->html, 'HTML does not contain "valid-discount"');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddValidPrivateDiscountOnlyCompanyFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $discount = $this->createDiscount();
        $code = $discount->getCode();
        $service = $this->getRandomService(array('onlinePayment' => true));
        $serviceId = $service->getId();

        $rabatt = $discount->getParent();
        $rabatt->setOnlyPrivate(true);
        $rabatt->save();

        $this->dispatch("/request_order/adddiscount/customer/false/kind/comp/code/" . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertFalse($json->result);
        $this->assertEquals(__("Dieser Gutschein ist nur für Privatkunden"), $json->msg);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddValidCompanyDiscountOnlyCompanySuccess() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $discount = $this->createDiscount();
        $code = $discount->getCode();
        $service = $this->getRandomService(array('onlinePayment' => true));
        $serviceId = $service->getId();

        $rabatt = $discount->getParent();
        $rabatt->setOnlyCompany(true);
        $rabatt->save();

        $this->dispatch("/request_order/adddiscount/customer/false/kind/comp/code/" . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertTrue($json->result);
        $this->assertRegExp('/valid-discount/', $json->html, 'HTML does not contain "valid-discount"');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddValidCompanyDiscountOnlyPrivateFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $discount = $this->createDiscount();
        $code = $discount->getCode();
        $service = $this->getRandomService(array('onlinePayment' => true));
        $serviceId = $service->getId();

        $rabatt = $discount->getParent();
        $rabatt->setOnlyCompany(true);
        $rabatt->save();

        $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/" . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertFalse($json->result);
        $this->assertEquals(__("Dieser Gutschein ist nur für Firmenkunden"), $json->msg);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function specialAssocs() {
        return array(
            array(6473, 13439),
        );
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     *
     * @dataProvider specialAssocs
     */
    public function testAddValidDiscountSpecialAssocSuccess($rabattId, $serviceId) {
        $request = $this->getRequest();
        $request->setMethod('GET');

        // onkel lee rabatt
        $rabatt = new Yourdelivery_Model_Rabatt($rabattId);
        $code = $rabatt->generateCode();
        $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
        $serviceId = $service->getId();

        $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/" . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertTrue($json->result);
        $this->assertRegExp('/valid-discount/', $json->html, 'HTML does not contain "valid-discount"');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     *
     * @dataProvider specialAssocs
     */
    public function testAddValidDiscountSpecialAssocFail($rabattId, $serviceId, $serviceName) {
        $request = $this->getRequest();
        $request->setMethod('GET');

        // onkel lee rabatt
        $rabatt = new Yourdelivery_Model_Rabatt($rabattId);
        $code = $rabatt->generateCode();

        do {
            $service = $this->getRandomService(array('onlinePayment' => true));
        } while ($i++ <= MAX_LOOPS && $service->getId() == $serviceId);

        $serviceId = $service->getId();

        $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/" . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertFalse($json->result);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     * @modified 25.11.2011
     */
    public function testAddValidDiscountLieferando11Success() {
        $this->markTestSkipped('kein System,- sondern ein Backoffice-Fehler');

        $request = $this->getRequest();
        $request->setMethod('GET');

        // onkel lee rabatt
        $rabatt = new Yourdelivery_Model_Rabatt(6472);
        $code = $rabatt->generateCode();

        $invalidAssocs = array();
        $check = true;

        foreach (Yourdelivery_Model_Rabatt::getLieferando11Restaurants() as $serviceId) {
            $request = $this->getRequest();
            $request->setMethod('GET');
            $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
            $serviceId = $service->getId();

            $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/" . $code . "/service/" . $serviceId);
            $json = json_decode($this->getResponse()->getBody());
            $this->assertTrue(is_object($json));
            if (!$json->result) {
                $check = false;
                $invalidAssocs[] = '#' . $serviceId . ' ' . $service->getName();
            }
            $this->assertRegExp('/valid-discount/', $json->html, 'HTML does not contain "valid-discount"');
            $this->resetResponse();
        }
        $msg = 'INFO AN BACKOFFICE: Folgende Lieferdienste sind dem Lieferando11-Gutschein (Druckkostenbeteiligung) zugeordnet akzeptieren aber keine Onlinezahlung. Das heißt, man kann bei diesem DL keinen Gutschein (auch keinen Lieferando11) einlösen, was aber vermutlich mit ihm abgesprochen wurde. Bitte umgehend klären. Entweder der DL akzeptiert die Onlinezahlung, oder wir entfernen ihn von der Lieferando11-Aktion. Bei letzterem bitte der IT (springer@lieferando.de) Bescheid geben. ********* Lieferdienst-Lieferando11 aber keine Onlinezahlung: ' . implode('; ', $invalidAssocs) . ' *********';
        $this->assertTrue($check, '*** COPY INTO MAIL TO BACKOFFICE ***  ' . $msg);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddValidDiscountLieferando11Fail() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        // onkel lee rabatt
        $rabatt = new Yourdelivery_Model_Rabatt(6472);
        $code = $rabatt->generateCode();

        // get service, that is not associated with this discount
        do {
            $service = $this->getRandomService(array('onlinePayment' => true));
        } while ($i++ <= MAX_LOOPS && in_array($service->getId(), Yourdelivery_Model_Rabatt::getLieferando11Restaurants()));

        $serviceId = $service->getId();

        $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/" . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertFalse($json->result);
        $this->assertEquals(__('Dieser Gutschein ist bei diesem Restaurant nicht einlösbar'), $json->msg);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddUsedDiscountFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $discount = $this->createDiscount();
        $code = $discount->getCode();
        $service = $this->getRandomService(array('onlinePayment' => true));
        $serviceId = $service->getId();

        $discount->setCodeUsed();

        $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/" . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertFalse($json->result);
        $this->assertEquals(__('Der Gutschein wurde schon benutzt oder wurde noch nicht aktiviert. Zum Aktivieren des Gutscheins bitte auf die genannte Aktionsseite gehen.'), $json->msg);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 09.07.2012
     */
    public function testAddDiscountButFraud(){
        $request = $this->getRequest();
        $request->setMethod('GET');

        $toTest = array(0,1,2,3,5);
        $db = Zend_Registry::get('dbAdapter');
        foreach($toTest as $type){
            $discount = $this->createNewCustomerDiscount(array("type" => $type));
            $rabattTable = new Yourdelivery_Model_DbTable_RabattCodes();
            $code = Default_Helper::generateRandomString();
            $rabattTable->createRow(array(
                'code' => $code,
                'rabattId' => $discount->getId()
            ))->save();

            $service = $this->getRandomService(array('onlinePayment' => true));
            $serviceId = $service->getId();

            $db->query('truncate blacklist');
            $db->query('truncate blacklist_matching');
            $db->query('truncate blacklist_values');

            $blacklist = new Yourdelivery_Model_Support_Blacklist();
            $blacklist->setAdminId(1);
            $blacklist->setComment('testing value');
            $blacklist->setOrderId(null);
            $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_IP_NEWCUSTOMER_DISCOUNT, Default_Helpers_Web::getClientIp(), Yourdelivery_Model_Support_Blacklist::MATCHING_EXACT);
            $blacklist->save();

            $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/" . $code . "/service/" . $serviceId);
            $json = json_decode($this->getResponse()->getBody());
            $this->assertTrue(is_object($json));

            if($type == 0){
                // normal discount has to be allowed with blacklisted
                $this->assertTrue($json->result);
            }else{
                $this->assertFalse($json->result);
            }
            $this->resetRequest();
            $this->resetResponse();

            //still an order with this ip should work properly
            $order = new Yourdelivery_Model_Order($this->placeOrder(array('checkForFraud' => false)));
            $this->assertGreaterThanOrEqual(0, $order->getStatus());
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddIPhoneDiscountFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $discount = $this->createDiscount();
        $code = $discount->getCode();
        $service = $this->getRandomService(array('onlinePayment' => true));
        $serviceId = $service->getId();

        $rabatt = $discount->getParent();
        $rabatt->setOnlyIphone(true);
        $rabatt->save();

        $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/" . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertFalse($json->result);
        $this->assertEquals(__("Dieser Gutschein ist nur in der Android- oder iPhone-App von Lieferando.de einlösbar."), $json->msg);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddDiscountNotUsableYetFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $discount = $this->createDiscount();
        $code = $discount->getCode();
        $service = $this->getRandomService(array('onlinePayment' => true));
        $serviceId = $service->getId();

        $rabatt = $discount->getParent();
        $rabatt->setStart(time() + 360);
        $rabatt->save();

        $this->dispatch("/request_order/adddiscount/customer/false/kind/priv/code/" . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertFalse($json->result);
        $this->assertEquals(__("Dieser Gutschein ist noch nicht gültig"), $json->msg);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddValidDiscountToOnlyCashRestaurantFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        // get service that does not allow online payment
        $service = $this->getRandomService(array('onlinePayment' => false));
        $serviceId = $service->getId();
        $discount = $this->createDiscount();
        $code = $discount->getCode();


        $this->dispatch('/request_order/adddiscount/customer/false/kind/priv/code/' . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertFalse($json->result);
        $this->assertEquals(__('Bei diesem Restaurant können keine Gutscheine eingelöst werden, da keine Online- oder Teil-Online-Zahlung akzeptiert wird.'), $json->msg);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddDiscountToPremiumServiceSuccess() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        // get a premium service that does allow online payment
        $service = $this->getRandomService(array('onlinePayment' => true, 'premium' => true));
        $this->assertTrue($service->isPremium(), sprintf('serviceId %s', $service->getId()));
        
        $rabattCode = $this->createDiscount(false, 0, 10, FALSE, FALSE, false, true);
        $code = $rabattCode->getCode();

        $this->dispatch('/request_order/adddiscount/customer/false/kind/priv/code/' . $code . "/service/" . $service->getId());
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertTrue($json->result, $service->getId());
        $this->assertRegExp('/valid-discount/', $json->html, 'HTML does not contain "valid-discount"');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     */
    public function testAddPremiumDiscountToNonPremiumServiceFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        // get service that does not allow online payment
        $service = $this->getRandomService(array('onlinePayment' => true));
        $serviceId = $service->getId();
        $checkPremium = $service->getFranchiseTypeId();
        $service->setFranchiseTypeId(1);
        $service->save();

        $discount = $this->createDiscount();
        $code = $discount->getCode();

        $rabatt = $discount->getParent();
        $rabatt->setOnlyPremium(true);
        $rabatt->save();

        $this->dispatch('/request_order/adddiscount/customer/false/kind/priv/code/' . $code . "/service/" . $serviceId);
        $json = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($json));
        $this->assertFalse($json->result);
        $this->assertEquals(__("Dieser Gutschein ist nur für Premium-Restaurants"), $json->msg);

        $service->setFranchiseTypeId($checkPremium);
        $service->save();
    }

    /**
     * @author
     * @since
     */
    protected function _initRoutes() {
        $cityId = $this->getRandomCityId();
        $city = new Yourdelivery_Model_City($cityId);

        $services = Yourdelivery_Model_Order_Abstract::getServicesByCityId($cityId, "rest");
        $service = $services[array_rand($services)];

        self::$serviceUrl = "/" . $service->getRestUrl();
        self::$cityUrl = "/" . $city->getRestUrl();
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 16.04.2012
     */
    public function testSearchMealsSuccess() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $service = $this->getRandomService();

        $testMealName = time() . 'mealName';
        $costMin = '500';
        $costMax = '2345';

        $category = new Yourdelivery_Model_Meal_Category();
        $category->setData(
                array(
                    'name' => $testMealName . '_category',
                    'restaurantId' => $service->getId()
        ));
        $category->save();

        $size1 = new Yourdelivery_Model_Meal_Sizes();
        $size1->setData(
                array(
                    'name' => $testMealName . '_size1',
                    'restaurantId' => $service->getId()
        ));
        $size1->save();

        $size2 = new Yourdelivery_Model_Meal_Sizes();
        $size2->setData(
                array(
                    'name' => $testMealName . '_size2',
                    'restaurantId' => $service->getId()
        ));
        $size2->save();

        $meal = new Yourdelivery_Model_Meals();
        $meal->setData(array(
            'name' => $testMealName,
            'categoryId' => $category->getId(),
            'restaurantId' => $service->getId()
        ));
        $mealId = $meal->save();

        $sizeNn1 = new Yourdelivery_Model_Meal_SizesNn();
        $sizeNn1->setData(
                array(
                    'mealId' => $meal->getId(),
                    'sizeId' => $size1->getId(),
                    'cost' => $costMin
        ));
        $sizeNn1->save();

        $sizeNn2 = new Yourdelivery_Model_Meal_SizesNn();
        $sizeNn2->setData(
                array(
                    'mealId' => $meal->getId(),
                    'sizeId' => $size2->getId(),
                    'cost' => $costMax
        ));
        $sizeNn2->save();

        $this->dispatch('/request_order/search/search?search=' . $testMealName . '&ids[]=' . $service->getId());
        $json = json_decode($this->getResponse()->getBody());

        $foundMeals = $json->meals;
        $this->assertTrue(is_array($foundMeals[0]));

        $firstMeal = $foundMeals[0][0];

        $this->assertEquals($mealId, $firstMeal->id);
        $this->assertEquals($service->getId(), $firstMeal->restaurantId);
        $this->assertEquals($testMealName, $firstMeal->name);
        $this->assertEquals($costMin, $firstMeal->min);
        $this->assertEquals($costMax, $firstMeal->max);

        $category->setWeekdays(pow(2, (intval(date('N', time())) + 1) % 7 - 1));
        $category->save();
        $meal->setName($meal->getName() . '-2');
        $meal->save();

        $this->dispatch('/request_order/search/search?search=' . $meal->getName() . '&ids[]=' . $service->getId());
        $json = json_decode($this->getResponse()->getBody());

        // nothing shall be found
        $foundMeals = $json->meals;
        $this->assertFalse(is_array($foundMeals[0]));

        // change again to today
        $category->setWeekdays(127);
        $category->save();
        $meal->setName($meal->getName() . '-3');
        $meal->save();

        // cleaning
        Yourdelivery_Model_DbTable_Meal_SizesNn::removeByMeal($mealId);
        Yourdelivery_Model_DbTable_Meal_Categories::remove($category->getId());
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meals', 'meals.id = ' . $mealId);
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 16.04.2012
     */
    public function testSearchMealsWrongWeekday() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $service = $this->getRandomService();

        $testMealName = time() . 'mealName';

        // set weekday of category to tomorrow
        $category = new Yourdelivery_Model_Meal_Category();
        $category->setData(
                array(
                    'name' => $testMealName . '_category',
                    'restaurantId' => $service->getId(),
                    'weekdays' => pow(2, (intval(date('N', time())) + 1) % 7 - 1)
        ));
        $category->save();

        $size = new Yourdelivery_Model_Meal_Sizes();
        $size->setData(
                array(
                    'name' => $testMealName . '_size',
                    'restaurantId' => $service->getId()
        ));
        $size->save();

        $meal = new Yourdelivery_Model_Meals();
        $meal->setData(array(
            'name' => $testMealName,
            'categoryId' => $category->getId(),
            'restaurantId' => $service->getId()
        ));
        $mealId = $meal->save();

        $sizeNn = new Yourdelivery_Model_Meal_SizesNn();
        $sizeNn->setData(
                array(
                    'mealId' => $meal->getId(),
                    'sizeId' => $size->getId(),
                    'cost' => '500'
        ));
        $sizeNn->save();

        $this->dispatch('/request_order/search/search?search=' . $testMealName . '&ids[]=' . $service->getId());
        $json = json_decode($this->getResponse()->getBody());

        // nothing shall be found
        $foundMeals = $json->meals;
        $this->assertFalse(is_array($foundMeals[0]));

        // cleaning
        Yourdelivery_Model_DbTable_Meal_SizesNn::removeByMeal($mealId);
        Yourdelivery_Model_DbTable_Meal_Categories::remove($category->getId());
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meals', 'meals.id = ' . $mealId);
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 16.04.2012
     */
    public function testSearchMealsWrongDaytime() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        $service = $this->getRandomService();

        $testMealName = time() . 'mealName';

        // set end time of category to (now-10 minutes)
        $category = new Yourdelivery_Model_Meal_Category();
        $category->setData(
                array(
                    'name' => $testMealName . '_category',
                    'restaurantId' => $service->getId(),
                    'to' => date('H:i:s', time() - 600)
        ));
        $category->save();

        $size = new Yourdelivery_Model_Meal_Sizes();
        $size->setData(
                array(
                    'name' => $testMealName . '_size',
                    'restaurantId' => $service->getId()
        ));
        $size->save();

        $meal = new Yourdelivery_Model_Meals();
        $meal->setData(array(
            'name' => $testMealName,
            'categoryId' => $category->getId(),
            'restaurantId' => $service->getId()
        ));
        $mealId = $meal->save();

        $sizeNn = new Yourdelivery_Model_Meal_SizesNn();
        $sizeNn->setData(
                array(
                    'mealId' => $meal->getId(),
                    'sizeId' => $size->getId(),
                    'cost' => '500'
        ));
        $sizeNn->save();

        $this->dispatch('/request_order/search/search?search=' . $testMealName . '&ids[]=' . $service->getId());
        $json = json_decode($this->getResponse()->getBody());

        // nothing shall be found
        $foundMeals = $json->meals;
        $this->assertFalse(is_array($foundMeals[0]));

        // cleaning
        Yourdelivery_Model_DbTable_Meal_SizesNn::removeByMeal($mealId);
        Yourdelivery_Model_DbTable_Meal_Categories::remove($category->getId());
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meals', 'meals.id = ' . $mealId);
    }

    /**
     * test the lastorder request
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     */
    public function testGetLastOrder() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());

        //found an order
        $this->dispatch(sprintf('/request_order/lastorder?hash=%s&mode=%s&kind=priv', $order->getHashtag(), 'rest', 'priv'));
        $this->assertResponseCode(200);

        //wrong kind
        $this->dispatch(sprintf('/request_order/lastorder?hash=%s&mode=%s&kind=priv', $order->getHashtag(), 'cater', 'priv'));
        $this->assertResponseCode(204);

        //wrong mode
        $this->dispatch(sprintf('/request_order/lastorder?hash=%s&mode=%s&kind=priv', $order->getHashtag(), 'rest', 'comp'));
        $this->assertResponseCode(204);

        //no hash provided
        $this->dispatch(sprintf('/request_order/lastorder?hash=%s&mode=%s&kind=priv', 'asd', 'rest', 'priv'));
        $this->assertResponseCode(204);
    }

    /**
     * Checks whether fidelity max cost getter returns a numeric value
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 20.06.2012
     */
    public function testGetFidelityMaxCost() {
        $this->dispatch('/request_order/getfidelitymaxcost');
        $this->assertResponseCode(200);
        $rawBody = $this->getResponse()->getBody();
        $responseArray = Zend_Json::decode($rawBody);
        $this->assertEquals(1, count($responseArray));
        $this->assertArrayHasKey('maxCost', $responseArray);
        $this->assertTrue(is_numeric($responseArray['maxCost']));
    }

    /**
     * try to repeat an not available order
     * @author Matthias Laug
     * @since 05.07.2012
     */
    public function testRepeatOrderNotFound() {
        $this->dispatch('/request_order/repeat?hash=123');
        $this->assertResponseCode(404);
    }

    /**
     * repeat an order but service is not available any more
     * @author Matthias Laug
     * @since 05.07.2012
     */
    public function testRepeatOrderNotAvailable() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $service = $order->getService();
        $service->setIsOnline(false);
        $service->save();
        $this->dispatch('/request_order/repeat?hash=' . $order->getHashtag());
        $this->assertResponseCode(406);
        $service->setIsOnline(true);
        $service->save();
    }

    /**
     * repeat an order and check if card is the same as before
     * @author Matthias Laug
     * @since 05.07.2012
     */
    public function testRepeatOrderSuccess() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->dispatch('/request_order/repeat?hash=' . $order->getHashtag());
        $this->assertResponseCode(200);
        $response = $this->getResponse()->getBody();
        $this->assertGreaterThan(0, strlen($response));
        $json = json_decode($response);
        $this->assertTrue(is_object($json));

        $cart = $order->getCard();
        foreach ($cart['bucket'] as $cElem) {
            foreach ($cElem as $elem) {

                $count = (integer) $elem['count'];
                $mealId = (integer) $elem['meal']->getId();
                $sizeId = (integer) $elem['size'];

                $found = false;
                foreach ($json->meal as $hash => $meal) {
                    if ((integer) $meal->id == $mealId &&
                            (integer) $meal->size == $sizeId &&
                            (integer) $meal->count == $count) {
                        $found = true;
                        break;
                    }
                }

                $this->assertTrue($found);
            }
        }
    }

}

