<?php

/**
 * @author Andre Ponert <ponert@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses
 */
class FeedbackApiTest extends Yourdelivery_Test{

    /**
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.07.2012
     */
    public function testPostFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_feedback');
        $this->assertController('get_feedback');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }

    /**
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.07.2012
     */
    public function testPostWithMissingParamFail() {
        $json = '{"name":"blub","prename":"foo"}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_feedback');

        $this->assertController('get_feedback');
        $this->assertAction('post');
        $this->assertResponseCode(406);
    }

    /**
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.07.2012
     */
    public function testPostSuccess() {
        $json = '{"name":"Schmidt","prename":"Helmut","email":"helmut.schmidt@email.de","tel":"12345678","comment":"12345678901234567890123456789012345678901234567890"}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_feedback');

        $this->assertController('get_feedback');
        $this->assertAction('post');
        $this->assertResponseCode(200);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.07.2012
     */
    public function testPostSuccessOnlyRequiredParams() {
        $json = '{"prename":"Helmut","email":"helmut.schmidt@email.de","comment":"Fu baaaa -- foo bar --- ole ole"}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_feedback');

        $this->assertController('get_feedback');
        $this->assertAction('post');
        $this->assertResponseCode(200);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.07.2012
     */
    public function testPostFailEmptyComment() {
        $json = '{"prename":"Helmut","email":"helmut.schmidt@email.de","comment":""}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_feedback');

        $this->assertController('get_feedback');
        $this->assertAction('post');
        $this->assertResponseCode(406);
    }


    /**
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.07.2012
     */
    public function testIndexFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_feedback');
        $this->assertController('get_feedback');
        $this->assertAction('index');
        $this->assertResponseCode(403);

    }

    /**
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.07.2012
     */
    public function testGetFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_feedback/foo');
        $this->assertController('get_feedback');
        $this->assertAction('get');
        $this->assertResponseCode(403);
    }




    /**
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.07.2012
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_feedback');
        $this->assertController('get_feedback');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }


    /**
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.07.2012
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_feedback');
        $this->assertController('get_feedback');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

}
