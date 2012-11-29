<?php

/**
 * Description of RabattTest
 *
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses
 */
class RabattTest extends Yourdelivery_Test {

    /**
     * test discount, which is only usable more often
     */
    public function testRepeatDiscount() {

        $code1 = $this->createDiscount(true, 1, 10, false, false, false, false, false);
        // nothing should happen here
        $code1->setCodeUsed();
        $this->assertTrue($code1->isUsable());


        $code2 = $this->createDiscount(false, 1, 10, false, false, false, false, false);
        // code should not be usable anymore
        $code2->setCodeUsed();
        $this->assertFalse($code2->isUsable());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.01.2011
     */
    public function testDiscountWithGivenCount() {
        $code = $this->createDiscount(2, 1, 10);
        $code->getParent()->setCountUsage(10)->save();

        $this->assertTrue($code->isUsable());

        $code->setCodeUsed();
        $this->assertTrue($code->isUsable());
        $this->assertEquals($code->getCountUsed(), 1);

        $code->setCodeUsed();
        $this->assertTrue($code->isUsable());
        $this->assertEquals($code->getCountUsed(), 2);

        $code->setCodeUnused();
        $this->assertTrue($code->isUsable());
        $this->assertEquals($code->getCountUsed(), 1);

        $code->setCountUsed(9)->save();
        $this->assertTrue($code->isUsable());
        $this->assertEquals($code->getCountUsed(), 9);

        $code->setCodeUsed();
        $this->assertFalse($code->isUsable());
        $this->assertEquals($code->getCountUsed(), 10);
    }

    /**
     * test discount, which is already too old
     */
    public function testOldDiscount() {
        $code = $this->createDiscount(false, 0, 10, false, false, false, false, false, date('Y-m-d H:i:s', strtotime('-2day')), date('Y-m-d H:i:s', strtotime('-1day')));
        $this->assertTrue($code instanceof Yourdelivery_Model_Rabatt_Code);

        $this->assertFalse($code->isUsable());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.08.2010
     */
    public function testDeleteRabatt() {

        $code = $this->createDiscount();
        $this->assertTrue($code instanceof Yourdelivery_Model_Rabatt_Code);
        $id = $code->getParent()->getId();

        // delete rabatt
        $code->getParent()->delete();

        // check db entries
        // codes table
        $row = null;
        $dbTable = new Yourdelivery_Model_DbTable_RabattCodes();
        $row = $dbTable->findByRabattId($id);
        $this->assertFalse($row);

        // rabatt table
        $row = null;
        $dbTable = new Yourdelivery_Model_DbTable_Rabatt();
        $row = $dbTable->findById($id);
        $this->assertFalse($row);
    }

    public function testSetCodeUsed() {

        $code = $this->createDiscount();
        $this->assertTrue($code instanceof Yourdelivery_Model_Rabatt_Code);

        $this->assertTrue($code->setCodeUsed());

        // check db
        $dbTable = new Yourdelivery_Model_DbTable_RabattCodes();
        $row = $dbTable->findByRabattId($code->getParent()->getId());
        $this->assertEquals(1, $row['used']);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @modiefied fhaferkorn, 05.10.2011
     */
    public function testRabattIsFidelity() {
        $code = $this->createFidelityDiscount();
        $this->assertTrue($code instanceof Yourdelivery_Model_Rabatt_Code);
        $this->assertTrue($code->getParent()->isFidelity());
    }

    public function testSetCodeUnused() {

        $code = $this->createDiscount();
        $this->assertTrue($code instanceof Yourdelivery_Model_Rabatt_Code);

        $this->assertTrue($code->setCodeUsed());

        // check db
        $dbTable = new Yourdelivery_Model_DbTable_RabattCodes();
        $row = $dbTable->findByRabattId($code->getParent()->getId());
        $this->assertEquals(1, $row['used']);

        $this->assertTrue($code->setCodeUnused());

        // check db
        $dbTable = new Yourdelivery_Model_DbTable_RabattCodes();
        $row = $dbTable->findByRabattId($code->getParent()->getId());
        $this->assertEquals(0, $row['used']);
    }

    public function testCodeNotUsableYet() {

        $code = $this->createDiscount(false, 0, 10, false, false, false, false, null, date('Y-m-d H:i:s', strtotime('+3days')));
        $this->assertFalse($code->isUsable());
    }

    /**
     * check the generatecode method
     * @author mlaug
     * @since 25.02.2011
     */
    public function testGenerateCodes() {

        $discount = new Yourdelivery_Model_Rabatt();
        $discount->setData(
                array(
                    'name' => 'TestingDiscount-' . time(),
                    'rrepeat' => 1,
                    'kind' => 1,
                    'rabatt' => 10,
                    'status' => true,
                    'start' => date('Y-m-d H:i:s', time()),
                    'end' => date('Y-m-d H:i:s', time() + 60 * 60 * 24),
                    'onlyPrmium' => false,
                    'onlyCustomer' => false,
                    'onlyCompany' => false,
                    'onlyPrivate' => false,
                    'noCash' => false,
                    'iphoneOnly' => false,
                )
        );
        $this->assertGreaterThan(0, $discount->save());


        $randomString = $discount->getId() . Default_Helper::generateRandomString(10);

        $this->assertTrue(strlen($discount->generateCode()) > 0);
        $this->assertEquals($randomString, $discount->generateCode($randomString));
        $this->assertEquals(2, count($discount->getCodes()));
        $this->assertEquals($randomString, $discount->generateCode($randomString));
        $this->assertEquals(2, count($discount->getCodes()));

        unset($discount);
        $discount = new Yourdelivery_Model_Rabatt();
        $discount->setData(
                array(
                    'name' => 'TestingDiscount-' . time(),
                    'rrepeat' => 1,
                    'kind' => 1,
                    'rabatt' => 10,
                    'status' => true,
                    'start' => date('Y-m-d H:i:s', time()),
                    'end' => date('Y-m-d H:i:s', time() + 60 * 60 * 24),
                    'onlyPrmium' => false,
                    'onlyCustomer' => false,
                    'onlyCompany' => false,
                    'onlyPrivate' => false,
                    'noCash' => false,
                    'iphoneOnly' => false,
                )
        );

        $this->assertGreaterThan(0, $discount->save());
        $this->assertTrue(strlen($discount->generateCode()) > 0);
        $this->assertFalse($discount->generateCode($randomString)); //will be find, but not in the same discount
        $this->assertEquals(1, count($discount->getCodes()));

        $codeLength = 13;
        $codeWithOnlyNumbers = $discount->generateCode(null, $codeLength, '0123456789'); //test for dailydeal, etc.
        $this->assertTrue(is_int((int) $codeWithOnlyNumbers));
        $this->assertTrue(strlen($codeWithOnlyNumbers) == $codeLength); // if this test fails, then you have to increase the discountcodes
        $this->assertEquals(2, count($discount->getCodes()));
        $this->assertEquals(10, strlen($discount->generateCode(null, 10)));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.06.2011
     *
     * @dataProvider diffPercentProvider
     */
    public function testCalculateCorrectDiffPercent($percent, $amount, $expectedDiff, $expectedNewAmount) {
        $code = $this->createDiscount(false, Yourdelivery_Model_Rabatt::RELATIVE, $percent);

        list($diff, $newAmount) = $code->calcDiff($amount);
        $this->assertEquals($diff, $expectedDiff);
        $this->assertEquals($newAmount, $expectedNewAmount);
        $this->assertEquals($amount - $diff, $newAmount);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.06.2011
     * @return array(percentageOfDiscount, amount, expectedDiff, newAmount)
     */
    public function diffPercentProvider() {
        return array(
            array(10, 1234, 123, 1111),
            array(10, 1000, 100, 900),
            array(13, 1000, 130, 870),
            array(17, 1205, 205, 1000),
            array(10, 9876543, 987654, 8888889),
            array(11, 9876543, 1086420, 8790123),
            array(100, 9876, 9876, 0)
        );
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.07.2011
     *
     * @dataProvider diffAbsoluteProvider
     */
    public function testCalculateCorrectDiffAbsolute($absolute, $amount, $expectedDiff, $expectedNewAmount) {

        $code = $this->createDiscount(false, Yourdelivery_Model_Rabatt::ABSOLUTE, $absolute);

        list($diff, $newAmount) = $code->calcDiff($amount);
        $this->assertEquals($diff, $expectedDiff);
        $this->assertEquals($newAmount, $expectedNewAmount);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.06.2011
     * @return array(percentageOfDiscount, amount, expectedDiff, newAmount)
     */
    public function diffAbsoluteProvider() {
        return array(
            array(1200, 1300, 1200, 100),
            array(1000, 900, 900, 0),
            array(432, 1234, 432, 802)
        );
    }

    /*
     * @author Allen Frank <frank@lieferando.de>
     * @since 14-02-2012
     */
    public function testGetZippedDiscountCodes(){
        $discount = new Yourdelivery_Model_Rabatt();
        $discount->setData(
                array(
                    'name' => 'TestingDiscount-' . time(),
                    'rrepeat' => 1,
                    'kind' => 1,
                    'rabatt' => 10,
                    'status' => true,
                    'start' => date('Y-m-d H:i:s', time()),
                    'end' => date('Y-m-d H:i:s', time() + 60 * 60 * 24),
                    'onlyPrmium' => false,
                    'onlyCustomer' => false,
                    'onlyCompany' => false,
                    'onlyPrivate' => false,
                    'noCash' => false,
                    'iphoneOnly' => false,
                    'type' => Yourdelivery_Model_Rabatt::TYPE_REGULAR
                )
        );
        $this->assertGreaterThan(0, $discount->save());


        $randomString = $discount->getId() . Default_Helper::generateRandomString(10);

        $this->assertTrue(strlen($discount->generateCode()) > 0);
        $this->assertEquals($randomString, $discount->generateCode($randomString));
        $this->assertEquals(2, count($discount->getCodes()));
        $this->assertEquals($randomString, $discount->generateCode($randomString));
        $this->assertEquals(2, count($discount->getCodes()));

        $this->assertEquals($discount->getType(), Yourdelivery_Model_Rabatt::TYPE_REGULAR);
        $this->assertEquals(0, Yourdelivery_Model_Rabatt::TYPE_REGULAR);
        $this->assertEquals($discount->getType(), 0);


        $discount->getZipFile();
        $fileLocation = sprintf('/tmp/rabattCodes-%s/codes-%s-0.csv',$discount->getId(),$discount->getId());
        $this->assertEquals(true, is_file($fileLocation));
        $this->assertEquals(true, is_file(sprintf('/tmp/rabattCodes-%s/rabatt_codes-%s.zip',$discount->getId(),$discount->getId())));
        $file = fopen($fileLocation, 'r');
        while (!feof($file)) {
            $codes[] = fgetcsv($file);
        }
        fclose($file);
        foreach ($discount->getCodes() as $key => $code){
            $this->assertEquals($codes[$key+1][0], $code->code);
        }
    }

    /*
     * @author Allen Frank <frank@lieferando.de>
     * @since 14-02-2012
     */
    public function testGetZippedRegistrationCodes(){
        $discount = new Yourdelivery_Model_Rabatt();
        $discount->setData(
                array(
                    'name' => 'TestingDiscount-' . time(),
                    'rrepeat' => 1,
                    'kind' => 1,
                    'rabatt' => 10,
                    'status' => true,
                    'start' => date('Y-m-d H:i:s', time()),
                    'end' => date('Y-m-d H:i:s', time() + 60 * 60 * 24),
                    'onlyPrmium' => false,
                    'onlyCustomer' => false,
                    'onlyCompany' => false,
                    'onlyPrivate' => false,
                    'noCash' => false,
                    'iphoneOnly' => false,
                    'type' => Yourdelivery_Model_Rabatt::TYPE_VERIFICATION_MANY
                )
        );
        $this->assertGreaterThan(0, $discount->save());

        $discount->generateCodes(5);

        $this->assertEquals($discount->getType(), Yourdelivery_Model_Rabatt::TYPE_VERIFICATION_MANY);
        $this->assertEquals(2, Yourdelivery_Model_Rabatt::TYPE_VERIFICATION_MANY);
        $this->assertEquals($discount->getType(), 2);


        $discount->getZipFile();
        $fileLocation = sprintf('/tmp/rabattCodes-%s/codes-%s-0.csv',$discount->getId(),$discount->getId());
        $this->assertEquals(true, is_file($fileLocation));
        $this->assertEquals(true, is_file(sprintf('/tmp/rabattCodes-%s/rabatt_codes-%s.zip',$discount->getId(),$discount->getId())));
        $file = fopen($fileLocation, 'r');
        while (!feof($file)) {
            $codes[] = fgetcsv($file);
        }
        fclose($file);
        foreach ($discount->getCodes() as $key => $code){
            $this->assertEquals($codes[$key+1][0], $code->code);
        }
    }

    /*
     * @author Alex Vait <vait@lieferando.de>
     * @since 30.04.2012
     *
     */
    public function testGetOrder(){
        $discount = new Yourdelivery_Model_Rabatt();
        $discount->setData(
                array(
                    'name' => 'TestingDiscount-' . time(),
                    'rrepeat' => 0,
                    'kind' => 1,
                    'rabatt' => 10,
                    'status' => true,
                    'start' => date('Y-m-d H:i:s', time()),
                    'end' => date('Y-m-d H:i:s', time() + 60 * 60 * 24),
                    'type' => Yourdelivery_Model_Rabatt::TYPE_REGULAR
                )
        );
        $this->assertGreaterThan(0, $discount->save());

        $discount->generateCodes(1);

        $codes = $discount->getCodes();

        $code = new Yourdelivery_Model_Rabatt_Code(null, $codes[0]['id']);
        $order = $code->getOrder();
        $this->assertNull($order);

        $orderId = $this->placeOrder(array('discount' => $code));

        $codeOrder = $code->getOrder();

        $this->assertEquals($orderId, $codeOrder->getId());

        // cleanup

        $discount->delete();
    }

    /**
     * Check if storno Handling works like it should
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 08.05.2012
     */
    public function testHandleStorno() {

        $discount = $this->createDiscount();

        $orderId1 = $this->placeOrder(array('discount' => $discount));

        $order1 = new Yourdelivery_Model_Order($orderId1);
        $this->assertTrue(is_object($order1->getDiscount()));
        $order1->setStatus(Yourdelivery_Model_Order::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON, 'testcase'));

        $orderId2 = $this->placeOrder();
        $order2 = new Yourdelivery_Model_Order($orderId2);
        $order2->getTable()->update(array('rabattCodeId' => $discount->getId()), "id =".$orderId2);
        $order2 = new Yourdelivery_Model_Order($orderId2);
        $this->assertTrue(is_object($order2->getDiscount()));
        $discount->setCodeUsed();


        $discount->getParent()->handleStorno($order1->getState(), -2, $order1);
        //not usable if used in another Order
        $this->assertFalse($discount->isUsable());



        $discount = $this->createDiscount();
        $orderId = $this->placeOrder(array('discount' => $discount));
        $order = new Yourdelivery_Model_Order($orderId);
        //usable if used only once
        $discount->setCodeUsed();

        $discount->getParent()->handleStorno($order->getState(), -2, $order);

        $this->assertTrue($discount->isUsable());


    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 05.06.2012
     */
    public function testRabattRestaurant() {
        $discount = $this->createNewCustomerDiscount(array('type' => 6));

        $service = $this->getRandomService();

        $this->assertTrue($discount->setRestaurants(array($service->getId())));

        $restaurants = $discount->getRestaurants();

        $this->assertEquals(count($restaurants), 1);

        $this->assertTrue($discount->isDiscountRestaurant($service->getId()));

        $service2 = $this->getRandomService();

        //make sure we don't get the same service here
        $count = 0;
        while($service->getId() == $service2->getId() && $count < 50) {
             $service2 = $this->getRandomService();
             $count++;
        }


        $this->assertFalse($discount->isDiscountRestaurant($service2->getId()));

        $this->assertTrue($discount->setRestaurants(array($service2->getId())));

        $this->assertTrue($discount->isDiscountRestaurant($service2->getId()));
        $this->assertFalse($discount->isDiscountRestaurant($service->getId()));

    }


    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 06.06.2012
     */
    public function testRabattCity() {
        $discount = $this->createNewCustomerDiscount(array('type' => 6));

        $cityId = $this->getRandomCityId();

        $this->assertTrue($discount->setCitys(array($cityId)));

        $cities = $discount->getCitys();

        $this->assertEquals(count($cities), 1);

        $this->assertTrue($discount->isDiscountCity($cityId));

        $cityId2 = $this->getRandomCityId();

        //make sure we don't get the same cityId here
        $count = 0;
        while($cityId == $cityId2 && $count < 50) {
             $cityId2 = $this->getRandomCityId();
             $count++;
        }

        $this->assertNotEquals($cityId, $cityId2);

        $this->assertFalse($discount->isDiscountCity($cityId2));

        $this->assertTrue($discount->setCitys(array($cityId2)));

        $this->assertTrue($discount->isDiscountCity($cityId2));
        $this->assertFalse($discount->isDiscountCity($cityId));

    }

    /**
     * tests the getRandomCode method
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 13.08.2012
     */
    public function testGetRandomCode() {
        $discount = $this->createNewCustomerDiscount(array('type' => 0));
        $discount->save();
        $discount->generateCodes(10);
        $codes = $discount->getCodes()->toArray();

        // making code [0] used
        $code1 = new Yourdelivery_Model_Rabatt_Code($codes[0]['code']);
        $code1 ->setCodeUsed();
        $code1->save();

        // making code [1] reserved
        $code2 = new Yourdelivery_Model_Rabatt_Code($codes[1]['code']);
        $code2 ->setReserved(1);
        $code2->save();

        // getting 10 times a random code and assert neither code[0], nor code[1] is returned
        for ($i = 0; $i <= 9; $i++) {
            $this->assertFalse(in_array($discount->getRandomCode(), array($code1->getCode(), $code2->getCode())));
        }
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 16.08.2012
     */
    public function testHasAlreadyBeenUsedWithUuid() {
        
        $uuid = uniqid();

        $discount = $this->createDiscount();
        $discountParent = $discount->getParent();
        $this->assertFalse($discountParent->hasAlreadyBeenUsedForThatUuid($uuid), sprintf('Uuid:%s DiscountId:%s', $uuid, $discountParent->getId()));
        
        $orderId = $this->placeOrder(array('uuid' => $uuid, 'discount' => $discount));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'testcase'));
        $this->assertTrue($discountParent->hasAlreadyBeenUsedForThatUuid($uuid), sprintf('Order:%s Uuid:%s DiscountId:%s', $order->getId(), $uuid, $discountParent->getId()));
      
        $orderId = $this->placeOrder(array('uuid' => $uuid, 'discount' => $discount));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'testcase'));
        $this->assertTrue($discountParent->hasAlreadyBeenUsedForThatUuid($uuid), sprintf('Order:%s Uuid:%s DiscountId:%s', $order->getId(), $uuid, $discountParent->getId()));
    }
}

