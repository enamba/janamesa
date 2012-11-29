<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Restaurant Controller
 *
 * @author vait
 */
class Request_RestaurantController extends Default_Controller_RequestRestaurantBase {

    /**
     * get meal and set it for the view
     * @author alex
     */
    public function getmealAction(){
        $request = $this->getRequest();
        if ( $request->isPost() ) {
            $post = $request->getPost();
            $mealId = (integer) $post['mealId'];
            if ($mealId > 0) {
                try {
                    $meal = new Yourdelivery_Model_Meals($mealId);
                    $this->view->assign('meal', $meal);

                    $category = new Yourdelivery_Model_Meal_Category($meal->getCategoryId());
                    $this->view->assign('category', $category);

                    $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($category->getRestaurantId());
                    $this->view->assign('restaurant', $restaurant);
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                    $this->logger->err(sprintf('Kann ein Objekt nicht erstellen'));
                }
            }
        }
    }

    /**
     * get category and set it for the view
     * @author alex
     */
    public function getcategoryAction(){
        $request = $this->getRequest();
        if ( $request->isPost() ) {
            $post = $request->getPost();
            $categoryId = (integer) $post['categoryId'];
            try {
                $category = new Yourdelivery_Model_Meal_Category($categoryId);
                $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($category->getRestaurantId());

                $this->view->assign('category', $category);
                $this->view->assign('restaurant', $restaurant);
                $this->view->assign('allcategories', Yourdelivery_Model_DbTable_Meal_Categories::getCategories($category->getRestaurantId()));

                $mealOptionRows = Yourdelivery_Model_DbTable_Meal_OptionsRows::findByRestaurantId($restaurant->getId());
                $this->view->assign('mealOptionRows', $mealOptionRows);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
            }
        }
    }

    /**
     * get category and set it for the view
     * @author alex
     */
    public function showcategorytableAction(){
        $request = $this->getRequest();
        if ( $request->isPost() ) {
            $post = $request->getPost();

            try {
                $category = new Yourdelivery_Model_Meal_Category($post['categoryId']);
                $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($category->getRestaurantId());

                $this->view->assign('category', $category);
                $this->view->assign('restaurant', $restaurant);
                $this->view->assign('isOpen', $post['isOpen']);
                $this->view->assign('allcategories', Yourdelivery_Model_DbTable_Meal_Categories::getCategories($category->getRestaurantId()));
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
            }
        }
    }

    /**
     * get updated list fo all categories
     * @author alex
     */
    public function getcategorieslistAction(){
        $request = $this->getRequest();
        if ( $request->isPost() ) {
            $post = $request->getPost();

            try {
                if (isset ($post['categoryId'])) {
                    $category = new Yourdelivery_Model_Meal_Category($post['categoryId']);
                    $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($category->getRestaurantId());
                    $this->view->assign('selectedCategory', $category);
                }
                else {
                    $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($post['restaurantId']);
                }

                $this->view->assign('restaurant', $restaurant);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
            }
        }
    }

    /**
     * toggle the "checked" value
     * @author alex
     * @since 05.10.2010
     */
    public function togglecheckedAction() {
        $request = $this->getRequest();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        if ($request->isPost()) {
            $restaurantId = $request->getParam('restaurantId', null);
            if (is_null($restaurantId)) {
                $this->logger->adminInfo(sprintf('Failed to create restaurant %d in request_restaurant/togglecheckedAction', $restaurantId));
                return;
            }

            $service = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);

            $status = $service->getChecked();
            $status = !$status;
            $service->setChecked($status);
            $service->save();

            $this->session_restaurant->currentRestaurant = $service;
            echo($status);
        }
    }

    /**
     * get updated list fo all categories
     * @author alex
     */
    public function markmenuAction(){
        $request = $this->getRequest();
        if ( $request->isPost() ) {
            $post = $request->getPost();

            if (isset($post['restaurantId'])) {
                try {
                    $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($post['restaurantId']);

                    // mark menu as new
                    if ($post['asnew'] == 1) {
                        $restaurant->setMenuUpdateTime(date("Y-m-d", time()));
                    }
                    // mark menu as old
                    else {
                        $restaurant->setMenuUpdateTime(0);
                    }
                    $restaurant->save();

                    if (is_null($restaurant->getMenuIsNewUntil())) {
                        $this->view->assign('msg', __b('Speisekarte ist nicht neu'));
                    }
                    else {
                        $this->view->assign('msg', __b('Speisekarte ist bis') . ' ' . date("d.m.Y", $restaurant->getMenuIsNewUntil()) . ' ' . __b('als neu markiert'));
                    }
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                    $this->logger->adminInfo(sprintf('Exception in request_restaurant/markmenuAction: %s', $e->getMessage()));
                }
            }
        }
    }

    /*
     * Lightbox for order editing options
     * @author alex
     * @since 05.01.2011
     * @return void
     */
    public function ordereditAction() {
        $request = $this->getRequest();

        $order = null;

        if ( $request->isPost() ) {
            $post = $request->getPost();

            $id = $post['orderId'];
            if (!is_null($id)) {
                try {
                    $order = new Yourdelivery_Model_Order($id);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    return;
                }
            }

            $action = $post['command'];

            // change the deliver time and set order to affirmed state
            if ( (strcmp($action, "setDeliverTime") == 0) && (strlen(trim($post['deliverTimeD']))>0) ) {
                $timeTH = substr($post['deliverTimeT'], 0, 2);
                $timeTM = substr($post['deliverTimeT'], 3, 2);

                // test data sanity
                if ( (intval($timeTH)>24) || (intval($timeTH)<0)) {
                    $timeTH = 0;
                }

                if ( (intval($timeTM)>59) || (intval($timeTM)<0)) {
                    $timeTM = 0;
                }

                $unixtime = mktime($timeTH, $timeTM, 0, substr($post['deliverTimeD'], 3, 2), substr($post['deliverTimeD'], 0, 2), substr($post['deliverTimeD'], 6));

                if ( !is_null($unixtime) && ($unixtime>0) ) {
                    $date = date('Y-m-d H:i:s', $unixtime);
                    $orderRow = $order->getRow();

                    if (!is_null($orderRow)) {
                        $orderRow['deliverTime'] = $date;
                        $orderRow['state'] = 1;
                        $orderRow->save();
                    }
                }

                $this->view->lightboxstate = 1;
            }
            // set the pfand time and set order to delivered state
            else if ( (strcmp($action, "setPfand")==0) && (strlen(trim($post['pfand']))>0) ) {

                $row = $order->getRow();
                $row->pfand = priceToInt2($post['pfand']);
                $row->save();

                $order->setStatus(Yourdelivery_Model_Order::DELIVERED,
                    new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::BACKEND_SET_DEPOSIT, $post['pfand'])
                );

                $this->logger->adminInfo(sprintf('Pfand was set to %s for order %d', $post['pfand'], $order->getId()));

                $this->view->lightboxstate = 1;
            }

        }
        else {
            $id = $request->getParam('orderId');
            if (!is_null($id)) {
                try {
                    $order = new Yourdelivery_Model_Order($id);
                    $this->view->order = $order;
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->view->order = null;
                    $this->view->lightboxstate = -1;
                    return;
                }
            }

            $this->view->state = 0;
        }
    }

    /**
     * remove meal category
     * @author alex
     */
    public function removecategoryAction(){
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();

        $cid = $request->getParam('categoryId');

        if ($request->isPost()) {
            try {
                $category = new Yourdelivery_Model_Meal_Category($cid);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Failed to create category %d in request_restaurant/removecategoryAction', $cid));
                return;
            }

            try {
                $restaurant =  new Yourdelivery_Model_Servicetype_Restaurant($category->getRestaurantId());
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Failed to create restaurant %d in request_restaurant/removecategoryAction', $category->getRestaurantId()));
                return;
            }

            $restaurant->deleteMealCategory($cid);
            $this->logger->adminInfo(sprintf('Kategorie "%s" (%d) vom Restaurant %d wurde gelöscht', $category->getName(), $category->getId(), $category->getRestaurantId()));
            echo 1;
        }
    }

    /**
     * remove meal
     * @author alex
     */
    public function removemealAction(){
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();

        if ($request->isPost()) {
            try {
                $meal = new Yourdelivery_Model_Meals($request->getParam('mealId'));
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Failed to create meal %d in request_restaurant/removemealAction', $request->getParam('mealId')));
                echo $e->getMessage();
                return;
            }

            Yourdelivery_Model_DbTable_Meals::remove($meal->getId());
            $this->logger->adminInfo(sprintf('Speise "%s" (%d) vom Restaurant %d wurde gelöscht', $meal->getName(), $meal->getId(), $meal->getRestaurantId()));
            echo 1;
        }
    }

    /**
     * remove meal size
     * @author alex
     */
    public function removesizeAction(){
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();

        if ($request->isPost()) {
            try {
                $size = new Yourdelivery_Model_Meal_Sizes($request->getParam('sizeId'));
                Yourdelivery_Model_DbTable_Meal_Sizes::remove($size->getId());
                $this->logger->adminInfo(sprintf('Speisegröße "%s" (%d) wurde gelöscht', $size->getName(), $size->getId()));
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Exception in request_restaurant/removesizeAction : %s', $e->getMessage()));
                return;
            }
            echo 1;
        }
    }

    /**
     * add new meal category
     * @author alex
     */
    public function addcategoryAction(){
        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();

            $restaurantId = $post['restaurantId'];

            try {
                $category = new Yourdelivery_Model_Meal_Category();

                $category->setName(stripslashes($post['name']));
                $category->setDescription(stripslashes($post['description']));
                $category->setParentMealCategoryId(stripslashes($post['parentMealCategoryId']));
                $category->setRestaurantId($restaurantId);
                $category->setMwst($post['mwst']);
                $category->setHasPfand(isset($post['hasPfand']));
                $category->setExcludeFromMinCost(isset($post['excludeFromMinCost']));
                $category->setFrom($post['from']);
                $category->setTo($post['until']);

                $wdbinary = 0;
                foreach ($post['weekdays'] as $wd) {
                    $wdbinary += pow(2, $wd-1);
                }

                $category->setWeekdays($wdbinary);

                $maxRank = Yourdelivery_Model_DbTable_Meal_Categories::getMaxRank($restaurantId);
                $category->setRank($maxRank + 1);

                $category->save();

                $serviceTypes = $post['servicetypes'];

                if (is_array($serviceTypes)) {
                    foreach ($serviceTypes as $st) {
                        $servicetype_cat = new Yourdelivery_Model_Servicetype_MealCategorysNn();
                        $servicetype_cat->setServicetypeId($st);
                        $servicetype_cat->setMealCategoryId($category->getId());
                        $servicetype_cat->save();
                    }
                }

                // add new default size "normal"
                $size = new Yourdelivery_Model_Meal_Sizes();
                $size->setRank(Yourdelivery_Model_DbTable_Meal_Sizes::getMaxRank($category->getId()) + 1);
                $size->setName("Normal");
                $size->setCategoryId($category->getId());
                $size->save();

                $this->logger->adminInfo(sprintf('Neue Kategorie "%s" (%d) wurde dem Restaurant %d hinzugefügt', $category->getName(), $category->getId(), $category->getRestaurantId()));

                $this->view->assign('categoryId', $category->getId());
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Exception in request_restaurant/addcategoryAction : %s', $e->getMessage()));
            }
        }
    }

    /**
     * add new meal size
     * @author alex
     */
    public function addsizeAction(){
        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();

            $categoryId = $post['categoryId'];
            $sizeName = $post['sizeName'];

            if( is_null($categoryId) || is_null($sizeName) ) {
                echo -1;
                return;
            }

            try {
                $size = new Yourdelivery_Model_Meal_Sizes();
                $size->setRank(Yourdelivery_Model_DbTable_Meal_Sizes::getMaxRank($categoryId) + 1);
                $size->setName($sizeName);
                $size->setCategoryId($categoryId);
                $size->save();

                $this->logger->adminInfo(sprintf('Neue Speisegröße "%s" (%d) wurde der Kategorie %d hinzugefügt', $size->getName(), $size->getId(), $categoryId));
                echo 1;
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Exception in request_restaurant/addsizeAction : %s', $e->getMessage()));
            }
        }
    }

    /**
     * add new meal
     * @author alex
     */
    public function addmealAction(){
        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();

            $restaurantId = $post['restaurantId'];
            $categoryId = $post['categoryId'];

            if ($categoryId == 0) {
                $this->view->assign('mealId', __b('Error-Es wurde keine Kategorie angegeben!'));
                return;
            }

            try {
                $category = new Yourdelivery_Model_Meal_Category($categoryId);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Faled to create category %d request_restaurant/addmealAction : %s', $categoryId));
                return;
            }

            $mealSizes = $post['yd-meal-sizecost'];
            $mealPfands = $post['yd-meal-pfandcost'];
            $mealNrs = $post['yd-meal-sizenr'];

            try {
                $meal = new Yourdelivery_Model_Meals();

                $attributes = array();
                if ($post['bio']) { $attributes[] = 'bio'; }
                if ($post['spicy']) { $attributes[] = 'spicy'; }
                if ($post['garlic']) { $attributes[] = 'garlic'; }
                if ($post['vegetarian']) { $attributes[] = 'vegetarian'; }
                if ($post['fish']) { $attributes[] = 'fish'; }
                $attributesStr = implode(",", $attributes);
                $meal->setAttributes($attributesStr);

                $meal->setName(stripslashes(str_replace('$', ' ', $post['name'])));
                $meal->setDescription(stripslashes(str_replace('$', ' ', $post['description'])));
                $meal->setMwst($post['mwst']);
                $meal->setNr($post['nr']);
                $meal->setMinAmount($post['minAmount']);
                $meal->setTabaco($post['tabaco']);
                $meal->setExcludeFromMinCost($post['excludeFromMinCost']);
                $meal->setPriceType($post['priceType']);

                $meal->setCategoryId($categoryId);
                $meal->setRestaurantId($restaurantId);
                $meal->setRank(Yourdelivery_Model_DbTable_Meals::getMaxRank($categoryId) + 1);
                $meal->save();

                if (!is_null($mealSizes)) {
                    foreach ($mealSizes as $sizeId => $cost) {
                        if ( strlen(trim($cost)) != 0 ) {
                            try {
                                // create new meal-size relation
                                $sizeNn = new Yourdelivery_Model_Meal_SizesNn();
                                $sizeNn->setMealId($meal->getId());
                                $sizeNn->setSizeId($sizeId);
                                $sizeNn->setCost(priceToInt2($cost));
                                $sizeNn->setPfand(priceToInt2($mealPfands[$sizeId]));
                                $sizeNn->setNr($mealNrs[$sizeId]);
                                $sizeNn->setHasSpecials(0);
                                $sizeNn->save();
                            }
                            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                            }
                        }
                    }
                }
                $this->view->assign('mealId', $meal->getId());
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Exception in request_restaurant/addmealAction : %s', $e->getMessage()));
            }
        }
    }

    /**
     * move meal size
     * @author alex
     */
    public function movesizeAction(){
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();

        if ($request->isPost()) {
            try {
                $sizeId = $request->getParam('sizeId', null);
                $categoryId = $request->getParam('categoryId', null);
                $direction = $request->getParam('direction', null);

                $category = new Yourdelivery_Model_Meal_Category($categoryId);
                $restaurant =  new Yourdelivery_Model_Servicetype_Restaurant($category->getRestaurantId());

                if (strcmp($direction, 'left') == 0) {
                    $restaurant->moveMealSizeLeft($sizeId);
                }
                else if (strcmp($direction, 'right') == 0)  {
                    $restaurant->moveMealSizeRight($sizeId);
                }
                else {
                    echo __b('Unbekanntes Befehl');
                    return;
                }
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Exception in request_restaurant/movesizeAction : %s', $e->getMessage()));
                return;
            }

            echo 1;
        }
    }

    /**
     * move meal
     * @author alex
     */
    public function movemealAction(){
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();

        if ($request->isPost()) {
            try {
                $mealId = $request->getParam('mealId', null);
                $categoryId = $request->getParam('categoryId', null);
                $direction = $request->getParam('direction', null);

                $category = new Yourdelivery_Model_Meal_Category($categoryId);
                $restaurant =  new Yourdelivery_Model_Servicetype_Restaurant($category->getRestaurantId());

                if (strcmp($direction, 'up') == 0) {
                    $restaurant->moveMealUp($mealId);
                }
                else if (strcmp($direction, 'down') == 0)  {
                    $restaurant->moveMealDown($mealId);
                }
                else {
                    echo __b('Unbekannter Befehl');
                    return;
                }
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Exception in request_restaurant/movemealAction : %s', $e->getMessage()));
                return;
            }

            echo 1;
        }
    }

    /**
     * update meal
     * @author alex
     */
    public function updatemealAction(){
        $request = $this->getRequest();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        if ( $request->isPost() ) {
            $post = $request->getPost();

            try {
                $mealId = $post['mealId'];
                $meal = new Yourdelivery_Model_Meals($mealId);

                $meal->setName(stripslashes($post['mealName']));
                $meal->setDescription(stripslashes($post['mealDescription']));
                $meal->setMwst($post['mealMwst']);
                $meal->setNr($post['mealNr']);
                $meal->setMinAmount($post['minAmount']);

                $attributes = array();
                if ($post['bio']) { $attributes[] = 'bio'; }
                if ($post['spicy']) { $attributes[] = 'spicy'; }
                if ($post['garlic']) { $attributes[] = 'garlic'; }
                if ($post['vegetarian']) { $attributes[] = 'vegetarian'; }
                if ($post['fish']) { $attributes[] = 'fish'; }
                $attributesStr = implode(",", $attributes);
                $meal->setAttributes($attributesStr);

                $meal->setStatus($post['online']);
                $meal->setTabaco($post['tabaco']);
                $meal->setExcludeFromMinCost($post['excludeFromMinCost']);
                $meal->setPriceType($post['priceType']);

                $meal->save();

                $mealSizes = $post['sizeCosts'];
                $mealPfandCosts = $post['pfandCosts'];
                $sizeMealNumbers = $post['sizenumbers'];

                //save meal sizes
                if (is_array($mealSizes)) {
                    foreach ($mealSizes as $sizeId => $mealSizeCost) {
                        $mealPfandCost = $mealPfandCosts[$sizeId];
                        $mealSizeNr = $sizeMealNumbers[$sizeId];

                        // remove meal size price value is empty
                        if (strlen(trim($mealSizeCost)) == 0) {
                            $id = Yourdelivery_Model_DbTable_Meal_SizesNn::findBySizeAndMealId($mealId, $sizeId);
                            if (!is_null($id) && ($id != 0)) {
                                Yourdelivery_Model_DbTable_Meal_SizesNn::remove($id);
                            }

                            //remove extras, associated with with meal and this size
                            $relId = Yourdelivery_Model_DbTable_Meal_ExtrasRelations::findByMealIdAndSizeId($mealId, $sizeId);
                            if (!is_null($relId['id']) && ($relId['id'] != 0)) {
                                Yourdelivery_Model_DbTable_Meal_ExtrasRelations::remove($relId['id']);
                            }
                        }
                        //change meal-size relation
                        else {
                            try {
                                $id = Yourdelivery_Model_DbTable_Meal_SizesNn::findBySizeAndMealId($mealId, $sizeId);
                            }
                            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                                continue;
                            }

                            try {
                                //alter the meal-size relation or create new one such relation is not found
                                if ($id) {
                                    $sizeNn = new Yourdelivery_Model_Meal_SizesNn($id);
                                }
                                else {
                                    $sizeNn = new Yourdelivery_Model_Meal_SizesNn();
                                }

                                $sizeNn->setMealId($mealId);
                                $sizeNn->setSizeId($sizeId);
                                $sizeNn->setCost(priceToInt2($mealSizeCost));
                                $sizeNn->setPfand(priceToInt2($mealPfandCost));
                                $sizeNn->setNr($mealSizeNr);
                                $sizeNn->save();
                            }
                            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                                continue;
                            }
                        }
                    }
                }
                echo '1';
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Exception in request_restaurant/updatemealAction : %s', $e->getMessage()));
            }
        }
    }

    /**
     * edit meal category
     * @author alex
     * @since 15.02.2011
     */
    public function editcategoryAction(){
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();

            //create category object
            try {
                $category = new Yourdelivery_Model_Meal_Category($request->getParam('categoryId'));
                $restaurant =  new Yourdelivery_Model_Servicetype_Restaurant($category->getRestaurantId());
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                $this->logger->adminInfo(sprintf('Exception in request_restaurant/editcategoryAction : %s', $e->getMessage()));
                return;
            }

            $form = new Yourdelivery_Form_Restaurant_MealCategoryEdit();

            if ($form->isValid($post)) {
                $values = $form->getValues();

                if ($values['main'] == 1) {
                    $categories = $restaurant->getMealCategories();
                    foreach ($categories as $c) {
                        $c->setMain(0);
                        $c->save();
                    }
                }

                $values['from'] = substr($values['from'], 0, 2) . ':' . substr($values['from'], 2, 2) . ':' . substr($values['from'], 4, 2);
                $values['to'] = substr($values['to'], 0, 2) . ':' . substr($values['to'], 2, 2) . ':' . substr($values['to'], 4, 2);

                $wdbinary = 0;
                foreach ($post['weekdays'] as $wd) {
                    $wdbinary += pow(2, $wd-1);
                }
                $values['weekdays'] = $wdbinary;

                $category->setData($values);
                $category->save();

                // set the same mwst for all meals in the category
                if ($post['mwstForSizes'] == 1) {
                    $meals = $category->getMealsSorted();
                    foreach ($meals as $meal) {
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

                $this->success(__b('Kategorie erfolgreich bearbeitet!'));
                $this->logger->adminInfo(sprintf('Kategorie "%s" (%d) vom Restaurant %d wurde geändert', $category->getName(), $category->getId(), $restaurant->getId()));
            }
            else {
                $this->logger->adminErr(sprintf('Invalid form in request_restaurant/editcategoryAction'));
            }
        }
    }

    /**
     * edit meal size
     * @author alex
     * @since 15.02.2011
     */
    public function editsizeAction(){
        $request = $this->getRequest();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        if ( $request->isPost() ) {
            $post = $request->getPost();

            $sizeId = $post['sizeId'];
            $sizeName = $post['sizeName'];

            if ( ($sizeId==0) || (strlen($sizeName)==0) ) {
                echo Zend_Json::encode(array('error' => __b('Angaben fehlen')));
                return;
            }

            try {
                $size = new Yourdelivery_Model_Meal_Sizes($sizeId);
                $size->setName($sizeName);
                $size->save();
                echo Zend_Json::encode(array('success' => __b('ok')));
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                echo Zend_Json::encode(array('error' => __b('Konnte das Size Objekt nicht erstellen!')));
            }
        }
    }

    /**
     * rearrange the order of categories
     * @author alex
     * @since 27.09.2010
     */
    public function arrangecategoriesAction(){
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $categoriesArray = array();

            $argCategories = $post['categories'];
            $restaurantId = $post['restaurantId'];
            $rank = 1;

            if (is_null($argCategories)) {
                echo __b('Fehler bei Übermittlung der Kategorien');
                return;
            }

            if (is_null($restaurantId)) {
                echo __b('Kein Restaurant id wurde gesetzt');
                return;
            }

            try {
                $restaurant =  new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->logger->adminInfo(sprintf('Failed to create restaurant %d in request_restaurant/arrangecategoriesAction', $restaurantId));

                return;
            }

            foreach ($argCategories as $cat) {
                //index of underscore as separator of string and category id
                $separatorInd = strpos($cat, "_");
                $categoryId = substr($cat, $separatorInd+1);
                $categoriesArray[$rank] = $categoryId;
                $rank++;
            }

            $restaurant->arrangeCategories($categoriesArray, $restaurantId);
            echo 1;
        }
    }

    /**
     * open lightbox to display preview of meal options and extras
     * @author alex
     * @since 11.11.2011
     */
    public function mealpreviewlightboxAction() {
        $request = $this->getRequest();
        $mealId = (integer) $request->getParam('id');
        $sizeId = (integer) $request->getParam('size');

        $this->view->count = 1;

        if ($sizeId == 0) {
            $sizeId = null;
        }

        if ($mealId !== null) {
            try {
                $meal = new Yourdelivery_Model_Meals($mealId);
                if ($sizeId === null) {
                    $sizes = $meal->getSizes();
                    if (is_array($sizes)) {
                        $size = current($sizes);
                        $sizeId = $size['id'];
                    } else {
                        return;
                    }
                }
                $meal->setCurrentSize($sizeId);
                $this->view->meal = $meal;
                $this->view->count = $meal->getMinAmount();
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return;
            }
        }
    }

    /**
     * update special opening
     * @author Alex Vait
     * @since 19.07.2012
     */
    public function updateopeningAction(){
        $request = $this->getRequest();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        if ( $request->isPost() ) {
            $post = $request->getPost();
            
            $type = $post['type'];
            $openingId = $post['openingId'];
            $openingSpecial = new Yourdelivery_Model_Servicetype_OpeningsSpecial($openingId);
            $from = $post['from'];
            $until = $post['until'];
            $date = $post['date'];
            $weekday = $post['weekday'];
            $closed = $post['closed'];
            $restaurantId = $post['restaurantId'];
            
            if (!$closed) {
                if ($from > $until) {
                    echo Zend_Json::encode(array('error' => __b('Die Öffnungszeit kann nicht größer als Schließzeit sein!')));
                    return;
                }
            }

            try {
                $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e) {
                echo Zend_Json::encode(array('error' => $e->getMessage()));
                return;
            }                
            
            
            // handle different types of openings
            // special opening
            if (strpos($type, 'special') !== false) {
                // check the correctness of the date
                if (!Default_Helpers_Date::isDate($date)) {
                    echo Zend_Json::encode(array('error' => __b('Falsches Datumformat!')));
                    return;                
                }
                
                $dateFormatted = mktime(0, 0, 0, substr($date, 3, 2), substr($date, 0, 2), substr($date, 6));
                // temp vars to compare new opening times with already available
                $reqFrom = intval($from/100);
                $reqUntil = intval($until/100);

                $openingsTable = new Yourdelivery_Model_DbTable_Restaurant_Openings_Special();
                $testOpening = $openingsTable->getOpeningsAtDate($openingSpecial->getRestaurantId(), $dateFormatted);

                // check opening intersections
                foreach ($testOpening as $to) {
                    if ($to['id'] == $openingId) {
                        continue;
                    }

                    // check if the restaurant is closed on this day
                    if ($to['closed'] == 1) {
                        echo Zend_Json::encode(array('error' => __b('An diesem Tag ist das Restaurant geschlossen!')));
                        return;
                    }

                    // check if the restaurant have some opening while we try to set it to 'closed'
                    if ( ($to['closed'] == 0) && ($closed) ){
                        echo Zend_Json::encode(array('error' => __b('An diesem Tag sind bereits Öffnungszeiten eingetragen. Wenn das Restaurant an dem Tag doch geschlossen ist, löschen Sie zuerst alle Öffnungszeiten für den Tag!')));
                        return;
                    }

                    $oFrom  = intval(substr($to['from'], 0, 2) . substr($to['from'], 3, 2));
                    $oUntil = intval(substr($to['until'], 0, 2) . substr($to['until'], 3, 2));

                    // check if this opening intersects with already available opening times on that day
                    if ( ( ($reqFrom <= $oFrom) &&  ($reqUntil > $oFrom)) ||
                            ( ($reqFrom < $oUntil) &&  ($reqUntil > $oUntil)) ||
                            ( ($reqFrom > $oFrom) &&  ($reqUntil <= $oUntil))
                        ) {
                        echo Zend_Json::encode(array('error' => __b('Diese Öffnungszeit überschneidet sich mit einer anderen!')));
                        return;
                    }
                }            

                try {
                    $dateEngl = substr($date, 6) . "-" . substr($date, 3, 2) . "-" . substr($date, 0, 2);

                    if ($post['closed']) {
                        $openingSpecial->setSpecialDate($dateEngl);
                        $openingSpecial->setClosed(1);
                        $openingSpecial->setFrom('0');
                        $openingSpecial->setUntil('0');
                    }
                    else {
                        $openingSpecial->setSpecialDate($dateEngl);
                        $openingSpecial->setClosed(0);
                        $openingSpecial->setFrom($post['from']);
                        $openingSpecial->setUntil($post['until']);
                    }

                    $openingSpecial->save();
                    echo Zend_Json::encode(array('success' => '1'));
                    
                    $this->logger->adminInfo(sprintf('Successfully edited special opening time %d for service %s (%s)',
                        $openingSpecial->getId(),
                        $restaurant->getName(),
                        $restaurant->getId()));
                    
                    return;
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                    $this->logger->adminInfo(sprintf('Exception in request_restaurant/updateopeningspecialAction : %s', $e->getMessage()));
                    echo Zend_Json::encode(array('error' => $e->getMessage()));
                    return;
                }                
            }
            // normal opening
            else {
                // check the correctness of the weekday
                if (!in_array($weekday, array(1, 2, 3, 4, 5, 6, 0, 10))) {
                    echo Zend_Json::encode(array('error' => __b('Falsches Format für Wochentag!')));
                    return;                
                }
                
                // check if this opening intersects with already available opening times on that day
                foreach ($restaurant->getRegularOpenings($weekday) as $o) {
                    $o_from = str_replace(':', '', $o->from);
                    $o_until = str_replace(':', '', $o->until);

                    if ($o->id != $openingId) {
                        if ((($from <= $o_from) &&  ($until >= $o_from)) ||
                            (($from <= $o_until) &&  ($until >= $o_until)) ||
                            (($from >= $o_from) &&  ($until <= $o_until))) {
                            echo Zend_Json::encode(array('error' => __b('Diese Öffnungszeit überschneidet sich mit einer anderen!')));
                            return;
                        }
                    }
                }

                // for the case if the opening was deleted in another tab or by another admin while the older page is still open
                try {
                    $openings = new Yourdelivery_Model_Servicetype_Openings(null, $openingId);
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                    echo Zend_Json::encode(array('error' => __b('Die Öffnungszeit existiert nicht mehr!')));
                    return;
                }

                $openings->setData(array (
                    'restaurantId'=> $restaurantId,
                    'day'=> intval($weekday),
                    'from'=> $from,
                    'until'=> $until
                ));
                $openings->save();
                echo Zend_Json::encode(array('success' => '1'));

                $this->logger->adminInfo(sprintf('Successfully edited opening time %d for service %s (%s)',
                    $openings->getId(),
                    $restaurant->getName(),
                    $restaurant->getId()));
                return;
            }
        }
    }

    /**
     * remove normal or special opening
     * @author Alex Vait
     * @since 19.07.2012
     */
    public function removeopeningAction(){
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();            
            $restaurantId = $post['restaurantId'];
            $openingId = $post['openingId'];
            $type = $post['type'];
            
            if (is_null($restaurantId) || is_null($openingId)) {
                echo Zend_Json::encode(array('error' => __b('Parameter fehlt')));
                return;                
            }
                
            try {
                $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e) {
                echo Zend_Json::encode(array('error' => $e->getMessage()));
                return;
            }                
            
            if (strpos($type, 'special') !== false) {
                try {
                    $openingSpecial = new Yourdelivery_Model_Servicetype_OpeningsSpecial($openingId);
                    if ($openingSpecial->getRestaurantId() != $restaurantId) {
                        echo Zend_Json::encode(array('error' => __b('Die Öffnungszeit gehört nicht zu diesem Dienstleister')));
                        return;                        
                    }
                    
                    Yourdelivery_Model_DbTable_Restaurant_Openings_Special::remove($openingId);
                    echo Zend_Json::encode(array('success' => '1'));
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e) {
                    echo Zend_Json::encode(array('error' => $e->getMessage()));
                    return;
                }                
            }
            else {
                if(!is_null($openingId)) {
                    $restaurant->deleteOpening($openingId);
                    echo Zend_Json::encode(array('success' => '1'));
                }

                $this->logger->adminInfo(sprintf('Successfully deleted opening time for service %s (%s)',
                            $restaurant->getName(),
                            $restaurant->getId()));                
            }
        }
    }
}
?>
