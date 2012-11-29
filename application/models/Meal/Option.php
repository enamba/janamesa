<?php

/**
 * @author mlaug
 * @package service
 * @subpackage menu
 */
class Yourdelivery_Model_Meal_Option extends Default_Model_Base{

    protected $_cost = null;
    
    /**
     * get current costs
     * @author mlaug
     * @since 27.06.2011
     * @return integer
     */
    public function getCost(){
        if ( $this->_cost != null ){
            return $this->_cost;
        }
        return (integer) $this->_data['cost'];
    }
    
    /**
     * @author mlaug
     * @since 27.06.2011
     * @param integer $cost
     */
    public function setCost($cost = 0){
        $this->_cost = $cost;
        $this->_data['cost'] = $cost;
    }
    
    /**
     * get current table
     * @return Yourdelivery_Model_DbTable_Meal_Options
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Meal_Options();
            if ( !is_null($this->getId()) ){
                $this->_table->setId($this->getId());
            }
        }
        return $this->_table;
    }

    /**
     * get id of the  option group this option belongs to
     * @return int
     */
    public function getOptRowId() {
        return $this->getTable()->getOptRowId();
    }

    /**
     * get all meal categories this option belongs to
     * @return array
     */
    public function getCategories(){
        return $this->getTable()->getCategories();
    }

    /**
     * get all options groups this option belongs to
     * @return array
     */
    public function getOptionsGroups(){
        return $this->getTable()->getOptionsGroups();
    }

}