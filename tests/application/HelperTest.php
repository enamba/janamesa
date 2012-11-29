<?php
/**
 * @runTestsInSeparateProcesses 
 */
class HelpersTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.01.2012
     */
    public function testGetRandomService() {

        $service = $this->getRandomService();
        $this->assertTrue($service->isRestaurant());
        $this->assertFalse($service->isDeleted());

        $service = $this->getRandomService(array('online' => true));
        $this->assertTrue($service->isOnline());

        $service = $this->getRandomService(array('online' => false));
        $this->assertFalse($service->isOnline());

        $service = $this->getRandomService(array('deleted' => true));
        $this->assertTrue($service->isDeleted());

        $service = $this->getRandomService(array('onlinePayment' => false));
        $this->assertFalse($service->isDeleted());

        $service = $this->getRandomService(array('type' => 1));
        $this->assertTrue($service->isRestaurant());

        $service = $this->getRandomService(array('type' => 2));
        $this->assertTrue($service->isCatering());

        $service = $this->getRandomService(array('type' => 3));
        $this->assertTrue($service->isGreat());

        $service = $this->getRandomService(array('plz' => 10115));
        $ranges = $service->getRanges();
        $check = null;
        foreach($ranges as $range){
            if($range['plz'] == 10115){
                $check = $range['plz'];
                break;
            }
        }
        $this->assertNotNull($check);
        $this->assertEquals(10115, $check);

        $service = $this->getRandomService(array('onlinePayment' => true));
        $this->assertFalse($service->isOnlycash());

        $service = $this->getRandomService(array('onlinePayment' => false));
        $this->assertTrue($service->isOnlycash());

        $service = $this->getRandomService(array('barPayment' => true));
        $this->assertTrue($service->isPaymentbar());

        $service = $this->getRandomService(array('barPayment' => false));
        $this->assertFalse($service->isPaymentbar());

        $service = $this->getRandomService(array('excludeCourier' => true));
        $this->assertFalse($service->hasCourier());
        
        $service = $this->getRandomService(array('notify' => 'all'));
        $this->assertEquals('all', $service->getNotify());
        
        $service = $this->getRandomService(array('notify' => 'email'));
        $this->assertEquals('email', $service->getNotify());
        
        $service = $this->getRandomService(array('notify' => 'fax'));
        $this->assertEquals('fax', $service->getNotify());
        
        $service = $this->getRandomService(array('notify' => 'sms'));
        $this->assertEquals('sms', $service->getNotify());
        
        $service = $this->getRandomService(array('notify' => 'smsemail'));
        $this->assertEquals('smsemail', $service->getNotify());

    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de> (at the beach)
     * @since 11.09.2011
     */
    public function testGetRandomMealSize() {
        $service = $this->getRandomService();
        $meals = $service->getMeals();
        shuffle($meals);
        $meal = new Yourdelivery_Model_Meals($meals[0]['id']);
        $this->assertTrue($meal->isPersistent(),
                sprintf('Service #%s %s - ShuffleMeal: ', $service->getId(), $service->getName(), $meals[0]['id']));
        $size = $this->getRandomMealSize($meal);

        $meal->setCurrentSize($size->getId());
        $this->assertTrue($size instanceof Yourdelivery_Model_Meal_Sizes);
        $this->assertTrue($size->isPersistent(),
                sprintf('Service #%s %s - Meal #%s %s - Size #%s %s',
                        $service->getId(), $service->getName(),
                        $meal->getId(), $meal->getName(),
                        $size->getId(), $size->getName()));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de> (at the beach)
     * @since 11.01.2012
     */
    public function testPlaceOrder(){

        $this->placeOrder();

        $this->placeOrder(array('payment' => 'bar', 'kind' => 'priv', 'mode' => 'rest'));

        $this->placeOrder(array('discount' => true));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de> (at the beach)
     * @since 11.01.2012
     */
    public function testPlaceOrderGreat(){

        $this->placeOrder(array('discount' => $this->createDiscount(), 'mode' => 'cater'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de> (at the beach)
     * @since 11.01.2012
     */
    public function testPlaceOrderCater(){

        $this->placeOrder(array('discount' => $this->createDiscount(), 'mode' => 'cater'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 02.01.2012
     */
    public function testGetRandomCustomer(){

        $customer = $this->getRandomCustomer();
        $this->assertTrue($customer->isPersistent());
        $this->assertInstanceof(Yourdelivery_Model_Customer_Abstract, $customer);

        $customer = null;
        $customer = $this->getRandomCustomer(false);
        $this->assertTrue(is_null($customer->getCompany()), ' customerId: '.$customer->getId());
        $this->assertFalse($customer->isEmployee());

        $customer = null;
        $customer = $this->getRandomCustomer(true);
        $this->assertInstanceof(Yourdelivery_Model_Company, $customer->getCompany());
        $this->assertTrue($customer->isEmployee());


        $customer = null;
        $customer = $this->getRandomCustomer(false, true);
        $this->assertTrue(is_null($customer->getCompany()));
        $this->assertFalse($customer->isEmployee());
        $this->assertGreaterThan(0, $customer->getLocations()->count());

        $customer = null;
        $customer = $this->getRandomCustomer(null, true);
        $this->assertGreaterThan(0, $customer->getLocations()->count());

        $customer = null;
        $customer = $this->getRandomCustomer(null, null, false);
        $this->assertNull($customer->getDiscount());

    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 04.06.2012
     */
    public function testGetRandomCompany(){
        $except = array(1218,1235, 1260, 1673, 1674, 1675);
        $company = $this->getRandomCompany(true, true, false, $except);
        $this->assertTrue(!in_array($company->getId(), $except));
    }

}

?>
