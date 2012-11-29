<?php

/**
 * Editing partner restaurant data
 *
 * @author Alex Vait <vait@lieferando.de>
 * @since 02-08.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Request_ServiceControllerTest extends Yourdelivery_Test {

    /**
     * get exisiting partner data or create new entry and modify it's email
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.08.2012
     */
    public function testModifypartneremail() {
        $service = $this->getRandomService();

        $newEmailValue = Default_Helper::generateRandomString(10) . "@test.de";

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'restaurantId' => $service->getId(),
            'kind' => 'email',
            'newvalue' => $newEmailValue
        );
        
        $request->setPost($post);
        $this->dispatch('/request_administration_service/changepartnerdata');
        $json = json_decode($this->getResponse()->getBody(), true);
        $this->assertTrue(is_array($json));
        $this->assertEquals($json['type'], 'success');
        
        // test the modified partner email 
        $this->assertEquals($service->getPartnerEmail(), $newEmailValue);
        
        // send an email in wrong format, it will not be set
        $newEmailValueWrong = Default_Helper::generateRandomString(10) . "testde";

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'restaurantId' => $service->getId(),
            'kind' => 'email',
            'newvalue' => $newEmailValueWrong
        );
        
        $request->setPost($post);
        $this->dispatch('/request_administration_service/changepartnerdata');
        
        // test the partner email, must not be modified
        $this->assertNotEquals($service->getPartnerEmail(), $newEmailValueWrong);        
    }
    
    /**
     * get exisiting partner data or create new entry and modify it's mobile phone number
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.08.2012
     */
    public function testModifypartnermobile() {
        $service = $this->getRandomService();

        $newMobileValue = "0179" . Default_Helper::generateRandomString(10, "0123456789");

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'restaurantId' => $service->getId(),
            'kind' => 'mobile',
            'newvalue' => $newMobileValue
        );
        
        $request->setPost($post);
        $this->dispatch('/request_administration_service/changepartnerdata');
        $json = json_decode($this->getResponse()->getBody(), true);
        $this->assertTrue(is_array($json));
        $this->assertEquals($json['type'], 'success');
        
        // test the modified partner mobile number
        $this->assertEquals($service->getPartnerMobile(), Default_Helpers_Normalize::telephone($newMobileValue));
        
        // send an mobile number in wrong format, it will not be set
        $newMobileValueWrong = "030" . Default_Helper::generateRandomString(10, "0123456789");

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'restaurantId' => $service->getId(),
            'kind' => 'mobile',
            'newvalue' => $newMobileValueWrong
        );
        
        $request->setPost($post);
        $this->dispatch('/request_administration_service/changepartnerdata');
        
        // test the partner email, must not be modified
        $this->assertNotEquals($service->getPartnerMobile(), Default_Helpers_Normalize::telephone($newMobileValueWrong));
        
        // send an mobile number as letters, it will not be set
        $newMobileValueLetters = "030" . Default_Helper::generateRandomString(10, "abcdefghijklmnopqrstuvwxyz");

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'restaurantId' => $service->getId(),
            'kind' => 'mobile',
            'newvalue' => $newMobileValueLetters
        );
        
        $request->setPost($post);
        $this->dispatch('/request_administration_service/changepartnerdata');
        
        // test the partner email, must not be modified
        $this->assertNotEquals($service->getPartnerMobile(), Default_Helpers_Normalize::telephone($newMobileValueLetters));        
    }
    
    /**
     * create new printer and manipulate it's firmware
     * @author Alex Vait <vait@lieferando.de>
     * @since 13.08.2012
     */
    public function testSetFirmwareLessThanActual() {                
        
        $initialFirmware = rand(100, 200);
        
        $printerTable = new Yourdelivery_Model_DbTable_Printer_Topup();
        $printerId = $printerTable->createRow(
                    array(
                        'type' => 'topup',
                        'serialNumber' => Default_Helper::generateRandomString(10),
                        'simNumber' => Default_Helper::generateRandomString(10),
                        'firmware' => $initialFirmware
                    )
                )->save();
        
        // test that we have succesfully created a printer 
        $this->assertGreaterThan(0, $printerId);        
        
        try {
            $printer = Yourdelivery_Model_Printer_Abstract::factory($printerId);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        // test that we have succesfully created a printer through factory
        $this->assertNotNull($printer);
        $this->assertGreaterThan(0, $printer->getId());
        
        // **** less than actual, must return error
        $newFirmware = $initialFirmware - 1;
        
        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'printerId' => $printerId,
            'firmware' => $newFirmware,
        );
        
        $request->setPost($post);
        $this->dispatch('/request_administration_service/setfirmware');
        $json1 = json_decode($this->getResponse()->getBody(), true);
        $this->assertTrue(is_array($json1));
        $this->assertEquals($json1['error'], '1');

        try {
            $printerRefreshed = Yourdelivery_Model_Printer_Abstract::factory($printerId);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
                
        // test that we have succesfully created a printer through factory
        $this->assertNotNull($printerRefreshed);
        $this->assertGreaterThan(0, $printerRefreshed->getId());

        // test that we don't have the new firmware upgrade version
        $this->assertNotEquals(intval($printerRefreshed->getUpgrade()), $newFirmware);     
                
        // *****  delete the printer
        $printer->getTable()
                ->getCurrent()
                ->delete();
        
        // test that we have succesfully deleted a printer
        try {
            $printerDeleted = Yourdelivery_Model_Printer_Abstract::factory($printerId);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }        

        $this->assertNull($printerDeleted);
    }    
    
    /**
     * create new printer and manipulate it's firmware
     * @author Alex Vait <vait@lieferando.de>
     * @since 13.08.2012
     */
    public function testSetFirmwareSameAsActual() {                
        
        $initialFirmware = rand(100, 200);
        
        $printerTable = new Yourdelivery_Model_DbTable_Printer_Topup();
        $printerId = $printerTable->createRow(
                    array(
                        'type' => 'topup',
                        'serialNumber' => Default_Helper::generateRandomString(10),
                        'simNumber' => Default_Helper::generateRandomString(10),
                        'firmware' => $initialFirmware
                    )
                )->save();
        
        // test that we have succesfully created a printer 
        $this->assertGreaterThan(0, $printerId);
        
        
        try {
            $printer = Yourdelivery_Model_Printer_Abstract::factory($printerId);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        // test that we have succesfully created a printer through factory
        $this->assertNotNull($printer);
        $this->assertGreaterThan(0, $printer->getId());
        
        // **** same as actual firmware, must return error
        $newFirmware = $initialFirmware;
        
        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'printerId' => $printerId,
            'firmware' => $newFirmware,
        );
        
        $request->setPost($post);
        $this->dispatch('/request_administration_service/setfirmware');
        $json = json_decode($this->getResponse()->getBody(), true);        
        $this->assertTrue(is_array($json));
        $this->assertEquals($json['error'], '1');

        try {
            $printerRefreshed = Yourdelivery_Model_Printer_Abstract::factory($printerId);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        // test that we have succesfully created a printer through factory
        $this->assertNotNull($printerRefreshed);
        $this->assertGreaterThan(0, $printerRefreshed->getId());

        // test that we don't have the new firmware upgrade version
        $this->assertNotEquals(intval($printerRefreshed->getUpgrade()), $newFirmware);     
        
        // *****  delete the printer
        $printer->getTable()
                ->getCurrent()
                ->delete();
        
        // test that we have succesfully deleted a printer
        try {
            $printerDeleted = Yourdelivery_Model_Printer_Abstract::factory($printerId);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }        

        $this->assertNull($printerDeleted);
    }    
    
    /**
     * create new printer and manipulate it's firmware
     * @author Alex Vait <vait@lieferando.de>
     * @since 13.08.2012
     */
    public function testSetFirmwareGreaterThanActual() {                
        
        $initialFirmware = rand(100, 200);
        
        $printerTable = new Yourdelivery_Model_DbTable_Printer_Topup();
        $printerId = $printerTable->createRow(
                    array(
                        'type' => 'topup',
                        'serialNumber' => Default_Helper::generateRandomString(10),
                        'simNumber' => Default_Helper::generateRandomString(10),
                        'firmware' => $initialFirmware
                    )
                )->save();
        
        // test that we have succesfully created a printer 
        $this->assertGreaterThan(0, $printerId);
                
        try {
            $printer = Yourdelivery_Model_Printer_Abstract::factory($printerId);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        // test that we have succesfully created a printer through factory
        $this->assertNotNull($printer);
        $this->assertGreaterThan(0, $printer->getId());
        
        // **** greater than actual firmware, must return success
        $newFirmware = $initialFirmware + 1;
        
        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'printerId' => $printerId,
            'firmware' => $newFirmware,
        );
        
        $request->setPost($post);
        $this->dispatch('/request_administration_service/setfirmware');
        $json = json_decode($this->getResponse()->getBody(), true);
        $this->assertTrue(is_array($json));
        $this->assertEquals($json['success'], '1');
        
        try {
            $printerRefreshed = Yourdelivery_Model_Printer_Abstract::factory($printerId);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        // test that we have succesfully created a printer through factory
        $this->assertNotNull($printerRefreshed);
        $this->assertGreaterThan(0, $printerRefreshed->getId());

        // test that we don't have the new firmware upgrade version
        $this->assertEquals(intval($printerRefreshed->getUpgrade()), $newFirmware);     
        
        // *****  delete the printer
        $printer->getTable()
                ->getCurrent()
                ->delete();
        
        // test that we have succesfully deleted a printer
        try {
            $printerDeleted = Yourdelivery_Model_Printer_Abstract::factory($printerId);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }        

        $this->assertNull($printerDeleted);
    }    
}

