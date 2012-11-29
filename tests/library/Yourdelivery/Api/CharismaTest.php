<?php

/**
 * Description of CarismaTest
 *
 * @author mlaug
 * 
 * the sandbox can be found under 
 * PRODUCTION: http://webservice.artsolution.de/sandbox/apikey/1679091c5a880faf6fb5e6087eb1b2dc 
 * 
 * DEVELOPMENT: http://webservice.artsolution.de/sandbox/apikey/d26f0afc5779984c9e35ae67b6e0d16b
 * 
 * @runTestsInSeparateProcesses 
 */
class Yourdelivery_Api_CharismaTest extends Yourdelivery_Test {

    /**
     * seconds to wait before getting real state of order in charisma system 
     */
    const API_WAIT_TIMEOUT = 2;
    
    
    /**
     * test charisma grill api from art solutions
     * 
     * @author Matthias Laug 
     * @since 11.07.2012
     */
    public function testOrderX() {

        $s = new Yourdelivery_Model_Servicetype_Restaurant(16631);
        $s->setNotify('charisma');
        $s->save();
        
        $service = new Yourdelivery_Model_Servicetype_Restaurant(16631);
        
        $api = new Yourdelivery_Api_Charisma_Soap();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array(
                            'service' => $service //charisma grill
                        )));
        
        $this->assertEquals(Yourdelivery_Model_Order::AFFIRMED, $order->getState(), Default_Helpers_Log::getLastLog('log', 30));

        // check state in charisma system
        sleep(self::API_WAIT_TIMEOUT);
        $api = new Yourdelivery_Api_Charisma_Soap();
        $state = $api->getStatus($order);
        $this->assertEquals(1, $state, sprintf('failed to get correct state of order #%s %s from charisma api - get state %s', $order->getId(), $order->getNr(), $state));
    }
    
    /**
     * test order with no entry in company, etage, comment
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.08.2012 
     */
    public function testOrderWithNullValues() {

        $s = new Yourdelivery_Model_Servicetype_Restaurant(16631);
        $s->setNotify('charisma');
        $s->save();
        
        $service = new Yourdelivery_Model_Servicetype_Restaurant(16631);
        
        $range = $this->getRandomDeliverRange($service);
        $location = new Yourdelivery_Model_Location();
        $location->setData(
                array(
                    'street' => 'Teststraße',
                    'hausnr' => '54',
                    'cityId' => $range['cityId'],
                    'tel' => rand(1234567,8765432)
                )
                )->save();
        
        $order = new Yourdelivery_Model_Order($this->placeOrder(array(
                            'service' => $service,
                            'location' => $location
                        )));
                
        $this->assertEquals(Yourdelivery_Model_Order::AFFIRMED, $order->getState(), Default_Helpers_Log::getLastLog('log', 30));

        // check state in charisma system
        sleep(self::API_WAIT_TIMEOUT);
        $api = new Yourdelivery_Api_Charisma_Soap();
        $state = $api->getStatus($order);
        $this->assertEquals(1, $state, sprintf('failed to get correct state of order #%s %s from charisma api - get state %s', $order->getId(), $order->getNr(), $state));
    }

    /**
     * test charisma grill api from art solutions
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.08.2012
     */
    public function testOrderWithOptions() {
        // charisma grill
        $s = new Yourdelivery_Model_Servicetype_Restaurant(16631);
        $s->setNotify('charisma');
        $s->save();
        
        $service = new Yourdelivery_Model_Servicetype_Restaurant(16631);

        // get some meals with options / extras
        $meals = array();
        // add 3 meals with options
        for ($i = 0; $i <= 3; $i++) {
            $options = null;
            $meal = null;
            while (is_null($options)) {

                $meal = $this->getRandomMealFromService($service, TRUE);
                $options = $meal->getOptionsFast();
                if (count($options) > 0) {
                    shuffle($options);
                    break;
                }
            }

            $size = $this->getRandomMealSize($meal);
            $meal->setCurrentSize($size->getId());

            $opt_ext = array(
                'options' => $options[0]['id'],
                'extras' => $extras,
                'special' => 'testcase special comment for meal'
            );

            $meals[$i]['meal'] = $meal;
            $meals[$i]['opt_ext'] = $opt_ext;
            $meals[$i]['count'] = 2;
        }

        $order = new Yourdelivery_Model_Order($this->placeOrder(array(
                            'service' => $service,
                            'meals' => $meals
                        )));

        $this->assertEquals(Yourdelivery_Model_Order::AFFIRMED, $order->getState(), Default_Helpers_Log::getLastLog('log', 30));

        // check state in charisma system
        sleep(self::API_WAIT_TIMEOUT);
        $api = new Yourdelivery_Api_Charisma_Soap();
        $state = $api->getStatus($order);
        $this->assertEquals(1, $state, sprintf('failed to get correct state of order #%s %s from charisma api - get state %s', $order->getId(), $order->getNr(), $state));
    }

    /**
     * test charisma grill api from art solutions
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.08.2012
     */
    public function testOrderWithExtras() {
        // charisma grill
        $s = new Yourdelivery_Model_Servicetype_Restaurant(16631);
        $s->setNotify('charisma');
        $s->save();
        
        $service = new Yourdelivery_Model_Servicetype_Restaurant(16631);

        // get some meals with options / extras
        $meals = array();


        // add 3 meals with extras
        for ($i = 4; $i <= 6; $i++) {
            $extras = null;
            $meal = null;
            while (is_null($extras)) {

                $meal = $this->getRandomMealFromService($service, TRUE);
                $size = $this->getRandomMealSize($meal);
                $meal->setCurrentSize($size->getId());

                $extras = $meal->getExtrasFast();
                if (count($extras) > 1) {
                    shuffle($extras);
                }
            }

            $opt_ext = array(
                'options' => $options[0]['id'],
                'extras' => array(array('id' => $extras['']['items'][0]['id'], 'count' => rand(1, 3)), array('id' => $extras['']['items'][1]['id'], 'count' => rand(1, 3))),
                'special' => 'testcase special comment for meal'
            );

            $meals[$i]['meal'] = $meal;
            $meals[$i]['opt_ext'] = $opt_ext;
            $meals[$i]['count'] = 2;
        }

        $order = new Yourdelivery_Model_Order($this->placeOrder(array(
                            'service' => $service,
                            'meals' => $meals
                        )));

        $this->assertEquals(Yourdelivery_Model_Order::AFFIRMED, $order->getState(), Default_Helpers_Log::getLastLog('log', 30));

        // check state in charisma system
        sleep(self::API_WAIT_TIMEOUT);
        $api = new Yourdelivery_Api_Charisma_Soap();
        $state = $api->getStatus($order);
        $this->assertEquals(1, $state, sprintf('failed to get correct state of order #%s %s from charisma api - get state %s', $order->getId(), $order->getNr(), $state));
    }

    /**
     * test charisma grill api from art solutions
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.08.2012
     */
    public function testOrderWithExtrasAndOptions() {
        // charisma grill
        $service = new Yourdelivery_Model_Servicetype_Restaurant(16631);

        // get some meals with options / extras
        $meals = array();
        // add 3 meals with options
        for ($i = 0; $i <= 3; $i++) {
            $options = null;
            $meal = null;
            while (is_null($options)) {

                $meal = $this->getRandomMealFromService($service, TRUE);
                $options = $meal->getOptionsFast();
                if (count($options) > 0) {
                    shuffle($options);
                    break;
                }
            }

            $size = $this->getRandomMealSize($meal);
            $meal->setCurrentSize($size->getId());

            $opt_ext = array(
                'options' => $options[0]['id'],
                'extras' => $extras,
                'special' => 'testcase special comment for meal'
            );

            $meals[$i]['meal'] = $meal;
            $meals[$i]['opt_ext'] = $opt_ext;
            $meals[$i]['count'] = 2;
        }

        // add 3 meals with extras
        for ($i = 4; $i <= 6; $i++) {
            $extras = null;
            $meal = null;
            while (is_null($extras)) {

                $meal = $this->getRandomMealFromService($service, TRUE);
                $size = $this->getRandomMealSize($meal);
                $meal->setCurrentSize($size->getId());

                $extras = $meal->getExtrasFast();
                if (count($extras) > 1) {
                    shuffle($extras);
                }
            }

            $opt_ext = array(
                'options' => $options[0]['id'],
                'extras' => array(array('id' => $extras['']['items'][0]['id'], 'count' => rand(1, 3)), array('id' => $extras['']['items'][1]['id'], 'count' => rand(1, 3))),
                'special' => 'testcase special comment for meal'
            );

            $meals[$i]['meal'] = $meal;
            $meals[$i]['opt_ext'] = $opt_ext;
            $meals[$i]['count'] = 2;
        }

        $order = new Yourdelivery_Model_Order($this->placeOrder(array(
                            'service' => $service,
                            'meals' => $meals
                        )));

        $this->assertEquals(Yourdelivery_Model_Order::AFFIRMED, $order->getState(), Default_Helpers_Log::getLastLog('log', 30));

        // check state in charisma system
        sleep(self::API_WAIT_TIMEOUT);
        $api = new Yourdelivery_Api_Charisma_Soap();
        $state = $api->getStatus($order);
        $this->assertEquals(1, $state, sprintf('failed to get correct state of order #%s %s from charisma api - get state %s', $order->getId(), $order->getNr(), $state));
    }
    
}

?>
