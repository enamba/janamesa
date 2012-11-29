<?php
/**
 * Restaurant Servicetype Table
 * @author vpriem
 * @since 04.08.2010
*/
class Yourdelivery_Model_DbTable_Restaurant_Servicetype extends Default_Model_DbTable_Base{
    
    /**
     * Table name
     * @param string
     */
    protected $_name = 'restaurant_servicetype';

    /**
     * Primary key name
     * @param string
     */
    protected $_primary = 'id';

    /**
     * delete a table row by given reestaurant id
     * @param integer $id
     * @return void
     */
    public static function removeByRestaurantId($restaurantId)
    {
        if (is_null($restaurantId) || ($restaurantId==0)) {
            return;
        }
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_servicetype', 'restaurant_servicetype.restaurantId = ' . $restaurantId);
    }    
}
