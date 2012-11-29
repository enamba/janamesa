<?php

/**
 * @author mlaug
 * @package service
 * @subpackage menu
 */
class Yourdelivery_Model_Meal_OptionRow extends Default_Model_Base{

    /**
     * get all options in this options row
     * @return SplObjectStorage
     */
    public function getOptions(){
        $options = new SplObjectStorage();
        foreach($this->getTable()->getOptionsSorted() as $opt){
            try{
                $o = new Yourdelivery_Model_Meal_Option($opt['id']);
                $options->attach($o);
            }
            catch ( Yourdelivery_Exception_DatabaseInconsistency $e ){
                continue;
            }

        }
        return $options;
    }

    /**
     * get all options of this group sorted by name
     * @return array
     */
     public function getOptionsSorted($sortby = null){
        return $this->getTable()->getOptionsSorted($sortby);
    }

    /**
     * get all meals associated with this group sorted by name
     * @author alex
     * @since 11.08.2010
     * @return array
     */
     public function getAssociatedMeals($sortby = null){
        return $this->getTable()->getAssociatedMeals($sortby);
    }

    /**
     * get all meals this option group is assigned to
     * @return SplObjectStorage
     */
    public function getMeals(){
        $meals = new SplObjectStorage();
        foreach($this->getTable()->getMeals() as $meal){
            try{
                $meals->attach(new Yourdelivery_Model_Meals($meal->mealId));
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e){
                continue;
            }
        }
        return $meals;
    }

    /**
     * get the meal category of this option
     * @return Yourdelivery_Model_Meal_Category
     */
     public function getCategory(){
        $cat = $this->getTable()->getCategory();

         if ( count($cat)>0 ){
            $id = $cat->current()->id;

            try{
                $category = new Yourdelivery_Model_Meal_Category($id);
            }
            catch ( Yourdelivery_Exception_DatabaseInconsistency $e ){
                return null;
            }

            return $category;
            }

        return null;
    }

    /**
     * get current table
     * @return Yourdelivery_Model_DbTable_Meal_Options
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Meal_OptionsRows();
        }
        return $this->_table;
    }

    /**
     * create new options group with same options, but assigned to another category
     * @author Alex Vait <vait@lieferando.de>
     * @since 18.06.2012
     */
    public function duplicate($categoryId) {
        
        $optionsRowNew = new Yourdelivery_Model_Meal_OptionRow();
        $optionsRowNew->setData($this->getData());
        $optionsRowNew->setCategoryId($categoryId);
        $optionsRowNew->save();

        $options = $this->getOptions();
        foreach ($options as $option) {
            $newOption = new Yourdelivery_Model_Meal_Option();
            $newOption->setData($option->getData());
            $newOption->save();

            $optRowNN = new Yourdelivery_Model_Meal_OptionsNn();
            $optRowNN->setOptionId($newOption->getId());
            $optRowNN->setOptionRowId($optionsRowNew->getId());
            $optRowNN->save();
        }
        
        return $optionsRowNew->getId();
    }
}