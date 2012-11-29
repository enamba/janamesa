<?php
/**
 * @author vpriem
 * @since 02.11.2010
 */
/**
 * @runTestsInSeparateProcesses
 */
class DbTableSatelliteTest extends Yourdelivery_Test {

    /**
     * @author vpriem, Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 02.11.2010 , 17,04,2012
     */
    public function testFindByDomain() {

        $dbTable = new Yourdelivery_Model_DbTable_Satellite();
        $allSatellites = $dbTable->fetchAll('disabled = 0')->toArray();
        shuffle($allSatellites);

        $iddomain = array();
        $uniqueSatellites = array();
        for ($i = 0; $i < count($allSatellites); $i++) {
            $iddomain[$allSatellites[$i]['id']] = $allSatellites[$i]['domain'];
        }
        $uniqueSatellites = array_unique($iddomain);
        $randId = array_rand($uniqueSatellites);
        $domain = $uniqueSatellites[$randId];

        $satellite = $dbTable->find($randId);
        $this->assertInstanceof(Zend_Db_Table_Rowset_Abstract, $satellite);

        $satellite = $dbTable->findByDomain($domain);
        $this
                ->assertInstanceof(Default_Model_DbTable_Row, $satellite, 'could not find activated satellite by domain '
                        . $allSatellites[0]['domain']);
        $this->assertEquals($satellite['id'], $randId);

        $satellite->disabled = 1;
        $satellite = $dbTable->findByDomain($domain);
        $this->assertInstanceof(Default_Model_DbTable_Row, $satellite);
        $this->assertEquals($satellite['id'], $randId);

        $satellite->disabled = 0;
        $satellite = $dbTable->findByDomain($domain);
        $this->assertInstanceof(Default_Model_DbTable_Row, $satellite);
        $this->assertEquals($satellite['id'], $randId);
    }

    /**
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 17,04,2012
     */
    public function testFindAllByDomain() {
        $dbTable = new Yourdelivery_Model_DbTable_Satellite();
        $allSatellites = $dbTable->fetchAll('disabled = 0')->toArray();
        shuffle($allSatellites);

        $satellite = $dbTable->find($allSatellites[0]['id']);
        $this->assertInstanceof(Zend_Db_Table_Rowset_Abstract, $satellite);

        $satellite = $dbTable->findAllByDomain($allSatellites[0]['domain']);
        $domainsats = array();
        for ($i = 0; $i < count($satellite); $i++) {
            $domainsats[] = $satellite[$i]['id'];
        }

        $this
                ->assertInstanceof(Default_Model_DbTable_Rowset, $satellite, 'could not find activated satellite by domain '
                        . $allSatellites[0]['domain']);
        $satexist = in_array($allSatellites[0]['id'], $domainsats) ? true
                : false;
        $this->assertTrue($satexist);

        $satellite->disabled = 1;
        $satellite = $dbTable->findAllByDomain($allSatellites[0]['domain']);
        $this->assertInstanceof(Default_Model_DbTable_Rowset, $satellite);
        for ($i = 0; $i < count($satellite); $i++) {
            $domainsats[] = $satellite[$i]['id'];
        }

        $satexist = in_array($allSatellites[0]['id'], $domainsats) ? true
                : false;
        $this->assertTrue($satexist);

        $satellite->disabled = 0;
        $satellite = $dbTable->findAllByDomain($allSatellites[0]['domain']);
        $this->assertInstanceof(Default_Model_DbTable_Rowset, $satellite);
        for ($i = 0; $i < count($satellite); $i++) {
            $domainsats[] = $satellite[$i]['id'];
        }

        $satexist = in_array($allSatellites[0]['id'], $domainsats) ? true
                : false;
        $this->assertTrue($satexist);
    }

    /**
     * @author vpriem
     * @since 02.11.2010
     */
    public function testFindByRestaurantId() {

        $dbTable = new Yourdelivery_Model_DbTable_Satellite();
        $allSatellites = $dbTable->fetchAll('restaurantId > 0')->toArray();
        shuffle($allSatellites);

        $satellite = $dbTable->find($allSatellites[0]['id']);
        $this->assertTrue($satellite instanceof Zend_Db_Table_Rowset_Abstract);
        $this->assertEquals($satellite[0]['id'], $allSatellites[0]['id']);

        // TODO: a restaurant kann have more than one satellite
        // but it's not actually correct, we have to fix it with domain aliases
        //        $satellite = $dbTable->findByRestaurantId($allSatellites[0]['restaurantId']);
        //        $this->assertTrue($satellite instanceof Zend_Db_Table_Row);
        //        $this->assertEquals($satellite['id'], $allSatellites[0]['id']);

    }

}
