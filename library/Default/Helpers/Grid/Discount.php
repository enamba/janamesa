<?php

/**
 * Grid Discount Helper
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 13.06.2012
 */
class Default_Helpers_Grid_Discount {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 13.06.2012
     * @param integer $rabattCodeId
     * @param integer $orderId
     * @param boolean $isDiscount
     */
    public static function discountInfo($rabattCodeId, $orderId, $isDiscount) {

        if ($isDiscount) {
            return sprintf('<div class="yd-grid">
                                <a href="#" class="yd-grid-trigger"
                                    data-grid-callback="discountInfo"
                                    data-rabattCodeId = "%s"
                                    data-orderId="%s"><img src="/media/images/yd-backend/ok-%s.png"></a>
                            </div>', $rabattCodeId, $orderId, $isDiscount);
        }

        return sprintf('<div><img src="/media/images/yd-backend/ok-%s.png"></div>', $isDiscount);
    }

}