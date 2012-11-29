<?php
/**
 * @author alex
 * @since 01.07.2011
 */
class Yourdelivery_Model_Meal_Type extends Default_Model_Base{
    
    /**
     * @return Yourdelivery_Model_DbTable_Meal_Types
     */
    public function getTable() {
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Meal_Types();
        }
        
        return $this->_table;
    }

    /**
     * get all types of this parent
     * @author alex
     * @since 18.07.2011
     * @return SplObjectStorage
     */
    public static function getTypesOfParent($parentId) {
        $types = new SplObjectStorage();
        
        if (is_null($parentId)) {
           $parentId = 0;
        }
        
        $typesOfParent = Yourdelivery_Model_DbTable_Meal_Types::getTypesOfParent($parentId);
        foreach ($typesOfParent as $t) {
            $tmodel = new Yourdelivery_Model_Meal_Type($t['id']);
            $types->attach($tmodel);
        }
        
        return $types;
    } 
    
    /**
     * get all children of this type
     * @author alex
     * @since 01.07.2011
     * @return Yourdelivery_Model_Meal_Type[]
     */
    public function getChildren() {
        
        $children = array();
        foreach ($this->getTable()->getChildren() as $c) {
            $children[] = new Yourdelivery_Model_Meal_Type($c['id']);
        }
        
        return $children;
    }
    
    /**
     * get parent type
     * @author alex
     * @since 11.08.2011
     * @return Yourdelivery_Model_Meal_Type
     */
    public function getParent() {
        
        if (!$this->getParentId()) {
            return null;
        }
        
        try {
            return new Yourdelivery_Model_Meal_Type($this->getParentId());
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        return null;
    }       

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 29.06.2012
     * @param string $glue
     * @return array
     */
    public function getHierarchy($glue = " > ") {
        
        $hierarchy = array($this->getName());
        
        $parent = $this->getParent();
        while ($parent instanceof Yourdelivery_Model_Meal_Type) {
            $hierarchy[] = $parent->getName();
            $parent = $parent->getParent();
        }
        
        return implode($glue, array_reverse($hierarchy));
    }       
    
}
