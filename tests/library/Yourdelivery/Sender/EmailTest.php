<?php
/**
 * @author mlaug
 * @since 03.11.2010
 * 
 * @runTestsInSeparateProcesses
 */
class YourdeliverySenderEmailTest extends Yourdelivery_Test{

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 04.03.2011
     */
    public function testOverride(){

        $config = Zend_Registry::get('configuration');
        
        $email = new Yourdelivery_Sender_Email();
        $res = $email
            ->setSubject("What's up")
            ->setBodyText("blub")
            ->addTo("miles@kane.com")
            ->addCc("joe@edwards.com")
            ->addBcc("greg@mighall.com")
            ->send();

        $this->assertTrue($res);
        $this->assertEquals($email->getCharset(), 'UTF-8');
        $this->assertEquals($email->getSubject(), "!TESTING! What's up");
        
        $this->assertEquals($email->getFrom(), $config->sender->email->from);
        
        $recipients = $email->getRecipients();
        $this->assertEquals(count($recipients), 1);
        $this->assertEquals($recipients[0], $config->testing->email);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.11.2010
     */
    public function testQuickSend(){

        $email = Yourdelivery_Sender_Email::quickSend('Warning', '404 Not Found, Oh my God!', null, 'seo');
        $this->assertTrue($email);

        $email = Yourdelivery_Sender_Email::quickSend('Warning', '<b>404</b> Not Found, Oh my God!', null, 'seo');
        $this->assertTrue($email);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.11.2010
     */
    public function testNotify(){

        $email = Yourdelivery_Sender_Email::notify('My Notification');
        $this->assertTrue($email);

        $email = Yourdelivery_Sender_Email::notify('My Notification', null, false, "Foobar");
        $this->assertTrue($email);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.11.2010
     */
    public function testWarning(){

        $email = Yourdelivery_Sender_Email::warning('My Warning');
        $this->assertTrue($email);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.11.2010
     */
    public function testError(){

        $email = Yourdelivery_Sender_Email::error('My Error');
        $this->assertTrue($email);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.11.2010
     */
    public function testConfidence(){

        $email = Yourdelivery_Sender_Email::confidence('FYI');
        $this->assertTrue($email);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.11.2010
     */
    public function testAttachFileSuccess(){

        $email = new Yourdelivery_Sender_Email();
        $this->assertTrue($email->attachFile(APPLICATION_PATH . "/../public/robots.txt", "text/plain") instanceof Yourdelivery_Sender_Email);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.11.2010
     * @expectedException Zend_Mail_Exception
     */
    public function testAttachFileFailed(){

        $email = new Yourdelivery_Sender_Email();
        $email->attachFile("/this/is/sparta", "text/plain");

    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 30.01.2012
     */
    public function testSave(){
        
        $config = Zend_Registry::get('configuration');
        
        $dbTable = new Yourdelivery_Model_DbTable_Emails();
        
        $email = new Yourdelivery_Sender_Email();
        $res = $email->setSubject("save test")
                     ->setBodyText("blub")
                     ->addTo("miles@kane.com")
                     ->send();
        $this->assertTrue($res);
        
        $dbRow = $dbTable->getLastRow();
        $this->assertEquals($dbRow->type, "customer");
        $this->assertEquals($dbRow->status, 1);
        $this->assertEquals($dbRow->error, "");
        $this->assertEquals($dbRow->email, $config->testing->email);
        
        $email = new Yourdelivery_Sender_Email();
        $res = $email->addTo("miles@kane.com")
                     ->send();
        $this->assertFalse($res);
        
        $dbRow = $dbTable->getLastRow();
        $this->assertEquals($dbRow->type, "customer");
        $this->assertEquals($dbRow->status, 0);
        $this->assertNotEquals($dbRow->error, "");
        $this->assertEquals($dbRow->email, $config->testing->email);
        
        $email = new Yourdelivery_Sender_Email();
        $res = $email->setSubject("save test")
                     ->setBodyText("blub")
                     ->addTo("miles@kane.com")
                     ->save();
        $this->assertGreaterThan(0, $res);
        
        $dbRow = $dbTable->getLastRow();
        $this->assertEquals($dbRow->id, $res);
        $this->assertEquals($dbRow->type, "customer");
        $this->assertEquals($dbRow->status, 1);
        $this->assertEquals($dbRow->error, "");
        $this->assertEquals($dbRow->email, $config->testing->email);
    }
}
