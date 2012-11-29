<?php
/**
 * Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday
 * @author alex
 * @since 02.12.2010
 */
class Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday extends Default_Model_DbTable_Base{

    /**
     * Table name
     * @var string
     */
    protected $_name = 'restaurant_openings_holidays';
    
    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Delete a table row by given primary key
     * @param int $id
     * @return void
     */
    public static function remove ($id) {
        if (!is_null($id)) {
            $db = Zend_Registry::get('dbAdapter');
            $db->delete('restaurant_openings_holidays', 'id = ' . ((integer) $id));
        }        
    }

    /**
     * Delete all table rows by given date
     * @param int $id
     * @return void
     */
    public static function removeByDate ($date) {
        if (!is_null($date)) {
            $db = Zend_Registry::get('dbAdapter');
            $db->delete('restaurant_openings_holidays', 'date = "' . $date . '"');
        }
    }

    /**
     * Get all holidays
     * @author alex
     * @since 02.12.2010
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public static function getHolidays() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAll(
                "select distinct(roh.id), roh.date, roh.name, o.stateId from restaurant_openings_holidays roh join city o on o.stateId=roh.stateId order by date");
    }

    /**
     * Test if this day is a holiday in this federal land
     * @author alex
     * @since 02.12.2010
     * @return boolean
     */
    public static function isHoliday($date, $stateId) {
        if ( is_null($date) || is_null(intval($stated)) ){
            return false;
        }
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $row = $db->fetchRow("SELECT count(`id`) AS `count` FROM `restaurant_openings_holidays` WHERE date = '" . $date . "' AND stateId = " . $stateId . " LIMIT 1");
        
        return $row['count'] > 0;
    }
}
