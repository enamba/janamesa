<?php

/**
 * Description of Category
 * @package service
 * @subpackage menu
 * @author mlaug
 */
class Yourdelivery_Model_Meal_Category extends Default_Model_Base {

    /**
     * get all related sizes
     * @author mlaug
     * @return Zend_Db_Table_Rowset
     */
    public function getSizes() {
        return $this->getTable()->getSizes();
    }

    /**
     * get all related sizes ordered by rank
     * @return Zend_Db_Table_Rowset
     */
    public function getSizesByRank() {
        return $this->getTable()->getSizesByRank();
    }

    /**
     * get all meals belonging to this category
     * @author mlaug
     * @return Zend_Db_Table_Rowset
     */
    public function getMeals() {
        return $this->getTable()->getMeals();
    }

    /**
     * get all meals associated with this category sorted by rank
     * @return array
     */
    public function getMealsSorted() {
        $meals = new SplObjectStorage();
        foreach ($this->getTable()->getMealsIdSorted() as $m) {
            $meals->attach(new Yourdelivery_Model_Meals($m['id']));
        }
        return $meals;
    }

    /**
     * get all meals belonging to this category as objects 
     * @return Zend_Db_Table_Rowset
     */
    public function getMealsAsObjects() {
        $meals = new SplObjectStorage();
        foreach ($this->getTable()->getMeals() as $m) {
            if (!$m['deleted']) {
                $meals->attach(new Yourdelivery_Model_Meals($m['id']));
            }
        }
        return $meals;
    }

    /**
     * get all extras for this category
     * @return Zend_Db_Table_Rowset
     */
    public function getExtras() {
        return $this->getTable()->getExtras();
    }

    /**
     * get all extras for this category with corresponding groups
     * @return Zend_Db_Table_Rowset
     */
    public function getExtrasWithGroups() {
        return $this->getTable()->getExtrasWithGroups();
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 18.06.2012
     * @return array
     */
    public function getOptionRows() {
        
        $res = array();
        
        $rows = $this->getTable()->getOptionRows();
        foreach ($rows as $row) {
            try {
                $res[] = new Yourdelivery_Model_Meal_OptionRow($row['id']);
            } 
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
        }
        
        return $res;
    }
    
    /**
     * if this meal category has a valid category picture
     * @return boolean
     */
    public function hasAllCategoryPictures() {
        $piccatTable = new Yourdelivery_Model_DbTable_Category_Picture();
        $picCategories = $piccatTable->getIdsNames();

        foreach ($picCategories as $pcat) {
            if ($pcat['id'] == $this->getCategoryPictureId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * get current table
     * @return Yourdelivery_Model_DbTable_Meal_Categories
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Meal_Categories();
        }
        return $this->_table;
    }

    /**
     * category-size-extra association exists?
     * @return boolean
     */
    public function hasExtra($extraId, $sizeId) {
        return $this->getTable()->hasExtra($extraId, $sizeId);
    }

    /**
     * category-options row association exists?
     * @return boolean
     */
    public function hasOptionsRow($optRowId) {
        $bool = $this->getTable()->hasOptionsRow($optRowId);
        return $this->getTable()->hasOptionsRow($optRowId);
    }

    /**
     * cost for certain extra in this size
     * @return int
     */
    public function getExtraCost($extraId, $sizeId) {
        return $this->getTable()->getExtraCost($extraId, $sizeId);
    }

    /**
     * remove all extras relationship with this category for the size
     * used to clean the data before saving new extras relationships
     * @return
     */
    public function removeExtrasForSize($sizeId) {
        $this->getTable()->removeExtrasForSize($sizeId);
    }

    /**
     * shows if the category has certain service type assigned
     * @param int $type Yourdelivery_Model_Servicetype_Abstract::[RESTAURANT_IND | CATER_IND | GREAT_IND | FRUIT_IND]
     * @return boolean
     */
    public function hasServiceType($type) {
        return $this->getTable()->hasServiceType($type);
    }

    /**
     * get all service types
     * @return array
     */
    public function getServiceTypes() {
        return $this->getTable()->getServiceTypes();
    }

    /**
     * check if an image has been associated
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.06.2012
     * @return boolean 
     */
    public function hasImage(){
        return (boolean) $this->getCategoryPictureId() > 0;
    }
    
    /**
     * get a random image for associated image category. if image has already been choosen
     * randomly, we stick to this choice
     * @return string
     */
    public function getImage($overwrite = false) {
        if (!is_null($this->getCategoryPictureId())) {
            $dest = sprintf("%d-image.jpg", $this->getId());
                    
            //respond with timthumb image             
            $width = $this->config->timthumb->category->normal->width;
            $height = $this->config->timthumb->category->normal->height;
            $http = isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443 ? 'https://' : 'http://';
            $url = sprintf('%s/%s/service/%s/categories/%s/%s-%d-%d.jpg', $http . $this->config->domain->timthumb, $this->config->domain->base, $this->getService()->getId(), $this->getId(), urlencode(str_replace("/", "_", $this->getName())), $width, $height);
            
            if (!$this->getStorage()->exists($dest) || $overwrite) {
                try {
                    $pic_cat = new Yourdelivery_Model_Category_Picture($this->getCategoryPictureId());
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->logger->err('Cannot find CategoryPicture with ID ' . $this->getCategoryPictureId());
                    return null;
                }
                $resource = $pic_cat->getRandomImg();
                if (!is_null($resource)) {
                    $this->getStorage()->store($dest, file_get_contents(APPLICATION_PATH . $resource));

                    //save image additionally in amazon s3
                    $config = Zend_Registry::get('configuration');
                    Default_Helpers_AmazonS3::putObject(
                            $config->domain->base, "restaurants/" . $this->getRestaurantId() . "/categories/" . $this->getId() . "/default.jpg", APPLICATION_PATH . $resource);
                                    
                    //clear varnish
                    if ($this->config->varnish->enabled) {
                        $varnishPurger = new Yourdelivery_Api_Varnish_Purger();
                        $varnishPurger->addUrl($url);
                        $varnishPurger->executePurge();
                    }
                    
                }
            }
            if ($this->getStorage()->exists($dest)) {                    
                return $url;              
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * get count of all meals associated with this category
     * @author alex
     * @since 10.02.2011
     */
    public function getMealsCount() {
        return $this->getTable()->getMealsCount();
    }

    /**
     * get parent meal category
     * @return Yourdelivery_Model_Meal_CategoryParent
     * @author alex
     * @since 13.10.2011
     */
    public function getParentMealCategory() {
        if ($this->getParentMealCategoryId() == 0) {
            return null;
        }

        try {
            $parent = new Yourdelivery_Model_Meal_CategoryParent($this->getParentMealCategoryId());
            return $parent;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
    }

    /**
     * check if this category is available on this weekday (1 - monday ... 7 - sunday)
     * @return bool
     * @author alex
     * @since 10.11.2011
     */
    public function isAvailableOnWeekday($weekday) {
        return $this->getTable()->isAvailableOnWeekday($weekday);
    }

    /**
     * update hasSpecial flag for all meals of this category
     * @author Alex Vait <vait@lieferando.de>
     * @since 15.12.2011
     * @see YD-848
     */
    public function updateHasSpecials() {
        if ($this->getId() <= 0) {
            return;
        }
        
        foreach ($this->getTable()->getMeals() as $m) {
            try {
                $meal = new Yourdelivery_Model_Meals($m['id']);
                $meal->updateHasSpecials();
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
        }
    }
    
    /**
     * get storage object of this meal
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.12.2010
     * @return Default_File_Storage
     */
    public function getStorage($path = null) {
        if (is_null($this->_storage)) {
            $this->_storage = new Default_File_Storage();
            $this->_storage->resetSubFolder();
            $this->_storage->setSubFolder('category');
        }
        return $this->_storage;
    }
    
    /**
     * duplicate this category within the same resaurant
     * @author Alex Vait <vait@lieferando.de>
     * @since 24.07.2012
     * @return Yourdelivery_Model_Meal_Category
     */
    public function duplicate() {
        //mapping of sizes
        $sizesMapping = array();
        
        //mapping of meals
        $mealMapping = array();
        
        $duplicatedCategory = new Yourdelivery_Model_Meal_Category($this->getId());
        $duplicatedCategory->setId(null);
        $duplicatedCategory->setRank(Yourdelivery_Model_DbTable_Meal_Categories::getMaxRank($this->getRestaurantId()) + 1);
        $newCatId = $duplicatedCategory->save();

        $optRows = $this->getOptionRows();
        if (!is_null($optRows)) {
            foreach ($optRows as $or) {
                $or->duplicate($newCatId);
            }                    
        }        
        
        //assign servicetypes to the category
        foreach ($this->getServiceTypes() as $type) {
            try {
                $servicetype_cat = new Yourdelivery_Model_Servicetype_MealCategorysNn();
                $servicetype_cat->setServicetypeId($type['id']);
                $servicetype_cat->setMealCategoryId($newCatId);
                $servicetype_cat->save();
            } 
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
        }

        foreach ($this->getSizes() as $size) {
            try {
                $duplicatedSize = new Yourdelivery_Model_Meal_Sizes($size['id']);
                $duplicatedSize->setId(null);
                $duplicatedSize->setCategoryId($newCatId);
                $sizesMapping[$size['id']] = $duplicatedSize->save();
            } 
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
        }
        
        $extrasDone[] = array();

        foreach ($this->getMeals() as $meal) {
            $oldMeal = new Yourdelivery_Model_Meals($meal['id']);
            $newMeal = new Yourdelivery_Model_Meals($meal['id']);
            $newMeal->setId(null);
            $newMeal->setCategoryId($newCatId);
            $newMealId = $newMeal->save();

            // update meal object so we have new storage path in it
            $newMealUpdated = new Yourdelivery_Model_Meals($newMealId);            
            $newMealUpdated->setImg($oldMeal->getImgLocalPath());
            
            $mealMapping[$mealId] = $newMealId;

            // duplicate all options relations to this meal
            foreach ($oldMeal->getOptionsRowNNByMeal() as $optionsRowMeal) {
                $optionRowNn = new Yourdelivery_Model_Meal_OptionsRowsNn();
                $optionRowNn->setData($optionsRowMeal);
                $optionRowNn->setMealId($newMealId);
                $optionRowNn->save();
            }            
                        
            foreach ($this->getSizes() as $size) {
                try {
                    $oldMeal->setCurrentSize($size['id']);
                } 
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    continue;
                }
                
                foreach ($oldMeal->getExtrasForCopying() as $extraGroup) {
                    foreach ($extraGroup as $extraEntry) {
                        $rel = new Yourdelivery_Model_Meal_ExtrasRelations($extraEntry['relId']);
                        $rel->setId(null);
                        
                        if ($extraEntry['mealId'] != 0) {
                            $rel->setMealId($newMealId);
                            $rel->setSizeId($sizesMapping[$size['id']]);
                            $rel->save();                    
                        }
                        else {
                            $key = $sizesMapping[$size['id']] . "-" . $extraEntry['id'];
                            if (!in_array($key, $extrasDone)) {
                                $rel->setCategoryId($newCatId);
                                $rel->setSizeId($sizesMapping[$size['id']]);
                                $rel->save();                    
                                $extrasDone[] = $key;
                            }
                        }                        
                    }
                }
                
                //if it has no null as cost, this item has this size
                if ($oldMeal->getCost()>0) {
                    $mealSizeRelId = Yourdelivery_Model_DbTable_Meal_SizesNn::findBySizeAndMealId($oldMeal->getId(), $size['id']);
                    
                    try {
                        $mealSizeRel = new Yourdelivery_Model_Meal_SizesNn($mealSizeRelId);
                        $mealSizeRel->setId(null);
                        $mealSizeRel->setMealId($newMealId);
                        $mealSizeRel->setSizeId($sizesMapping[$size['id']]);
                        $mealSizeRel->save();
                    } 
                    catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        continue;
                    }
                    
                }
            }
        }
        
        return $duplicatedCategory;
    }    

}

?>
