<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 26.06.2012
 */
class Administration_Request_Service_MealsController extends Default_Controller_RequestAdministrationBase {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.06.2012
     */
    public function stateAction() {
        
        $request = $this->getRequest();
        $q = $request->getParam('q', "types");
        
        $countMeals = Yourdelivery_Model_Meals::getConditionalCount();
        
        // types
        $countDone = 0;
        if ($q == "types") {
            $countDone = Yourdelivery_Model_Meals::getConditionalCount(array('types' => true));
        } 
        // ingredients
        else if ($q == "ingredients") {
            $countDone = Yourdelivery_Model_Meals::getConditionalCount(array('ingredients' => true));            
        }
        
        $this->view->doneCount = $countDone;
        $this->view->donePercentage = $donePercentage = round(($countDone * 100) / $countMeals);
        $this->view->todoCount = $countMeals - $countDone;
        $this->view->todoPercentage = 100 - $donePercentage;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.06.2012
     * @return array
     */
    protected function _getMealIds() {
        
        $request = $this->getRequest();
        $ids = $request->getParam('yd-id-checkbox');
        
        if (!is_array($ids)) {
            return array();
        }
        
        return array_keys($ids);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.06.2012
     */
    public function typesAction() {

        $this->_disableView();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            $meals = array();
            $mealIds = $this->_getMealIds();
            foreach ($mealIds as $mealId) {
                try {
                    $meal = new Yourdelivery_Model_Meals($mealId);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    continue;
                }
                
                if (isset($post['delete'])) {
                    $meal->removeTypes();
                }
                else {
                    $typeId = $post['typeId0'] ? (integer) $post['typeId0'] : 
                             ($post['typeId4'] ? (integer) $post['typeId4'] :  
                             ($post['typeId3'] ? (integer) $post['typeId3'] : 
                             ($post['typeId2'] ? (integer) $post['typeId2'] : (integer) $post['typeId1'])));
                    try {
                        $type = new Yourdelivery_Model_Meal_Type($typeId);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        continue;
                    }
                    
                    if (isset($post['set'])) {
                        $meal->removeTypes();
                    }
                    $meal->addType($type);
                }
                
                $meals[$meal->getId()] = $meal->getTypesHierarchyAsString();
            }
            
            return $this->_json(array('meals' => $meals));
        }
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 27.06.2012
     */
    public function ingredientsAction() {

        $this->_disableView();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            $meals = array();
            $mealIds = $this->_getMealIds();
            
            foreach ($mealIds as $mealId) {
                try {
                    $meal = new Yourdelivery_Model_Meals($mealId);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    continue;
                }
                
                if (isset($post['deleteIngredients'])) {
                    $meal->removeIngredients();
                }
                elseif (isset($post['deleteAttributes'])) {
                    $meal->setAttributes('');
                    $meal->save();
                }
                elseif (isset($post['setAttributes'])) {
                    $attributes = $post['attributes'];
                    $attributesStr = implode(",", $attributes);                    
                    $meal->setAttributes($attributesStr);
                    $meal->save();
                }
                elseif (isset($post['setIngredients']) || isset($post['setIngredientsQuick'])) {
                    
                    if (isset($post['setIngredients'])) {
                        $ingredientsId = $post['ingredientsId'];
                    }
                    else {
                        $ingredientsId = $post['quickaddingredientIds'];                        
                    }
                    
                    foreach ($ingredientsId as $ingredientId) {
                        try {
                            $ingredient = new Yourdelivery_Model_Meal_Ingredients($ingredientId);
                            $meal->addIngredient($ingredient);
                        } 
                        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            continue;
                        }                        
                    }
                }
                    
                $meals[$meal->getId()] = array();
                
                if ($post['deleteIngredients'] || $post['setIngredients'] || $post['setIngredientsQuick']) {
                    $meals[$meal->getId()]['ingredients'] = $meal->getIngredientsAsString();                    
                }
                else {
                    $meals[$meal->getId()]['attributes'] = $meal->getAtributesAsString();
                }
            }
            
            return $this->_json(array('meals' => $meals));
        }
    }    
}
