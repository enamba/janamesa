<?php
/**
 * @author vpriem
 * @since 24.08.2010
 */
class HelpersWebTest extends Yourdelivery_Test{

    /**
     * @author vpriem
     * @since 24.08.2010
     */
    public function testUrlify(){

        $this->assertEquals(
            Default_Helpers_Web::urlify(" Getränke  *     in  10117 Französische Straße  'München'  bestellen  "),
            "getraenke-in-10117-franzoesische-strasse-muenchen-bestellen"
        );

        $this->assertEquals(
            Default_Helpers_Web::urlify("àáâãäå ç èéêë ìíîï ñ òóôõöø ùúûü ýÿ"),
            "aaaaaea-c-eeee-iiii-n-oooooeo-uuuue-yy"
        );
    }

    /**
     * @author vpriem
     * @since 12.11.2010
     */
    public function testCookie(){
        
         $this->markTestSkipped(
              "This test always fails - HAS TO BE REFACTORED !!! "
            );

        $this->assertTrue(
            Default_Helpers_Web::setCookie('foo', "bar")
        );

        $this->assertTrue(
            Default_Helpers_Web::setCookie('bar', array('bar' => "baz"))
        );

    }
    
    /**
     * @author vpriem
     * @since 06.12.2010
     */
    public function testGetDomain(){

        $this->assertEquals(Default_Helpers_Web::getDomain("node1.yourdelivery.de"), "yourdelivery.de");
        $this->assertEquals(Default_Helpers_Web::getDomain("www.yourdelivery.de"), "yourdelivery.de");
        $this->assertEquals(Default_Helpers_Web::getDomain("yourdelivery.de"), "yourdelivery.de");
        
        $this->assertEquals(Default_Helpers_Web::getDomain("www.janamesa.com.br"), "janamesa.com.br");
        $this->assertEquals(Default_Helpers_Web::getDomain("janamesa.com.br"), "janamesa.com.br");
        
        $this->assertEquals(Default_Helpers_Web::getDomain("www.yourdelivery.co.uk"), "yourdelivery.co.uk");
        $this->assertEquals(Default_Helpers_Web::getDomain("yourdelivery.co.uk"), "yourdelivery.co.uk");

    }

    /**
     * @author vpriem
     * @since 24.18.2011
     */
    public function testSubdomain(){

        $this->assertEquals(Default_Helpers_Web::getSubdomain("www.yourdelivery.de"), "www");
        $this->assertEquals(Default_Helpers_Web::getSubdomain("samson.yourdelivery.de"), "samson");
        $this->assertEquals(Default_Helpers_Web::getSubdomain("yourdelivery.de"), "www");
        $this->assertEquals(Default_Helpers_Web::getSubdomain("samson.staging.yourdelivery.de"), "samson.staging");
    }
    
    /**
     * @author vpriem
     * @since 23.03.2011
     */
    public function testGetReferer(){

        $this->assertEquals(Default_Helpers_Web::getReferer(), "UNKNOWN");

    }

    /**
     * @author vpriem
     * @since 20.04.2011
     */
    public function testSetTimeout(){

        $timeout = ini_get('default_socket_timeout');
        Default_Helpers_Web::setTimeout(2);
        $this->assertEquals(ini_get('default_socket_timeout'), 2);
        Default_Helpers_Web::setTimeout();
        $this->assertEquals(ini_get('default_socket_timeout'), $timeout);

    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.03.2012 
     */
    public function testEmailValidate(){
        $this->assertTrue(Default_Helper::email_validate('matthias.laug@gmail.com'));
        $this->assertTrue(Default_Helper::email_validate('a@a.de'));
        $this->assertFalse(Default_Helper::email_validate('a.de'));
        $this->assertFalse(Default_Helper::email_validate('###@###.de'));
        $this->assertFalse(Default_Helper::email_validate('asd@.de'));
        $this->assertTrue(Default_Helper::email_validate('asd@samsonm.de'));
        $this->assertTrue(Default_Helper::email_validate('miles@kane.co.uk'));
        $this->assertFalse(Default_Helper::email_validate('miles@.co.uk'));
        $this->assertFalse(Default_Helper::email_validate('miles@+kane.co.uk'));
        
        // test blacklist restricted email
        $email = 'my-unique-email@' . time() . '.de';
        // set emailto blacklist
        /* @deprecated BLACKLIST */
        $fp = fopen(BLACKLIST, 'w+');
        fputs($fp, $email);
        fclose($fp);
        $this->assertFalse(Default_Helper::email_validate($email, true));
        $this->assertFalse(Default_Helper::email_validate(strtoupper($email . "   "), true));
        $this->assertTrue(Default_Helper::email_validate($email));
        
    }

}