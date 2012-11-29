<?php
/**
 * Description of parent meal category 
 * @author alex
 * @since 13.10.2011
 */
class Yourdelivery_Model_Meal_CategoryParent extends Default_Model_Base{

    /**
     * get current table
     * @return Yourdelivery_Model_DbTable_Meal_CategoriesParent
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Meal_CategoriesParents();
        }
        return $this->_table;
    }
    
    /**
     * get all children categories of this category
     * @return array of Yourdelivery_Model_Meal_Category objects
     * @author alex
     * @since 13.10.2011
     */
    public function getChildren(){
        $children = array();
        
        foreach($this->getTable()->getChildren() as $c){
            $cat = new Yourdelivery_Model_Meal_Category($c['id']);
            $children[] = $cat;
        }

        return $children;
    }    
}
?>
