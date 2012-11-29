<?php

/**
 * @author mlaug
 * @package service
 * @subpackage menu
 */
class Yourdelivery_Model_Meal_Extra extends Default_Model_Base{

    protected $_size = null;

    protected $_meal = null;

    protected $_tax = null;

    protected $_cost = null;

    /**
     * set current meal to get correct costs
     * according to meal
     * @param int $meal id
     * @author alex
     */
    public function setMeal($mealId) {
        $this->_meal = $mealId;
    }

    /**
     * set current size to get correct costs
     * according to size
     * @param int $size
     */
    public function setSize($size) {
        $this->_size = $size;
    }

    /**
     * get cost of this options, depending on size
     * so size should be sez before
     * @return int
     */
    public function getCost($multiple = true){
        if ( is_null($this->_cost) ){
            if ( !is_null($this->_size) ){
                $this->getTable()->setId($this->getId());
                $par = $this->getTable()->getRelationCosts($this->_size, $this->_meal);
                if ( is_array($par) ){
                    $cost = current($par);
                }
                $this->_cost = $cost;
            }
        }
        
        if ( $multiple ){
            return (integer) $this->_cost * $this->getCount();
        }
        return $this->_cost;
    }

    /**
     * overwrite cost
     * @param int $cost
     */
    public function setCost($cost){
        $this->_cost = $cost;
    }

    /**
     * overwrite tax
     * TODO mwst must be separated from tax
     * @param int $tax
     */
    public function setMwst($tax){
        $this->_tax = $tax;
        $this->_data['mwst'] = $tax;
    }

    /**
     * get taxe
     * @return int
     */
    public function getMwst(){
        if ( !is_null($this->_tax) ){
            return $this->_tax;
        }
        return $this->_data['mwst'];
    }

    /**
     * get meal category this meal belongs to
     * @return Yourdelivery_Model_DbTable_Meal_Extras
     */
    public function getCategory() {
        if (is_null($this->getCategoryId())) {
            return null;
        }
        return new Yourdelivery_Model_Meal_Category($this->getCategoryId());
    }

    /**
     * get current table
     * @return Yourdelivery_Model_DbTable_Meal_Extras
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Meal_Extras();
        }
        return $this->_table;
    }


    /**
     * get all distinct extras name, available in the database
     * @author alex
     * @since 06.10.2010
     */
    public static function getDistinctExtrasNames()
    {
        $extras = Yourdelivery_Model_DbTable_Meal_Extras::getDistinctExtrasNames();
        $all_extras = array();
        
        foreach ($extras as $e) {
            $name = $e['extra_name'];
            if ( strlen(trim($name)) == strlen($name) ) {
                $all_extras[] = $name;
            }
        }

        return $all_extras;
    }
    
}