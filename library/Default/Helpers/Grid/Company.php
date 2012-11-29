<?php

/**
 * @author Matthias Laug <laug@lieferando.de>
 * @since 13.06.2012
 */
class Default_Helpers_Grid_Company {

    /**
     * bubble box for company infos
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.06.2012
     * @param string $companyName
     * @param integer $companyId
     * @param integer $orderId
     * @return string
     */
    public static function companyinfo($companyName, $companyId, $orderId) {

        if (!strlen($companyName)) {
            return '';
        }
        $header = sprintf('<div class="yd-grid">
                            <a href="#" class="yd-grid-trigger">%s</a>
                            <div class="yd-grid-box">
                                <a href="#" class="yd-grid-box-close"></a>
                                <ul class="yd-grid-box-content">
        ', $companyName);
        $cond = sprintf('
                                    <li><a href="/administration/companylogin/id/%d">%s</a></li>
                                    <li><a href="/administration_company_edit/index/companyid/%d">%s</a></li>',
            $companyId, __b('Bei der Firma einloggen'),
            $companyId, __b('Firma bearbeiten')
        );
        $footer = sprintf('<li><a href="/administration_order/index/type/view_grid_orders/Firmagrid/%s" target="_blank">%s</a></li>
                                    <li class="yd-grid-box-separation"><a href="/administration_order/index/type/view_grid_orders/Gutscheingrid/1/Firmagrid/%s/" target="_blank">%s</a></li>
                                    <li><a href="/administration_request_blacklist/keyword/company/%s/orderId/%s" class="yd-blacklist-lightbox">%s</a></li>
                                </ul>
                            </div>
                        </div>',
            $companyName, __b('Alle Bestellungen der Firma anzeigen'),
            $companyName, __b('Verwendete Gutscheine der Firma'),
            urlencode(preg_replace('/ \#[0-9]+$/', '', $companyName)), $orderId,  __b('Firma blacklisten')
        );

        return $header . ($companyId > 0 ? $cond : '') . $footer;
    }
}