<?php
/**
    * Database interface for Yourdelivery_Model_Servicetype_Comission
    *
    * @author alex
    * @since 22.12.2010
    *
*/

class Yourdelivery_Model_DbTable_Restaurant_Commission extends Default_Model_DbTable_Base
{
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'restaurant_commission';

    /**
     * primary key
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
        $db->delete('restaurant_commission', 'id = ' . $id);
    }


    /**
     * get additiona comissions for this restaurant
     * @since 22.12.2010
     * @author alex
     */
    public static function getAdditionalCommissions($restaurantId) {
        $db = Zend_Registry::get('dbAdapter');

        $result = array();

        try{
            $sql = 'select * from restaurant_commission rc where rc.restaurantId = '. $restaurantId . ' order by rc.from';
            $result = $db->fetchAll($sql);
        }
        catch ( Zend_Db_Statement_Exception $e ){
            return null;
        }

        return $result;
    }
}
