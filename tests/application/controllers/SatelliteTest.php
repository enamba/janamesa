<?php
/**
 * @runTestsInSeparateProcesses 
 */
class SatelliteControllerTest extends Yourdelivery_Test {
    
    /**
     * get random satellite and login in Backend to check the previews
     * 
     * @return integer satelliteId
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.04.2012
     */
    private function _prepare(){
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $satellite = $this->getRandomSatellite();
        return $satellite->getId();
    }
    
    /**
     * Testing Index Action
     * @author Mohammad RAWAQA <rawaqa@lieferando.de>
     * @since 17.04.2012
     */
    public function testIndex(){
        $id = $this->_prepare();
        $this->dispatch('/satellite/index/id/' . $id);
        $this->assertNotRedirect();
        $this->assertResponseCode(200);
        $this->resetRequest();
        $this->resetResponse();
    }
    
    /**
     * Testing About Action
     * @author Mohammad RAWAQA <rawaqa@lieferando.de>
     * @since 17.04.2012
     */
    public function testAbout(){
        $id = $this->_prepare();
        $this->dispatch('/satellite/about/id/' . $id);
        $this->assertNotRedirect(sprintf('satellite #%d',$id));
        $this->assertResponseCode(200);
        $this->resetRequest();
        $this->resetResponse();
    }
        
    /**
     * Testing NotFound Action
     * @author Mohammad RAWAQA <rawaqa@lieferando.de>
     * @since 17.04.2012
     */
    public function testNotFound(){
        $id = $this->_prepare();
        $this->dispatch('/satellite/notfound/id/' . $id);
        $this->assertNotRedirect();
        $this->assertResponseCode(200);
        $this->resetRequest();
        $this->resetResponse();
    }
        
    /**
     * Testing jobs Action
     * @author Mohammad RAWAQA <rawaqa@lieferando.de>
     * @since 17.04.2012
     */
    public function testJobs(){
        $id = $this->_prepare();
        $this->dispatch('/satellite/jobs/id/' . $id);
        $this->assertRedirectTo('/');
        $this->resetRequest();
        $this->resetResponse();
    }
        
    /**
     * Testing opinion Action
     * @author Mohammad RAWAQA <rawaqa@lieferando.de>
     * @since 17.04.2012
     */
    public function testOpinion(){
        $id = $this->_prepare();
        $this->dispatch('/satellite/opinion/id/' . $id);
        $this->assertRedirectTo('/');
        $this->resetRequest();
        $this->resetResponse();
    }
        
    /**
     * Testing menu Action
     * @author Mohammad RAWAQA <rawaqa@lieferando.de>
     * @since 17.04.2012
     */
    public function testMenu(){
        $id = $this->_prepare();
        $this->dispatch('/satellite/menu/id/' . $id);
        $this->assertNotRedirect();
        $this->assertResponseCode(200);
        $this->resetRequest();
        $this->resetResponse();
    }
    
    /**
     * Testing Finish Action
     * @author Mohammad RAWAQA <rawaqa@lieferando.de>
     * @since 17.04.2012
     */
    public function testFinish(){
        $id = $this->_prepare();
        $this->dispatch('/satellite/finish/id/' . $id);
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
    }
}
?>
