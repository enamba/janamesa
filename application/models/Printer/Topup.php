<?php
/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 27.07.2011
 */
class Yourdelivery_Model_Printer_Topup extends Yourdelivery_Model_Printer_Abstract {

    /**
     * @var string
     */
    protected $_type = self::TYPE_TOPUP;
    
    /**
     * Is printer online
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     * @return boolean
     */
    public function isOnline() {
        
        return $this->getOnline() && (time() - $this->getUpdated()) < 360;
    }
    
    /**
     * Push this printer into the queue
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     * @return boolean
     */
    public function pushOrder($orderId) {
        
        $queue = new Yourdelivery_Model_Printer_Topup_Queue();
        return $queue->push($this->getId(), $orderId);
    }
    
}
