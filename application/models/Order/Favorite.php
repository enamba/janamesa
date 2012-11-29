<?php

/**
 * association for favorite orders
 * 
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 12.12.2011
 */
class Yourdelivery_Model_Order_Favorite extends Default_Model_Base {

    private $_table = null;

    /**
     * add favorite for order / customer
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     * 
     * @param type $orderId
     * @param type $customerId
     * @param string $name (optional)
     * 
     * @return boolean
     */
    public function add($orderId, $customerId, $name = null) {
        if (is_null($orderId) || is_null($customerId)) {
            return false;
        }

        if (is_null($name)) {
            $order = null;
            try {
                $order = new Yourdelivery_Model_Order($orderId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return false;
            }
            $name = $order->getService()->getName() . " " . date("d.m.Y", $order->getTime());
        }
        $fav = new Yourdelivery_Model_Order_Favorite();
        $fav->setData(array(
            'orderId' => $orderId,
            'customerId' => $customerId,
            'name' => $name)
        );

        return $fav->save() > 0 ? true : false;
    }
    
    
    /**
     * delete favorite for order / customer
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     * 
     * @return boolean
     */
    public function delete($customerId = null){
        if(is_null($this->getId())){
            $this->_logger->debug('id is null');
            return false;
        }
        
        if(is_null($customerId)){
            return $this->getTable()->delete('id = '. $this->getId()) > 0 ? true : false;
        }else{
            return $this->getTable()->delete('id = '. $this->getId() . ' AND customerId = ' . $customerId) > 0 ? true : false;
        }
            
        
    }

    /**
     * get table
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     * 
     * @return Yourdelivery_Model_DbTable
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Order_Favourites();
        }
        return $this->_table;
    }

}

?>
