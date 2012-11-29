<?php
/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 11.05.2012
 * 
 * @runTestsInSeparateProcesses
 */
class YourdeliverySenderSmsTemplateTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     * @expectedException Yourdelivery_Sender_Sms_Exception
     */
    public function testNoTemplate() {

        $sms = new Yourdelivery_Sender_Sms_Template();
        $sms->send();
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     * @expectedException Yourdelivery_Sender_Sms_Exception
     */
    public function testTemplateNotExists() {

        $sms = new Yourdelivery_Sender_Sms_Template("findmeifyoucan");
        $sms->send();
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     */
    public function testTemplateCompileAndSend() {

        $sms = new Yourdelivery_Sender_Sms_Template("test");
        $sms->assign('test', "only");
        $this->assertTrue($sms->send("1234567890"));
        $this->assertEquals($sms->getText(), "This is a SMS template for testing purposes only.");
    }
}
