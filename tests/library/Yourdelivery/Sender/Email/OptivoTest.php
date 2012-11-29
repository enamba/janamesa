<?php

/**
 * @author Vincent Priem <priem@lieferando.de> 
 * @since 11.04.2012
 * 
 * @runTestsInSeparateProcesses
 */
class YourdeliverySenderEmailOptivoTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de> 
     * @since 11.04.2012
     */
    public function testSetGet() {
        
        $email = new Yourdelivery_Sender_Email_Optivo();
        $this->assertTrue($email->setThat("shake") instanceof Yourdelivery_Sender_Email_Optivo);
        $this->assertEquals($email->getThat(), "shake");
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de> 
     * @since 11.04.2012
     */
    public function testOverrideRecipient() {
        
        $email = new Yourdelivery_Sender_Email_Optivo();
        $email->setbmRecipientId("johnny@truelove.com");
        $this->assertNotEquals($email->getbmRecipientId(), "johnny@truelove.com");
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de> 
     * @since 11.04.2012
     */
    public function testGetUrl() {

        $email = new Yourdelivery_Sender_Email_Optivo();
        $email->setbmRecipientId($bmRecipientId = "johnny@truelove.com")
              ->setUserPrename($UserPrename = "Johnny Truelove")
              ->setLastOrderServiceName($LastOrderServiceName = "Pizza Schief");
        $bmRecipientId = $email->getbmRecipientId(); // cause it will be overrided
        
        $this->assertEquals(
            $email->getUrl("RATING_BAD_FOOD", "lieferando.de"), 
            "https://api.broadmail.de/http/form/1UXR6ED-1V46OJK-XEH1BFX/sendtransactionmail?bmRecipientId=" . urlencode($bmRecipientId) . "&bmMailingId=4059941739&UserPrename=" . urlencode($UserPrename) . "&LastOrderServiceName=" . urlencode($LastOrderServiceName));
    }

    /**
     * @author Vincent Priem <priem@lieferando.de> 
     * @since 11.04.2012
     * @expectedException Yourdelivery_Sender_Email_Optivo_Exception
     */
    public function testExceptionUnknowCall() {
        
        $email = new Yourdelivery_Sender_Email_Optivo();
        $email->getUrl("YOU_WILL_NEVER_FOUND_ME");
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de> 
     * @since 11.04.2012
     * @expectedException Yourdelivery_Sender_Email_Optivo_Exception
     */
    public function testExceptionUnknowCallForDomain() {
        
        $email = new Yourdelivery_Sender_Email_Optivo();
        $email->getUrl("RATING_BAD_FOOD", "google.de");
    }
}
