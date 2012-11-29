<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 
 *
 * @author vait
 */
class Yourdelivery_Model_Servicetype_MealCategorysNn extends Default_Model_Base {

    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Servicetypes_MealCategorysNn();
        }
        return $this->_table;
    }

    /**
     * save servicetype association and check this type in the restaurant association
     * @return int
     */
    public function save() {
        $serviceTypeId = $this->getServicetypeId();
        $mealCategoryId = $this->getMealCategoryId();

        // add this servicetype to the restaurant if not yet available
        try {
            $mealCategory = new Yourdelivery_Model_Meal_Category($mealCategoryId);
            $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($mealCategory->getRestaurantId());
            if (!in_array($serviceTypeId, $restaurant->getAllServiceTypes())) {
                $restSt = new Yourdelivery_Model_Servicetype_Servicetype();
                $restSt->setRestaurantId($restaurant->getId());
                $restSt->setServicetypeId($serviceTypeId);
                $restSt->save();
            }
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->logger->adminErr(sprintf('Cannot create meal category %d or restaurant, maybe the servicetype for the restaurant was not set correctly', $mealCategoryId));
        }

        $id = parent::save();
        return $id;
    }
}
?>
