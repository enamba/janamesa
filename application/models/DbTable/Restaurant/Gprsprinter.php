<?php
/**
 * GPRS printer for restaurants
 * @author alex
 * @since 24.05.2011
*/
class Yourdelivery_Model_DbTable_Restaurant_Gprsprinter extends Default_Model_DbTable_Base{
    
    /**
     * Table name
     * @param string
     */
    protected $_name = 'restaurant_printer_topup';

    /**
     * Primary key name
     * @param string
     */
    protected $_primary = 'id';
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_printer_topup', 'restaurant_printer_topup.id = ' . $id);
    }

    
    /**
     * get associated restaurants
     * @author alex
     * @since 08.06.2011
     * @return array
     */
    public static function getRestaurantAssociations($printerId){
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('select p.*, r.name as restaurantName from restaurant_printer_topup p join restaurants r on r.id=p.restaurantId where printerId=%d order by r.name', $printerId);
        $query = $db->query($sql);
        return $query->fetchAll();
    }

    /**
     * delete a table row by given printer and restaurant id
     * @param integer $id
     * @return void
     */
    public static function removeRestaurantAssociation($printerId, $restaurantId)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_printer_topup', 'restaurant_printer_topup.printerId = ' . $printerId . ' and restaurant_printer_topup.restaurantId = ' . $restaurantId);
    }
    
}
