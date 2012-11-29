<?php

/**
 * @runTestsInSeparateProcesses 
 */
class ContactTest extends Yourdelivery_Test {

    /**
     * @author mpantar
     * @since 07.03.2011
     */
    public function testGetFullname() {


        $contact = new Yourdelivery_Model_Contact();

        $contact->setData(array(
            'prename' => 'matej',
            'name' => 'pantar',
        ));

        $this->assertEquals('matej pantar', $contact->getFullname());
        $this->assertTrue('matejpantar' != $contact->getFullname());

        $this->assertNotNull($contact->getCompanys());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.01.2012
     */
    public function testGetServices() {

        $contact = new Yourdelivery_Model_Contact();
        $contact->setData(array(
            'name' => 'pantar',
            'prename' => 'matejt',
            'tel' => 12345678
        ))->save();


        $service = $this->getRandomService();
        $service->setContact($contact);
        $service->save();

        $service2 = $this->getRandomService();
        $service2->setContact($contact);
        $service2->save();

        if ($service->getId() == $service2->getId()) {
            // if services are identical, regt new random service2
            $service2 = $this->getRandomService();
            $service2->setContact($contact);
            $service2->save();
        }

        $this->assertNotEquals($service->getId(), $service2->getId(), sprintf("services should not be identical - get service 1: #%s - service 2: #%s", $service->getId(), $service2->getId()));

        $services = array();
        foreach ($contact->getServices() as $serv) {
            $services[] = $serv['id'];
        }

        $this->assertTrue(in_array($service->getId(), $services));
        $this->assertTrue(in_array($service2->getId(), $services));
    }

    /**
     * @author mpantar
     * @since 07.03.2011
     */
    public function testGetByEmail() {


        $contact = new Yourdelivery_Model_Contact();

        $randString = Default_Helper::generateRandomString(20);

        $contact->setData(array(
            'name' => 'pantar',
            'prename' => 'matej',
            'tel' => 12345678,
            'email' => $randString . '@mail.de'
        ))->save();

        $id = Yourdelivery_Model_Contact::getByEmail($randString . '@mail.de');
        $this->assertEquals($id, $contact->getId());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.01.2012
     */
    public function testGetCity() {
        $cityId = $this->getRandomCityId();
        $contact = new Yourdelivery_Model_Contact();
        $id = $contact->setData(array(
                    'name' => 'Felix',
                    'prename' => 'Haferkorn',
                    'tel' => '015140031777',
                    'cityId' => $cityId
                ))->save();

        $contact = new Yourdelivery_Model_Contact($id);
        $this->assertTrue($contact->isPersistent());
        $this->assertEquals($cityId, $contact->getCity()->getId());
        // call twice should return the same
        $this->assertEquals($cityId, $contact->getCity()->getId());


        $contact = new Yourdelivery_Model_Contact();
        $id = $contact->setData(array(
                    'name' => 'Felix',
                    'prename' => 'Haferkorn',
                    'tel' => '015140031777',
                    'cityId' => 0
                ))->save();

        $contact = new Yourdelivery_Model_Contact($id);
        $this->assertTrue($contact->isPersistent());

        $this->assertNull($contact->getCity());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.01.2012
     */
    public function testGetOrt() {
        $cityId = $this->getRandomCityId();
        $contact = new Yourdelivery_Model_Contact();
        $id = $contact->setData(array(
                    'name' => 'Felix',
                    'prename' => 'Haferkorn',
                    'tel' => '015140031777',
                    'cityId' => $cityId
                ))->save();

        $contact = new Yourdelivery_Model_Contact($id);
        $this->assertTrue($contact->isPersistent());
        $this->assertEquals($cityId, $contact->getOrt()->getId());
        // call twice should return the same
        $this->assertEquals($cityId, $contact->getOrt()->getId());


        $contact = new Yourdelivery_Model_Contact();
        $id = $contact->setData(array(
                    'name' => 'Felix',
                    'prename' => 'Haferkorn',
                    'tel' => '015140031777',
                    'cityId' => rand(12345678, 9876543218)
                ))->save();

        $contact = new Yourdelivery_Model_Contact($id);
        $this->assertTrue($contact->isPersistent());

        $this->assertNull($contact->getOrt());
    }

}
