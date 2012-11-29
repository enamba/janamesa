<?php

/**
 * Description of Service
 *
 * @author mlaug
 */
class Default_Helpers_Grid_Service {

    /**
     * Get corresponding Image if it has been provided
     * @author Allen Frank <frank@lieferando.de>
     * @since 22-02-2012
     * @param integer $restaurantId
     * @param integer $franchiseTypeId
     * @param integer $restaurantName
     * @return string corresponding Image if it has been provided
     */
    public static function decorateService($restaurantId, $franchiseTypeId, $dlName) {

        $img = '';
        switch ($franchiseTypeId) {
            case FRANCHISE_TYPE_NORMAL: //normal
                break;
            case FRANCHISE_TYPE_NOCONTRACT://noContract
                break;
            case FRANCHISE_TYPE_PREMIUM://premium
                $img .= '<img src="/media/images/yd-backend/premium-1.png" alt="" />';
                break;
            case FRANCHISE_TYPE_BUTLER://butler
                break;
            case FRANCHISE_TYPE_BLOOMBURYS://bloomsbury
                $img .= '<img src="/media/images/yd-backend/bloomsbury-1.png" alt="" />';
                break;
            case FRANCHISE_TYPE_EATSTAR://eatstar
                break;
        }


        return sprintf('<div class="yd-grid">
                            <a href="#" class="yd-grid-trigger" 
                                data-service-id="%d" 
                                data-grid-callback="serviceoptions">%s %s</a>
                        </div>', $restaurantId, $img, $dlName);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.06.2012
     * @param string $telService
     * @param string $telCustomer
     * @return string
     */
    public static function decorateTel($telCustomer, $telService, $orderId) {

        return sprintf('<div class="yd-grid">
                            <a href="#" class="yd-grid-trigger" title="%s">%s</a>
                            <div class="yd-grid-box">
                                <a href="#" class="yd-grid-box-close"></a>
                                <ul class="yd-grid-box-content">
                                    <li><a href="sip:0%s" class="yd-sip">%s</a></li>
                                    <li><a href="/administration_order/index/type/view_grid_orders?tel=%s" target="_blank">%s</a></li>
                                    <li class="yd-grid-box-separation"><a href="/administration_order/index/type/view_grid_orders/Gutscheingrid/1/?tel=%s" target="_blank">%s</a></li>
                                    <li><a href="/administration_request_blacklist/keyword/tel/%s/orderId/%s" class="yd-blacklist-lightbox">%s</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="yd-grid">
                            <a href="#" class="yd-grid-trigger" title="%s">%s</a>
                            <div class="yd-grid-box">
                                <a href="#" class="yd-grid-box-close"></a>
                                <ul class="yd-grid-box-content">
                                    <li><a href="sip:0%s" class="yd-sip">%s</a></li>
                                </ul>
                            </div>
                        </div>', 
                        __b('Kunde'), __b('Kunde: %s', $telCustomer), $telCustomer, __b('Kunden anrufen'), $telCustomer, __b('Alle Bestellungen der Nummer anzeigen'), $telCustomer, __b('Alle Bestellungen der Nummer mit Gutschein'), $telCustomer, $orderId, __b('Telefonnummer blacklisten'), 
                        __b('Dienstleister:'), __b('Dienstleister %s', $telService), $telService, __b('Dienstleister anrufen'));
    }

}

