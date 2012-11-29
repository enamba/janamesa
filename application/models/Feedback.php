<?php

/**
 * Description of Feedback
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class Yourdelivery_Model_Feedback extends Default_Model_Base {

    
    public function __construct($id = null) {
        if(is_null($id))
            return $this;
        parent::__construct($id);
    }

    
    /**
     * get related table
     * @return Yourdelivery_Model_DbTable_Feedback
     */
    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Feedback();
        }
        return $this->_table;
    }


}

