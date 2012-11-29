<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * meal categories management
 *
 * @author alex
 */
class Restaurant_CategoriesController extends Default_Controller_RestaurantBase {
    /**
     * create meal category
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

            $form = new Yourdelivery_Form_Restaurant_MealCategoryEdit();
            if($form->isValid($post)) {
                $values = $form->getValues();
                $values['restaurantId'] = $restaurant->getId();
                
                $category = new Yourdelivery_Model_Meal_Category();

                $category->setData($values);                
                $maxRank = Yourdelivery_Model_DbTable_Meal_Categories::getMaxRank($restaurant->getId());
                $category->setRank($maxRank + 1);
                $category->save();

                $serviceTypes = $post['servicetypes'];

                foreach ($serviceTypes as $st) {
                    $servicetype_cat = new Yourdelivery_Model_Servicetype_MealCategorysNn();
                    $servicetype_cat->setServicetypeId($st);
                    $servicetype_cat->setMealCategoryId($category->getid());
                    $servicetype_cat->save();
                }                
            }
            else {
                $this->error($form->getMessages());
            }
        }
        $this->_redirect('/restaurant/mealcategories');
    }

    /**
     * edit meal category
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
            $category = new Yourdelivery_Model_Meal_Category($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error('Diese Kategorie gibt es nicht!');
            $this->_redirect('/restaurant/mealcategories');
        }

        if ($restaurant->getId() != $category->getRestaurantId() ) {
            $this->error('Diese Kategorie gibt es nicht bei diesem Restaurant!');
            $this->_redirect('/restaurant/mealcategories');
        }

        if ( $request->isPost() ) {
            $post = $request->getPost();

            //edit category
            if ($request->getParam('cancel') !== null) {
                
                //get path so the sorting and filtering will stay when we edit some meal category
                $path = $this->session->mealcategoriespath;
                if (!is_null($path)) {
                    $this->_redirect($path);
                }
                else {
                    $this->_redirect('/restaurant/mealcategories');
                }
            }

            $form = new Yourdelivery_Form_Restaurant_MealCategoryEdit();

            if($form->isValid($post)) {
                $values = $form->getValues();

                $category->setData($values);
                $category->save();

                // set the same mwst for all meals in the category
                if ($post['mwstForSizes'] == 1) {
                    foreach ($category->getMealsSorted() as $meal) {
                        $meal->setMwst($post['mwst']);
                        $meal->save();
                    }
                }

                $serviceTypes = $post['servicetypes'];

                // remove all servicetypes of this category from the restaurant if this was the last category of this type
                Yourdelivery_Model_Servicetype_Servicetype::removeIfLastServicetypeOfMealCategories($category->getId());

                // it's easier and quicker to remove all associations and add new ones, than to check for existing
                Yourdelivery_Model_DbTable_Servicetypes_MealCategorysNn::removeByMealCategoryId($category->getId());

                if (is_array($serviceTypes)) {
                    foreach ($serviceTypes as $st) {
                        $servicetype_cat = new Yourdelivery_Model_Servicetype_MealCategorysNn();
                        $servicetype_cat->setServicetypeId($st);
                        $servicetype_cat->setMealCategoryId($category->getid());
                        $servicetype_cat->save();
                    }
                }

                $this->success('Kategorie erfolgreich bearbeitet!');
                $this->logger->adminInfo(sprintf('Kategorie "%s" (%d) vom Restaurant %d wurde geändert', $category->getName(), $category->getId(), $restaurant->getId()));

                //get path so the sorting and filtering will stay when we edit some meal category
                $path = $this->session->mealcategoriespath;
                if (!is_null($path)) {
                    $this->_redirect($path);
                }
                else {
                    $this->_redirect('/restaurant/mealcategories');
                }
            }
            else {
                $this->error($form->getMessages());
                $this->_redirect('/restaurant_categories/edit/id/' . $category->getId());
            }
        }

        $this->view->assign('restaurant', $restaurant);
        $this->view->assign('category', $category);
        $this->view->assign('allcategories', Yourdelivery_Model_DbTable_Meal_Categories::getCategories($restaurant->getId()));
    }

    /**
     * delete meal category
     * @author alex
     */
    public function deleteAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->getParam('id', false)) {
            $restaurant->deleteMealCategory($request->getParam('id'));
            $this->logger->adminInfo(sprintf('Kategorie %d vom Restaurant %d wurde gelöscht', $request->getParam('id'), $restaurant->getId()));
            $this->success('Kategorie wurde gelöscht');
        }

        //get path so the sorting and filtering will stay when we edit some meal category
        $path = $this->session->mealcategoriespath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/mealcategories');
        }
    }

    /**
     * move category rank up
     * @author alex
     */
    public function rankupAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();
        $cid = $request->getParam('id', false);

        if ($cid) {
            $restaurant->upMealCategory($cid);
        }

        $this->_redirect('/restaurant/mealcategories/#cat_' . $cid);
    }

    /**
     * move category rank down
     * @author alex
     */
    public function rankdownAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();
        $cid = $request->getParam('id', false);

        if ($cid) {
            $restaurant->downMealCategory($cid);
        }

        $this->_redirect('/restaurant/mealcategories/#cat_' . $cid);
    }

    /**
     * duplicate this category for this restaurant
     * @author alex
     * @since 09.11.2011
     */
    public function duplicateAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();
        $cid = $request->getParam('id', false);

        if (!$cid) {
            $this->_redirect('/restaurant/mealcategories/');
        }
        
        try {
            $category = new Yourdelivery_Model_Meal_Category($cid);
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error('Diese Kategorie gibt es nicht!');
            $this->_redirect('/restaurant/mealcategories/');
        }
        
        $newCategory = $category->duplicate();

        $this->success('Kategorie wurde dupliziert');
        $this->_redirect('/restaurant/mealcategories/');
    }    
}
?>
