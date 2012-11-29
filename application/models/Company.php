<?php

/**
 * @author mlaug
 */
class Yourdelivery_Model_Company extends Default_Model_Base {

    /**
     * get all departments of company
     * @var SplObjectStorage
     */
    protected $_departments = null;
    

    /**
     * store all employees as objects
     * @var SplObjectStorage
     */
    protected $_employees = null;

    /**
     * returns an empty model if no id is given
     * @author mlaug
     * @param int $id
     * @return Yourdelivery_Model_Company
     */
    function __construct($id = null) {
        //nothing is set so we return null
        if (is_null($id))
            return $this;
        parent::__construct($id);
    }

    /**
     * get all customers from database
     * @author mlaug
     * @return SplObjectStorage
     */
    public static function all() {

        $db = Zend_Registry::get('dbAdapter');
        $result = $db->query('select id from companys')->fetchAll();
        $companys = new SplObjectStorage();
        foreach ($result as $c) {
            try {
                $company = new Yourdelivery_Model_Company($c['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            $companys->attach($company);
        }
        return $companys;
    }

    /**
     * get all email addresses of one certian company
     * @author mlaug
     * @param int $companyId
     * @return array
     */
    public static function allEmployeesEmail($companyId = null) {
        if (is_null($companyId)) {
            return array();
        }

        $db = Zend_Registry::get('dbAdapter');
        return $db->query('select c.email from customers c inner join customer_company cc on c.id=cc.customerId where c.deleted=0 and cc.companyId=' . $companyId . ' order by c.email')->fetchAll();
    }

    /**
     * get related table
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Company
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Company();
        }
        return $this->_table;
    }

    /**
     * Returns Customer_Company objects of all employees of this company
     * @author mlaug
     * @return splStorageObjects
     */
    public function getEmployees() {
        if ( is_null($this->_employees) ){
            $customers = $this->getTable()->getEmployees();
            $objects = new splObjectStorage();

            foreach ($customers AS $customer) {
                try {
                    $ccust = new Yourdelivery_Model_Customer_Company($customer->customerId, $this->getId());
                    if ($ccust->isDeleted()) {
                        continue;
                    }
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    continue;
                }
                $objects->attach($ccust);
            }
            $this->_employees = $objects;
        }
        return $this->_employees;
    }

    /**
     * Returns count of employees
     * @author mlaug
     * @return int
     */
    public function getEmployeesCount() {
        return $this->getTable()->getEmployeesCount();
    }

    /**
     * Returns the company name
     * @author mlaug
     * @return string
     */
    public function getCompanyName() {
        return $this->getName();
    }

    /**
     * Returns the contact Object of this company
     * @author mlaug
     * @return Yourdelivery_Model_Contact
     */
    public function getContact() {
        try {
            return new Yourdelivery_Model_Contact($this->getContactId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
    }

    /**
     * Returns the billing contact object if this company
     * @author mlaug
     * @return Yourdelivery_Model_Contact
     */
    public function getBillingContact() {
        try {
            return new Yourdelivery_Model_Contact($this->getBillingContactId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
    }

    /**
     * returns all budget objects of this company
     * @author mlaug
     * @return splStorageObjects
     */
    public function getBudgets() {
        $table = new Yourdelivery_Model_DbTable_Company_Budgets();
        $all = $table->fetchAll('companyId = ' . $this->getId());
        $obj = new splObjectStorage();
        foreach ($all AS $budget) {
            try {
                $budget = new Yourdelivery_Model_Budget($budget->id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            $obj->attach($budget);
        }
        return $obj;
    }

    /**
     * create new relation between company address and budget
     * @author mlaug
     * @param Yourdelivery_Model_Budget $budget
     * @param Yourdelivery_Model_Location $location
     * @return int
     */
    public function addBudget($budget, $location) {

        if (!is_object($budget) || !is_object($location)) {
            return false;
        }

        $rel = new Yourdelivery_Model_DbTable_Company_Locations();
        $row = $rel->createRow();
        $row->budgetId = $budget->getId();
        $row->locationId = $location->getId();
        return $row->save();
    }

    /**
     * return all admins of this company
     * @author mlaug
     * @return SplObjectStorage
     */
    public function getAdmins() {
        $a = new SplObjectStorage();
        $admins = $this->getTable()->getAdmins();
        foreach ($admins as $admin) {
            try {
                $customer = new Yourdelivery_Model_Customer_Company($admin['id'], $this->getId());
                $a->attach($customer);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
        }
        return $a;
    }

    /**
     * returns all addresses of this company as RowSet
     * @author mlaug
     * @return Zend_Db_Rowset
     */
    public function getAddresses() {
        $table = new Yourdelivery_Model_DbTable_Locations();
        return $table->fetchAll('companyId = ' . $this->getId());
    }


    /**
     * get locations associated to this company
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.03.2011
     * @return SplObjectStorage
     */
    public function getLocations(){
        $table = new Yourdelivery_Model_DbTable_Locations();
        $locationRows = $table->fetchAll(sprintf('companyId = %d AND deleted = 0' , $this->getId()));

        $locations = new SplObjectStorage();
        foreach($locationRows as $locationRow){
            try{
                $loc = new Yourdelivery_Model_Location($locationRow['id']);
                $locations->attach($loc);
            }catch(Yourdelivery_Exception_Database_Inconsistency $e){
                $this->logger->err(sprintf('Could not create location #', $locationRow['id']));
                continue;
            }
        }
        return $locations;
    }

    /**
     * gets all Budgets that are related to a given address
     * @author mlaug
     * @param int $addressId
     * @return splStorageObjects
     */
    public function getBudgetsByAddressId($addressId) {
        $nnTable = new Yourdelivery_Model_DbTable_Company_Location();
        $all = $nnTable->fetchAll('locationId = "' . $addressId . '"');
        $collector = new SplObjectStorage();
        foreach ($all AS $budget) {
            try {
                $budget = new Yourdelivery_Model_Budget($budget->id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            $collector->attach($budget);
        }
        return $collector;
    }

    /**
     * gets all Billings of the Company filtered by $filter
     * @author mlaug
     * @param array $filter
     * @return splStorageObjects
     */
    public function getBillings($filter = null) {
        $billingTable = new Yourdelivery_Model_DbTable_Billing();
        $all = $billingTable->fetchAll('mode="company" AND refId="' . $this->getId() . '"');
        $storage = new splObjectStorage();
        foreach ($all AS $bill) {
            try {
                $bill = new Yourdelivery_Model_Billing($bill->id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            $storage->attach($bill);
        }
        return $storage;
    }
    
    /**
     * @author mlaug
     * @return Yourdelivery_Model_Billing_Balance
     */
    public function getBalance() {
        $balance = new Yourdelivery_Model_Billing_Balance();
        $balance->setObject($this);
        return $balance;
    }

    /**
     * get all departments assigned to company
     * @return SplObjectStorage
     */
    public function getDepartments($onlyCostcenters = false) {

        $departments = new SplObjectStorage();
        foreach ($this->getTable()->getDepartments() as $dep) {
            try {
                $department = new Yourdelivery_Model_Department($dep['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }

            $departments->attach($department);
        }
        $this->_departments = $departments;
        
        return $this->_departments;
    }

    /**
     * get all costcenters assigned to company
     * @author mlaug
     * @return SplObjectStorage
     */
    public function getCostcenters() {
        return $this->getDepartments(true);
    }

    /**
     * get all project numbers
     * @author mlaug
     * @return SplObjectStorage
     */
    public function getProjectNumbers($getDeletedToo = true) {
        $numbers = $this->getTable()->getProjectNumbers($getDeletedToo);
        $pnums = new SplObjectStorage();
        foreach ($numbers as $number) {
            try {
                $num = new Yourdelivery_Model_Projectnumbers($number['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            $pnums->attach($num);
        }
        return $pnums;
    }

    /**
     * Returns count of project numbers
     * @author alex
     * @return int
     */
    public function getProjectNumbersCount($getDeletedToo = true) {
        return $this->getTable()->getProjectNumbersCount($getDeletedToo);
    }

    /**
     * add project number
     * @author mlaug
     * @param Yourdelivery_Model_Projectnumer $number
     * @param boolean $intern
     * @param string $comment
     */
    public function addProjectNumber($number, $intern, $comment) {
        $pnum = new Yourdelivery_Model_Projectnumbers();
        $pnum->setNumber($number);
        $pnum->setIntern($intern);
        $pnum->setComment($comment);
        $pnum->setCompany($this);
        $pnum->save();
    }

    /**
     * check wether this company has a department or not
     * @author mlaug
     * @return boolean
     */
    public function hasDepartments() {
        $deps = $this->getDepartments();
        if ($deps->count() > 0) {
            return true;
        }
        return false;
    }

    /**
     * return bill interval
     * @author abril
     * @return int
     */
    public function getBillMode() {
        return $this->getBillInterval();
    }

    /**
     * @author mlaug
     * @return Yourdelivery_Model_Billing_Customized
     */
    public function getBillingCustomizedData() {

        //set defaults
        $default = array(
            'heading' => $this->getName(),
            'street' => $this->getStreet(),
            'hausnr' => $this->getHausnr(),
            'zHd' => null,
            'plz' => $this->getPlz(),
            'city' => $this->getOrt()->getOrt(),
            'showCostcenter' => $this->getCostcenters()->count() > 0 ? true : false, //deactivate by default, if no project or costcenter is available
            'showProject' => $this->getProjectNumbersCount() > 0 ? true : false,
            'showEmployee' => true,
            'verbose' => true,
            'projectSub' => false,
            'costcenterSub' => false,
            'template' => 'standard',
            'reminder' => 14
        );

        //merge it with defaults
        $customized = array_merge($default, $this->getBillingCustomized()->getData());
        return $customized;
    }

    /**
     * get customized billing data
     * @author mlaug
     * @since 02.10.2010
     * @return Yourdelivery_Model_Billing_Customized
     */
    public function getBillingCustomized() {
        $customized = new Yourdelivery_Model_Billing_Customized();
        $cid = $this->getTable()->getBillingCustomized();
        if ($cid === false) {
            $customized->setMode('comp');
        } else {
            $customized->load($cid['id']);
        }

        $customized->setCompany($this);
        $customized->setRefId($this->getId());

        return $customized;
    }

    /**
     * get next bill of this company
     * @author mlaug
     * @return Yourdelivery_Model_Billing_Company
     */
    public function getNextBill($from = 0, $until=0, $test = 0) {
        $mode = $this->getBillMode();
        return new Yourdelivery_Model_Billing_Company($this, $from, $until, $mode, $test);
    }

    /**
     * get ort object of location
     * @author mlaug
     * @return Yourdelivery_Model_City
     */
    public function getOrt() {
        $cid = $this->getCityId();
        try {
            return new Yourdelivery_Model_City($cid);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
    }

    /**
     * @author alex
     * @since 13.04.2011
     * @return Yourdelivery_Model_City
     */
    public function getCity() {
        if ($this->_city !== null) {
            return $this->_city;
        }

        $cid = (integer) $this->getCityId();
        if ($cid <= 0) {
            throw new Yourdelivery_Exception_Database_Inconsistency('city id has not been set');
        }

        return $this->_city = new Yourdelivery_Model_City($cid);
    }


    /**
     * returns all order objects of the company's employees
     * @author mlaug
     * @return splStorageObjects
     */
    public function getOrders($filter = array()) {
        return $this->getTable()->getOrders();
    }

    /**
     * get restaurants, having restriction on this company
     * any company assigned to restaurant is only allowed to order
     * @author alex
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRestaurantRestrictions() {
        return $this->getTable()->getRestaurantRestrictions();
    }

    /**
     * get restaurants, having restriction on this company
     * any company assigned to restaurant is only allowed to order
     * same as getRestaurantRestrictions, returning the array
     * @author alex
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRestaurantsAssociations() {
        return $this->getTable()->getRestaurantsAssociations();
    }

    /**
     * set restaurant, having restriction on this company
     * any company assigned to restaurant is only allowed to order
     * @author alex
     * @return int
     */
    public function setRestaurantRestriction($restId, $exclusive=0) {
        return $this->getTable()->setRestaurantRestriction($restId, $exclusive);
    }

    /**
     * remove restriction relationship with this restaurant
     * @author alex
     * @param int $companyId
     * @return
     */
    public function removeRestaurantRestriction($restId) {
        return $this->getTable()->removeRestaurantRestriction($restId);
    }

    /**
     * returns count of orders of the company's employees
     * @author alex
     * @return int
     */
    public function getOrdersCount() {
        return $this->getTable()->getOrdersCount();
    }

    /**
     * create html for tree ( recursive )
     * @author mlaug
     * @param SplObjectStorage $elems
     * @return string
     */
    private function _traverse($elems) {
        $html = "";
        foreach ($elems as $elem) {
            $html .= "<li>" . $elem->getName();
            if ($elem->getBilling() == 0) {
                $html .= $this->_appendDepartmentOptions($elem);
            } else {
                $html .= "( " . $elem->getIdentNr() . " )";
            }
            if ($elem->hasChilds()) {
                $html .= "<ul>";
                $html .= $this->_traverse($elem->getChilds());
                $html .= "</ul>";
            }
            $html . "</li>";
        }
        return $html;
    }

    /**
     *
     * @param Yourdelivery_Model_Departments $department
     * @return string
     */
    private function _appendDepartmentOptions($department) {
        $html = '
        <a href="/company/department/id/' . $department->getId() . '" target="_top" title="Bearbeiten">
             <img src="/media/images/yd-icons/pencil.png" alt="Bearbeiten" />
         </a>
        <a href="/company/department/del/' . $department->getId() . '" target="_top" onclick="javascript:return confirm(\'Soll diese Abteilung wirklich gelöscht werden?\')">
            <img src="/media/images/yd-icons/cross.png" alt="Abteilung löschen" />
        </a>
        ';
        return $html;
    }

    /**
     * get company name as default string
     * @return string
     */
    public function __toString() {
        $name = $this->getName();
        if (!is_string($name)) {
            return "";
        }
        return $name;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @param int $budgetId
     * @return boolean
     */
    public function hasBudgetGroup($budgetId = null) {
        $check = false;
        if (is_null($budgetId)) {
            return $check;
        }

        $budgets = $this->getBudgets();
        foreach ($budgets as $budget) {
            if ($budget->getId() == $budgetId) {
                return true;
            }
        }
        return $check;
    }

    /**
     * @author mlaug
     * @return int
     */
    public function getUnsendBillAmount() {
        return $this->getTable()->getUnsendBillAmount();
    }

    /**
     * @author mlaug
     * @return int
     */
    public function getUnpayedBillAmount() {
        return $this->getTable()->getUnpayedBillAmount();
    }

    /**
     * @author mlaug
     * @return int
     */
    public function getPayedBillAmount() {
        return $this->getTable()->getPayedBillAmount();
    }

    /**
     * check, if company has address
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.10.2010
     * @param int $addrId
     * @return boolean
     */
    public function hasAddress($addrId) {
        $table = new Yourdelivery_Model_DbTable_Locations();
        return count($table->fetchAll('companyId = "' . $this->getId() . '" AND deleted = 0')) > 0;
    }

    /**
     * TODO
     * Refactor - only for backend
     * @author alex
     * @since 14.07.2011
     */
    public function getEditlink(){
        return sprintf("<a href=\"/administration_company_edit/index/companyid/%s\">%s</a>", $this->getId(), $this->getName());        
    }        
}
