<?php

/**
 * @author mlaug
 * @package service
 * @subpackage menu
 */
class Yourdelivery_Model_Meal_SizesNn extends Default_Model_Base{

    /**
     * get associated table
     * @return Yourdelivery_Model_DbTable_Courier
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Meal_SizesNn();
        }
        return $this->_table;
    }

}


?>
