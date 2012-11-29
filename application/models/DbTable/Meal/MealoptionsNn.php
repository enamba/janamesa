<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealoptionsNn.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Meal_MealoptionsNn extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_mealoptions_nn';

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
        $db->delete('meal_mealoptions_nn', 'meal_mealoptions_nn.id = ' . $id);
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
                    ->from( array("%ftable%" => "meal_mealoptions_nn") );
                    
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
                    ->from( array("m" => "meal_mealoptions_nn") )                           
                    ->where( "m.id = " . $id );

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
                    ->from( array("m" => "meal_mealoptions_nn") )                           
                    ->where( "m.optionId = " . $optionId );

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
                    ->from( array("m" => "meal_mealoptions_nn") )                           
                    ->where( "m.optionRowId = " . $optionRowId );

        return $db->fetchRow($query); 
    }
    
    
}
