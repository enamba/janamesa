<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 30.09.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class ApiBaseTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.09.2011
     */
    public function testWrongAccessFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setHeader("X-HTTP-Method-Override","DELETE");
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => 'samson-hat-keinen-zutritt'
            ))
        ));
        $this->dispatch('/get_location?id=13132' );
        
        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertResponseCode(403);
        $this->assertEquals('false',$doc->getElementsByTagName("success")->item(0)->nodeValue, Default_Helpers_Log::getLastLog());
    }
    
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.10.2011
     */
    public function testFidelityResponse(){
        $service = $this->getRandomService();

        $this->dispatch('/get_service/' . $service->getId());
        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertEquals('true',$doc->getElementsByTagName("success")->item(0)->nodeValue);
        $this->assertGreaterThan(0,$doc->getElementsByTagName("fidelity")->length);
        $this->assertEquals(0,$doc->getElementsByTagName("points")->item(0)->nodeValue);
        $this->assertEquals('',$doc->getElementsByTagName("message")->item(0)->nodeValue);
    }
}
