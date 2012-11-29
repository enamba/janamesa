<?php
/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 03.08.2011
 */
class Yourdelivery_Model_Printer_Topup_Queue extends Default_Model_Base{

    /**
     * @var Yourdelivery_Model_DbTable_Printer_Topup_Queue
     */
    protected $_table = null;
    
    /**
     * @var Yourdelivery_Model_Printer_Topup
     */
    protected $_printer = null;

    /**
     * @var Yourdelivery_Model_Order
     */
    protected $_order = null;
    
    /**
     * Get order
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.08.2011
     * @return Yourdelivery_Model_Order
     */
    public function getOrder() {
        
        if ($this->_order !== null) {
            return $this->_order;
        }
        
        try {
            $this->_order = new Yourdelivery_Model_Order($this->getOrderId());
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        return $this->_order;
    }
    
    /**
     * Get printer
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.08.2011
     * @return Yourdelivery_Model_Printer_Topup
     */
    public function getPrinter() {
        
        if ($this->_printer !== null) {
            return $this->_printer;
        }
        
        try {
            $this->_printer = new Yourdelivery_Model_Printer_Topup($this->getPrinterId());
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        return $this->_printer;
    }
    
    /**
     * Get related table
     * @author Vincent Priem <priem@lieferando.de>
     * @since 03.08.2011
     * @return Yourdelivery_Model_DbTable_Printer_Topup_Queue
     */
    public function getTable(){

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Printer_Topup_Queue();
        }
        return $this->_table;
    }
    
    /**
     * Get queue
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     * @return Zend_Db_Table_Rowset
     */
    public function getQueue(){
        
        return $this->getTable()
                    ->fetchAll("`state` >= 0");
    }
    
    /**
     * Delete from something
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.08.2011
     * @param int $orderId
     * @return int
     */
    public function deleteFrom($orderId) {
        
        return $this->getTable()
                    ->delete("`orderId` = " . ((integer) $orderId));
    }
    
    /**
     * Push in queue
     * @author Vincent Priem <priem@lieferando.de>
     * @since 06.08.2011
     * @param int $printerId
     * @param int $orderId
     * @return boolean
     */
    public function push($printerId, $orderId){
        
        $this->getTable()
             ->insert(array(
                 'printerId' => $printerId,
                 'orderId' => $orderId,
             ));
        return true;
    }
    
    /**
     * Repush failed order in the queue
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.08.2011
     * @param int $printerId
     * @return int
     */
    public function repush($printerId) {
        
        return $this->getTable()
                    ->update(array('state' => 0), "TIMESTAMPDIFF(MINUTE, `created`, NOW()) < 10 AND `state` < 0 AND `printerId` = " . ((integer) $printerId));
    }
}
