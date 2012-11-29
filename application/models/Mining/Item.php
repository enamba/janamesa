<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Item
 *
 * @author mlaug
 */
class Yourdelivery_Model_Mining_Item extends Default_Model_Base {

    public function  __construct($id = null) {

        if ( is_null($id) ){
            return $this;
        }

        $this->load($id,true);

    }

    //put your code here
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Mining_Item();
        }
        return $this->_table;
    }
}
?>
