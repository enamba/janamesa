<?php
/**
 * @author alex
 * @since 21.07.2011
 */
class Yourdelivery_Model_Meal_Ingredients extends Default_Model_Base{
    
    /**
     * Get ids and names of ingredients groups
     * @author Alex Vait <vait@lieferando.de>
     * @since 26.06.2012
     * @return array
     */
    static public function getGroups() {
        return array(
            1  => __b('Alkoholika'),
            2  => __b('Eier'),
            3  => __b('Fisch & Meeresfrüchte'),
            4  => __b('Fleisch - Geflügel'),
            5  => __b('Fleisch - Lamm'),
            6  => __b('Fleisch - Rind'),
            7  => __b('Fleisch - Schwein'),
            8  => __b('Fleisch - Sonstiges'),
            9  => __b('Gemüse'),
            10 => __b('Getreide'),
            11 => __b('Gewürze'),
            12 => __b('Käse'),
            13 => __b('Obst'),
            14 => __b('Nudeln'),
            15 => __b('Nüsse'),
            16 => __b('Pilze'),
            17 => __b('Sonstiges'),
        );
    }    
    
    /**
     * get associated table
     * @return Default_Model_DbTable_Base
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Meal_Ingredients();
        }
        return $this->_table;
    }

    /**
     * get all ingredients of the specified ingredients group
     * @author Alex Vait
     * @since 26.06.2012
     */
    public static function findByGroupId($groupId) {
        return Yourdelivery_Model_DbTable_Meal_Ingredients::findByGroupId($groupId);
    }    
}


?>
