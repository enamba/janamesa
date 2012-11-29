<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @see http://tickets.yourdelivery.de/issues/5230
 */

/**
 * @runTestsInSeparateProcesses 
 */
class OrderCompanyControllerTest extends AbstractOrderController {

    public function testRedirectIfNotEmployee() {
        $this->dispatch('/order_company/start');
        $this->assertRedirectTo('/');
    }

    /**
     * @modified Daniel Hahn <hahn@lieferando.de>
     * @since 07.12.2011
     * @return type
     */
    public function testCompanyRestEnoughBudget() {

        $customer = $this->getRandomCustomerCompany(null, true);
        //$customer = new Yourdelivery_Model_Customer_Company(6379,1235);

        $company = $customer->getCompany();


        $db = Zend_Registry::get('dbAdapter');
        $countLoops = 0;
        do {
            list($post, $order) = $this->_preparePost(null, $customer, $company, 'rest', 'comp', 'bill');
            $countLoops++;
        } while ($countLoops < 10 && (!$order->getService()->getOpening()->isOpen()));
        
        $countLoops >= 10 ? $this->markTestSkipped("please modify this testcase") : null;
        
        $serviceDeliverTime = $order->getService()->getDeliverTime($post['cityId']);
        
        $deliverTime = time() + $serviceDeliverTime;


        $post['deliver-time'] = date('H:i', $deliverTime);
        $post['deliver-time-day'] = date('d.m.Y', $deliverTime);

        $this->assertTrue($order->getService()->getOpening()->isOpen($deliverTime), sprintf('service:#%s delvierTime:%s',$order->getService(),$deliverTime));

        if ($company->getProjectNumbersCount(false) > 0 || $company->getCodeVariant() == 1) {
            $projects = $company->getProjectNumbers(false);
            $projects->rewind();
            $post['pnumber'] = $projects->current()->getNumber();
            $this->assertNotEmpty($post['pnumber']);
        } else {
            $this->assertTrue(in_array($company->getCodeVariant(), array(0, 2, 3)));
        }

        if (!$order->getService()->isOnline($customer, $order->getKind()) && $order->getService()->getIsOnline() == 1) {
            $db->insert('restaurant_company', array('restaurantId' => $order->getService()->getId(), 'companyId' => $company->getId(), 'exclusive' => 0));
        }

        $this->assertTrue($order->getService()->isOnline($customer, $order->getKind()));

        $budget = $customer->getBudget();

        $this->assertNotNull($budget);

        $budgetTimes = $customer->getBudget()->getTable()->getBudgetTimesAll($budget->getId());

        foreach ($budgetTimes as $budgetTime) {
            if ($budgetTime['day'] == date("w", time())) {
                $db->update('company_budgets_times', array('from' => "00:00:00",
                    'until' => "23:59:59",
                    'amount' => $order->getBucketTotal() + 100000
                        ), array('id= ?' => $budgetTime['id']));
                $found = true;
            }
        }

        if (!$found) {
            $customer->getBudget()->addBudgetTime(date("w", $order->getDeliverTime()), date("H:00:00", ($order->getDeliverTime() - 2400)), date("H:00:00", ($order->getDeliverTime() + 2400)), 100000);
        }

        $customerId = $customer->getId();
        $companyId = $company->getId();

        unset($customer);

        $customer = new Yourdelivery_Model_Customer_Company($customerId, $companyId);
        $this->assertGreaterThan($order->getBucketTotal(), $customer->getCurrentBudget());

        $locations = $customer->getCompanyLocations();
        $locations->rewind();
        if ($locations->count() > 0) {
            $location = $locations->current();

            $session = new Zend_Session_Namespace('Default');
            $session->customerId = $customer->getId();

            $this->resetRequest();
            $state = base64_encode($location->getCityId() . '#' . $location->getId() . '#comp#rest');
            $this->request->setCookie('yd-state', $state);
            $this->request->setMethod('POST');
            $this->request->setPost($post);
            $this->dispatch('/order_company/finish');

            $header = $this->getResponse()->getHeaders();

            $body = $this->getResponse()->getBody();

            $this->assertRedirectTo('/order_company/success', $body);

            //append some data to transmit to dependend test
            $post['state'] = $state;
            $post['customerId'] = $customer->getId();
            return $post;
        } else {
            $this->markTestIncomplete('Company has no Locations');
        }
    }

    /**
     * @depends testCompanyRestEnoughBudget
     */
    public function testCompanyRestNotEnoughBudget($post) {
        $session = new Zend_Session_Namespace('Default');
        $session->customerId = $post['customerId'];

        $customer = new Yourdelivery_Model_Customer($post['customerId']);
        $customer = new Yourdelivery_Model_Customer_Company($customer->getId(), $customer->getCompany()->getId());
        $budget = $customer->getBudget();
        $budget->removeMember($customer->getId());
        $this->assertEquals(0, $customer->getCurrentBudget());

        $this->resetRequest();
        $this->request->setCookie('yd-state', $post['state']);
        $this->request->setMethod('POST');
        $this->request->setPost($post);
        $this->dispatch('/order_company/finish');

        $budget->addMember($customer->getId());
        $this->assertRedirectTo('/order_private/start');
    }

    public function testCompanyCater() {
        $this->markTestIncomplete('not implemented yet');
    }

    public function testCompanyGreat() {
        $this->markTestIncomplete('not implemented yet');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 14.09.2011
     *
     * @dataProvider specialCompanies
     */
    public function testFinishCompanySpecials($compShort, $companyId) {
        $company = new Yourdelivery_Model_Company($companyId);
        foreach ($company->getLocations() as $loc) {
            $location = $loc;
            break;
        }

        $this->assertInstanceOf(Yourdelivery_Model_Location, $location);

        $customerCompany = $this->getRandomCustomerCompany($company);
        $this->assertEquals($companyId, $customerCompany->getCompany()->getId());

        $session = new Zend_Session_Namespace('Default');
        $session->customerId = $customerCompany->getId();

        $service = $this->getRandomService();
        $meal = $this->getRandomMealFromService($service);
        $size = $this->getRandomMealSize($meal);

        $range = $this->getRandomDeliverRange($service);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'cityId' => $range['cityId'],
            'serviceId' => $service->getId(),
            'restore' => '0',
            'meal' => array(
                $meal->getHash() => array(
                    'id' => $meal->getId(),
                    'size' => $size->getId(),
                    'special' => '',
                    'count' => 4
                )
            )
        ));

        $request->setCookies(
                array(
                    'yd-state' =>
                    base64_encode(
                            json_encode(
                                    implode('#', array(
                                        'city' => $range['cityId'],
                                        'location' => $location->getId(),
                                        'kind' => 'comp',
                                        'mode' => 'rest')
                                    )
                            )
                    )
                )
        );

        $this->dispatch('/order_company/finish');
        $response = $this->getResponse();
        $this->assertResponseCode(200);

        $this->assertXpath('//input[@id="check_amount_euro"]');
        $this->assertXpath('//input[@id="check_amount_cent"]');
        // button to add budget
        $this->assertXpath('//input[@id="yd-add-budget"]');

        switch ($compShort) {
            case 'houlihan': {

                    $this->assertXpath('//select[@id="check_email"]');
                    // check, that all employees are shown in drop down
                    foreach ($company->getEmployees() as $employee) {
                        $this->assertXpath('//select[@id="check_email"]/option[@value="' . $employee->getEmail() . '"]');
                    }
                    $this->assertXpath('//select[@id="projectcode"]');
                    $this->assertXpath('//select[@id="yd-pn"]');
                    // check, that all projectcodes are shown in drop down
                    foreach ($company->getProjectNumbers(false) as $project) {
                        $this->assertXpath('//select[@id="projectcode"]/option[@value="' . $project->getNumber() . '"]');
                        $this->assertXpath('//select[@id="yd-pn"]/option[@value="' . $project->getNumber() . '"]');
                    }

                    if ($company->isCode()) {
                        $this->assertXpath('//select[@id="yd-pn"][contains(@class, "validate[projectnumber]")]');
                    }
                    break;
                }
            case 'bbdo': {
                    $this->assertXpath('//*[@id="check_email"]');
                    $this->assertXpath('//*[@id="projectcode"]');
                    $this->assertXpath('//*[@id="check_project_addition"]');
                    $this->assertXpath('//*[@id="check_project_addition2"]');

                    $this->assertXpath('//*[@id="check_project_addition_for"][@value="bbdo"]');

                    $this->assertXpath('//*[@id="yd-pn"]');
                    $this->assertXpath('//*[@id="yd-pn-bbdo-1"]');
                    $this->assertXpath('//*[@id="yd-pn-bbdo-2"]');

                    break;
                }
            case 'scholz_bln': {

                    $this->assertXpath('//*[@id="check_email"]');
                    $this->assertXpath('//*[@id="projectcode"]');

                    $this->assertXpath('//select[@id="check_project_addition"]/option[@value="NWB"]');
                    $this->assertXpath('//select[@id="check_project_addition"]/option[@value="WB"]');

                    $this->assertXpath('//select[@name="projectAddition"]/option[@value="NWB"]');
                    $this->assertXpath('//select[@name="projectAddition"]/option[@value="WB"]');

                    break;
                }
            case 'scholz_hh': {
                    $this->assertXpath('//select[@id="check_email"]');
                    // check, that all employees are shown in drop down
                    foreach ($company->getEmployees() as $employee) {
                        $this->assertXpath('//select[@id="check_email"]/option[@value="' . $employee->getEmail() . '"]');
                    }

                    $this->assertXpath('//*[@id="projectcode"][contains(@class, "scholz-hh")]');
                    $this->assertXpath('//*[@id="check_project_addition"]');

                    $this->assertXpath('//*[@id="yd-pn"][contains(@class, "validate[custom[scholzhh]]")]');
                    $this->assertXpath('//*[@id="yd-pn-2"]');
                    $this->assertXpath('//*[@name="projectAddition"]');

                    break;
                }
            case 'scholz_group': {
                    $this->assertXpath('//select[@id="check_email"]');
                    // check, that all employees are shown in drop down
                    foreach ($company->getEmployees() as $employee) {
                        $this->assertXpath('//select[@id="check_email"]/option[@value="' . $employee->getEmail() . '"]');
                    }

                    $this->assertXpath('//*[@id="projectcode"][contains(@class, "scholz-hh")]');
                    $this->assertXpath('//*[@id="check_project_addition"]');

                    $this->assertXpath('//*[@id="yd-pn"][contains(@class, "validate[custom[scholzhh]]")]');
                    $this->assertXpath('//*[@id="yd-pn-2"]');
                    $this->assertXpath('//*[@name="projectAddition"]');

                    break;
                }
            case 'scholz_strategy': {
                    $this->assertXpath('//select[@id="check_email"]');
                    // check, that all employees are shown in drop down
                    foreach ($company->getEmployees() as $employee) {
                        $this->assertXpath('//select[@id="check_email"]/option[@value="' . $employee->getEmail() . '"]');
                    }

                    $this->assertXpath('//*[@id="projectcode"][contains(@class, "scholz-hh")]');
                    $this->assertXpath('//*[@id="check_project_addition"]');

                    $this->assertXpath('//*[@id="yd-pn"][contains(@class, "validate[custom[scholzhh]]")]');
                    $this->assertXpath('//*[@id="yd-pn-2"][contains(@class, "validate[custom[scholzhhtext]]")]');
                    $this->assertXpath('//*[@name="projectAddition"]');

                    break;
                }
            default:
                $this->assertTrue('This Test failed');
                break;
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 14.09.2011
     *
     * @return array ('companyShortName', companyId)
     */
    public function specialCompanies() {
        return array(
            array('houlihan', 1218),
            array('bbdo', 1235),
            array('scholz_bln', 1260),
            array('scholz_hh', 1673),
            array('scholz_group', 1674),
            array('scholz_strategy', 1675)
        );
    }

}
