<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_RestaurantPlz.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Restaurant_Plz extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'restaurant_plz';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_referenceMap    = array(
        'Restaurant' => array(
            'columns'           => array('restaurantId','plz'),
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Restaurant',
            'refColumns'        => array('id','plz')
        )

    );

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
        $db->update('restaurant_plz', $data, 'restaurant_plz.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_plz', 'restaurant_plz.id = ' . $id);
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
                    ->from( array("%ftable%" => "restaurant_plz") );
                    
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
                    ->from( array("r" => "restaurant_plz") )                           
                    ->where( "r.id = " . $id );

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
                    ->from( array("r" => "restaurant_plz") )                           
                    ->where( "r.restaurantId = " . $restaurantId );

        return $db->fetchRow($query); 
    }


    /**
     * get a rows matching Plz by given value
     * @param int $plz
     */
    public static function findByPlz($plz)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "restaurant_plz") )                           
                    ->where( "r.plz = " . $plz );

        return $db->fetchRow($query); 
    }

    /**
     * Get a rows matching restaurantId and cityId 
     * @author Alex Vait
     * @since 17.08.2012
     */
    public static function findByRestaurantIdAndCityId($restaurantId, $cityId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from(array("rp" => "restaurant_plz"))
                    ->where("rp.restaurantId = ?", $restaurantId)
                    ->where("rp.cityId = ?", $cityId);
        
        return $db->fetchRow($query); 
    }

    /**
     * get a rows matching Status by given value
     * @param tinyint $status
     */
    public static function findByStatus($status)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "restaurant_plz") )                           
                    ->where( "r.status = " . $status );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching DeliverTime by given value
     * @param int $deliverTime
     */
    public static function findByDeliverTime($deliverTime)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "restaurant_plz") )                           
                    ->where( "r.deliverTime = " . $deliverTime );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Delcost by given value
     * @param int $delcost
     */
    public static function findByDelcost($delcost)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "restaurant_plz") )                           
                    ->where( "r.delcost = " . $delcost );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Mincost by given value
     * @param int $mincost
     */
    public static function findByMincost($mincost)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "restaurant_plz") )                           
                    ->where( "r.mincost = " . $mincost );

        return $db->fetchRow($query); 
    }

    /**
     * get all plz where this restaurant is delivering to
     * @return RowSet
     */
    public static function getAll($restaurantId) {
        $db = Zend_Registry::get('dbAdapter');

        $result = array();

        try{
            $sql = 'select * from restaurant_plz where restaurantId = '. $restaurantId . " order by plz";
            $result = $db->fetchAll($sql);
        }
        catch ( Zend_Db_Statement_Exception $e ){
            return 0;
        }

        return $result;
    }

    
}
