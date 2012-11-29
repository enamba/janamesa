<?php

/**
 * @author vpriem
 * @since 14.06.2011
 * 
 * @runTestsInSeparateProcesses
 */
class YourdeliverySenderVullTest extends Yourdelivery_Test {

    /**
     * @author vpriem
     * @since 14.06.2011
     */
    public function testGetterSetter() {

        $vull = new Yourdelivery_Sender_Vull("Sams0n123", "Tiffy.pdf", "Tiffy.txt");

        $this->assertEquals($vull->getNr(), "123");
        $this->assertEquals($vull->getPdf(), "Tiffy.pdf");
        $this->assertEquals($vull->getTxt(), "Tiffy.txt");

        $this->assertTrue($vull->setNr("4Tiffy6") instanceof Yourdelivery_Sender_Vull);
        $this->assertTrue($vull->setPdf("Samson.pdf") instanceof Yourdelivery_Sender_Vull);
        $this->assertTrue($vull->setTxt("Samson.txt") instanceof Yourdelivery_Sender_Vull);

        $this->assertEquals($vull->getNr(), "46");
        $this->assertEquals($vull->getPdf(), "Samson.pdf");
        $this->assertEquals($vull->getTxt(), "Samson.txt");
    }

    /**
     * @author vpriem
     * @since 14.06.2011
     */
    public function testSend() {

        $vull = new Yourdelivery_Sender_Vull("", "");
        try {
            $vull->send();
            $this->assertTrue(false);
        } catch (Zend_Exception $e) {
            $this->assertTrue(true);
        }

        $vull->setNr("123456789");
        try {
            $vull->send();
            $this->assertTrue(false);
        } catch (Zend_Exception $e) {
            $this->assertTrue(true);
        }
    }

}
