<?php
/**
 * Restaurants partner data
 * @author Alex Vait
 * @since 31.07.2012
*/
class Yourdelivery_Model_DbTable_Restaurant_Partner extends Default_Model_DbTable_Base{
    
    /**
     * Table name
     * @var string
     */
    protected $_name = 'partner_restaurants';

    /**
     * @author Alex Vait
     * @since 31.07.2012
     * @param string $restaurantId
     * @return array
     */
    public static function findByRestaurantId($restaurantId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()->from('partner_restaurants')->where('restaurantId = ?', $restaurantId);
        $row = $db->fetchRow($select);
        return $row;
    }
}
