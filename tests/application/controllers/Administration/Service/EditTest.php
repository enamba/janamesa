<?php

/**
 * Service create/edit in admin backend
 *
 * @author alex
 * @since 23.09.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Administration_Service_EditTest extends Yourdelivery_Test {
    
    protected static $restaurant;
    

    /**
     * @author alex
     * @since 23.09.2011
     */
    public function setUp()
    {
        parent::setUp();
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();     
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
        
    }
    
    /**
     * Test starting page
     * @author alex
     * @since 23.09.2011
     */    
    public function testIndex() {        
        $service = $this->getRandomService();
        
        $request = $this->getRequest();
        $request->setMethod('GET');
        
        $this->dispatch('/administration_service_edit/index/id/'. $service->getId());
        
        $response = $this->getResponse();
                        
        $this->assertEquals('200', $response->getHttpResponseCode());
        $this->assertQuery('form[action="'. '/administration_service_edit/index/id/'. $service->getId() .'"]', $response->getBody());        
    }
    

}
?>
