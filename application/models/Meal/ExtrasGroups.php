<?php

/**
 * @author mlaug
 * @package service
 * @subpackage menu
 */
class Yourdelivery_Model_Meal_ExtrasGroups extends Default_Model_Base{


    /**
     * get all extras of this group
     * @return SplObjectStorage
     */
    public function getExtras(){
        $extras = new SplObjectStorage();
        foreach($this->getTable()->getExtras() as $extra){
            try{
                $extras->attach(new Yourdelivery_Model_Meal_Extra($extra['id']));
            }
            catch ( Yourdelivery_Exception_DatabaseInconsistency $e ){
                continue;
            }

        }
        return $extras;
    }

    /**
     * get all extras of this group sorted by criteria
     * @return SplObjectStorage
     */
    public function getExtrasSorted($sortby = 'TRIM(name)'){
        return $this->getTable()->getExtrasSorted($sortby);
    }

    /**
     * get current table
     * @return Yourdelivery_Model_DbTable_Meal_Extras
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Meal_ExtrasGroups();
        }
        return $this->_table;
    }
}