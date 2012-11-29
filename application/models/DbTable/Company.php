<?php

/**
 * Description of Company
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Model_DbTable_Company extends Default_Model_DbTable_Base {

    /**
     * table name
     * @var string
     */
    protected $_name = 'companys';

    /**
     * primary key
     * @var string
     */
    protected $_primary = 'id';

    /**
     * depending tables
     * @var array
     */
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Customer_Company',
        'Yourdelivery_Model_DbTable_Order_CompanyGroup',
        'Yourdelivery_Model_DbTable_Department',
        'Yourdelivery_Model_DbTable_BillingAsset',
        'Yourdelivery_Model_DbTable_Projectnumbers',
        'Yourdelivery_Model_DbTable_Restaurant_Company',
        'Yourdelivery_Model_DbTable_Locations',
        'Yourdelivery_Model_DbTable_Billing_Balance'
    );

    /**
     * edit data of company
     * 
     * @param integer $id   id of company to edit
     * @param array   $data data to update
     *
     * @return void
     */
    public static function edit($id, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('companys', $data, 'companys.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * delet depending rows in customer_company table
     * 
     * @param integer $id id of city to remove
     * 
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('companys', array('deleted' => '1'), 'companys.id = ' . $id);
        $db->delete('customer_company', 'companyId = ' . $id);
        $db->delete('restaurant_company', 'companyId = ' . $id);
    }

    /**
     * get rows
     * 
     * @param string  $order  addition to order result by something
     * @param integer $limit  some result limit
     * @param string  $offset some result offset
     * 
     * @return Zend_DbTable_Rowset_Abstract
     */
    public static function get($order=null, $limit=0, $offset=0) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("%ftable%" => "companys"));

        if ($order != null) {
            $query->order($order);
        }

        if ($limit != 0) {
            $query->limit($limit, $offset);
        }

        return $db->fetchAll($query);
    }

    /**
     * get a rows matching Id by given value
     * 
     * @param int $id id of row
     * 
     * @return Zend_DbTable_Row_Abstract
     * 
     * @deprecated
     */
    public static function findById($id) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.id = ?", $id);

        return $db->fetchRow($query);
    }

    /**
     * get actual maximal customer number
     * 
     * @return integer
     */
    public static function getActualCustNr() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        try {
            $sql = sprintf('select max(customerNr) as max from companys');
            $result = $db->fetchRow($sql);
        } catch (Zend_Db_Statement_Exception $e) {
            $db->setFetchMode(Zend_Db::FETCH_ASSOC);
            return 0;
        }

        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        return $result->max;
    }

    /**
     * get a rows matching Name by given value
     * 
     * @param string $name name to search for
     * 
     * @return Zend_DbTable_Row_Abstract
     * 
     * @deprecated
     */
    public static function findByName($name) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.name = ?", $name);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Street by given value
     * 
     * @param string $street street to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     * 
     * @deprecated
     */
    public static function findByStreet($street) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.street = ?", $street);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Hausnr by given value
     * 
     * @param varchar $hausnr hausnr to fond company by
     * 
     * @return Zend_DbTable_Row_Abstract
     * 
     * @deprecated
     */
    public static function findByHausnr($hausnr) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.hausnr = ?", $hausnr);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Plz by given value
     * 
     * @param int $plz plz to find company by
     * 
     * @return Zend_DbTable_Rowset_Abstract
     * 
     * @deprecated
     */
    public static function findByPlz($plz) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.plz = ?", $plz);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Status by given value
     * 
     * @param integer $status status to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findByStatus($status) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.status = ?", $status);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Payment by given value
     * 
     * @param varchar $payment payment to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findByPayment($payment) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.payment = ?", $payment);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Regtime by given value
     * 
     * @param string $regtime timestamp to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findByRegtime($regtime) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.created = ?", $regtime);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching ContactId by given value
     * 
     * @param varchar $contactId id of contact to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findByContactId($contactId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.contactId = ?", $contactId);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching ContactId by given value
     * 
     * @param varchar $customerNr number of customer to find company by
     * 
     * @return Zend_DbTable_Rowset_Abstract
     */
    public static function findByCustomerNr($customerNr) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.customerNr = ?", $customerNr);

        return $db->fetchAll($query);
    }

    /**
     * get a rows matching KtoNr by given value
     * 
     * @param string $ktoNr to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findByKtoNr($ktoNr) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.ktoNr = ?", $ktoNr);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching KtoBlz by given value
     * 
     * @param varchar $ktoBlz to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findByKtoBlz($ktoBlz) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.ktoBlz = ?", $ktoBlz);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching KtoName by given value
     * 
     * @param varchar $ktoName to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findByKtoName($ktoName) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.ktoName = ?", $ktoName);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Comment by given value
     * 
     * @param text $comment commment to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findByComment($comment) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.comment = ?", $comment);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching SteuerNr by given value
     * 
     * @param varchar $steuerNr to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findBySteuerNr($steuerNr) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.steuerNr = ?", $steuerNr);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Website by given value
     * 
     * @param varchar $website to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findByWebsite($website) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.website = ?", $website);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Industry by given value
     * 
     * @param varchar $industry to find company by
     * 
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findByIndustry($industry) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.industry = ?", $industry);

        return $db->fetchRow($query);
    }

    /**
     * find all employees of company
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getEmployees() {
        return $this->getCurrent()
                        ->findDependentRowset('Yourdelivery_Model_DbTable_Customer_Company');
    }

    /**
     * find $count of employees of company starting from index $start
     * 
     * @param integer $start offset to start
     * @param integer $count limit for result
     * 
     * @return Zend_DbTable_Rowset_Abstract
     */
    public function getEmployeesPage($start, $count) {
        $limit = $count ? ' limit ' . $count : '';
        $from = $start ? ' offset ' . $start : '';

        $sql = sprintf('select * from customer_company where companyId = ' . $this->getId() . $limit . $from);
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * get employees count
     * 
     * @return integer
     * 
     * @todo refactor to zend-select
     */
    public function getEmployeesCount() {
        $sql = sprintf('select count(*) as count from customer_company cc inner join customers c on c.id=cc.customerId where c.deleted=0 and companyId = ' . $this->getId());
        $result = $this->getAdapter()->fetchRow($sql);
        return $result['count'];
    }

    /**
     * find all departments of company
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     * 
     * @todo refactor to zend-select
     */
    public function getDepartments() {
        $sql = sprintf('select * from department where companyId=' . $this->getId());
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * get the list of all distinct fields
     * 
     * @param string $sortby addition for sorting
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     * 
     * @todo refactor to zend-select
     */
    public function getDistinctNameId($sortby = 'name') {
        $sql = sprintf('select distinct(id), name, customerNr from companys where COALESCE(companys.deleted,0)=0 and status=1 order by ' . $sortby);
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * get project numbers
     * 
     * @param boolean $getDeletedToo show deleted numbers too
     * 
     * @return Zend_DbTable_Rowset_Abstract
     * 
     * @todo refactor to zend-select
     */
    public function getProjectNumbers($getDeletedToo = true) {
        $getDeletedToo ?
                        $sql = sprintf('select * from projectnumbers where companyId=' . $this->getId()) :
                        $sql = sprintf('select * from projectnumbers where deleted = 0 AND companyId=' . $this->getId());

        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * find $count of project numbers of company starting from index $start
     * 
     * @param integer $start         set start offset
     * @param integer $count         set limit for result
     * @param boolean $getDeletedToo show deleted numbers too
     * 
     * @return Zend_DbTable_Rowset_Abstract
     * 
     * @todo refactor to zend-select
     */
    public function getProjectNumbersPage($start, $count, $getDeletedToo = true) {
        $limit = $count ? ' limit ' . $count : '';
        $from = $start ? ' offset ' . $start : '';

        $getDeletedToo ?
                        $sql = sprintf('select * from projectnumbers where companyId = ' . $this->getId() . $limit . $from) :
                        $sql = sprintf('select * from projectnumbers where deleted = 0 AND companyId = ' . $this->getId() . $limit . $from);

        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * get project numbers count
     * 
     * @param boolean $getDeletedToo show deleted numbers too
     * 
     * @return Zend_DbTable_Row_Abstract
     * 
     * @todo refactor to zend-select
     */
    public function getProjectNumbersCount($getDeletedToo = true) {
        $getDeletedToo ?
                        $sql = sprintf('select count(*) as count from projectnumbers where companyId = %d', $this->getId()) :
                        $sql = sprintf('select count(*) as count from projectnumbers where companyId = %d AND deleted = 0', $this->getId());

        $result = $this->getAdapter()->fetchRow($sql);
        return $result['count'];
    }

    /**
     * get restaurants, having restriction on this company
     * any company assigned to restaurant is only allowed to order
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRestaurantRestrictions() {
        if (!is_null($this->getCurrent())) {
            $row = $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Restaurant_Company');
            return $row;
        }
        return null;
    }

    /**
     * get restaurants, having restriction on this company
     * any company assigned to restaurant is only allowed to order
     * same as getRestaurantRestrictions, returning the array
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     * 
     * @todo refactor to zend-select
     */
    public function getRestaurantsAssociations() {
        $sql = sprintf('select restaurant_company.restaurantId, restaurants.name, exclusive from restaurant_company join restaurants on restaurant_company.restaurantId=restaurants.id where restaurant_company.companyId = ' . $this->getId());
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * get the number of associated restaurants
     * 
     * @return integer
     * 
     * @todo refactor to zend-select
     */
    public function getRestaurantsAssociationsCount() {
        $sql = sprintf('select count(*) as count from restaurant_company join restaurants on restaurant_company.restaurantId=restaurants.id where restaurant_company.companyId = ' . $this->getId());
        $result = $this->getAdapter()->fetchRow($sql);
        return $result['count'];
    }

    /**
     * set restaurants, having restriction on this company
     * any company assigned to restaurant is only allowed to order
     * 
     * @param integer $restId restaurantId
     * @param boolean $excl   set exclusive for this company
     * 
     * @return integer | null
     */
    public function setRestaurantRestriction($restId, $excl) {
        if (is_null($restId)) {
            return null;
        }

        if (Yourdelivery_Model_DbTable_Restaurant_Company::findByAssoc($restId, $this->getId())) {
            return null;
        }

        return Yourdelivery_Model_DbTable_Restaurant_Company::add($restId, $excl, $this->getId());
    }

    /**
     * remove company-restaurant relationship
     * 
     * @param int $restaurantId id of restaurant
     * 
     * @return integer
     */
    public function removeRestaurantRestriction($restaurantId) {
        if (is_null($restaurantId)) {
            return null;
        }
        return Yourdelivery_Model_DbTable_Restaurant_Company::remove($restaurantId, $this->getId());
    }

    /**
     * get project numbers count
     * 
     * @return integer
     * 
     * @todo refactor to zend-select
     */
    public function getOrdersCount() {
        $sql = sprintf('select count(*) as count from (select distinct orders.id from orders inner join order_company_group on orders.id=order_company_group.orderId and order_company_group.companyId=' . $this->getId() . ') t');
        $result = $this->getAdapter()->fetchRow($sql);
        return $result['count'];
    }

    /**
     * returns all order objects of the company's employees
     *
     * @return SplObjectStorage
     * 
     * @todo refactor to zend-select
     * @todo refactor to array
     */
    public function getOrders() {
        // get the orders for this company
        $sql = sprintf("SELECT orders.id as id FROM orders left join customer_company on orders.customerId=customer_company.customerId WHERE customer_company.companyId=%d order by orders.time DESC", $this->getId());
        $orders = $this->getAdapter()->fetchAll($sql);

        $storage = new splObjectStorage();
        foreach ($orders AS $ord) {
            try {
                $obj = new Yourdelivery_Model_Order($ord['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }

            $storage->attach($obj);
        }

        return $storage;
    }

    /**
     * get all admins of company
     * 
     * @return Zend_DbTable_Rowset_Abstract
     * 
     * @todo refactor to zend-select
     */
    public function getAdmins() {
        $sql = sprintf('select c.id from customers c inner join user_rights ur on ur.customerId=c.id where ur.refId=%d and ur.kind="c"', $this->getId());
        $result = $this->getAdapter()->fetchAll($sql);
        return $result;
    }

    /**
     * is the company marked as deleted?
     * 
     * @return boolean
     */
    public function isDeleted() {
        return $this->getCurrent()->deleted == 1;
    }

    /**
     * ????
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * 
     * @return Zend_DbTable_Row_Abstract
     * 
     * @todo refactor to zend-select
     * @todo comment this thing
     */
    public function getBillingCustomized() {
        $sql = "SELECT id FROM billing_customized WHERE refId=? AND mode='comp'";
        return $this->getAdapter()->fetchRow($sql, array($this->getId()));
    }

    /**
     * get all cities with companies
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     * 
     * @todo refactor to zend-select
     */
    public static function getAllCities() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf('select distinct(city.city) as city from companys inner join city on companys.cityId=city.id order by city.city');
        $result = $db->fetchAll($sql);
        return $result;
    }

    /**
     * ????
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @return integer
     * 
     * @todo refactor to zend-select
     * @todo comment this thing
     */
    public function getUnsendBillAmount() {
        $sql = "SELECT sum(amount) FROM billing WHERE refId=? AND mode='company' AND status=0";
        return intval($this->getAdapter()->fetchOne($sql, array($this->getId())));
    }

    /**
     * ????
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * 
     * @return integer
     * 
     * @todo refactor to zend-select
     * @todo comment this thing
     */
    public function getUnpayedBillAmount() {
        $sql = "SELECT sum(amount) FROM billing WHERE refId=? AND mode='company' AND status=1";
        return intval($this->getAdapter()->fetchOne($sql, array($this->getId())));
    }

    /**
     * ????
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * 
     * @return integer
     * 
     * @todo refactor to zend-select
     * @todo comment this thing
     */
    public function getPayedBillAmount() {
        $sql = "SELECT sum(amount) FROM billing WHERE refId=? AND mode='company' AND status=2";
        return intval($this->getAdapter()->fetchOne($sql, array($this->getId())));
    }

    /**
     * get associated canteeId
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 24.08.2010
     * 
     * @return integer
     * 
     * @deprecated
     */
    public function getCanteenId() {

        return $this->getAdapter()->fetchOne(
                        "SELECT `id`
            FROM `canteen_company`
            WHERE `companyId` = ?
            LIMIT 1", $this->getId()
        );
    }

    /**
     * get associated canteeId
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 13.10.2010
     * 
     * @return Zend_DbTable_Rowset
     * 
     * @deprecated
     */
    public function getCanteenIds() {

        return $this->getAdapter()->fetchAll(
                        "SELECT `canteenId`
            FROM `canteen_company`
            WHERE `companyId` = ?", $this->getId()
        );
    }

    /**
     * get companies with at least one canteen
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 14.10.2010
     * 
     * @return SplObjectStorage
     * 
     * @deprecated
     */
    public static function getCompaniesWithCanteen() {
        $sql = sprintf('
            SELECT DISTINCT `companyId`
            FROM `canteen_company` ');

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $compsArray = $db->fetchAll($sql);

        $companies = new SplObjectStorage();

        foreach ($compsArray as $compArray) {
            $comp = null;
            try {
                $comp = new Yourdelivery_Model_Company($compArray['companyId']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            $companies->attach($comp);
        }

        return $companies;
    }

    /**
     * ????? 
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.04.2011
     * 
     * @return string
     * 
     * @todo refactor to zend-select
     * @todo comment this thing
     */
    public static function getAccountsPerCompany() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchOne('select sum(employees)/count(*) from data_view_company_count_employees');
    }

    /**
     * ????? 
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.04.2011
     * 
     * @return string
     * 
     * @todo refactor to zend-select
     * @todo comment this thing
     */
    public static function getOrderPerCompanyPerEmployee() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchOne('select sum(average_orders)/count(*) from data_view_company_average_orders');
    }

    /**
     * ????? 
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.04.2011
     * 
     * @return string
     * 
     * @todo refactor to zend-select
     * @todo comment this thing
     */
    public static function getAverageBucketValue() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchOne('select sum(average_bucket_value)/count(*) from data_view_company_average_orders');
    }

}
