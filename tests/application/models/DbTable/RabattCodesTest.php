<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RabattCodesTest
 *
 * @author daniel
 */
class DbTableRabattCodesTest extends Yourdelivery_Test {

    /**
     * @author  Daniel Hahn <hahn@lieferando.de>
     * @since 09.08.2012
     */
    public function testGetOnlyCustomersIds() {

        $discount = $this->createDiscount(1, 0, 20, false, false, true);

        $rabattTable = new Yourdelivery_Model_DbTable_RabattCodes();
        $idRows = $rabattTable->getOnlyCustomersIds();

        $ids = array_map(function($element) {
                    return $element['id'];
                }, $idRows);


        $this->assertTrue(in_array($discount->getId(), $ids));
    }

    /**
     * @author  Daniel Hahn <hahn@lieferando.de>
     * @since 09.08.2012
     */
    public function testGetOnlyCompanyIds() {
        $discount = $this->createDiscount(1, 0, 20, false, true);

        $rabattTable = new Yourdelivery_Model_DbTable_RabattCodes();
        $idRows = $rabattTable->getOnlyCompanyIds();

        $ids = array_map(function($element) {
                    return $element['id'];
                }, $idRows);


        $this->assertTrue(in_array($discount->getId(), $ids));
    }

}

?>
