<?php

/**
 * @runTestsInSeparateProcesses 
 */
class RestaurantControllerTest extends Yourdelivery_Test {

    protected $_password = 'samsontiffy';

    /**
     * Testing Index action.
     * @author Mohammad RAWAQA <rawaqa@lieferando.de>
     * @since 18.04.2012
     */
    public function testRestIndexAction() {
        $this->dispatch('/restaurant');
        $this->assertRedirectTo('/login');
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de> 
     * @since 09.07.2012
     */
    private function setAdminAuth() {
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $session->admin->masterAdmin = $this->createRandomAdministrationUser();

        $restaurantSession = new Zend_Session_Namespace('Restaurant');
        $restaurant = $this->getRandomService();
        $restaurantSession->currentRestaurant = $restaurant;
    }

    /**
     * Testing Restaurant actions.
     * @author Mohammad RAWAQA <rawaqa@lieferando.de>
     * @since 18.04.2012
     */
    public function testMealCategories() {
        $this->setAdminAuth();
        $this->dispatch('/restaurant/mealcategories');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }
    
    public function testMealSizes(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/mealsizes');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }

    public function testMeals(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/meals');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }
    
    public function testMenu(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/menu');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }

    public function testMenuPreview(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/menupreview');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }
    
    public function testMealExtras(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/mealextras');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }
    
    public function testMealExtrasGroups(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/mealextrasgroups');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }
    
    public function testMealOptions(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/mealoptions');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }

    public function testMealOptionRows(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/mealoptionrows');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }
    
    public function testOrders(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/orders');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }

    public function testLoginRedirect(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/loginredirect');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }
    
    public function testLocations(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/locations');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }

    public function testStats(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/stats');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }
    
    public function testBilling(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/billing');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }
    
    public function testNotepad(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/notepad');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }
    
    public function testUncache(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/uncache');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }

    public function testAddsize(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/addsize');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }

    public function testSettings(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant_settings');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }

    public function testLogout(){
        $this->setAdminAuth();
        $this->dispatch('/restaurant/logout');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing login with not existing user.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 18.04.2012
     */
    public function testRestaurantLogInIDNotFound() {
        $restaurant = $this->getRandomService();
        $restId = $restaurant->getId();
        $email = 'moka@test.de';

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('user' => $email, 'pass' => $this->_password, 'restaurantId' => $restId));
        $this->dispatch('/restaurant/login');
        $this->assertNotRedirect();
        $this->assertResponseCode(200);
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing loging in with empty data.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 18.04.2012
     */
    public function testRestaurantLogInEmptyData() {
        $restaurant = $this->getRandomService();
        $restId = $restaurant->getId();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('user' => '', 'pass' => '', 'restaurantId' => $restId));

        $this->dispatch('/restaurant/login');
        $this->assertNotRedirect();
        $this->assertResponseCode(200);
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing login with false password.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 18.04.2012
     */
    public function testRestaurantLogInInvalid() {
        $restaurant = $this->getRandomService();
        $restId = $restaurant->getId();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('user' => 'haferkorn@lieferando.de', 'pass' => '123', 'restaurantId' => $restId));

        $this->dispatch('/restaurant/login');
        $this->assertNotRedirect();
        $this->assertResponseCode(200);
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing when loging in with admin privilages.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 18.04.2012
     */
    public function testRestaurantLogInNotAdmin() {
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $restaurantSession = new Zend_Session_Namespace('Restaurant');
        $restaurant = $this->getRandomService();
        $restId = $restaurant->getId();
        $restaurantSession->currentRestaurant = $restaurant;

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('user' => 'haferkorn@lieferando.de', 'pass' => 'ostmukke', 'restaurantId' => $restId));

        $this->dispatch('/restaurant/login');
        $this->assertNotRedirect();
        $this->assertResponseCode(200);

        $this->dispatch('/restaurant/logout');
        $this->assertRedirectTo('/restaurant/login');

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing when a Restaurant Admin logs in.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 18.04.2012
     */
    public function testRestaurantLogInAsAdmin() {
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();

        $restaurantSession = new Zend_Session_Namespace('Restaurant');
        $restaurant = $this->getRandomService();
        $restId = $restaurant->getId();
        $restaurantSession->currentRestaurant = $restaurant;

        $customer = $this->getRandomCustomer();
        $cid = $customer->getId();

        $customer->setData(array('password' => md5('samsontiffy')));
        $customer->save();
        $customerData = $customer->getData();

        $db = Zend_Registry::get('dbAdapter');
        $db->query(sprintf('delete from user_rights where customerId = %d AND kind = "r"', $customer->getId()));
        $db->insert('user_rights', array('customerId' => $cid, 'kind' => "r", 'status' => 1, 'refId' => $restId));

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('user' => $customerData['email'], 'pass' => 'samsontiffy', 'restaurantId' => $restId));

        $this->dispatch('/restaurant/login');
        $this->assertRedirectTo('/restaurant');

        $this->dispatch('/restaurant/logout');
        $this->assertRedirectTo('/restaurant/login');

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing Meal actions.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 19.04.2012
     */
    public function testMealAction() {
        $restaurant = $this->getRandomService();
        $meal = $this->getRandomMealFromService($restaurant);
        $catId = $meal->getCategoryId();

        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $restaurantSession = new Zend_Session_Namespace('Restaurant');
        $restaurant = $this->getRandomService();
        $restaurantSession->currentRestaurant = $restaurant;

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('categoryId' => $catId));

        $this->dispatch('/restaurant/meals');
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * add openings in batch
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 28.08.2012
     */
    public function testAddOpeningsBatch(){
        // set login data
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $restaurantSession = new Zend_Session_Namespace('Restaurant');
        $restaurant = $this->getRandomService();
        $restaurantSession->currentRestaurant = $restaurant;

        // remove all openings from this restaurant
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_openings', 'restaurantId = ' . $restaurant->getId());
        
        $request = $this->getRequest();
        $request->setMethod('POST');
        
        // set openings monday till wednesday, from 10:00 until 13:00
        $request->setPost(array(
            'addFrom' => '100000',
            'addUntil' => '130000',
            'firstDay' => 1,
            'lastDay' => 3
        ));
        
        $this->dispatch('/restaurant_settings/addopeningsbatch');
        
        // **************** TEST 1
        
        // now test if the times were set correctly from monday till wednesday
        for ($weekday = 1; $weekday <= 3; $weekday++) {
            $openings = $restaurant->getRegularOpenings($weekday);
            
            // there must be only one opening
            $this->assertEquals(1, count($openings));
            
            $opening = $openings[0];
            
            $this->assertEquals('10:00:00', $opening['from']);
            $this->assertEquals('13:00:00', $opening['until']);
        }
        
        // now test if the times were NOT set from thursday till sunday and for holidays
        foreach (array(4, 5, 6, 0, 10) as $weekday) {
            $openings = $restaurant->getRegularOpenings($weekday);
            // there must be no openings
            $this->assertEquals(0, count($openings));
        }
                
        // **************** TEST 2
        
        // now set openings from monday till sunday, that intersects with first opening, 12:00 till 15:00
        $request->setPost(array(
            'addFrom' => '120000',
            'addUntil' => '150000',
            'firstDay' => 1,
            'lastDay' => 7
        ));
        
        $this->dispatch('/restaurant_settings/addopeningsbatch');
        
        // now test if the times from monday till wednesday are still the same
        for ($weekday = 1; $weekday <= 3; $weekday++) {
            $openings = $restaurant->getRegularOpenings($weekday);
            
            // there must be only one opening
            $this->assertEquals(1, count($openings));
            
            $opening = $openings[0];            
            $this->assertEquals('10:00:00', $opening['from']);
            $this->assertEquals('13:00:00', $opening['until']);
        }
        
        // now test if the times for other days are set correctly
        foreach (array(4, 5, 6, 0) as $weekday) {
            $openings = $restaurant->getRegularOpenings($weekday);
            
            // there must be only one opening
            $this->assertEquals(1, count($openings));
            
            $opening = $openings[0];            
            $this->assertEquals('12:00:00', $opening['from']);
            $this->assertEquals('15:00:00', $opening['until']);
        }
        
        
        // **************** TEST 3
        
        // now set additional openings from monday till sunday, that intersects only with the second opening, 14:00 till 20:00
        $request->setPost(array(
            'addFrom' => '140000',
            'addUntil' => '200000',
            'firstDay' => 1,
            'lastDay' => 7
        ));
        
        $this->dispatch('/restaurant_settings/addopeningsbatch');
        
        // now test if there are two openings from monday till wednesday
        for ($weekday = 1; $weekday <= 3; $weekday++) {
            $openings = $restaurant->getRegularOpenings($weekday);
            
            // there must be only one opening
            $this->assertEquals(2, count($openings));

            // the first opening
            $opening = $openings[0];
            $this->assertEquals('10:00:00', $opening['from']);
            $this->assertEquals('13:00:00', $opening['until']);
            
            // the new opening
            $opening = $openings[1];
            $this->assertEquals('14:00:00', $opening['from']);
            $this->assertEquals('20:00:00', $opening['until']);            
        }
        
        // now test if the times for other days are still the same
        foreach (array(4, 5, 6, 0) as $weekday) {
            $openings = $restaurant->getRegularOpenings($weekday);
            
            // there must be only one opening
            $this->assertEquals(1, count($openings));
            
            $opening = $openings[0];            
            $this->assertEquals('12:00:00', $opening['from']);
            $this->assertEquals('15:00:00', $opening['until']);
        }        
                
    }    
}

?>
