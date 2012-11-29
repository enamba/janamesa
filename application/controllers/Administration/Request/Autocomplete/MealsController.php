<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 26.06.2012
 */
require_once(APPLICATION_PATH . '/controllers/Administration/Request/Autocomplete/Abstract.php');

class Administration_Request_Autocomplete_MealsController extends Administration_Request_Autocomplete_Abstract {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.06.2012
     */
    public function typesAction() {
        
        return $this->_json(
            Yourdelivery_Model_DbTable_Meal_Types::getAutocomplete());
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 26.06.2012
     */
    public function ingredientsAction() {
        $ingredientGroups = Yourdelivery_Model_Meal_Ingredients::getGroups();
        $ingrRow = Yourdelivery_Model_DbTable_Meal_Ingredients::getAutocomplete();
        
        $resultIngredients = array();
        foreach ($ingrRow as $ri) {
            $resultIngredients[] = array('id' => $ri['id'], 'value' => sprintf("%s (%s)", $ri['name'], $ingredientGroups[$ri['groupId']]));
        }
                
        return $this->_json($resultIngredients);
    }

}
