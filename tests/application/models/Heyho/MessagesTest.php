<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 16.11.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class HeyhoMessagesTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 16.11.2011
     */
    public function testGetTable() {
        
        $model = new Yourdelivery_Model_Heyho_Messages();
        $this->assertInstanceof(Yourdelivery_Model_DbTable_Heyho_Messages,$model->getTable());
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 16.11.2011
     */
    public function testInsert() {

        $model = new Yourdelivery_Model_Heyho_Messages();
        $model->setType("printer");
        $model->setMessage("Printer #123 goes offline");
        $uniqueEntry = "changeRestaurantNotification,putOffline-".time();
        $model->setCallbackAvailable($uniqueEntry);
        $id = $model->save();
        
        // check db
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow(sprintf('SELECT * FROM heyho_messages WHERE callbackAvailable = "%s"', $uniqueEntry));
        $this->assertEquals($id, $row['id']);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     */
    public function testAddCallbacks() {

        $model = new Yourdelivery_Model_Heyho_Messages();
        
        $uniqueEntry1 = 'samson-'.time();
        $uniqueEntry2 = 'tiffy-'.time();
        
        $model->addCallbackAvailable($uniqueEntry1);
        $model->addCallbackAvailable($uniqueEntry2);
        $model->addCallbackAvailable($uniqueEntry1);
        $model->addCallbackAvailable($uniqueEntry2);
        $this->assertEquals($model->getCallbackAvailable(), $uniqueEntry1.','.$uniqueEntry2);
        
        $uniqueEntry1 = 'nick-'.time();
        $uniqueEntry2 = 'fury-'.time();
        
        $model->addCallbackTriggered($uniqueEntry1);
        $model->addCallbackTriggered($uniqueEntry2);
        $model->addCallbackTriggered($uniqueEntry1);
        $model->addCallbackTriggered($uniqueEntry2);
        $this->assertEquals($model->getCallbackTriggered(), $uniqueEntry1.','.$uniqueEntry2);
        $id = $model->save();
        
        // check db
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow(sprintf('SELECT * FROM heyho_messages WHERE callbackTriggered = "%s"', $uniqueEntry1.','.$uniqueEntry2));
        $this->assertEquals($id, $row['id']);
        
    }
}
