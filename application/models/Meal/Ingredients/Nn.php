<?php
/**
 * @author Alex Vait
 * @since 27.06.2012
 */
class Yourdelivery_Model_Meal_Ingredients_Nn extends Default_Model_Base{
    
    /**
        * Get associated table
        * @author Alex Vait <vaitqlieferando.de>
        * @since 27.06.2012
        * @return Yourdelivery_Model_DbTable_Meal_Ingredients_Nn
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Meal_Ingredients_Nn();
        }
        return $this->_table;
    }
}


?>
