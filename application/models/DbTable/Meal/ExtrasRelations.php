<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealExtrasRelations.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Meal_ExtrasRelations extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_extras_relations';

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
        $db->update('meal_extras_relations', $data, 'meal_extras_relations.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_extras_relations', 'meal_extras_relations.id = ' . $id);
    }

    /**
     * delete a table row by given extra id, meal id and size id
     * @param integer $id
     * @return void
     */
    public static function removeByExtraMealSize($extraId, $mealId, $sizeId)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_extras_relations', 'meal_extras_relations.extraId = ' . $extraId . ' and meal_extras_relations.mealId = ' . $mealId . ' and meal_extras_relations.sizeId = ' . $sizeId);
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
                    ->from( array("%ftable%" => "meal_extras_relations") );
                    
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
                    ->from( array("m" => "meal_extras_relations") )                           
                    ->where( "m.id = " . $id );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching ExtraId by given value
     * @param int $extraId
     */
    public static function findByExtraId($extraId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_extras_relations") )                           
                    ->where( "m.extraId = " . $extraId );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching MealId by given value
     * @param int $mealId
     */
    public static function findByMealId($mealId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_extras_relations") )                           
                    ->where( "m.mealId = " . $mealId );

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
                    ->from( array("m" => "meal_extras_relations") )                           
                    ->where( "m.categoryId = " . $categoryId );

        return $db->fetchRow($query); 
    }
    
    /**
     * get a rows matching SizeId by given value
     * @param int $sizeId
     */
    public static function findBySizeId($sizeId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_extras_relations") )                           
                    ->where( "m.sizeId = " . $sizeId );

        return $db->fetchRow($query); 
    }

    /**
     * get a rows matching SizeId and mealId by given values
     * @param int $mealId
     * @param int $sizeId
     */
    public static function findByMealIdAndSizeId($mealId, $sizeId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_extras_relations") )
                    ->where(  "m.mealId = " . $mealId . " and m.sizeId = " . $sizeId );

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
                    ->from( array("m" => "meal_extras_relations") )                           
                    ->where( "m.cost = " . $cost );

        return $db->fetchRow($query); 
    }
    
    
}
