<?php
/**
 * @author mlaug
 * @package service
 * @subpackage menu
 */
class Yourdelivery_Model_Meal_Sizes extends Default_Model_Base{

    /**
     * get associated table
     * @return Default_Model_DbTable_Base
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Meal_Sizes();
        }
        return $this->_table;
    }

    /**
     * size-extra association exists?
     * @return boolean
     */
    public function hasExtra($extraId) {
        return $this->getTable()->hasExtra($extraId);
    }

}


?>
