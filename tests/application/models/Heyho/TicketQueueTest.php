<?php

/**
 * Description of TicketQueueTest
 *
 * @author daniel
 */

/**
 * @runTestsInSeparateProcesses 
 */
class TicketQueueTest extends Yourdelivery_Test {

    public function testGetTickets() {
        $ticketQueue = new Yourdelivery_Model_Heyho_TicketQueue();
        $ticketQueue->getTable()->updateQueue();

        $ticketQueue->getTickets(false);
        $stats_old = $ticketQueue->getStats();

        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId, false);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::DELIVERERROR, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "test error"));

        
        $ticketQueue = new Yourdelivery_Model_Heyho_TicketQueue();
        
        $ticketQueue->getTable()->updateQueue();
         sleep(2);
        $ticketQueue->getTickets(false);
       
        $stats_new = $ticketQueue->getStats();

        $this->assertGreaterThan( $stats_old['Error'], $stats_new['Error'] );
        $this->assertGreaterThan($stats_old['All'], $stats_new['All'], print_r($stats_new, true));
        $errorOrder = null;

        $tickets = $ticketQueue->getTicketsTotal();
        foreach ($tickets as $ticket) {

            if ($ticket->getId() == $orderId) {

                $errorOrder = $ticket;
            }
        }
        $this->assertFalse(is_null($errorOrder));
        $this->assertTrue($errorOrder instanceof Yourdelivery_Model_Order_Ticket);
        $this->assertEquals($errorOrder->getState(), Yourdelivery_Model_Order_Abstract::DELIVERERROR);
    }

}

?>
