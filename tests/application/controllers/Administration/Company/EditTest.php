<?php

/**
 * Description of EditTest
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Administration_Company_EditTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();

        if (HOSTNAME == 'lieferando.at' || HOSTNAME == 'lieferando.ch' || HOSTNAME == 'smakuje.pl') {
            $this->markTestSkipped("in AT, CH, PL we don't have companies yet");
        }

        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $this->getRequest()->setHeader('Authorization', 'Basic ' . base64_encode('gf:thisishell'));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testEditIndex() {

        $company = $this->getRandomCompany();

        $request = $this->getRequest();
        $request->setMethod('GET');

        $this->dispatch('/administration_company_edit/index/companyid/' . $company->getId());

        $response = $this->getResponse();

        $this->assertEquals('200', $response->getHttpResponseCode());
        $this->assertQuery('form[action="' . '/administration_company_edit/index/companyid/' . $company->getId() . '"]', $response->getBody());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testEditIndexPost() {

        $company = $this->getRandomCompany();

        $cid = $company->getId();
        $data = $company->getData();

        if (intval($data['cityId']) == 0) {
            $data['cityId'] = $this->getRandomCityId();
        }

        $name_changed = "Testname" . "-" . time();

        // set empty data fields
        $data['hausnr'] = (strlen($data['hausnr']) == 0) ? '66' : $data['hausnr'];
        $data['street'] = (strlen($data['street']) == 0) ? generateRandomString(12) . "-Strasse" : $data['street'];

        $post = array(
            'name' => $name_changed,
            'street' => $data['street'],
            'hausnr' => $data['hausnr'],
            'industry' => $data['industry'],
            'billDeliver' => $data['billdeliver'],
            'billInterval' => $data['billInterval'],
            'cityId' => $data['cityId'],
            'website' => $data['website'],
            'ktoBlz' => $data['ktoBlz'],
            'ktoName' => $data['ktoName'],
            'ktoNr' => $data['ktoNr'],
            'steuerNr' => $data['steuerNr'],
            'status' => $data['status'],
            'comment' => $data['comment'],
            'website' => $data['website']
        );

        foreach ($post as $key => &$value) {
            if (is_null($value)) {
                $value = false;
            }
        }

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);
        $this->dispatch('/administration_company_edit/index/companyid/' . $company->getId());

        $this->assertRedirectTo('/administration_company_edit/index/companyid/' . $company->getId());

        $changedCompany = new Yourdelivery_Model_Company($cid);

        $this->assertEquals($changedCompany->getName(), $name_changed);

        $response = $this->getResponse();
    }

    public function testEditContacts() {
        $company = $this->getRandomCompany();

        $request = $this->getRequest();
        $request->setMethod('GET');
        $contact = $company->getContact();
        $billingContact = $company->getBillingContact();

        $this->dispatch('/administration_company_edit/contacts/companyid/' . $company->getId());

        $response = $this->getResponse();

        $this->assertEquals('200', $response->getHttpResponseCode());

        if ($contact instanceof Yourdelivery_Model_Contact && $contact->getId()) {
            $this->assertQuery('input[value="' . $contact->getId() . '"]', $response->getBody());
        }

        if ($billingContact instanceof Yourdelivery_Model_Contact && $billingContact->getId()) {
            $this->assertQuery('input[value="' . $billingContact->getId() . '"]', $response->getBody());
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function testEditContactExistingContact() {
        $company = $this->getRandomCompany(true, true, true);
        $contact = $this->getRandomContact();

        $this->assertGreaterThan(0, $contact->getId());
        $this->assertGreaterThan(0, $company->getContact()->getId());

        $post = array(
            'selContactId' => $contact->getId(),
            'contactId' => $company->getContact()->getId()
        );

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);
        $this->dispatch('/administration_company_edit/contact/companyid/' . $company->getId());
        $this->assertRedirectTo('/administration_company_edit/contacts/companyid/' . $company->getId(), print_r($this->getResponse(), true));

        $changedCompany = $this->getCompanyById($company->getId());
        $this->assertEquals($changedCompany['contactId'], $contact->getId());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testEditContactExistingBillingContact() {
        do {
            $company = $this->getRandomCompany();
        } while ($i++ <= MAX_LOOPS && $company->getContact() == null);

        $billingContact = $this->getRandomContact();

        $comp_bill_contact = $company->getBillingContact()->getId();

        if (is_null($comp_bill_contact)) {
            $comp_bill_contact = 0;
        }

        $post = array('contactId' => $comp_bill_contact,
            'selBillingContactId' => $billingContact->getId(),
            'billingContact' => 1
        );

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);
        $this->dispatch('/administration_company_edit/contact/companyid/' . $company->getId());

        $changedCompany = $this->getCompanyById($company->getId());
        $this->assertRedirectTo('/administration_company_edit/contacts/companyid/' . $company->getId(), print_r($this->getResponse(), true));
        $this->assertEquals($changedCompany['billingContactId'], $billingContact->getId());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testEditContactNewContact() {
        $company = $this->getRandomCompany(true);
        $contact = $this->createNewContact();
        $contact['selContactId'] = -1;
        $contact['contactId'] = ($company->getContact()) ? $company->getContact()->getId() : 0;

        $request = $this->getRequest();
        $request->setMethod('POST');

        foreach ($contact as $key => &$value) {
            if (is_null($value)) {
                $value = false;
            }
        }



        $request->setPost($contact);
        $this->dispatch('/administration_company_edit/contact/companyid/' . $company->getId());

        $changedCompany = $this->getCompanyById($company->getId());
        $newContact = new Yourdelivery_Model_Contact($changedCompany['contactId']);

        $this->assertRedirectTo('/administration_company_edit/contacts/companyid/' . $company->getId(), print_r($this->getResponse(), true));
        $this->assertEquals($contact['name'], $newContact->getName());
    }

    /**
     * @author Felix Haferkorn <hafarekorn@lieferando.de>
     * @since 10.05.2012
     */
    public function testEditContactNewBillingContact() {
        $company = $this->getRandomCompany();
        $originBillingContact = $company->getBillingContact();
        
        $name = 'Testcase-Tiffy-'.time().rand(1,99);
        $post = array(
            'saveBillingContact' => true,
            'prename' => 'Testcase-Samson',
            'name'    => $name,
            'street'  => 'Testacse-Straße',
            'hausnr'  => rand(1,99),
            'cityId'  => $this->getRandomCityId(),
            'email'   => 'testemail@'.time().rand(1,99).'.de'
            );

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);
        $this->dispatch('/administration_company_edit/contact/companyid/' . $company->getId());
        $this->assertRedirectTo('/administration_company_edit/contacts/companyid/' . $company->getId(), $this->getResponse()->getBody());

        $db = $this->_getDbAdapter();
        
        $billingContactId = $db->fetchOne('SELECT billingContactId from companys WHERE id = '.$company->getId());
        
        $newContact = new Yourdelivery_Model_Contact($billingContactId);
        $this->assertEquals($name, $newContact->getName());
        $this->assertNotEquals($newContact, $originBillingContact);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testEditContactRemoveBillingContact() {
        $company = $this->getRandomCompany();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('removeBillingContact' => 1));
        $this->dispatch('/administration_company_edit/contact/companyid/' . $company->getId());
        $changedCompany = $this->getCompanyById($company->getId());
        $this->assertRedirectTo('/administration_company_edit/contacts/companyid/' . $company->getId(), print_r($this->getResponse(), true));
        $this->assertEquals($changedCompany['billingContactId'], 0);
    }

    /**
     *
     * @return string
     */
    public function createNewContact() {

        $city = $this->getRandomCityId();

        $contact = array('name' => 'Bill_' . Default_Helper::generateRandomString(4),
            'prename' => 'o Standard',
            'street' => 'Standard Street',
            'hausnr' => Default_Helper::randomCounter(),
            'email' => Default_Helper::generateRandomString(4) . "@test.de",
            'cityId' => $city
        );

        return $contact;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testAssoc() {
        $company = $this->getRandomCompany();

        $request = $this->getRequest();
        $request->setMethod('GET');

        $this->dispatch('/administration_company_edit/assoc/companyid/' . $company->getId());

        $response = $this->getResponse();

        $this->assertEquals('200', $response->getHttpResponseCode());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testAddAndRemoveRestaurant() {

        $restaurant = $this->getRandomService();
        $company = $this->getRandomCompany();

        $post = array('exclusive' => 1,
            'restaurantId' => $restaurant->getId());

        $url = "/administration_company_edit/addrestaurant/companyid/" . $company->getId();

        $this->dispatchPost($url, $post);

        $this->assertRedirect('/administration_company_edit/assoc/companyid/' . $company->getId());

        $assocs = $company->getRestaurantsAssociations();

        $rest_ids = array();

        foreach ($assocs as $assoc) {
            $rest_ids[] = $assoc['restaurantId'];
        }

        $this->assertContains($restaurant->getId(), $rest_ids);

        // remove restaurant

        $url = "/administration_company_edit/removerestaurant/companyid/" . $company->getId() . " /restaurantId/" . $restaurant->getId();

        $this->dispatch($url);

        $this->assertRedirect('/administration_company_edit/assoc/companyid/' . $company->getId());

        $assocs = $company->getRestaurantsAssociations();

        $rest_ids = array();

        foreach ($assocs as $assoc) {
            $rest_ids[] = $assoc['restaurantId'];
        }

        if (is_array($rest_ids) && !empty($rest_ids)) {
            $this->assertFalse(in_array($company, $rest_ids));
        }
    }

}

?>
