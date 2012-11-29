<?php
/**
 * @runTestsInSeparateProcesses
 */
class AutocompleteControllerTest extends Yourdelivery_Test {
    /**
     * Current DB rows to be removed after test
     * @var array
     */
    private $dbRows = array();

    /**
     * Ids of current cache entries to be purged after test
     * @var array
     */
    private $cacheIds = array();

    /**
     * Creates record instances and puts them into DB
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     *
     * @param array $dataSet
     * @return void
     */
    private function createCityRecords($dataSet) {
        $serviceId = 0;
        $cacheIds = array();

        $dbTable = new Yourdelivery_Model_DbTable_City();
        foreach ($dataSet as $data) {
            try {
                $dbRow = $dbTable->createRow(array_merge($data, array(
                    'state' => 'testcaseAutocomplete',
                    'stateId' => rand(1,10),
                )));
                $dbRow->save();
                $this->dbRows[] = $dbRow;
                $cacheIds[] = md5("cityAutocomplete" . $serviceId);
            } catch (Zend_Db_Exception $ex) {
                // already exists - won't be collected to be deleted
            }
        }
        $this->reloadCache($cacheIds);
    }

    /**
     * Search for cites for city verbose records
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     *
     * @param array $dataSet
     * @return void
     */
    private function createCityVerboseRecords($dataSet) {
        $serviceId = 0;
        $cacheIds = array();

        $dbTable = new Yourdelivery_Model_DbTable_City_Verbose();
        foreach ($dataSet as $data) {
            try {
                $matchingCities = Yourdelivery_Model_City::getByCity($data['city']);
                $dbRow = $dbTable->createRow(array_merge($data, array(
                    'cityId' => $matchingCities[0]['id'],
                    'tp_street' => 'street',
                )));
                $dbRow->save();
                $this->dbRows[] = $dbRow;
                $cacheIds[] = md5("streetAutocomplete" . $data['city'] . "s" . $serviceId);
            } catch (Zend_Db_Exception $ex) {
                // already exists - won't be collected to be deleted
            }
        }
        $this->reloadCache($cacheIds);
    }

    /**
     * Reloads cache entries by passed ids
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     *
     * @param array $cacheIds
     * @return void
     */
    private function reloadCache($cacheIds) {
        foreach (array_unique($cacheIds) as $cacheId) {
            Default_Helpers_Cache::remove($cacheId);
            Default_Helpers_Cache::load($cacheId);
            $this->cacheIds[] = $cacheId;
        }
    }

    /**
     * Removes rows and cache entries created for a test
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     *
     * @return void
     */
    public function tearDown() {
        foreach ($this->dbRows as $dbRow) {
            $dbRow->delete();
        }
        foreach ($this->cacheIds as $cacheId) {
            Default_Helpers_Cache::remove($cacheId);
        }
        $this->dbRows = array();
        $this->cacheIds = array();
        parent::tearDown();
    }

    /**
     * Testing city autocomplete mechanism for non existing one
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteCityNotExistingTerm() {
        $this->setUsingCache($cache);
        
        $cityDbTable = new Yourdelivery_Model_DbTable_City();
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('term' => 'strange term which should never exist'));
        $this->dispatch('/autocomplete/city');
        $this->assertResponseCode(404);

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for existing city (standard case, partial city name)
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteCityStandardSearch() {
        $this->setUsingCache($cache);
        
        $this->createCityRecords(array(
            array('plz' => '50-001', 'city' => 'Wrocław'),
            array('plz' => '50-002', 'city' => 'Wrocław'),
            array('plz' => '42-130', 'city' => 'Wręczyca'),
            array('plz' => '98-285', 'city' => 'Wróblew'),
            array('plz' => '38-483', 'city' => 'Wróblik Szlachecki'),
            array('plz' => '87-423', 'city' => 'Wrocki'),
            array('plz' => '97-340', 'city' => 'Wroników'),
            array('plz' => '64-510', 'city' => 'Wronki'),
            array('plz' => '24-333', 'city' => 'Wrzelowiec'),
            array('plz' => '62-300', 'city' => 'Września'),
            array('plz' => '62-313', 'city' => 'Września'),
            array('plz' => '42-263', 'city' => 'Wrzosowa'),
            array('plz' => '78-114', 'city' => 'Wrzosowo'),
            array('plz' => '97-403', 'city' => 'Wrzosy'),
        ));
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('term' => 'Wr'));
        $this->dispatch('/autocomplete/city');
        $this->assertResponseCode(200);

        $rawBody = $this->getResponse()->getBody();
        $this->assertNotEmpty($rawBody);
        $responseArray = Zend_Json::decode($rawBody);
        $this->assertLessThanOrEqual(10, count($responseArray));

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for existing city with multpart name containing diacritic characters
     * Example: wodzislaw sl. -> Wodzisław Śląski
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteCityExtendedSearch() {
        $this->setUsingCache($cache);
        
        $this->createCityRecords(array(
            array('plz' => '44-286', 'city' => 'Wodzisław Śląski'),
        ));
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('term' => 'wodzislaw sl.'));
        $this->dispatch('/autocomplete/city');
        $this->assertResponseCode(200);

        $rawBody = $this->getResponse()->getBody();
        $this->assertContains($this->unicode('Wodzisław Śląski'), $rawBody);
        $responseArray = Zend_Json::decode($rawBody);
        $this->assertEquals(1, count($responseArray));

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for non existing city
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteStreetNotExistingCity() {
        $this->setUsingCache($cache);
        
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('city' => 'Fake city name which does not exist', 'term' => 'whatever'));
        $this->dispatch('/autocomplete/street');
        $this->assertResponseCode(406);

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for street name not existing in passed city
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteStreetNotExistingTerm() {
        $this->setUsingCache($cache);
        
        $this->createCityRecords(array(
            array('plz' => '50-001', 'city' => 'Wrocław'),
        ));
        $this->createCityVerboseRecords(array(
            array('city' => 'Wrocław', 'street' => 'Przyjaźni'),
        ));
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('city' => 'Wrocław', 'term' => 'weird street which does not exist'));
        $this->dispatch('/autocomplete/street');
        $this->assertResponseCode(404);

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for both existing city and street (standard case, partial street name)
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteStreetStandardSearch() {
        $this->setUsingCache($cache);
        
        $this->createCityRecords(array(
            array('plz' => '50-001', 'city' => 'Wrocław'),
        ));
        $this->createCityVerboseRecords(array(
            array('city' => 'Wrocław', 'street' => 'Pochyła'),
            array('city' => 'Wrocław', 'street' => 'Pocztowa'),
            array('city' => 'Wrocław', 'street' => 'Podbiałowa'),
            array('city' => 'Wrocław', 'street' => 'Podchorążych'),
            array('city' => 'Wrocław', 'street' => 'Podolska'),
            array('city' => 'Wrocław', 'street' => 'Podróżnicza'),
            array('city' => 'Wrocław', 'street' => 'Podwale'),
            array('city' => 'Wrocław', 'street' => 'Podwórcowa'),
            array('city' => 'Wrocław', 'street' => 'Pogodna'),
            array('city' => 'Wrocław', 'street' => 'Plac Powstańców Śląskich'),
            array('city' => 'Wrocław', 'street' => 'Polaka Benedykta'),
            array('city' => 'Wrocław', 'street' => 'Polanowicka'),
        ));

        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('city' => 'Wrocław', 'term' => 'po'));
        $this->dispatch('/autocomplete/street');
        $this->assertResponseCode(200);

        $rawBody = $this->getResponse()->getBody();
        $this->assertNotEmpty($rawBody);
        $responseArray = Zend_Json::decode($rawBody);
        $this->assertLessThanOrEqual(10, count($responseArray));

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for street name containing diacritic characters
     * (mixed mode - term may contain 'usual' characters instead)
     * Example: swietokrzyska -> Świętokrzyska
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteStreetMixedDiacriticSearch() {
        $this->setUsingCache($cache);
        
        $this->createCityRecords(array(
            array('plz' => '50-001', 'city' => 'Wrocław'),
        ));
        $this->createCityVerboseRecords(array(
            array('city' => 'Wrocław', 'street' => 'Świętokrzyska'),
        ));
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('city' => 'Wrocław', 'term' => 'swietokrzyska'));
        $this->dispatch('/autocomplete/street');
        $this->assertResponseCode(200);

        $rawBody = $this->getResponse()->getBody();
        $this->assertContains($this->unicode('Świętokrzyska'), $rawBody);
        $responseArray = Zend_Json::decode($rawBody);
        $this->assertEquals(1, count($responseArray));

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for street name containing diacritic characters
     * (stict mode - term also contains the diactritics)
     * Example: łódzka -> Łódzka
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteStreetStrictDiacriticSearch() {
        $this->setUsingCache($cache);
        
        $this->createCityRecords(array(
            array('plz' => '50-001', 'city' => 'Wrocław'),
        ));
        $this->createCityVerboseRecords(array(
            array('city' => 'Wrocław', 'street' => 'Łódzka'),
        ));
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('city' => 'Wrocław', 'term' => 'łódzka'));
        $this->dispatch('/autocomplete/street');
        $this->assertResponseCode(200);

        $rawBody = $this->getResponse()->getBody();
        $this->assertContains($this->unicode('Łódzka'), $rawBody);
        $responseArray = Zend_Json::decode($rawBody);
        $this->assertEquals(1, count($responseArray));

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for street name containing spaces (2 or more words)
     * Example: jana pawla -> Plac Jana Pawła II
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteStreetMultiwordSearch() {
        $this->setUsingCache($cache);
        
        $this->createCityRecords(array(
            array('plz' => '50-001', 'city' => 'Wrocław'),
        ));
        $this->createCityVerboseRecords(array(
            array('city' => 'Wrocław', 'street' => 'Plac Jana Pawła II'),
        ));
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('city' => 'Wrocław', 'term' => 'jana pawla'));
        $this->dispatch('/autocomplete/street');
        $this->assertResponseCode(200);

        $rawBody = $this->getResponse()->getBody();
        $this->assertContains($this->unicode('Plac Jana Pawła II'), $rawBody);
        $responseArray = Zend_Json::decode($rawBody);
        $this->assertEquals(1, count($responseArray));

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for street name containing hyphen (2 or more words joined with hyphen)
     * example: komorowskiego -> Generała Bora-Komorowskiego Tadeusza
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteStreetHyphenSearch() {
        $this->setUsingCache($cache);
        
        $this->createCityRecords(array(
            array('plz' => '50-001', 'city' => 'Wrocław'),
        ));
        $this->createCityVerboseRecords(array(
            array('city' => 'Wrocław', 'street' => 'Generała Bora-Komorowskiego Tadeusza'),
        ));
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('city' => 'Wrocław', 'term' => 'komorowskiego'));
        $this->dispatch('/autocomplete/street');
        $this->assertResponseCode(200);

        $rawBody = $this->getResponse()->getBody();
        $this->assertContains($this->unicode('Generała Bora-Komorowskiego Tadeusza'), $rawBody);
        $responseArray = Zend_Json::decode($rawBody);
        $this->assertEquals(1, count($responseArray));

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for street name prepended by word: ulica (like alee)
     * example: ul. lelewela -> Lelewela Joachima
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteStreetDummySearch() {
        $this->setUsingCache($cache);
        
        if (substr($this->config->domain->base, -3) != '.pl') {
            $this->markTestSkipped('Street dummy test has sense only for *.pl');
        }
        $this->createCityRecords(array(
            array('plz' => '50-001', 'city' => 'Wrocław'),
        ));
        $this->createCityVerboseRecords(array(
            array('city' => 'Wrocław', 'street' => 'Lelewela Joachima'),
        ));
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('city' => 'Wrocław', 'term' => 'ul. lelewela'));
        $this->dispatch('/autocomplete/street');
        $this->assertResponseCode(200);

        $rawBody = $this->getResponse()->getBody();
        $this->assertContains($this->unicode('Lelewela Joachima'), $rawBody);
        $responseArray = Zend_Json::decode($rawBody);
        $this->assertEquals(1, count($responseArray));

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for street name containing a shortcut (like str. -> straße)
     * example: os. dywizjonu 303 -> Osiedle Dywizjonu 303
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteStreetShortcutSearch() {
        $this->setUsingCache($cache);
        
        $this->createCityRecords(array(
            array('plz' => '30-001', 'city' => 'Kraków'),
        ));
        $this->createCityVerboseRecords(array(
            array('city' => 'Kraków', 'street' => 'Osiedle Dywizjonu 303'),
        ));
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('city' => 'Kraków', 'term' => 'os.dywizjonu  303'));
        $this->dispatch('/autocomplete/street');
        $this->assertResponseCode(200);

        $rawBody = $this->getResponse()->getBody();
        $this->assertContains($this->unicode('Osiedle Dywizjonu 303'), $rawBody);
        $responseArray = Zend_Json::decode($rawBody);
        $this->assertEquals(1, count($responseArray));

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Testing street autocomplete mechanism for street name typed in reverse order
     * example: jana matejki -> Aleja Matejki Jana
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testAutocompleteStreetReverseSearch($cache) {
        $this->setUsingCache($cache);
        
        $this->createCityRecords(array(
            array('plz' => '50-001', 'city' => 'Wrocław'),
        ));
        $this->createCityVerboseRecords(array(
            array('city' => 'Wrocław', 'street' => 'Aleja Matejki Jana'),
        ));
        $request = $this->getRequest();
        $request->setMethod('GET');
        $request->setPost(array('city' => 'Wrocław', 'term' => 'jana matejki'));
        $this->dispatch('/autocomplete/street');
        $this->assertResponseCode(200);

        $rawBody = $this->getResponse()->getBody();
        $this->assertContains($this->unicode('Aleja Matejki Jana'), $rawBody);
        $responseArray = Zend_Json::decode($rawBody);
        $this->assertEquals(1, count($responseArray));

        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * Strips quotes from JSONed text (JSONification is used for encoding unicode characters)
     *
     * @param string $text
     * @return string
     */
    protected function unicode($text) {
        return substr(Zend_Json::encode($text), 1, -1);
    }
}
