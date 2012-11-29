<?php

/**
 * Description of Base
 *
 * @package core
 * @subpackage model
 * @author mlaug
 */
class Default_Model_DbTable_Base extends Zend_Db_Table_Abstract {

    /**
     * Current id working on
     * @var int
     */
    protected $_id = null;

    /**
     * Current row
     * @var Zend_Db_Table_Row_Abstract
     */
    protected $_current = null;
    
    /**
     * @var string 
     */
    protected $_defaultSource = self::DEFAULT_DB;
    
    /**
     * Classname for row
     * @var string
     */
    protected $_rowClass = 'Default_Model_DbTable_Row';
    
    /**
     * Classname for rowset
     * @var string
     */
    protected $_rowsetClass = 'Default_Model_DbTable_Rowset';

    /**
     * set current primary key value of row
     * @author mlaug
     * @param int $id
     */
    public function setId($id) {
        $this->_id = $id;
        $this->_current = null;
    }

    /**
     * get current primary key value
     * @author mlaug
     * @return int;
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @author mlaug
     * reset current database object
     */
    public function resetCurrent() {
        $this->_current = null;
    }

    /**
     * set current database object
     * @author mlaug
     * @param Zend_Db_Table_Row_Abstract $current 
     */
    public function setCurrent(Zend_Db_Table_Row_Abstract $current) {
        
        if ($current->id == $this->getId()) {
            $this->_current = $current;
        }
    }

    /**
     * get our current rowset if an id is set
     * @author mlaug
     * @return Zend_db_Table_Row_Abstract
     */
    public function getCurrent() {
        
        if ($this->_current === null) {
            $this->setDefaultAdapter(Zend_Registry::get('dbAdapterReadOnly'));
            if ($this->getId() === null) {
                return null;
            }

            $id = intval($this->getId());
            $find = $this->find($id);
            $current = $find->current();

            if ($current === null) {
                throw new Yourdelivery_Exception_Database_Inconsistency('Element with id ' . $this->getId() . ' cannot not be found in table ' . $this->_name);
            }
            $this->_current = $current;
            $this->setDefaultAdapter(Zend_Registry::get('dbAdapter'));
        }

        return $this->_current;
    }

    /**
     * insert a set of data into table
     * if check is set true, the given array $data will be checked
     * to validate that there is not key present matching no table column
     * @author mlaug
     * @param array $data
     * @param boolean $check
     */
    public function insert(array $data, $check = true) {

        //truncate all unwanted array elements
        $_data = array();
        if ($check === true) {
            $fields = $this->info(Zend_Db_Table_Abstract::COLS);
            foreach ($data as $field => $value) {
                if (in_array($field, $fields)) {
                    if (is_string($value)) {
                        $_data[$field] = trim($value);
                    } else {
                        $_data[$field] = $value;
                    }
                }
            }
        } else {
            $_data = $data;
        }
        //insert data into table
        return parent::insert($_data);
    }

    /**
     * only update fields that exist
     * check colummns against array keys of $data
     * if an value is empty, we ignore it if $skipEmptyValue is set true
     * @author mlaug
     * @param array $data
     * @param string $where
     * @param boolean $check
     */
    public function update(array $data, $where, $check = true, $skipEmptyValues = false) {
        //truncate all unwanted array elements
        if ($check === true) {
            $_data = array();
            $fields = $this->info(Zend_Db_Table_Abstract::COLS);
            foreach ($data as $field => $value) {
                if (in_array($field, $fields) && (!$skipEmptyValues || $value != '' )) {
                    if (is_string($value)) {
                        $_data[$field] = trim($value);
                    } else {
                        $_data[$field] = $value;
                    }
                }
            }
        } else {
            $_data = $data;
        }
        //insert data into table
        parent::update($_data, $where);
    }

    /**
     * @todo: comment
     * @param string $table
     * @param string $field
     * @param string $cfield
     * @param string $cvalue
     * @return mixed boolean|string
     */
    public function getField($table, $field, $cfield, $cvalue) {
        $qry = $this->getAdapter()->query(sprintf("SELECT %2\$s FROM %1\$s WHERE %3\$s = '%4\$s' LIMIT 1", $table, $field, $cfield, $cvalue))->fetch();
        if (is_array($qry)) {
            return $qry[$field];
        } else {
            return false;
        }
    }

    /**
     * get count of rows
     * @author mlaug
     * @param string $where
     * @return int
     */
    public function getCount($where) {
        $table = $this->_name;
        if (is_null($table)) {
            return 0;
        }

        if (is_null($where)) {
            $where = 'true';
        }

        $sql = sprintf("SELECT count(id) as count FROM %s WHERE %s", $table, $where);
        $rowCount = $this->getAdapter()->fetchRow($sql);

        return intval($rowCount['count']);
    }

    /**
     * switch to slave for fetchAll
     * 
     * @author mlaug
     * @since 23.01.2012
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null) {
        $this->setDefaultAdapter(Zend_Registry::get('dbAdapterReadOnly'));
        $result = parent::fetchAll($where, $order, $count, $offset);
        $this->setDefaultAdapter(Zend_Registry::get('dbAdapter'));
        return $result;
    }
    
    /**
     * switch to slave for fetchRow
     *
     * @author mlaug
     * @since 23.01.2012
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @return Zend_Db_Table_Row_Abstract|null The row results per the
     *     Zend_Db_Adapter fetch mode, or null if no row found.
     */
    public function fetchRow($where = null, $order = null) {
        $this->setDefaultAdapter(Zend_Registry::get('dbAdapterReadOnly'));
        $result = parent::fetchRow($where, $order);
        $this->setDefaultAdapter(Zend_Registry::get('dbAdapter'));
        return $result;
    }

    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.07.2012 
     * @param bool $withFromPart Whether or not to include the from part of the select based on the table
     * @return Default_Model_DbTable_Select
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART) {
        
        $select = new Default_Model_DbTable_Select($this);
        if ($withFromPart == self::SELECT_WITH_FROM_PART) {
            $select->from($this->info(self::NAME), Zend_Db_Table_Select::SQL_WILDCARD, $this->info(self::SCHEMA));
        }
        return $select;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.07.2012 
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapterReadOnly() {
        
        return Zend_Registry::get('dbAdapterReadOnly');
    }
    
}
