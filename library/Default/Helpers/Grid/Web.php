<?php

/**
 * Description of Web
 *
 * @author mlaug
 */
class Default_Helpers_Grid_Web {

    /**
     * get options for ip address
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.06.2012
     * @param string $ip
     * @return string
     */
    public static function ipinfo($ip, $uuid, $orderId) {

        $return = sprintf('
            <div class="yd-grid">
                <a href="#" class="yd-grid-trigger">%s</a>
                <div class="yd-grid-box">
                    <a href="#" class="yd-grid-box-close"></a>
                    <ul class="yd-grid-box-content">
                        <li><a href="/administration_order/index/type/view_grid_orders/ipAddrgrid/%s/" target="_blank">%s</a></li>
                        <li class="yd-grid-box-separation"><a href="/administration_order/index/type/view_grid_orders/ipAddrgrid/%s/Gutscheingrid/1" target="_blank">%s</a></li>
                        <li><a href="/administration_request_blacklist/keyword/ip/%s/orderId/%s" class="yd-blacklist-lightbox">%s</a></li>
                        <li><a href="/administration_request_blacklist/keyword/ip_newcustomer_discount/%s/orderId/%s" class="yd-blacklist-lightbox">%s</a></li>
                    </ul>
                </div>
            </div>', $ip, $ip, __b('Alle Bestellungen mit dieser IP anzeigen'), $ip, __b('Verwendete Gutscheine mit dieser IP'), $ip, $orderId, __b('IP Adresse sperren'), $ip, $orderId, __b('IP Adresse für Neukundengutscheine sperren'));


        if ($uuid) {
            $uuidBox = sprintf('
            <div class="yd-grid">
                <a href="#" class="yd-grid-trigger">UUID: %s</a>
                <div class="yd-grid-box">
                    <a href="#" class="yd-grid-box-close"></a>
                    <ul class="yd-grid-box-content">
                        <li><a href="/administration_order/index/type/view_grid_orders/uuidgrid/%s/" target="_blank">%s</a></li>
                        <li class="yd-grid-box-separation"><a href="/administration_order/index/type/view_grid_orders/uuidgrid/%s/Gutscheingrid/1" target="_blank">%s</a></li>
                        <li><a href="/administration_request_blacklist/keyword/uuid/%s/orderId/%s" class="yd-blacklist-lightbox">%s</a></li>
                    </ul>
                </div>
            </div>', $uuid, $uuid, __b('Alle Bestellungen mit dieser UUID anzeigen'), $uuid, __b('Verwendete Gutscheine mit dieser UUID'), $uuid, $orderId, __b('UUID Adresse sperren'));
        }

        return $return . $uuidBox;
    }

}
