<?php

/**
 * stores and creates the menu of a restaurant
 * @package service
 * @subpackage menu
 */
class Yourdelivery_Model_Menu extends Default_Model_Base {

    /**
     * service type on which menu is based
     * @var Default_Model_Servicetype
     */
    protected $_type = null;

    /**
     * set service type
     * @author mlaug
     * @param Yourdelivery_Model_Servicetype_Abstract $type
     */
    public function setServiceType($type) {
        $this->_type = $type;
    }

    /**
     * get service type
     * @author mlaug
     * @return Yourdelivery_Model_Servicetype_Abstract
     */
    public function getServiceType() {
        return $this->_type;
    }

    /**
     * get menu card for current servicetype
     * we do not use objects here to avoid heacy load
     * @since 07.08.2010
     * @author mlaug
     * @param string $search
     * @return array()
     */
    public function getItems($search = null, $forcopy = false) {

        $r = $this->getServiceType()->getId();
        $t = $this->getServiceType()->getTypeId();

        $cats = $this->getTable()->getCategories($r, $t, $forcopy);
        $menu = array();
        $parents = array();
           
        //get sizes for meal and category
        $http = isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443 ? 'https://' : 'http://';
        $widthCat = $this->config->timthumb->category->normal->width;
        $heightCat = $this->config->timthumb->category->normal->height;
        
        $imgsize = 'normal';
        $width = $this->config->timthumb->meal->{$imgsize}->width;
        $height = $this->config->timthumb->meal->{$imgsize}->height;

        foreach ($cats as $cat) {

            $sizes = $this->getTable()->getSizesOfCategory($cat['id']);
            $meals = $this->getTable()->getMealsOfCategory($cat['id']);

            if ((count($sizes) == 0) || (count($meals) == 0)) {
                continue;
            }

            $categoryId = (integer) $cat['id'];
            $parentId = (integer) $cat['parentCategoryId'];
            $parentName = $cat['parentCategoryName'];

            $menu[$categoryId] = array();
            $menu[$categoryId]['desc'] = Default_Helpers_Filter::apply("mealCategoryNameDescription", stripslashes($cat['description']), "replace");
            $menu[$categoryId]['name'] = Default_Helpers_Filter::apply("mealCategoryNameDescription", stripslashes($cat['name']), "replace");
            
            /**
             * this is duplcate code from getImg
             * @see Yourdelivery_Model_Meal_Category
             */      
            $menu[$categoryId]['imageExists'] = true || $cat['CategoryPictureId'] > 0;
            $menu[$categoryId]['image'] = sprintf('%s/%s/service/%s/categories/%s/%s-%d-%d.jpg', $http . $this->config->domain->timthumb, $this->config->domain->base, $r, $categoryId, urlencode(str_replace("/", "_", $cat['name'])), $widthCat, $heightCat);
            
            $menu[$categoryId]['main'] = $cat['main'];
            $menu[$categoryId]['parent'] = $parentId;
            $menu[$categoryId]['parentName'] = $parentName;
            $menu[$categoryId]['sizes'] = $sizes;
            //$menu[$categoryId]['object'] = new Yourdelivery_Model_Meal_Category($categoryId);
            $menu[$categoryId]['meals'] = array();

            if ($parentId > 0) {
                if (!array_key_exists($parentId, $parents)) {
                    $parents[$parentId] = array(
                        'childId' => $categoryId,
                        'name' => $parentName,
                        'main' => $cat['main'],
                    );
                } else {
                    // if one of the child is mark as main
                    // we mark the parent as main too and never change it
                    $parents[$parentId]['main'] = $cat['main'] ? $cat['main'] : $parents[$parentId]['main'];
                }
            }

            foreach ($meals as $meal) {
                $mealAttributes = explode(",", $meal['attributes']);
                
                // now we use only the first element, because one meal can have only one type. Will be changed later probably
                $typesOfMeal = Yourdelivery_Model_DbTable_Meal_Types::getTypesOfMeal($meal['id']);
                $sizesOfMeal = $this->getTable()->getSizesOfMeal($meal['id']);

                // hide meals that have no size assigned
                if (count($sizesOfMeal) <= 0) {
                    continue;
                }

                /**
                 * this is duplcate code from $meal->getImg()
                 * @see Yourdelivery_Model_Meals 
                 */
                $image = sprintf('%s/%s/service/%s/categories/%s/meals/%s/%s-%d-%d.jpg', $http . $this->config->domain->timthumb, $this->config->domain->base, $r, $categoryId, $meal['id'], urlencode($meal['name']), $width, $height);
                          
                foreach ($sizes as $sizeId => $size) {
                    $menu[$categoryId]['meals'][$meal['id']][$sizeId] = array(
                        'id' => (integer) $meal['id'],
                        'vegetarian' => in_array('vegetarian', $mealAttributes),
                        'bio' => in_array('bio', $mealAttributes),
                        'spicy' => in_array('spicy', $mealAttributes),
                        'garlic' => in_array('garlic', $mealAttributes),
                        'fish' => in_array('fish', $mealAttributes),
                        'name' => Default_Helpers_Filter::apply("mealNameDescription", $meal['name'], "replace"),
                        'cost' => null,
                        'netto' => null,
                        'size' => (integer) $sizeId,
                        'sizeName' => $size['name'],
                        'desc' => Default_Helpers_Filter::apply("mealNameDescription", $meal['description'], "replace"),
                        'pfand' => null,
                        'typeName' => is_array($typesOfMeal) ? $typesOfMeal[0]['name'] : null,
                        'imageExists' => (boolean) $meal['hasPicture'],
                        'image' => $image,
                        'priceType' => $meal['priceType']
                    );
                }

                foreach ($sizesOfMeal as $sizeMeal) {
                    $cost = (integer) $sizeMeal['cost'];
                    $pfand = (integer) $sizeMeal['pfand'];

                    $tax_meal = (integer) $meal['mwst'];
                    if (!in_array($tax_meal, array(7, 19))) {
                        $tax = (integer) $cat['mwst'];
                        if (!in_array($tax, array(7, 19))) {
                            $tax = 7;
                        }
                    } else {
                        $tax = $tax_meal;
                    }

                    $netto = ($cost - $pfand) / (1 + ($tax / 100));
                    $current = &$menu[$categoryId]['meals'][$meal['id']][$sizeMeal['sizeId']];
                    $current['netto'] = $netto;
                    $current['pfand'] = $pfand;
                    $current['cost'] = $cost;
                    $current['nr'] = $sizeMeal['nr'];
                    $current['hasSpecials'] = (boolean) $sizeMeal['hasSpecials'];
                    $current['excludeFromMinCost'] = (boolean) ($meal['excludeFromMinCost'] || $cat['excludeFromMinCost']);
                    $current['minAmount'] = (integer) $meal['minAmount'];
                    $current['hasPicture'] = (boolean) $meal['hasPicture'];

                    if ($meal['minAmount'] > 1) {
                        $current['hasSpecials'] = true;
                    }
                }
            }
        }

        return array($menu, $parents);
    }

    /**
     * copy one menu to another
     * @author mlaug
     * @param Yourdelivery_Model_Servicetype_Abstract $to
     * @param Yourdelivery_Model_Servicetype_Abstract $from
     * @return boolean
     */
    public function copy($to, $from = null) {
        set_time_limit(300);

        if (is_null($from) && is_null($this->getServiceType())) {
            return false;
        }

        if (!is_object($to)) {
            return false;
        }

        if (!is_null($from) && is_object($from)) {
            $this->setServiceType($from);
        }

        list($menu, ) = $this->getItems(null, true);
        if (count($menu) == 0) {
            return;
        }

        $mealSizeRel = new Yourdelivery_Model_DbTable_Meal_SizesNn();
        $extraTable = new Yourdelivery_Model_DbTable_Meal_Extras();
        $extraTableRel = new Yourdelivery_Model_DbTable_Meal_ExtrasRelations();

        //mapping of old categories to new
        $categoryMapping = array();

        //mapping of sizes
        $sizesMapping = array();

        //array of copied extras groups, so we don't copy the same group from different meals
        $extrasGroupMapping = array();

        //array of copied extras, so we don't copy the same extra from different meals
        $extrasMapping = array();

        //array of copied extras, so we don't copy the same extra from different meals
        $extrasRelDone = array();

        // array of copied options, so we don't copy the same option from different meals
        $optCopied = array();

        // array of copied option rows, so we don't copy the same row from different meals
        $optRowsMapping = array();

        // array of copied option rows - meals relations
        $optRowsNnMappingDone = array();

        // iterate through menu to copy all categories
        foreach ($menu as $categoryId => $oldCat) {
            //copy all categories
            $category = new Yourdelivery_Model_Meal_Category($categoryId);
            $serviceTypes = $category->getServiceTypes();
            $category->setId(null);
            $category->setRestaurantId($to->getId());
            $newCatId = $category->save();
            // set category images
            $category->getImage(true);
            $categoryMapping[$categoryId] = $newCatId;

            //add category to servicetypes
            foreach ($serviceTypes as $type) {
                $servicetype_cat = new Yourdelivery_Model_Servicetype_MealCategorysNn();
                $servicetype_cat->setServicetypeId($type['id']);
                $servicetype_cat->setMealCategoryId($newCatId);
                $servicetype_cat->save();
            }

            foreach ($oldCat['sizes'] as $oldSize) {
                $size = new Yourdelivery_Model_Meal_Sizes($oldSize['id']);
                $size->setId(null);
                $size->setCategoryId($newCatId);
                $newSizeId = $size->save();
                $sizesMapping[$oldSize['id']] = $newSizeId;
            }
        }

        // iterate through menu to copy all meals, extras and options
        foreach ($menu as $categoryId => $oldCat) {
            //copy all meals in this category
            foreach ($oldCat['meals'] as $mealId => $meal) {
                $oldMeal = new Yourdelivery_Model_Meals($mealId);
                $newMeal = new Yourdelivery_Model_Meals($mealId);
                $newMeal->setId(null);
                $newMeal->setRestaurant($to);
                $newMeal->setCategoryId($categoryMapping[$categoryId]);
                $newMealId = $newMeal->save();
                $mealMapping[$mealId] = $newMealId;

                $newMealUpdated = new Yourdelivery_Model_Meals($newMealId);
                $newMealUpdated->setImg($oldMeal->getImgLocalPath());

                foreach ($oldCat['sizes'] as $oldSize) {
                    $sizeId = $oldSize['id'];

                    try {
                        $oldMeal->setCurrentSize($sizeId);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        continue;
                    }

                    //if it has no null as cost, this item has this size
                    if (!is_null($meal[$sizeId]['cost'])) {
                        $row = $mealSizeRel->createRow();
                        $row->mealId = $newMealId;
                        $row->sizeId = $sizesMapping[$sizeId];
                        $row->cost = $meal[$sizeId]['cost'];
                        $row->pfand = $meal[$sizeId]['pfand'];
                        $row->nr = $meal[$sizeId]['nr'];
                        $row->save();
                        unset($row);

                        // copy all option rows belonging to this category
                        foreach ($oldMeal->getOptionsRowsByCategory() as $catOptRow) {
                            if (!array_key_exists($catOptRow['id'], $optRowsMapping)) {
                                $row = new Yourdelivery_Model_Meal_OptionRow($catOptRow['id']);
                                $row->setId(null);
                                $row->setCategoryId($categoryMapping[$catOptRow['categoryId']]);
                                $row->setRestaurantId($to->getId());
                                $newOptionRowId = $row->save();
                                unset($row);

                                $optRowsMapping[$catOptRow['id']] = $newOptionRowId;
                            }
                        }

                        // copy all option rows association with this meal and copy all corresponding option rows if not yet copied
                        foreach ($oldMeal->getOptionsRowNNByMeal() as $mealOptRowNn) {
                            if (!array_key_exists($mealOptRowNn['optionRowId'], $optRowsMapping)) {
                                try {
                                    $row = new Yourdelivery_Model_Meal_OptionRow($mealOptRowNn['optionRowId']);
                                    $row->setId(null);
                                    if (intval($row->getCategoryId()) != 0) {
                                        $row->setCategoryId($categoryMapping[$row->getCategoryId()]);
                                    } else {
                                        $row->setCategoryId(0);
                                    }
                                    $row->setRestaurantId($to->getId());
                                    $newOptionRowId = $row->save();
                                    unset($row);

                                    $optRowsMapping[$mealOptRowNn['optionRowId']] = $newOptionRowId;
                                } catch (Exception $e) {
                                    $this->logger->adminInfo(sprintf('Error while copying menu from restaurant #%d to #%d', $from->getId(), $to->getId()));
                                    continue;
                                }
                            } else {
                                $newOptionRowId = $optRowsMapping[$mealOptRowNn['optionRowId']];
                            }

                            if (!in_array($newMealId . "_" . $newOptionRowId, $optRowsNnMappingDone)) {
                                $optRowNn = new Yourdelivery_Model_Meal_OptionsRowsNn($mealOptRowNn['id']);
                                $optRowNn->setId(null);
                                $optRowNn->setRestaurantId($to->getId());
                                $optRowNn->setMealId($newMealId);
                                $optRowNn->setOptionRowId($newOptionRowId);
                                $optRowNn->save();
                                $optRowsNnMappingDone[] = $newMealId . "_" . $newOptionRowId;
                            }
                        }

                        // iterate through all option rows and copy all options
                        foreach ($optRowsMapping as $oldOptRowId => $newOptRowId) {
                            $row = new Yourdelivery_Model_Meal_OptionRow($oldOptRowId);

                            foreach ($row->getOptions() as $oldOption) {
                                //copy the option and option-option row dependency if we havn't done it yet
                                if (!in_array($oldOption->getId(), $optCopied)) {
                                    $newOption = new Yourdelivery_Model_Meal_Option($oldOption->getId());
                                    $newOption->setId(null);
                                    $newOption->setRestaurantId($to->getId());
                                    $newOptionId = $newOption->save();

                                    $optionNn = new Yourdelivery_Model_Meal_OptionsNn();
                                    $optionNn->setOptionId($newOptionId);
                                    $optionNn->setOptionRowId($newOptRowId);
                                    $optionNn->save();

                                    //save the id so we know that this option is already copied
                                    $optCopied[] = $oldOption->getId();
                                }
                            }
                        }

                        //add extras
                        foreach ($oldMeal->getExtrasForCopying() as $groupId => $extrasGroup) {
                            if ($groupId > 0) {
                                if (!array_key_exists($groupId, $extrasGroupMapping)) {
                                    $newExtraGroup = new Yourdelivery_Model_Meal_ExtrasGroups($groupId);
                                    $newExtraGroup->setId(null);
                                    $newExtraGroup->setRestaurantId($to->getId());
                                    $newGroupId = $newExtraGroup->save();
                                    //save the id so we know that this extras group is already copied
                                    $extrasGroupMapping[$groupId] = $newGroupId;
                                } else {
                                    $newGroupId = $extrasGroupMapping[$groupId];
                                }
                            } else {
                                $newGroupId = 0;
                            }

                            foreach ($extrasGroup as $extra) {
                                $newExtraId = 0;
                                if (!array_key_exists($extra['id'], $extrasMapping)) {
                                    $row = $extraTable->find($extra['id'])->current();
                                    $newExtraData = $row->toArray();

                                    unset($newExtraData['id']);
                                    $newExtraData['restaurantId'] = $to->getId();
                                    $newExtraData['groupId'] = $newGroupId;
                                    $newRow = $extraTable->createRow($newExtraData);
                                    $newExtraId = $newRow->save();
                                    $extrasMapping[$extra['id']] = $newExtraId;
                                } else {
                                    $newExtraId = $extrasMapping[$extra['id']];
                                }

                                $rowRel = $extraTableRel->find($extra['relId'])->current();
                                $newRelData = $rowRel->toArray();
                                unset($newRelData['id']);
                                $newRelData['sizeId'] = $sizesMapping[$newRelData['sizeId']];
                                $newRelData['extraId'] = $newExtraId;
                                // extra is related to the whole category
                                if ((strlen($newRelData['categoryId']) > 0) && ($newRelData['categoryId'] != 0)) {
                                    $newRelData['categoryId'] = $categoryMapping[$newRelData['categoryId']];
                                    $newRelData['mealId'] = 0;
                                    $key = $newExtraId . "_" . $newRelData['categoryId'] . "_0_" . $newRelData['sizeId'];
                                } else {
                                    $newRelData['categoryId'] = 0;
                                    $newRelData['mealId'] = $newMealId;
                                    $key = $newExtraId . "_0_" . $newMealId . "_" . $newRelData['sizeId'];
                                }

                                if (!in_array($key, $extrasRelDone)) {
                                    $newRel = $extraTableRel->createRow($newRelData);
                                    $relId = $newRel->save();
                                    $extrasRelDone[] = $key;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * no database needed here
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Servicetypes_Meal_Categorys_Nn
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Servicetypes_MealCategorysNn();
        }
        return $this->_table;
    }

}
