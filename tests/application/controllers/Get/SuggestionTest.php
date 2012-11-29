<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class SuggestionApiTest extends Yourdelivery_Test{

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.02.2012
     */
    public function testPostFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_suggestion');
        $this->assertController('get_suggestion');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.02.2012
     */
    public function testPostWithMissingParamFail() {
        $json = '{"name":"blub","service":"Restaurant"}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_suggestion');
        
        $this->assertController('get_suggestion');
        $this->assertAction('post');
        $this->assertResponseCode(406);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.02.2012
     */
    public function testPostSuccess() {
        $json = '{"name":"blub","service":"Restaurant", "ort":"Berlin"}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_suggestion');
        
        $this->assertController('get_suggestion');
        $this->assertAction('post');
        $this->assertResponseCode(200);
    }
    
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.02.2012
     */
    public function testIndexFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_suggestion');
        $this->assertController('get_suggestion');
        $this->assertAction('index');
        $this->assertResponseCode(403);

    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.02.2012
     */
    public function testGetFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_suggestion/foo');
        $this->assertController('get_suggestion');
        $this->assertAction('get');
        $this->assertResponseCode(403);
    }




    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.02.2012
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_suggestion');
        $this->assertController('get_suggestion');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.02.2012
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_suggestion');
        $this->assertController('get_suggestion');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

}
