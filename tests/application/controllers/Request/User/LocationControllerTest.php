<?php

/**
 * Description of LocationControllerTest
 *
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses 
 */
class LocationControllerTest extends Yourdelivery_Test {

    /**
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 24.05.2012
     * @return city_verbose row 
     */
    public function getRandomCityVerboseId() {
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select('RAND()')->from('city_verbose')->where('number' != '')->limit(1);
        return $db->fetchRow($query);
    }

    /**
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 24.05.2012
     * @return customer row
     */
    public function getMyCustomer() {
        $customer = $this->getRandomCustomer(false, true, false);
        $customerId = $customer->getId();

        $db = Zend_Registry::get('dbAdapter');
        $sql = $db->quoteInto('UPDATE customers SET password=md5(\'samsontiffy\') WHERE id=?', $customerId);
        $db->query($sql);

        $this->login($customer->getEmail(), 'samsontiffy');
        return $customer;
    }

    /**
     * trying to create a new address without a cityId
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 25.05.2012
     */
    public function testCreateActionNoCityId() {
        $config = Zend_Registry::get('configuration');
        if (!in_array($config->domain->base, array('pyszne.pl', 'smakuje.pl'))) {
            $this->markTestSkipped('only in PL');
        }

        $customer = $this->getMyCustomer();

        $request = $this->getRequest();
        $request->setMethod('POST');

        $verbose = $this->getRandomCityVerboseId();

        $city = new Yourdelivery_Model_City($verbose['cityId']);
        $city_data = $city->getData();

        $post = array(
            'city' => $verbose['city'],
            'street' => $verbose['street'],
            'hausnr' => $verbose['number'],
            'plz' => $city_data['plz'],
            'tel' => '523258956'
        );
        $request->setPost($post);

        $this->dispatch('/request_user_location/create');
        $customerId = $customer->getId();

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('locations')->where('customerId=' . $customerId);
        $res = $db->fetchAll($query);
        $count = count($res) - 1;

        $this->assertEquals($verbose['cityId'], $res[$count]['cityId']);
        $this->assertEquals($city_data['plz'], $res[$count]['plz']);
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * create a new user address
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 24.05.2012
     */
    public function testCreateAction() {
        $config = Zend_Registry::get('configuration');
        if (!in_array($config->domain->base, array('pyszne.pl', 'smakuje.pl'))) {
            $this->markTestSkipped('only in PL');
        }
        
        $customer = $this->getMyCustomer();

        $request = $this->getRequest();
        $request->setMethod('POST');

        $verbose = $this->getRandomCityVerboseId();

        $city = new Yourdelivery_Model_City($verbose['cityId']);
        $city_data = $city->getData();

        $request->setPost(array(
            'city' => $verbose['city'],
            'street' => $verbose['street'],
            'hausnr' => $verbose['number'],
            'cityId' => $verbose['cityId'],
            'plz' => $city_data['plz'],
            'tel' => '523258956'
                )
        );

        $this->dispatch('/request_user_location/create');
        $customerId = $customer->getId();

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('locations')->where('customerId=' . $customerId);
        $res = $db->fetchAll($query);
        $count = count($res) - 1;

        $this->assertEquals($verbose['cityId'], $res[$count]['cityId']);
        $this->assertEquals($city_data['plz'], $res[$count]['plz']);
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * trying to create a new user address with invalid post data
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 25.05.2012
     */
    public function testCreateActionInvalidPost() {
        $config = Zend_Registry::get('configuration');
        if (!in_array($config->domain->base, array('pyszne.pl', 'smakuje.pl'))) {
            $this->markTestSkipped('only in PL');
        }
        
        $customer = $this->getMyCustomer();

        $request = $this->getRequest();
        $request->setMethod('POST');

        $verbose = $this->getRandomCityVerboseId();

        $request->setPost(array('city' => $verbose['city'], 'tel' => '523258956'));

        $this->dispatch('/request_user_location/create');
        $this->assertResponseCode(406);
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * edit an address 
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 24.05.2012
     */
    public function testEditAction() {
        $config = Zend_Registry::get('configuration');
        if (!in_array($config->domain->base, array('pyszne.pl', 'smakuje.pl'))) {
            $this->markTestSkipped('only in PL');
        }
        
        $customer = $this->getMyCustomer();
        $customerId = $customer->getId();

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('locations')->where('customerId=' . $customerId);
        $result = $db->fetchAll($query);
        $locId = $result[0]['id'];
        $request = $this->getRequest();
        $request->setMethod('POST');

        $verbose = $this->getRandomCityVerboseId();

        $city = new Yourdelivery_Model_City($verbose['cityId']);
        $city_data = $city->getData();

        $post = array(
            'id' => $locId,
            'city' => $verbose['city'],
            'street' => $verbose['street'],
            'hausnr' => $verbose['number'],
            'cityId' => $verbose['cityId'],
            'plz' => $city_data['plz'],
            'tel' => '1010101010'
        );
        $request->setPost($post);

        $this->dispatch('/request_user_location/edit');

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('locations')->where('id=' . $locId);
        $res = $db->fetchRow($query);

        $this->assertEquals($post['tel'], $res['tel']);
        $this->assertEquals($post['plz'], $res['plz']);
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * edit an address withinvalid posted data
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 25.05.2012
     */
    public function testEditActionInvalidPost() {
        $config = Zend_Registry::get('configuration');
        if (!in_array($config->domain->base, array('pyszne.pl', 'smakuje.pl'))) {
            $this->markTestSkipped('only in PL');
        }
        
        $customer = $this->getMyCustomer();

        $request = $this->getRequest();
        $request->setMethod('POST');

        $verbose = $this->getRandomCityVerboseId();

        $request->setPost(array('city1' => $verbose['city']));

        $this->dispatch('/request_user_location/edit');
        $this->assertResponseCode(406);
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * mark an address as primary address 
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 25.05.2012
     */
    public function testPrimaryAction() {
        $customer = $this->getMyCustomer();
        $customerId = $customer->getId();

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('locations')->where('customerId=' . $customerId);
        $result = $db->fetchAll($query);

        foreach ($result as $loc) {
            if ($loc['primary'] == 1) {
                $sql = $db->quoteInto('UPDATE locations SET `primary`= 0 WHERE id= ?', (integer) $loc['id']);
                $db->query($sql);
            }
        }

        $this->dispatch('/request_user_location/primary/id/' . $result[0]['id'] . '/toggle/1');

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('locations')->where('id=' . $result[0]['id']);

        $res = $db->fetchRow($query);

        $this->assertEquals($res['primary'], 1);
        $this->resetRequest();
        $this->resetResponse();
    }

    /**
     * get an address 
     */
    public function testGetAction() {
        $customer = $this->getMyCustomer();
        $this->dispatch('/request_user_location/get');
        $this->assertRedirect();
        $this->assertResponseCode(302);
        $this->resetRequest();
        $this->resetResponse();
    }

}

?>
