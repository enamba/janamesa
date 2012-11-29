<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealOptionsRows.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Meal_OptionsRows extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_options_rows';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_dependentTables = array(
                                        'Yourdelivery_Model_DbTable_Meal_Categories',
                                        'Yourdelivery_Model_DbTable_Meals'
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
    public static function remove($id, $data)
    {        
        $db = Zend_Registry::get('dbAdapter');
        $db->update('meal_options_rows', $data, 'meal_options_rows.id = ' . $id);
        $db->delete('meal_options_nn', 'meal_options_nn.optionRowId = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function removeById($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_options_rows', $db->quoteInto('meal_options_rows.id = ?', $id));

        $query = $db->select()
                    ->from( array("mnn" => "meal_options_nn") )                           
                    ->where( "optionRowId = ?", $id);
        $options = $db->fetchAll($query);
        foreach ($options as $o) {
            $db->delete('meal_options', $db->quoteInto('meal_options.id = ?', $o['id']));
        }
        
        $db->delete('meal_options_nn', $db->quoteInto('meal_options_nn.optionRowId = ?', $id));
        $db->delete('meal_options_rows_nn', $db->quoteInto('meal_options_rows_nn.optionRowId = ?', $id));
        sleep(2);        
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
                    ->from( array("%ftable%" => "meal_options_rows") );
                    
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
                    ->from( array("m" => "meal_options_rows") )                           
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
                    ->from( array("m" => "meal_options_rows") )                           
                    ->where( "m.mealId = " . $mealId );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Choices by given value
     * @param int $choices
     */
    public static function findByChoices($choices)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_options_rows") )                           
                    ->where( "m.choices = " . $choices );

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
                    ->from( array("m" => "meal_options_rows") )                           
                    ->where( "m.name = " . $name );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Description by given value
     * @param text $description
     */
    public static function findByDescription($description)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_options_rows") )                           
                    ->where( "m.description = " . $description );

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
                    ->from( array("m" => "meal_options_rows") )                           
                    ->where( "m.restaurantId = " . $restaurantId );

        return $db->fetchAll($query); 
    }
        /**
     * get a rows matching CategoryId by given value
     * @param int $categoryId
     */
    public static function findByCategoryId($categoryId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_options_rows") )                           
                    ->where( "m.categoryId = " . $categoryId );

        return $db->fetchRow($query); 
    }
    
    /**
     * get all meals this option group is assigned to
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getMeals(){
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Meal_OptionsRowsNn');
    }

    /**
     * get all options of this row
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getOptions(){
        $sql = sprintf("select o.* from meal_options o join meal_options_nn mon on o.id=mon.optionId where mon.optionRowId=%d and o.status=1", $this->getId());
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * get all options of this group sorted by name
     * @return array
     */
     public function getOptionsSorted($sortby = null){
        if (is_null($sortby)) {
            $sortby = 'TRIM(mo.name)';
        }
        $sql = sprintf("select mo.* from meal_options mo join meal_options_nn mon on mo.id=mon.optionId where mon.optionRowId=%d and mo.status=1 order by %s", $this->getId(), $sortby);
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * get all meals associated with this group sorted by name
     * @author alex
     * @since 11.08.2010
     * @return array
     */
     public function getAssociatedMeals($sortby){
        if (is_null($sortby)) {
            $sortby = 'TRIM(m.name)';
        }
        $sql = sprintf("select m.*, mc.id as categoryId, mc.name as categoryName from meals m join meal_options_rows_nn morn on morn.mealId=m.id join meal_categories mc on mc.id=m.categoryId where morn.optionRowId=%d order by %s", $this->getId(), $sortby);
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * get the meal category of this option
     * @return Zend_Db_Table_Rowset_Abstract
     */
     public function getCategory(){
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Meal_Categories');
    }

}
