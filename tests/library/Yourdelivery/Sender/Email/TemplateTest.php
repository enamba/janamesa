<?php
/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 11.05.2012
 * 
 * @runTestsInSeparateProcesses
 */
class YourdeliverySenderEmailTemplateTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 25.07.2012
     */
    public function testSetGetTemplateName() {

        $email = new Yourdelivery_Sender_Email_Template();
        $email->setTemplateName("foo");
        $this->assertEquals($email->getTemplateName(), "foo.htm");
        $email->setTemplateName("foo.txt");
        $this->assertEquals($email->getTemplateName(), "foo.txt");
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     * @expectedException Yourdelivery_Sender_Email_Template_Exception
     */
    public function testNoTemplate() {

        $email = new Yourdelivery_Sender_Email_Template();
        $email->send();
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     * @expectedException Yourdelivery_Sender_Email_Template_Exception
     */
    public function testTemplateNotExists() {

        $email = new Yourdelivery_Sender_Email_Template("findmeifyoucan");
        $email->send();
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     */
    public function testTemplateCompileAndSendHtml() {

        $email = new Yourdelivery_Sender_Email_Template("test");
        $email->setSubject("Test")
              ->addTo("miles@kane.co.uk")
              ->assign('test', "only");
        $this->assertTrue($email->send());
        $this->assertEquals($email->getBodyHtml(true), "This is an email HTML template for testing purposes only ...");
        
        $this->config->domain->base = "test.com";
        
        $email = new Yourdelivery_Sender_Email_Template("test");
        $email->setSubject("Test")
              ->addTo("miles@kane.co.uk")
              ->assign('test', "only");
        $this->assertTrue($email->send());
        $this->assertEquals($email->getBodyHtml(true), "This is an overridden email HTML template for testing purposes only ...");
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     */
    public function testTemplateCompileAndSendText() {

        $email = new Yourdelivery_Sender_Email_Template("test.txt");
        $email->setSubject("Test")
              ->addTo("miles@kane.co.uk")
              ->assign('test', "only");
        $this->assertTrue($email->send());
        $this->assertEquals($email->getBodyText(true), "This is an email TEXT template for testing purposes only.");
        
        $this->config->domain->base = "test.com";
        
        $email = new Yourdelivery_Sender_Email_Template("test.txt");
        $email->setSubject("Test")
              ->addTo("miles@kane.co.uk")
              ->assign('test', "only");
        $this->assertTrue($email->send());
        $this->assertEquals($email->getBodyText(true), "This is an overridden email TEXT template for testing purposes only.");
    }
}
