<?php

/**
 * Description of TestCaseExpectation
 *
 * @author mpantar, afrank
 */
class Yourdelivery_Model_Testing_TestCaseExpectation extends Default_Model_Base {

    protected $_table = null;

    /**
     * creates and return a instance of testCaseExpectation
     * @return this _table
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Testing_TestCaseExpectation();
        }
        return $this->_table;
    }

    /**
     * delete a expectation from testcase
     */
    public function delete() {
        try {
            $this->getTable()->delete(sprintf('id = %d', $this->getId()));
        } catch (Exception $e) {
            $this->_logger->error($e);
            return;
        }
    }

}

?>
