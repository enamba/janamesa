<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Worker
 *
 * @author mlaug
 */
class Yourdelivery_Api_Acom_Worker {

    /**
     * @author mlaug
     * @since 03.11.2011
     * @return SplObjectStorage 
     */
    static function getAcomOrders($service) {
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->fetchAll('SELECT o.id FROM orders o 
                              INNER JOIN restaurants r 
                              ON r.id=o.restaurantId AND r.id=?
                              WHERE r.notify="acom" AND o.state=0', (integer) $service);
        $orders = new SplObjectStorage();
        foreach ($result as $c) {
            try{
                $order = new Yourdelivery_Model_Order((integer) $c['id']);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                continue;
            }
            if ($order->getState() >= 0) {
                $orders->attach($order);
            }
        }
        return $orders;
    }

    /**
     * translate the payment string into
     * a valid payment method for 
     * @author mlaug
     * @since 03.11.2011
     * @param string $payment
     * @return integer
     */
    static function payment($payment) {
        switch ($payment) {
            default:
                return 0;
            case 'bar':
                return 0;
            case 'credit':
                return 201;
            case 'paypal':
                return 202;
            case 'ebanking':
                return 203;
            case 'bill':
                return 205;
        }
    }

}

?>
