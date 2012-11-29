<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealCategoriesParents
 *
 * @author alex
 * @since 13.10.2011
*/

class Yourdelivery_Model_DbTable_Meal_CategoriesParents extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_categories_parents';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * get a rows matching Id by given value
     * @param int $id
     */
    public static function findById($id)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_categories_parents") )                           
                    ->where( "m.id = " . $id );

        return $db->fetchRow($query); 
    }
    
    /**
    * get a rows matching Name by given value
    * @return array
    * @author alex
    * @since 13.10.2011
    */
    public static function findByName($name)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_categories_parents") )                           
                    ->where( "m.name = " . $name );

        return $db->fetchRow($query); 
    }
    
    /*
    * get all parent categories
    * @return array
    * @author alex
    * @since 13.10.2011
    */
    public static function getAll(){
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("select * from meal_categories_parents");
        $row = $db->query($sql)->fetchAll();
        return $row;
    }

    /**
     * get all meal categories for which this category is a parent
     * @return array
     * @author alex
     * @since 13.10.2011
     */
    public function getChildren() {
        $sql = sprintf("select * from meal_categories where parentMealCategoryId = %d", $this->getId());
        return $this->getAdapter()->query($sql)->fetchAll();
    }

}
