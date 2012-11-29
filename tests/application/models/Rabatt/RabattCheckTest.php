<?php

/**
 * Description of RabattCheck
 *
 * @author daniel
 */
/**
 * @runTestsInSeparateProcesses 
 */
class RabattCheckTest extends Yourdelivery_Test {
    
    /**
     * Test Discount Process from Step 1 to Finish
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.01.2012
     */
    public function testDiscountProcess() {
        $discount = $this->createNewCustomerDiscount();
        $code = $discount->generateCode(null, 15, null, true);

        $check = new Yourdelivery_Model_Rabatt_Check();
        //Step 1
        $row = $check->getValidVerificationCode($code, $discount->getId());

        $this->assertFalse(empty($row));
        $this->assertEquals($row['send'], 0);

        $email = Default_Helper::generateRandomString(8) . "@lfd.de";
        $name = "Name_" . Default_Helper::generateRandomString(4);
        $prename = "Vorname_" . Default_Helper::generateRandomString(4);

        //Step 2

        $check->saveStep2($discount, $email, $name, $prename, $row['id']);

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('rabatt_check')->where('id=?', $check->getId());
        $result = $db->fetchRow($query);

        $this->assertEquals($check->getId(), $result['id'], sprintf("checkId #%s - result #%s", $check->getId(), $result['id']));
        $this->assertEquals($name, $result['name']);
        $this->assertEquals($prename, $result['prename']);
        $this->assertEquals($email, $result['email']);
        $this->assertNotNull($result['codeEmail']);
        $this->assertNotNull($result['codeTel']);
        $this->assertNull($result['tel']);
        if ($discount->getType() > 1) {
            $this->assertNotNull($result['rabattVerificationId']);
        }

        //Step 3 - 1
        $tel = Default_Helper::generateRandomString(11, "123456789");
        while ($check->getTable()->findByEmailOrTel("bigNotEmptyStringNotInDb", $tel)) {
            $tel = Default_Helper::generateRandomString(11, "123456789");
        }

        $this->assertTrue($check->checkTel($tel));

        $date = date(DATETIME_DB);
        $check->setTel($tel);
        $check->setTelSend($date);
        $check->save();


        $result = $db->fetchRow($db->select()->from('rabatt_check')->where('id=?', $check->getId()));

        $this->assertEquals($result['tel'], $tel);
        $this->assertEquals($result['telSend'], $date);
        $this->assertNull($result['raballCodeId']);


        //Step 3 - 2 
        $final = $check->finalize($discount);

        $result = $db->fetchRow($db->select()->from('rabatt_check')->where('id=?', $check->getId()));

        $this->assertNotNull($result['rabattCodeId']);
        $this->assertNotNull($result['customerId']);
        $result = $db->fetchRow($db->select()->from('rabatt_codes_verification')->where('id=?', $result['rabattVerificationId']));
        if ($discount->getType() == 2) {

            $this->assertEquals($result['send'], 1);
        } elseif ($discount->getType() == 3) {
            $this->assertEquals($result['send'], 0);
        }
    }
    
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 16.02.2012
     * check if user has registered elsewhere in between step 2 and 3, add customer id to discount
     * 
     */
    public function testDiscountProcessWithUserRegistered() {
         $discount = $this->createNewCustomerDiscount();
        $code = $discount->generateCode(null, 15, null, true);

        $check = new Yourdelivery_Model_Rabatt_Check();
        //Step 1
        $row = $check->getValidVerificationCode($code, $discount->getId());

        $this->assertFalse(empty($row));
        $this->assertEquals($row['send'], 0);

        $email = Default_Helper::generateRandomString(8) . "@lfd.de";
        $name = "Name_" . Default_Helper::generateRandomString(4);
        $prename = "Vorname_" . Default_Helper::generateRandomString(4);

        //Step 2

        $check->saveStep2($discount, $email, $name, $prename, $row['id']);

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('rabatt_check')->where('id=?', $check->getId());
        $result = $db->fetchRow($query);

        $this->assertEquals($check->getId(), $result['id'], sprintf("checkId #%s - result #%s", $check->getId(), $result['id']));
        $this->assertEquals($name, $result['name']);
        $this->assertEquals($prename, $result['prename']);
        $this->assertEquals($email, $result['email']);
        $this->assertNotNull($result['codeEmail']);
        $this->assertNotNull($result['codeTel']);
        $this->assertNull($result['tel']);
        if ($discount->getType() > 1) {
            $this->assertNotNull($result['rabattVerificationId']);
        }
        
        $customer_data  = array('email' => $check->getEmail(),
                                                'name' => $check->getName(),
                                                'prename' => $check->getPrename(),
                                                'tel' => $check->getTel(),
                                                'password' => Default_Helper::generateRandomString(8));
        
         $customerId = Yourdelivery_Model_Customer::add($customer_data);
        
        
        //Step 3 - 1
        $tel = Default_Helper::generateRandomString(11, "123456789");
        while ($check->getTable()->findByEmailOrTel("bigNotEmptyStringNotInDb", $tel)) {
            $tel = Default_Helper::generateRandomString(11, "123456789");
        }

        $this->assertTrue($check->checkTel($tel));

        $date = date(DATETIME_DB);
        $check->setTel($tel);
        $check->setTelSend($date);
        $check->save();


        $result = $db->fetchRow($db->select()->from('rabatt_check')->where('id=?', $check->getId()));

        $this->assertEquals($result['tel'], $tel);
        $this->assertEquals($result['telSend'], $date);
        $this->assertNull($result['raballCodeId']);


        //Step 3 - 2 
        $final = $check->finalize($discount);

        $result = $db->fetchRow($db->select()->from('rabatt_check')->where('id=?', $check->getId()));

        $this->assertNotNull($result['rabattCodeId']);
        $this->assertNotNull($result['customerId']);
        $result = $db->fetchRow($db->select()->from('rabatt_codes_verification')->where('id=?', $result['rabattVerificationId']));
        if ($discount->getType() == 2) {

            $this->assertEquals($result['send'], 1);
        } elseif ($discount->getType() == 3) {
            $this->assertEquals($result['send'], 0);
        }
    }
    
    

    /**
     * @author Matthias Laug <laug@lieferando.de> 
     */
    public function testGetCustomerOfDiscount() {
        $table = new Yourdelivery_Model_DbTable_RabattCheck();
        $customer = $this->getRandomCustomer();
        
        $id = $table->createRow(array(
            'referer' => 'samson',
            'email' => $customer->getEmail(),
            'name' => $customer->getName(),
            'prename' => $customer->getPrename(),
            'tel' => $customer->getTel(),
            'codeEmail' => Default_Helper::generateRandomString(4, "12345657"),
            'codeTel' => Default_Helper::generateRandomString(4, "1234567"),
            'customerId' => $customer->getId()
        ))->save();
        
        $this->assertGreaterThan(0,$id);
        $check = new Yourdelivery_Model_Rabatt_Check($id);
        $this->assertEquals($check->getCustomer()->getId(), $customer->getId());
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de> 
     * @since 01.02.2012
     */
    public function testFindByEmailOrTelOrCustomerOrVerificationcodeAndGetterAndResend(){
        $table = new Yourdelivery_Model_DbTable_RabattCheck();
        $customer = $this->getRandomCustomer();
        
        $codeEmail = Default_Helper::generateRandomString(4, "12345657");
        $codeTel = Default_Helper::generateRandomString(4, "12345657");
        
        $rabatt = $this->createNewCustomerDiscount(array('type' => Yourdelivery_Model_Rabatt::TYPE_VERIFICATION_MANY));
        $verificationCode = $rabatt->getCodes(true)->getRow(0);
        
        $customerId = time();
        $email = time() . '@gmail.com';
        $tel = time();
        
        $id = $table->createRow(array(
            'referer' => 'samson',
            'email' => $email,
            'name' => $customer->getName(),
            'prename' => $customer->getPrename(),
            'tel' => $tel,
            'codeEmail' => $codeEmail,
            'codeTel' => $codeTel,
            'customerId' => $customerId,
            'rabattVerificationId' => $verificationCode->id
        ))->save();
        
        //getter and validation
        $check = new Yourdelivery_Model_Rabatt_Check($id);
        $this->assertTrue(Yourdelivery_Model_Rabatt_Check::isVerificationCodeById($verificationCode->id));
        $this->assertTrue($check->codeIsValid($codeTel));
        
        $result = Yourdelivery_Model_Rabatt_Check::findByEmailOrTelOrCustomerOrVerificationcode($email);
        $this->assertEquals($id,$result->getId());
        $result = Yourdelivery_Model_Rabatt_Check::findByEmailOrTelOrCustomerOrVerificationcode(null, $tel);
        $this->assertEquals($id,$result->getId());
        $result = Yourdelivery_Model_Rabatt_Check::findByEmailOrTelOrCustomerOrVerificationcode(null, null, $customerId);
        $this->assertEquals($id,$result->getId());
        $result = Yourdelivery_Model_Rabatt_Check::findByEmailOrTelOrCustomerOrVerificationcode(null, null, null, $verificationCode['registrationCode']);
        $this->assertEquals($id,$result->getId());
        
        //resend
        // for resend we need valid customer
        $cust = $this->createCustomer();
        $check->setCustomerId($cust->getId());
        $check->save();
        
        $this->assertTrue($check->resend());
        $this->assertTrue($check->resend());
        $this->assertTrue($check->resend());
        //$this->assertFalse($check->resend()); //once YD-1302 is finalized, this should return false
    }

}

?>
