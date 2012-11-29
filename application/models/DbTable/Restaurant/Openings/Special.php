<?php
/**
 * Yourdelivery_Model_DbTable_Restaurant_Openings_Special
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Restaurant_Openings_Special extends Default_Model_DbTable_Base{

    /**
     * Table name
     * @var string
     */
    protected $_name = 'restaurant_openings_special';
    
    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * @author vpriem
     * @param int $id
     * @return array
     */
    public function getOpenings ($id, $time = null) {

        $db = Zend_Registry::get('dbAdapterReadOnly');
        if ($time === null) {
            return $db->fetchAll(
                "SELECT *
                FROM `restaurant_openings_special`
                WHERE `restaurantId` = ?
                    AND `specialDate` >= CURRENT_DATE()", $id
            );
        }
        
        $specialDate = date('Y-m-d', $time);
        return $db->fetchAll(
            "SELECT *
            FROM `restaurant_openings_special`
            WHERE `restaurantId` = ?
                AND specialDate = ?
                AND `specialDate` >= CURRENT_DATE()", array($id, $specialDate)
        );

    }

    /**
     * Get restaurant openings by given date in sql date format
     * @param int $id
     * @return
     */
    public static function getOpeningsAtSqlDate($id, $date = null){
        $db = Zend_Registry::get('dbAdapterReadOnly');

        if ( is_null($date) ){
            $sql = sprintf("select * from restaurant_openings_special where restaurantId=%d and specialDate >= CURRENT_DATE()", $id);
            $result = $db->query($sql)->fetchAll();
        }
        else{
            $sql = sprintf("select * from restaurant_openings_special where restaurantId=%d and specialDate='%s' and specialDate >= CURRENT_DATE()", $id, $date);
            $result = $db->query($sql)->fetchAll();
        }
        return $result;
    }

    /**
     * Get restaurant openings by given date
     * @author vpriem
     * @param int $id restaurantId
     * @param int $ts timestamp
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getOpeningsAtDate ($id, $ts) {

        return $this->fetchAll(
            $this->select()
                ->where("`restaurantId` = ?", $id)
                ->where("`specialDate` >= CURRENT_DATE()")
                ->where("`specialDate` = ?", date('Y-m-d', $ts))
        );

    }

    /**
     * Delete a table row by given primary key
     * @param int $id
     * @return void
     */
    public static function remove ($id) {

        if ($id !== null) {
            $db = Zend_Registry::get('dbAdapter');
            $db->delete('restaurant_openings_special', 'id = ' . ((integer) $id));
        }       
    }

    /**
     * Delete all special opening for certain restaurant
     * @param int $restaurantId
     * @return void
     * @author alex
     * @since 21.11.2011
     */
    public static function removeAll($restaurantId) {
        if (intval($restaurantId) != 0) {
            $db = Zend_Registry::get('dbAdapter');
            $db->delete('restaurant_openings_special', 'restaurantId = ' . ((integer) $restaurantId));
        }       
    }    
}
