<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealExtrasNn.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Meal_ExtrasNn extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_extras_nn';

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
        $db->update('meal_extras_nn', $data, 'meal_extras_nn.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_extras_nn', 'meal_extras_nn.id = ' . $id);
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
                    ->from( array("%ftable%" => "meal_extras_nn") );
                    
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
                    ->from( array("m" => "meal_extras_nn") )                           
                    ->where( "m.id = " . $id );

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
                    ->from( array("m" => "meal_extras_nn") )                           
                    ->where( "m.mealId = " . $mealId );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching OptionId by given value
     * @param int $optionId
     */
    public static function findByOptionId($optionId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_extras_nn") )                           
                    ->where( "m.optionId = " . $optionId );

        return $db->fetchRow($query); 
    }
    
    
}
