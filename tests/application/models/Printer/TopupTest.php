<?php

/**
 * @author Daniel Hahn <hahn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class PrinterTopupTest extends Yourdelivery_Test {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testPushOrder() {

        $printer = $this->_getRandomTopupPrinter();
        $orderId = $this->placeOrder();
        $printer->pushOrder($orderId);

        $orderIds = array();
        $queue = new Yourdelivery_Model_Printer_Topup_Queue();
        $orders = $queue->getQueue();
        foreach ($orders as $order) {
            $orderIds[] = $order->orderId;
        }
        $this->assertTrue(in_array($orderId, $orderIds));
        
        $queue->deleteFrom($orderId);
        $this->assertNotEquals(count($orders), count($queue->getQueue()));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return Yourdelivery_Model_DbTable_Printer_Topup
     */
    protected function _getRandomTopupPrinter() {

        $dbTable = new Yourdelivery_Model_DbTable_Printer_Topup();
        $dbRows = $dbTable->fetchAll();
        shuffle($dbRows);
        
        $printer = new Yourdelivery_Model_Printer_Topup($dbRows[0]['id']);
        $this->assertEquals($dbRows[0]['id'], $printer->getId());
        return $printer;
    }

}
