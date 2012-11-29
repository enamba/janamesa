<?php

/**
 * This is the base of all models
 * @abstract
 * @package core
 * @subpackage model
 * @since 17.08.2010
 * @author mlaug
 */
abstract class Default_Model_Base {

    /**
     * the id of our configurable
     * @var int
     */
    protected $_id = null;

    /**
     * Store access to logging facilities
     * @var Zend_Log
     */
    protected $_logger = null;

    /**
     * store cached values
     * @var Zend_Cache
     */
    protected $_cache = null;

    /*
     * store session
     * @var Zend_Session
     */
    protected $_session = null;

    /**
     * store common data
     * @var array
     */
    protected $_data = array();

    /**
     * store common data
     * @var array
     */
    protected $_unmodifiedData = array();

    /**
     * application configuration
     * @var Zend_Config
     */
    protected $_config = null;

    /**
     * storage object to manage files
     * @var Default_File_Storage
     */
    protected $_storage = null;

    /**
     * flags if an object does not have any data
     * written in our database
     * @var boolean
     */
    protected $_persistent = false;

    /**
     * standard constructor of each model
     * @author mlaug
     * @param int $id
     * @return mixed null|Default_Model_Base
     */
    public function __construct($id = null, $current = null) {
        
        $this->load($id, false, $current);
    }

    /**
     * load based on the given id the current row from
     * the database, store as array and Zend_Db_Row
     * @author mlaug
     * @param int $id
     * @return mixed boolean|Default_Model_Base
     */
    public function load($id = null, $raw = false, $current = null) {

        if ($id === null || empty($id)) {
            return false;
        }

        $this->setId($id);

        // get data from database
        $table = $this->getTable();
        if ($table !== null) {
            $this->setData(
                $table->getCurrent()->toArray(), $raw
            );
        }

        $this->_unmodifiedData = $this->_data;
        $this->_persistent = true;
        
        return $this;
    }

    /**
     * Reload data
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.06.2012
     * @return boolean|Default_Model_Base
     */
    public function reload() {
        
        return $this->load($this->getId());
    }
    
    /**
     * check if there is a conection with the database
     * @author mlaug
     * @return boolean
     */
    public function isPersistent() {
        return $this->_persistent;
    }

    /**
     * @author mlaug
     * @since 26.10.2010
     * @return int
     */
    public function getCreated() {
        return strtotime($this->_data['created']);
    }

    /**
     * @author mlaug
     * @since 26.10.2010
     * @return int
     */
    public function getUpdated() {
        return strtotime($this->_data['updated']);
    }

    /**
     * Magic Getter method to get some Zend Objects
     * @author mlaug
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        switch ($name) {

            /**
             * get the logger
             */
            case "logger":
                if ($this->_logger === null) {
                    $this->_logger = Zend_Registry::get('logger');
                }
                return $this->_logger;

            /**
             * get the cache
             */
            case "cache": 
                if ($this->_cache === null && Zend_Registry::isRegistered('cache')) {
                    $this->_cache = Zend_Registry::get('cache');
                }
                return $this->_cache;

            /**
             * get our session
             * we use "Default", should consider to differ in the futuere
             * to avoid redundant usage of variable names
             */
            case "session": 
                if ($this->_session === null) {
                    // start new Session or get previous from namespace
                    $this->_session = new Zend_Session_Namespace('Default');
                }
                return $this->_session;

            /**
             * get current configuration of application
             */
            case "config": 
                if ($this->_config === null) {
                    $this->_config = Zend_Registry::get('configuration');
                }
                return $this->_config;
        }
    }

    /**
     * each unknown call we trap into __call. If it is a getter method
     * we try to access if in the data array
     * @param string $method
     * @param array $args
     * @return string
     */
    public function __call($method, $args) {
        switch (substr($method, 0, 3)) {

            //get a value|array from the data array
            case 'get':
                //remove _get
                $key = lcfirst(substr($method, 3));

                //get value
                $value = null;
                if (array_key_exists($key, $this->_data)) {
                    $value = $this->_data[$key];
                    //if string has id at end, remove
                    //check if object has already been created
                    if (is_object($this->_data[$key])) {
                        return $this->_data[$key];
                    }
                }

                /**
                 * get all the relations as object
                 * but store the id, too
                 */
                switch ($key) {
                    default:
                        break;

                    //get company object
                    case 'company':
                        $id = $this->_data['companyId'];
                        try {
                            $this->_data['company'] = new Yourdelivery_Model_Company($id);
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->_data['company'] = new Yourdelivery_Model_Company();
                        }
                        return $this->_data['company'];

                    //get admin object
                    case 'admin':
                        $id = $this->_data['adminId'];
                        try {
                            $this->_data['admin'] = new Yourdelivery_Model_Admin($id);
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->_data['admin'] = new Yourdelivery_Model_Admin();
                        }
                        return $this->_data['admin'];

                    //if the key is 'custoemr_id' this reference a customer model, so we create it
                    case 'customer':
                        $id = $this->_data['customerId'];
                        try {
                            $customer = new Yourdelivery_Model_Customer($id);
                            if ($customer->isEmployee()) {
                                $customer = new Yourdelivery_Model_Customer_Company(
                                    $customer->getId(),
                                    $customer->getCompany()->getId()
                                );
                            }
                            $this->_data['customer'] = $customer;
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->_data['customer'] = new Yourdelivery_Model_Customer();
                        }
                        return $this->_data['customer'];

                    //get order object
                    case 'order':
                        $id = $this->_data['orderId'];
                        try {
                            $this->_data['order'] = new Yourdelivery_Model_Order($id);
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->_data['order'] = new Yourdelivery_Model_Order();
                        }
                        return $this->_data['order'];

                    case 'rabatt_code':
                        $id = $this->_data['rabattCodeId'];
                        try {
                            $this->_data['discount'] = new Yourdelivery_Model_Rabatt_Code(null, $id);
                        } catch (Yourdelivery_Exception_DatabaseInconsistency $e) {
                            $this->_data['discount'] = new Yourdelivery_Model_Rabatt_Code();
                        }
                        return $this->_data['discount'];

                    // get service object
                    case 'service':
                    case 'restaurant':
                        $id = $this->_data['restaurantId'];
                        
                        //if this is an order object we may get mode
                        $mode = "rest";
                        if (array_key_exists('mode', $this->_data)) {
                            $mode = $this->_data['mode'];
                        }
                        
                        /**
                         * we need to differ between those three types
                         * to get the rights values and association from the database
                         */
                        try {
                            switch ($mode) {
                                default:
                                case 'rest':
                                    $this->_data['service'] = new Yourdelivery_Model_Servicetype_Restaurant($id);
                                    break;

                                case 'cater': 
                                    $this->_data['service'] = new Yourdelivery_Model_Servicetype_Cater($id);
                                    break;

                                case 'great': 
                                    $this->_data['service'] = new Yourdelivery_Model_Servicetype_Great($id);
                                    break;

                                case 'fruit': 
                                    $this->_data['service'] = new Yourdelivery_Model_Servicetype_Fruit($id);
                                    break;

                            }
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->_data['service'] = Yourdelivery_Model_Servicetype_Restaurant();
                        }

                        return $this->_data['restaurant'] = $this->_data['service'];
                }

                // if is string remove slashes
                if (is_string($value)) {
                    return stripslashes($value);
                }
                return $value;


            //set a variable in the data array
            case 'set':
                //prepare string to match a array key: getPrimaryColor => primary_color
                $key = lcfirst(substr($method, 3));
                if (count($args) > 0) {
                    $value = $args[0];
                    if (is_string($value)) {
                        $value = addslashes($value);
                    }

                    $this->_data[$key] = $value;
                    return $this;
                } 
                
                return false;

            //check if varialbe is set
            case 'has':
                //prepare string to match a array key: getPrimaryColor => primary_color
                $key = lcfirst(substr($method, 3));
                if (array_key_exists($key, $this->_data)) {
                    $value = $this->_data[$key];
                    if (!empty($value)) {
                        return true;
                    }
                }
                
                return false;

            //append an elemtn to an array
            case 'app':
                //prepare string to match a array key: getPrimaryColor => primary_color
                $key = lcfirst(substr($method, 6));
                if (count($args) > 0) {

                    //if this key is not array we make it one
                    if (!array_key_exists($key, $this->_data) || !is_array($this->_data[$key])) {
                        $this->_data[$key] = array();
                    }

                    //if the supplied key already points to an array we merge those two
                    //old data will be overwritten by new data
                    if (is_array($args[1]) &&
                            array_key_exists($args[0], $this->_data[$key]) &&
                            is_array($this->_data[$key][$args[0]])) {
                        $this->_data[$key][$args[0]] = array_merge(
                                $this->_data[$key][$args[0]], $args[1]
                        );
                    }
                    //otherwise we just place it in
                    else {
                        $this->_data[$key][$args[0]] = $args[1];
                    }

                    return $this;
                } else {
                    return false;
                }

            // notifications
            case 'war': 
                Default_View_Notification::warn($args[0]);
                return true;

            case 'err': 
                Default_View_Notification::error($args[0]);
                return true;

            case 'inf':
            case 'suc': 
                Default_View_Notification::success($args[0]);
                return true;
        }

        /**
         * checks if a value is true or false
         * BE AWARE: if the is method is called and does not found a
         * corresponding value in the _data array this method will return
         * false; this must be considered while implementing logic
         * it may be wise to through a exception here if call method
         * does not trap anything in its switch cases
         */
        switch (substr($method, 0, 2)) {
            //check if value is true (1) or false (0)
            case 'is':
                //prepare string to match a array key: getPrimaryColor => primary_color
                $key = lcfirst(substr($method, 2));                
                
                //loop throw data array
                if (is_array($this->_data)) {
                    if (array_key_exists($key, $this->_data)) {
                        if ($this->_data[$key] == 1) {
                            return true;
                        }
                    }
                }
                
                return false;
        }

        return false;
    }

    /**
     * get id of model
     * @author mlaug
     * @return int
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * setter id
     * @author mlaug
     * @param int $id
     */
    public function setId($id) {

        $this->_id = $id;
        $this->_data['id'] = $id;
        
        $table = $this->getTable();
        if ($table !== null) {
            // we also set up id in table to get correct row
            $table->setId($id);
            $this->_persistent = true;
        }
    }

    /**
     * Set all data at once, but keep the old data not set 
     * in the new data array. this will be done by merging the 
     * old data array with the new one
     * @author mlaug
     * @param array $data
     * @return Default_Model_Base
     */
    public function setData($data = array(), $raw = false) {

        if (array_key_exists('id', $data)) {
            unset($data['id']);
        }
        $this->_data = array_merge($this->_data, $data);
        return $this;
    }

    /**
     * get entire data array from this object
     * @author mlaug
     * @return array
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * save this object using a smart save method. If an id exists, we update
     * the current object but filter first all data which is not in the table. If
     * no id exists, we create a new row, using sql insert. 
     * @author mlaug
     * @return mixed boolean|int
     */
    public function save() {

        //create data array
        $_data = $this->getData();
        $meta = $this->getTable()->info(Zend_Db_Table_Abstract::METADATA);
        $rows = array_keys($meta);
        $data = array();

        $isInsert = $this->getId() === null || !$this->isPersistent();
        
        //work over provieded data
        foreach ($_data as $key => $value) {
            //check if this is an object => foreign key relation
            if (is_object($value)) {
                $key = $key . "Id";
                $_value = $value->getId();
                if ($_value === null || $_value === false) {
                    continue;
                }
                $data[$key] = $_value;
            } else {
                if ($key == 'created') {
                    continue;
                } elseif ($key == 'updated') {
                    $data[$key] = date('Y-m-d H:i:s');
                } elseif ($value === null) {
                    if (!$meta[$key]['NULLABLE']) {
                        $data[$key] = $meta[$key]['DEFAULT'] === null ? "" : $meta[$key]['DEFAULT'];
                    } elseif (!$isInsert) {
                        $data[$key] = $value;
                    }
                } else {
                    $data[$key] = $value;
                }
            }
        }

        unset($data['id']);

        // object must be created
        if ($isInsert) {
            try {
                $id = $this->getTable()->insert($data);
                if ($id === false) {
                    $this->logger->crit(sprintf('could not save data to database : %s', print_r($data, true)));
                    throw new Yourdelivery_Exception_Database_Inconsistency('Could not create row');
                }

                $this->load($id); // reload data
                $this->logger->debug(sprintf('saved model %s with id #%d new data :: %s', get_class($this), $id, str_replace(array("\r", "\r\n", "\n"), '', print_r($data, true))));
                return $id;
            } catch (Exception $e) {

                if (!IS_PRODUCTION) {
                    $db = Zend_Db_Table::getDefaultAdapter();
                    $dbProfiler = $db->getProfiler();
                    $dbQuery = $dbProfiler->getLastQueryProfile();
                    $dbSQL = $dbQuery->getQuery();
                    $this->logger->crit($dbSQL);
                }

                //something went wrong, rollback
                $this->logger->crit($e->getMessage() . $e->getTraceAsString());
                Yourdelivery_Sender_Email::error($e->getMessage() . $e->getTraceAsString(), true);
                throw new Yourdelivery_Exception_Database_Inconsistency('Could not create row: ' . $e->getMessage());
            }
        }
        //object must be updated
        else {
            try {

                //update only those column which have been modified and available in the table
                $updateData = array_diff($data, array_intersect_key($rows, $this->_unmodifiedData));
                $this->logger->debug(sprintf('updating model %s #%d with modified data :: %s', __CLASS__, $this->getId(), str_replace(array("\r", "\r\n", "\n"), '', print_r($updateData, true))));

                if (count($updateData) > 0) {
                    $this->getTable()
                         ->update($updateData, 'id = ' . (integer) $this->getId());

                    $this->getTable()->resetCurrent();
                    $this->_unmodifiedData = $this->getTable()->getCurrent()->toArray();
                } else {
                    $this->logger->debug('no modified data available');
                }

                return true;
            } catch (Exception $e) {

                if (!IS_PRODUCTION) {
                    $db = Zend_Db_Table::getDefaultAdapter();
                    $dbProfiler = $db->getProfiler();
                    $dbQuery = $dbProfiler->getLastQueryProfile();
                    $dbSQL = $dbQuery->getQuery();
                    $this->logger->crit($dbSQL);
                }

                $this->logger->crit($e->getMessage() . $e->getTraceAsString());
                Yourdelivery_Sender_Email::error($e->getMessage() . $e->getTraceAsString(), true);
                throw new Yourdelivery_Exception_Database_Inconsistency('Could not update row ' . $e->getMessage());
            }
        }
    }

    /**
     * transform everyting from the data array to valid xml
     * @author mlaug
     * @return string
     */
    public function toXML() {
        $xml = "";
        $worker = new Yourdelivery_XML_Worker();
        $xml = $worker->array2xml($this->getData(), $this->getId());
        return $xml;
    }

    /**
     * return file storage object to work with files
     * @author mlaug
     * @return Default_File_Storage
     */
    public function getStorage() {
        if ($this->_storage === null) {
            $this->_storage = new Default_File_Storage();
        }
        return $this->_storage;
    }

    /**
     * if this object is serialized we do not
     * want to have any pdo connection
     * @author mlaug
     */
    public function closeConnection() {
        $this->_table = null;
    }

    /**
     * check if the object is healthy :)
     * if this object return false, constructor throws InconsistencyException
     * @author mlaug
     * @todo this has to be implemented, once we got the time to check for any inconsistency
     * should be abstract then
     * @return boolean
     */
    public function gesundheitsVorsorge() {
        return true;
    }

    /**
     * get elements of this class
     * @author mlaug
     * @return array
     */
    public static function getFields() {
        return self::_getFields(__CLASS__);
    }

    /**
     * @author mlaug
     * @deprecated should be removed once the gc_enable is stable
     * get all class variables and unset them
     */
    public function cleanUp() {
        //cleanup everything from attributes
        foreach (get_class_vars(__CLASS__) as $clsVar => $_) {
            unset($this->$clsVar);
        }

        //cleanup all objects inside data array
        if (is_array($this->_data)) {
            foreach ($this->_data as $value) {
                if (is_object($value) && method_exists($value, 'cleanUp')) {
                    $value->cleanUp();
                }
            }
        }
    }

    /**
     * get all data which should be serialized
     * @author mlaug
     * @return array
     */
    public function __sleep() {
        $this->_table = null;

        //store strings as base64 mime so that
        //we do not lose utf8 encoding
        foreach ($this->_data as $key => $value) {
            if (is_string($value)) {
                $this->_data[$key] = base64_encode($value);
            }
        }
        //get class name and discover class properties
        return array_keys(get_class_vars(get_class($this)));
    }

    /**
     * recreate object after unserialization
     * @author mlaug
     */
    public function __wakeup() {

        if ($this->_id > 0) {
            //it may be deleted
            try {
                $this->setId($this->_id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->err(sprintf('Could not wake up object %s with id %d', get_class($this), $this->_id));
            }
        }

        //restore strings from base64 encoding
        foreach ($this->_data as $key => $value) {
            if (is_string($value)) {
                //this is to restore utf8 encoding
                $this->_data[$key] = base64_decode($value);
            }
        }
    }

    /**
     * implement just to avoid any errors from objects in smarty
     * @author mlaug
     * @return string
     */
    public function __toString() {
        return '';
    }

    /**
     * must be implemented or at least return a null
     * @author mlaug
     * @abstract
     * @return Default_Model_DbTable_Base
     */
    abstract function getTable();
}
