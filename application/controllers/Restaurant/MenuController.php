<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * menu management
 *
 * @author alex
 */
class Restaurant_MenuController extends Default_Controller_RestaurantBase {

    /**
     * form for managing extras for meal
     * @author Alex Vait <vait@lieferando.de>
     * @modified 15.12.2011
     * @see YD-848
     */
    public function manageextrasAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
            $mealId = $post['mealId'];
            $sizeId = $post['sizeId'];

            // if some parameters missing, leave
            if (!$mealId || !$sizeId) {
                return;
            }

            $meal = new Yourdelivery_Model_Meals($mealId);

            //it's quicker to remove alles extras and add them again, than to iterate over all and check them
            $meal->removeExtrasForSize($sizeId);

            $costs = $post['cost'];

            if (is_array($costs)) {
                foreach ($costs as $extraId => $cost) {
                    if (strlen(trim($cost)) != 0) {
                        $rel = new Yourdelivery_Model_Meal_ExtrasRelations();
                        $rel->setExtraId($extraId);
                        $rel->setCategoryId(0);
                        $rel->setMealId($mealId);
                        $rel->setSizeId($sizeId);
                        $rel->setCost(priceToInt2($cost));
                        $rel->save();
                    }
                }
                $meal->updateHasSpecials();
            }
        }
    }

    /**
     * form for managing extras for size
     * @author Alex Vait <vait@lieferando.de>
     * @modified 15.12.2011
     * @see YD-848
     */
    public function manageextrassizeAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $sizeId = $post['sizeId'];
            $categoryId = $post['categoryId'];

            if (!$categoryId || !$sizeId) {
                return;
            }

            $category = new Yourdelivery_Model_Meal_Category($categoryId);
            $category->removeExtrasForSize($sizeId);

            $costs = $post['cost'];

            if (is_null($costs)) {
                return;
            }
            
            foreach ($costs as $extraId => $cost) {
                //remove all extras relation with meal of this category, it quicker than checking each category-extra relation
                foreach ($category->getMealsAsObjects() as $meal) {
                    Yourdelivery_Model_DbTable_Meal_ExtrasRelations::removeByExtraMealSize($extraId, $meal->getId(), $sizeId);
                }

                if (strlen(trim($cost)) != 0) {
                    $rel = new Yourdelivery_Model_Meal_ExtrasRelations();
                    $rel->setExtraId($extraId);
                    $rel->setCategoryId($categoryId);
                    $rel->setSizeId($sizeId);
                    $rel->setMealId(0);
                    $rel->setCost(priceToInt2($costs[$extraId]));
                    $rel->save();
                }
            }

            $category->updateHasSpecials();
        }
    }

    /**
     * form for adding new extras for a size
     * @author alex
     */
    public function extrasforsizeAction() {
        $request = $this->getRequest();

        $restaurantId = $request->getParam('restaurantId');
        $categoryId = $request->getParam('categoryId');
        $sizeId = $request->getParam('sizeId');

        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        $category = new Yourdelivery_Model_Meal_Category($categoryId);
        $size = new Yourdelivery_Model_Meal_Sizes($sizeId);

        $this->view->assign('restaurant', $restaurant);
        $this->view->assign('category', $category);
        $this->view->assign('size', $size);
    }

    /**
     * form for adding new extras to the meal
     * @author alex
     */
    public function extrasformealAction() {
        $request = $this->getRequest();

        $restaurantId = $request->getParam('restaurantId');
        $categoryId = $request->getParam('categoryId');
        $sizeId = $request->getParam('sizeId');
        $mealId = $request->getParam('mealId');

        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        $category = new Yourdelivery_Model_Meal_Category($categoryId);
        $size = new Yourdelivery_Model_Meal_Sizes($sizeId);
        $meal = new Yourdelivery_Model_Meals($mealId);

        $this->view->assign('restaurant', $restaurant);
        $this->view->assign('category', $category);
        $this->view->assign('size', $size);
        $this->view->assign('meal', $meal);
    }

    /**
     * form for managing option for the category
     * @author alex
     */
    public function optionsformealAction() {
        $request = $this->getRequest();

        $mealId = $request->getParam('mealId');
        $categoryId = $request->getParam('categoryId');
        $restaurantId = $request->getParam('restaurantId');

        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        $meal = new Yourdelivery_Model_Meals($mealId);

        $this->view->assign('meal', $meal);
        $this->view->assign('categoryId', $categoryId);
        $this->view->assign('restaurant', $restaurant);
    }

    /**
     * managing options for the meal
     * @author Alex Vait <vait@lieferando.de>
     * @modified 15.12.2011
     * @see YD-848
     */
    public function manageoptionsmealAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
            $mealId = $post['mealId'];
            $restaurantId = $post['restaurantId'];

            if (!$mealId) {
                return;
            }

            $meal = new Yourdelivery_Model_Meals($mealId);
            $meal->removeAllOptions();

            $checked = $post['check'];

            if (!is_null($checked)) {
                foreach ($checked as $optionRowId => $on) {
                    $row = new Yourdelivery_Model_Meal_OptionsRowsNn();
                    $row->setRestaurantId($restaurantId);
                    $row->setMealId($mealId);
                    $row->setOptionRowId($optionRowId);
                    $row->save();
                }
            }

            $meal->updateHasSpecials();
        }
    }

    /**
     * preview of extras for a size
     * @author alex
     */
    public function previewsizeextrasAction() {
        $request = $this->getRequest();

        $restaurantId = $request->getParam('restaurantId');
        $sizeId = $request->getParam('sizeId');

        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        $size = new Yourdelivery_Model_Meal_Sizes($sizeId);
        $category = new Yourdelivery_Model_Meal_Category($size->getCategoryId());

        $this->view->assign('restaurant', $restaurant);
        $this->view->assign('category', $category);
        $this->view->assign('size', $size);
    }

    /**
     * preview of extras for a meal
     * @author alex
     */
    public function previewmealextrasAction() {
        $request = $this->getRequest();

        $restaurantId = $request->getParam('restaurantId');
        $sizeId = $request->getParam('sizeId');
        $mealId = $request->getParam('mealId');

        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        $size = new Yourdelivery_Model_Meal_Sizes($sizeId);
        $category = new Yourdelivery_Model_Meal_Category($size->getCategoryId());
        $meal = new Yourdelivery_Model_Meals($mealId);

        $this->view->assign('restaurant', $restaurant);
        $this->view->assign('category', $category);
        $this->view->assign('size', $size);
        $this->view->assign('meal', $meal);
    }

    /**
     * preview of option for the meal
     * @author alex
     */
    public function previewmealoptionsAction() {
        $request = $this->getRequest();

        $mealId = $request->getParam('mealId');
        $restaurantId = $request->getParam('restaurantId');

        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        $meal = new Yourdelivery_Model_Meals($mealId);
        $category = new Yourdelivery_Model_Meal_Category($meal->getCategoryId());

        $this->view->assign('meal', $meal);
        $this->view->assign('category', $category);
        $this->view->assign('restaurant', $restaurant);
    }

    /**
     * form for adding new extras for a size
     * @since 21.09.2010
     * @author alex
     */
    public function mealsforextraAction() {
        $request = $this->getRequest();

        $extraId = $request->getParam('extraId');

        $extra = new Yourdelivery_Model_Meal_Extra($extraId);
        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($extra->getRestaurantId());

        $this->view->assign('extra', $extra);
        $this->view->assign('restaurant', $restaurant);
    }

}