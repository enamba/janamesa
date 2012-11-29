<?php

/**
 * Description of ServiceTest
 *
 * @author
 */
/**
 * @runTestsInSeparateProcesses
 */
class Administration_ServiceTest extends Yourdelivery_Test {

    protected static $admin = null;
    protected static $db;

    public function setUp() {
        parent::setUp();
        self::$db = Zend_Registry::get('dbAdapter');
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        self::$admin = $session->admin;

        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.12.2011
     */
    public function testCreate() {
        // wait one second - so we avoid restaurants with same name
        sleep(1);

        $city = null;
        $city = new Yourdelivery_Model_City($this->getRandomCityId());

        //Fake Files Array to simlulate real file upload
        $_FILES = array('img' => array('name' => '', 'type' => '', 'tmp_name' => '', 'error' => 4, 'size' => 0));

        $this->assertFalse(is_null($city));

        $name = 'TestRestaurant' . time();
        $street = Default_Helper::generateRandomString(10) . ' Strasse';
        $hausNr = Default_Helper::generateRandomString(3, '1234567890');
        $email = Default_Helper::generateRandomString(8) . '@service.de';
        $tel = Default_Helper::generateRandomString(6, '1234567890');
        $fax = Default_Helper::generateRandomString(6, '1234567890');
        $description = Default_Helper::generateRandomString(20);
        $specialComment = Default_Helper::generateRandomString(20);
        $statecomment = Default_Helper::generateRandomString(20);
        $ktoBase = Default_Helper::generateRandomString(10);

        $notifyArr = array('fax', 'email', 'all', 'sms', 'acom');
        $notify = $notifyArr[array_rand($notifyArr)];

        $faxServiceArr = array('retarus', 'interfax');
        $faxService = $faxServiceArr[array_rand($faxServiceArr)];

        $post = array('name' => $name,
            'isOnline' => 0,
            'street' => $street,
            'hausnr' => $hausNr,
            'cityId' => $city->getId(),
            'plz' => $city->getPlz(),
            'email' => $email,
            'tel' => $tel,
            'fax' => $fax,
            'description' => $description,
            'categoryId' => 7, // spanisch
            'specialComment' => $specialComment,
            'statecomment' => $statecomment,
            'notify' => $notify,
            'img' => '',
            'faxService' => $faxService,
            'service_courier' => -1,
            'service_company' => -1,
            'service_salesperson' => -1,
            'selContactId' => -1,
            'metaRobots' => 'index,follow',
            'ktoName' => $ktoBase . 'Name',
            'ktoNr' => $ktoBase . 'Nr',
            'ktoBlz' => $ktoBase . 'Blz',
            'ktoIban' => $ktoBase . 'Iban',
            'ktoSwift' => $ktoBase . 'Swift',
            'ktoBank' => $ktoBase . 'Bank',
            'ktoAgentur' => $ktoBase . 'Agentur',
            'ktoDigit' => $ktoBase . 'Digit',
            'floorfee' => 666,
            'komm' => 22,
            'fee' => 33,
            'item' => 44,
            'billInterval' => 1, // default is 0
            'billDeliver' => 'fax');

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/administration_service/create');

        $serviceId = (integer) self::$db->fetchOne('SELECT MAX(`id`) FROM `restaurants`');

        $this->assertRedirect('/administration_service_edit/index/id/' . $serviceId);


        $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);

        $this->assertFalse(is_null($service));
        $this->assertNotEquals($service->getId(), 0);

        $this->assertEquals($service->getName(), $name);
        $this->assertEquals($service->getRestUrl(), Default_Helpers_Web::urlify(__('lieferservice-%s-%s', $name, $city->getFullName())));
        $this->assertEquals($service->getCaterUrl(), Default_Helpers_Web::urlify(__('catering-%s-%s', $name, $city->getFullName())));
        $this->assertEquals($service->getGreatUrl(), Default_Helpers_Web::urlify(__('grosshandel-%s-%s', $name, $city->getFullName())));
        $this->assertEquals($service->getIsOnline(), 0);
        $this->assertEquals($service->getStreet(), $street);
        $this->assertEquals($service->getHausnr(), $hausNr);
        $this->assertEquals($service->getCityId(), $city->getId());
        $this->assertEquals($service->getPlz(), $city->getPlz());
        $this->assertEquals($service->getEmail(), $email);
        $this->assertEquals($service->getTel(), $tel);
        $this->assertEquals($service->getFax(), $fax);
        $this->assertEquals($service->getDescription(), $description);
        $this->assertEquals($service->getSpecialComment(), $specialComment);
        $this->assertEquals($service->getCategoryId(), 7);
        $this->assertEquals($service->getStatecomment(), $statecomment);
        $this->assertEquals($service->getNotify(), $notify);
        $this->assertEquals($service->getFaxService(), $faxService);
        $this->assertEquals($service->getKtoName(), $ktoBase . 'Name');
        $this->assertEquals($service->getKtoNr(), $ktoBase . 'Nr');
        $this->assertEquals($service->getKtoBlz(), $ktoBase . 'Blz');
        $this->assertEquals($service->getKtoIban(), $ktoBase . 'Iban');
        $this->assertEquals($service->getKtoSwift(), $ktoBase . 'Swift');
        $this->assertEquals($service->getKtoBank(), $ktoBase . 'Bank');
        $this->assertEquals($service->getKtoAgentur(), $ktoBase . 'Agentur');
        $this->assertEquals($service->getKtoDigit(), $ktoBase . 'Digit');
        $this->assertEquals($service->getFloorfee(), 666);
        $this->assertEquals($service->getKomm(), 22);
        $this->assertEquals($service->getFee(), 33);
        $this->assertEquals($service->getItem(), 44);
        $this->assertEquals($service->getBillInterval(), 1);
        $this->assertEquals($service->getBillDeliver(), 'fax');
        $this->assertEquals($service->getBillInterval(), 1);

        $comments = Yourdelivery_Model_DbTable_Restaurant_Notepad::getComments($service->getId());

        $this->assertEquals(count($comments),1);
        $this->assertEquals($comments[0]['comment'], __b("Dienstleister angelegt."));

    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 13.10.2011
     */
    public function testEdit() {

        $s = $this->getRandomService();

        if (!$s->isOnline()) {
            $status = array_rand(Yourdelivery_Model_Servicetype_Abstract::getStati());
        } else {
            $status = 0;
        }

        $name = $s->getName() . " - test";

        //Fake Files Array to simlulate real file upload
        $_FILES = array('img' => array('name' => '', 'type' => '', 'tmp_name' => '', 'error' => 4, 'size' => 0));

        $post = array('name' => $name,
            'franchiseTypeId' => $s->getFranchise()->getId(),
            'restUrl' => $s->getRestUrl(),
            'caterUrl' => $s->getCaterUrl(),
            'greatUrl' => $s->getGreatUrl(),
            'topUntil' => $s->getTopUntilAsTimestamp(),
            'isOnline' => (int) $s->isOnline(),
            'status' => $status,
            'offline-change-reason-text' => 'test terst',
            'offlineStatusUntil' => '',
            'street' => $s->getStreet(),
            'hausnr' => $s->getHausnr(),
            'cityId' => $s->getCity()->getId(),
            'email' => $s->getEmail(),
            'tel' => $s->getTel(),
            'fax' => $s->getFax(),
            'categoryId' => $s->getCategoryId(),
            'qypeId' => $s->getQypeId(),
            'description' => $s->getDescription(),
            'specialComment' => $s->getSpecialComment(),
            'statecomment' => $s->getStatecomment(),
            'notify' => $s->getNotify(),
            'faxService' => $s->getFaxService(),
            'img' => '',
            'isLogo' => 1,
            'metaTitle' => $s->getMetaTitle(),
            'metaKeywords' => $s->getMetaKeywords(),
            'metaDescription' => $s->getMetaDescription(),
            'metaRobots' => 'index,follow',
            'ktoName' => $s->getKtoName(),
            'ktoNr' => $s->getKtoNr(),
            'ktoBlz' => $s->getKtoBlz(),
            'ktoIban' => $s->getKtoIban(),
            'ktoSwift' => $s->getKtoSwift(),
            'onlycash' => $s->getOnlycash(),
            'billDeliverCost' => $s->getBillDeliverCost(),
            'floorfee' => $s->getFloorfee(),
            'debit' => $s->getDebit(),
            'komm' => $s->getKomm(),
            'fee' => $s->getFee(),
            'item' => $s->getItem(),
            'billInterval' => $s->getBillInterval(),
            'billDeliver' => $s->getBillDeliver(),
            'notifyPayed' => $s->getNotifyPayed(),
            'chargeFix' => ($s->getChargeFix())? $s->getChargeFix(): "45",
            'chargeStart' => date(DATE_DB, time()),
            'editservice' => 'Speichern');


        foreach ($post as &$p) {
            if (is_null($p)) {
                $p = false;
            }
        }

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);
        $url = '/administration_service_edit/index/id/' . $s->getId();

        $this->dispatch($url);

        $this->assertController('administration_service_edit');
        $this->assertAction('index');

        sleep(2); //wait for the transaction to be finished

        $select = self::$db->select()->from('restaurants')->where('id=?', $s->getId());
        $result = self::$db->fetchAll($select);

        $this->assertEquals($result[0]['name'], $name);
        $this->assertEquals($result[0]['status'], $status);
        $select = self::$db->select()->from('admin_access_tracking')->where('modelId=?', $s->getId())
                ->where("modelType='service'")
                ->where('adminId=?', self::$admin->getId())
                ->where('TIMESTAMPDIFF(MINUTE,time,NOW())<5');



        $result = self::$db->fetchAll($select);

        $this->assertTrue(count($result) > 0);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.04.2011
     */
    public function testSetFloorfee() {


        $service = $this->getRandomService();
        $serviceId = (integer) $service->getId();
        $this->assertGreaterThan(0, $serviceId);
        $randFloorFee = rand(10, 9999);
        $this->assertNotEquals($service->getFloorfee(), $randFloorFee);

        $service->setFloorfee($randFloorFee);
        $service->save();
        unset($service);

        $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
        $this->assertEquals($service->getFloorfee(), $randFloorFee);
    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 10.02.2012
     */
    public function testActivateRatings() {

        $service = $this->getRandomService();
        $customer = $this->getRandomCustomer();
        $cityId = $this->getRandomCityId();

        $clRes = $service->createLocation($cityId, 0, 0, 10, 0);
        $this->assertTrue($clRes !== false);

        $location = $this->createLocation(null, $cityId);

        // make a new order
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $service, 'location' => $location, 'customer' => $customer)));

        $this->assertTrue($order->getId()>0);

        $rating = $this->createRating($order, array('advise' => 1));

        // must be offline
        $this->assertEquals($rating->getStatus(), 0);

        $post = array('from' => date('d.m.Y', time()), 'until' => date('d.m.Y', time()), 'advise' => '1');

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/administration_service_ratings/batchactivate');

        $updatedRating = new Yourdelivery_Model_Servicetype_Rating($rating->getId());
        // must be online
        $this->assertEquals($updatedRating->getStatus(), 1);


        // *****************  same stuff but with negative advise
        // make a new order
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $service, 'location' => $location, 'customer' => $customer)));

        $this->assertTrue($order->getId()>0);

        $rating = $this->createRating($order, array('advise' => 0));
        $ratingId = $rating->getId();

        // must be offline
        $this->assertEquals($rating->getStatus(), 0);

        $post = array('from' => date('d.m.Y', time()), 'until' => date('d.m.Y', time()), 'advise' => '1');

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/administration_service_ratings/batchactivate');

        $updatedRating = new Yourdelivery_Model_Servicetype_Rating($rating->getId());
        // must still be offline
        $this->assertEquals($updatedRating->getStatus(), 0);

        $post = array('from' => date('d.m.Y', time()), 'until' => date('d.m.Y', time()), 'advise' => '0');

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/administration_service_ratings/batchactivate');

        $updatedRating = new Yourdelivery_Model_Servicetype_Rating($rating->getId());
        // must be online
        $this->assertEquals($updatedRating->getStatus(), 1);


        // *************** now with all types

        // *****************  same stuff but with negative advise
        // make a new order
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $service, 'location' => $location, 'customer' => $customer)));

        $this->assertTrue($order->getId()>0);

        $rating = $this->createRating($order, array('advise' => 0));
        $ratingId = $rating->getId();

        // must be offline
        $this->assertEquals($rating->getStatus(), 0);

        $post = array('from' => date('d.m.Y', time()), 'until' => date('d.m.Y', time()), 'advise' => '2');

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/administration_service_ratings/batchactivate');

        $updatedRating = new Yourdelivery_Model_Servicetype_Rating($rating->getId());
        // must be online
        $this->assertEquals($updatedRating->getStatus(), 1);

    }

    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 10.02.2012
     */
    public function testDeleteRating() {
        $service = $this->getRandomService();
        $customer = $this->getRandomCustomer();
        $cityId = $this->getRandomCityId();

        $clRes = $service->createLocation($cityId, 0, 0, 10, 0);
        $this->assertTrue($clRes !== false);

        $location = $this->createLocation(null, $cityId);

        // make a new order
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $service, 'location' => $location, 'customer' => $customer)));

        $this->assertTrue($order->getId()>0);

        $rating = $this->createRating($order, array('advise' => 1));

        // must be offline
        $this->assertEquals($rating->getStatus(), 0);

        $transaction = new Yourdelivery_Model_Customer_FidelityTransaction();
        $transaction->setData(array('email' => $customer->getEmail(), 'transactionData' => $order->getId(), 'status' => 0, 'action' => 'rate_low'));
        $transaction->save();

        // must be offline
        $this->assertEquals($transaction->getStatus(), 0);

        $post = array('ratingId' => $rating->getId());

        // delete rating
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/request_administration/deleterating');

        $updatedRating = new Yourdelivery_Model_Servicetype_Rating($rating->getId());
        // must be inactive
        $this->assertEquals($updatedRating->getStatus(), -1);

        // transaction must be inactive
        $updatedTransaction = new Yourdelivery_Model_Customer_FidelityTransaction($transaction->getId());
        $this->assertEquals($updatedTransaction->getStatus(), -1);
    }


    /**
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 10.02.2012
     */
    public function testUndeleteRating() {
        $service = $this->getRandomService();
        $customer = $this->getRandomCustomer();
        $cityId = $this->getRandomCityId();

        $clRes = $service->createLocation($cityId, 0, 0, 10, 0);
        $this->assertTrue($clRes !== false);

        $location = $this->createLocation(null, $cityId);

        // make a new order
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $service, 'location' => $location, 'customer' => $customer)));

        $this->assertTrue($order->getId()>0);

        $rating = $this->createRating($order, array('advise' => 1));

        // must be offline
        $this->assertEquals($rating->getStatus(), 0);

        $transaction = new Yourdelivery_Model_Customer_FidelityTransaction();
        $transaction->setData(array('email' => $customer->getEmail(), 'transactionData' => $order->getId(), 'status' => 0, 'action' => 'rate_low'));
        $transaction->save();

        // must be offline
        $this->assertEquals($transaction->getStatus(), 0);

        $post = array('ratingId' => $rating->getId());

        //delete rating
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/request_administration/deleterating');

        $updatedRating = new Yourdelivery_Model_Servicetype_Rating($rating->getId());
        // must be online
        $this->assertEquals($updatedRating->getStatus(), -1);

        //undelete rating
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/request_administration/undeleterating');

        $updatedRating = new Yourdelivery_Model_Servicetype_Rating($rating->getId());
        // must be active
        $this->assertEquals($updatedRating->getStatus(), 0);
    }

}

?>
