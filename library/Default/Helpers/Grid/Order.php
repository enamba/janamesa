<?php

/**
 * Description of Order
 *
 * @author mlaug
 */
class Default_Helpers_Grid_Order {

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.06.2012
     * @param integer $id
     * @return string 
     */
    public static function options($id) {

        return sprintf('<div class="yd-grid yd-order-show-options">
                            <a href="#" class="yd-grid-trigger"
                                data-order-id="%d" 
                                data-grid-callback="orderoptions">%s</a>
                        </div>', $id, __b('Options'));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.06.2012
     * @param integer $id
     * @return string 
     */
    public static function payment($payment, $id) {

        switch ($payment) {
            default:
                return Default_Helpers_Human_Readable_Default::payment($payment);

            case 'paypal':
                return sprintf('<div class="yd-grid yd-order-show-paypal-options">
                                    <a href="#" class="yd-grid-trigger"
                                        data-order-id="%d" 
                                        data-grid-callback="paypaloptions">%s</a>
                                </div>', $id, __('PayPal'));
                break;
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.06.2012
     * @param string $address
     * @param string $city
     * @return string
     */
    public static function address($address, $city, $orderId) {
        return sprintf('
            <div class="yd-grid">
                <a href="#" class="yd-grid-trigger">%s</a>
                <div class="yd-grid-box">
                    <a href="#" class="yd-grid-box-close"></a>
                    <ul class="yd-grid-box-content">
                        <li><a href="/administration_order/index/type/view_grid_orders/Adressegrid/%s" target="_blank">%s</a></li>
                        <li class="yd-grid-box-separation"><a href="/administration_order/index/type/view_grid_orders/Adressegrid/%s/Gutscheingrid/1/" target="_blank">%s</a></li>
                        <li><a href="/administration_request_blacklist/keyword/address/%s/orderId/%s" class="yd-blacklist-lightbox">%s</a></li>
                    </ul>
                </div>
            </div>', $address, $address, __b('Alle Bestellungen zu dieser Adresse'), $address, __b('Verwendete Gutscheine mit dieser Adresse'), urlencode($address . " " . $city), $orderId, __b('Adresse blacklisten'));
    }

}
