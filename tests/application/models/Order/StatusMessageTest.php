<?php

/**
 * Description of StatusMessage
 *
 * @author daniel
 */

/**
 * @runTestsInSeparateProcesses
 */
class Yourdelivery_Model_Order_StatusMessageTest extends Yourdelivery_Test {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.07.2012
     */
    public function testStatusMessage() {

        $message = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_BY_FAX, "test", "test", "12345678");

        $text = "Send out order to service via test (test) to 12345678";

        $this->assertEquals($message->getRawMessage(), $text);

        $translatedText = __b('Send out order to service via %s (%s) to %s ', "test", "test", "12345678");

        $this->assertEquals(trim($message->getTranslateMessage()), trim($translatedText));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.07.2012
     */
    public function testStatusMessageInOrder() {

        $db = Zend_Registry::get('dbAdapter');

        $select = $db->select()->from('orders', array('id'))->order('RAND()');

        $row = $db->fetchRow($select);

        $order = new Yourdelivery_Model_Order($row['id']);

        $order->setStatus($order->getStatus(), new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "testcomment"));

        $history = $order->getTable()->getStateHistory();
        
        $row = $history->current();
        
        $this->assertEquals(__b("Comment: %s", 'testcomment'), $row->getStatusMessage());
        
        $state = $order->getLastStateComment();
         

        $this->assertEquals(__b("Comment: %s", 'testcomment'), $state);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.07.2012
     */
    public function testStatusConstantUnique() {

        $reflection = new ReflectionClass('Yourdelivery_Model_Order_StatusMessage');

        $constants = $reflection->getConstants();

        $unique = array_unique($constants);

        $this->assertEquals(count($constants), count($unique), "Konstanten sind nicht unique !!!!");
    }
    
    /**
     * @author Matthias Laug
     * @since 23.07.2012
     * @expectedException Yourdelivery_Exception_InvalidMessage
     */
    public function testInvalidMessage(){
        new Yourdelivery_Model_Order_StatusMessage(1000000000000000);
    }

    /**
     *@author Daniel Hahn <hahn@lieferando.de>
     *@since 25.07.2012
     */
    public function testInvalidParamCount(){
        
        $message = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT);
                
        $raw = $message->getRawMessage();
        
        $this->assertTrue(strpos($raw, "Param missing in Status") !== false);
        
        $translated = $message->getTranslateMessage();
        
        $this->assertTrue(strpos($translated, "Param missing in Status") !== false);
        
        $message = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "test");
      
        $raw = $message->getRawMessage();
        $this->assertFalse(strpos($raw, "Param missing in Status"));
        $translated = $message->getTranslateMessage();
        
        $this->assertFalse(strpos($translated, "Param missing in Status"));
        
        
    }
    
    
}
