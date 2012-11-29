<?php

/**
 * @author Daniel Hahn <hahn@lieferando.de>
 */
class Yourdelivery_Model_Printer_Wiercik extends Yourdelivery_Model_Printer_Abstract {

    /**
     * @var string
     */
    protected $_type = self::TYPE_WIERCIK;

    /**
     * Push this printer into the queue
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.05.2012
     * @return boolean
     */
    public function pushOrder($orderId) {

        $q = new Yourdelivery_Sender_Wiercik(new Yourdelivery_Model_Order($orderId), $this->getId());
        return $q->sendToPrinterQueue();
    }

    /**
     * Is online?
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.05.2012
     * @return boolean
     */
    public function isOnline() {
        
        return ($this->getOnline()) ? true : false;
    }

}

