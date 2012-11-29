<?php


/**
 * Description of testCase
 *
 * @author mpantar, afrank
 */
class Yourdelivery_Model_Testing_TestCase extends Default_Model_Base{

    protected $_table = null;

    /**
     * creates and return a instance of testCase
     * @author mpantar, afrank
     * @since 25-03-11
     * @return Yourdelivery_Model_DbTable_Testing_TestCase
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Testing_TestCase;
        }
        return $this->_table;
    }
    
    /**
     * Returns all infos for all testcases
     * @author Allen Frank <frank@lieferando.de>
     * @since 03-01-12
     * @return array Yourdelivery_Model_DbTable_Testing_TestCase
     */
    public function getAllInfos(){
        return $this->getTable()->getAllInfos();
    }

    /**
     * Returns an array an expectations in a sqlobjectstorage
     * @author mpantar, afrank
     * @since 25-03-11
     * @return Array Expectations in a sqlobjectstorage
     */
    public function getExpectations() {

        $missionRows = $this->getTable()->getExpecations($this->getId());
        $missions = new SplObjectStorage();
        foreach ($missionRows as $missionRow) {
            try {
                $missions->attach(new Yourdelivery_Model_Testing_TestCaseExpectation($missionRow['id']));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->crit('Can\'t create model in TestCaseExpectation in models/.../TestCase.php');
                continue;
            }
        }

        return $missions;
    }


    /**
     *
     * deletes one specific testcase/expectation
     * @author mpantar
     * @since 29-03-11
     */
    public function delete() {
        try {
            $this->getTable()->delete(sprintf('id = %d', $this->getId()));
        } catch (Exception $e) {
            $this->_logger->error($e);
            return;
        }
    }
    
    /**
     * returns the next id for one testcase or null if there is no next id
     * @author Allen Frank <frank@lieferando.de>
     * @since 28-03-11
     * @param int the current id of the expectation
     * @return array
     */
    public function getNextId($currentTestCaseExpId){
        $values = $this->getTable()->getNextId($this->getId(), $currentTestCaseExpId);
        if(count($values)>0){
            return $values[0];
        }
        else {
            return null;
        }
    }
    
    /**
     * returns the rows which contain the string
     * @param string $tag
     * @return array 
     */
    public static function searchForTags($tag){
        return Yourdelivery_Model_DbTable_Testing_TestCase::searchForTags($tag);
    }
}
?>
