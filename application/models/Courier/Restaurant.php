<?php

/**
 * Description of Location
 * @package courier
 * @author mlaug
 */
class Yourdelivery_Model_Courier_Restaurant extends Default_Model_Base {

    /**
     * Adds a courier<->restaurant relationship
     * @param array $courierId
     * @param int $restaurantId
     * @return boolean
     */
    public static function add ($courierId, $restaurantId) {

        if ($courierId === null || $restaurantId === null) {
            return false;
        }

        $dbTable = new Yourdelivery_Model_DbTable_Courier_Restaurant();
        $dbTable->createRow(array(
            'courierId' => $courierId,
            'restaurantId' => $restaurantId,
        ))->save();

        // remove all plz from restaurant
        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        $restaurant->deleteAllRanges();

        $courier = new Yourdelivery_Model_Courier($courierId);
        $ranges = $courier->getRanges();
        foreach ($ranges as $r) {
            $restaurant->createLocation($r['cityId'], $r['mincost'], 0, 30 * 60);
        }
        
        return true;

    }

    /**
     * Removes courier<->restaurant relationship
     * @author vpriem
     * @since 21.02.2011
     * @param int $courierId
     * @param int $restaurantId
     * @return boolean
     */
    public static function delete ($courierId, $restaurantId) {
        
        if ($courierId === null || $restaurantId === null) {
            return false;
        }

        $db = Zend_Registry::get('dbAdapter');
        $rows = $db->delete('courier_restaurant', '`courierId` = ' . ((integer) $courierId) . ' AND `restaurantId` = ' . ((integer) $restaurantId));

        if ($rows) {
            // remove all plz from restaurant
            $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
            $restaurant->deleteAllRanges();
        }

        return (boolean) $rows;
    }

    /**
     * Get table
     * @author vpriem
     * @since 21.02.2011
     * @return Yourdelivery_Model_DbTable_Courier_Restaurant
     */
    public function getTable(){

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Courier_Restaurant();
        }
        return $this->_table;
        
    }

}
