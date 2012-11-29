<?php

/**
 * Bucket meals are an implementation of an redundant meal
 * which has been used for an order. this is stored seperatly, so that
 * changes in menu do not effect the order
 * @author mlaug
 */

class Yourdelivery_Model_DbTable_Order_BucketMeals extends Default_Model_DbTable_Base {

    protected $_name = 'orders_bucket_meals';

    protected $_dependentTables = array(
                                        'Yourdelivery_Model_DbTable_Order_BucketMeals_Options',
                                        'Yourdelivery_Model_DbTable_Order_BucketMeals_Mealoptions',
                                        'Yourdelivery_Model_DbTable_Restaurant_BucketMeals_Extras',
    );

    protected $_referenceMap    = array(
        'Order' => array(
            'columns'           => 'orderId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Order',
            'refColumns'        => 'id'
        )
    );


    /**
     * delete bucketMeal including Extras and Options
     */
    public function delete($where){
        // delete depending data
        $this->deleteExtras();
        $this->deleteOptions();
        parent::delete($where);     
    }

    /**
     * delete all extras to this bucketMeal
     * @return boolean
     */
    public function deleteExtras(){
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('orders_bucket_meals_extras', 'orders_bucket_meals_extras.bucketItemId = '.$this->getId());
        return true;
    }

    /**
     * delete one extra from meal
     * @param int $extraId of extra
     * @return boolean
     */
    public function deleteExtra($extraId){
        if( is_null($extraId) ){
            return false;
        }
        if( $extraId == '' ){
            return false;
        }

        $db = Zend_Registry::get('dbAdapter');
        try{
        $db->delete('orders_bucket_meals_extras',
                array(
                    'orders_bucket_meals_extras.bucketItemId = '.$this->getId(),
                    'orders_bucket_meals_extras.extraId = '.$extraId
                    )
            );
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * delete all options to this bucketMeal
     * @return boolean
     */
    public function deleteOptions(){
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('orders_bucket_meals_options', 'orders_bucket_meals_options.bucketItemId = '.$this->getId());
        return true;
    }

    /**
     * delete one option from meal
     * @param int $optionId of option
     * @return boolean
     */
    public function deleteOption($optionId){
        if( is_null($optionId) ){
            return false;
        }
        if( $optionId==''){
            return false;
        }

        $db = Zend_Registry::get('dbAdapter');
        try{
        $db->delete('orders_bucket_meals_options',
                array(
                    'orders_bucket_meals_options.bucketItemId = '.$this->getId(),
                    'orders_bucket_meals_options.optionId = '.$optionId
                    )
            );
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
        return true;
    }


    public function addOption($optionId){
        if(is_null($optionId)){
            return false;
        }
        $option = null;
        try{
            $option = new Yourdelivery_Model_Meal_Option($optionId);
        }catch(Yourdelivery_Exception_Database_Inconsistency $e){
            return false;
        }
        $bucketOptionTable = new Yourdelivery_Model_DbTable_Order_BucketMeals_Options();
        try{
            $id = $bucketOptionTable->insert(
                array(
                    'bucketItemId' => $this->getId(),
                    'optionId' => $option->getId(),
                    'cost' => $option->getCost(),
                    'tax' => $option->getMwst(),
                    'count' => 1
                )
            );
        }catch(Exception $e){
            echo $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * delete one option from meal
     * @param int $optionId of option
     * @return boolean
     */
    public function deleteMealoption($mealId){
        if( is_null($mealId) ){
            return false;
        }
        if( $mealId==''){
            return false;
        }

        $db = Zend_Registry::get('dbAdapter');
        try{
        $db->delete('orders_bucket_meals_mealoptions',
                array(
                    'orders_bucket_meals_options.bucketItemId = '.$this->getId(),
                    'orders_bucket_meals_options.mealId = '.$mealId
                    )
            );
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
        return true;
    }


    public function addMealoption($mealId){
        if(is_null($mealId)){
            return false;
        }
        $option = null;
        try{
            $option = new Yourdelivery_Model_Meal_Mealoption($mealId);
        }catch(Yourdelivery_Exception_Database_Inconsistency $e){
            return false;
        }
        $bucketMealoptionTable = new Yourdelivery_Model_DbTable_Order_BucketMeals_Mealoptions();
        try{
            $id = $bucketMealoptionTable->insert(
                array(
                    'bucketItemId' => $this->getId(),
                    'mealId' => $option->getId(),
                    'cost' => $option->getCost(),
                    'tax' => $option->getMwst(),
                    'count' => 1
                )
            );
        }catch(Exception $e){
            echo $e->getMessage();
            return false;
        }
        return true;
    }


    /**
     * check, if bucketmeal has extra
     * @param int $extraId
     * @param int $sizeId
     * @return boolean
     */
    public function hasExtra($extraId, $sizeId){
        $sql = sprintf('select id from meal_extras_relations where mealId='.$this->getMealId().' and extraId='.$extraId.' and sizeId='.$sizeId.' LIMIT 1');
        return $this->getAdapter()->fetchRow($sql);
    }

    /**
     * check, if option for meal is in bucket
     * @param int $optionId
     * @return boolean
     */
    public function hasOptionInBucket($optionId){
        $sql = sprintf('select id from orders_bucket_meals_options where bucketItemId = '.$this->getId().' and optionId='.$optionId.'LIMIT 1');
        return $this->getAdapter()->fetchRow($sql);
    }
}
?>
