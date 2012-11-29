<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 15.11.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class ImageApiTest extends Yourdelivery_Test {

    /**
     * if this test fails, be sure that
     *       <IP OF YOUR LOCAL WEBSERVER> www.lieferando.local 
     * is in your vhost and /etc/hosts
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.11.2011
     * 
     * @todo this test depends on the URL of the application, this is not nice. Refactor it sometime!!
     */
    public function testPostSuccess() {
        $this->markTestSkipped('refactor this!');
        
        
        $this->assertFileExists(APPLICATION_PATH_TESTING . '/../data/samson.jpg', 'Could not find test-image');
        $customer = $this->getRandomCustomer();
        rmdir(APPLICATION_PATH . '/../storage/customer/' . $customer->getId());
        $this->assertFalse(is_dir(APPLICATION_PATH . '/../storage/customer/' . $customer->getId()));
        
        $request_url = 'http://www.lieferando.local/get_image';
        $post_params['parameters'] = '{"access":"' . $customer->getSalt() . '"}';
        $post_params['img'] = '@' . APPLICATION_PATH_TESTING . '/../data/samson.jpg';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $this->assertFileExists(APPLICATION_PATH . '/../storage/customer/' . $customer->getId() . '/' . $customer->getProfileImage(true));
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.01.2012
     */
    public function testPostFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_image');
        $this->assertController('get_image');
        $this->assertAction('post');
        $this->assertResponseCode(405);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.01.2012
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_image');
        $this->assertController('get_image');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.01.2012
     */
    public function testGetFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_image/foo');
        $this->assertController('get_image');
        $this->assertAction('get');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.01.2012
     */
    public function testIndexFail() {
        $request = $this->getRequest();
        $request->setMethod('Get');
        $this->dispatch('/get_image?blub=bla');
        $this->assertController('get_image');
        $this->assertAction('index');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.01.2012
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $request->setMethod('Put');
        $this->dispatch('/get_image');
        $this->assertController('get_image');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }

}
