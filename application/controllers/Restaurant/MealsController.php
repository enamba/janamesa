<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * meals management
 *
 * @author alex
 */
class Restaurant_MealsController extends Default_Controller_RestaurantBase {

    /**
     * create meal
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

            $form = new Yourdelivery_Form_Restaurant_MealCreate();
            if($form->isValid($post)) {
                $values = $form->getValues();
                $values['restaurantId'] = $restaurant->getId();

                $values['status'] = !$values['status'];

                $meal = new Yourdelivery_Model_Meals();
                $meal->setData($values);
                $meal->setRank(Yourdelivery_Model_DbTable_Meals::getMaxRank($values['categoryId']) + 1);
                $meal->save();

                $path = $this->session->mealspath;
                if (!is_null($path)) {
                    $this->_redirect($path);
                }
                else {
                    $this->_redirect('/restaurant/meals');
                }
            }
            else {
                $this->error($form->getMessages());
            }
        }
        $this->_redirect('/restaurant/meals');
    }

    /**
     * edit meal
     * @author alex
     */
    public function editAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        //create meal object
        try {
            $meal = new Yourdelivery_Model_Meals($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error('Diese Speise gibt es nicht!');
            $this->_redirect('/restaurant/meals');
        }

        $this->view->assign('restaurant', $restaurant);
        $this->view->assign('meal', $meal);
        
        if ( $request->isPost() ) {
            if ($request->getParam('cancel') !== null) {
                //get path so the sorting and filtering will stay when we edit some meal
                $path = $this->session->mealspath;
                if (!is_null($path)) {
                    $this->_redirect($path);
                }
                else {
                    $this->_redirect('/restaurant/meals');
                }
            }

            $form = new Yourdelivery_Form_Restaurant_MealEdit();
            $post = $request->getPost();

            if($form->isValid($post)) {
                $values = $form->getValues();
                $meal->setData($values);
                $meal->save();

                $this->success('Speise erfolgreich bearbeitet!');
                
                //get path so the sorting and filtering will stay when we edit some meal
                $path = $this->session->mealspath;
                if (!is_null($path)) {
                    $this->_redirect($path);
                }
                else {
                    $this->_redirect('/restaurant/meals');
                }
            }
            else {
                $this->error($form->getMessages());
                $this->_redirect('/restaurant_meals/edit/id/' . $meal->getId());
            }
        }
        $this->view->assign('category', $meal->getCategory());
    }

    /**
     * delete meal
     * @author alex
     */
    public function deleteAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('id', false)) {
            $meal = new Yourdelivery_Model_Meals($request->getParam('id'));
            $meal->setDeleted(1);
            $meal->save();
            //if we wanted to really delete it from the database
            //Yourdelivery_Model_DbTable_Meals::remove($mealId);
            //Yourdelivery_Model_DbTable_Meal_SizesNn::removeByMeal($mealId);
        }

        $this->success('Speise wurde gelöscht');

        //get path so the sorting and filtering will stay when we edit some meal
        $path = $this->session->mealspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/meals');
        }
    }

    /**
     * get mwst of this category
     * @author alex
     */
    public function getcategorymwstAction(){
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        
        $request = $this->getRequest();

        try {
            $category = new Yourdelivery_Model_Meal_Category($request->getParam('categoryId'));
            echo $category->getMwst();
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
            echo -1;
        }
    }

    /**
     * move meal rank up
     * @author alex
     */
    public function movemealupAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('id', false)) {
            $restaurant->moveMealUp($request->getParam('id', null));
        }

        //get path so the sorting and filtering will stay when we edit some meal
        $path = $this->session->mealspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/meals');
        }
    }

    /**
     * move meal rank down
     * @author alex
     */
    public function movemealdownAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('id', false)) {
            $restaurant->moveMealDown($request->getParam('id', null));
        }

        //get path so the sorting and filtering will stay when we edit some meal
        $path = $this->session->mealspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/meals');
        }
    }

    /**
     * upload new images for meal
     * @author alex
     * @since 14.12.2010
     */
    public function uploadimageAction(){
        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();
            $form = new Yourdelivery_Form_Restaurant_MealPictureEdit();

            if ( $form->isValid($post) ) {
                $values = $form->getValues();

                $meal = new Yourdelivery_Model_Meals($values['mealId']);

                if($form->img->isUploaded() ) {
                    $fn = $form->img->getFileName();
                    $meal->setImg($form->img->getFileName());
                }
            }
            else {
                $this->error($form->getMessages());
            }
        }

        //get path so the sorting and filtering will stay when we edit some meal
        $path = $this->session->mealspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/meals');
        }
    }

    /**
     * remove images for meal
     * @author alex
     * @since 14.12.2010
     */
    public function removeimageAction(){
        $request = $this->getRequest();

        $mealId = $request->getParam('mealId', false);

        $meal = new Yourdelivery_Model_Meals($mealId);
        $meal->removeImg();

        //get path so the sorting and filtering will stay when we edit some meal
        $path = $this->session->mealspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/meals');
        }
    }

}
?>
