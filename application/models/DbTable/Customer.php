<?php

/**
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Customer extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @param string
     */
    protected $_name = "customers";

    /**
     * Primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * Dependent tables
     * @param array
     */
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_UserRights',
        'Yourdelivery_Model_DbTable_Customer_Company',
        'Yourdelivery_Model_DbTable_Order',
        'Yourdelivery_Model_DbTable_Order_Favourites',
        'Yourdelivery_Model_DbTable_Locations',
        'Yourdelivery_Model_DbTable_Order_CompanyGroup',
        'Yourdelivery_Model_DbTable_Department_Customer',
        'Yourdelivery_Model_DbTable_Customer_Messages',
        'Yourdelivery_Model_DbTable_Customer_FavouriteMeals'
    );

    /**
     * edit customer row
     *
     * @param integer $id   id of row to edit
     * @param array   $data data to update
     *
     * @return void
     */
    public static function edit($id, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('customers', $data, 'customers.id = ' . $id);
    }

    /**
     * set the deleted flag by passing the id in the deleted field
     *
     * @param integer $id id of customer to set deleted
     *
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('customers', array('deleted' => $id), 'customers.id = ' . $id);
    }

    /**
     * get rows
     *
     * @param string  $order order by addition
     * @param integer $limit limit for query
     * @param string  $from  offest for query
     *
     * @return Zend_DbTable_Rowset
     */
    public static function get($order = null, $limit = 0, $from = 0) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("%ftable%" => "customers"));

        if ($order != null) {
            $query->order($order);
        }

        if ($limit != 0) {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
    }

    /**
     * get a rows matching Id by given value
     *
     * @param integer $id id to find customer row by
     *
     * @return array
     */
    public static function findById($id) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "customers"))
                ->where("c.id = " . $id);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Title by given value
     *
     * @param string $title title to find customer row by
     *
     * @return array
     */
    public static function findByTitle($title) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "customers"))
                ->where("c.title = ?", $title);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Name by given value
     *
     * @param string $name name to find customer row by
     *
     * @return array
     */
    public static function findByName($name) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "customers"))
                ->where("c.name = ?", $name);

        return $db->fetchRow($query);
    }

    /**
     * find customer by name and prename
     *
     * @param string $name    name to search for
     * @param string $prename prename to search for
     *
     * @return array
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.03.2011
     */
    public static function findByNameAndPrename($name, $prename) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "customers"))
                ->where("c.name = '" . $name . "' AND c.prename = '" . $prename . "'");

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Prename by given value
     *
     * @param string $prename prename to search for
     *
     * @return array
     */
    public static function findByPrename($prename) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "customers"))
                ->where("c.prename = " . $prename);

        return $db->fetchRow($query);
    }

    /**
     * Get a rows matching Email
     * @author Alex Vait <vait@lieferando.de>
     * @since 11.06.2012
     * @param string $email emailaddress to search for
     * @param boolean $includeDeleted
     * @return array
     */
    public static function findByEmail($email, $includeDeleted = true) {

        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "customers"))
                ->where("c.email = ?", $email);

        if (!$includeDeleted) {
            $query->where('deleted = 0');
        }

        return $db->fetchRow($query);
    }

    /**
     *  @author Daniel Hahn <hahn@lieferando.de>
     * @param type $secure
     * @return array
     */
    public static function findBySecure($secure) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "customers"))
                ->where(sprintf('MD5(CONCAT(id,"%s")) = ? AND deleted=0', SALT), $secure);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Lang by given value
     *
     * @param string $lang language to search for
     *
     * @return array
     */
    public static function findByLang($lang) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "customers"))
                ->where("c.lang = " . $lang);

        return $db->fetchRow($query);
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 20.01.2012
     *
     * get a rows matching phone number by given value
     * @param varchar $tel. number
     */
    public static function findByTel($tel) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "customers"))
                ->where("c.tel = '" . $tel . "'");

        return $db->fetchRow($query);
    }

    /**
     * get customers information
     *
     * @return array
     */
    public function getInformation() {

        if (is_null($this->getId())) {
            return false;
        }
        return $this->find($this->getId())->current();
    }

    /**
     * get company associated with customer
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getCompany() {

        if (is_null($this->getId())) {
            return false;
        }

        $c = $this->getCurrent()
                ->findDependentRowset('Yourdelivery_Model_DbTable_Customer_Company');

        return $c;
    }

    /**
     * if available get the company customer
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.02.2012
     */
    public function getCompanyUser() {
        if ($this->isEmployee()) {
            $companyId = (integer) $this->getCompany()->id;
            try {
                if ($companyId <= 0) {
                    throw new Yourdelivery_Exception_Database_Inconsistency();
                }
                return new Yourdelivery_Model_Customer_Company($this->getId(), $companyId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * check if a customer is employee of ANY company
     *
     * @return boolean
     */
    public function isEmployee() {

        if (is_null($this->getId())) {
            return false;
        }
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("c" => "companys"), array("c.id", "cc.customerId"))
                ->join(array("cc" => "customer_company"), "cc.companyId = c.id", array())
                ->where("c.deleted IS NULL OR c.deleted = 0")
                ->where("cc.customerId = ?", $this->getId());


        $result = $db->fetchAll($query);
        return count($result) > 0 ? true : false;
    }

    /**
     * get max customerId
     *
     * @return integer
     */
    public function getLastCustomerId() {

        $sql = 'SELECT max(id) FROM customers';
        $result = $this->getAdapter()->query($sql)->fetchAll();

        return $result;
    }

    /**
     * Get based on current order (page) the last order of same kind and mode
     *
     * @param integer $count specify count of last orders to get (limit)
     * @param string  $mode  order mode
     * @param string  $kind  order kind
     *
     * @return array
     *
     * @author Vincent Priem <priem@lieferando.de>
     * @since 06.04.2011
     *
     * @todo refactor to Zend_Select
     */
    public function getLastOrder($count = 1, $mode = 'rest', $kind = 'priv') {

        if ($this->getId() === null) {
            return false;
        }

        $count = (integer) $count;

        $email = "";
        $current = $this->getCurrent();
        if (is_object($current)) {
            $email = $current->email;
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');

        // if this is an company order we check for budget sharing as well
        if ($kind == 'comp') {
            return $db->fetchAll(
                            "(SELECT DISTINCT(o.id)
                    FROM `orders` o
                    WHERE o.customerId = ?
                        AND o.kind = ?
                        AND o.mode = ?)

                    UNION

                    (SELECT DISTINCT(o.id)
                    FROM `orders` o
                    INNER JOIN `order_company_group` ocg ON ocg.orderId = o.id
                    WHERE ocg.customerId = ?
                        AND o.kind = ?
                        AND o.mode = ?)

                    ORDER BY id DESC
                    LIMIT " . ((integer) $count), array($this->getId(), $kind, $mode, $this->getId(), $kind, $mode)
            );
        }

        // otherwise only single and group orders
        return $db->fetchAll("
                (SELECT DISTINCT(o.id)
                FROM `orders` o
                WHERE o.customerId = ?
                    AND o.kind = ?
                    AND o.mode = ?)

                UNION

                (SELECT DISTINCT(o.id)
                FROM `orders` o
                INNER JOIN `orders_customer` oc ON o.id = oc.orderId
                WHERE oc.email = ?
                    AND o.kind = ?
                    AND o.mode = ?)

                ORDER BY id DESC
                LIMIT " . ((integer) $count), array($this->getId(), $kind, $mode, $email, $kind, $mode)
        );
    }

    /**
     * get favourites of customer
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getFavourites() {
        if (is_null($this->getId())) {
            return false;
        }

        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_Favourites');
    }

    /**
     * Get all orders of customer
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 31.07.2012
     * @return array
     */
    public function getOrders($onlyConfirmed = false, $limit = 1000, $offset = 0) {
        if (is_null($this->getId())) {
            return array();
        }
        return $this->getOrdersSelect($onlyConfirmed, $limit, $offset)->query()->fetchAll();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 31.07.2012
     * 
     * @param boolean $onlyConfirmed
     * @param integer $limit
     * @param integer $offset
     * @return Zend_Db_Select
     */
    public function getOrdersSelect($onlyConfirmed = false, $limit = 1000, $offset = 0) {
        $customer = new Yourdelivery_Model_Customer($this->getId());
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->distinct()
                ->from(array('o' => 'orders'), array(
                    'ID' => 'o.id',
                    'RID' => 'r.id',
                    'Bestellnummer' => 'o.nr',
                    'Bestellzeit' => new Zend_Db_Expr("DATE_FORMAT(o.time, '" . __("%d.%m.%Y %H:%i") . "')"),
                    'Lieferservice' => 'r.name',
                    'Lieferadresse' => new Zend_Db_Expr('CONCAT(l.street, " ", l.hausnr, " ", l.plz)'),
                    'Preis' => new Zend_Db_Expr('o.total + o.serviceDeliverCost + o.courierCost - o.courierDiscount'),
                    'mode' => 'o.mode',
                    'kind' => 'o.kind',
                    'NR' => 'o.nr',
                    'STATE' => 'o.state',
                    'Speisen' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT obm.name order by obm.name ASC SEPARATOR ', ')"),
                    'RATED' => 'rr.id',
                    'RATEABLE' => new Zend_Db_Expr('IF(SUBDATE(NOW(), INTERVAL 30 DAY) < o.deliverTime , 1,0)')
                ))
                ->join(array('r' => 'restaurants'), 'o.restaurantId = r.id', array())
                ->join(array('l' => 'orders_location'), 'l.orderId = o.id', array())
                ->join(array('c' => 'orders_customer'), 'c.orderId = o.id', array())
                ->joinLeft(array('rr' => 'restaurant_ratings'), "rr.orderId = o.id", array())
                ->joinLeft(array('obm' => 'orders_bucket_meals'), "obm.orderId=o.id", array())
                ->where("o.customerId = ?", $customer->getId())
                //->orWhere("c.email = ?", $customer->getEmail()) //removed due to perfomance and it should be known, that if a customer does not order via its account, that this order is not listed here
                ->where('o.state > -3')
                ->group('o.id')
                ->order('o.time DESC')
                ->limit($limit, $offset);

        if ($onlyConfirmed) {
            $select->where('state > 0');
        }
                
        return $select;
    }
    
    /**
     * getting count of orders without taking care of limit and offest
     * use in combination with getOrdersSelect()
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.08.2012
     *  
     * @return array( ARRAY $orders, INTEGER $countOrders)
     */
    public function getOrdersSelectWithCountRows($onlyConfirmed, $limit, $offset){
        $db = Zend_Registry::get('dbAdapterReadOnly');
        // get query for orders
        $select = $this->getOrdersSelect($onlyConfirmed, $limit, $offset);
        // add calc_found_rows to select
        $select->from(array(), array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS o.id')));
        $orders = $db->fetchAll($select);
        
        // fetch result from found rows
        $count = (integer) $db->fetchOne('SELECT FOUND_ROWS()');
        return array($orders, $count);
    }

    /**
     * query to get orders from customer with ratings
     *
     * @return Zend_Db_Table_Select
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 06.12.2011
     */
    protected static function getOrdersWithRatingsQuery() {

        $db = Zend_Registry::get('dbAdapterReadOnly');

        $select = $db->select()->from(array('o' => 'orders'), array(
                    'order_id' => 'o.id',
                    'id' => 'o.id',
                    'o.total',
                    'o.serviceDeliverCost',
                    'o.time',
                    'r.name',
                    'rr.quality',
                    'rr.delivery',
                    'rr.status',
                    'ol.street',
                    'ol.hausnr',
                    'o.delivertime',
                    'o.hashtag',
                    'restaurantId' => 'r.id',
                    'created' => 'rr.id',
                    'ratingTime' => 'rr.created'))
                ->joinLeft(array('rr' => 'restaurant_ratings'), 'rr.orderId = o.id', array())
                ->joinLeft(array('r' => 'restaurants'), "r.id = o.restaurantId", array())
                ->joinLeft(array('ol' => 'orders_location'), "ol.orderId = o.id", array())
                ->joinLeft(array('oc' => 'orders_customer'), "oc.orderId = o.id", array())
                ->where('o.state > 0');
        return $select;
    }

    /**
     * get rated orders
     *
     * @param Yourdelivery_Model_Customer_Abstract $customer customer model
     * @param integer                              $limit    specify limit of result
     * @param integer                              $start    specify offset
     *
     * @return array
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @modified Matthias Laug <laug@lieferando.de> use read only adapter
     * @since 06.12.2011
     */
    public static function getRatedOrders($customer, $limit = false, $start = 0, $order = 0) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $select = self::getOrdersWithRatingsQuery();
        $select->where('rr.delivery IS NOT NULL');
        $select->where('rr.quality IS NOT NULL');
        $union = self::createUnion($select, $customer, $limit, $start, $order);

        $result = $db->fetchAll($union);

        return $result;
    }

    /**
     * get unrated orders
     *
     * @param Yourdelivery_Model_Customer_Abstract $customer customer model
     * @param integer                              $limit    specify limit of result
     * @param integer                              $start    specify offset
     *
     * @return array
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @modified Matthias Laug <laug@lieferando.de> use read only adapter
     * @since 06.12.2011
     */
    public static function getUnratedOrders($customer, $limit = false, $start = 0, $order = 0) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = self::getOrdersWithRatingsQuery();
        $select->where('rr.delivery IS NULL');
        $select->where('rr.quality IS  NULL');
        $select->where('o.deliverTime < SUBTIME(NOW(), "1:00")');
        $select->where('o.deliverTime > SUBDATE(NOW(), INTERVAL 30 DAY)');

        $union = self::createUnion($select, $customer, $limit, $start, $order);

        $result = $db->fetchAll($union);

        return $result;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param Zend_Db_Select $select
     * @return Zend_Db_Select
     */
    public static function createUnion($select, $customer, $limit, $start, $order) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        //create the union
        $select2 = clone $select;
        //we use union of those two select to avoid or and full table scan
        $select2->where("o.customerId = ?", $customer->getId());
        $select->where("oc.email = ?", $customer->getEmail());

        $union = $db->select()->union(array($select, $select2));
        if ($limit) {
            $union->limit($limit, $start);
        }

        if ($order) {
            $union->order($order);
        } else {
            $union->order('created DESC');
        }

        return $union;
    }

    /**
     * get rights for restaurant-backend  or company-backend
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getRights() {
        return $this->getCurrent()
                        ->findDependentRowset('Yourdelivery_Model_DbTable_UserRights');
    }

    /**
     * add a right of type $what (r, c) for id $id
     *
     * @param string  $kind  kind of right ("c" = company, "r" = restaurant)
     * @param integer $refId refering id of company OR restaurant depending on $kind
     *
     * @return Zend_Db_Table_Rowset
     *
     * @todo refactor to Zend_Select
     */
    public function addRight($kind, $refId) {
        $rightsTable = new Yourdelivery_Model_DbTable_UserRights();
        return $rightsTable->insert(
                        array(
                            'refId' => $refId,
                            'customerId' => $this->getId(),
                            'kind' => $kind
                        )
        );
    }

    /**
     * delete a right of type $what (r, c) for id $id
     *
     * @param string  $kind  kind of right ("c" = company, "r" = restaurant)
     * @param integer $refId refering id of company OR restaurant depending on $kind
     *
     * @return integer
     *
     * @todo refactor to Zend_Select
     */
    public function delRight($kind, $refId) {
        $rightsTable = new Yourdelivery_Model_DbTable_UserRights();
        return $rightsTable->delete(
                        'refId = ' . $refId .
                        ' AND customerId = ' . $this->getId() .
                        ' AND kind = "' . $kind . '"'
        );
    }

    /**
     * get associated locations
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getLocations() {
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Locations');
    }

    /**
     * get associated company locations
     *
     * @param integer $companyId id of company
     *
     * @return array
     */
    public function getCompanyLocations($companyId) {
        $table = new Yourdelivery_Model_DbTable_Locations();
        return $table->select()->where('companyId=?', $companyId)->where('deleted = 0')->query()->fetchAll();
    }

    /**
     * get all unread persistent messages from user
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getPersistentMessages() {
        if (!is_object($this->getCurrent())) {
            return array();
        }
        $select = $this->select()->where('`read`=0');
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Customer_Messages', null, $select);
    }

    /**
     * create a persistent message
     *
     * @param string $type    type of persistant message
     * @param string $message content of message
     *
     * @return void
     */
    public function createPersistentMessage($type, $message) {
        $table = new Yourdelivery_Model_DbTable_Customer_Messages();
        $row = $table->createRow();
        $row->type = $type;
        $row->customerId = $this->getId();
        $row->message = $message;
        $row->save();
    }

    /**
     * check, if customer has rated a specified order
     *
     * @param Yourdelivery_Model_Order    $order    order model
     * @param Yourdelivery_Model_Customer $customer customer model
     *
     * @return boolean
     *
     * @todo refactor to Zend_Select
     */
    public function hasRated($order, $customer) {
        if (!is_object($order) || !is_object($customer)) {
            return true;
        }

        $orderId = $order->getId();
        $custId = $customer->getId();
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf("select * from restaurant_ratings where orderId=%d and customerId=%d", $orderId, $custId);
        $rate = $db->fetchOne($sql);
        if ($rate === false) {
            return false;
        }
        return true;
    }

    /**
     * is the user marked as deleted?
     *
     * @return boolean
     */
    public function isDeleted() {
        return $this->getCurrent()->deleted > 0;
    }

    /**
     * set a permanent discount for customer
     *
     * @param Yourdelivery_Model_Rabatt_Code $rabattCode rabattCode model
     *
     * @return mixed
     */
    public function setDiscount($rabattCode) {
        if ($rabattCode == null) {
            return false;
        }

        $db = Zend_Registry::get('dbAdapter');
        $db->update('customers', array('permanentDiscountId' => $rabattCode->getId()), 'customers.id = ' . $this->getId());
    }

    /**
     * get permanent discount for customer
     *
     * @return Yourdelivery_Model_Rabatt_Code
     *
     * @todo refactor to Zend_Select
     */
    public function getDiscount() {

        $result = $this->getAdapter()->fetchRow(
                'SELECT `permanentDiscountId` `rabatt`
            FROM `customers`
            WHERE `id` = ?', $this->getId()
        );

        if ($result['rabatt'] === null) {
            return null;
        }

        return new Yourdelivery_Model_Rabatt_Code(null, $result['rabatt']);
    }

    /**
     * remove permanent discount from customer
     *
     * @return void
     */
    public function removeDiscount() {
        $current = $this->getCurrent();
        $current->permanentDiscountId = null;
        $current->save();
    }

    /**
     * find data by given company and email
     *
     * @param integer $companyId id of company
     * @param string  $email     emailaddress of customer
     *
     * @return Zend_DbTable_Rowset
     *
     * @todo refactor to Zend_Select
     */
    public function findByCompanyAndEmail($companyId, $email) {
        $sql = 'select * from customers c, customer_company cc where c.id=cc.customerId and c.email="' . $email . '" and cc.companyId="' . $companyId . '";';
        return $this->getAdapter()->query($sql)->fetchAll();
    }

    /**
     * edit data by given email
     *
     * @param string $email emailaddress of customer
     * @param array  $data  data to update
     *
     * @return Zend_DbTable_Rowset
     *
     * @todo refactor to Zend_Select
     */
    public function editByEmail($email, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('customers', $data, 'customers.email = ' . $email);
    }

    /**
     * get the list of all distinct fields
     *
     * @param string $sortby addition to sort fields by
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public static function getDistinctNameId($sortby = 'name') {
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('select distinct(id), CONCAT(prename, " ", name) as name from customers where ' . FLAG_NOT_DELETED . ' order by ' . $sortby);
        $fields = $db->fetchAll($sql);
        return $fields;
    }

    /**
     * get customer satistics for customer types from several views
     *
     * @param string $type type of statistics ("comp", "reg", "notreg")
     *
     * @return Zend_DbTable_Rowset
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.04.2011
     */
    public static function getCustomerStats($type = 'reg') {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        switch ($type) {
            default:
            case 'reg':
                $data = $db->fetchAll('select * from data_view_customer_registered_by_month');
                break;
            case 'notreg':
                $data = $db->fetchAll('select * from data_view_customer_notregistered_by_month');
                break;
            case 'comp':
                $data = $db->fetchAll('select * from data_view_company_customer_registered_by_month');
                break;
        }

        //sort values in a multidimension array
        $prepared = array();
        foreach ($data as $d) {

            //hack for invalid data
            if ($d['year'] == 0 || $d['month'] == 0) {
                $d['year'] = 2009;
                $d['month'] = 3;
            }
            if ($type == 'comp') {
                if ($d['mode'] == 'fruit' || $d['mode'] == 'great') {
                    foreach ($d as $k => $v) {
                        $prepared[$d['year']][$d['month']]['great'][$k] += $v;
                    }
                } else {
                    $prepared[$d['year']][$d['month']][$d['mode']] = $d;
                }
            } else {
                $prepared[$d['year']][$d['month']] = $d;
            }
        }
        return $prepared;
    }

    /**
     * get customer satistics for orders from several views
     *
     * @param string $type type of orders ("comp", "reg", "notreg")
     *
     * @return Zend_DbTable_Rowset
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.04.2011
     */
    public static function getCustomerOrderStats($type = 'reg') {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        switch ($type) {
            default:
            case 'reg':
                $data = $db->fetchAll('select * from data_view_orders_customer_registered_by_month');
                break;
            case 'notreg':
                $data = $db->fetchAll('select * from data_view_orders_customer_unregistered_by_month');
                break;
            case 'comp':
                $data = $db->fetchAll('select * from data_view_orders_company_customer_registered_by_month');
                break;
        }

        $prepared = array();
        foreach ($data as $d) {

            //hack for invalid data
            if ($d['year'] == 0 || $d['month'] == 0) {
                $d['year'] = 2009;
                $d['month'] = 4;
            }
            if ($type == 'comp') {
                if ($d['mode'] == 'fruit' || $d['mode'] == 'great') {
                    foreach ($d as $k => $v) {
                        $prepared[$d['year']][$d['month']]['great'][$k] += $v;
                    }
                } else {
                    $prepared[$d['year']][$d['month']][$d['mode']] = $d;
                }
            } else {
                $prepared[$d['year']][$d['month']] = $d;
            }
        }
        return $prepared;
    }

    /**
     * find customer by hash of email
     *
     * @param string $hash hash of emailaddress of customer to search for
     *
     * @return Zend_DbTable_Row
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.04.2011
     */
    public static function findByEmailHash($hash) {
        $table = new Yourdelivery_Model_DbTable_Customer();
        return $table->select()->where(sprintf('md5(CONCAT("%s",email,"%s"))=?', SALT, SALT), $hash)->query()->fetch();
    }

    /**
     * check if this customer has a primary location
     *
     * @return boolean
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 11.11.2011
     *
     * @todo refactor to Zend_Select
     */
    public function hasPrimaryLocation() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return count($db->fetchAll('select id from locations where customerId=? and `primary`=1', $this->getId())) > 0 ? true : false;
    }

    /**
     * get the list of all distinct restaurants, where favourite orders have been made
     *
     * @return Zend_Db_Table_Row_Abstract
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 18.11.2011
     *
     * @todo refactor to Zend_Select
     */
    public function getFavouriteRestaurants() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = sprintf('select r.id rid, o.id oid, count(f.id) cnt from favourites f
                                join orders o on o.id=f.orderId
                                join restaurants r on o.restaurantId=r.id
                                where f.customerId=%d and r.deleted = 0 and r.status IN (0, 2, 3, 4, 5, 6, 7, 10, 14, 15, 16, 17, 18, 20, 21, 24)
                               group by r.id order by cnt desc', $this->getId());
        $r = $db->fetchAll($query);
        return $r;
    }

    /**
     * cache result of getFirstAndLastAndCountOrders in array
     *
     * @author Felix Haferkorn
     * @since 23.12.2011
     *
     * @var array
     */
    protected $_firstAndLastAndCountOrders = array();

    /**
     * get date of first order
     * get date of last order
     * get count of all orders
     *
     * @param string $email emailaddress of customer
     *
     * @author Felix Haferkorn
     * @since 23.12.2011
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getFirstAndLastAndCountOrders($email) {

        if (!isset($this->_firstAndLastAndCountOrders[$email]) || is_null($this->_firstAndLastAndCountOrders[$email])) {

            $db = Zend_Registry::get('dbAdapterReadOnly');
            $select = $db->select()->from(
                            array('o' => 'orders'), array(
                        'countOrders' => new Zend_Db_Expr('COUNT(o.id)'),
                        'deliverTimeFirstOrder' => new Zend_Db_Expr('MIN(o.deliverTime)'),
                        'deliverTimeLastOrder' => new Zend_Db_Expr('MAX(o.deliverTime)')
                    ))
                    ->join(array('oc' => 'orders_customer'), 'oc.orderId = o.id', array())
                    ->join(array('ol' => 'orders_location'), "ol.orderId = o.id", array())
                    ->where('o.state > 0')
                    ->where('o.kind = "priv"')
                    ->where('o.mode = "rest"')
                    ->where('oc.email = ?', $email);

            $this->_firstAndLastAndCountOrders[$email] = $db->fetchRow($select);
        }

        return $this->_firstAndLastAndCountOrders[$email];
    }

    /**
     * Set stauts of all fidelity points to 0
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.05.2012
     */
    public static function deleteFidelityPoints($email) {
        if (is_null($email)) {
            return;
        }

        $db = Zend_Registry::get('dbAdapter');
        $db->update('customer_fidelity_transaction', array('status' => '-2'), "email = '" . $email . "'");
    }

}
