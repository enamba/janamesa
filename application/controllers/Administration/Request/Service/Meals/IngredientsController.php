<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 26.06.2012
 */
class Administration_Request_Service_Meals_IngredientsController extends Default_Controller_RequestAdministrationBase {

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 26.06.2012
     */
    public function addAction() {

        $this->_disableView();

        $request = $this->getRequest();
        $groupId = (integer) $request->getParam('groupId');
        $name = trim($request->getParam('name', ""));
        
        if ($request->isPost() && $groupId && !empty($name)) {
            $ingredient = new Yourdelivery_Model_Meal_Ingredients();
            $ingredient->setName($name);
            $ingredient->setGroupId($groupId);
            $ingredient->save();
            
            return $this->_json(array('id' => $ingredient->getId()));
        }
        
        return $this->_json(array('error' => __("Konnte nicht angelegt werden")));
    }
    
}
