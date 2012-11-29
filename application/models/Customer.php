<?php

/**
 * Class of an existing user with persistent data in database
 * but no relation to any company
 * @package customer
 */
class Yourdelivery_Model_Customer extends Yourdelivery_Model_Customer_Abstract {

    /**
     * related base table
     * @var Yourdelivery_Model_DbTable_Customer
     */
    protected $_table = null;

    /**
     * @var Yourdelivery_Model_Budget
     */
    protected $_budget = null;

    /**
     * associated company
     * @var Yourdelivery_Model_Company
     */
    protected $_company = null;

    /**
     * all saved locations of customer
     * @var SplObjectStorage
     */
    protected $_locations = null;

    /**
     * granted rights to protected areas
     * @var array
     */
    protected $_rights = array();

    /**
     * stores all favourite meals
     * @var SplObjectStorage
     */
    protected $_favMeals = null;

    /**
     * store is Employee
     * @var boolean
     */
    protected $_isEmployee = null;

    /**
     * store last Order
     * @var array
     */
    protected $_lastOrder = null;

    /**
     * Adds a new customer to the database,
     * based on data array $data
     * @author Alex Vait <vait@lieferando.de>
     * @param array $args
     * @return int
     */
    public static function add($data) {
        $data['email'] = Default_Helpers_Normalize::email($data['email']);
        
        $alreadyRegistred = Yourdelivery_Model_DbTable_Customer::findByEmail($data['email']);
        if (is_array($alreadyRegistred) && ($alreadyRegistred['deleted'] == 0)) {
            return $alreadyRegistred['id'];
        }

        $cust = new Yourdelivery_Model_Customer();

        // clean array and add some extra information
        $data['password'] = md5($data['password']);
        $config = Zend_Registry::get('configuration');
        if ($config->domain->base != 'janamesa.com.br') {
            $data['tel'] = Default_Helpers_Normalize::telephone($data['tel']);
        }

        // check birthday
        $birthday = Default_Helpers_Date::isDate($data['birthday']);
        if ($birthday !== false) {
            $data['birthday'] = date('Y-m-d', mktime(0, 0, 0, $birthday['m'], $birthday['d'], $birthday['y']));
        } else {
            unset($data['birthday']);
        }

        try {
            $cust->setData($data);
            $id = $cust->save();
            $logger = Zend_Registry::get('logger');

            if (is_array($alreadyRegistred) && ($alreadyRegistred['deleted'] != 0)) {
                $logger->info(sprintf('customer #%s was already registered once, not adding fidelity points for registering', $id));
            } else {
                $cust->addFidelityPoint('register', $id);
                $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
                $logger->info(sprintf('customer #%s successfully registered, added %s fidelity points', $id, $fidelityConfig->fidelity->points->register));
            }
            return $id;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * get a customer by given id, email or secure hash
     * 
     * @author mlaug
     * @param int $id
     * @param string $email
     * @param string $secure
     * @return Yourdelivery_Model_Customer
     */
    function __construct($id = null, $email = null, $secure = null) {

        //nothing is set so we return null
        if (is_null($id) && is_null($email) && is_null($secure)) {
            return $this;
        }

        //if username is set we try to gather it
        if (!is_null($secure)) {
            //get from secure key, salted!
            $result = Yourdelivery_Model_DbTable_Customer::findBySecure($secure);
            if (is_array($result) && $result['id']) {
                $this->load($result['id']);
            } elseif (!is_null($id)) {
                $this->load($id);
            } else {
                throw new Yourdelivery_Exception_Database_Inconsistency('Customer could not be found by secure');
            }
        } elseif (!is_null($email)) {
            $result = Yourdelivery_Model_DbTable_Customer::findByEmail($email, false);
            if (is_array($result) && $result['id']) {
                $this->load($result['id']);
            } elseif (!is_null($id)) {
                //if this fails, but a id is given we have another try
                $this->load($id);
            } else {
                throw new Yourdelivery_Exception_Database_Inconsistency('Customer could not be found by email');
            }
        } else {
            $this->load($id);
        }

        //set/(have) sex
        switch ($this->getSex()) {
            default: $anrede = __('Herr/Frau');
                break;
            case 'm': $anrede = __('Herr');
                break;
            case 'w': $anrede = __('Frau');
                break;
        }
        $this->setAnrede($anrede);
        return $this;
    }

    /**
     * get salted has to identify user;
     * @author mlaug
     * @since 13.10.2010
     * @return string
     */
    public function getSalt() {
        return md5($this->getId() . SALT);
    }

    /**
     * Add address
     * @author vpriem
     * @modified Daniel
     * @since 16.03.2011
     * @param array $data
     * @return boolean
     */
    public function addAddress(array $data) {
        if (!isset($data['comment'])) {
            $data['comment'] = '';
        }
        $data['customerId'] = $this->getId();

        $location = new Yourdelivery_Model_Location();
        $location->setData($data);

        //set Primary for First
        if (!$this->getLocations()->count()) {
            $location->setPrimary(true);
        }
        $cookie = Yourdelivery_Cookie::factory('yd-customer');
        $cookie->set('hasLocations', 1);
        $cookie->save();
        return $location->save();
    }

    /**
     * Edit address
     * @author vpriem
     * @since 16.03.2011
     * @param array $data
     * @param int $id
     * @return boolean
     */
    public function editAddress(array $data, $id) {
        try {
            $location = new Yourdelivery_Model_Location($id);
            if ($location->getCustomerId() == $this->getId()) {
                $location->setData($data);
                return $location->save();
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

        }
        return false;
    }

    /**
     * Get the last n orders
     * @author vpriem
     * @since 06.04.2011
     * @param int $count
     * @param string $mode
     * @param string $kind
     * @return array|Yourdelivery_Model_Order
     */
    public function getLastOrder($count = 1, $mode = 'rest', $kind = 'priv') {
        $storeKey = $count . "_" . $mode . '_' . $kind;

        if (!empty($this->_lastOrder[$storeKey])) {
            return $this->_lastOrder[$storeKey];
        }

        if (!in_array($mode, array('rest', 'cater', 'great'))) {
            $mode = 'rest';
        }
        if (!in_array($kind, array('priv', 'comp'))) {
            $kind = 'priv';
        }

        $orderIDs = $this->getTable()->getLastOrder($count, $mode, $kind);
        if ($orderIDs === null) {
            return array();
        }

        $orders = array();
        foreach ($orderIDs as $value) {
            try {
                $order = new Yourdelivery_Model_Order($value['id'], false);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            if ($count == 1) {
                $this->_lastOrder[$storeKey] = $order;
                return $order;
            }
            $orders[] = $order;
        }
        $this->_lastOrder[$storeKey] = $orders;
        return $orders;
    }

    /**
     * get the favourite orders of this customer
     * @author mlaug
     * @since 07.11.2011
     * @return array
     */
    public function getFavourites() {
        $ids = $this->getTable()->getFavourites();
        if (is_null($ids)) {
            return array();
        }
        $orders = array();
        foreach ($ids as $orderID => $values) {
            try {
                $obj = new Yourdelivery_Model_Order($values->orderId, false);
                $obj->getService()->setCurrentCityId($obj->getLocation()->getCity()->getId());
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            $orders[] = $obj;
        }
        return $orders;
    }

    /**
     * get the list of all distinct restaurants, where favourite orders have been made
     * @author alex
     * @since 18.11.2011
     * @return array
     */
    public function getFavouriteRestaurants() {
        $restaurants = Yourdelivery_Model_DbTable_Favourites::getAllRestaurantIds($this->getId(), false);

        return $restaurants;
    }

    /**
     *
     * @author mlaug
     * @param mixed Yourdelivery_Model_Company|Yourdelivery_Model_Servicetype_Abstract $obj
     * @return boolean
     */
    public function isAdmin($obj) {
        if (is_object($obj)) {
            if ($obj instanceof Yourdelivery_Model_Company) {
                $rights = $this->getRights('c');
                foreach ($rights as $r) {
                    if ($r == $obj->getId()) {
                        return true;
                    }
                }
            }

            if ($obj instanceof Yourdelivery_Model_Servicetype_Abstract) {
                $rights = $this->getRights('r');
                foreach ($rights as $r) {
                    if ($r == $obj->getId()) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * make this customer to be the admin of either a restaurant or a company
     * @author mlaug
     * @param mixed Yourdelivery_Model_Company|Yourdelivery_Model_Servicetype_Abstract $obj
     * @return boolean
     */
    public function makeAdmin($company) {
        if (is_object($company)) {
            if ($company instanceof Yourdelivery_Model_Company) {
                if ($company->getStatus() == 0) {
                    $this->logger->warn(sprintf('Trying to make #%s %s admin in company #%s %s which is deactivated!', $this->getId(), $this->getFullname(), $company->getId(), $company->getName()));
                    return false;
                }

                if ($this->isEmployee() && $company->getId() == $this->getCompany()->getId()) {
                    $check = $this->getTable()->addRight('c', $company->getId());
                    if ($check) {
                        $this->logger->info(sprintf('Successfully made #%s %s to admin in company #%s %s which is deactivated!', $this->getId(), $this->getFullname(), $company->getId(), $company->getName()));
                        return true;
                    }
                    $this->logger->warn(sprintf('Trying to make #%s %s admin, but addRight failed for company #%s %s', $this->getId(), $this->getFullname(), $company->getId(), $company->getName()));
                    return false;
                }
                $this->logger->warn(sprintf('Trying to make #%s %s admin, who is not employed in company #%s %s', $this->getId(), $this->getFullname(), $company->getId(), $company->getName()));
                return false;
            }

            if ($company instanceof Yourdelivery_Model_Servicetype_Abstract) {
                return $this->getTable()->addRight('r', $company->getId());
            }
        }
        return false;
    }

    /**
     * make customer admin of a certian object
     * @author mlaug
     * @param mixed Yourdelivery_Model_Company|Yourdelivery_Model_ServicetypeAbstract $obj
     * @return boolean
     */
    public function removeAdmin($obj) {
        if (is_object($obj)) {
            //remove company admin
            if ($obj instanceof Yourdelivery_Model_Company) {
                //extra checks here?
                return (boolean) $this->getTable()->delRight('c', $obj->getId());
            }
            //remove restaurant admin
            if ($obj instanceof Default_Model_Servicetype) {
                //extra checks here?
                return (boolean) $this->getTable()->delRight('r', $obj->getId());
            }
        }
        return false;
    }

    /**
     * get priveliges of user
     * @author olli
     * @deprecated should be replaced by isAdmin
     * @param string $what
     * @return array
     */
    public function getRights($what = null) {
        $result = array('r' => array(), 'c' => array());
        $rights = $this->getTable()->getRights();
        foreach ($rights as $v) {
            switch ($v->kind) {
                case 'r': {
                        if ($v->status == '1') {
                            $result['r'][] = $v->refId;
                        }
                        break;
                    }
                case 'c': {
                        if ($v->status == '1') {
                            $result['c'][] = $v->refId;
                        }
                        break;
                    }
            }
        }
        if (!is_null($what)) {
            return $result[$what];
        }
        return $result;
    }

    /**
     * add a right based on kind r/c and refId for the user
     * @author olli
     * @deprecated
     * @param string $what
     * @param int $id
     * @return boolean
     */
    public function addRight($what, $id) {
        if (in_array($id, $this->getRights($what))) {
            return false;
        }
        return $this->getTable()->addRight($what, $id);
    }

    /**
     * delete a right based on kind r/c and refId for the user
     * @author olli
     * @param string $what
     * @param int $id
     * @return boolean
     */
    public function delRight($what, $id) {
        if (!in_array($id, $this->getRights($what))) {
            return false;
        }
        return $this->getTable()->delRight($what, $id);
    }

    /**
     * check for a primary address
     * @author mlaug
     * @since 11.11.2011
     * @return boolean
     */
    public function hasPrimaryLocation() {
        return $this->getTable()->hasPrimaryLocation($this->getId());
    }

    /**
     * get saved locations from customer
     * @author mlaug
     * @param int $plz
     * @return SplObjectStorage
     */
    public function getLocations($plz = null, $primary = false) {
        $this->_locations = new SplObjectStorage();

        $locations = $this->getTable()->getLocations();
        foreach ($locations as $loc) {
            if ($loc->deleted == 0) {
                $location = new Yourdelivery_Model_Location($loc->id);
                if (!is_object($location->getOrt())) {
                    $mesg = sprintf('could not find city for location %s', $loc->id);
                    $this->logger->crit($mesg);
                    $error = array();
                    $traces = debug_backtrace();
                    foreach ($traces as $i => $trace) {
                        $error[] = " " . $i . ". " .
                                (isset($trace['class']) ? $trace['class'] . $trace['type'] : "") .
                                (isset($trace['function']) ? $trace['function'] . "() " : "") .
                                (isset($trace['file']) ? $trace['file'] . ":" . $trace['line'] : "");
                    }
                    $error = implode("\n", $error);
                    $mesg .= $error;

                    Yourdelivery_Sender_Email::error($mesg, true);
                    continue;
                }

                //check for plz
                if ($plz && $plz != $location->getPlz()) {
                    continue;
                }

                //check for primary
                if ($primary && $location->isPrimary()) {
                    return $location;
                }
                $this->_locations->attach($location);
            }
        }
        $this->_locations->rewind();

        //if no primary address has been selected, get the first one
        if ($primary && $this->_locations->count() > 0) {
            return $this->_locations->current();
        }
        return $this->_locations;
    }

    /**
     * if the customer is working in a company
     * we will get its object
     * @author mlaug
     * @return Yourdelivery_Model_Company
     */
    public function getCompany() {
        $company = $this->getTable()->getCompany();
        if (is_object($company) && $company->count() == 1) {
            $id = $company->current()->companyId;

            $company = new Yourdelivery_Model_Company($id);
            if ($company->isDeleted()) {
                return null;
            }
            return $company;
        } else {
            return null;
        }
    }

    /**
     * get saved company locations from customer
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getCompanyLocations() {
        if ($this->isEmployee()) {
            $locations = new SplObjectStorage();
            foreach ($this->getTable()->getCompanyLocations($this->getCompany()->getId()) as $loc) {
                try {
                    $locId = (integer) $loc['id'];
                    if ($locId <= 0) {
                        //that is weird
                        continue;
                    }
                    $location = new Yourdelivery_Model_Location($locId);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    continue;
                }
                $locations->attach($location);
            }
            return $locations;
        }
        return new SplObjectStorage();
    }

    /**
     * get all orders from user
     *
     * @param boolean $onlyConfirmed
     * @param boolean $asArray
     *
     * @return SplObjectStorage | array($orders, $countOrders)
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function getOrders($limit = 1000, $offset = 0, $onlyConfirmed = false, $asArray = false) {

        $hash = $this->getCacheTag('getOrders' . 'l' . (integer) $limit . 'o' . (integer) $offset . 'c' . (integer) $onlyConfirmed . 'a' . (integer) $asArray);
        $orders = Default_Helpers_Cache::load($hash);
        if (is_null($orders)) {
            $orders = $this->getTable()->getOrders($onlyConfirmed, $limit, $offset);
            Default_Helpers_Cache::store($hash, $orders);
        }
        
        if ($asArray) {
            return $orders;
        }

        $ret = new splObjectStorage();
        foreach ($orders as $order) {
            $obj = null;
            try {
                $obj = new Yourdelivery_Model_Order($order->id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }

            if ($obj->getState() >= 0) {
                $ret->attach($obj);
            }
        }
        return $ret;
    }

    /**
     * ordinary user should not have a budget
     * @return boolean
     */
    public function getBudget() {
        return false;
    }

    /**
     * check if a user works at a company
     * @return boolean
     */
    public function isEmployee() {
        if (is_null($this->_isEmployee)) {
            $this->_isEmployee = $this->getTable()->isEmployee();
        }

        return $this->_isEmployee;
    }

    /**
     * return an customer_company association if a user works at a company
     * @author alex
     * @since 10.01.2010
     * @return Yourdelivery_Model_Customer_Company
     */
    public function getEmployee() {
        if (!$this->isEmployee()) {
            return null;
        } else {
            $cc = $this->getTable()->getCompany();
            try {
                $customerCompany = new Yourdelivery_Model_Customer_Company($cc->current()->customerId, $cc->current()->companyId);
                return $customerCompany;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return null;
            }
        }
    }

    /**
     * Get related table
     * @author vpriem
     * @since 06.05.2011
     * @return Yourdelivery_Model_DbTable_Customer
     */
    public function getTable() {
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Customer();
        }
        return $this->_table;
    }

    /**
     * this customer object returns always true
     * dispite the anonymous Object because validation has already been done
     * also set all necessary cookies
     * @return boolean
     */
    public function isLoggedIn($set = false) {
        if ($set) {
            $this->login();
        }
        return true;
    }

    /**
     * add to message object current persistent message
     */
    public function setPersistentNotfication() {
        $messages = $this->getTable()->getPersistentMessages();
        foreach ($messages as $message) {
            $type = $message->type;
            $message->read = true;
            $message->save();
            $this->$type($message->message);
        }
    }

    /**
     * create a persistend database driven message
     * @param string $type
     * @param string $message
     * @return boolean
     */
    public function createPersistentMessage($type, $message) {
        if (empty($type) || empty($message)) {
            return false;
        }
        $this->getTable()->createPersistentMessage($type, $message);
        return true;
    }

    /**
     * check if user has access to any restaurant
     * @return mixed
     */
    public function getRestaurantAccess() {
        $rights = $this->getRights();
        if (isset($rights['r'][0])) {
            return $rights['r'][0];
        } else {
            return false;
        }
    }

    /**
     * check if user has access to any company
     * @return mixed
     */
    public function getCompanyAccess() {
        $rights = $this->getRights();
        if (isset($rights['c'][0])) {
            return $rights['c'][0];
        } else {
            return false;
        }
    }

    /**
     * check if a user has access to any given object
     * @todo implement: just for the future
     * @param mixed $obj
     * @deprecated
     * @return boolean
     */
    public function hasAccess($obj) {
        return $this->isAdmin($obj);
    }

    /**
     * reset customers password and return it
     * @return string
     */
    public function resetPassword() {
        $pass = Default_Helper::generateRandomString();
        $this->setPassword(md5($pass));
        $this->save();
        return $pass;
    }

    /**
     * add a discount to
     * @param Yourdelivery_Model_Rabatt_Code $discount
     */
    public function setDiscount($discount) {
        $this->setPermanentDiscount($discount);
        return $this->save();
    }

    /**
     * get discount if any
     * @return Yourdelivery_Model_Rabatt_Code
     */
    public function getDiscount() {
        return $this->getTable()->getDiscount();
    }

    /**
     * remove Discount from customer
     * @return boolean
     */
    public function removeDiscount() {
        $this->getTable()->removeDiscount();
    }

    /**
     * @paran Yourdelivery_Model_Order $order
     * @return boolean
     */
    public function hasRated($order = null) {
        if (is_null($order)) {
            return false;
        }
        return $this->getTable()->hasRated($order, $this);
    }

    /**
     * update one customers data
     * @todo this may be done with save method after all
     * @deprecated
     * @param array $values
     */
    public function update($values) {
        $id = $this->getTable()->update($values, 'id = ' . $this->getId(), true, true);
        $this->_data = array_merge($this->_data, $this->getTable()->getInformation()->toArray());
        return $id;
    }

    /**
     * we delete a customer setting its deleted flag to the id itself
     * this way we can delete as many customers of the same email adress
     * as we want. still only one email adresse may be allowed to be not deleted
     * at one time
     * @author mlaug
     * @since 26.10.2011
     */
    public function delete() {
        if ($this->isEmployee()) {
            // remove relation to the company
            $compId = is_null($this->getCompany()) ? null : $this->getCompany()->getId();
            if (!is_null($compId)) {

                $relationTable = new Yourdelivery_Model_DbTable_Customer_Company();
                if ($relationTable->delete(sprintf('companyId = %d AND customerId = %d', $this->getCompany()->getId(), $this->getId())) > 0) {
                    $this->logger->info(sprintf('successfully deleted customer(#%s %s)-company(#%s)-relation', $this->getId(), $this->getFullname(), $compId));
                }
            } else {
                return false;
            }
        }

        $rightsTable = new Yourdelivery_Model_DbTable_UserRights();
        if ($rightsTable->delete(sprintf('customerId = %d', $this->getId())) > 0) {
            $this->logger->info(sprintf('successfully deleted customer #%s %s from user_rights', $this->getId(), $this->getFullname()));
        }

        //remove from newsletter
        $this->setNewsletter(false);

        //set delete flag
        $this->setDeleted($this->getId());
        $this->deleteFidelityPoints();
        $this->save();
        $this->logger->info(sprintf('successfully deleted customer #%s %s', $this->getId(), $this->getFullname()));
        return true;
    }

    /**
     * Set stauts of all fidelity points to 0
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.05.2012
     */
    public function deleteFidelityPoints() {
        return Yourdelivery_Model_DbTable_Customer::deleteFidelityPoints($this->getEmail());
    }

    /**
     * is the user marked as deleted?
     * @return string
     */
    public function isDeleted() {
        return $this->_data['deleted'] > 0;
    }

    /**
     * crate new customer from given Yourdelivery_Model_Contact object
     * @return Yourdelivery_Model_Customer
     */
    public static function createFromContact($contact) {
        if (!($contact instanceof Yourdelivery_Model_Contact)) {
            return null;
        }

        if (strlen($contact->getEmail()) == 0) {
            return null;
        }

        try {
            $customer = new Yourdelivery_Model_Customer(null, $contact->getEmail());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $customer = null;
        }

        /**
         *  customer doesnt exist in database, so create him
         */
        if (is_null($customer)) {
            $customer = new Yourdelivery_Model_Customer();

            // generate a nice password (char char char char int int)
            $password = '';
            for ($i = 0; $i < 4; $i++) {
                $password .= chr(rand(97, 122));
            }

            $values = $contact->getData();

            $password .= rand(10, 99);
            $values['password'] = $password;

            // add the customer to the database
            $cid = Yourdelivery_Model_Customer::add($values);
            $customer = new Yourdelivery_Model_Customer($cid);

            Default_View_Notification::success(__('Nutzer erfolgreich erstellt. Passwort: ' . $password));
        }
        return $customer;
    }

    /**
     * in model Customer always return false
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.08.2010
     * @return boolean
     */
    public function isRegistered() {
        return true;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 02.09.2010
     * @param int $custId
     * @return array
     */
    public static function findById($custId) {
        if (is_null($custId)) {
            return null;
        }

        $table = new Yourdelivery_Model_DbTable_Customer();
        return $table->findById($custId);
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 20.01.2011
     * @param int $custId
     * @return array
     */
    public static function findByTel($tel) {
        if (is_null($tel)) {
            return null;
        }

        $row = Yourdelivery_Model_DbTable_Customer::findByTel($tel);

        if (intval($row) == 0) {
            return null;
        }

        try {
            $customer = new Yourdelivery_Model_Customer($row['id']);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
        return $customer;
    }

    /**
     * if last login value is 0, return the value of registration date
     * @author alex
     * @since 30.11.2010
     * @return date
     */
    public function getLastlogin() {
        $lastLogin = parent::getLastlogin();

        if (intval($lastLogin) == 0) {
            return $this->getCreated();
        }
        return $lastLogin;
    }

    /**
     * Update ONLY cookies
     * @author vpriem
     * @since 26.08.2011
     */
    public function login() {
        $cookie = Yourdelivery_Cookie::factory('yd-customer');
        $cookie->set('name', rawurlencode($this->getName()));
        $cookie->set('prename', rawurlencode($this->getPrename()));
        $cookie->set('admin', 0);
        $cookie->set('companyId', $this->isEmployee() ? $this->getCompany()->getId() : 0);
        if ($this->isEmployee()) {
            $company = $this->getCompany();
            $cookie->set('company', rawurlencode($company->getName()));
            if ($this->isAdmin($company)) {
                $cookie->set('admin', 1);
            }
        }
        if(count($this->getLocations())) {
            $cookie->set('hasLocations', 1);
        }
        $cookie->save();

        Default_Helpers_Web::setCookie('YD_UID', 'nein man, ich will noch nicht gehen');
    }

    /**
     * get url to redirect when user logs in
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.06.2011
     * @return string
     */
    public function getStartUrl() {
        $start = $this->getStart();

        if (!$this->isEmployee()) {
            return '/order_private/start?mode=' . $start;
        } else {
            return '/order_company/start?mode=' . $start;
        }
    }

    /**
     * TODO
     * Refactor - only for backend
     * @author alex
     * @since 14.07.2011
     */
    public function getEditlink() {
        return sprintf("<a href=\"/administration_user_edit/index/userid/%s\">%s</a>", $this->getId(), $this->getPrename() . " " . $this->getName());
    }

    /**
     * add an image to the customer and maybe use it as a profile
     * @author Matthias Laug <laug@lieferando.de>
     * @since 03.11.2011
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 19.12.2011
     *
     * @param string $image
     * @param boolean $useAsProfile
     *
     * @return integer count of new fidelity points
     */
    public function addImage($image, $useAsProfile = false) {
        $fidelityCount = 0;
        if (file_exists($image)) {
            $storage = new Default_File_Storage();
            $storage->setStorage(APPLICATION_PATH . '/../storage/');
            $storage->setSubFolder('customer/' . $this->getId());
            $filename = time() . '.jpg';
            $storage->store($filename, file_get_contents($image));

            if ($useAsProfile) {
                //save image additionally in amazon s3
                $config = Zend_Registry::get('configuration');
                if (!Default_Helpers_AmazonS3::putObject($config->domain->base, "customers/" . $this->getId() . "/profile.jpg", $image)) {
                    $this->logger->warn(sprintf('failed to upload image "%s" to s3 storage for customer #%d %s', $image, $this->getId(), $this->getFullname()));
                }

                $fidelityCount = $this->addFidelityPoint('accountimage', $filename);
                $this->setProfileImage($filename);
                $this->save();

                if ($this->config->varnish->enabled) {
                    $varnishPurger = new Yourdelivery_Api_Varnish_Purger();
                    $varnishPurger->addUrl($this->getProfileImage());
                    $varnishPurger->executePurge();
                }

            }
        }
        return $fidelityCount;
    }

    /**
     * delete the current selected profile image
     * leave the image itself on the server...
     * @author mlaug
     * @since 18.11.2011
     */
    public function deleteProfileImage() {
        //remove fidelity points
        $this->getFidelity()->cancelTransactionByAction('accountimage');
        $this->setProfileImage(null);
        $this->save();
    }

    /**
     * check if an image is avilable
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.06.2012
     * @return boolean
     */
    public function hasProfileImage(){
        return (boolean) strlen($this->_data['profileImage']) > 0;
    }

    /**
     * get the profile image of a customer
     *
     * @author Matthias Laug <laug@liefereando.de>
     * @since 03.11.2011
     * @return string
     */
    public function getProfileImage() {
        if ( !$this->hasProfileImage() ){
            return Yourdelivery_Model_Customer_Abstract::DEFAULT_IMG;
        }

        return self::createProfileImageUrl($this->getId(), $this->_data['profileImage']);     
    }
    
    /**
     * generate profile image url
     * 
     * @author Matthias Laug <laug@liefereando.de>
     * @since 03.08.2012
     * 
     * @param integer $id
     * @param string $profileImage
     * @return string
     */
    public static function createProfileImageUrl($id, $profileImage = 'default.jpg'){
        $config = Zend_Registry::get('configuration');
        $width = (integer) $config->timthumb->customer->normal->width;
        $height = (integer) $config->timthumb->customer->normal->height;
        $http = isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443 ? 'https://' : 'http://';
        $url = sprintf('%s/%s/customer/%s/%s-%d-%d.jpg', $http . $config->domain->timthumb, $config->domain->base, $id, substr($profileImage, 0,-4), $width, $height);
        return $url;  
    }

    /**
     * caching variable for count rated orders
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 23.12.2011
     *
     * @var array
     */
    protected $_ratedCount = array();

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 06.12.2011
     * @param int $limit
     * @param int $start
     * @return array
     */
    public function getRatedOrders($limit, $start) {
        $hash = $this->getCacheTag('rated_' . $limit . "_" . $start);
        $orders = Default_Helpers_Cache::load($hash);

        if (!is_null($orders)) {
            $this->logger->debug(sprintf('get RATED ORDERS (limit %d - start %d) for customer #%d %s from cache with id: %s - ratedOrderCount: %s', $limit, $start, $this->getId(), $this->getEmail(), $hash, $this->_ratedCount));
            return $orders;
        } else {
            $orders = Yourdelivery_Model_DbTable_Customer::getRatedOrders($this, $limit, $start, 'time DESC');
            $this->logger->debug(sprintf('search RATED ORDERS (limit %d - start %d) for customer #%d %s - ratedOrderCount: %s', $limit, $start, $this->getId(), $this->getEmail(), count($orders)));
            Default_Helpers_Cache::store($hash, $orders);
        }
        return $orders;
    }

    /**
     *  @author Daniel Hahn <hahn@lieferando.de>
     *  @since 06.12.2011
     */
    public function getRatedOrdersCount() {
        $hashCount = $this->getCacheTag("ratedCount");
        $this->_ratedCount = Default_Helpers_Cache::load($hashCount);

        if (!$this->_ratedCount) {
            $this->_ratedCount = count(Yourdelivery_Model_DbTable_Customer::getRatedOrders($this, false, false));
            $this->logger->debug(sprintf('search RATED ORDERS COUNT for customer #%d %s - ratedOrderCount: %s', $this->getId(), $this->getEmail(), $this->_ratedCount));
            Default_Helpers_Cache::store($hashCount, $this->_ratedCount);
        } else {
            $this->logger->debug(sprintf('get RATED ORDERS COUNT for customer #%d %s from cache with id: %s - ratedOrderCount: %s', $this->getId(), $this->getEmail(), $hashCount, $this->_ratedCount));
        }
        return $this->_ratedCount;
    }

    /**
     * caching variable for count unrated orders
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 23.12.2011
     *
     * @var array
     */
    protected $_unratedCount = array();

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 06.12.2011
     * @param int $limit
     * @param int $start
     * @return array
     */
    public function getUnratedOrders($limit, $start) {
        $hash = $this->getCacheTag('unrated_' . $limit . "_" . $start);
        $orders = Default_Helpers_Cache::load($hash);

        if (!is_null($orders)) {
            $this->logger->debug(sprintf('get UNRATED ORDERS (limit %d - start %d) for customer #%d %s from cache with id: %s - unratedOrderCount: %s', $limit, $start, $this->getId(), $this->getEmail(), $hash, $this->_unratedCount));
            return $orders;
        } else {
            $orders = Yourdelivery_Model_DbTable_Customer::getUnRatedOrders($this, $limit, $start, 'time DESC');
            $this->logger->debug(sprintf('search UNRATED ORDERS (limit %d - start %d) for customer #%d %s - unratedOrderCount: %s', $limit, $start, $this->getId(), $this->getEmail(), count($orders)));
            Default_Helpers_Cache::store($hash, $orders);
        }
        return $orders;
    }

    /**
     *  @author Daniel Hahn <hahn@lieferando.de>
     *  @since 06.12.2011
     */
    public function getUnratedOrdersCount() {
        $hashCount = $this->getCacheTag('unratedCount');
        $this->_unratedCount = Default_Helpers_Cache::load($hashCount);

        if (!$this->_unratedCount) {
            $this->_unratedCount = count(Yourdelivery_Model_DbTable_Customer::getUnRatedOrders($this, false, false));
            $this->logger->debug(sprintf('search UNRATED ORDERS COUNT for customer #%d %s - unratedOrderCount: %s', $this->getId(), $this->getEmail(), $this->_unratedCount));
            Default_Helpers_Cache::store($hashCount, $this->_unratedCount);
        } else {
            $this->logger->debug(sprintf('get UNRATED ORDERS COUNT for customer #%d %s from cache with id: %s - unratedOrderCount: %s', $this->getId(), $this->getEmail(), $hashCount, $this->_unratedCount));
        }
        return $this->_unratedCount;
    }

    /**
     *  @todo clear cache for all dynamic limits
     *
     *  @author Daniel Hahn <hahn@lieferando.de>
     *  @since 06.12.2011
     */
    public function clearCache() {
        $start = 0;
        $limit = array(5, 10, 25, 50, 100);
        $actions = array('rated', 'unrated');

        foreach ($actions as $item) {
            foreach ($limit as $entry) {
                $hash = $this->getCacheTag($item . '_' . $entry . "_" . $start);
                if (Default_Helpers_Cache::remove($hash)) {
                    $this->logger->debug(sprintf('CUSTOMER - Cache cleared for limit %d id: %s', $limit, $hash));
                } else {
                    $this->logger->debug(sprintf('CUSTOMER - could not clear cache for limit %d id: %s', $limit, $hash));
                }
            }
        }
        Default_Helpers_Cache::remove($this->getCacheTag("ratedCount"));
        Default_Helpers_Cache::remove($this->getCacheTag("unratedCount"));
        Default_Helpers_Cache::remove($this->getCacheTag("company"));
        Default_Helpers_Cache::remove($this->getCacheTag("company_relation"));
        Default_Helpers_Cache::remove($this->getCacheTag("firstandlastandcountorders"));

        // clear Fidelity Cache
        $fidelity = $this->getFidelity();
        if (is_object($fidelity)) {
            $fidelity->clearCache();
        }

        // clear OrderApi GET cache
        for ($limit = 0; $limit <= 50; $limit++) {
            for ($offset = 0; $offset <= 50; $offset++) {
                Default_Helpers_Cache::remove($this->getCacheTag('getOrders' . 'l' . $limit . 'o' . $offset . 'c' . (integer) true . 'a' . (integer) true));
                Default_Helpers_Cache::remove($this->getCacheTag('getOrders' . 'l' . $limit . 'o' . $offset . 'c' . (integer) true . 'a' . (integer) false));
                Default_Helpers_Cache::remove($this->getCacheTag('getOrders' . 'l' . $limit . 'o' . $offset . 'c' . (integer) false . 'a' . (integer) true));
                Default_Helpers_Cache::remove($this->getCacheTag('getOrders' . 'l' . $limit . 'o' . $offset . 'c' . (integer) false . 'a' . (integer) false));
                
                Default_Helpers_Cache::remove($this->getCacheTag('getOrders' . 'l' . $limit . 'o' . $offset . 'c' . (integer) false . 'a' . (integer) false));
                Default_Helpers_Cache::remove($this->getCacheTag('getOrders' . 'l' . $limit . 'o' . $offset . 'c' . (integer) false . 'a' . (integer) false));
            }
        }
        // clear OrderApi GET cache standard values
        Default_Helpers_Cache::remove($this->getCacheTag('getOrders' . 'l1000o0c' . (integer) true . 'a' . (integer) true));
        Default_Helpers_Cache::remove($this->getCacheTag('getOrders' . 'l1000o0c' . (integer) true . 'a' . (integer) false));
        Default_Helpers_Cache::remove($this->getCacheTag('getOrders' . 'l1000o0c' . (integer) false . 'a' . (integer) true));
        Default_Helpers_Cache::remove($this->getCacheTag('getOrders' . 'l1000o0c' . (integer) false . 'a' . (integer) false));

    }

}
