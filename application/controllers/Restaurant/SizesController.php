<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * meal sizes management
 *
 * @author alex
 */
class Restaurant_SizesController extends Default_Controller_RestaurantBase {
    /**
     * create meal size
     * @author alex
     */
    public function createAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Restaurant_MealSizeCreate();
            if($form->isValid($post)) {
                $values = $form->getValues();

                //checked must be online and online is 0
                $values['status'] = !$values['status'];
                $size = new Yourdelivery_Model_Meal_Sizes();
                $size->setRank(Yourdelivery_Model_DbTable_Meal_Sizes::getMaxRank($values['categoryId']) + 1);
                $size->setData($values);
                $size->save();
            }
            else {
                $this->error($form->getMessages());
            }
        }
        $this->_redirect('/restaurant/mealsizes/cat/' . $values['categoryId']);
    }

    /**
     * edit meal size
     * @author alex
     */
    public function editAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        //create category object
        try {
            $size = new Yourdelivery_Model_Meal_Sizes($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error('Diese Größe gibt es nicht!');
            $this->_redirect('/restaurant/mealsizes');
        }

        if ( $request->isPost() ) {
            $post = $request->getPost();

            //edit size
            if ($request->getParam('cancel') !== null) {
                //get path so the sorting and filtering will stay when we edit some meal size
                $path = $this->session->mealsizespath;
                if (!is_null($path)) {
                    $this->_redirect($path);
                }
                else {
                    $this->_redirect('/restaurant/mealsizes');
                }
            }

            $form = new Yourdelivery_Form_Restaurant_MealSizeEdit();

            if($form->isValid($post)) {
                $values = $form->getValues();

                $size->setData($values);
                $size->save();

                $this->success('Größe erfolgreich bearbeitet!');
                $this->logger->adminInfo(sprintf('Größe "%s" (%d) vom Restaurant %d wurde geändert', $size->getName(), $size->getId(), $restaurant->getId()));

                //get path so the sorting and filtering will stay when we edit some meal size
                $path = $this->session->mealsizespath;
                if (!is_null($path)) {
                    $this->_redirect($path);
                }
                else {
                    $this->_redirect('/restaurant/mealsizes');
                }
            }
            else {
                $this->error($form->getMessages());
                $this->_redirect('/restaurant_sizes/edit/id/' . $size->getId());
            }
        }

        $this->view->assign('size', $size);
        $this->view->assign('restaurant', $restaurant);
    }

    /**
     * delete meal size
     * @author alex
     */
    public function deleteAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('id', false)) {
            Yourdelivery_Model_DbTable_Meal_Sizes::remove($request->getParam('id'));
        }

        $this->success('Größe wurde gelöscht');

        //get path so the sorting and filtering will stay when we edit some meal size
        $path = $this->session->mealsizespath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/mealsizes');
        }
    }

    /**
     * move size left (size rank up)
     * @author alex
     */
    public function sizeleftAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('id', false)) {
            $restaurant->moveMealSizeLeft($request->getParam('id', null));
        }

        //get path so the sorting and filtering will stay when we edit some meal size
        $path = $this->session->mealsizespath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/mealsizes');
        }
    }

    /**
     * move size right (size rank down)
     * @author alex
     */
    public function sizerightAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('id', false)) {
            $restaurant->moveMealSizeRight($request->getParam('id', null));
        }

        //get path so the sorting and filtering will stay when we edit some meal size
        $path = $this->session->mealsizespath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/mealsizes');
        }
    }
}
?>
