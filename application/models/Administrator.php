<?php
class Yourdelivery_Model_Administrator extends Default_Model_Base{

    public function __construct($id = null) {
        if(is_null($id))
            return $this;
        parent::__construct($id);
    }

    /**
     * get related table
     * @return Yourdelivery_Model_DbTable_Admin_Access_Users
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Admin_Access_Users();
        }
        return $this->_table;
    }
}
?>