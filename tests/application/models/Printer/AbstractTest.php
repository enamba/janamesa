<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class PrinterAbstractTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 04.05.2012
     * @expectedException Yourdelivery_Exception_Database_Inconsistency
     */
    public function testFactoryNotFound() {

        Yourdelivery_Model_Printer_Abstract::factory(99999999);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 04.05.2012
     * @expectedException Yourdelivery_Exception_Database_Inconsistency
     */
    public function testFactoryFactoryWrongType() {

        $printer = $this->_getRandomPrinter();
        $worngType = $printer->getType() == Yourdelivery_Model_Printer_Abstract::TYPE_TOPUP 
            ? Yourdelivery_Model_Printer_Abstract::TYPE_WIERCIK 
            : Yourdelivery_Model_Printer_Abstract::TYPE_TOPUP;
        Yourdelivery_Model_Printer_Abstract::factory($printer->getId(), $worngType);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 04.05.2012
     */
    public function testConstruct() {

        new Yourdelivery_Model_Printer_Topup();
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 04.05.2012
     */
    public function testType() {

        $printer = $this->_getRandomPrinter();
        $this->assertEquals($printer->getType(), $printer->getClassType());
    }
}
