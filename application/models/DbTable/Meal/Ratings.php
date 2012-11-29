<?php

class Yourdelivery_Model_DbTable_Meal_Ratings extends Default_Model_DbTable_Base {

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_ratings';
    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    
    protected $_referenceMap = array(
        'Meal' => array(
            'columns' => 'mealId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Meals',
            'refColumns' => 'id'
        )
    );
    
    /**
     * check if this meal has already been rated
     * for this order
     * @author mlaug
     * @since 28.06.2011
     * @param integer $orderId
     * @param integer $mealId
     * @return boolean
     */
    public function isRated($orderId, $mealId){
        $select = $this->select();
        $select->where('orderId=?', $orderId)
               ->where('mealId=?', $mealId);
        if ( $select->query()->rowCount() > 0 ){
            return true;
        }
        return false;
    }
    
    /**
     *
     * @param type $orderId
     * @param type $mealId
     * @return Zend_Db_Table_Row_Abstract|false
     */
    public function getRating($orderId, $mealId) {
        $select = $this->select();
        $select->where('orderId=?', $orderId)
               ->where('mealId=?', $mealId);
        if ( $select->query()->rowCount() > 0 ){
            return $this->fetchRow($select);
        }
        return false;
    }
    
}