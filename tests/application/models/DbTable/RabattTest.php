<?php

/**
 * @runTestsInSeparateProcesses
 */
class DbTable_RabattTest extends Yourdelivery_Test {

    /**
     * Tests getting by hash
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 10.08.2012
     */
    public function testGetByHash() {

        $discount = $this->createNewCustomerDiscount();
        $id = $discount->getId();

        $discount2 = Yourdelivery_Model_Rabatt::getByHash($discount->getHash());

        $this->assertEquals($id, $discount2->getId());
    }
}