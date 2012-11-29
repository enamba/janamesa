<?php


/**
 * store status of this order for the partner
 *
 * @author Matthias Laug <laug@lieferando.de>
 * @since 28.08.2012
 */
class Yourdelivery_Model_DbTable_Order_Geolocation_StatusLog extends Default_Model_DbTable_Base{
    
    protected $_name = 'order_geolocation_status_log';
    
    /**
     * get the last order status
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 30.08.2012
     * @param integer $orderId
     */
    public function getLastStatus($orderId){
        return $this->select()->where('orderId=?', $orderId)
                    ->query()->fetch();
    }
    
}