<?php

/**
 * Description of Department
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Department extends Default_Model_DbTable_Base {

    protected $_name = 'department';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    
    /**
     * @var array
     */
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Department_Customer',
        'Yourdelivery_Model_DbTable_Department_Projectnumbers'
    );
    
    /**   
     * @var array
     */
    protected $_referenceMap = array(
        'Company' => array(
            'columns' => 'companyId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Company',
            'refColumns' => 'id'
        )
    );

    /**
     * get a rows matching Name by given value
     * 
     * @param string $name name of department to search for
     * 
     * @return Zend_Db_Table_Row
     */
    public static function findByName($name) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "department"))
                ->where("c.name = '" . $name . "'");

        return $db->fetchRow($query);
    }

    /**
     * find by name and companyId
     * 
     * @param string  $name      name of department
     * @param integer $companyId id of department
     * 
     * @return Zend_DbTable_Row
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 28.07.2011
     */
    public static function findByNameAndCompanyId($name, $companyId) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "department"))
                ->where("c.name = '" . $name . "' AND companyId = " . $companyId);

        return $db->fetchRow($query);
    }

    /**
     * find by identNr
     * 
     * @param string  $identNr   ident nr to search for
     * @param integer $companyId id of company
     * 
     * @return Zend_DbTable_Rowset
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 03.03.2011
     */
    public static function findByIdentNr($identNr, $companyId = null) {
        $db = Zend_Registry::get('dbAdapter');

        if (is_null($companyId)) {
            $query = $db->select()
                    ->from(array("c" => "department"))
                    ->where("c.identNr = '" . $identNr . "'");
        } else {
            $query = $db->select()
                    ->from(array("c" => "department"))
                    ->where("c.identNr = '" . $identNr . "' AND companyId = $companyId");
        }

        return $db->fetchRow($query);
    }

    /**
     * create relation between customer and department
     * 
     * @param integer $customerId id of employee to add
     * 
     * @return boolean
     */
    public function addEmployee($customerId = null) {
        if (is_null($customerId) || is_null($this->getId())) {
            return false;
        }

        $table = new Yourdelivery_Model_DbTable_Department_Customer();
        $row = $table->createRow();
        $row->customerId = $customerId;
        $row->departmentId = $this->getId();
        $row->save();

        return true;
    }

    /**
     * remove all employees from this relation
     * 
     * @return mixed
     * 
     * @todo refactor return value to boolean
     */
    public function resetEmployees() {
        if (is_null($this->getId())) {
            return false;
        }
        foreach ($this->getEmployees() as $empl) {
            $empl->delete();
        }
    }

    /**
     * get employees for department
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function getEmployees() {
        if (is_null($this->getId())) {
            return null;
        }

        return $this->getCurrent()
                        ->findDependentRowset('Yourdelivery_Model_DbTable_Department_Customer');
    }

    /**
     * get projectnumbers of department
     * 
     * @return Zend_Db_Table_Rowset|null 
     * 
     * @todo refactor to Zend_Select
     */
    public function getProjectNumbers() {
        if (is_null($this->getId())) {
            return null;
        }

        /**
         * @TODO: WTF: we lose the adapter here
         */
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('select projectnumbersId from department_projectnumbers where departmentId = %d', $this->getId());
        return $db->fetchAll($sql);
    }

    /**
     * remove projectnumber
     * 
     * @param integer $id id of projectnumber to delete
     * 
     * @return mixed
     * 
     * @todo refactor return value to boolean
     */
    public function removeProjectNumber($id) {
        if (is_null($this->getId())) {
            return false;
        }

        $relation = new Yourdelivery_Model_DbTable_Department_Projectnumbers();
        $relation->delete(sprintf('projectnumbersId = %d and departmentId = %d', $id, $this->getId()));
    }

    /**
     * add projectnumber
     * 
     * @param string  $nr      number of projectnumber to add
     * @param boolean $intern  flag for internal only
     * @param string  $comment comment for projectnumber
     * 
     * @return mixed
     * 
     * @todo refactor return value to boolean
     */
    public function addProjectNumber($nr, $intern, $comment) {

        if (is_null($this->getId())) {
            return false;
        }

        $table = new Yourdelivery_Model_DbTable_Projectnumbers();
        $relation = new Yourdelivery_Model_DbTable_Department_Projectnumbers();

        //create new projectnumber
        $row = $table->createRow();
        $row->number = $nr;
        $row->comment = $comment;
        $row->intern = $intern;
        $id = $row->save();

        //append to current department
        $row = $relation->createRow();
        $row->projectnumbersId = $id;
        $row->departmentId = $this->getId();
        $row->save();

        return $id;
    }

    /**
     * find by companyId
     * 
     * @param integer $companyId id of company to search for
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function findByCompanyId($companyId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "department"))
                ->where("c.companyId = '" . $companyId . "'");

        return $db->fetchAll($query);
    }

    /**
     * find by name
     * 
     * @param integer $id   id of department to search for
     * @param string  $name name of department to search for
     * 
     * @return Zend_Db_Table_Row
     */
    public function getIdByName($id, $name) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from("department")
                ->where("companyId = " . $id . " and name = '" . $name . "'");

        return $db->fetchRow($query);
    }

}
