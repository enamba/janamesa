<?php

/**
 * Description of OverviewTest
 *
 * @author daniel
 *
 * @runTestsInSeparateProcesses 
 */
class PartnerControllerTest extends Yourdelivery_Test {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 07.05.2012 
     */
    public function testContact() {

        $service = $this->getRandomService();

        $request = $this->getRequest();
        $_FILES = array('attachment' => array('name' => '', 'type' => '', 'tmp_name' => '', 'error' => 4, 'size' => 0));
        $request->setMethod('POST');
        $request->setPost(array(
            'subject' => "Anderes",
            'message' => 'PartnerControllerTest::testContact'
        ));


        $session = new Zend_Session_Namespace('Default');
        $session->partnerRestaurantId = $service->getId();

        $this->dispatch('/partner/contact');

        $this->assertRedirectTo("/partner/contact");
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 07.05.2012 
     */
    public function testAccountEmail() {
        
        $service = $this->getRandomService(array('online' => 1));
        $session = new Zend_Session_Namespace('Default');
        $session->partnerRestaurantId = $service->getId();

        $contactBill = $service->getBillingContact();
        $contactRegular = $service->getContact();
        $contact = $contactBill;
        if (is_null($contact)) {
            $contact = $contactRegular;
        }

        $this->assertInstanceof('Yourdelivery_Model_Contact', $contact);

        $email = $contact->getEmail();

        if (empty($email)) {
            $email = "nocontact@test.de";
        } else {
            $email = "test-" . $email;
        }


        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'type' => "email",
            'email' => $email,
            'emailConfirm' => $email
        ));

        $this->dispatch('/partner/account');

        if ($contact === "nocontact@test.de") {
            $this->assertNotRedirect();
        } else {
            $this->assertRedirectTo('/partner/account');
        }

        $partnerData = new Yourdelivery_Model_Servicetype_Partner(null, $service->getId());
        $this->assertEquals($partnerData->getEmail(), $email);
    }

}

?>
