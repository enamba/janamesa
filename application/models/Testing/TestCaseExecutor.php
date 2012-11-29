<?php

/**
 * Description of TestCaseExecutor
 *
 * @author mpantar, afrank
 */
class Yourdelivery_Model_Testing_TestCaseExecutor extends Default_Model_Base {

    protected $_table = null;

    /**
     * creates and return a instance of testCaseExecutor
     * @return this _table
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Testing_TestCaseExecutor();
        }
        return $this->_table;
    }

}

?>
