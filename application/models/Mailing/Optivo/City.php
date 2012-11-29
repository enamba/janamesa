<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of City
 *
 * @author daniel
 */
class Yourdelivery_Model_Mailing_Optivo_City extends Default_Model_Base {
    
    protected $_table = null;
    
           
    public function getTable() {
         if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Mailing_Optivo_City();
        }
        return $this->_table;
    }
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.07.2012
     * @param int $mailingId
     * @param int $cityId 
     */
    public static function deleteByMailingAndCityId($mailingId, $cityId) {
        
        $db = Zend_Registry::get('dbAdapter');
        
        $db->delete('mailing_optivo_city',sprintf("mailingId = %d and cityId = %d", $mailingId, $cityId));
        
    }
}


