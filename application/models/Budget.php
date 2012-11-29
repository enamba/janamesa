<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Budget
 *
 * @author mlaug
 */
class Yourdelivery_Model_Budget extends Default_Model_Base {

    /**
     * stores all times when a budget is valid
     * @var Yourdelivery_Model_DbTable_Company_BudgetsTimes
     */
    protected $_times;

    /**
     * create new budget model and locally set the times it is available
     * @param int $budgetId
     */
    function __construct($id = null) {
        //nothing is set so we return null
        if (is_null($id)) {
            return $this;
        }
        $this->_times = new Yourdelivery_Model_DbTable_Company_BudgetsTimes();
        parent::__construct($id);
    }

    /**
     * get budgetTime(s)
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $time (optional)
     * @return array
     */
    public function getBudgetTimes($time = null) {
        // get all budgetTimes for this budget
        if (is_null($time)) {
            $result = $this->getTable()->getBudgetTimesAll($this->getId());
            $budgetTimes = array();
            foreach ($result as $key => $res) {
                // solve conflict between our weekdays and those of mysql
                $budgetTimes[$res['day']][$key]['id'] = $res['id'];
                $budgetTimes[$res['day']][$key]['from'] = strtotime($res['from']);
                $budgetTimes[$res['day']][$key]['until'] = strtotime($res['until']);
                $budgetTimes[$res['day']][$key]['amount'] = $res['amount'];
            }
            return $budgetTimes;
        } else {

            $day = date('w', $time);
            if (!in_array($day, array(0, 1, 2, 3, 4, 5, 6))) {
                return null;
            }

            // todo: MERGE times for one day
            return $this->getTable()->getBudgetTimes($this->getId(), $day);
        }
    }

    /**
     * @todo: reimplement
     */
    public function getBudgetTimesMerged() {
        
    }

    /**
     * add budgetTime
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $start
     * @param int $end
     * @param int $amount
     * @return int newId
     */
    public function addBudgetTime($day, $start, $end, $amount) {
        // TODO: check correctness
        return $this->getTable()->addBudgetTime($this->getId(), $day, $start, $end, $amount);
    }

    /**
     * add budgetTimes
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param array $times
     * @return boolean
     */
    public function addBudgetTimes(array $times) {
        $db = Zend_Registry::get('dbAdapter');
        $db->beginTransaction();

        foreach ($times as $time) {
            $this->addBudgetTime($times['day'], $times['start'], $times['end'], $times['amount']);
        }

        $db->commit();
    }

    /**
     * delete budgetTime
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $budgetTimeId
     * @return boolean
     */
    public function removeBudgetTime($id) {

        return $this->getTable()->removeBudgetTime($this->getId(), $id);
    }

    /**
     * delete budgetTime(s) at specified day
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $day
     * @return int count deleted rows
     */
    public function removeBudgetTimeAtDay($day) {

        return $this->getTable()->removeBudgetTimeAtDay($day);
    }

    /**
     * delete budgetTime(s) at specified day
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $day
     * @return int count deleted rows
     */
    public function removeBudgetTimesAll() {

        return $this->getTable()->removeBudgetTimesAll($this->getId());
    }

    /**
     * remove budgetTimes
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param array $ids
     * return boolean
     */
    public function removeBudgetTimes(array $ids) {
        $db = Zend_Registry::get('dbAdapter');
        $db->beginTransaction();

        foreach ($ids as $id) {
            $this->removeBudgetTime($id);
        }

        $db->commit();
        return true;
    }

    /**
     * bin employees to this budget
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param array Yourdelivery_Model_Customer_Company $employees
     * return boolean
     */
    public function bindEmployees(array $employees) {
        $db = Zend_Registry::get('dbAdapter');
        $db->beginTransaction();

        $success = false;
        foreach ($employees as $empl) {
            // remove binding
            $this->removeMember($empl);
            // create new binding
            $success = $success && $this->addMember($empl);
        }

        if (!$success) {
            $db->rollback();
            return false;
        }

        $db->commit();
        return true;
    }

    /**
     * remove relation to budget
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $employeeId
     * @return boolean
     */
    public function removeMember($employeeId) {
        $table = new Yourdelivery_Model_DbTable_Customer_Company();
        return $table->update(
                array(
                    'budgetId' => '0'
                ), 'customerId = ' . $employeeId . ' AND budgetId = ' . $this->getId()
        );
    }

    /**
     * add an employee to a budget group
     * @author mlaug, fhaferkorn
     * @param int $employeeId
     * @return boolean
     */
    public function addMember($employeeId, $checkCompany = true) {

        if ($checkCompany) {
            $empl = null;
            try {
                $empl = new Yourdelivery_Model_Customer_Company($employeeId, $this->getCompany()->getId());
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                // customer is not employee of this company
                return false;
            }
        }

        $customerTable = new Yourdelivery_Model_DbTable_Customer_Company();
        $customerTable->update(
                array(
                    'budgetId' => $this->getId()
                ), 'customerId = ' . $employeeId
        );
        return true;
    }

    /**
     * get Company
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @return Yourdelivery_Model_Company
     */
    public function getCompany() {
        if (is_null($this->getId())) {
            return null;
        }
        $row = Yourdelivery_Model_DbTable_Company_Budgets::findById($this->getId());

        $company = null;
        try {
            $company = new Yourdelivery_Model_Company($row['companyId']);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
        return $company;
    }

    /**
     * Fetches all belonging members of the budget
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 19.10.2010
     * @return SplObjectStorage Yourdelivery_Model_Customer_Company
     */
    public function getMembers() {
        $table = new Yourdelivery_Model_DbTable_Customer_Company();
        $company = $this->getCompany();
        if (!is_object($company)) {
            return null;
        }

        $companyId = $company->getId();

        $result = $table->getMembers($this->getId(), $companyId);

        $members = new SplObjectStorage();
        foreach ($result as $mem) {
            $member = null;
            try {
                $member = new Yourdelivery_Model_Customer_Company($mem['customerId'], $companyId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            $members->attach($member);
        }
        return $members;
    }

    /**
     * remove address from budget
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $locationId
     * @return boolean
     */
    public function removeLocation($locationId) {
        if (!$this->hasLocation($locationId)) {
            return false;
        }

        $table = new Yourdelivery_Model_DbTable_Company_Locations();
        return count($table->delete('budgetId = ' . $this->getId() . ' AND locationId = ' . $locationId)) > 0;
    }

    /**
     * check, if an location is associated to budget
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.03.2011
     *
     * @param int $locationId
     * @return boolean
     */
    public function hasLocation($locationId) {
        $table = new Yourdelivery_Model_DbTable_Company_Locations();
        return count($table->fetchAll(sprintf('budgetId = %d AND locationId = %d', $this->getId(), $locationId))) > 0;
    }

    /**
     * get associated table
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Company_Budgets
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Company_Budgets();
        }
        return $this->_table;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.03.2011
     * @param integer $locationId
     */
    public function addLocation($locationId) {
        // check, if company has address
        if (!is_object($this->getCompany())) {
            return false;
        }

        if ($this->hasLocation($locationId)) {
            return true;
        }

        try{
            $location = new Yourdelivery_Model_Location($locationId);
        }catch(Yourdelivery_Exception_Database_Inconsistency $e){
            return false;
        }

        if(!is_object($location->getCompany())){
            $this->logger->warn('try to add location to budget, that was not a company location');
            return false;
        }

        if($location->getCompany()->getId() != $this->getCompany()->getId()){
            $this->logger->warn('try to add location to budget, that was not associated to company of budget');
            return false;
        }

        $table = new Yourdelivery_Model_DbTable_Company_Locations();
        $row = $table->createRow();
        $row->budgetId = $this->getId();
        $row->locationId = $locationId;
        $ckeck = (integer) $row->save();

        return $ckeck > 0 ? true : false;
    }

    /**
     * get location associated to budget
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.03.2011
     *
     * @return SplObjectStorage
     */
    public function getLocations() {

        $locations = new SplObjectStorage();
        if (is_null($this->getId())) {
            return $locations;
        }

        $table = new Yourdelivery_Model_DbTable_Company_Locations();
        $relationRows = $table->fetchAll(sprintf('budgetId = %d', $this->getId()));

        foreach ($relationRows as $row) {
            try {
                $locations->attach(new Yourdelivery_Model_Location($row['locationId']));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->_logger->err(sprintf('Could not create location #%d', $row['locationId']));
                continue;
            }
        }
        return $locations;
    }

    /**
     * 1) set customer_company budgetId = 0 for all members of budget
     * 2) delete company_budget_times rows
     * 3) delete budget rows
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.03.2011
     */
    public function delete() {
        if (is_null($this->getId())) {
            return false;
        }

        $db = Zend_Registry::get('dbAdapter');
        $db->beginTransaction();
        try {
            // set customer-relation to 0
            $relTable = new Yourdelivery_Model_DbTable_Customer_Company();
            $relTable->update(array('budgetId' => 0), sprintf('budgetId = %d', $this->getId()));

            // delete budget times
            $budgetTimesTable = new Yourdelivery_Model_DbTable_Company_BudgetsTimes();
            $budgetTimesTable->delete(sprintf('budgetId = %d', $this->getId()));

            // delete from budget table
            $budgetTable = $this->getTable();
            $budgetTable->delete(sprintf('id = %d', $this->getId()));
            
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $db->rollback();
            return false;
        }
        $db->commit();
        return true;
    }

}
