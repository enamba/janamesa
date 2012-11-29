<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Varnish
 *
 * @author mlaug
 */
class VarnishTest extends Yourdelivery_Test {

    /**
     * @author Matthias Laug 
     * @since 21.05.2012 
     * @return string 
     */
    private function getPrepend() {
        $config = Zend_Registry::get('configuration');
        $prepend .= 'http://staging.' . $config->domain->base;
        return $prepend;
    }

    /**
     * if no url is provided we use index page
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012 
     */
    public function testAddUrlEmpty() {
        $purger = new Yourdelivery_Api_Varnish_Purger();
        $purger->addUrl(); //will result in index page
        $url = array_pop($purger->getUrlList());
        $this->assertEquals($url, $this->getPrepend() . '/');
    }
    
    /**
     * check http prepending
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 19.06.2012 
     */
    public function testHttpPrepending(){
        $purger = new Yourdelivery_Api_Varnish_Purger();
        $purger->addUrl('http://www.yourdelivery.local/samson');
        $url = array_pop($purger->getUrlList());
        $this->assertEquals(1, preg_match('/^http/', $url));
        
        $purger2 = new Yourdelivery_Api_Varnish_Purger();
        $purger2->addUrl('/samson');
        $url = array_pop($purger2->getUrlList());
        $this->assertEquals(1, preg_match('/^http/', $url));
    }

    /**
     * a value is appended and just checked against leading slash
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012 
     */
    public function testAddUrlWithValue() {
        $purger = new Yourdelivery_Api_Varnish_Purger();
        $purger->addUrl('some_url');
        $url = array_pop($purger->getUrlList());
        $this->assertEquals($url, $this->getPrepend() . '/some_url');
        $purger->clearUrlList();
        $purger->addUrl('/some_url');
        $url = array_pop($purger->getUrlList());
        $this->assertEquals($url, $this->getPrepend() . '/some_url');
    }

}

?>
