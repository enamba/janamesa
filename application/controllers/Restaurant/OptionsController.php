<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * meal options management
 *
 * @author alex
 */
class Restaurant_OptionsController extends Default_Controller_RestaurantBase {

    /**
     * create meal option
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @modified 15.12.2011
     * @see YD-848
     * 
     * @return redirect
     */
    public function createAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Restaurant_MealOptionEdit();
            if ($form->isValid($post)) {
                $values = $form->getValues();
                $option = new Yourdelivery_Model_Meal_Option();
                $values['cost'] = priceToInt2($values['cost']);

                $option->setData($values);
                $option->setRestaurantId($restaurant->getId());
                $option->save();

                $option_nn = new Yourdelivery_Model_Meal_OptionsNn();
                $option_nn->setOptionId($option->getId());
                $option_nn->setOptionRowId($values['optRow']);
                $option_nn->save();

                // if status of the option is 1, update the hasSpecials flag of the corresponding category
                if ($values['status'] == 1) {
                    try {
                        $optionsRow = new Yourdelivery_Model_Meal_OptionRow($values['optRow']);
                        if ($optionsRow->getCategoryId() > 0) {
                            $category = new Yourdelivery_Model_Meal_Category($optionsRow->getCategoryId());
                            $category->updateHasSpecials();
                        }
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->error("Optionsgruppe oder Kategorie konnte nicht initialisiert werden, bitte 'Cache leeren' betätigen");
                    }
                }
            } else {
                $this->error($form->getMessages());
            }
        }

        $this->success('Option wurde erfolgreich erstellt!');

        $path = $this->session->optionspath;
        if (!is_null($path)) {
            return $this->_redirect($path);
        } else {
            return $this->_redirect('/restaurant/mealoptions');
        }
    }

    /**
     * create meal options in batch
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @modified 15.12.2011
     * @see YD-848
     * 
     * @return redirect
     */
    public function createbatchAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Restaurant_MealOptionsBatchEdit();
            if ($form->isValid($post)) {
                $names = explode(";", $form->getValue('names'));
                $values = $form->getValues();
                $values['cost'] = priceToInt2($values['cost']);

                if (sizeof($names) > 0) {
                    foreach ($names as $n) {
                        if (strlen(trim($n)) != 0) {
                            $option = new Yourdelivery_Model_Meal_Option();
                            $option->setName(trim($n));
                            $option->setData($values);
                            $option->setRestaurantId($restaurant->getId());
                            $option->save();

                            $option_nn = new Yourdelivery_Model_Meal_OptionsNn();
                            $option_nn->setOptionId($option->getId());
                            $option_nn->setOptionRowId($values['optRow']);
                            $option_nn->save();
                        }
                    }

                    // if status of the option is 1, update the hasSpecials flag of the corresponding category
                    if ($values['status'] == 1) {
                        try {
                            $optionsRow = new Yourdelivery_Model_Meal_OptionRow($values['optRow']);
                            if ($optionsRow->getCategoryId() > 0) {
                                $category = new Yourdelivery_Model_Meal_Category($optionsRow->getCategoryId());
                                $category->updateHasSpecials();
                            }
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->error("Optionsgruppe oder Kategorie konnte nicht initialisiert werden, bitte 'Cache leeren' betätigen");
                        }
                    }
                }
                $this->success('Optionen wurden erfolgreich erstellt!');
            } else {
                $this->error($form->getMessages());
            }
        }

        $path = $this->session->optionspath;
        if (!is_null($path)) {
            return $this->_redirect($path);
        } else {
            return $this->_redirect('/restaurant/mealoptions');
        }
    }

    /**
     * edit meal option
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @modified 15.12.2011
     * @see YD-848
     * 
     * @return redirect
     */
    public function editAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        try {
            $option = new Yourdelivery_Model_Meal_Option($request->getParam('id'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error('Diese Option gibt es nicht!');
            $path = $this->session->optionspath;
            if (!is_null($path)) {
                return $this->_redirect($path);
            } else {
                return $this->_redirect('/restaurant/mealoptions');
            }
        }

        if ($request->isPost()) {
            $post = $request->getPost();

            if ($request->getParam('cancel') !== null) {
                $path = $this->session->optionspath;
                if (!is_null($path)) {
                    return $this->_redirect($path);
                } else {
                    return $this->_redirect('/restaurant/mealoptions');
                }
            }

            $form = new Yourdelivery_Form_Restaurant_MealOptionEdit();
            if ($form->isValid($post)) {
                $optionStatus = $option->getStatus();
                $values = $form->getValues();
                $values['cost'] = priceToInt2($values['cost']);

                //save new data
                $option->setData($values);
                $option->save();

                // if online/offline status of the option has changed, update the hasSpecials flag for all meals of the corresponding category
                if ($optionStatus != $values['status']) {
                    try {
                        $optionsRow = new Yourdelivery_Model_Meal_OptionRow($option->getOptRowId());
                        if ($optionsRow->getCategoryId() > 0) {
                            $category = new Yourdelivery_Model_Meal_Category($optionsRow->getCategoryId());
                            $category->updateHasSpecials();
                        }
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->error($e->getMessage());
                    }
                }

                $this->success('Option wurde erfolgreich bearbeitet!');

                $path = $this->session->optionspath;
                if (!is_null($path)) {
                    return $this->_redirect($path);
                } else {
                    return $this->_redirect('/restaurant/mealoptions');
                }
            } else {
                $this->error($form->getMessages());
                return $this->_redirect('/restaurant_options/edit/id/' . $option->getId());
            }
        }

        $this->view->assign('option', $option);
        $this->view->assign('restaurant', $restaurant);
    }

    /**
     * delete meal option
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @modified 15.12.2011
     * @see YD-848
     * 
     * @return redirect
     */
    public function deleteAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('id', false)) {

            try {
                $option = new Yourdelivery_Model_Meal_Option($request->getParam('id'));
                $optionsRow = new Yourdelivery_Model_Meal_OptionRow($option->getOptRowId());

                $restaurant->deleteMealOption($request->getParam('id'));
                $this->logger->adminInfo(sprintf('Option %d vom Restaurant %d wurde gelöscht', $request->getParam('id', false), $restaurant->getId()));

                // update flag for all meals of the corresponding category
                if ($optionsRow->getCategoryId() > 0) {
                    $category = new Yourdelivery_Model_Meal_Category($optionsRow->getCategoryId());
                    $category->updateHasSpecials();
                }

                $this->success('Option wurde gelöscht!');
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error($e->getMessage());
            }
        }
        $path = $this->session->optionspath;
        if (!is_null($path)) {
            return $this->_redirect($path);
        } else {
            return $this->_redirect('/restaurant/mealoptions');
        }
    }

    //options group management

    /**
     * create meal options group
     * 
     * @author Alex Vait <vait@lieferando.de>
     * 
     * @return redirect
     */
    public function creategroupAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();
        $formOptionRow = new Yourdelivery_Form_Restaurant_MealOptionsRowEdit();
        $formOptionRow->setService($restaurant);

        if ($request->isPost()) {
            $post = $request->getPost();
            if ($formOptionRow->isValid($post)) {
                $values = $formOptionRow->getValues();
                $optionsRow = new Yourdelivery_Model_Meal_OptionRow();
                $optionsRow->setData($values);
                $optionsRow->setRestaurantId($restaurant->getId());
                $optionsRow->save();
                $this->success('Optionsgruppe wurde erfolgreich erstellt!');
            } else {
                $this->error($formOptionRow->getMessages());
            }
        }
        
        $path = $this->session->optionrowsspath;
        if (!is_null($path)) {
            return $this->_redirect($path);
        } else {
            return $this->_redirect('/restaurant/mealoptionrows');
        }
    }

    /**
     * edit meal options group
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @modified 15.12.2011
     * @see YD-848
     * 
     * @return redirect
     */
    public function editgroupAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            $path = $this->session->optionrowsspath;
            if (!is_null($path)) {
                return $this->_redirect($path);
            } else {
                return $this->_redirect('/restaurant/mealoptionrows');
            }
        }

        //create options group object
        try {
            $optionsRow = new Yourdelivery_Model_Meal_OptionRow($request->getParam('id'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error('Diese Gruppe gibt es nicht!');

            $path = $this->session->optionrowsspath;
            if (!is_null($path)) {
                return $this->_redirect($path);
            } else {
                return $this->_redirect('/restaurant/mealoptionrows');
            }
        }
        
        $form = new Yourdelivery_Form_Restaurant_MealOptionsRowEdit();
        $form->setService($restaurant);
        $form->setAction(sprintf('/restaurant_options/editgroup/id/%d', $request->getParam('id')));
        $form->populate($optionsRow->getData());
        
        if ($request->isPost()) {
            $post = $request->getPost();

            if ($form->isValid($post)) {
                $oldCategory = $optionsRow->getCategory();
                $oldCategoryId = $optionsRow->getCategoryId();
                
                $values = $form->getValues();
                $optionsRow->setData($values);
                $optionsRow->save();

                /**
                 * if there is no category, we cant't update specials of old category
                 */
                if (!is_null($oldCategory)) {
                    $oldCategory->updateHasSpecials();
                }

                if ( ($values['categoryId'] != 0) && ($oldCategoryId != $values['categoryId'])) {
                    try {
                        $category = new Yourdelivery_Model_Meal_Category($values['categoryId']);
                        $category->updateHasSpecials();
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->error("Kategorie kontne nicht aktualisiert werden, bitte 'Cache leeren' betätigen");
                    }
                }

                $this->success('Optionsgruppe wurde erfolgreich bearbeitet!');
                $this->logger->adminInfo(sprintf('Optionsgruppe %s (%d) vom Restaurant %d wurde bearbeitet', $optionsRow->getName(), $optionsRow->getId(), $restaurant->getId()));

                $path = $this->session->optionrowsspath;
                if (!is_null($path)) {
                    return $this->_redirect($path);
                } else {
                    return $this->_redirect('/restaurant/mealoptionrows');
                }
            } else {
                $this->error($form->getMessages());
            }
        }

        $this->view->form = $form;
        $this->view->assign('optionsgroup', $optionsRow);
        $this->view->assign('restaurant', $restaurant);
    }

    /**
     * delete meal options group
     *  
     * @author Alex Vait <vait@lieferando.de>
     * @modified 15.12.2011
     * @see YD-848
     * 
     * @return redirect
     */
    public function deletegroupAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        $id = $request->getParam('id', false);

        try {
            $optionsRow = new Yourdelivery_Model_Meal_OptionRow($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error($e->getMessage());
            $path = $this->session->optionrowsspath;
            if (!is_null($path)) {
                return $this->_redirect($path);
            } else {
                return $this->_redirect('/restaurant/mealoptionrows');
            }
        }

        $categoryId = $optionsRow->getCategoryId();
        $meals = $optionsRow->getMeals();

        if ($id) {
            Yourdelivery_Model_DbTable_Meal_OptionsRows::removeById($id);
            $this->logger->adminInfo(sprintf('Optionsgruppe %d vom Restaurant %d wurde gelöscht', $id, $restaurant->getId()));
            $this->success('Optionsgruppe wurde gelöscht!');
        }

        // update hasSpecials flag for all meals of this category
        try {
            if ($categoryId > 0) {
                $category = new Yourdelivery_Model_Meal_Category($categoryId);
                $category->updateHasSpecials();
            }
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }

        // update hasSpecials flag for all meals that has separate association with this option row
        foreach ($meals as $m) {
            $m->updateHasSpecials();
        }
        
        return $this->_redirect('/restaurant/mealoptionrows');
    }

    /**
     * change selected options
     * 
     * @since 09.11.2010
     * @author alex
     * 
     * @return redirect
     */
    public function changeselectedAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $cost = $request->getParam('cost', null);
            $isOnline = $request->getParam('status', null);
            $mwst = $request->getParam('mwst', null);
            $selectedOptions = $post['yd-id-checkbox'];

            if (is_array($selectedOptions)) {
                foreach ($selectedOptions as $optionId => $val) {
                    // "delete" button was pressed, so delete selected options
                    if (isset($post['deleteSelected'])) {
                        $restaurant->deleteMealOption($optionId);
                    } else {
                        // test if we should change this fields
                        $option = new Yourdelivery_Model_Meal_Option($optionId);

                        if ($isOnline != -1) {
                            $option->setStatus($isOnline);
                        }

                        if ($mwst != -1) {
                            $option->setMwst($mwst);
                        }

                        if (strcmp($cost, 'Nicht ändern') != 0) {
                            $option->setCost(priceToInt2($cost));
                        }

                        $option->save();
                    }
                }

                if (isset($post['deleteSelected'])) {
                    $this->success('Markierte Optionen wurden gelöscht');
                } else {
                    $this->success('Markierte Optionen wurden geändert');
                }
            }

            // return to the saved path or to the default url
            $path = $this->session->optionspath;
            if (!is_null($path)) {
                return $this->_redirect($path);
            } else {
                return $this->_redirect('/restaurant/mealoptions');
            }
        }
    }

    /**
     * delete selected option groups
     * 
     * @since 21.09.2010
     * @author alex
     * 
     * @return redirect
     */
    public function deleteselectedgroupsAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $selectedOptionGroups = $post['yd-id-checkbox'];

            if (is_array($selectedOptionGroups)) {
                foreach ($selectedOptionGroups as $groupId => $val) {
                    Yourdelivery_Model_DbTable_Meal_OptionsRows::removeById($groupId);
                    $this->logger->adminInfo(sprintf('Optionsgruppe %d vom Restaurant %d wurde gelöscht', $groupId, $restaurant->getId()));
                }
            }

            $this->success('Gruppen wurden gelöscht');

            // return to the saved path or to the default url
            $path = $this->session->optionrowsspath;
            if (!is_null($path)) {
                return $this->_redirect($path);
            } else {
                return $this->_redirect('/restaurant/mealoptionrows');
            }
        }
    }

    /**
     * clone meal options group
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @modified 15.12.2011
     * @see YD-848
     * 
     * @return redirect
     */
    public function clonegroupAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            //create options group object
            try {
                $optionsRow = new Yourdelivery_Model_Meal_OptionRow($post['optRow']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error($e->getMessage());
                $path = $this->session->optionrowsspath;
                if (!is_null($path)) {
                    return $this->_redirect($path);
                } else {
                    return $this->_redirect('/restaurant/mealoptionrows');
                }
            }

            if ($optionsRow->getCategoryId() == $post['categoryId']) {
                $this->error('Die Optionsgruppe gehört bereits zu dieser Kategorie');
                $path = $this->session->optionrowsspath;
                if (!is_null($path)) {
                    return $this->_redirect($path);
                } else {
                    return $this->_redirect('/restaurant/mealoptionrows');
                }
            }

            $optionsRow->duplicate($post['categoryId']);
            
            try {
                if ($post['categoryId'] > 0) {
                    $category = new Yourdelivery_Model_Meal_Category($post['categoryId']);
                    $category->updateHasSpecials();
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error("Kategorie konnte nicht aktualisiert werden, bitte 'Cache leeren' betätigen");
            }
        }

        $this->success('Gruppe wurde erfolgreich kopiert');
        $path = $this->session->optionrowsspath;
        if (!is_null($path)) {
            return $this->_redirect($path);
        } else {
            return $this->_redirect('/restaurant/mealoptionrows');
        }
    }

}

?>
