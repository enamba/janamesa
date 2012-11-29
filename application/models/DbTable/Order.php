<?php

/**
 * Description of Orders
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Order extends Default_Model_DbTable_Base {

    protected $_name = "orders";
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Order_Favourites',
        'Yourdelivery_Model_DbTable_Restaurant_Ratings',
        'Yourdelivery_Model_DbTable_Order_Status',
        'Yourdelivery_Model_DbTable_Order_GroupNN',
        'Yourdelivery_Model_DbTable_Order_Group',
        'Yourdelivery_Model_DbTable_Order_CompanyGroup',
        'Yourdelivery_Model_DbTable_Order_BucketMeals',
        'Yourdelivery_Model_DbTable_Order_Customer',
        'Yourdelivery_Model_DbTable_Order_Location',
        'Yourdelivery_Model_DbTable_Prompt_Tracking'
    );
    protected $_referenceMap = array(
        'Customer' => array(
            'columns' => 'customerId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Customer',
            'refColumns' => 'id'
        ),
        'Restaurant' => array(
            'columns' => 'restaurantId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Restaurant',
            'refColumns' => 'id'
        )
    );

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public static function edit($id, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('orders', $data, 'orders.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('orders', 'orders.id = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order = null, $limit = 0, $from = 0) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("%ftable%" => "orders"));

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
     * @param int $id
     */
    public static function findById($id) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("o.id = ?", $id);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Nr by given value
     * @param varchar $nr
     */
    public static function findByNr($nr) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("o.nr = ?", $nr);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Nr by given value
     * @param varchar $nr
     */
    public static function findByNrMd5($nr) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("MD5(CONCAT('" . SALT . "', o.nr)) = ?", $nr);
        return $db->fetchRow($query);
    }

    /**
     * get a row matching id by given md5-string
     * @param string id
     * @return array row
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public static function findByIdMd5($id) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("MD5(o.id) = ?", $id);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching RestaurantId by given value
     * @param int $restaurantId
     */
    public static function findByRestaurantId($restaurantId) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("o.restaurantId = ?", $restaurantId);

        return $db->fetchAll($query);
    }

    /**
     * get a rows matching CustomerId by given value
     * @param int $customerId
     */
    public static function findByCustomerId($customerId) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("o.customerId = ?", $customerId);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Pickup by given value
     * @param tinyint $pickup
     */
    public static function findByPickup($pickup) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("o.pickup = ?", $pickup);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Kind by given value
     * @param varchar $kind
     */
    public static function findByKind($kind) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("o.kind = ?", $kind);

        return $db->fetchAll($query);
    }

    /**
     * get a rows matching Mode by given value
     * @param varchar $mode
     */
    public static function findByMode($mode) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("o.mode = ?", $mode);

        return $db->fetchAll($query);
    }

    /**
     * get a rows matching BillCompany by given value
     * @param int $billCompany
     */
    public static function findByBillCompany($billCompany) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("o.billCompany = ?", $billCompany);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching BillRest by given value
     * @param int $billRest
     */
    public static function findByBillRest($billRest) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("o.billRest = ?", $billRest);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Id by given value
     *
     * @param int $rabattCodeId
     *
     * @return Zend_Db_Table_Rowset_Abstract
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 04.04.2012
     */
    public static function findByRabattCodeId($rabattCodeId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("o.rabattCodeId = ?", $rabattCodeId);

        return $db->fetchAll($query);
    }

    /**
     * get status of order
     * @author mlaug
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getState() {
        // get the latest set state
        $states = $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_Status');
        $highest = 0;
        $oldState = null;
        $toReturn = new stdClass();
        $toReturn->status = 0;
        foreach ($states AS $state) {
            if ($state->created > $highest) {
                $toReturn = $state;
                $highest = $state->created;
            } else if ($state->created == $highest) {
                $toReturn = ($state->id > $oldState->id) ? $state : $oldState;
            }
            $oldState = $state;
        }


        return $toReturn;
    }

    /**
     * Get status of order
     * @author mlaug
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getStates() {

        return $this->getCurrent()
                        ->findDependentRowset('Yourdelivery_Model_DbTable_Order_Status');
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getEbankingRefundTransactions() {

        return $this->getCurrent()
                        ->findDependentRowset('Yourdelivery_Model_DbTable_Ebanking_Refund_Transactions');
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getEbankingTransactions() {

        return $this->getCurrent()
                        ->findDependentRowset('Yourdelivery_Model_DbTable_Ebanking_Transactions');
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getPaypalTransactions() {

        return $this->getCurrent()
                        ->findDependentRowset('Yourdelivery_Model_DbTable_Paypal_Transactions');
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getHeidelpayWpfTransactions() {

        return $this->getCurrent()
                        ->findDependentRowset('Yourdelivery_Model_DbTable_Heidelpay_Wpf_Transactions');
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getHeidelpayXmlTransactions() {

        return $this->getCurrent()
                        ->findDependentRowset('Yourdelivery_Model_DbTable_Heidelpay_Xml_Transactions');
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.03.2012
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getAdyenTransaction() {
        return $this->getCurrent()
                        ->findDependentRowset('Yourdelivery_Model_DbTable_Adyen_Transactions');
    }

    /**
     * get history of status
     * @author alex
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getStateHistory() {
        // get all set state
        $table = new Yourdelivery_Model_DbTable_Order_Status();
        return $table->fetchAll($table->select()
                                ->where('orderId = ?', $this->getId())
                                ->order('id DESC'));
    }

    /**
     * just a helper, to throw errors on calling setStatus of dbTable directly
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $status
     * @param string $comment
     * @param boolean $useTransaction
     * @return boolean
     */
    public function _setStatus($status, Yourdelivery_Model_Order_StatusMessage $comment, $useTransaction) {
        return $this->setStatus($status, $comment, $useTransaction);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $status
     * @param string $comment
     * @param boolean $useTransaction
     * @return boolean
     */
    protected function setStatus($status, Yourdelivery_Model_Order_StatusMessage $comment, $useTransaction) {
        if ($useTransaction) {
            $db = Zend_Registry::get('dbAdapter');
            $db->beginTransaction();
        }

        $log = Zend_Registry::get('logger');

        try {

            //save in order table
            $orderRow = $this->getCurrent();
            $oldStatus = $orderRow->state;
            $orderRow->state = $status;
            $result = $orderRow->save();

            $log->debug(sprintf('Status Wechsel in Order Tabelle: #%s - alter Status: %s - neuer Status: %s', $this->getId(), $oldStatus, $orderRow->state));

            //create history row and save
            $row = new Yourdelivery_Model_DbTable_Order_Status();
            $resultStatusRow = $row->createRow(array(
                        'orderId' => $this->getId(),
                        'status' => $status,
                        'comment' => $comment->getRawMessage(),
                        'message' => $comment->__toString()
                    ))->save();

            if ($result != $this->getId() || !is_numeric($resultStatusRow)) {
                $log->crit('Failed new order status entry id: ' . $result . ', status: ' . $orderRow->state);
                $errorMsg = 'Fehler bei Status Eintrag in Order Tabelle :' . $this->getId() . ' != ' . $result . " , status: " . $status;
                Yourdelivery_Sender_Email::error($errorMsg . "\n");
                $log->err($errorMsg);

                $useTransaction ? $db->rollback() : null;
                return false;
            } else {
                $log->debug('New order status entry id: ' . $result . ', status: ' . $orderRow->state);
                $useTransaction ? $db->commit() : null;
                return true;
            }
        } catch (Exception $e) {
            $useTransaction ? $db->rollback() : null;
            $log->err($e->getMessage());
            Yourdelivery_Sender_Email::error($e->getMessage() . $e->getTraceAsString(), true);
            return false;
        }
    }

    /**
     * get ratings of order
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRating() {
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Restaurant_Ratings');
    }

    /**
     * return favourite attributes
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getFavourite() {
        if (!is_null($this->getId()))
            return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_Favourites');
        return null;
    }

    /**
     * check if order is favourite
     * @return boolean
     */
    public function isFavourite() {
        if (is_null($this->getId()))
            return false;
        if ($this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_Favourites')->count() == 1) {
            return true;
        }
        return false;
    }

    /**
     * get ordered restaurant
     * @deprecated use getService instead
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRestaurant() {
        if (!is_null($this->getId()))
            return $this->getCurrent()->findParentRow('Yourdelivery_Model_DbTable_Restaurant');
        return null;
    }

    /**
     * get sum of the $field value between $starttime and $endtime
     * @return int
     */
    public function getSumOfOrders($field, $starttime, $endtime) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        try {
            $sql = sprintf('select sum(%s) as sum from orders where time between from_unixtime(%s) and from_unixtime(%s)', $field, $starttime, $endtime);
            $result = $db->fetchRow($sql);
        } catch (Zend_Db_Statement_Exception $e) {
            $db->setFetchMode(Zend_Db::FETCH_ASSOC);
            return 0;
        }

        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        return ($result->sum / 100);
    }

    /**
     * get ordered service
     * @author mlaug
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getService() {
        if (is_null($this->getId())) {
            return null;
        }
        return $this->getCurrent()->findParentRow('Yourdelivery_Model_DbTable_Restaurant');
    }

    /**
     * check if an order is of type group
     * @author mlaug
     * @return boolean
     */
    public function isGroupOrder() {
        if (is_null($this->getId())) {
            return false;
        }
        $row = $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_Group');
        if ($row->count() > 0) {
            return true;
        }
        return false;
    }

    /**
     * check if an order is a company group order
     * this is kind of deprecated, because each company order is special group order
     * @author mlaug
     * @return boolean
     */
    public function isCompanyGroupOrder() {
        if (is_null($this->getId())) {
            return false;
        }
        $row = $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_CompanyGroup');
        if ($row->count() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get all group members append to order
     * @author mlaug
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getGroupMembers() {
        if (is_null($this->getId())) {
            return new SplObjectStorage();
        }

        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_GroupNn');
    }

    /**
     * get all group data
     * @author mlaug
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getGroupData() {
        if (is_null($this->getId())) {
            return null;
        }
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_Group');
    }

    /**
     * get project code of this order
     * might be more than one
     * @author mlaug
     * @return array
     */
    public function getProject() {
        if (is_null($this->getId())) {
            return null;
        }

        $sql = sprintf("select projectId,projectAddition as addition from order_company_group where orderId=%d", $this->getId());

        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * get project code of this order
     * might be more than one
     * @author mlaug
     * @return array
     */
    public function getCostcenter() {
        if (is_null($this->getId())) {
            return null;
        }

        $sql = sprintf("select costcenterId from order_company_group where orderId=%d", $this->getId());

        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * get all company group members
     * @author mlaug
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getCompanyGroupMembers() {
        if (is_null($this->getId())) {
            return array();
        }
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_CompanyGroup');
    }

    /**
     * get group row of given customer
     * @author mlaug
     * @param int $customerId
     * @return Zend_Db_Table_Row_Abstract;
     */
    public function getGroupRow($customerId) {
        foreach ($this->getGroupMembers() as $row) {
            if ($row->customerId == $customerId) {
                return $row;
            }
        }
        return false;
    }

    /**
     * end this group order, mark all members as applied
     * @author mlaug
     * @return boolean
     */
    public function endGroupOrder() {
        if (is_null($this->getId())) {
            return false;
        }

        foreach ($this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_GroupNN') as $row) {
            if ($row->status == 0) {
                $row->status = -1;
                $row->save();
            }
        }
        return true;
    }

    /**
     * mark this order as billed
     * @author mlaug
     * @param int $billId
     * @param string $mode
     * @return boolean
     */
    public function billMe($billId, $mode) {
        if (is_null($this->getId())) {
            return false;
        }

        $current = $this->getCurrent();

        if (!is_object($current)) {
            return false;
        }

        if ($mode == "rest") {
            $current->billRest = $billId;
        }

        if ($mode == "company") {
            $current->billCompany = $billId;
        }

        if ($mode == "courier") {
            $current->billCourier = $billId;
        }

        if ($mode == "order") {
            $current->billOrder = $billId;
        }

        try {
            $current->save();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Assign user to this order
     * this is only used once an unregistered user, wants to create
     * an account based on his given informations
     * @author vpriem
     * @since 06.05.2011
     * @param int $customerId
     * @return int
     */
    public function updateCustomer($customerId) {
        $res = $this->getAdapter()->query(
                "UPDATE `orders`
            SET `customerId` = ?, `registeredAfterSale` = 1
            WHERE `id` = ?", array($customerId, $this->getId()));

        return $res->rowCount();
    }

    /**
     * set id and current row to null, so we may save this order again
     * @author mlaug
     */
    public function resetId() {
        $this->_id = null;
        $this->_current = null;
    }

    /**
     * Rate this order
     * if rating does not exists create row otherwise update
     * @author mlaug, felix
     * @since 18.10.2010 (vpriem)
     * @param int $customerId
     * @param int $delivery
     * @param int $quality
     * @param string $comment
     * @return boolean
     */
    public function rate($customerId = null, $delivery = null, $quality = null, $comment = null, $title = null, $advise = null, $author = null) {
        if ($this->getId() === null) {
            return false;
        }

        // get rating from this order if exists
        $rating = $this->getRating();
        if ($rating->count() > 0) {
            // rating already exists
            $r = $rating->current();

            if ($quality !== null) {
                $r->quality = $quality;
            }
            if ($delivery !== null) {
                $r->delivery = $delivery;
            }
            if ($comment !== null) {
                $r->comment = $comment;
            }
            if ($title !== null) {
                $r->title = $title;
            }
            if ($advise !== null) {
                $r->advise = $advise;
            }
            if ($author !== null) {
                $r->author = $author;
            }
            // allways set status to offline, when rating is updated
            $r->status = 0;
            $r->save();
        }
        // create rating row
        else {
            /*
             * if some comment was written, set status to offline,
             * for this rating to be visible it must be activated in admin backend
             */
            $status = intval(strlen($comment) == 0);
            $table = new Yourdelivery_Model_DbTable_Restaurant_Ratings();
            $table->createRow(array(
                'quality' => $quality,
                'delivery' => $delivery,
                'advise' => $advise,
                'title' => $title,
                'comment' => $comment,
                'customerId' => $customerId,
                'restaurantId' => $this->getCurrent()->restaurantId,
                'orderId' => $this->getId(),
                'author' => $author,
                'status' => $status
            ))->save();
        }
        return true;
    }

    /**
     * check if generated number is unique in database
     * @return boolean
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.07.2012
     */
    public function checkUniqueNr($nr) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()->from('orders', 'id')->where('nr = ?', $nr);
        return (boolean) !$db->fetchOne($select);
    }

    /**
     * get the list of all distinct fields
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctOrders($type) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        //$sql = sprintf('select id from orders group by id order by id');
        $sql = sprintf('select distinct(id) from orders WHERE (SELECT status FROM order_status WHERE orderId = orders.id ORDER BY created DESC LIMIT 1)=' . $type . ' order by id');
        return $db->fetchAll($sql);
    }

    /**
     * get the list of all distinct customers who ever ordered something
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctCustomers() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf('select distinct(customerId) as id, name, prename from orders join customers on orders.customerId=customers.id order by name');
        return $db->fetchAll($sql);
    }

    /**
     * get the list of all distinct restaurants, where someone ever ordered something
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctRestaurants($type) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf('select distinct(restaurantId) as id, name from orders left outer join restaurants on orders.restaurantId=restaurants.id WHERE (SELECT status FROM order_status WHERE orderId = orders.id ORDER BY created DESC LIMIT 1)=' . $type . ' order by name');
        return $db->fetchAll($sql);
    }

    /**
     * store customer in database redundant
     * @author mlaug
     * @return boolean
     */
    public function storeCustomer($customer) {
        if (is_null($this->getId())) {
            return false;
        }

        $table = new Yourdelivery_Model_DbTable_Order_Customer();
        $cRow = $table->createRow();
        $cRow->orderId = $this->getId();
        $cRow->prename = $customer->getPrename();
        $cRow->name = $customer->getName();
        $cRow->email = $customer->getEmail();
        $cRow->ktoNr = $customer->getKtoNr();
        $cRow->ktoBlz = $customer->getKtoBlz();
        $cRow->save();

        return true;
    }

    /**
     * store location of order redundant, so we do not need to think about user
     * altering his location over time
     * @author mlaug
     * @return boolean
     */
    public function storeLocation($location) {
        if ($this->getId() === null) {
            return false;
        }

        $dbTable = new Yourdelivery_Model_DbTable_Order_Location();
        $dbTable->createRow(array(
            'orderId' => $this->getId(),
            'street' => $location->getStreet(),
            'hausnr' => $location->getHausnr(),
            'comment' => $location->getComment(),
            'etage' => $location->getEtage(),
            'companyName' => $location->getCompanyName(),
            'tel' => $location->getTel(),
            'cityId' => $location->getCityId(),
            'plz' => $location->getPlz(),
        ))->save();
        return true;
    }

    /**
     *
     * @param <type> $location
     * @return boolean
     */
    public function updateLocation($location) {
        if (is_null($this->getId())) {
            return false;
        }

        $table = new Yourdelivery_Model_DbTable_Order_Location();
        $lRow = $table->update(array(
            'street' => $location->getStreet(),
            'hausnr' => $location->getHausnr(),
            'comment' => $location->getComment(),
            'etage' => $location->getEtage(),
            'companyName' => $location->getCompanyName(),
            'tel' => $location->getTel(),
            'plz' => $location->getPlz()
                ), 'orderId = ' . $this->getId());
        return true;
    }

    /**
     * store the entire bucket redundant, so we do not to think
     * about meals being deleted over time
     * @author mlaug
     * @since 10.09.2010
     * @return boolean
     */
    public function storeBucket($bucket) {
        if (is_null($this->getId())) {
            return false;
        }

        $bucketT = new Yourdelivery_Model_DbTable_Order_BucketMeals();
        $optionsT = new Yourdelivery_Model_DbTable_Order_BucketMeals_Options();
        $mealoptionsT = new Yourdelivery_Model_DbTable_Order_BucketMeals_Mealoptions();
        $extrasT = new Yourdelivery_Model_DbTable_Order_BucketMeals_Extras();
        //loop through the bucket

        if (!is_array($bucket['bucket'])) {
            return false;
        }

        $total = 0;
        foreach ($bucket['bucket'] as $custId => $card) {

            foreach ($card as $meal) {
                try {
                    $total += $meal['count'] * $meal['cost'];
                    /**
                     * @see Yourdelivery_Model_Meals
                     */
                    $mealObj = $meal['meal'];

                    $bRow = $bucketT->createRow();
                    $bRow->orderId = $this->getId();
                    //do not use anonymous
                    if ($custId > 10000000) {
                        $custId = null;
                    }
                    $bRow->customerId = $custId;
                    $bRow->mealId = $mealObj->getId();
                    $bRow->sizeId = $mealObj->getCurrentSize();

                    try {
                        $bRow->pfand = $mealObj->getPfand();
                    } catch (Exception $e) {
                        $this->logger->crit("Store bucket: missing sizes: " . $mealObj->getCurrentSize() . "//" . $mealObj->getId());
                        $bRow->pfand = 0;
                    }

                    $bRow->name = $mealObj->getName();
                    $bRow->tax = $mealObj->getMwst();
                    $bRow->count = $meal['count'];
                    $bRow->cost = $meal['cost'];
                    $bRow->special = $mealObj->getSpecial();
                    $currentId = $bRow->save();

                    foreach ($mealObj->getCurrentExtras() as $extra) {
                        $eRow = $extrasT->createRow();
                        $eRow->bucketItemId = $currentId;
                        $eRow->extraId = $extra->getId();
                        $eRow->cost = $extra->getCost(false);
                        $eRow->count = $extra->getCount();
                        $eRow->tax = $extra->getMwst();
                        $eRow->name = $extra->getName();
                        $eRow->save();
                    }

                    foreach ($mealObj->getCurrentOptions() as $option) {
                        if ($option instanceof Yourdelivery_Model_Meals) {
                            $oRow = $mealoptionsT->createRow();
                            $oRow->mealId = $option->getId();
                        } else {
                            $oRow = $optionsT->createRow();
                            $oRow->optionId = $option->getId();
                        }
                        $oRow->bucketItemId = $currentId;
                        $oRow->cost = $option->getCost();
                        $oRow->count = 1;
                        $oRow->tax = $option->getMwst();
                        $oRow->name = $option->getName();
                        $oRow->save();
                    }
                    $currentId = null;
                } catch (Exception $e) {
                    $error = "Fehler bei storeBucket:" . $e->getMessage() . "######" . print_r($meal, true);
                    Yourdelivery_Sender_Email::error($error);
                    continue;
                }
            }
        }
        return true;
    }

    /**
     * get company order row
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getMeals() {
        if (is_null($this->getId())) {
            return false;
        }

        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_BucketMeals');
    }

    /**
     * get options of meal
     * @param int $bucketItemId
     * @return array
     */
    public function getMealOptions($bucketItemId = null) {
        if (is_null($this->getId()) || is_null($bucketItemId)) {
            return null;
        }

        $table = new Yourdelivery_Model_DbTable_Order_BucketMeals_Options();
        $result = $table->fetchAll('bucketItemId = ' . $bucketItemId);
        return $result;
    }

    /**
     * get mealoptions of meal (half pizza)
     * @param int $bucketItemId
     * @return array
     */
    public function getMealMealoptions($bucketItemId = null) {
        if (is_null($this->getId()) || is_null($bucketItemId)) {
            return null;
        }

        $table = new Yourdelivery_Model_DbTable_Order_BucketMeals_Mealoptions();
        $result = $table->fetchAll('bucketItemId = ' . $bucketItemId);
        return $result;
    }

    /**
     * get extras of meal
     * @param int $bucketItemId
     * @return array
     */
    public function getMealExtras($bucketItemId = null) {
        if (is_null($this->getId()) || is_null($bucketItemId)) {
            return null;
        }

        $table = new Yourdelivery_Model_DbTable_Order_BucketMeals_Extras();
        $result = $table->fetchAll('bucketItemId = ' . $bucketItemId);
        return $result;
    }

    /**
     * get the list of all distinct fields
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getLocation() {

        if (is_null($this->getId())) {
            return false;
        }

        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_Location')->current();
    }

    /**
     * get not registered customer
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getCustomer() {
        if (is_null($this->getId())) {
            return false;
        }

        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_Customer')->current();
    }

    /**
     * get company order row
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getCompanyGroupOrderRow($id) {
        if (is_null($this->getId())) {
            return false;
        }

        $rows = $this->getCurrent()
                ->findDependentRowset('Yourdelivery_Model_DbTable_Order_CompanyGroup');

        if ($rows->count() > 0) {
            foreach ($rows as $row) {
                if ($row->customerId == $id) {
                    return $row;
                }
            }
        }

        return null;
    }

    /**
     * get pfand sold by this order
     * @return int
     */
    public function getSoldPfand() {
        if (is_null($this->getId())) {
            return 0;
        }
        $sql = sprintf('select sum(count*pfand) from orders_bucket_meals where orderId=%d;', $this->getId());
        return intval($this->getAdapter()->fetchOne($sql));
    }

    /**
     * get repeating order for private customer
     * it will group based on mealId and count total of repeating order
     * @return string
     */
    public static function getRepeatOrderCountRegistered() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        return $db->query(" SELECT o.customerId, obm.mealId, count(obm.id) as SUM
                            FROM orders_bucket_meals obm, orders o
                            WHERE obm.orderId = o.id
                            AND o.payment != 'bill'
                            AND o.customerId is not null
                            AND o.customerId != '0'
                            AND obm.customerId != '0'
                            AND obm.customerId is not null
                            GROUP BY  obm.mealId,o.customerId
                            ORDER BY COUNT( obm.id ) DESC ")
                        ->fetchAll();
    }

    public static function getRepeatOrderCountUnregistered() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        return $db->query(" SELECT ocn.email,obm.mealId  , count( ocn.email ) as SUM
                            FROM orders_customer ocn, orders_bucket_meals obm
                            WHERE ocn.orderId = obm.orderId
                            AND (obm.customerId IS Null
                            OR obm.customerId = '0')
                            GROUP BY obm.mealId, ocn.email
                            ORDER BY count( ocn.email ) DESC")
                        ->fetchAll();
    }

    /**
     * get distinct cities where orders have even been made
     * @author alex
     * @var array
     * @since 22.10.2010
     * @todo relocated it in Helper, when the helper class is ready
     */
    public static function getDistinctCities() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        try {
            $sql = "select distinct(city.city) as ort from orders o join orders_location ol on o.id=ol.orderId join city on ol.cityId=city.id where o.state>0 order by city.city";
            $result = $db->query($sql)->fetchAll();
        } catch (Zend_Db_Statement_Exception $e) {
            return 0;
        }
        return $result;
    }

    /**
     * get all orders which have a status lower 0. Those need to be processed.
     * Premium orders have to be in that list as well
     * @author mlaug
     * @modified daniel
     * @since 21.01.2011
     * @return array
     */
    public static function getOpenOrdersByPrio() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        try {
            // prio is calculated as elapsed minutes multiplied with factor,
            // which depends on the type and state of the order (premium, fax error, cash payment and so)
            // add +1 to the timediff that orders that are just been placed have a time diff of at least 1

            $sql = "SELECT o.id as orderId, COALESCE(o.supporter, 0) as supporter, o.state,o.kind, o.mode, o.payment,  r.notifyPayed, if(r.franchiseTypeId=3, 1, 0) premium, (TIMESTAMPDIFF(MINUTE,`time`,NOW())+1) as timediff, rnt.allwaysCall, rnt.created as notepad_created, ";
            $sql .= "GREATEST(";
            $sql .= "IF (r.franchiseTypeId=3 AND o.mode='rest' AND o.state IN (-4,-3,-1,0,1), 8, 0), "; //premium
            $sql .= "IF (o.state IN (-3,-1) AND o.kind='comp', 6, 0),";  //company
            $sql .= "IF (o.state IN (-4,-3,-1) AND o.kind='priv' AND o.mode='rest' AND o.payment!='bar', 4, 0),"; //private online
            $sql .= "IF (o.state IN (-4,-3,-1) AND o.kind='priv' AND o.mode='rest' AND o.payment='bar', 3, 0),"; //privat bar
            $sql .= "IF (o.state IN (-4,-3,-1,0) AND o.mode!='rest',1,0),";  //not restaurant
            $sql .= "IF (o.payment!='bar' AND r.notifyPayed>0 AND o.mode='rest' AND o.state IN (-1,-3,-4,0,1), 3, 0),";  //restaurant notify payed
            $sql .= "IF (o.payment!='bar' AND r.notifyPayed>0 AND o.mode!='rest' AND o.state IN (-1,-3,-4,0), 3, 0),";  // not resteaurant notify payed
            $sql .= "IF (rnt.allwaysCall=1 AND (DATE_ADD(rnt.created,INTERVAL 1 DAY) > NOW()) AND o.state IN (-1,-3,-4,0,1) , 3, 0),";   //allwaysCall for today
            $sql .= "IF (o.state=-1, 5, 0),"; //errors
            $sql .= "IF (o.state=-3, 4, 0),";  //fakes
            $sql .= "IF (o.state=-15, 1, 0),"; //new state for fax no train
            $sql .= "IF (o.state=-22, 6, 0),"; //rejected from printer
            $sql .= "IF (r.notify='phone', 3, 0),"; //order by phone
            $sql .= "IF (TIMESTAMPDIFF(MINUTE,`time`,NOW())>10 AND o.state=0,3,0)"; //not affirmed > 10 min
            $sql .= ") * (TIMESTAMPDIFF(MINUTE,`time`,NOW())+1) as prio
                        FROM orders o
                        INNER JOIN restaurants r ON r.id=o.restaurantId
                        LEFT JOIN restaurant_notepad_ticket rnt ON rnt.restaurantId=o.restaurantId
                        WHERE o.time > DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                        HAVING prio > 0
                        ORDER BY prio DESC";
            return $db->query($sql)->fetchAll();
        } catch (Zend_Db_Statement_Exception $e) {

            echo $e->getMessage();
            return array();
        }
    }

    public static function getOpenOrderStats() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        try {

            //Muss vielleicht in View ausgelagert werden
            $sql = "SELECT SUM(IF (r.franchiseTypeId=3 AND o.mode='rest' AND o.state IN (-4,-3,-1,0,1), 1, 0)) as premium,
                                     SUM(IF (o.state=-3, 1, 0)) as fraud,
                                     SUM(IF (o.state=-1, 1, 0)) as error,
                                     SUM(IF (TIMESTAMPDIFF(MINUTE,`time`,NOW())>10 AND o.state=0,1,0)) as not_affirmed,
                                     SUM(IF (o.payment!='bar' AND r.notifyPayed>0 AND o.mode='rest' AND o.state IN (-1,-3,-4,0,1), 1, 0)) as notifyPayed
                        FROM orders o
                        INNER JOIN restaurants r ON r.id=o.restaurantId
                        WHERE DATE_ADD(o.time,INTERVAL 1 DAY) > NOW()";



            return $db->query($sql)->fetchAll();
        } catch (Zend_Db_Statement_Exception $e) {
            return array();
        }
    }

    /**
     * get unrated orders from <hours ago> till now
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @return array
     */
    static function allUnrated($hours = 2, $email = null, $asArray = false) {
        $db = Zend_Registry::get('dbAdapterReadOnly');


        $select = $db->select()
                ->from(array('o' => 'orders'), array('o.id', 'o.time', 'o.restaurantId'))
                ->join(array('oc' => 'orders_customer'), 'oc.orderId = o.id', array())
                ->joinLeft(array('rr' => 'restaurant_ratings'), "rr.orderId = o.id", array())
                ->where('kind= "priv"')
                ->where('rr.id IS NULL')
                ->where('o.state > 0');

        if ($hours != 'all') {
            $select->where('o.deliverTime BETWEEN SUBTIME(NOW(), ?) AND ADDTIME(SUBTIME(NOW(), ?),"01:00:00")', $hours . ':00:00');
        } else {
            $select->where('o.deliverTime > SUBDATE(NOW(), INTERVAL 30 DAY)');
        }

        if ($email !== null) {
            $select->where('oc.email =  ? ', $email);
        }
        
        $result = $db->fetchAll($select);



        if ($asArray) {
            return $result;
        }

        $orders = array();
        foreach ($result as $o) {
            try {
                $orders[] = new Yourdelivery_Model_Order((integer) $o['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
        }
        return $orders;
    }

    /**
     * Search orders for backend
     * @author Alex Vait <vait@lieferando.de>
     * @since 13.12.2011
     * @see YD-709
     */
    static function searchOrdersPerIdNr($whereString = null) {
        if (is_null($whereString)) {
            return null;
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("o" => "orders"))
                ->joinLeft(array('r' => 'rabatt_codes'), "r.id = o.rabattCodeId", array('code'))
                ->joinLeft(array('ra' => 'rabatt'), "r.rabattId = ra.id", array('rabattId' => 'ra.id', 'rabattName' => 'ra.name'))
                ->joinLeft(array('oc' => 'orders_customer'), 'o.id = oc.orderId', array())
                ->joinLeft(array('efo' => 'data_view_email_first_order_time_accepted_order'), "oc.email = efo.email", array('orderTime' => 'efo.o_time'))
                ->where($whereString)
                ->order("o.id");

        return $db->fetchAll($query);
    }

}
