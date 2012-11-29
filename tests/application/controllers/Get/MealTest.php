<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class MealApiTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.08.2011
     */
    public function testApiMealByIdWithoutSizeIdShouldFail() {

        $meal = $this->getRandomMealFromService($this->getRandomService());

        $this->dispatch('/get_meal/id/' . $meal->getId());
        $this->assertController('get_meal');
        $this->assertAction('get');
        $response = $this->getResponse();
        $this->assertResponseCode(404);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testGetWithoutMealIdFail() {
        $this->dispatch('/get_meal/id/');
        $this->assertController('get_meal');
        $this->assertAction('get');
        $response = $this->getResponse();
        $this->assertResponseCode(404);
        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $this->assertEquals('No mealId provided', $doc->getElementsByTagName("message")->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testGetWithoutInvalidMealIdFail() {
        $this->dispatch('/get_meal/id/' . time());
        $this->assertController('get_meal');
        $this->assertAction('get');
        $response = $this->getResponse();
        $this->assertResponseCode(404);
        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $this->assertEquals('meal not found', $doc->getElementsByTagName("message")->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.08.2011
     */
    public function testApiMealByIdWithSizeIdWithOptions() {

        // get random meal

        $service = $this->getRandomService();
        for ($i = 0; $i < MAX_LOOPS; $i++) {
            try {
                $meal = $this->getRandomMealFromService($service, true);
        
                if ($meal->hasOptions()) {
                    break;
                }
            } catch (Exception $e) {

                $service = $this->getRandomService();
                continue;
            }
        }
        $this->assertTrue($meal->hasOptions());

        $size = $this->getRandomMealSize($meal);

        $this->dispatch('/get_meal/id/' . $meal->getId() . '/size/' . $size->getId());
        $this->assertController('get_meal');
        $this->assertAction('get');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $this->assertEquals('true', $doc->getElementsByTagName("success")->item(0)->nodeValue);
        $this->assertNotNull($doc->getElementsByTagName("hasspecials")->item(0)->nodeValue);

        $this->assertGreaterThan(0, $doc->getElementsByTagName("id")->item(0)->nodeValue);
        $this->assertGreaterThanOrEqual(1, $doc->getElementsByTagName("size")->length);
        $this->assertGreaterThan(0, $doc->getElementsByTagName("name")->length);
        $this->assertEquals($doc->getElementsByTagName("hasspecials")->length, 1);
        $this->assertEquals($doc->getElementsByTagName('category')->length, 1);
        $this->assertEquals($doc->getElementsByTagName('image')->length, 1);
        $this->assertGreaterThan(0, $doc->getElementsByTagName('cost')->length);

        $config = Zend_Registry::get('configuration');
        $this->assertTrue(in_array($doc->getElementsByTagName('tax')->item(0)->nodeValue, $config->tax->types->toArray()));
        $this->assertTrue(in_array((integer) $doc->getElementsByTagName("hasspecials")->item(0)->nodeValue, array(0, 1)));
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 09.06.2012
     */
    public function testApiMealByIdWithSizeIdWithExtras() {

        // get random meal

        $service = $this->getRandomService();
        for ($i = 0; $i < MAX_LOOPS; $i++) {
            try {
                $meal = $this->getRandomMealFromServiceWithExtras($service);
        
                if ($meal->hasExtras()) {
                    break;
                }
            } catch (Exception $e) {

                $service = $this->getRandomService();
                continue;
            }
        }
        $this->assertTrue($meal->hasExtras());

        $this->dispatch('/get_meal/id/' . $meal->getId() . '/size/' . $meal->getCurrentSize());
        $this->assertController('get_meal');
        $this->assertAction('get');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $this->assertEquals('true', $doc->getElementsByTagName("success")->item(0)->nodeValue);
        $this->assertNotNull($doc->getElementsByTagName("hasspecials")->item(0)->nodeValue);

        $this->assertGreaterThan(0, $doc->getElementsByTagName("id")->item(0)->nodeValue);
        $this->assertGreaterThanOrEqual(1, $doc->getElementsByTagName("size")->length);
        $this->assertGreaterThan(0, $doc->getElementsByTagName("name")->length);
        $this->assertEquals($doc->getElementsByTagName("hasspecials")->length, 1);
        $this->assertEquals($doc->getElementsByTagName('category')->length, 1);
        $this->assertEquals($doc->getElementsByTagName('image')->length, 1);
        $this->assertGreaterThan(0, $doc->getElementsByTagName('cost')->length);
        
        $this->assertEquals(1, $doc->getElementsByTagName('extras')->length);
        
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()
                        ->from(array('mer' => 'meal_extras_relations'))
                        ->join(array('me' => 'meal_extras'), 'me.id = mer.extraId')
                        ->where('mer.sizeId = ?', $meal->getCurrentSize())
                        ->where(sprintf('mer.mealId = %s or mer.categoryId = %s', $meal->getId(), $meal->getCategoryId()))
                        ->order('RAND()');
        $mealExtras = $db->fetchRow($select);
        $this->assertTrue($meal->hasExtras());
        $this->assertContains($mealExtras['name'], $doc->getElementsByTagName('extras')->item(0)->nodeValue, $meal->getId().'|'.$meal->getCategoryId().'|'.$meal->getCurrentSize());
        $config = Zend_Registry::get('configuration');
        $this->assertTrue(in_array($doc->getElementsByTagName('tax')->item(0)->nodeValue, $config->tax->types->toArray()));
        $this->assertTrue(in_array((integer) $doc->getElementsByTagName("hasspecials")->item(0)->nodeValue, array(0, 1)));
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.01.2012
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_meal');
        $this->assertController('get_meal');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.01.2012
     */
    public function testPostFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_meal');
        $this->assertController('get_meal');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.01.2012
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_meal');
        $this->assertController('get_meal');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.01.2012
     */
    public function testIndexFail() {
        $request = $this->getRequest();
        $this->dispatch('/get_meal?foo=bar');
        $this->assertController('get_meal');
        $this->assertAction('index');
        $this->assertResponseCode(403);
    }

}
