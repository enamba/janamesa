<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Ticket
 * @author Daniel  Hahn <hahn@lieferando.de>
 * @since 13.07.2012
 */
class Default_Helpers_Ticket {

    /**
     * @author Daniel  Hahn <hahn@lieferando.de>
     * @since 13.07.2012
     * @param string $comment
     * @param string $orderId
     * @return string
     */
    public static function parseOrderLog($comment, $orderId) {
        try {
            $order = new Yourdelivery_Model_Order($orderId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return $comment;
        }
        //paypal match
        $comment = preg_replace("/paypal/i", '<div class="yd-grid yd-ticket-log"><a  class="yd-grid-trigger" data-grid-callback="paypaloptions" data-order-id="' . $orderId . '">$0</a></div>', $comment);
        //change FaxService
        $comment = preg_replace("/ " . __b('fax') . " /i", "<div class='yd-grid yd-ticket-log'><a class='yd-grid-trigger' data-grid-callback='faxServiceSelect' data-grid-service-id='" . $order->getService()->getId() . "'>$0</a></div>", $comment);
        //email Bubble
        $comment = preg_replace("/[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+/i", '<div class="yd-grid yd-ticket-log"><a  class="yd-grid-trigger" data-orderid="' . $orderId . '" data-grid-callback="emailinfo" data-email="$0">$0</a></div> ', $comment);
        //order
        $comment = preg_replace("/" . __b('order') . " #(\d+)/", '<div class="yd-grid yd-ticket-log"><a  class="yd-grid-trigger" data-grid-callback="orderoptions" data-order-id="$1">$0</a></div>', $comment);
        return $comment;
    }

}

