<?php

/**
 * @author vpriem
 * @since 07.06.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class PdfTest extends Yourdelivery_Test {

    public function testTemplateBillCompany() {

        $billId = $this->_getRandomBillId('company');
        $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));

        $billId = $this->_getRandomBillId('company', 'costcenter');
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('company', 'project');
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('company', null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('company', null, null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('company', null, null, null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('company', null, null, null, null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('company', null, null, null, null, null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('company', null, null, null, null, null, null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }
    }

    public function testTemplateBillRestaurant() {

        $billId = $this->_getRandomBillId('rest');
        $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));

        $billId = $this->_getRandomBillId('rest', 'costcenter');
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('rest', 'project');
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('rest', null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('rest', null, null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('rest', null, null, null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('rest', null, null, null, null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('rest', null, null, null, null, null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }

        $billId = $this->_getRandomBillId('rest', null, null, null, null, null, null, 1);
        if ($billId !== false) {
            $this->assertTrue(Yourdelivery_Model_Billing::rebuild($billId));
        }
    }

}
