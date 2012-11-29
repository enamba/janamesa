<?php

/**
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 14.06.2012
 */
class Default_Helpers_Grid_Blacklist {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 21.06.2012
     * @return string
     */
    public static function options($valueId) {
        
        return sprintf('<a href="/administration_request_blacklist_value/delete/id/%s" class="yd-blacklist-delete-value yd-are-you-sure" title="%s">%s</a>', 
            $valueId, __b("Echt sicher?"), __b("Löschen / Wiederherstellen"));
    }
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 14.06.2012
     * @return string
     */
    public static function paypalOptions($valueId, $blacklistId) {
        
        return sprintf('<a href="/administration_blacklist/whitelist/id/%s" class="yd-are-you-sure" title="%s">%s</a>', 
            $blacklistId, __b("Vorsicht!! Der Eintrag wird der Whitelist hinzugfügt?"), __b("Whitelist")) . "<br />" . self::options($valueId);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 14.06.2012
     * @return string
     */
    public static function orderLink($orderId) {
        
        if (empty($orderId)) {
            return "-";
        }
        
        return sprintf('<a href="/administration_order/index/type/view_grid_orders/IDgrid/%s" target="_blank">#%s</a>', 
            $orderId, $orderId);
    }

     /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 14.06.2012
     * @return string
     */
    public static function matchingTypes($matching) {
         $matchings = Yourdelivery_Model_Support_Blacklist::getMatchings();
         
         return $matchings[$matching];
    }
    
    
}

