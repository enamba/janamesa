<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealOptionsRowsNn.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Meal_OptionsRowsNn extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_options_rows_nn';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';


    protected $_dependentTables = array(
                                        'Yourdelivery_Model_DbTable_Meal_OptionsRows'
                                       );

    protected $_referenceMap    = array(
        'OptionsRows' => array(
            'columns'           => 'optionRowId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Meal_OptionsRows',
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
        $db->update('meal_options_rows_nn', $data, 'meal_options_rows_nn.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_options_rows_nn', 'meal_options_rows_nn.id = ' . $id);
    }

    /**
     * delete a table row by given meal id
     * @param integer $id
     * @return void
     */
    public static function removeByMealId($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_options_rows_nn', 'meal_options_rows_nn.mealId = ' . $id);
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
                    ->from( array("%ftable%" => "meal_options_rows_nn") );
                    
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
                    ->from( array("m" => "meal_options_rows_nn") )                           
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
                    ->from( array("m" => "meal_options_rows_nn") )                           
                    ->where( "m.restaurantId = " . $restaurantId );

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
                    ->from( array("m" => "meal_options_rows_nn") )                           
                    ->where( "m.mealId = " . $mealId );

        return $db->fetchRow($query); 
    }

    /**
     * get a rows matching OptionRowId by given value
     * @param int $optionRowId
     */
    public static function findByOptionRowId($optionRowId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_options_rows_nn") )                           
                    ->where( "m.optionRowId = " . $optionRowId );

        return $db->fetchAll($query); 
    }
}
