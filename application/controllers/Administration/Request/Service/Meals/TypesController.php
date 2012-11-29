<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 26.06.2012
 */
class Administration_Request_Service_Meals_TypesController extends Default_Controller_RequestAdministrationBase {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.06.2012
     */
    public function getAction() {

        $this->_disableView();

        $request = $this->getRequest();
        $parentId = (integer) $request->getParam('parentId');

        if (!$parentId) {
            return $this->_json(array('error' => __("Nicht gefunden")));
        }
        
        try {
            $type = new Yourdelivery_Model_Meal_Type($parentId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return $this->_json(array('error' => __("Nicht gefunden")));
        }

        $json = array();
        $children = $type->getChildren();
        foreach ($children as $child) {
            $json[] = array(
                'id' => $child->getId(),
                'name' => $child->getName(),
            );
        }

        return $this->_json(array('children' => $json));
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.06.2012
     */
    public function addAction() {

        $this->_disableView();

        $request = $this->getRequest();
        $parentId = (integer) $request->getParam('parentId');
        $name = trim($request->getParam('name', ""));

        if ($request->isPost() && !empty($name)) {
            $type = new Yourdelivery_Model_Meal_Type();
            $type->setName($name);
            $type->setParentId($parentId);
            $type->save();
            
            return $this->_json(array('id' => $type->getId()));
        }
        
        return $this->_json(array('error' => __("Konnte nicht angelegt werden")));
    }
    
}
