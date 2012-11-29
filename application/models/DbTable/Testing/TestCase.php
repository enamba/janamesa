<?php

/**
 * Description of TestCase
 *
 * @author mpantar, afrank
 */
class Yourdelivery_Model_DbTable_Testing_TestCase extends Default_Model_DbTable_Base {

    protected $_name = 'test_case';
    protected $_primary = 'id';

    /**
     * Returns an array an expectations in a sqlobjectstorage
     * @author mpantar, afrank
     * @since 25-03-11
     * @param int $testCaseId
     * @return Array Expectations in a sqlobjectstorage
     */
    public function getExpecations($testCaseId) {

        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                        ->from(array('e' => 'test_case_expectation'))
                        ->where(sprintf('e.testCaseId = %d', $testCaseId));

        return $db->fetchAll($query);
    }
    
    /**
     * Returns infos how often all testcases have been worked on today
     * @author Allen Frank <frank@lieferando.de>
     * @since 03-01-2012
     * @return Array 
     */
    public function getAllInfos(){
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                        ->from(array('tcer' => 'test_case_executors_response'), 
                                array('count' => '
                                        count(tcer.testCaseExpectationId)/
                                        (select count(tce_sub.id) from test_case_expectation tce_sub where tce_sub.testCaseId=tcex.testCaseId group by tce_sub.testCaseId)'))
                        ->join(array('tcex' => 'test_case_executor'), 'tcer.testCaseExecutorId = tcex.id', 
                                array('last' => 'max(tcex.created)'))
                        ->join(array('tc' => 'test_case'), 'tc.id=tcex.testCaseId', array('title' => 'tc.title', 'id' => 'tc.id'))
                        ->where('date(tcex.created) >= ?', date('Y-m-d'))
                        ->group('tcex.testCaseId');

        return $db->fetchAll($query);
    }
    

    /**
     * returns the next id for one testcase or null if there is no next id
     * @author Allen Frank <frank@lieferando.de>
     * @since 29-03-11
     * @param int testCaseId
     * @param int current testCaseExpectationId
     * @return array
     */
    public function getNextId($testCaseId, $currentId) {

        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                        ->from(array('e' => 'test_case_expectation'))
                        ->where(sprintf('e.testCaseId = %d AND e.id > %d', $testCaseId, $currentId));


        return $db->fetchAll($query);
    }
    
      public static function searchForTags($tag){
          
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                        ->from(array('tc' => 'test_case'))
                        ->where($db->quoteInto('tc.tag = ?', $tag));

        return $db->fetchAll($query);
      }  

}

?>
