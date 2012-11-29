<?php

/**
 * meal extras management
 *
 * @author alex
 */
class Restaurant_ExtrasController extends Default_Controller_RestaurantBase {

    /**
     * create meal extra
     * @author alex
     */
    public function createAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Restaurant_MealExtraEdit();
            if ($form->isValid($post)) {
                $extra = new Yourdelivery_Model_Meal_Extra();
                $extra->setData($form->getValues());
                $extra->setRestaurantId($restaurant->getId());
                $extra->save();
                $this->success('Extra wurde erfolgreich erstellt!');
            } else {
                $this->error($form->getMessages());
            }
        }

        // return to the saved path or to the default url
        $path = $this->session->extrasspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        } else {
            $this->_redirect('/restaurant/mealextras');
        }
    }

    /**
     * create meal extras in batch
     * @author alex
     */
    public function createbatchAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Restaurant_MealExtraBatchEdit();
            if ($form->isValid($post)) {
                $names = explode(";", $form->getValue('names'));

                if (sizeof($names) > 0) {
                    foreach ($names as $n) {
                        if (strlen(trim($n)) != 0) {
                            $extra = new Yourdelivery_Model_Meal_Extra();
                            $extra->setData($form->getValues());
                            $extra->setName(trim($n));
                            $extra->setRestaurantId($restaurant->getId());
                            $extra->save();
                        }
                    }
                }
                $this->success('Extras wurden erfolgreich erstellt!');
            } else {
                $this->error($form->getMessages());
            }
        }

        // return to the saved path or to the default url
        $path = $this->session->extrasspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        } else {
            $this->_redirect('/restaurant/mealextras');
        }
    }

    /**
     * create meal extras in batch from available extras names
     * @author alex
     * @since 07.10.2010
     */
    public function createbatchfromavailableAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $names = $post['extras_names'];

            if (sizeof($names) > 0) {
                foreach ($names as $n => $val) {
                    if (strlen(trim($n)) != 0) {
                        $extra = new Yourdelivery_Model_Meal_Extra();
                        $extra->setName(trim($n));
                        $extra->setMwst($post['mwst']);
                        $extra->setGroupId($post['groupId']);
                        $extra->setStatus($post['status']);
                        $extra->setRestaurantId($restaurant->getId());
                        $extra->save();
                    }
                }
            }
        }

        // return to the saved path or to the default url
        $path = $this->session->extrasspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        } else {
            $this->_redirect('/restaurant/mealextras');
        }
    }

    /**
     * edit meal extra
     * @author alex
     */
    public function editAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            // return to the saved path or to the default url
            $path = $this->session->extrasspath;
            if (!is_null($path)) {
                $this->_redirect($path);
            } else {
                $this->_redirect('/restaurant/mealextras');
            }
        }

        //create extra object
        try {
            $extra = new Yourdelivery_Model_Meal_Extra($request->getParam('id'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error('Diese Extra gibt es nicht!');

            // return to the saved path or to the default url
            $path = $this->session->extrasspath;
            if (!is_null($path)) {
                $this->_redirect($path);
            } else {
                $this->_redirect('/restaurant/mealextras');
            }
        }

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Restaurant_MealExtraEdit();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                //save new data
                $extra->setData($values);
                $extra->save();

                $this->success('Extra wurde erfolgreich bearbeitet!');

                // return to the saved path or to the default url
                $path = $this->session->extrasspath;
                if (!is_null($path)) {
                    $this->_redirect($path);
                } else {
                    $this->_redirect('/restaurant/mealextras');
                }
            } else {
                $this->error($form->getMessages());
                $this->_redirect('/restaurant_extras/edit/id/' . $extra->getId());
            }
        }

        $this->view->assign('extra', $extra);
        $this->view->assign('restaurant', $restaurant);
    }

    /**
     * delete meal extra
     * @author alex
     */
    public function deleteAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('id', false)) {
            $restaurant->deleteMealExtra($request->getParam('id'));
            $this->logger->adminInfo(sprintf('Extra %d vom Restaurant %d wurde gelöscht', $request->getParam('id'), $restaurant->getId()));
            $this->success('Extra wurde gelöscht!');

            // return to the saved path or to the default url
            $path = $this->session->extrasspath;
            if (!is_null($path)) {
                $this->_redirect($path);
            } else {
                $this->_redirect('/restaurant/mealextras');
            }
        }
    }

    //extras group management

    /**
     * create meal extras group
     * @author alex
     */
    public function creategroupAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Restaurant_MealExtraGroupEdit();
            if ($form->isValid($post)) {
                $extrasGroup = new Yourdelivery_Model_Meal_ExtrasGroups();
                $extrasGroup->setData($form->getValues());
                $extrasGroup->setRestaurantId($restaurant->getId());
                $extrasGroup->save();
                $this->success('Gruppe wurde erfolgreich erstellt!');
            } else {
                $this->error($form->getMessages());
            }
        }

        // return to the saved path or to the default url
        $path = $this->session->extrasgroupsspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        } else {
            $this->_redirect('/restaurant/mealextrasgroups');
        }
    }

    /**
     * edit meal extras group
     * @author alex
     */
    public function editgroupAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            // return to the saved path or to the default url
            $path = $this->session->extrasgroupsspath;
            if (!is_null($path)) {
                $this->_redirect($path);
            } else {
                $this->_redirect('/restaurant/mealextrasgroups');
            }
        }

        //create extras group object
        try {
            $mealgroup = new Yourdelivery_Model_Meal_ExtrasGroups($request->getParam('id'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error('Diese Gruppe gibt es nicht!');

            // return to the saved path or to the default url
            $path = $this->session->extrasgroupsspath;
            if (!is_null($path)) {
                $this->_redirect($path);
            } else {
                $this->_redirect('/restaurant/mealextrasgroups');
            }
        }



        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Restaurant_MealExtraGroupEdit();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                //save new data
                $mealgroup->setData($values);
                $mealgroup->save();

                $this->success('Extras Gruppe wurde erfolgreich bearbeitet!');
                $this->logger->adminInfo(sprintf('Extras Gruppe "%s" (%d) vom Restaurant %d wurde bearbeitet', $mealgroup->getName(), $mealgroup->getId(), $restaurant->getId()));

                // return to the saved path or to the default url
                $path = $this->session->extrasgroupsspath;
                if (!is_null($path)) {
                    $this->_redirect($path);
                } else {
                    $this->_redirect('/restaurant/mealextrasgroups');
                }
            } else {
                $this->error($form->getMessages());
                $this->_redirect('/restaurant_extras/editgroup/id/' . $mealgroup->getId());
            }
        }

        $this->view->assign('restaurant', $restaurant);
        $this->view->assign('extrasgroup', $mealgroup);
    }

    /**
     * delete meal extra group
     * @author alex
     */
    public function deletegroupAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        $id = $request->getParam('id', false);
        if ($id) {
            $group = new Yourdelivery_Model_Meal_ExtrasGroups($id);
            if ($group->getExtras()->count() > 0) {
                $this->error("Nur eine leere Gruppe kann gelöscht werden");
            } else {
                $restaurant->deleteMealExtraGroup($id);
                $this->logger->adminInfo(sprintf('Extras Gruppe "%s" (%d) vom Restaurant %d wurde gelöscht', $group->getName(), $group->getId(), $restaurant->getId()));
                $this->success('Extras Gruppe wurde gelöscht!');
            }
        }

        // return to the saved path or to the default url
        $path = $this->session->extrasgroupsspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        } else {
            $this->_redirect('/restaurant/mealextrasgroups');
        }
    }

    /**
     * delete or change selected extras
     * @since 20.09.2010
     * @author alex
     */
    public function changeselectedAction() {

        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            $isOnline = $request->getParam('status');
            $mwst = $request->getParam('mwst');

            $selectedExtras = $post['yd-id-checkbox'];
            if (is_array($selectedExtras)) {
                foreach ($selectedExtras as $extraId => $val) {
                    if (isset($post['deleteSelected'])) {
                        $restaurant->deleteMealExtra($extraId);
                    } else {
                        // test if we should change this fields
                        $extra = new Yourdelivery_Model_Meal_Extra($extraId);

                        if ($isOnline != -1) {
                            $extra->setStatus($isOnline);
                        }

                        if ($mwst != -1) {
                            $extra->setMwst($mwst);
                        }

                        $extra->save();
                    }
                }
            }

            if (isset($post['deleteSelected'])) {
                $this->success('Markierte Extras wurden gelöscht');
            } else {
                $this->success('Markierte Extras wurden geändert');
            }

            // return to the saved path or to the default url
            $path = $this->session->extrasspath;
            if (!is_null($path)) {
                $this->_redirect($path);
            } else {
                $this->_redirect('/restaurant/mealextras');
            }
        }
    }

    /**
     * delete selected extras groups
     * @since 21.09.2010
     * @author alex
     */
    public function deleteselectedgroupsAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();
 
        if ($request->isPost()) {
            $post = $request->getPost();

            $selectedExtrasGroups = $post['yd-id-checkbox'];

            foreach ($selectedExtrasGroups as $groupId => $val) {
                try {
                    $group = new Yourdelivery_Model_Meal_ExtrasGroups($groupId);
                    if ($group->getExtras()->count() > 0) {
                        $this->error(sprintf("Gruppe %s (#%d) kann gelöscht werden, die Gruppe ist nicht leer", $group->getName(), $group->getId()));
                    } else {
                        $restaurant->deleteMealExtraGroup($groupId);
                        $this->success(sprintf("Gruppe %s (#%d) wurde gelöscht", $group->getName(), $group->getId()));
                        $this->logger->adminInfo(sprintf('Extras Gruppe "%s" (%d) vom Restaurant %d wurde gelöscht', $group->getName(), $group->getId(), $restaurant->getId()));
                    }
                } 
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {                    
                }
            }

            // return to the saved path or to the default url
            $path = $this->session->extrasgroupsspath;
            if (!is_null($path)) {
                $this->_redirect($path);
            } else {
                $this->_redirect('/restaurant/mealextrasgroups');
            }
        }
    }

}

?>
