<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealSizesNn.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Meal_SizesNn extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_sizes_nn';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_referenceMap    = array(
        'Meal' => array(
            'columns'           => 'mealId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Meals',
            'refColumns'        => 'id'
        ),
        'Size' => array(
            'columns'           => 'sizeId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Meal_Sizes',
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
        $db->update('meal_sizes_nn', $data, 'meal_sizes_nn.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_sizes_nn', 'meal_sizes_nn.id = ' . $id);
    }

    /**
     * delete a table row by given meal id
     * @param integer $id
     * @return void
     */
    public static function removeByMeal($mealId)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_sizes_nn', 'meal_sizes_nn.mealId = ' . $mealId);
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
                    ->from( array("%ftable%" => "meal_sizes_nn") );
                    
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
                    ->from( array("m" => "meal_sizes_nn") )                           
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
                    ->from( array("m" => "meal_sizes_nn") )                           
                    ->where( "m.mealId = " . $mealId );

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
                    ->from( array("m" => "meal_sizes_nn") )                           
                    ->where( "m.sizeId = " . $sizeId );

        return $db->fetchRow($query); 
    }

    /**
     * get a row matching MealId and SizeId by given values
     * @param int $mealId
     * @param int $sizeId
     */
    public static function findBySizeAndMealId($mealId, $sizeId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_sizes_nn") )
                    ->where( "m.mealId = " . $mealId . " and m.sizeId = " . $sizeId);

        $row = $db->fetchRow($query);
        return $row['id'];
    }
    
    /**
     * get a rows matching Cost by given value
     * @param int $cost
     */
    public static function findByCost($cost)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_sizes_nn") )                           
                    ->where( "m.cost = " . $cost );

        return $db->fetchRow($query); 
    }

    /**
     * get a rows matching Store by given value
     * @param tinyint $store
     */
    public static function findByStore($store)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_sizes_nn") )                           
                    ->where( "m.store = " . $store );

        return $db->fetchRow($query); 
    }

    /**
     * get a rows matching Pfand by given value
     * @param int $pfand
     */
    public static function findByPfand($pfand)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_sizes_nn") )                           
                    ->where( "m.pfand = " . $pfand );

        return $db->fetchRow($query); 
    }
    
    
}
