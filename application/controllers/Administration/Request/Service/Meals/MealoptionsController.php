<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 26.06.2012
 */
class Administration_Request_Service_Meals_MealoptionsController extends Default_Controller_RequestAdministrationBase {

    /**
     * add new meal option nn
     * @author jens naie <naie@lieferando.de>
     * @since 22.08.2012
     */
    public function addAction(){
        $this->_disableView();
        $request = $this->getRequest();
        $mealId = (integer) $request->getParam('mealId');
        $optionRowId = (integer) $request->getParam('optionRowId');

        if ( $request->isPost() && $mealId && $optionRowId) {
            try {
                $mealoptionNn = new Yourdelivery_Model_Meal_MealoptionsNn();
                $mealoptionNn->setMealId($mealId);
                $mealoptionNn->setOptionRowId($optionRowId);
                $mealoptionNn->save();
                $this->_updateHasSpecials($mealoptionNn->getOptionRowId());
                $this->logger->adminInfo(sprintf('Neue Mealoption wurde hinzugefügt'));                
                return $this->_json(array('id' => $mealoptionNn->getId()));
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {                
                $this->logger->adminInfo(sprintf('Exception in request_restaurant/addmealoptionAction : %s', $e->getMessage()));
            }
        }
        
         return $this->_json(array('error' => __("Meal Option konnte nicht angelegt werden")));
    }

    /**
     * remove meal option nn
     * @author jens naie <naie@lieferando.de>
     * @since 22.08.2012
     */
    public function removeAction(){
        $this->_disableView();
        $request = $this->getRequest();
        $mealoptionNnId = trim($request->getParam('mealoptionNnId'));

        if ($request->isPost() && $mealoptionNnId) {
            try {
                $mealoptionNn = new Yourdelivery_Model_Meal_MealoptionsNn($mealoptionNnId);
                Yourdelivery_Model_DbTable_Meal_MealoptionsNn::remove($mealoptionNn->getId());
                $this->_updateHasSpecials($mealoptionNn->getOptionRowId());
                $this->logger->adminInfo(sprintf('Mealoption %d wurde gelöscht', $mealoptionNn->getId()));
                return $this->_json(array('id' => $mealoptionNn->getId()));
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Exception in request_restaurant/removemealoptionAction : %s', $e->getMessage()));
            }
        }
        
         return $this->_json(array('error' => __("Meal Option konnte nicht gelöscht werden")));
    }   
    

    /**
     * helper function to update the hasSpecials flag in meals
     * @author jens naie <naie@lieferando.de>
     * @since 22.08.2012
     */
    private function _updateHasSpecials($optionRowId) {
        $mealOptionRowsNn = Yourdelivery_Model_DbTable_Meal_OptionsRowsNn::findByOptionRowId($optionRowId);
        if ($mealOptionRowsNn) {
            foreach($mealOptionRowsNn as $row) {
                $meal = new Yourdelivery_Model_Meals($row['mealId']);
                $meal->updateHasSpecials();
            }
        } else {
            $mealOptionRow = Yourdelivery_Model_DbTable_Meal_OptionsRows::findById($optionRowId);
            if ($mealOptionRow) {
                if ($mealOptionRow['categoryId']) {
                    $meals = Yourdelivery_Model_DbTable_Meals::findByCategoryId($mealOptionRow['categoryId']);
                    foreach($meals as $mealRow) {
                        $meal = new Yourdelivery_Model_Meals($mealRow['id']);
                        $meal->updateHasSpecials();
                    }
                }
            }
        }
    }
}
