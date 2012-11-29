<?php

/**
 * Description of Customer
 *
 * @author mlaug
 */
class Default_Helpers_Grid_Customer {

    /**
     * create an information bubble for a customer in a grid
     * 
     * @author Matthias Laug
     * @since 07.06.2012
     * 
     * @param string $name
     * @param string $email
     * @param integer $customerId
     * @param string $iPhoneUuid
     * @return string 
     */
    public static function customerInfo($name, $email, $customerId = "", $iPhoneUuid = "", $orderId = null) {
        $img = '';
        if (strlen($iPhoneUuid) > 0) {
            $img = "<img src='/media/images/yd-backend/iphone.png' alt='' />";
        }

        $name_strip = preg_replace('/\#[0-9]+/', '', $name);

        return sprintf("<div class='yd-grid yd-show-customer-info'>
                             <a href='#' class='yd-grid-trigger' 
                                data-email='%s' 
                                data-customerId='%s'
                                data-customer-name='%s'
                                data-orderId='%s'
                                data-grid-callback='customerinfo'                                
                                >%s %s</a>
                        </div>", $email, $customerId, $name_strip, $orderId, $name, $img);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.06.2012
     * 
     * @param string $email
     */
    public static function emailinfo($email, $orderId = null) {
        return sprintf('<div class="yd-grid">
                            <a href="#" class="yd-grid-trigger"
                                data-email="%s" 
                                data-grid-callback="emailinfo" data-orderId="%s">%s</a>
                        </div>', $email, $orderId, $email);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.08.2012
     * 
     * @param string $email
     */
    public static function checkNewsletter($customerEmail) {
        
        $email = $customerEmail;
        try {
            $cust = new Yourdelivery_Model_Customer(null, $email);
            if ($cust->getNewsletter()) {
                return '<input type="checkbox" class="yd-checkbox-newsletter" data-email="' . $email . '" value="1" checked="checked"  />';
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }

        return '<input type="checkbox" class="yd-checkbox-newsletter" data-email="' . $email . '" value="1" />';
    }

}
