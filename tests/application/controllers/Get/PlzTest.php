<?php

/**
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses 
 */
class PlzApiTest extends Yourdelivery_Test {

    /**
     * @author mlaug
     * @since 03.11.2010
     */
    public function testPlzIndexWithPLZ() {

        $table = new Yourdelivery_Model_DbTable_City();
        $cityRow = $table->fetchRow(null, 'RAND()');
        #echo substr($cityRow['plz'], 0, 3)."\n\n";
        $this->dispatch(sprintf('/get_plz?plz=%s', substr($cityRow['plz'], 0, 3)));
        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $success = $doc->getElementsByTagName("success");
        $this->assertEquals('true', $success->item(0)->nodeValue);
        $this->assertGreaterThan(0, $doc->getElementsByTagName('suggestion')->length);

        for ($i = 0; $i < $doc->getElementsByTagName('plz')->length; $i++) {
            $this->assertGreaterThan(0, strlen(strstr($doc->getElementsByTagName('plz')->item($i)->nodeValue, substr($cityRow['plz'], 0, 3))));
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.03.2012
     */
    public function testPlzIndexWithCoordinates() {
        // get random entry from geocoding table
        $table = new Yourdelivery_Model_DbTable_Geocoding();
        $geo = $table->fetchRow('lat IS NOT NULL and lng IS NOT NULL', 'RAND()');

        $this->dispatch(sprintf('/get_plz?lng=%s&lat=%s', $geo['lng'], $geo['lat']));
        $this->assertResponseCode(200);
        $this->assertController('get_plz');
        $this->assertAction('index');

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $success = $doc->getElementsByTagName("success");
        $this->assertEquals('true', $success->item(0)->nodeValue);
        $this->assertGreaterThan(0, $doc->getElementsByTagName('areas')->length);

        for ($i = 0; $i < $doc->getElementsByTagName('plz')->length; $i++) {
            $this->assertEquals(5, strlen($doc->getElementsByTagName('plz')->item($i)->nodeValue));
            $this->assertGreaterThan(6, strlen($doc->getElementsByTagName('city')->item($i)->nodeValue));
            $this->assertGreaterThan(0, strlen($doc->getElementsByTagName('cityId')->item($i)->nodeValue));
            $this->assertTrue(false !== strstr($doc->getElementsByTagName('city')->item($i)->nodeValue, $doc->getElementsByTagName('plz')->item($i)->nodeValue));
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.04.2012
     */
    public function testPlzIndexWithPlzAndCity() {
        // get random entry from geocoding table
        $db = $this->_getDbAdapter();
        $row = $db->fetchRow('SELECT * from city order by rand() limit 1');

        $this->dispatch(sprintf('/get_plz?plz=%s&city=%s', $row['plz'], $row['city']));
        $this->assertResponseCode(200);

        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());

        $success = $doc->getElementsByTagName("success");
        $this->assertEquals('true', $success->item(0)->nodeValue);
        $this->assertGreaterThan(0, $doc->getElementsByTagName('suggestions')->length);

        $cityIds = array();
        for ($i = 0; $i <= $doc->getElementsByTagName('suggestions')->length; $i++) {
            $cityIds[] = $doc->getElementsByTagName('cityId')->item($i)->nodeValue;
        }

        $this->assertTrue(in_array($row['id'], $cityIds));


        // reset
        $this->resetRequest();
        $this->resetResponse();

        $this->dispatch(sprintf('/get_plz?plz=%s&city=%s', $row['plz'], rand(1234567,7654321)));
        $this->assertResponseCode(200);

        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());

        $success = $doc->getElementsByTagName("success");
        $this->assertEquals('true', $success->item(0)->nodeValue);
        $this->assertEquals(0, $doc->getElementsByTagName('suggestion')->length);
    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.01.2012
     */
    public function testPlzIndexWithoutParams() {
        $this->dispatch('/get_plz');
        $this->assertController('get_plz');
        $this->assertAction('index');
        $this->assertResponseCode(400);
        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertEquals('no parameters provided', $doc->getElementsByTagName('message')->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_plz');
        $this->assertController('get_plz');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testGetFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_plz/foo');
        $this->assertController('get_plz');
        $this->assertAction('get');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPostFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_plz');
        $this->assertController('get_plz');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_plz');
        $this->assertController('get_plz');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }

}
