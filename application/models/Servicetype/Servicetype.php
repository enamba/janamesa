<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of restaurant servicetypes
 *
 * @author vait
 */
class Yourdelivery_Model_Servicetype_Servicetype extends Default_Model_Base {

    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Servicetype();
        }
        return $this->_table;
    }

    /**
     * remove servicetypes from the restaurant if this is the last meal category of this type
     * @author alex
     * @since 09.02.2011
    */
    public static function removeIfLastServicetypeOfMealCategories($mealCategory) {
        if (intval($mealCategory) == 0) {
            return;
        }

        try {
            $mealCategory = new Yourdelivery_Model_Meal_Category($mealCategory);
            $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($mealCategory->getRestaurantId());

            foreach ($mealCategory->getServiceTypes() as $stdata) {
                $servicetypeId = $stdata['id'];
                if ($restaurant->countMealCategoriesWithServiceType($servicetypeId) < 2) {
                    Yourdelivery_Model_DbTable_Servicetypes::removeByRestaurantAndServicetypeId($restaurant->getId(), $servicetypeId);
                }
            }
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->logger->adminErr(sprintf('Cannot create meal category %d or restaurant, maybe the servicetype for the restaurant was not deleted correctly', $id));
        }
        
    }

}
?>
