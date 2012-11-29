<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 20.07.2012
 */
class Yourdelivery_Model_Order_Deliverdelay extends Default_Model_Base {
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.07.20121
     * @return Yourdelivery_Model_DbTable_Order_Deliverdelay
     */
    public function getTable() {
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Order_Deliverdelay();
        }
        
        return $this->_table;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.07.2012
     * @return int
     */
    public function computeDelay() {

        return $this->getServiceDeliverDelay() + $this->getCourierDeliverDelay();
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2012
     * @return string
     */
    public function computeDelayFormated() {

        $seconds = $this->computeDelay();
        
        $hours = intval($seconds / 3600);
        $minutes = intval(($seconds / 60) % 60); 
        
        return trim(($hours ? __("%d Std.", $hours) : "") . " " . ($minutes ? __("%d Min.", $minutes) : ""));
    }

}
