<?php

/**
 * Description of Budget
 * @package order
 * @author mlaug
 */
class Yourdelivery_Model_Order_BucketMeals extends Default_Model_Base {

    public function  __construct($id = null, $current = null) {
        parent::__construct($id, $current);
    }


    /**
     * get table
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Order_BucketMeals
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Order_BucketMeals();
        }
        return $this->_table;
    }

    /**
     * delete an bucket meal
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function delete(){
        $this->getTable()->delete(array('id',$this->getId()));
    }

    /**
     * @todo check min and max count of options for this meal
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function addOption($optionId){
        $this->getTable()->addOption($optionId);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @param int $optionId
     */
    public function deleteOption($optionId) {
        $this->getTable()->deleteOption($optionId);
    }

    /**
     * @author mlaug
     * @todo impelement
     * @param int $optionId
     * @return boolean
     */
    public function hasOption($optionId){
        return true;
    }

    /**
     *
     * @param int $extraId
     */
    public function addExtra($extraId){
        $this->getTable()->addExtra($extraId);
    }

    /**
     *
     * @param int $extraId
     */
    public function deleteExtra($extraId) {
        $this->getTable()->deleteExtra($extraId);
    }

    /**
     * check, if original meal has given extra
     * @param int $extraId
     * @author mlaug
     * @return boolean
     */
    public function hasExtra($extraId, $sizeId){
        if( is_null($extraId) || is_null($sizeId) ){
            return false;
        }
        return $this->getTable()->hasExtra($extraId, $sizeId);
    }

    /**
     * check, if given option is in bucket
     * @author mlaug
     * @return boolean
     */
    public function hasOptionInBucket($optionId){
        if( is_null($optionId) ){
            return false;
        }
        return $this->getTable()->hasOptionInBucket($optionId);
    }
}
?>
