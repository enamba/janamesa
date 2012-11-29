<?php
/**
 * Description of CompanyTest
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 02.08.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Administration_CompanyTest extends Yourdelivery_Test {

    #protected static $companyId;
   # protected static $contactId;
    protected static $db;

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * set up db
     */
    public function setUp() {
        parent::setUp();
        self::$db = Zend_Registry::get('dbAdapter');
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * test company creation without new contact
     */
    public function testCreateCompanyWoContact() {
        $city = $this->getRandomCityId();
        $companyName = "Company_" . Default_Helper::generateRandomString(4);
        $industry = "testingWo";
        $street = "testStreeWo";
        $hausnr = '1';
        $contact = $this->getRandomContact();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'name' => $companyName,
            'industry' => $industry,
            'website' => "www.test.de",
            'street' => $street,
            'hausnr' => $hausnr,
            'cityId' => $city,
            'selContactId' => $contact->getId(),
            'bill_as_contact' => 1
        ));
        $this->dispatch('/administration_company/create');

        $company = $this->getCompanyByName($companyName);

        $contactCity = new Yourdelivery_Model_City($city);

        $this->assertEquals($street, $company['street']);
        $this->assertEquals($hausnr, $company['hausnr']);
        $this->assertEquals($contact->getId(), $company['contactId']);
        $this->assertEquals($contactCity->getPlz(), $company['plz']);

        $this->assertRedirectTo('/administration_company_edit/index/companyid/' . $company['id'], print_r($this->getResponse(), true));
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     */
    public function testCreateCompanyWoContactNoEmail() {
        $city = $this->getRandomCityId();
        $companyName = "Company_" . Default_Helper::generateRandomString(4);
        $industry = "testingWoNoE";
        $street = "testStreeWoNoE";
        $hausnr = '11';
        $contact = $this->getRandomContact(false);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'name' => $companyName,
            'industry' => $industry,
            'website' => "www.test.de",
            'street' => $street,
            'hausnr' => $hausnr,
            'cityId' => $city,
            'selContactId' => $contact->getId(),
            'bill_as_contact' => 1
        ));

        $this->dispatch('/administration_company/create');

        $company = $this->getCompanyByName($companyName);

        $contactCity = new Yourdelivery_Model_City($city);

        $this->assertEquals($street, $company['street']);
        $this->assertEquals($hausnr, $company['hausnr']);
        $this->assertEquals($contactCity->getPlz(), $company['plz']);

        $this->assertRedirectTo('/administration_company_edit/index/companyid/' . $company['id'], print_r($this->getResponse(), true));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * test with new contact
     */
    public function testCreateCompanyWithContact() {
        $city = $this->getRandomCityId();
        $companyName = "Company_" . Default_Helper::generateRandomString(4);
        $industry = "testingWi";
        $street = "testStreetWi";
        $hausnr = '2';

        $contact_name = "Contact_" . Default_Helper::generateRandomString(4);
        $contact_email = Default_Helper::generateRandomString(4) . "@test.de";

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'name' => $companyName,
            'industry' => $industry,
            'website' => "www.test.de",
            'street' => $street,
            'hausnr' => $hausnr,
            'cityId' => $city,
            'contact_name' => $contact_name,
            'contact_prename' => 'Company Test Create',
            'contact_street' => 'Test str',
            'contact_hausnr' => '123',
            'contact_cityId' => $city,
            'contact_email' => $contact_email,
            'selContactId' => -1,
            'bill_as_contact' => 1
        ));
        $this->dispatch('/administration_company/create');

        $company = $this->getCompanyByName($companyName);

        $contact = $this->getContactByName($contact_name);

        //  print_r($company);

        #self::$contactId = $contact['id'];
        #self::$companyId = $company['id'];

        $this->assertEquals($contact['email'], $contact_email);

        $contactCity = new Yourdelivery_Model_City($city);

        $this->assertEquals($street, $company['street']);
        $this->assertEquals($hausnr, $company['hausnr']);
        $this->assertEquals($industry, $company['industry']);
        $this->assertEquals($contactCity->getPlz(), $company['plz']);

        $this->assertRedirectTo('/administration_company_edit/index/companyid/' . $company['id'], print_r($this->getResponse(), true));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * test with new contact and billing contact
     */
    public function testCreateCompanyWithBillingContact() {

        $city = $this->getRandomCityId();
        $companyName = "Company_" . Default_Helper::generateRandomString(4);
        $billName = "Bill_" . Default_Helper::generateRandomString(4);
        $industry = "testingWiBi";
        $street = "testStreetWiBi";
        $hausnr = '3';

        $contact_name = "Contact_" . Default_Helper::generateRandomString(4);
        $contact_email = Default_Helper::generateRandomString(4) . "@test.de";
        $bill_mail = Default_Helper::generateRandomString(4) . "@bill.de";

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'name' => $companyName,
            'industry' => $industry,
            'website' => "www.test.de",
            'street' => $street,
            'hausnr' => $hausnr,
            'cityId' => $city,
            'contact_name' => $contact_name,
            'contact_prename' => 'Company Test Create',
            'contact_street' => 'Test str',
            'contact_hausnr' => '123',
            'contact_cityId' => $city,
            'contact_email' => $contact_email,
            'selContactId' => -1,
            'selBillingContactId' => -1,
            'bill_name' => $billName,
            'bill_prename' => "Tester",
            'bill_email' => $bill_mail
        ));
        $this->dispatch('/administration_company/create');

        $company = $this->getCompanyByName($companyName);

        $bill = $this->getContactByName($billName);

        $this->assertEquals($bill['email'], $bill_mail);

        $contactCity = new Yourdelivery_Model_City($city);

        $this->assertEquals($bill['id'], $company['billingContactId']);
        $this->assertEquals($hausnr, $company['hausnr']);
        $this->assertEquals($industry, $company['industry']);
        $this->assertEquals($contactCity->getPlz(), $company['plz']);

        $this->assertRedirectTo('/administration_company_edit/index/companyid/' . $company['id'], print_r($this->getResponse(), true));


        // delete company

        $this->dispatch('/administration_company/delete/id/' . $company['id']);

        $this->assertRedirectTo('/administration/companys', print_r($this->getResponse(), true));

        $company = $this->getCompanyById($company['id']);

        $this->assertEquals(1, $company['deleted']);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @deprecated ist jetzt in EditTest
     */
    public function testEditCompany() {

        $company = $this->getRandomCompany();

        $cid = $company->getId();
        $data = $company->getData();

        if ( intval($data['cityId']==0) || intval($data['plz']==0) ) {
            $city = new Yourdelivery_Model_City($this->getRandomCityId());
            $data['cityId'] = $city->getId();
            $data['plz'] = $city->getId();
        }

        if(intval($data['hausnr']) == 0) {
            $data['hausnr'] = Default_Helper::generateRandomString(2, "1234567890");
        }


        $name_changed = $data['name'] . " getestet: OK";

        $post = array(
            'name' => $name_changed,
            'street' => $data['street'],
            'hausnr' => $data['hausnr'],
            'industry' => $data['industry'],
            'billDeliver' => $data['billdeliver'],
            'billInterval' => $data['billInterval'],
            'cityId' => $data['cityId'],
            'plz' => $data['plz'],
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

        $this->assertRedirectTo('/administration_company_edit/index/companyid/' . $company->getId(), print_r($this->getResponse(), true));
        $changedCompany = $this->getCompanyById($cid);
        $this->assertEquals($changedCompany['name'], $name_changed);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param string $name
     * @return array
     */
    public function getContactByName($name) {
        $db = self::$db;

        $query = $db->select()
                ->from(array("c" => "contacts"))
                ->where("c.name = ?", $name);
        return $db->fetchRow($query);
    }

    /**
     *
     * @param string $name
     * @return array
     */
    public function getCompanyByName($name) {
        $db = self::$db;

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.name = ?", $name);
        return $db->fetchRow($query);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param int $id
     * @return array
     */
    public function getCompanyById($id) {
        $db = self::$db;

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.id = ?", $id);
        return $db->fetchRow($query);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return Yourdelivery_Model_Contact
     */
    public function getRandomContact($has_email = true) {

        $db = self::$db;

        $select = $db->select()->from(array("c" => "contacts"));

        if ($has_email) {
            $select->where("c.email != 0");
        } else {
            $select->where("c.email = 0");
        }

        $stmt = $db->query($select);
        $all = $stmt->fetchAll();
        shuffle($all);
        return new Yourdelivery_Model_Contact($all[0]['id']);
    }

}

?>