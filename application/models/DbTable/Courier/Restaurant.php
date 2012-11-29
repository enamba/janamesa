<?php
/**
 * Description of Restaurant
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Courier_Restaurant extends Default_Model_DbTable_Base{

    /**
     * Table name
     */
    protected $_name = "courier_restaurant";

    /**
     * Primary key name
     */
    protected $_primary = 'id';

   /**
     * check if a curier works for any restaurant
     * @return restaurantId
     */
    public function isCourierBy($courierId){
        $sql = sprintf('select * from courier_restaurant where courierId=%d;', $courierId);
        $query = $this->getAdapter()->query($sql);
        $result = $query->fetchAll();

        $highest = 0;
        foreach($result as $r) {
            if($r['created'] > $highest) {
                $restId = $r['restaurantId'];
                $highest = $r['created'];
            }
        }

        return $restId;
    }

    /**
     * Delete rows by given courierId
     * @author vpriem
     * @param integer $courierId
     * @return int
     */
    public static function removeByCourier ($courierId) {

        $db = Zend_Registry::get('dbAdapter');
        return $db->delete('courier_restaurant', 'courierId = ' . ((integer) $courierId));

    }
    
    /**
     * Delete rows by given restaurantId
     * @author vpriem
     * @param integer $restaurantId
     * @return int
     */
    public static function removeByRestaurant ($restaurantId) {

        $db = Zend_Registry::get('dbAdapter');
        return $db->delete('courier_restaurant', 'restaurantId = ' . ((integer) $restaurantId));

    }

}
