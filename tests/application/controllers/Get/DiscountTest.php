<?php

/**
 * This claas tests the discount handling in our Rest-API
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 01.12.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class DiscountTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.12.2011
     */
    public function testCompanyDiscountNotAllowed() {
        $discountCode = $this->createDiscount(1, 0, 10, false, true);

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_discount/'.$discountCode->getCode());

        $this->assertResponseCode(406);
    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.12.2011
     */
    public function testNotAvailableDiscount() {

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_discount/some-random-stuff-here-'.time());

        $this->assertResponseCode(404);
    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testNotUsableDiscount(){
        $discountCode = $this->createDiscount(false, 0, 10, true, false, false, false, false, date('Y-m-d H:i:s',strtotime('-2 days')), date('Y-m-d H:i:s',strtotime('-1day')));

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_discount/'.$discountCode->getCode());
        $this->assertResponseCode(406);
    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testCalculatePercentageAmount(){
        $discountCode = $this->createDiscount(false, 0, 20, true, false, false, false, false);

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_discount/'.$discountCode->getCode().'?amount=1000');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertEquals('200', $doc->getElementsByTagName("diff")->item(0)->nodeValue);
        $this->assertEquals('800', $doc->getElementsByTagName("newamount")->item(0)->nodeValue);
        $this->assertEquals('20', $doc->getElementsByTagName("percent")->item(0)->nodeValue);
        $this->assertEquals('0', $doc->getElementsByTagName("amount")->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testCalculateAbsoluteAmount(){
        $discountCode = $this->createDiscount(false, 1, 300, true, false, false, false, false);

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_discount/'.$discountCode->getCode().'?amount=1000');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertEquals('300', $doc->getElementsByTagName("diff")->item(0)->nodeValue);
        $this->assertEquals('700', $doc->getElementsByTagName("newamount")->item(0)->nodeValue);
        $this->assertEquals('0', $doc->getElementsByTagName("percent")->item(0)->nodeValue);
        $this->assertEquals('300', $doc->getElementsByTagName("amount")->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.12.2011
     */
    public function testValidDiscount() {
        $discountCode = $this->createDiscount(1, 0, 10, false, false);

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_discount/'.$discountCode->getCode());

        $this->assertResponseCode(200);
    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testIndexFail(){
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_discount?foo=bar');
        $this->assertController('get_discount');
        $this->assertAction('index');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPostFail(){
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_discount');
        $this->assertController('get_discount');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPutFail(){
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_discount');
        $this->assertController('get_discount');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testDeleteFail(){
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_discount');
        $this->assertController('get_discount');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.05.2012 
     */
    public function testDiscountMinAmount(){
        $rabattCodeModel = $this->createDiscount(false, 0, 10, false, false, false, false, false, null, null, null, null, 1000);
        
        // we can't check minamount of discount, if we don't get an amount
        $this->dispatch('/get_discount/'.$rabattCodeModel->getCode());
        $this->assertResponseCode(200);
        
        $this->resetRequest();
        $this->resetResponse();
        
        // check discount not validated with amount below minamount of rabatt
        $this->dispatch('/get_discount/'.$rabattCodeModel->getCode().'?amount='.rand(1,999));
        $this->assertResponseCode(406, $this->getResponse()->getBody());
        
        $this->resetRequest();
        $this->resetResponse();
        
        // check discount validated with amount == minamount
        $this->dispatch('/get_discount/'.$rabattCodeModel->getCode().'?amount=1000');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
    }

}

?>
