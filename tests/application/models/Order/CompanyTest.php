<?php

/**
 * Description of Order_CompanyTest
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Order_CompanyTest extends Yourdelivery_Test {

    /**
     * @author mlaug
     * @since 02.11.2010
     */
    public function testCompanySingleRestaurantOrder() {

        if ($this->config->domain->base != 'lieferando.de') {
            $this->markTestSkipped(
                    'Only used in DE'
            );
        }

        $customer = $this->getRandomCustomer(true);
        $this->assertTrue($customer instanceof Yourdelivery_Model_Customer_Company);
        $budget = $customer->getCurrentBudget();
        
        if ( $budget == 0 ){
            $company = $customer->getCompany();
            $budget = new Yourdelivery_Model_Budget();
            $budget->setData(array(
                'companyId' => $company->getId(),
                'name' => 'testbudget',
                'status' => true
            ));
            $budget->save();
            $budget->addBudgetTime(date('w'), date('H:i:s',time() - 3600), date('H:i:s',time() + 3600), 100000);
            $location = new Yourdelivery_Model_Location();
            $city = new Yourdelivery_Model_City($this->getRandomCityId());
            $location->setData(array(
                'companyId' => $company->getId(),
                'street' => 'samson',
                'hausnr' => 'tiffy',
                'cityId' => $city->getId(),
                'plz' => $city->getPlz(),
                'tel' => 123312312,
                'companyName' => 'test'
            ));
            $location->save();
            $company->addBudget($budget, $location);
            $budget->addMember($customer->getId());
        }
        
        $order = new Yourdelivery_Model_Order_Company();
        $order->setup($customer, 'rest');

        //check if everyting is setup correctly
        $this->assertEquals($order->getKind(), 'comp');
        $this->assertEquals($order->getMode(), 'rest');
        $this->assertTrue(strlen($order->getSecret()) == 20);
        $this->assertTrue($order->getCustomer() instanceof Yourdelivery_Model_Customer_Company);

        //set deliver time
        $order->setDeliverTime(__('sofort'), 0);

        //set location from customer
        $locations = $customer->getCompanyLocations();
        $this->assertTrue($locations instanceof SplObjectStorage);
        $this->assertGreaterThan(0, $locations->count());

        $locations->rewind();
        $location = $locations->current();

        $this->assertTrue($location instanceof Yourdelivery_Model_Location);
        $order->setLocation($location);

        //get nearby service
        $this->assertNotNull($location->getPlz());

        $service = $this->getRandomService();

        $this->assertTrue($service instanceof Yourdelivery_Model_Servicetype_Restaurant);
        $service->setCurrentPlz($location->getCity()->getId());
        $order->setService($service);

        //add meals
        $opt_ext = array(
            'extras' => array(),
            'options' => array(),
            'special' => 'testcase'
        );
        $meals = $service->getMeals();
        foreach ($meals as $meal) {
            $m = new Yourdelivery_Model_Meals($meal['id']);
            $sizeId = (integer) $m->getTable()
                            ->getCurrent()
                            ->findDependentRowset('Yourdelivery_Model_DbTable_Meal_SizesNn')
                            ->current()
                    ->sizeId;
            if ($sizeId <= 0) {
                // no size for meal
                continue;
            }
            $this->assertGreaterThan(0, $sizeId);
            if (!$m->isDeleted()) {
                $m->setCurrentSize($sizeId);
                $ret = $order->addMeal($m, $opt_ext, 8);
                $this->assertNotEquals($ret, false);
                break;
            }
        }

        $total = $order->getAbsTotal(false, false, true, false, false, false, false);
        $customer->currentBudgetAmount = null;
        $budget = $customer->getCurrentBudget();
        $this->assertGreaterThan(0, $total);
        $this->assertGreaterThan(0, $budget);

        $withoutBudget = $order->getAbsTotal();
        if ($total < $budget) {
            $check = 0;
        } else {
            $check = $total - $budget;
        }
        $this->assertEquals($withoutBudget, $check);

        $christoph = new Yourdelivery_Model_Customer_Company(1096, 1097);
        list ( $cust, $_budget, $msg ) = $order->addBudget($christoph->getEmail(), 10000000);
        $this->assertNull($cust); //too much
        list ( $cust, $_budget, $msg ) = $order->addBudget($christoph->getEmail(), 1000);
        if (is_object($cust)) {
            $this->assertEquals($order->getMembersBudget(), 1000);
            $this->assertEquals($cust->getId(), $christoph->getId()); //must be the same
        }
        list ( $cust, $_budget, $msg ) = $order->addBudget($christoph->getEmail(), 1000);
        $this->assertNull($cust); //already added

        $code = $this->createDiscount(false, Yourdelivery_Model_Rabatt::RELATIVE, 12);
        $order->setDiscount($code);

        $this->assertTrue($order->finish());
        $orderId = $order->getTable()->getId();
        $orderCompanyGroupTable = Yourdelivery_Model_DbTable_Order_CompanyGroup::findAllByOrderId($orderId);
        #var_dump($orderCompanyGroupTable);

        $amount = 0;
        foreach ($orderCompanyGroupTable as $usedBudget) {
            $amount += $usedBudget['amount'] + $usedBudget['privAmount'];
        }

        $this->assertEquals($order->getAbsTotal(false, false, false, false, false, false, false) - $order->getDiscountAmount(), $amount);

        $order->setStatus(
                Yourdelivery_Model_Order_Abstract::AFFIRMED,        new Yourdelivery_Model_Order_StatusMessage( Yourdelivery_Model_Order_StatusMessage::COMMENT,  'We will be revanged - with wrong spelling')     
        );

        //reset for new calculation
        $customer->currentBudgetAmount = null;
        $this->assertTrue($customer->getCurrentBudget() != $budget);
        $order->setStatus(
                Yourdelivery_Model_Order_Abstract::STORNO,        new Yourdelivery_Model_Order_StatusMessage( Yourdelivery_Model_Order_StatusMessage::COMMENT,  'We will be revanged - with wrong spelling')     
        );

        //reset for new calculation, but this time, the other way
        $customer->currentBudgetAmount['lifetime'] = 0;
        $this->assertEquals($budget, $customer->getCurrentBudget());
    }

}

?>
