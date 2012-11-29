<?php
/**
 * Description of Code
 * @author alex
 * @since 02.12.2010
 */
class Yourdelivery_Model_Servicetype_OpeningsHolidays extends Default_Model_Base {

    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday();
        }
        return $this->_table;
    }
}
?>
