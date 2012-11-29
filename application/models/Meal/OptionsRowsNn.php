<?php

/**
 * @author mlaug
 * @package service
 * @subpackage menu
 */
class Yourdelivery_Model_Meal_OptionsRowsNn extends Default_Model_Base{
    /**
     * get current table
     * @return Yourdelivery_Model_DbTable_Meal_OptionsNn
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Meal_OptionsRowsNn();
        }
        return $this->_table;
    }
}