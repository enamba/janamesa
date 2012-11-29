<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealExtrasGroups.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Meal_ExtrasGroups extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_extras_groups';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_dependentTables = array(
                                        'Yourdelivery_Model_DbTable_Meal_Extras',
                                       );
    
    protected $_referenceMap    = array(
        'Restaurant' => array(
            'columns'           => 'restaurantId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Restaurant',
            'refColumns'        => 'id'
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
        $db->update('meal_extras_groups', $data, 'meal_extras_groups.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_extras_groups', 'meal_extras_groups.id = ' . $id);
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
                    ->from( array("%ftable%" => "meal_extras_groups") );
                    
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
                    ->from( array("m" => "meal_extras_groups") )                           
                    ->where( "m.id = " . $id );

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
                    ->from( array("m" => "meal_extras_groups") )                           
                    ->where( "m.name = " . $name );

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
                    ->from( array("m" => "meal_extras_groups") )                           
                    ->where( "m.restaurantId = " . $restaurantId );

        return $db->fetchRow($query); 
    }

    /**
     * get all extras of this group
     * @return Zend_Db_Table_Rowset_Abstract
     */
     public function getExtras(){
        $sql = sprintf("select * from meal_extras where groupId=%d and status=1", $this->getId());
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * get all extras of this group sorted by name
     * @return array
     */
     public function getExtrasSorted($sortby){
        $sql = sprintf("select * from meal_extras where groupId=%d and status=1 order by %s", $this->getId(), $sortby);
        return $this->getAdapter()->fetchAll($sql);
    }

    /*
    * get all extras groups names for this restaurant
    * @return array
    */
    public static function getAllExtrasGroupsNames($restaurantId){
        if ( is_null($restaurantId) ){
            return null;
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf("select id, name, internalName from meal_extras_groups where restaurantId=" . $restaurantId);
        $row = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }   
}
