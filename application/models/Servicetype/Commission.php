<?php
/**
 * Additional comission data for defined time slots
 *
 * @author alex
 * @since 22.12.2010
 */
class Yourdelivery_Model_Servicetype_Commission extends Default_Model_Base {

    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Commission();
        }
        return $this->_table;
    }
}
?>
