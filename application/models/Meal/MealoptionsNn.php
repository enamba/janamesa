<?php

/**
 * @author mlaug
 * @package service
 * @subpackage menu
 */
class Yourdelivery_Model_Meal_MealoptionsNn extends Default_Model_Base{
    /**
     * get current table
     * @return Yourdelivery_Model_DbTable_Meal_MealoptionsNn
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Meal_MealoptionsNn();
        }
        return $this->_table;
    }
}