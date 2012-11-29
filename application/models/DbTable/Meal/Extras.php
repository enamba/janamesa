<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealExtras.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Meal_Extras extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_extras';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';


    protected $_referenceMap    = array(
        'Group' => array(
            'columns'           => 'groupId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Meal_ExtrasGroups',
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
        $db->update('meal_extras', $data, 'meal_extras.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_extras', 'meal_extras.id = ' . $id);
        $db->delete('meal_extras_relations', 'meal_extras_relations.extraId = ' . $id);
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
                    ->from( array("%ftable%" => "meal_extras") );
                    
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
                    ->from( array("m" => "meal_extras") )                           
                    ->where( "m.id = " . $id );

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
                    ->from( array("m" => "meal_extras") )                           
                    ->where( "m.restaurantId = " . $restaurantId );

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
                    ->from( array("m" => "meal_extras") )                           
                    ->where( "m.name = " . $name );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Kind by given value
     * @param int $kind
     */
    public static function findByKind($kind)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_extras") )                           
                    ->where( "m.kind = " . $kind );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Cost by given value
     * @param int $cost
     */
    public static function findByCost($cost)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_extras") )                           
                    ->where( "m.cost = " . $cost );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Nr by given value
     * @param varchar $nr
     */
    public static function findByNr($nr)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_extras") )                           
                    ->where( "m.nr = " . $nr );

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
                    ->from( array("m" => "meal_extras") )                           
                    ->where( "m.status = " . $status );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching CategoryId by given value
     * @param int $categoryId
     */
    public static function findByCategoryId($categoryId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_extras") )                           
                    ->where( "m.categoryId = " . $categoryId );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Mwst by given value
     * @param int $mwst
     */
    public static function findByMwst($mwst)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_extras") )                           
                    ->where( "m.mwst = " . $mwst );

        return $db->fetchRow($query); 
    }

    /**
     * get a rows matching GroupId by given value
     * @param int $groupId
     */
    public static function findByGroupId($groupId)
    {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                    ->from( array("m" => "meal_extras") )                           
                    ->where( "m.groupId = " . $groupId );

        return $db->fetchRow($query); 
    }


    /**
     * get the relation cost for this extra in relation to the meal and size or to the whole size
     */
    public function getRelationCosts($sizeId, $mealId){
        $id = $this->getId();

        // first search for meal-size-extra relation, so this extra belongs to the whole category
        $sql = sprintf("select cost from meal_extras_relations where extraId=%d and sizeId=%d and coalesce(mealId,0)=0", $id, $sizeId);
        $result = $this->getAdapter()->query($sql)->fetch();
        
        // found cost for this size-extra relation
        if (is_array($result) && !is_null(current($result))) {
            return $result;
        }
        $db = Zend_Registry::get('dbAdapterReadOnly');
        
        // no cost for the whole category was found, search for cost only for this extra-size-meal relation
        $sql = sprintf("select cost from meal_extras_relations where extraId=%d and sizeId=%d and mealId=%d", $id, $sizeId, $mealId);
        return $db
                    ->query($sql)
                    ->fetch();
    }

    /**
     * get all distinct extras name, available in the database
     * @author alex
     * @since 06.10.2010
     */
    public static function getDistinctExtrasNames()
    {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $sql = sprintf("select DISTINCT(TRIM(name)) as extra_name from meal_extras where LOCATE(' ', name)=0 order by TRIM(name)");
        return $db->fetchAll($sql);
    }
    
}
