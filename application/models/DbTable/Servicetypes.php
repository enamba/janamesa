<?php
/**
 * Description of Servicetypes
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Servicetypes extends Default_Model_DbTable_Base {


    protected $_primary = "id";

    protected $_name = "servicetypes";

    protected $_dependentTables = array(
                                        'Yourdelivery_Model_DbTable_Servicetypes_Meal_Categorys_Nn'
                                       );

    /**
     * delete a table row by given restaurant id and servicetype id
     * @author alex
     * @since 09.02.2011
     * @param integer $restaurantId, $serviceTypeId
     * @return void
     */
    public static function removeByRestaurantAndServicetypeId($restaurantId, $servicetypeId)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_servicetype', 'restaurantId = ' . $restaurantId . ' AND ' . ' servicetypeId=' . $servicetypeId);
    }

}
?>
