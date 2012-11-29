<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of salesperson working time
 *
 * @author vait
 */
class Yourdelivery_Model_Salesperson_Worktime extends Default_Model_Base {
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Salesperson_Worktime();
        }
        return $this->_table;
    }
}
?>
