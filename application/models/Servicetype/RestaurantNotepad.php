<?php
/**
 * Description of restaurant notepad
 *
 * @author vait
 * @since 07.10.2010
 */
class Yourdelivery_Model_Servicetype_RestaurantNotepad extends Default_Model_Base {

    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Notepad();
        }
        return $this->_table;
    }
}
?>
