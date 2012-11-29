<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Restaurant
 *
 * @author daniel
 */
class Yourdelivery_Model_Rabatt_City extends Default_Model_Base {
    

    protected $_table = null;
    
    
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Rabatt_City();
        }
        return $this->_table;
    }
    

    public static function deleteByRabattAndCityd($rabattId, $cityId) {
        
        $db = Zend_Registry::get('dbAdapter');
        
        $db->delete('rabatt_city',sprintf("rabattId = %d and cityId = %d", $rabattId, $cityId));
        
    }


}

?>
