<?php

/**
 * @runTestsInSeparateProcesses 
 */
class UserControllerTest extends Yourdelivery_Test {

    private $_password = 'samsontiffy';

    /**
     * Returns the generated Email from generateRandomEmail()
     * @author Allen Frank <frank@lieferando.de>
     * @since 11.03.11
     */
    public static function getRandomEmail() {
        return 'Testmail-' . time() . rand(0, 99) . "@test-no-minute-mailer.de";
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 21.03.2012
     */
    public function testAbmelden() {

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('newsletter_opt_out_reasons')->where('online = 1');
        $result = $db->fetchAll($select);

        $this->dispatch('/unsubscribe');
        $this->assertQueryCount("input[name='email']", 1);
        $this->assertQueryCount("input[name='reason']", count($result));
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 08.03.11
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 29.11.2011
     */
    public function testRegisterSuccessWithSingleOptin() {
        $config = Zend_Registry::get('configuration');
        $config->newsletter->method = 'singleoptin';
        Zend_Registry::set('configuration', $config);

        // check for newsletter is set
        $cust = $this->_testRegisterSuccess();
        $this->assertTrue($cust->getNewsletter(), sprintf('customer #%d %s was not set for newsletter', $cust->getId(), $cust->getEmail()));
    }

    public function testRegisterSuccessWithDoubleOptin() {
        $config = Zend_Registry::get('configuration');
        $config->newsletter->method = 'doubleoptin';
        Zend_Registry::set('configuration', $config);

        // check for newsletter is set
        $cust = $this->_testRegisterSuccess();
        $this->assertFalse($cust->getNewsletter());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.03.2012
     * @return \Yourdelivery_Model_Customer
     */
    private function _testRegisterSuccess() {
        $email = uniqid() . '@' . uniqid() . '.de';

        $randomOrt = $this->getRandomPlz();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'sex' => 'm',
            'prename' => Default_Helper::generateRandomString(13),
            'name' => Default_Helper::generateRandomString(13),
            'street' => 'HomeSweetHome',
            'hausnr' => $this->generateHausnr(),
            'plz' => $randomOrt['plz'],
            'cityId' => $randomOrt['cityId'],
            'tel' => rand(1000000, 9999999),
            'email' => $email,
            'password' => $this->_password,
            'birthday' => '09.04.1984',
            'agb' => 1,
            'privacy' => 1,
            'newcustomer' => 0,
            'action' => 'register'
        ));
        $this->dispatch('/user/register');

        // check, that customer is only once in datababse
        $customerTable = new Yourdelivery_Model_DbTable_Customer();
        $result = $customerTable->fetchAll('email = "' . $email . '" AND deleted = 0');
        $count = $result->count();

        $session = new Zend_Session_Namespace('Default');

        $this->assertEquals(1, $count, sprintf('customer %s was not saved in DB', $email));
        $this->assertEquals($session->customerId, $result->current()->id);

        $cust = new Yourdelivery_Model_Customer($session->customerId);
        $this->assertTrue($cust->isLoggedIn());
        $this->assertRedirectTo('/user/registered');

        $fidConf = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
        // check fidelity points
        $this->assertEquals((integer) $fidConf->fidelity->points->register, $cust->getFidelity()->getPoints());

        $db = Zend_Registry::get('dbAdapter');
        $query = sprintf('SELECT * FROM customer_fidelity_transaction WHERE email = "%s"', $cust->getEmail());
        $transactions = $db->fetchAll($query);

        $this->assertEquals(count($transactions), 1);

        return $cust;
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     */
    public function testValidLoginAndIsEmployee() {

        $session = new Zend_Session_Namespace('Default');

        $custComp = $this->getRandomCustomerCompany();
        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $custComp->getId());
        $db->query($sql);
        $session = new Zend_Session_Namespace('Default');
        $this->resetRequest();
        $this->login($custComp->getEmail(), $this->_password);

        $locations = $custComp->getLocations();
        if (!$locations || count($locations) == 0) {
            $redirectUrl = '/user/settings';
            // don't redirect from loginfailed to loginfailed
        } else {
            $redirectUrl = $custComp->getStartUrl();
        }
        $this->assertRedirectTo($redirectUrl, sprintf('customer #%d %s was not redirected correctly to %s after successfully login', $custComp->getId(), $custComp->getFullname(), $redirectUrl));

        $this->assertTrue($custComp->isEmployee());
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 08.03.11
     */
    public function testRegisterWithExistingUserFail() {

        $email = $this->getRandomEmail();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $randomOrt = $this->getRandomPlz();

        $session = new Zend_Session_Namespace('Default');
        $session->customer = new Yourdelivery_Model_Anonym();

        $request->setPost(array(
            'sex' => 'm',
            'prename' => Default_Helper::generateRandomString(13),
            'name' => Default_Helper::generateRandomString(13),
            'street' => 'HomeSweetHome',
            'hausnr' => $this->generateHausnr(),
            'plz' => $randomOrt['plz'],
            'cityId' => $randomOrt['cityId'],
            'tel' => '03012345678',
            'email' => $email,
            'password' => $this->_password,
            'birthday' => '09.04.1984',
            'agb' => 1,
            'privacy' => 1
        ));
        $this->dispatch('/user/register');
        $this->assertRedirectTo('/user/registered');
        $this->logout();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $randomOrt = $this->getRandomPlz();

        $request->setPost(array(
            'sex' => 'm',
            'prename' => Default_Helper::generateRandomString(13),
            'name' => Default_Helper::generateRandomString(13),
            'street' => 'HomeSweetHome',
            'hausnr' => $this->generateHausnr(),
            'plz' => $randomOrt['plz'],
            'cityId' => $randomOrt['cityId'],
            'tel' => '03012345678',
            'email' => $email,
            'password' => $this->_password,
            'birthday' => '09.04.1984',
            'agb' => 1,
            'privacy' => 1
        ));
        $this->dispatch('/user/register');
        //$this->assertRedirectTo('/user/register');

        $customer = new Yourdelivery_Model_Customer(null, $email);
        $this->assertTrue($customer->delete());
        $this->assertTrue($customer->isDeleted());

        $this->dispatch('/user/register');
        $this->assertRedirectTo('/user/registered');
    }

    /**
     * @author mpantar,afrank
     * @since 09.03.11
     */
    public function testRegisterFailPrenamePasswordSame() {

        $prename = 'testprename';
        $request = $this->getRequest();
        $randomOrt = $this->getRandomPlz();
        $request->setMethod('POST');
        $request->setPost(array(
            'sex' => 'm',
            'prename' => $prename,
            'name' => 'F.',
            'street' => 'HomeSweetHome',
            'hausnr' => $this->generateHausnr(),
            'plz' => $randomOrt['plz'],
            'cityId' => $randomOrt['cityId'],
            'tel' => '03012345678',
            'email' => $this->getRandomEmail(),
            'password' => $prename,
            'birthday' => '09.04.1984',
            'agb' => 1,
            'privacy' => 1
        ));
        $this->dispatch('/user/register');
        $this->assertNotRedirect();
    }

    /**
     * Test whether registration fails when passed hausnr is invalid
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 17.07.2012
     */
    public function testRegisterFailInvalidHausnr() {
        $request = $this->getRequest();
        $randomOrt = $this->getRandomPlz();
        $request->setMethod('POST');
        $request->setPost(array(
            'sex' => 'f',
            'prename' => Default_Helper::generateRandomString(13),
            'name' => Default_Helper::generateRandomString(13),
            'street' => 'HomeSweetHome',
            'hausnr' => $this->generateHausnr(false),
            'plz' => $randomOrt['plz'],
            'cityId' => $randomOrt['cityId'],
            'tel' => rand(1000000, 9999999),
            'email' => $this->getRandomEmail(),
            'password' => $this->_password,
            'birthday' => '13.01.1990',
            'agb' => 1,
            'privacy' => 1,
            'newcustomer' => 0,
            'action' => 'register'
        ));
        $this->dispatch('/user/register');
        $this->assertNotRedirect();
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 11.03.11
     */
    public function testOrdersUserNotLoggedIn() {

        $this->dispatch('/user/orders');
        $this->assertRedirectTo('/');
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 11.03.11
     * @todo modify(only if possible) this _test to a test which tests the grid
     */
    public function testOrdersUserLoggedIn() {

        $cust = $this->createCustomer();
        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $cust->getId());
        $db->query($sql);

        $this->login($cust->getEmail(), $this->_password);

        $this->dispatch('/user/orders');
        $this->assertController('user');
        $this->assertAction('orders');
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 11.03.11
     */
    public function testFavouritesUserNotLoggedIn() {


        $this->dispatch('/user/favourites/');
        $this->assertRedirectTo('/');
    }

    /**
     *
     * @author Allen Frank <frank@lieferando.de>
     * @since 08.04.11
     */
    public function testFavouritesDeleteAFavFromSomeoneElseFail() {

        $session = new Zend_Session_Namespace('Default');

        $cust = $this->createCustomer();
        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $cust->getId());
        $db->query($sql);

        $this->login($cust->getEmail(), $this->_password);

        $favouriteName = 'TestTestTest';
        $orderId = $this->placeOrder(array('customer' => $cust));
        $order = new Yourdelivery_Model_Order($orderId);
        $favourites = new Yourdelivery_Model_DbTable_Order_Favourites();
        //count before should be zero
        $this->assertEquals(0, $favourites->getCount("name='$favouriteName' AND orderId=$orderId"));

        $favouritesId =
                $favourites->insert(array(
            'customerId' => $cust->getId(),
            'orderId' => $orderId,
            'name' => $favouriteName
                ));

        $this->logout();
        $this->resetRequest();
        $session = new Zend_Session_Namespace('Default');
        $session->customerId = null;

        $anotherCust = $this->createCustomer();
        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $anotherCust->getId());
        $db->query($sql);

        $this->login($anotherCust->getEmail(), $this->_password);

        $this->dispatch('/request_user/delfavourite/restId/' . $order->getRestaurantId());
        $this->assertEquals(1, $favourites->getCount("name='$favouriteName' AND orderId=$orderId"));
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 11.03.11
     */
    public function testFavouritesDeleteOneFavouriteSuccess() {

        $session = new Zend_Session_Namespace('Default');

        $cust = $this->createCustomer();
        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $cust->getId());
        $db->query($sql);

        $this->login($cust->getEmail(), $this->_password);

        $favouriteName = 'TestTestTest';
        $orderId = $this->placeOrder(array('customer' => $cust));
        $order = new Yourdelivery_Model_Order($orderId);
        $favourites = new Yourdelivery_Model_DbTable_Order_Favourites();
        //count before should be zero
        $this->assertEquals(0, $favourites->getCount("name='$favouriteName' AND orderId=$orderId"));

        $favouritesId =
                $favourites->insert(array(
            'customerId' => $cust->getId(),
            'orderId' => $orderId,
            'name' => $favouriteName
                ));
        
        $session->customerId = $cust->getId();

        $this->assertEquals(1, $favourites->getCount("name='$favouriteName' AND orderId=$orderId"), sprintf('customerId: %d - orderId: %d - favoriteName: %s' , $cust->getId(), $orderId, $favouriteName));
        
        $this->dispatch('/request_user/delfavourite/restId/' . $order->getRestaurantId());

        //count after insert and delete  should not be one
        $this->assertEquals(0, $favourites->getCount("name='$favouriteName' AND orderId=$orderId"), sprintf('customerId: %d - orderId: %d - favoriteName: %s' , $cust->getId(), $orderId, $favouriteName));
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 11.03.11
     */
    public function testFavouritesDeleteOneFavouriteFail() {

        $session = new Zend_Session_Namespace('Default');

        $cust = $this->createCustomer();
        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $cust->getId());
        $db->query($sql);

        $this->login($cust->getEmail(), $this->_password);

        $orderId = (int) $this->placeOrder();
        $favouriteName = 'ShouldNotBeDeleted';
        $favourites = new Yourdelivery_Model_DbTable_Order_Favourites();
        //count before should be zero
        $this->assertEquals(0, $favourites->getCount("name='$favouriteName' AND orderId=$orderId"));
        $favouritesId =
                $favourites->insert(array(
            'customerId' => $session->customerId,
            'orderId' => $orderId,
            'name' => $favouriteName
                ));

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'del' => Default_Helper::generateRandomString()
        ));
        $this->dispatch('/request_user/delfavourite/restId/' . $orderId);

        //count after insert and failed attempt to delete this should be one
        $this->assertEquals(1, $favourites->getCount("name='$favouriteName' AND orderId=$orderId"));
    }

    /**
     * @author mpantar
     * @since 09.03.11
     */
    public function testSetSettingsUserNotLoggedIn() {

        $this->dispatch('/user/settings');
        $this->assertRedirectTo('/');
    }

    /**
     * @author mpantar
     * @since 30.03.11
     *
     */
    public function testSetSettingsFailNewEmailAlreadyExist() {

        $session = new Zend_Session_Namespace('Default');

        $custOne = $this->getRandomCustomer();
        $cust = $this->getRandomCustomer();

        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $cust->getId());
        $db->query($sql);

        $this->login($cust->getEmail(), $this->_password);


        //Try to change the mail to a already existing mail
        $request = $this->getRequest();
        $request->setMethod('POST');

        $post = array(
            'prename' => $cust->getPrename(),
            'name' => $cust->getName(),
            'email' => $custOne->getEmail(),
            'tel' => $cust->getTel(),
            'sex' => $cust->getSex()
        );
        $request->setPost($post);
        $this->dispatch('/user/settings');
        $this->assertNotRedirectTo('/user/settings');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.04.2012
     */
    public function testSetSettingsFailNewEmptyEMail() {

        $cust = $this->getRandomCustomer();

        $cust = $this->createCustomer();
        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $cust->getId());
        $db->query($sql);

        $this->login($cust->getEmail(), $this->_password);
        $points = $cust->getFidelity()->getPoints();
        // try to change email to empty email
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'prename' => $cust->getPrename(),
            'name' => $cust->getName(),
            'email' => "",
            'tel' => $cust->getTel(),
            'sex' => $cust->getSex()
        ));
        $this->dispatch('/user/settings');
        $checkCustomer = new Yourdelivery_Model_Customer($cust->getId());

        $this->assertEquals($cust->getEmail(), $checkCustomer->getEmail());

        $cust->clearCache();
        $this->assertEquals($points, $cust->getFidelity()->getPoints());
    }

    /**
     * @author mpantar,frank, rawaqah@lieferando.de
     * @since 10.03.11
     */
    public function testSetSettingsSuccess() {
        $session = new Zend_Session_Namespace('Default');

        $cust = $this->createCustomer();
        $oldEmail = $cust->getEmail();

        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $cust->getId());
        $db->query($sql);

        // add 2 fidelity transactions to test migration of points
        $randomPoints1 = rand(12, 987);
        $cust->addFidelityPoint('testSetSettingsSuccess', 'we want to test the migration of an email to another', $randomPoints1);
        $randomPoints2 = rand(12, 987);
        $cust->addFidelityPoint('testSetSettingsSuccess', 'we want to test the migration of an email to another', $randomPoints2);

        $this->login($cust->getEmail(), $this->_password);
        $newMail = $this->getRandomEmail();
        $newpw = 'mrawaqah';
        $this->assertEquals($randomPoints1 + $randomPoints2, $cust->getFidelity()->getPoints());
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'prename' => 'Max',
            'name' => 'Mustermann',
            'email' => $newMail,
            'tel' => '03087654321',
            'sex' => 'w',
            'newpw' => $newpw,
            'newpwagain' => $newpw,
            'birthday_day' => '31',
            'birthday_month' => '10',
            'birthday_year' => '1984'
        ));
        //print_r($_POST); die;
        $this->dispatch('/user/settings');
        $this->assertRedirectTo('/user/settings');
        $cust = new Yourdelivery_Model_Customer($session->customerId);
        $this->assertEquals($cust->getEmail(), $newMail);

        // check fidelity migration
        $this->assertEquals($randomPoints1 + $randomPoints2, $cust->getFidelity()->getPoints(), 'old email: ' . $oldEmail . ' new email: ' . $cust->getEmail());
        $this->assertEquals(md5($newpw), $cust->getPassword());
        $this->assertEquals('1984-10-31', $cust->getBirthday());
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     */
    public function testLocationsNotLoggedIn() {

        $this->dispatch('/user/locations');
        $this->assertRedirectTo('/');
    }

    /**
     * Test if the locationsAction() function deletes location if location id is set on the URL.
     * @author rawaqah@lieferando.de
     * @since 11.04.2012
     */
    public function testlocationsAction() {
        $session = new Zend_Session_Namespace('Default');
        $customer = $this->getRandomCustomer();
        $customerId = $customer->getId();
        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $customerId);
        $db->query($sql);

        $this->login($customer->getEmail(), $this->_password);
        $location = new Yourdelivery_Model_Location();

        $locationData = $location->getTable()->findByCustomerId($customerId);
        $locationId = $locationData['id'];
        if ($locationData) {
            $this->dispatch('/user/locations/del/' . $locationId);
        } else {
            $company = $customer->getCompany();
            $city = new Yourdelivery_Model_City($this->getRandomCityId());

            $location->setData(array(
                'customerId' => $customerId,
                'street' => 'samson',
                'hausnr' => '12',
                'cityId' => $city->getId(),
                'plz' => $city->getPlz(),
                'companyId' => !$company ? '' : $company->getId(),
                'companyName' => !$company ? '' : 'Test',
                'tel' => 123312312
            ));
            $location->save();
            $this->assertEquals($location->getCustomerId(), $customerId);
            $locationId = $location->getId();
            $this->dispatch('/user/locations/del/' . $locationId);
        }
        $location->load($locationId);
        $this->assertTrue($location->isDeleted());
    }

    /**
     * @author mpantar
     * @since 10.03.11
     */
    public function testUnsubscribeFromNewsletterFail() {

        //test with not valid email
        $anonym = new Yourdelivery_Model_Anonym();
        $anonym->setEmail('testmail');
        $this->assertFalse($anonym->setNewsletter(true));
    }

    /**
     * @author mpantar
     * @since 11.03.11
     */
    public function testUnsubscribeFromNewsletterSuccess() {

        $config = Zend_Registry::get('configuration');
        $config->newsletter->method = 'doubleoptin';
        Zend_Registry::set('configuration', $config);

        $randomEmail = 'testing-' . time() . "@test.de";

        $anonym = new Yourdelivery_Model_Anonym();
        $anonym->setEmail($randomEmail);
        $anonym->setNewsletter(true, true);
        $this->assertTrue($anonym->getNewsletter(), sprintf('email %s is not set for newsletter, but should be set', $randomEmail));

        $request = $this->getRequest();

        $this->getRequest()->setPost(array(
            'email' => $randomEmail
        ));
        $this->getRequest()->setMethod('POST');

        $this->dispatch('/unsubscribe');

        $this->assertFalse($anonym->getNewsletter(), sprintf('email %s is set for newsletter, but shouldn\'t be', $randomEmail));

        $db = $this->_getDbAdapter();
        $select = $db->select()->from(array('nr' => 'newsletter_recipients'), array('status'))->where('nr.email = ?', $randomEmail);
        $status = $db->fetchOne($select);
        $this->assertEquals(0, $status);
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     */
    public function testInvalidLogin() {
        $this->dispatch('/');
        $this->resetResponse();
        $this->getRequest()->setPost(array(
            'user' => 'undefinedUser-' . time() . '@something.com',
            'pass' => '1234567'
        ));
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/user/login');
        $this->assertRedirectTo('/user/loginfailed');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.08.2011
     */
    public function testRateOrder() {

        $this->markTestIncomplete('Allen will fix this problem.');

        $customer = $this->createCustomer();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer)));
        $link = '/rate/' . $order->getHash();

        $comment = 'Samson fand es toll um ' . date('Y-m-d H:m');
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'rate-1' => 4,
            'rate-2' => 2,
            'comment' => $comment,
            'title' => 'Testbewertung',
            'advise' => 1
        ));
        $this->dispatch($link);

        // get last insertet row
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->fetchRow('SELECT * FROM restaurant_ratings ORDER BY id DESC LIMIT 1');
        $this->assertEquals($comment, $result['comment']);
        // rating
        $this->assertEquals(0, $result['status']);

        // check fidelity points - customer should not get fidelity points for ratings with state = 0
        $customer = $order->getCustomer();
        $fidConf = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
        $this->assertEquals($fidConf->fidelity->points->order, $customer->getFidelity()->getPoints());
        // confirm rating in admin backend

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'ratingId' => $result['id']
        ));
        $this->dispatch('/request_administration/toggleratingstatus');

        $this->assertEquals($fidConf->fidelity->points->order, $customer->getFidelity()->getPoints());

        // set rating online
        $rating = new Yourdelivery_Model_Servicetype_Rating($result['id']);
        $rating->setStatus(1);
        $rating->save();

        $result = $db->fetchRow('SELECT * FROM restaurant_ratings ORDER BY id DESC LIMIT 1');
        $this->assertEquals(1, $result['status']);

        // rate this order again
        $link = '/rate/' . $order->getHash();

        $badComment = 'Samson fand es beschissen um ' . time();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'rate-1' => 4,
            'rate-2' => 2,
            'comment' => $badComment,
            'title' => 'Testbewertung',
            'advise' => 1
        ));
        $this->dispatch($link);
        $this->assertRedirectTo('/thankyou');

        $result = $db->fetchRow('SELECT * FROM restaurant_ratings WHERE id = ' . $result['id']);
        $this->assertEquals(0, $result['status']);
        $this->assertEquals(1, $result['advise']);
        $this->assertEquals('Testbewertung', $result['title']);
        $this->assertEquals(4, $result['quality']);
        $this->assertEquals(2, $result['delivery']);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 11.12.2011
     */
    public function testDontRateUnconfirmedOrder() {
        $db = Zend_Registry::get('dbAdapter');
        // get a random unconfirmed order not before 30 days
        $orderRow = $db->fetchRow(sprintf("SELECT o.id FROM orders o LEFT JOIN restaurant_ratings rr ON rr.orderId = o.id WHERE rr.id IS NULL AND o.state <= 0 AND o.time > '%s' ORDER BY RAND() LIMIT 1", date('Y-m-d H:i:s', strtotime('-29 days'))));
        $order = new Yourdelivery_Model_Order($orderRow['id']);
        $this->assertTrue($order->isPersistent());
        $this->dispatch('/rate/' . $order->getHash());
        $this->assertRedirect('/');
        // check log entry
        $this->assertTrue(false !== strstr(Default_Helpers_Log::getLastLog(), sprintf('user tried to rate order #%s, which was not affirmed', $order->getId())));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.03.2012
     */
    public function testDontRateOrderWhichIsNotRateableAnyMore() {
        $db = Zend_Registry::get('dbAdapter');
        // get a random confirmed order before 30 days
        $orderRow = $db->fetchRow(sprintf("SELECT id FROM orders WHERE state <= 0 AND time < '%s' ORDER BY RAND() LIMIT 1", date('Y-m-d H:i:s', strtotime('-30 days'))));
        $order = new Yourdelivery_Model_Order($orderRow['id']);
        $this->assertTrue($order->isPersistent());
        $this->dispatch('/rate/' . $order->getHash());
        $this->assertRedirect('/');
        // check log entry
        $this->assertTrue(false !== strstr(Default_Helpers_Log::getLastLog(), sprintf('user tried to rate order #%s, which is not rateable any more', $order->getId())));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.06.2011
     */
    public function testIndexUserLoggedIn() {

        $session = new Zend_Session_Namespace('Default');
        $cust = $this->createCustomer();
        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $cust->getId());
        $db->query($sql);

        $this->login($cust->getEmail(), $this->_password);
        $this->dispatch('user/index');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.06.2011
     */
    public function testIndexUserNotLoggedIn() {

        $this->dispatch('user/index');
        $this->assertRedirectTo('/');
    }

    /**
     * @author mpantar
     * @since 11.04.2011
     */
    public function testCreateBillFaild() {

        $this->dispatch('user/billcreate/hash/123456789');
        $this->assertRedirectTo('/');
    }

    /**
     * @author mpantar
     * @since 11.04.2011
     */
    public function testCreateBillSuccess() {

        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $hash = $order->getHash();

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost(array(
            'street' => $order->getLocation()->getStreet(),
            'hausnr' => $order->getLocation()->getHausnr(),
            'plz' => $order->getLocation()->getPlz(),
            'prename' => substr($order->getCustomer()->getPrename(), 0, 40),
            'name' => substr($order->getCustomer()->getName(), 0, 40),
            'companyName' => $order->getLocation()->getCompanyName()
        ));

        $this->dispatch('/user/billcreate/hash/' . $hash);
        $this->assertRedirectTo('/user/billconfirm');
    }

    /**
     * @author mpantar
     * @since 12.04.2011
     */
    public function testOrderOpenBestellzettelFaild() {

        $this->dispatch('/ordercoupon/1234566345');
        $this->assertRedirectTo('/error/notfound');
    }

    /**
     * @author mpantar
     * @since 12.04.2011
     */
    public function testOrderOpenBestellzettelSuccess() {

        $order = new Yourdelivery_Model_Order($this->placeOrder());

        $this->dispatch('/ordercoupon/' . $order->getHash());
        $this->assertNotRedirectTo('/error/notfound');
    }

    /**
     * Testing if the system redirect user to home page if he tried to access fidelity page without login.
     * @author rawaqah@lieferando.de
     * @since 10.04.2012
     */
    public function testfidelityAction() {
        $this->dispatch('/user/fidelity');
        $this->assertRedirectTo('/');
    }

    /**
     * Testing if the system redirect user to edit profile if he tried to access register page while he is logged in.
     * @author rawaqah@lieferando.de
     * @since 11.04.2012
     */
    public function testRegisterLoggedin() {
        $session = new Zend_Session_Namespace('Default');
        $customer = $this->getRandomCustomer();
        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'' . $this->_password . '\') WHERE id=?', $customer->getId());
        $db->query($sql);

        $this->login($customer->getEmail(), $this->_password);
        $this->dispatch('/user/register');
        $this->assertRedirectTo('/user/settings');
    }

    public function testMigrateEmail() {
        
    }

    /**
     * Generates valid/invalid hausnr value (depending on passed flag)
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 17.07.2012
     *
     * @param boolean $valid
     * @return string
     */
    private function generateHausnr($valid = true) {
        $config = Zend_Registry::get('configuration');
        $hausnrLength = ($valid) ? rand($config->locale->housenumber->min, $config->locale->housenumber->max) : rand($config->locale->housenumber->max + 1, $config->locale->housenumber->max * 2);
        $digits = array();
        for ($i = 0; $i < $hausnrLength; $i++) {
            $digits[] = rand(0, 9);
        }
        return implode('', $digits);
    }

}
