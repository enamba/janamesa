<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 01.02.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class OrderCourierControllerTest extends AbstractOrderController {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 23.02.2012
     */
    public function setUp() {

        parent::setUp();

        if (HOSTNAME != 'lieferando.de') {
            $this->markTestSkipped("DE only");
        }
    }

    /**
     * TODO: put this into Yourdelivery_Test::getRadomService wehen refactored
     * @author Vincent Priem <priem@lieferando.de>
     * @since 01.02.2012
     * @return Yourdelivery_Model_Servicetype_Restaurant
     */
    protected function _getRandomServiceWithCourier() {

        $params = func_get_args();
        $params = array_merge(array(
            'api' => "",
                ), $params);

        $db = Zend_Registry::get('dbAdapter');
        $deadLockPreventer = 0;
        do {
            $select = $db->select()
                    ->from(array('r' => 'restaurants'), array('r.id'))
                    ->join(array("rp" => "restaurant_plz"), "r.id = rp.restaurantId", array())
                    ->join(array('rc' => 'courier_restaurant'), "r.id = rc.restaurantId", array())
                    ->join(array('c' => 'courier'), "c.id = rc.courierId", array())
                    ->order('RAND()')
                    ->limit(1);

            if (!empty($params['api'])) {
                $select->where("c.api = ?", $params['api']);
            }

            $restaurantId = $db->fetchOne($select);
            $this->assertGreaterThan(0, $restaurantId);

            $queryMeals = $db->select()
                    ->from(array("m" => "meals"), array('m.id'))
                    ->join(array('mc' => 'meal_categories'), "m.categoryId= mc.id", array())
                    ->where("m.restaurantId = ?", $restaurantId);

            $mealsCount = count($db->fetchAll($queryMeals));

            $deadLockPreventer++;
            
        } while (($mealsCount == 0) && ($deadLockPreventer < 30));

        $service = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        $service->setFranchiseTypeId(3);
        $service->save();
        $courier = $service->getCourier();
        $this->assertTrue($courier instanceof Yourdelivery_Model_Courier);

        if (!empty($params['api'])) {
            $this->assertEquals($courier->getApi(), $params['api']);
        }

        return $service;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 01.02.2012
     */
    public function testWithBar() {

        $service = $this->_getRandomServiceWithCourier();
        $orderId = $this->placeOrder(array(
            'payment' => "credit",
            'service' => $service,
                ));

        list($post, $order) = $this->_preparePost($orderId, null, null, 'rest', 'priv', 'bar');
        $customer = $order->getCustomer();
        $post['email'] = $customer->getEmail();
        $post['hausnr'] = "3";
        $post['name'] = $customer->getName();
        $post['prename'] = $customer->getPrename();
        $post['street'] = "teststr.";
        $post['telefon'] = "1234567890";

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($post);
        $this->dispatch('/order_private/finish');
        $this->assertAction('finish');
        $this->assertNotRedirectTo('/order_private/success');
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 01.02.2012
     */
    public function testPromptWithWrongStreet() {

        $service = $this->_getRandomServiceWithCourier(array('api' => "prompt"));
        $orderId = $this->placeOrder(array(
            'payment' => "credit",
            'service' => $service,
                )); // will generate a wrong street

        list($post, $order) = $this->_preparePost($orderId, null, null, 'rest', 'priv', 'paypal');
        $customer = $order->getCustomer();
        $post['email'] = $customer->getEmail();
        $post['hausnr'] = "3";
        $post['name'] = $customer->getName();
        $post['prename'] = $customer->getPrename();
        $post['street'] = "teststr.";
        $post['telefon'] = "1234567890";

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($post);
        $this->dispatch('/order_private/finish');
        $this->assertAction('finish');
        $this->assertNotRedirectTo('/order_private/success');
    }

}
