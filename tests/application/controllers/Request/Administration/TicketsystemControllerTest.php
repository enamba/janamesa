<?php

/**
 * Description of TicketControllerTest
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class TicketsystemControllerTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
    }

    public function testCronjob() {

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('request_administration_ticketsystem/cronjob');

        $response = $this->getResponse();

        $json = json_decode($response->getBody(), true);
        $this->assertTrue(is_array($json));
        $this->assertArrayHasKey('html', $json);
        $this->assertArrayHasKey('stats', $json);
    }

}

?>
