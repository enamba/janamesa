<?php

/**
 * Description of Grid Helper
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class Yourdelivery_Helpers_Grid {

    /**
     * get names of budgets associated to location
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.03.2011
     * @return string
     */
    public static function getBudgetNamesFromLocation($locationId) {
        if (is_null($locationId)) {
            return null;
        }

        $db = Zend_Registry::get('dbAdapter');

        $row = $db->fetchRow(sprintf("SELECT GROUP_CONCAT(cb.name SEPARATOR ', ') AS `budgets` FROM company_locations cl
            JOIN company_budgets cb ON cb.id = cl.budgetId WHERE cl.locationId = %d", $locationId));

        return $row['budgets'];
    }

    /**
     * get hashed popup-link for ordercoupon / bestellzettel
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.05.2011
     * @param int $orderId
     * @return type string
     */
    public static function getOrderCouponLink($orderId) {
        return '<a onclick="return popup(\'/ordercoupon/' . Default_Helpers_Crypt::hash($orderId) . '\', \'Bestellzettel\', 650, 500);">' . __('Ansehen') . '</a>';
    }
    
    /**
     * @since 05.12.2011
     * @param string $name
     * @param array $defaults
     * @return string 
     */
    public static function adminGroupSelectBox($name, $defaults) {
                                        
        $db = Zend_Registry::get("dbAdapter");
        $groups = $db->fetchAll('SELECT * FROM admin_access_groups');
        
        $box = '<select multiple="multiple" name="' . $name . '">';
        $box .= '<option></option>';
        
        foreach ($groups as $group) {
            
            $selected = "";
            if (is_array($defaults) && in_array($group['id'], $defaults)){
                $selected = 'selected="selected"';
            }

            $box .= '<option value="' . $group['id'] . '" ' . $selected . ' >' . $group['name'] . '</option>';
        }
        
        $box .= '</select>';
        
        return $box;
    }
    

}
