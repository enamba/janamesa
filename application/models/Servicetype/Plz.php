<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of deliver locations
 *
 * @author vait
 */
class Yourdelivery_Model_Servicetype_Plz extends Default_Model_Base {

    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Plz();
        }
        return $this->_table;
    }
}
?>
