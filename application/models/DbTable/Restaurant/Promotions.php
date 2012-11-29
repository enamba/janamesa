<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_RestaurantPromotions.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Restaurant_Promotions extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'restaurant_promotions';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    
    /**
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public static function edit($id, $data)
    {        
        $db = Zend_Registry::get('dbAdapter');
        $db->update('restaurant_promotions', $data, 'restaurant_promotions.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_promotions', 'restaurant_promotions.id = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order=null, $limit=0, $from=0)
    {
        $db = Zend_Registry::get('dbAdapter');
        
        $query = $db->select()
                    ->from( array("%ftable%" => "restaurant_promotions") );
                    
        if($order != null)
        {
            $query->order($order);
        }

        if($limit != 0)
        {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
    }
    
        /**
     * get a rows matching Id by given value
     * @param int $id
     */
    public static function findById($id)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "restaurant_promotions") )                           
                    ->where( "r.id = " . $id );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Minprice by given value
     * @param int $minprice
     */
    public static function findByMinprice($minprice)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "restaurant_promotions") )                           
                    ->where( "r.minprice = " . $minprice );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching RestaurantId by given value
     * @param int $restaurantId
     */
    public static function findByRestaurantId($restaurantId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "restaurant_promotions") )                           
                    ->where( "r.restaurantId = " . $restaurantId );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Name by given value
     * @param varchar $name
     */
    public static function findByName($name)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "restaurant_promotions") )                           
                    ->where( "r.name = " . $name );

        return $db->fetchRow($query); 
    }
    
    
}
