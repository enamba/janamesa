<?php

/**
 * @author mlaug
 */
class Default_Model_DbTable_Row extends Zend_Db_Table_Row_Abstract {
   
    /**
     * use slave for this query
     *
     * @author mlaug
     * @since 23.01.2012
     * @param string|Zend_Db_Table_Abstract  $dependentTable
     * @param string                         OPTIONAL $ruleKey
     * @param Zend_Db_Table_Select           OPTIONAL $select
     * @return Zend_Db_Table_Rowset_Abstract Query result from $dependentTable
     * @throws Zend_Db_Table_Row_Exception If $dependentTable is not a table or is not loadable.
     */
    public function findDependentRowset($dependentTable, $ruleKey = null, Zend_Db_Table_Select $select = null) {
        $this->getTable()->setDefaultAdapter(Zend_Registry::get('dbAdapterReadOnly'));
        $result = parent::findDependentRowset($dependentTable, $ruleKey, $select);
        $this->getTable()->setDefaultAdapter(Zend_Registry::get('dbAdapter'));
        return $result;
    }
    
    /**
     * use slave for this query
     *
     * @author mlaug
     * @since 23.01.2012
     * @param string|Zend_Db_Table_Abstract $parentTable
     * @param string                        OPTIONAL $ruleKey
     * @param Zend_Db_Table_Select          OPTIONAL $select
     * @return Zend_Db_Table_Row_Abstract   Query result from $parentTable
     * @throws Zend_Db_Table_Row_Exception If $parentTable is not a table or is not loadable.
     */
    public function findParentRow($parentTable, $ruleKey = null, Zend_Db_Table_Select $select = null) {
        $this->getTable()->setDefaultAdapter(Zend_Registry::get('dbAdapterReadOnly'));
        $result = parent::findParentRow($parentTable, $ruleKey, $select);
        $this->getTable()->setDefaultAdapter(Zend_Registry::get('dbAdapter'));
        return $result;
    }
    
    /**
     * use slave for this query
     * 
     * @author mlaug
     * @since 23.01.2012
     * @param  string|Zend_Db_Table_Abstract  $matchTable
     * @param  string|Zend_Db_Table_Abstract  $intersectionTable
     * @param  string                         OPTIONAL $callerRefRule
     * @param  string                         OPTIONAL $matchRefRule
     * @param  Zend_Db_Table_Select           OPTIONAL $select
     * @return Zend_Db_Table_Rowset_Abstract Query result from $matchTable
     * @throws Zend_Db_Table_Row_Exception If $matchTable or $intersectionTable is not a table class or is not loadable.
     */
    public function findManyToManyRowset($matchTable, $intersectionTable, $callerRefRule = null, $matchRefRule = null, Zend_Db_Table_Select $select = null) {
        $this->getTable()->setDefaultAdapter(Zend_Registry::get('dbAdapterReadOnly'));
        $result = parent::findManyToManyRowset($matchTable, $intersectionTable, $callerRefRule, $matchRefRule, $select);
        $this->getTable()->setDefaultAdapter(Zend_Registry::get('dbAdapter'));
        return $result;
    }
    
}
