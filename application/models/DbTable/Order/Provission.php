<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Model_DbTable_Order_Provission extends Default_Model_DbTable_Base {

    protected $_name = 'orders_provission';

    /**
     * store the current fee of an order
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 05.07.2012
     * @param Yourdelivery_Model_Servicetype_Abstract $service 
     */
    public function create(Yourdelivery_Model_Order_Abstract $order, $satellite = false) {
        $prov = 0;
        $item = 0;
        $fee = 0;
        $service = $order->getService();

        if ($satellite) {
            $prov = $service->getKommSat();
            $item = $service->getItemSat();
            $fee = $service->getFeeSat();
        } else {
            $prov = $service->getKomm();
            $item = $service->getItem();
            $fee = $service->getFee();
        }

        $this->createRow(array(
            'orderId' => $order->getId(),
            'prov' => $prov,
            'item' => $item,
            'fee' => $fee
        ))->save();
        
    }

}

?>
