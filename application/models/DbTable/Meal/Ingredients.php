<?php
/**
* @author alex
* @since 21.07.2011
*/

class Yourdelivery_Model_DbTable_Meal_Ingredients extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_ingredients';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    
    /**
     * get all ingredients of the specified ingredients group
     * @author Alex Vait
     * @since 26.06.2012
     */
    public static function findByGroupId($groupId) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("mi" => "meal_ingredients") )                           
                    ->where( "mi.groupId = " . $groupId);

        return $db->fetchAll($query); 
    }
    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 26.06.2012
     * @return array
     */
    public static function getAutocomplete() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAll("SELECT id, groupId, name FROM `meal_ingredients` ORDER BY name");
    }
}
