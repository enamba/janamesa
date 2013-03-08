<?php

/**
 * Description of Servicetype
 * @package service
 * @author Matthias Laug <laug@lieferando.de>
 */
abstract class Yourdelivery_Model_Servicetype_Abstract extends Default_Model_Base {
    /**
     * those types are available for usage
     */

    const RESTAURANT = 'rest';
    const CATER = 'cater';
    const GREAT = 'great';
    const FRUIT = 'fruit';
    const CANTEEN = 'canteen';
    const RESTAURANT_IND = 1;
    const CATER_IND = 2;
    const GREAT_IND = 3;
    const FRUIT_IND = 4;
    const CANTEEN_IND = 5;
    const CLOSED_OVERLAY = 'closed_overlay.png';

    /**
     * defines the type of this service as statet above
     * @var int
     */
    protected $_type = null;

    /**
     * id of service type
     * @var int
     */
    protected $_typeId = null;

    /**
     * all items which are to be sold by this service
     * @var Yourdelivery_Model_Menu
     */
    protected $_menu = null;

    /**
     * name by which service is called or referenced
     * @var string
     */
    protected $_name = null;

    /**
     * current city id
     * @var string
     */
    protected $_currentCityId = null;

    /**
     * stores all the orders of an service
     * @var SplObjectStorage
     */
    protected $_orders = null;

    /**
     * current bill of this service
     * @var Yourdelivery_Model_Billing_Restaurant
     */
    protected $_currentBill = null;

    /**
     * courier of this service
     * @var Yourdelivery_Model_Courier
     */
    protected $_courier = null;

    /**
     * rating of service
     * @param array
     */
    protected $_starCount = null;

    /**
     * minimal cost, may be overwritten
     * @var int
     */
    protected $_minCost = null;

    /**
     * minimal deliver cost, may be overwritten
     * @var int
     */
    protected $_delCost = null;

    /**
     * Latitude Longitude
     * @var array
     */
    private $_latlng = null;

    /**
     * Deliver ranges
     * @var array
     */
    protected $_ranges = null;

    /**
     * @var Yourdelivery_Model_Printer_Abstract
     */
    protected $_printer = null;

    /**
     * @var Yourdelivery_Model_Servicetype_Openings
     */
    protected $_opening = null;

    /**
     * @var array
     */
    protected $_billingChildren = null;
    
    /**
     * @var Yourdelivery_Model_Servicetype_Partner
     */
    protected $_partnerData = null;

    /**
     * @var array
     */
    protected $_payments = null;

    /**
     * Get stati of services
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    static function getStati() {
        return array(
            0 => __b('online'),
            2 => __b('Nicht erreichbar'),
            3 => __b('in Bearbeitung'),
            4 => __b('Umbau/Umzug'),
            5 => __b('momentan kein Fahrer'),
            6 => __b('Vertragsprobleme'),
            7 => __b('defizientes Faxgerät'),
            8 => __b('Pipeline'),
            9 => __b('ready to check'),
            10 => __b('wartet auf Kurierdienst'),
            11 => __b('gekündigt'),
            12 => __b('Urlaub'),
            13 => __b('warten mit Freischaltung'),
            14 => __b('Inhaberwechsel'),
            15 => __b('keinen Lieferservice mehr'),
            16 => __b('Karte fehlt'),
            17 => __b('Liefergebiete fehlen'),
            18 => __b('neu angehen'),
            19 => __b('Betrieb aufgegeben'),
            20 => __b('Karten update'),
            21 => __b('Vertrag fehlt'),
            22 => __b('zur internen Nutzung'),
            23 => __b('nur für Support'),
            24 => __b('wartet auf SMS Terminal'),
            25 => __b('Problem mit Abrechnung'),
            26 => __b('weitergeleitet an Vertrieb'),
            27 => __b('Karte hochgeladen'),
            28 => __b('Kartenüberprüfung'),
            29 => __b('PLZ Unterteilung'),
            30 => __b('gekündigt ohne Vertrag'),
            31 => __b('gekündigt - Probephase'),
        );
    }

    /**
     * Get billing deliver kinds
     * @author Alex Vait <vait@lieferando.de>
     * @since 27.03.2012
     * @return array
     */
    static function getDeliverKinds() {
        return array(
            'none' => __b('Kein'),
            'all' => __b('Fax und eMail'),
            'fax' => __b('Faxgerät'),
            'email' => __b('eMail'),
            'post' => __b('Postversand'),
            'empo' => __b('Post und Email')
        );
    }

    /**
     * Get notification kinds
     * @author Alex Vait <vait@lieferando.de>
     * @since 28.03.2012
     * @return array
     */
    static function getNotificationKinds() {
        $notificationKinds = array(
            'all' => __b('Fax und eMail'),
            'smsemail' => __b('SMS Drucker und eMail'),
            'fax' => __b('Faxgerät'),
            'sms' => __b('GPRS Drucker'),
            'email' => __b('eMail'),
            'acom' => __b('Acom Kassensystem'),
            'mobile' => __b('SMS aufs Handy')
        );

        $config = Zend_Registry::get('configuration');

        if ($config->domain->base == 'janamesa.com.br') {
            $notificationKinds['ecletica'] = 'Ecletica API';
            $notificationKinds['phone'] = 'Telefon';
        }

        if ($config->domain->base == 'lieferando.de') {
            $notificationKinds['charisma'] = __b('Charisma Grill Schnittstelle');
        }

        return $notificationKinds;
    }

    /**
     * get all services by any given city. We reuse the getByCityId method
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 17.04.2012
     * @param string $city
     * @param integer $typeId
     * @param boolean $onlyOffline
     * @return SplObjectStorage
     */
    static public function getByCity($city, $typeId = null, $onlyOffline = false) {
        return self::getByCity($city, $typeId, $onlyOffline);
    }

    /**
     * get a list of restaurants based on the plz
     * @author Matthias Laug <laug@lieferando.de>
     * @param mixed array|integer $cityId
     * @param int $typeId
     * @param boolean $onlyOffline
     * @return array of objects
     */
    static public function getByCityId($cityId, $typeId = null, $onlyOffline = false, $limit = null) {

        switch ($typeId) {
            case 'rest':
                $typeId = 1;
                break;

            case 'cater':
                $typeId = 2;
                break;

            case 'great':
                $typeId = 3;
                break;
        }

        $result = array();
        try {
            if (!$onlyOffline) {
                $result = Yourdelivery_Model_DbTable_Restaurant::getList($cityId, $typeId, null, $limit);
            } else {
                //add Offline Stati                ;
                $result = Yourdelivery_Model_DbTable_Restaurant::getList($cityId, $typeId, array(2, 3, 4, 5, 6, 7, 10, 14, 15, 16, 17, 18, 20, 24), $limit);
            }
        } catch (Zend_Db_Statement_Exception $e) {
            return new SplObjectStorage();
        }

        $config = Zend_Registry::get('configuration');

        $services = array();
        foreach ($result as $s) {
            try {

                switch ($s['servicetypeId']) {
                    default:
                    case 1:
                        $service = new Yourdelivery_Model_Servicetype_Restaurant($s['id']);
                        break;
                    case 2:
                        $service = new Yourdelivery_Model_Servicetype_Cater($s['id']);
                        break;
                    case 3:
                        $service = new Yourdelivery_Model_Servicetype_Great($s['id']);
                        break;
                }

                if (is_array($cityId)) {
                    $cityId = $cityId[0];
                }

                $service->setCurrentCityId($cityId);

                if (!$service->isOnline() && !$onlyOffline) {
                    continue;
                }

                //check for eatstar
                //@todo: refactor in config
                if ($service->isBloomsburys() && $config->domain->base == 'eat-star.de') {
                    continue;
                }

                $services[md5($s['servicetypeId'] . $service->getId())] = $service;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
        }

        return $services;
    }
    
    /**
     * get a list of restaurants based on the plz
     * @author Matthias Laug <laug@lieferando.de>
     * @param mixed array|integer $cityId
     * @param int $typeId
     * @param boolean $onlyOffline
     * @return array of objects
     */
    static public function getByCategoryId($categoryId, $typeId = null, $onlyOffline = false, $limit = null) {
        switch ($typeId) {
            case 'rest':
                $typeId = 1;
                break;

            case 'cater':
                $typeId = 2;
                break;

            case 'great':
                $typeId = 3;
                break;
        }

        $result = array();
        try {
            if (!$onlyOffline) {
                $result = Yourdelivery_Model_DbTable_Restaurant::getListByCategoryId($categoryId, $typeId, null, $limit);
            } else {
                //add Offline Stati                ;
                $result = Yourdelivery_Model_DbTable_Restaurant::getListByCategoryId($categoryId, $typeId, array(2, 3, 4, 5, 6, 7, 10, 14, 15, 16, 17, 18, 20, 24), $limit);
            }
        } catch (Zend_Db_Statement_Exception $e) {
            return new SplObjectStorage();
        }

        $services = array();
        foreach ($result as $s) {
            try {

                switch ($s['servicetypeId']) {
                    default:
                    case 1:
                        $service = new Yourdelivery_Model_Servicetype_Restaurant($s['id']);
                        break;
                    case 2:
                        $service = new Yourdelivery_Model_Servicetype_Cater($s['id']);
                        break;
                    case 3:
                        $service = new Yourdelivery_Model_Servicetype_Great($s['id']);
                        break;
                }

//                if (is_array($cityId)) {
//                    $cityId = $cityId[0];
//                }
//
//                $service->setCurrentCityId($cityId);

                if (!$service->isOnline() && !$onlyOffline) {
                    continue;
                }
                $services[md5($s['servicetypeId'] . $service->getId())] = $service;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
        }

        return $services;
    }

    /**
     * get a number of online services
     * @author Jens Naie <laug@lieferando.de>
     * @param int $typeId
     * @param boolean $onlyOnline
     * @return int
     */
    static public function countAll($typeId = null, $onlyOnline = true) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $select = $db->select()->from('restaurants', array('count' => new Zend_Db_Expr('COUNT(*)')))
                     ->join('restaurant_servicetype', 'restaurants.id = restaurant_servicetype.restaurantId', array())
                     ->where('deleted = 0');
        if (!empty($typeId)) {
            $select->where('servicetypeId = ?', $typeId);
        }
        if ($onlyOnline) {
            $select->where('isOnline = 1');
        }

        $result = $db->query($select)->fetch();
        
        return $result['count'];
    }
    
     /**
     * Create service from id
     * @author vpriem
     * @return Yourdelivery_Model_Servicetype_Abstract
     */
    public static function createService($id) {

        // get db
        $db = Zend_Registry::get('dbAdapter');

        $row = $db->fetchRow(
                "SELECT r.id, s.className
            FROM `restaurants` r
            INNER JOIN `restaurant_servicetype` rs ON r.id = rs.restaurantId
            INNER JOIN `servicetypes` s ON rs.servicetypeId = s.id
            WHERE r.id = ?
            LIMIT 1", $id
        );

        if ($row) {
            $className = "Yourdelivery_Model_Servicetype_" . $row['className'];
            return new $className($row['id']);
        }

        return false;
    }

    /**
     * get a customer by given id, email or username
     * @author mlaug
     * @param int $id
     * @param string $secure
     * @return Yourdelivery_Model_Servicetype_Abstract
     */
    function __construct($id = null, $secure = null) {

        //nothing is set so we return null
        if (is_null($id) && is_null($secure)) {
            return $this;
        }

        //if username is set we try to gather it
        if (!is_null($secure)) {
            //get from secure key, salted!
            $result = Yourdelivery_Model_DbTable_Restaurant::findBySecure($secure);
            if (is_array($result) && $result['id']) {
                $this->load($result['id']);
            } elseif (!is_null($id)) {
                $this->load($id);
            } else {
                throw new Yourdelivery_Exception_Database_Inconsistency('Restaurant could not be found by secure');
            }
        }  else {
            $this->load($id);
        }

        return $this;
    }
    
    /**
     * implement the franchise IS check only for the servicetypes
     * 
     * @author Mattthias Laug <laug@lieferando.de>
     * @since 15.08.2012
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args) {
        switch (substr($method, 0, 2)) {
            case "is":
                $key = lcfirst(substr($method, 2));
                if (is_array($this->_data) && !array_key_exists($key, $this->_data)) {
                    $franchise = new Yourdelivery_Model_Servicetype_Franchise();
                    if (in_array($key, $franchise->getNames(true))) {
                        if ($key == $franchise->getAsciiNameById($this->_data['franchiseTypeId'])) {
                            return true;
                        }
                        return false;
                    }
                    return false;
                }
                return parent::__call($method, $args);
                
            default:
                return parent::__call($method, $args);
        }
    }
    
    /**
     * get storage object of this service
     * @author Matthias Laug <laug@lieferando.de>
     * @return Default_File_Storage
     */
    public function getStorage($path = null) {

        if (is_null($this->_storage)) {
            $this->_storage = new Default_File_Storage();
        }

        $this->_storage->resetSubFolder();
        $this->_storage->setSubFolder('restaurants/' . $this->getId());

        return $this->_storage;
    }

    /**
     * get storage object for documents of this service
     * @author alex
     * @since 21.12.2010
     * @return Default_File_Storage
     */
    public function getDocumentsStorage() {

        if (is_null($this->_docstorage)) {
            $this->_docstorage = new Default_File_Storage();
        }

        $this->_docstorage->resetSubFolder();
        $this->_docstorage->setSubFolder('restaurants/' . $this->getId() . '/documents');

        return $this->_docstorage;
    }

    /**
     * Get direct link depending on mode
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.07.2011
     * @param string $mode
     * @return string
     */
    public function getDirectLink($mode = null) {

        if ($mode === null) {
            $mode = $this->getType();
        }

        switch ($mode) {
            default:
            case 'rest':
                return $this->getRestUrl();

            case 'cater':
                return $this->getCaterUrl();

            case 'great':
                return $this->getGreatUrl();
        }
    }

    /**
     * get all documents associated with this service
     * @author alex
     * @since 21.12.2010
     * @return array
     */
    public function getDocuments() {
        $documents = array();

        $handler = @opendir(APPLICATION_PATH . '/../storage/restaurants/' . $this->getId() . '/documents');

        if (!$handler) {
            return array();
        }

        // open directory and read the filenames
        while ($file = readdir($handler)) {
            // if file isn't this directory or its parent, add it to the result
            if ($file != "." && $file != "..") {
                $documents[] = $file;
            }
        }
        closedir($handler);

        return $documents;
    }

    /**
     * remove document associated with this service
     * @author alex
     * @since 21.12.2010
     */
    public function removeDocument($document) {
        if (strlen(trim($document)) == 0) {
            return false;
        }

        return unlink(APPLICATION_PATH . '/../storage/restaurants/' . $this->getId() . "/documents/" . $document);
    }

    /**
     * get all restaurants from database
     * @author Matthias Laug <laug@lieferando.de>
     * @return SplObjectStorage
     */
    public static function all($all = false) {

        $table = new Yourdelivery_Model_DbTable_Restaurant();
        $result = $table->fetchAll();
        $restaurants = new SplObjectStorage();
        foreach ($result as $c) {
            if (intval($c->id) == 0) {
                continue;
            }
            $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($c->id);
            if ($all || $restaurant->isOnline()) {
                $restaurants->attach($restaurant);
            }
        }
        return $restaurants;
    }

    /**
     * get all categories that are available for services
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    public static function getCategories() {
        return Yourdelivery_Model_DbTable_Restaurant_Categories::getAll();
    }

    /**
     * this functions collects all tags from database and slices the
     * array into the defined count. After that we reduce the array only to
     * the given tag names
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2011
     * @return array
     */
    public function getTagsWithMaxStringlength($count = 30) {
        $tags = $this->getTable()->getTags();
        $closure = function($arr) use (&$count) {
                    $strlen = strlen($arr['tag']);
                    if (($count - $strlen) > 0) {
                        $count -= $strlen;
                        return true;
                    }
                    return false;
                };
        return array_map(function($arr) {
                            return $arr['tag'];
                        }, array_filter($tags, $closure));
    }

    /**
     * rearrange meal categories
     * @param array. {rank => categoryId}
     * @author alex
     * @since 27.09.2010
     */
    public function arrangeCategories($categories, $restaurantId) {
        return $this->getTable()->arrangeCategories($categories, $restaurantId);
    }

    /**
     * get all meals of restaurant
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    public function getMeals($includeDeleted = false) {
        return $this->getTable()->getMeals($this->getId(), $includeDeleted);
    }

    /**
     * this method should be overwritten if necessary
     * @since 07.08.2010
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $type
     * @return Yourdelivery_Model_Menu
     */
    public function getMenu($search = null, $noAlc = false, $noSmoke = false) {
        if (is_null($this->_menu)) {
            $menu = new Yourdelivery_Model_Menu();
            $menu->setServiceType($this);
            return $this->_menu = $menu->getItems($search, $noAlc, $noSmoke);
        }
        return $this->_menu;
    }

    /**
     * get menu fast, without extras informations
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    public function getSmallMenu($search = array()) {
        return $this->getTable()->getSmallMenu($search);
    }

    /**
     * get 3 most sold item
     * @author Matthias Laug <laug@lieferando.de>
     * @return SplObjectStorage
     */
    public $bestseller = null;

    /**
     * get the topsellers of this service
     *
     * @since 25.11.2011
     * @author Matthias Laug <laug@lieferando.de>
     * @param integer $total
     * @return array
     */
    public function getBestSeller($total = 3) {
        if ($this->bestseller === null || (is_array($this->bestseller) && count($this->bestseller) != $total)) {
            $meals = array();
            $bestseller = $this->getTable()->getBestSeller($total, $this->getTypeId());
            foreach ($bestseller as $result) {
                try {
                    $meal = new Yourdelivery_Model_Meals($result['mealId']);
                    $meal->setSoldCount($result['count']);
                    if (count($meal->getSizes()) == 1) {
                        $sizes = array_pop($meal->getSizes());
                        $meal->setCurrentSize($sizes['id']);
                    }
                    $meals[] = $meal;
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    continue;
                }
            }
            $this->bestseller = $meals;
        }
        return $this->bestseller;
    }

    /**
     * Get associated table
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_DbTable_Restaurant
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant();
        }
        return $this->_table;
    }

    /**
     * get the corresponding name from database
     * @abstract
     */
    abstract public function getServiceName();

    /**
     * get type of service
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * get type id
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getTypeId() {
        return $this->_typeId;
    }

    /**
     * Get longitude
     * @author Matthias Laug <laug@lieferando.de>
     * @since 01.08.2010
     * @return float
     */
    public function getLongitude() {

        list ($lat, $lng) = $this->getLatLng();
        return $lng;
    }

    /**
     * Get latitude
     * @author Matthias Laug <laug@lieferando.de>
     * @since 01.08.2010
     * @return float
     */
    public function getLatitude() {

        list ($lat, $lng) = $this->getLatLng();
        return $lat;
    }

    /**
     * add new meal category
     * @author alex
     * @param string $name
     * @param string $decs
     * @param string $mwst
     * @param string $hasPfand
     * @return int
     */
    public function addMealCategory($name, $desc, $mwst, $hasPfand = 0, $def = 0) {
        $catid = $this->getTable()->addMealCategory($name, $desc, $mwst, $hasPfand, $def);

        $catnn = new Yourdelivery_Model_Servicetype_MealCategorysNn();
        $catnn->setServicetypeId($this->getTypeId());
        $catnn->setMealCategoryId($catid);
        $catnn->save();

        return $catid;
    }

    /**
     * add new meal extra group
     * @author alex
     * @param string $name
     * @return Yourdelivery_Model_Meal_ExtrasGroups
     */
    public function addMealExtraGroup($name) {
        $group = new Yourdelivery_Model_Meal_ExtrasGroups();
        $group->setName($name);
        $group->setRestaurantId($this->getId());
        $group->save();
        return $group;
    }

    /**
     * delete meal category
     * @author alex
     * @param string $id
     * @return
     */
    public function deleteMealCategory($id) {
        Yourdelivery_Model_Servicetype_Servicetype::removeIfLastServicetypeOfMealCategories($id);
        $this->getTable()->deleteMealCategory($id);
        Yourdelivery_Model_DbTable_Servicetypes_MealCategorysNn::removeByMealCategoryId($id);
    }

    /**
     * delete options row
     * @author alex
     * @param string $id
     * @return
     */
    public function deleteMealOptionsRow($id) {
        $this->getTable()->deleteMealOptionsRow($id);
    }

    /**
     * delete meal option
     * @author alex
     * @param string $id
     * @return
     */
    public function deleteMealOption($id) {
        $this->getTable()->deleteMealOption($id);
    }

    /**
     * delete meal extra group
     * @author alex
     * @param string $id
     * @return
     */
    public function deleteMealExtraGroup($id) {
        $this->getTable()->deleteMealExtraGroup($id);
    }

    /**
     * delete meal extra
     * @author alex
     * @param string $id
     * @return
     */
    public function deleteMealExtra($id) {
        $this->getTable()->deleteMealExtra($id);
    }

    /**
     * move meal category up
     * @author alex
     * @param string $id
     * @return
     */
    public function upMealCategory($id) {
        $this->getTable()->upCategory($id);
    }

    /**
     * move meal category down
     * @author alex
     * @param string $id
     * @return
     */
    public function downMealCategory($id) {
        $this->getTable()->downCategory($id);
    }

    /**
     * move meal up in rank
     * @author alex
     * @param string $id
     * @return
     */
    public function moveMealUp($id) {
        $this->getTable()->moveMealUp($id);
    }

    /**
     * move meal down in rank
     * @author alex
     * @param string $id
     * @return
     */
    public function moveMealDown($id) {
        $this->getTable()->moveMealDown($id);
    }

    /**
     * move meal size left (up in rank)
     * @author alex
     * @param string $id
     * @return
     */
    public function moveMealSizeLeft($id) {
        $this->getTable()->moveMealSizeLeft($id);
    }

    /**
     * move meal size right (down in rank)
     * @author alex
     * @param string $id
     * @return
     */
    public function moveMealSizeRight($id) {
        $this->getTable()->moveMealSizeRight($id);
    }

    /**
     * get all available meal categories
     * @author alex
     * @return SplObjectStorage
     */
    public function getMealCategories() {
        $categories = new SplObjectStorage();
        foreach ($this->getTable()->getMealCategories() as $c) {
            $categories->attach(new Yourdelivery_Model_Meal_Category($c['id']));
        }
        return $categories;
    }

    /**
     * get all available meal categories sorted by rank
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    public function getMealCategoriesSorted() {
        $cats = $this->getTable()->getMealCategoriesSorted();
        $sortedList = array();
        foreach ($cats as $cat) {
            $sortedList[$cat['id']] = $cat['name'];
        }
        return $sortedList;
    }

    /**
     * get all available meal categories sorted by rank as array - is quicker than creating category objects
     * @author alex
     * @since 13.12.2010
     * @return array
     */
    public function getMealCategoriesSortedAsArray() {
        $categories = array();
        foreach ($this->getTable()->getMealCategoriesSorted() as $c) {
            try {
                $category = new Yourdelivery_Model_Meal_Category($c['id']);
                $categories[] = $category;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

            }
        }
        return $categories;
    }

    /**
     * get all extras groups of this restaurant
     * @author alex
     * @return SplObjectStorage
     */
    public function getMealExtrasGroups() {
        $groups = new SplObjectStorage();
        foreach ($this->getTable()->getMealExtrasGroups() as $g) {
            $groups->attach(new Yourdelivery_Model_Meal_ExtrasGroups($g['id']));
        }
        return $groups;
    }

    public function getMealExtrasGroupsCount() {
        return $this->getTable()->getMealExtrasGroupsCount();
    }

    /**
     * get all options rows of this restaurant
     * @author alex
     * @return SplObjectStorage
     */
    public function getMealOptionsRows() {
        $rows = new SplObjectStorage();
        foreach ($this->getTable()->getMealOptionsRows() as $r) {
            $rows->attach(new Yourdelivery_Model_Meal_OptionRow($r['id']));
        }
        return $rows;
    }

    /**
     * get category restaurant is listed in
     * THIS TELLS US THE CATEGORY OF FOOD (indian, italian )
     * NOT THE CATEGORY OF RESTAURANT ( rest, cater, great, fruit )
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * 
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getCategory() {
        return $this->getTable()->getCategory();
    }

    /**
     * get location object of this service
     * @author Matthias Laug <laug@lieferando.de>
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
     * @author vpriem
     * @since 05.01.2010
     * @param Yourdelivery_Model_Location $location
     * @param boolean $round
     * @return int
     */
    public function getDistanceTo(Yourdelivery_Model_Location $location, $round = false) {

        list($lat1, $lng1) = $this->getLatLng();

        list($lat2, $lng2) = $location->getLatLng();

        $distance = Default_Api_Google_Geocoding::distance($lng1, $lat1, $lng2, $lat2);

        return $round ? round($distance, 2) : $distance;
    }

    /**
     * get all orders related to this restaurant
     * @author Matthias Laug <laug@lieferando.de>
     * @return SplObjectStorage
     */
    public function getOrders() {
        if (is_null($this->_orders)) {
            $orders = new SplObjectStorage();
            foreach ($this->getTable()->getOrders() as $order) {
                $orders->attach(new Yourdelivery_Model_Order($order->id));
            }
            $this->_orders = $orders;
        }
        return $this->_orders;
    }

    /**
     * get count of all orders for this retaurant,
     * also fake, storno and so on
     * @author alex
     * @return array[Yourdelivery_Model_Order]
     */
    public function getOrdersCount($onlyConfirmed = false) {
        return $this->getTable()->getOrdersCount($onlyConfirmed);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return array
     */
    public function getOrdersCountPerWeek() {

        $orderStats = $this->getTable()->getOrdersCountPerWeek();

        $completeStats = array();


        foreach ($orderStats as $stat) {
            $completeStats[$stat['week']] = $stat['count'];
        }


        for ($i = 0; $i < date('W'); $i++) {
            if (empty($completeStats[$i])) {
                $completeStats[$i] = 0;
            }
        }

        uksort($completeStats, function($a, $b) {
                    if ($a == $b) {
                        return 0;
                    }
                    return ($a < $b) ? -1 : 1;
                });

        //  print_r($completeStats); die();

        return array_slice($completeStats, -10, 10, true);
    }

    /**
     * get order of service by given state
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $state
     * @return SplObjectStorage
     */
    public function getOrdersByState($state) {
        $orders = $this->getOrders();

        $orderState = new SplObjectStorage();
        foreach ($orders as $order) {
            $currentState = $order->getState();
            if ($currentState == $state) {
                $orderState->attach($order);
            }
        }
        return $orderState;
    }

    /**
     * add a child to this service, so their billings get merged
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.10.2011
     * @param Yourdelivery_Model_Servicetype_Abstract $service
     * @return boolean
     */
    public function addBillingChild(Yourdelivery_Model_Servicetype_Abstract $service) {
        if ($this->getId() == $service->getId()) {
            return false;
        }

        $billingMergeEntry = new Yourdelivery_Model_Servicetype_BillingMerge();
        $billingMergeEntry->setKind('rest');
        $billingMergeEntry->setParent($this->getId());
        $billingMergeEntry->setChild($service->getId());
        $billingMergeEntry->save();

        $this->_billingChildren = null;

        return true;
    }

    /**
     * get next bill based on given start and end point
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $string
     * @param int $until
     * @return Yourdelivery_Model_Billing_Restaurant
     */
    public function getNextBill($from = 0, $until = 0, $test = 0) {
        $mode = $this->getBillInterval();
        return new Yourdelivery_Model_Billing_Restaurant($this, $from, $until, $mode, $test);
    }

    /**
     * Check if restaurant has plz
     * @deprecated
     * @author vpriem
     * @param string $plz
     * @return boolean
     */
    public function hasPlz($plz) {

        return $this->getTable()->hasPlz($plz);
    }

    /**
     * Get latitude longitude
     * @author vpriem
     * @since 11.08.2010
     * @return array
     */
    public function getLatLng() {

        // retrieve coords if defined
        if ($this->_latlng !== null) {
            return $this->_latlng;
        }

        // get lon lat
        $lat = $this->_data['latitude'];
        $lng = $this->_data['longitude'];

        //
        if ($lat === null || $lng === null || !intval($lat) || !intval($lng)) {

            // build address
            $address = "";
            if ($this->getStreet() !== null) {
                $address .= $this->getStreet();
                if ($this->getHausnr() !== null) {
                    $address .= " " . $this->getHausnr();
                }
            }
            $address .= ( $address ? ", " : "") . $this->getPlz();

            // ask the one who knows everything
            $geo = new Default_Api_Google_Geocoding();
            if ($geo->ask($address)) {
                $this->setLatitude($lat = $geo->getLat());
                $this->setLongitude($lng = $geo->getLng());
            } else {
                return array(0, 0);
            }
        }
        return $this->_latlng = array($lat, $lng);
    }

    /**
     * Returns default graphic or the image of this service
     * @todo the path returned is not accurate, must be fixed, but poses no problem
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getImg($size = 'normal') {

        if (is_null($this->getId())) {
            return null;
        }
        
        //FIX to use the images from owner server.
        if (IS_PRODUCTION) {
            return '/../storage/restaurants/'.$this->getId().'/default.jpg';
        }
        //check for valid input
        $valid = array('api', 'tiny', 'small', 'normal');
        if (!in_array($size, $valid)) {
            $size = 'normal';
        }

        $width = $this->config->timthumb->service->{$size}->width;
        $height = $this->config->timthumb->service->{$size}->height;

        $http = isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443 ? 'https://' : 'http://';
        $domain = IS_PRODUCTION ? $this->config->domain->base : $this->config->domain->base . '.testing';

        $url = sprintf('%s/%s/service/%s/%s-%d-%d.jpg', $http . $this->config->domain->timthumb, $domain, $this->getId(), urlencode($this->getName()), $width, $height);
        return $url;
        
    }

    /**
     * Sets a new image for this service
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $name
     * @return boolean
     */
    public function setImg($name) {
        if (is_null($this->getId())) {
            return false;
        }

        $data = file_get_contents($name);
        // if file_get_contents failed $data is 'false'
        if ($data !== false) {
            $this->getStorage()->store('default.jpg', $data);

            //save image additionally in amazon s3
            $config = Zend_Registry::get('configuration');
            Default_Helpers_AmazonS3::putObject($config->domain->base, "restaurants/" . $this->getId() . "/default.jpg", $name);

            //purge varnish image logo
            if ($this->config->varnish->enabled) {
                $varnishPurger = new Yourdelivery_Api_Varnish_Purger();
                $varnishPurger->addUrl($this->getImg('tiny'));
                $varnishPurger->addUrl($this->getImg('small'));
                $varnishPurger->addUrl($this->getImg('normal'));
                $varnishPurger->executePurge();
            }
        }
    }

    /**
     * Returns the contact Object of this service
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Contact
     */
    public function getContact() {
        try {
            if (is_null(Yourdelivery_Model_DbTable_Contact::findById($this->getContactId()))) {
                return null;
            }
            $contact = new Yourdelivery_Model_Contact($this->getContactId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }

        return $contact;
    }

    /**
     * Returns the billing contact Object of this service
     * @author alex, Matthias Laug <laug@lieferando.de>
     * @since 15.08.2012
     * 
     * @return Yourdelivery_Model_Contact
     */
    public function getBillingContact() {
        try {
            $billContactId = (integer) $this->getBillingContactId();

            if ($billContactId <= 0) {
                return null;
            }

            return new Yourdelivery_Model_Contact($billContactId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
    }

    /**
     * get times and days, when service is open
     * @author Matthias Laug <laug@lieferando.de>
     * @return Zend_Db_Table_Rowset
     */
    public function getOpenings() {
        if (is_null($this->getId())) {
            return array();
        }

        $table = new Yourdelivery_Model_DbTable_Restaurant_Openings();
        return $table->getOpenings($this->getId());
    }

    /**
     * get times and days, when service is open
     * @author Matthias Laug <laug@lieferando.de>
     * @return Zend_Db_Table_Rowset
     */
    public function getRegularOpenings($day = null) {
        if (is_null($this->getId())) {
            return array();
        }

        $table = new Yourdelivery_Model_DbTable_Restaurant_Openings();
        return $table->getRegularOpenings($this->getId(), $day);
    }

    /**
     * get times when service is open for certain day
     * @author Matthias Laug <laug@lieferando.de>
     * @return Zend_Db_Table_Rowset
     */
    public function getOpeningsForDay($day) {
        if (is_null($this->getId())) {
            return array();
        }

        $table = new Yourdelivery_Model_DbTable_Restaurant_Openings();
        return $table->getOpenings($this->getId(), $day);
    }

    /**
     * remove certain times when service is open
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $id
     * @return boolean
     */
    public function deleteOpening($id) {
        if (is_null($id)) {
            return false;
        }
        return $this->getTable()->deleteOpening($id);
    }

    /**
     * get times and days, when service is open
     * @author Matthias Laug <laug@lieferando.de>
     * @return Zend_Db_Table_Rowset
     */
    public function getSpecialOpenings($time = null) {
        if (is_null($this->getId())) {
            return array();
        }
        $table = new Yourdelivery_Model_DbTable_Restaurant_Openings_Special();
        return $table->getOpenings($this->getId(), $time);
    }

    /**
     * overwrite mincost
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $mincost
     */
    public function setMinCost($mincost) {
        $this->_minCost = $mincost;
    }

    /**
     * overwrite mincost
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $mincost
     */
    public function setDelCost($delcost) {
        $this->_delCost = $delcost;
    }

    /**
     * get minimal cost of order
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $cityId
     * @return mixed boolean|int
     */
    public function getMinCost($cityId = null) {

        if ($this->_minCost !== null) {
            return $this->_minCost;
        }

        if ($cityId === null) {
            $cityId = $this->getCurrentCityId();
            if ($cityId === null) {
                return false;
            }
        }

        return $this->getTable()->getMinCost($cityId);
    }

    /**
     * get deliver cost of service, check if
     * we already reached a limit, where no costs
     * should be charged
     * @since 15.08.2010, 30.08.2010 (vpriem)
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $cityId
     * @param int $total
     * @return int
     */
    public function getDeliverCost($cityId = null, $total = 0, Yourdelivery_Model_Order_Abstract $order = null) {

        if (!is_null($this->_delCost)) {
            return $this->_delCost;
        }

        if ($cityId === null) {
            $cityId = $this->getCurrentCityId();
            if ($cityId === null) {
                return false;
            }
        }

        if ($total == 0 && is_object($order)) {
            $total = $order->getBucketTotal();
        }

        // TODO - change plz to cityId for courier
        if ($this->hasCourier()) {
            $courier = $this->getCourier();
            $company = null;
            if (is_object($order)) {
                $company = $order->getCustomer()->getCompany();
            }
            return $courier->getDeliverCost($cityId) - $courier->getDiscount($total, $company, $this);
        } else {

            $dcost = $this->getTable()->getDeliverCost($cityId);
            $noDeliverCost = $this->getTable()->getNoDeliverCostAbove($cityId);
            //if we are equally or above that limit, we do not charge deliver cost
            if ($noDeliverCost > 0 && $total >= $noDeliverCost) {
                return 0;
            }

            return $dcost;
        }
    }

    /**
     * get all ranges restaurants delivers in
     * you can also provide a limit to get only
     * a certian amount of ranges. this is mainly needed
     * for views
     * @author Matthias Laug <laug@lieferando.de>
     * @param integer $limit
     * @param boolean $withCourier
     * @return array
     */
    public function getRanges($limit = 10000, $withCourier = false) {
        if (is_null($this->_ranges)) {
            $this->_ranges = array();
        }

        $hash_limit = $limit . '-' . (string) $withCourier;
        if (!array_key_exists($hash_limit, $this->_ranges)) {

            if ($limit === null) {
                $limit = 10000;
            }

            $ranges = $this->getTable()->getRanges($limit);

            if ($withCourier && $this->hasCourier()) {
                $courier = $this->getCourier();

                $courierRanges = $courier->getRanges();
                foreach ($ranges as $k => $r) {
                    foreach ($courierRanges as $cr) {
                        if ($r['cityId'] == $cr['cityId'] || $r['parentCityId'] == $cr['cityId']) {
                            $ranges[$k]['deliverTime'] += $cr['deliverTime'] * 60;
                            $ranges[$k]['delcost'] = $cr['delcost'];
                            break;
                        }
                    }
                }
            }

            $this->_ranges[$hash_limit] = $ranges;
        }

        return $this->_ranges[$hash_limit];
    }

    /**
     * get the status of this range by cityId
     *
     * @param integer $cityId id of city
     *
     * @return boolean
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.02.2012
     */
    public function isRangeOnline($cityId = null) {
        if (is_null($cityId)) {
            return false;
        }
        return (boolean) $this->getTable()->getRangeStatus($cityId) > 0;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $id
     * @return boolean
     */
    public function deleteRange($id = null) {
        if (is_null($id)) {
            return false;
        }
        $this->getTable()->deleteRange($id);
        return true;
    }

    /**
     * delete delivering range by city id
     * @author alex
     * @param int $cityId
     * @since 09.03.2011
     */
    public function deleteRangeByCityId($cityId = null) {
        if ($cityId === null) {
            return false;
        }
        $this->getTable()->deleteRangeByCityId($cityId);
        return true;
    }

    /**
     * delete all locations for this restaurant
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function deleteAllRanges() {
        $this->getTable()->deleteAllRanges();
        return true;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $plz
     * @param int $mincost
     * @param int $delcost
     * @param int $deltime
     * @return boolean
     */
    public function createLocation($cityId, $mincost = 0, $delcost = 0, $deltime = 0, $noDeliverCostAbove = 0) {
        $rangeId = $this->getTable()->getUniqueDeliverRangeId($cityId);
        // if range already exists, overwrite it
        if ($rangeId != 0) {
            $range = new Yourdelivery_Model_Servicetype_Plz($rangeId);
        } else {
            $range = new Yourdelivery_Model_Servicetype_Plz();
        }

        try {
            $city = new Yourdelivery_Model_City($cityId);

            $range->setCityId($city->getId());
            $range->setPlz($city->getPlz());
            $range->setDeliverTime($deltime);
            $range->setDelcost(priceToInt2($delcost));
            $range->setMincost(priceToInt2($mincost));
            $range->setNoDeliverCostAbove(priceToInt2($noDeliverCostAbove));
            $range->setRestaurantId($this->getId());
            $range->setStatus(1);
            
            $city->setGeoReferences();
            
            return $range->save();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return false;
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $city
     * @param int $mincost
     * @param int $delcost
     * @param int $deltime
     * @return boolean
     */
    public function createCityRange($city, $mincost, $delcost, $deltime, $noDeliverCostAbove) {
        if (empty($city) || empty($deltime)) {
            return false;
        }

        $s = Yourdelivery_Model_City::getByCity($city);

        if (!$s) {
            return false;
        }

        foreach ($s as $row) {
            $this->createLocation($row['id'], $mincost, $delcost, $deltime, $noDeliverCostAbove);
        }

        return true;
    }

    /**
     * edit all locations with given data
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $mincost
     * @param int $delcost
     * @param int $time
     */
    public function editLocationAll($mincost, $delcost, $time, $isOnline) {
        $this->getTable()->editLocationAll(priceToInt2($mincost), priceToInt2($delcost), $time, $isOnline);
    }

    /**
     * get deliver time
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $cityId
     * @return mixed boolean|int
     */
    public function getDeliverTime($cityId = null) {

        if ($cityId === null) {
            $cityId = $this->getCurrentCityId();
            if ($cityId === null) {
                return false;
            }
        }
        $time = $this->getTable()->getDeliverTime($cityId);

        if ($this->hasCourier()) {
            /**
             * @todo: check which courier is nearest
             */
            $time += $this->getCourier()->getDeliverTime($cityId);
        }

        return $time;
    }

    /**
     * Get real deliver time, courier deliver time excluded
     * @author vpriem
     * @since 30.09.2010
     * @param int $cityId
     * @return mixed boolean|int
     */
    public function getRealDeliverTime($cityId = null) {

        if ($cityId === null) {
            $cityId = $this->getCurrentCityId();
            if ($cityId === null) {
                return false;
            }
        }
        return $this->getTable()->getDeliverTime($cityId);
    }

    /**
     * Return a formated string of the real deliver time
     * @author vpriem
     * @since 01.10.2010
     * @param int $cityId
     * @return string
     */
    public function getRealDeliverTimeFormated($cityId = null) {

        $minutes = $this->getRealDeliverTime($cityId) / 60;

        if ($minutes < 60) {
            return _n("%d Minute", "%d Minuten", $minutes, $minutes);
        }

        $hours = intval($minutes / 60);
        return _n("%d Stunde", "%d Stunden", $hours, $hours);
    }

    /**
     * Return a formated string of the deliver time
     * @author Matthias Laug <laug@lieferando.de>
     * @since 01.08.2010, 31.08.2010 (vpriem)
     * @param int $cityId
     * @return string
     */
    public function getDeliverTimeFormated($cityId = null) {

        $minutes = $this->getDeliverTime($cityId) / 60;

        if ($minutes < 60) {
            return _n("%d Minute", "%d Minuten", $minutes, $minutes);
        }

        $hours = intval($minutes / 60);
        return _n("%d Stunde", "%d Stunden", $hours, $hours);
    }

    /**
     * get static commission without checking special intervals
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.12.2010
     * @return integer
     */
    public function getStaticCommission() {
        return $this->_data['komm'];
    }

    /**
     * get static commission without checking special intervals
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.12.2010
     * @return integer
     */
    public function getStaticFee() {
        return $this->_data['fee'];
    }

    /**
     * get static commission without checking special intervals
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.12.2010
     * @return integer
     */
    public function getStaticItem() {
        return $this->_data['item'];
    }

    /**
     * overwrite and use getCommission
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.12.2010
     * @param integer $time
     * @return integer
     */
    public function getKomm($time = null) {
        return $this->getCommission($time);
    }

    /**
     * get commission of this service and check for special intervals
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.12.2010
     * @param integer $time
     * @return integer
     */
    public function getCommission($time = null) {
        if ($time === null) {
            $time = time();
        }

        $special = $this->getCurrentSpecialCommission($time);
        if (is_array($special)) {
            return $special['komm'];
        }

        return $this->_data['komm'];
    }

    /**
     * get the fee per item sold by any order and check
     * if we have a special interval
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.12.2010
     * @param integer $time
     * @return integer
     */
    public function getItem($time = null) {
        if ($time === null) {
            $time = time();
        }

        $special = $this->getCurrentSpecialCommission($time);
        if (is_array($special)) {
            return $special['item'];
        }

        return (integer) $this->_data['item'];
    }

    /**
     * get the fee per order and check if we have a special interval here
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.12.2010
     * @param integer $time
     * @return integer
     */
    public function getFee($time = null) {
        if ($time === null) {
            $time = time();
        }

        $special = $this->getCurrentSpecialCommission($time);
        if (is_array($special)) {
            return $special['fee'];
        }

        return (integer) $this->_data['fee'];
    }

    /**
     * get special commission amounts of a certian time
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.12.2010
     * @param integer $time
     * @return array
     */
    private function getCurrentSpecialCommission($time) {
        $specialIntervals = Yourdelivery_Model_DbTable_Restaurant_Commission::getAdditionalCommissions($this->getId());
        if (is_array($specialIntervals)) {
            foreach ($specialIntervals as $special) {
                $from = strtotime($special['from']);
                $until = strtotime($special['until']);
                if ($from <= $time && $until >= $time) {
                    return $special;
                }
            }
        }
        return null;
    }

    /**
     * check if this service is online
     *
     * We distinguish between the following cases
     *
     * 1) Is premium allowed? In the closed beta, we did not want to allow
     * everybody to gain access to premium services
     *
     * 2) if this is a company order we need to check for service list mode.
     *          1 => Show only assigned services
     *          2 => Show all and assigned services
     *
     * 3) some services are exclusivly assigned to a certian company. we do not
     * display those, if the company isn't the one who is ordering
     *
     * 4) check employees permission. some are not allowed to order cater, great or fruit
     *
     * if someone is allowed to order cater,great or fruit the online flat gets overwritten!
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function isOnline(Yourdelivery_Model_Customer_Abstract $customer = null, $currentKind = Yourdelivery_Model_Order_Abstract::PRIVATEORDER) {
        //if no customer is present we decide just to check the flags
        if ($customer === null) {
            return $this->_data['isOnline'] == 1 && !$this->isDeleted();
        }

        //get information
        $employee = $customer->isEmployee();
        if ($employee) {
            $company = $customer->getCompany();
            $companyId = $company->getId();
        }

        $onlyRestricted = false;
        if ($customer->isEmployee() && $currentKind == Yourdelivery_Model_Order_Abstract::COMPANYORDER) {
            if ($customer->getCompany()->getServiceListMode() == 1) {
                $onlyRestricted = true;
            }
        }

        //check the main flag!
        if ($this->_data['isOnline'] == 1) {

            $restrict = $this->getCompanyRestrictions();
            $online = true; //assume with isOnline == 1 service to be online
            //check company restrictions
            if (!is_null($restrict) && $restrict->count() > 0) {
                //check if this is a company order and set according default value
                $online = $currentKind == Yourdelivery_Model_Order_Abstract::COMPANYORDER && $onlyRestricted ? false : true;

                foreach ($restrict as $r) {
                    //is this one exclusive
                    if ($r->exclusive) {
                        $online = false;
                    }
                    //check is customer is an employee and this order is a company order
                    //otherwise to not overwrite exclusive thing
                    if ($currentKind == Yourdelivery_Model_Order_Abstract::COMPANYORDER && $employee) {
                        if ($r->companyId == $companyId) {
                            //yes it has, beat it
                            $online = true;
                            break;
                        }
                    }
                }
            }
            //if no restrictions are found, but company want only assigned ones
            //we set online to false
            elseif ($onlyRestricted) {
                $online = false; //if serviceListMode of company is 1 we assume service to be offline
            }

            //check permission and set to offline if permissions are not met
            if ($online === true && $currentKind == Yourdelivery_Model_Order_Abstract::COMPANYORDER) {
                if ($employee) {
                    switch ($this->getType()) {
                        case self::RESTAURANT :
                            break;


                        case self::CATER :
                            if (!$customer->allowCater()) {
                                $online = false;
                            }
                            break;


                        case self::GREAT :
                            if (!$customer->allowGreat()) {
                                $online = false;
                            }
                            break;


                        case self::FRUIT :
                            if (!$customer->allowGreat()) {
                                $online = false;
                            }
                            break;
                    }
                }
            }

            return $online;
        }

        return false;
    }

    /**
     * Check if this service has a courier
     * @author vpriem
     * @return boolean
     */
    public function hasCourier() {
        return is_object($this->getCourier());
    }

    /**
     * Get assigned courier for this service
     * @author vpriem
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return Yourdelivery_Model_Courier
     */
    public function getCourier(Yourdelivery_Model_Order_Abstract $order = null) {

        if ($this->_courier !== null) {
            return $this->_courier;
        }

        $couriers = $this->getTable()->getCourier();
        if (is_array($couriers)) {
            foreach ($couriers as $courier) {
                try {
                    $this->_courier = new Yourdelivery_Model_Courier($courier['courierId']);
                    $this->_courier->setCurrentService($this);
                    if (is_object($order)) {
                        $this->_courier->setCurrentLocation($order->getLocation());
                    }
                    return $this->_courier;
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    return false;
                }
            }
        }

        return $this->_courier = false;
    }

    /**
     * Check if this service has the go courier
     * @author vpriem
     * @since 16.09.2010
     * @return boolean
     */
    public function hasGoCourier() {

        $courier = $this->getCourier();
        if (is_object($courier)) {
            return $courier->getId() == 3; // hard coded
        }
        return false;
    }

    /**
     * Check if this service has the prompt courier
     * @author vpriem
     * @since 16.09.2010
     * @return boolean
     */
    public function hasPromptCourier() {

        $courier = $this->getCourier();
        if (is_object($courier)) {
            return $courier->getApi() == "prompt";
        }
        return false;
    }

    /**
     * get assigned salesperson for this service
     * @return Yourdelivery_Model_Salesperson
     */
    public function getSalesperson() {
        $sps = $this->getTable()->getSalesperson();
        if (is_null($sps) || (count($sps) == 0)) {
            return null;
        }

        $spassoc = $sps[0];

        if ($spassoc == null) {
            return null;
        }

        try {
            $salesperson = new Yourdelivery_Model_Salesperson($spassoc['salespersonId']);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }

        return $salesperson;
    }

    /**
     * Get billing parent id
     * @author alex
     * @since 28.09.2010
     * @return int
     */
    public function getBillingParentId() {
        $parents = $this->getTable()->getBillingParentId();
        if (is_null($parents) || count($parents) == 0) {
            return null;
        }
        return $parents[0]['parent'];
    }

    /**
     * Get current cityId
     * @param int $cityId
     */
    public function setCurrentCityId($cityId) {

        $this->_currentCityId = $cityId;
    }

    /**
     * Set current cityId
     * @return int
     */
    public function getCurrentCityId() {

        return $this->_currentCityId;
    }

    public function getOrdersCalendar($time = '0') {
        $db = Zend_Registry::get('dbAdapter');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $result = array();

        try {
            $sql = 'select count(id) as count from orders where restaurantId = ' . $this->getId() . ' and unix_timestamp(time) > ' . $time;
            $result = $db->fetchRow($sql);
        } catch (Zend_Db_Statement_Exception $e) {
            $db->setFetchMode(Zend_Db::FETCH_ASSOC);
            return 'error';
        }

        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        return $result->count;
    }

    public function getSalesCalendar($time = '0') {
        $db = Zend_Registry::get('dbAdapter');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $result = array();

        try {
            $sql = 'select sum(total) as sum from orders where restaurantId = ' . $this->getId() . ' and unix_timestamp(time) > ' . $time;
            $result = $db->fetchRow($sql);
        } catch (Zend_Db_Statement_Exception $e) {
            $db->setFetchMode(Zend_Db::FETCH_ASSOC);
            return 'error';
        }

        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        return $result->sum;
    }

    /**
     * is the restaurant marked as deleted?
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.03.2012
     * @return boolean
     */
    public function isDeleted() {
        return (boolean) $this->getDeleted() > 0;
    }

    /**
     * get restrictions of restaurant
     * any company assigned to restaurant is only allowed to order
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getCompanyRestrictions() {
        return $this->getTable()->getCompanyRestrictions();
    }

    /**
     * Get billing children of this restaurant
     * @return SplObjectStorage
     */
    public function getBillingChildren() {

        if ($this->_billingChildren !== null) {
            return $this->_billingChildren;
        }

        $children = $this->getTable()
                ->getBillingChildren($this->getId());

        $storage = new SplObjectStorage();
        foreach ($children as $c) {
            try {
                $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($c['childId']);
                if (!is_null($restaurant->getId())) {
                    $storage->attach($restaurant);
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
        return $this->_billingChildren = $storage;
    }

    /**
     * set restrictions of restaurant
     * any company assigned to restaurant is only allowed to order
     * @param int $companyId
     * @return
     */
    public function setCompanyRestriction($companyId, $excl = true) {
        return $this->getTable()->setCompanyRestriction($companyId, $excl);
    }

    /**
     * remove restrictions of restaurant
     * any company assigned to restaurant is only allowed to order
     * @param int $companyId
     * @return
     */
    public function removeCompanyRestriction($companyId) {
        return $this->getTable()->removeCompanyRestriction($companyId);
    }

    /**
     * check if this service has any categories of type restaurant
     * @return boolean
     */
    public function isRestaurant() {
        return $this->getTable()->isRestaurant();
    }

    /**
     * check if this service has any categories of type catering
     * @return boolean
     */
    public function isCatering() {
        return $this->getTable()->isCatering();
    }

    /**
     * check if this service has any categories of type fruit
     * @return boolean
     */
    public function isFruit() {
        return $this->getTable()->isFruit();
    }

    /**
     * check if this service has any categories of type great
     * @return boolean
     */
    public function isGreat() {
        return $this->getTable()->isGreat();
    }

    /**
     * check whether this service is not older than 10 days
     * @return boolean
     */
    public function isNew() {
        $regtime = $this->getCreated();
        if ($regtime > 0) {
            $days10 = $regtime + (14 * 24 * 60 * 60);
            if ($days10 > time()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param string $cityName
     */
    public function isInCity($cityName) {

        if (strcmp($this->getCity()->getCity(), $cityName) == 0) {
            return true;
        } elseif ($this->getCity()->getParentCityId() != null) {
            $city = new Yourdelivery_Model_DbTable_City();

            $row = $city->find($this->getCity()->getParentCityId());
            if (strcmp($row->current()->city, $cityName) == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Billing_Customized
     */
    public function getBillingCustomizedData() {

        //get billing contact
        $billcontact = $this->getBillingContact();
        $name = null;
        $street = null;
        $hausnr = null;
        $plz = null;
        $city = null;

        if ( $billcontact instanceof Yourdelivery_Model_Contact ){
            $name = $billcontact->getPrename() . ' ' . $billcontact->getName();
            $street = $billcontact->getStreet();
            $hausnr = $billcontact->getHausnr();
            $plz = $billcontact->getPlz();
            $cityObj = $billcontact->getCity();
            if ($cityObj instanceof Yourdelivery_Model_City) {
                $city = $cityObj->getCity();
            }
        }

        //set defaults
        $default = array(
            'heading' => $this->config->domain->base == 'pyszne.pl' && strlen($name) > 0 ? $name : $this->getName(),
            'street' => strlen($street) > 0 ? $street : $this->getStreet(),
            'hausnr' => strlen($hausnr) > 0 ? $hausnr : $this->getHausnr(),
            'zHd' => strlen($name) > 0 ? $name : '',
            'plz' => strlen($plz) > 0 ? $plz : $this->getPlz(),
            'city' => strlen($city) > 0 ? $city : $this->getOrt()->getOrt(),
            'addition' => strlen($this->getUstIdNr()) > 0 ? __b('Ust-ID:') . ' ' . $this->getUstIdNr() : '',
            'template' => 'standard',
            'reminder' => 14
        );
        
        $customized = array_merge($default, $this->getBillingCustomized()->getData());
        return $customized;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Billing_Customized
     */
    public function getBillingCustomized() {
        $customized = new Yourdelivery_Model_Billing_Customized();
        $cid = $this->getTable()->getBillingCustomized();
        if ($cid === false) {
            $customized->setMode('rest');
        } else {
            $customized->load($cid['id']);
        }

        $customized->setService($this);
        $customized->setRefId($this->getId());

        return $customized;
    }

    /**
     * get unsend, unpayed or payed billings
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getBalanceOfBillings() {
        return $this->getTable()->getBalanceOfBillings();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Billing_Balance
     */
    public function getBalance() {
        $balance = new Yourdelivery_Model_Billing_Balance();
        $balance->setObject($this);
        return $balance;
    }

    /**
     * has this one slots
     * @since 24.09.2010
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function hasSlots() {
        return $this->getSlots() > 0 ? true : false;
    }

    /**
     * check if there are free slots in given time period
     * @since 24.09.2010
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function hasFreeSlots() {
        if ($this->getTable()->getUsedSlots($this->getSlotsPeriod()) < $this->getSlots()) {
            return true;
        }
        return false;
    }

    /**
     * Remove all tags for this restaurant
     * @author alex
     * @since 05.10.2010
     */
    public function removeAllTags() {
        $this->getTable()->removeAllTags();
    }

    /**
     * Set new tag for this restaurant
     * 
     * @param integer $tagId
     * 
     * @return void
     * 
     * @author alex
     * @since 05.10.2010
     */
    public function addTag($tagId) {
        $this->getTable()->addTag($tagId);
    }

    /**
     * query if the restaurant has this tag set
     * @author alex
     * @since 05.10.2010
     */
    public function getAllTagsWithFlag() {
        return $this->getTable()->getAllTagsWithFlag();
    }

    /**
     * Returns fax of contact if available, else - the fax of restaurant
     * @author alex
     * @since 13.10.2010
     */
    public function getFax() {
        $fax = parent::getFax();

        if (!is_null($fax) && ($fax != 0)) {
            return $fax;
        }

        // restaurant has no fax, so get the fax of billing contact person
        try {
            $billcontact = new Yourdelivery_Model_Contact($this->getBillingContactId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

        }

        if (!is_null($billcontact) && strlen($billcontact->getFax()) > 0) {
            return $billcontact->getFax();
        }

        // restaurant has no fax and billing contact person has no fax, so get the fax of contact person
        try {
            $contact = new Yourdelivery_Model_Contact($this->getContactId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }

        if (!is_null($contact) && strlen($contact->getFax()) > 0) {
            return $contact->getFax();
        }

        return null;
    }

    /**
     * get a valid fax service, default is retarus
     * @author Matthias Laug <laug@lieferando.de>
     * @since 01.02.2011
     * @return string
     */
    public function getFaxService() {
        $faxservice = $this->_data['faxService'];
        switch ($faxservice) {
            default:
            case 'retarus':
                return 'retarus';
            case 'interfax':
                return 'interfax';
        }
    }

    /**
     * Returns email of restaurant if available, else - the email of billing contact or contact
     * @author alex
     * @since 13.10.2010
     */
    public function getEmail() {
        $email = parent::getEmail();

        if (!is_null($email) && (strlen($email) != 0)) {
            return $email;
        }

        // restaurant has no email, so get the email of billing contact person
        try {
            $billcontact = new Yourdelivery_Model_Contact($this->getBillingContactId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

        }

        if (!is_null($billcontact) && strlen($billcontact->getEmail()) > 0) {
            return $billcontact->getEmail();
        }

        // restaurant has no email and billing contact person has no email, so get the email of contact person
        try {
            $contact = new Yourdelivery_Model_Contact($this->getContactId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }

        if (!is_null($contact) && strlen($contact->getEmail()) > 0) {
            return $contact->getEmail();
        }

        return null;
    }
    
    /**
     * Returns tel number of restaurant if available, else - the tel of billing contact or contact
     * @author Alex Vait 
     * @since 31.07.2012
     */
    public function getMobile() {
        $tel = Default_Helpers_Normalize::telephone(parent::getTel());

        if (!is_null($tel) && (strlen($tel) > 0) && Default_Helpers_Phone::isMobile($tel)) {
            return $tel;
        }

        // restaurant has no tel number, so get the tel of billing contact person
        try {
            $billcontact = new Yourdelivery_Model_Contact($this->getBillingContactId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

        }

        if (!is_null($billcontact)) {
            $billcontactTel = Default_Helpers_Normalize::telephone($billcontact->getTel());
        }
            
        if ((strlen($billcontactTel) > 0) && Default_Helpers_Phone::isMobile($billcontactTel)) {
            return $billcontactTel;
        }
        
        // restaurant has no tel number and billing contact person has no tel, so get the tel of contact person
        try {
            $contact = new Yourdelivery_Model_Contact($this->getContactId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }

        if (!is_null($contact)) {
            $contactTel = Default_Helpers_Normalize::telephone($contact->getTel());
        }
        
        if ((strlen($contactTel) > 0) && Default_Helpers_Phone::isMobile($contactTel) ) {
            return $contactTel;
        }

        return null;
    }    

    /**
     * Returns email of billing contact if available, else - the email of restaurant
     * @author alex
     * @since 24.10.2011
     */
    public function getBillingEmail() {
        // restaurant has no email, so get the email of billing contact person
        try {
            $billcontact = new Yourdelivery_Model_Contact($this->getBillingContactId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

        }

        if (!is_null($billcontact) && strlen($billcontact->getEmail()) > 0) {
            return $billcontact->getEmail();
        }

        return $this->getEmail();
    }

    /**
     * get all meal categories servicetypes of this restaurant
     * @author alex
     * @since 12.10.2010
     */
    public function getMealCategoriesServicetypes() {
        return $this->getTable()->getMealCategoriesServicetypes();
    }

    /**
     * check if menu is marked as new. The last day when the menu is new is the change date + 6
     * i.e. if the menu was changed on 10.11.2010, it's still new on 16.11.2010, but not on 17.11.2010
     * @author alex
     * @since 16.12.2010
     * @return date in timestamp format. If returs null, then the menu is not new
     */
    public function getMenuIsNewUntil() {
        //so many days since change the menu is considered as new
        $days = 30;
        $seconds = $days * 24 * 60 * 60;

        // if no update time is defined
        if (intval($this->getMenuUpdateTime()) == 0) {
            return null;
        }

        // if the update time is older than the count of defined days
        if ((time() - $seconds) > strtotime($this->getMenuUpdateTime())) {
            return null;
        }

        return strtotime($this->getMenuUpdateTime()) + $seconds;
    }

    /**
     * gets all Billings of the restaurant filtered by $filter
     * @author alex
     * @since 22.12.2010
     * @param array $filter
     * @return splStorageObjects
     */
    public function getBillings($filter = null) {
        $billingTable = new Yourdelivery_Model_DbTable_Billing();

        $select = $billingTable->select()->where('mode="rest"')->where('refId =?', $this->getId());

        if (!empty($filter['year'])) {
            $select->where('YEAR(until) = ?', $filter['year']);
        }

        $all = $billingTable->fetchAll($select);
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
     * Get all service types og this restaurant
     * @author alex
     * @since 12.01.2011
     * @return array
     */
    public function getAllServiceTypes() {
        $types = array();

        foreach ($this->getTable()->getAllServiceTypes() as $st) {
            $types[] = $st['servicetypeId'];
        }

        return $types;
    }

    /**
     * how many meal categories of certain type are in this restaurant
     * @author alex
     * @since 09.02.2011
     */
    public function countMealCategoriesWithServiceType($serviceTypeId) {
        return $this->getTable()->countMealCategoriesWithServiceType($serviceTypeId);
    }

    /**
     * reset opening time - for tests to work correctly
     * @author alex
     * @since 23.02.2011
     */
    public function resetOpening() {
        $this->getTable()->resetOpening();
    }

    /**
     * delete restaurant, i.e. set deleted to 1
     * @author alex
     * @since 28.02.2011
     */
    public function delete() {
        //$this->getTable()->remove($service->getId());
        $this->setIsOnline(0);
        // set status to "gekündigt"
        $this->setStatus(11);
        $this->setDeleted(1);
        $this->save();
    }

    /**
     * get all available meal categories
     * @author alex
     * @return SplObjectStorage
     */
    public function getAdmins() {
        $admins = new SplObjectStorage();

        foreach ($this->getTable()->getAdmins() as $a) {
            $admins->attach(new Yourdelivery_Model_Customer($a['customerId']));
        }
        return $admins;
    }

    /**
     * Get sms printer if any
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.05.2012
     * @return Yourdelivery_Model_Printer_Abstract|null
     */
    public function getSmsPrinter() {

        if ($this->_printer instanceof Yourdelivery_Model_Printer_Abstract) {
            return $this->_printer;
        }

        $table = new Yourdelivery_Model_DbTable_Restaurant_Printer();
        $row = $table->findByRestaurantId($this->getId());

        if ($row && $row['id']) {
            try {
                return $this->_printer = Yourdelivery_Model_Printer_Abstract::factory($row['printerId'], $row['type']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }

        return null;
    }

    /**
     * TODO refactor for backend
     * get all restaurnts with certain offline status
     * @author alex
     * @since 14.07.2011
     * @return SplObjectStorage
     */
    public static function getAllByStatus($status) {
        $restaurants = new SplObjectStorage();

        foreach (Yourdelivery_Model_DbTable_Restaurant::getAllByStatus($status) as $r) {
            $restaurants->attach(new Yourdelivery_Model_Servicetype_Restaurant($r['id']));
        }

        return $restaurants;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 28.09.2011
     */
    public function getTicketComments() {

        $return = "";
        $comments = Yourdelivery_Model_DbTable_Restaurant_Notepad_Ticket::getAllCommentsOfToday($this->getId());


        foreach ($comments as $comment) {
            $return .= "<br />(" . substr($comment['time'], 11, 2) . ":" . substr($comment['time'], 14, 2) . ") " . $comment['name'] . ": " . $comment['comment'];
        }

        return $return;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.10.2011
     * @modified Aelx Vait <vait@lieferando.de>  09.12.2011
     * @param integer timestamp
     *
     * @return integer timestamp
     */
    public function getNextDeliverTime($timestamp = null) {
        if (is_null($timestamp)) {
            $timestamp = time();
        }

        $curdate = date('Y-m-d', $timestamp);
        $curtime = date('H:i:s', $timestamp);

        $result = $this->getTable()->getNextDeliverTime($curdate, $curtime);

        if ($result['nextopening'] == 0 || is_null($result['nextopening'])) {
            return strtotime('01.01.2020 10 pm');
        }
        return $result['nextopening'];
    }

    /**
     * Get as timestamp
     * @author vpriem
     * @since 30.09.2011
     * @param int $time started time
     * @return int
     */
    public function getTopUntilAsTimestamp($time = null) {

        if ($time === null) {
            $time = time();
        }

        $ts = $this->getTopUntil();
        if (empty($ts)) {
            return $time;
        }

        $ts = strtotime($ts);
        if ($ts < $time) {
            return $time;
        }
        return $ts;
    }

    /**
     * check if this service uses the topseller
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function useTopseller() {
        $no_use = array(17029, 17028, 17027, 17026, 16888, 13466, 17030, 17820, 13251, 16105, 16106, 16107, 16108, 16109, 16110);
        return !in_array($this->getId(), $no_use) && !$this->isAvanti();
    }

    /**
     * @var Yourdelivery_Model_Servicetype_Franchise 
     */
    protected $_franchise = null;
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.07.2012
     * @return Yourdelivery_Model_Servicetype_Franchise|null 
     */
    public function getFranchise() {
        
        if ($this->_franchise instanceof Yourdelivery_Model_Servicetype_Franchise) {
            return $this->_franchise;
        }
        
        $franchiseId = $this->getFranchiseTypeId();
        if ($franchiseId) {
            try {
                return $this->_franchise = Yourdelivery_Model_Servicetype_Franchise::getInstance($franchiseId);
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
        }
        
        return null;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.07.2012
     * @param string $name
     * @return boolean
     */
    public function hasFranchise($name = null) {
        
        if ($name === null) {
            return $this->getFranchise() instanceof Yourdelivery_Model_Servicetype_Franchise;
        }
        
        $franchise = $this->getFranchise();
        if ($franchise instanceof Yourdelivery_Model_Servicetype_Franchise) {
            return $franchise->getName() == $name;
        }
        
        return false;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.07.2012
     * @return boolean
     */
    public function isAvanti() {
        
        return $this->hasFranchise("Pizza AVANTI");
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.01.2012
     */
    public function getBasefee() {
        $basis = $this->_data['basefee'];
        $interval = $this->getBillInterval();
        switch ($interval) {
            case 0:
                return $basis;
            case 1:
                return round($basis / 2);
            case 2:
                return round($basis / 30);
        }
    }

    /**
     * clear locally saved variables, is needed for testcases
     * @author Alex Vait <vait@lieferando.de>
     */
    public function clearCachedVars() {

        $this->_ranges = array();
        $this->_printer = null;
    }

    /**
     * add all plz deliver ranges, which start with the specified string
     * @author Alex Vait <vait@lieferando.de>
     * @since 05.01.2012
     * @param string $plzprefix - search for plz, starting with this string and add them
     * @param int $mincost
     * @param int $delcost
     * @param int $deltime
     * @return boolean
     */
    public function createPlzByPrefixRange($plzprefix, $mincost, $delcost, $deltime, $noDeliverCostAbove) {
        if (empty($plzprefix) || empty($deltime)) {
            return false;
        }

        $cities = Yourdelivery_Model_City::getPlzByPrefix($plzprefix);

        if (!$cities) {
            return false;
        }

        foreach ($cities as $row) {
            $this->createLocation($row['id'], $mincost, $delcost, $deltime, $noDeliverCostAbove);
        }

        return true;
    }

    /**
     * get the transaction cost for this service
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     */
    public function getTransactionCost($payment, $amount) {
        $chargeStartTime = strtotime($this->getChargeStart());
        if ($chargeStartTime > time()) {
            return 0;
        }
        if ($payment == 'bar' || !in_array($payment, array('bar', 'credit', 'paypal', 'ebanking'))) {
            return 0;
        }

        return $this->getChargeFix() + (($amount / 100) * $this->getChargePercentage());
    }

    /**
     * set the flag for onlycash and check consistency with paymentbar
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @param boolean $onlycash
     */
    public function setOnlycash($onlycash) {
        $this->_data['onlycash'] = (boolean) $onlycash;
        if ($onlycash && !$this->_data['paymentbar']) {
            $this->logger->warn('setting payment bar to true, since this restaurant #%s only accepts bar now', $this->getId());
            $this->_data['paymentbar'] = true;
        }
    }

    /**
     * set the flag for paymentbar and check consistency with onlycash
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @param boolean $paymentbar
     */
    public function setPaymentbar($paymentbar) {
        $this->_data['paymentbar'] = (boolean) $paymentbar;
        if (!$paymentbar && $this->_data['onlycash']) {
            $this->logger->warn('setting onlycash to false, since this restaurant #%s only accepts bar now', $this->getId());
            $this->_data['onlycash'] = false;
        }
    }

    /**
     * check if service has holiday at this day
     * @author alex
     * @since 02.12.2010
     * @param $date - date in sql format, e.g '2010-12-02'
     * @return boolean
     */
    public function holidayAtDate($date) {
        return $this->getTable()->holidayAtDate($date);
    }

    /**
     * Build PHP Cache Files with contents from Url History
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function buildRedirectCache() {

        $links = Yourdelivery_Model_DbTable_Restaurant_UrlHistory::findByRestaurantId($this->getId());

        if (is_null($links)) {
            return;
        }

        foreach ($links as $link) {
            if (!empty($link['mode']) || !empty($link['url'])) {
                $filedir = APPLICATION_PATH . "/../public/cache/html/" . HOSTNAME . "/";

                if (!is_dir($filedir)) {
                    mkdir($filedir, 0777, true);
                }
                $filename = $filedir . $link['url'] . ".php";
                $redirectUrl = $this->getDirectLink($link['mode']);

                file_put_contents(
                        $filename, "<?php header('Location: http://" . HOSTNAME . "/" . $redirectUrl . "', true, 301);"
                );
            }
        }
    }

    /**
     * Add payment for this service
     *
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.08.2012
     * @param Yourdelivery_Model_Servicetype_Payment $payment
     * @return boolean
     */
    public function addPayment(Yourdelivery_Model_Servicetype_Payment $payment) {
        
        if (!$this->getId()) {
            return false;
        }
        
        $payment->setRestaurantId($this->getId());
        $payment->save();
        
        if (is_array($this->_payments)) {
            $this->_payments[] = $payment;
        }
        
        return true;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.08.2012
     * @return boolean
     */
    public function removePayments() {
        
        if (!$this->getId()) {
            return false;
        }
        
        $this->_payments = null;
        
        $rows = $this->getTable()
                     ->getCurrent()
                     ->findDependentRowset("Yourdelivery_Model_DbTable_Restaurant_Payments");
        
        $n = 0;
        foreach ($rows as $row) {
            $n += $row->delete();
        }
        
        return (boolean) $n;
    } 
    
    /**
     * Get payments for this service
     *
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.08.2012
     * @return Yourdelivery_Model_Servicetype_Payment[]
     */
    public function getPayments() {
        
        if (is_array($this->_payments)) {
            return $this->_payments;
        }
        $this->_payments = array();

        $rows = $this->getTable()
                     ->getCurrent()
                     ->findDependentRowset('Yourdelivery_Model_DbTable_Restaurant_Payments');
        foreach ($rows as $row) {
            try {
                $this->_payments[] = new Yourdelivery_Model_Servicetype_Payment($row->id);
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
        }
        
        return $this->_payments;
    }

    /**
     * Get default payment
     *
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.08.2012
     * @return string
     */
    public function getDefaultPayment() {
        
        $payments = $this->getPayments();
        foreach ($payments as $payment) {
            if ($payment->getDefault()) {
                return $payment->getPayment();
            }
        }
        
        return "";
    }

    /**
     * Check if a payment is allowed
     *
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.03.2012
     * @param string $paymentName
     * @return boolean
     */
    public function isPaymentAllowed($paymentName) {
        
        if (!isset($this->config->payment->$paymentName)) {
            return false;
        }
        if (!$this->config->payment->$paymentName->enabled) {
            return false;
        }
        
        $payments = $this->getPayments();
        foreach ($payments as $payment) {
            if ($payment->getPayment() == $paymentName) {
                return $payment->getStatus() == 1;
            }
        }
        
        // by default read config if payment is allowed
        return (boolean) $this->config->payment->$paymentName->allowed;
    }

    /**
     * Check if the notify is sms
     *
     * @return boolean
     */
    public function hasSmsNotify() {

        return in_array($this->getNotify(), array("sms", "smsemail"));
    }

    /**
     * Get a score for sorting on the service page in frontend
     * @author vpriem
     * @since 30.09.2011
     * @param int $startTime start time
     * @return int
     */
    public function getSortingScore($startTime = null) {

        $score = 0;

        if ($startTime === null) {
            $startTime = time();
        }

        $topTime = $this->getTopUntil();
        if ($this->isNew()) {
            $score += 15;
        } elseif (!empty($topTime)) {
            $topTime = strtotime($topTime);
            if ($topTime > $startTime) {
                $score += 15;
            }
        }

        switch ($this->getNotify()) {
            case "sms":
            case "smsemail":
                $score += 10;
                break;
        }

        return $score;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.05.2012
     * @return Yourdelivery_Model_Servicetype_Openings
     */
    public function getOpening(){
        return Yourdelivery_Model_Servicetype_Openings::getInstance($this);
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.06.2012
     * @return Yourdelivery_Model_Servicetype_Rating_Service 
     */
    public function getRating(){
        return Yourdelivery_Model_Servicetype_Rating_Service::getInstance($this);
    }
    
    /**
     * @author Mattthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     * @return \Yourdelivery_Model_Servicetype_Partner
     */
    public function getPartnerData(){
        if ( $this->_partnerData === null ){
            $this->_partnerData = new Yourdelivery_Model_Servicetype_Partner(null, $this->getId());
            $this->_partnerData->setRestaurantId($this->getId());
        }
        return $this->_partnerData;
        
    }
    
    /**
     * Returns email of restaurant in the partner table if available, else - the email of the restaurant as defined in getEmail()
     * @author Alex Vait
     * @since 01.08.2012
     */
    public function getPartnerEmail() {
        $partnerData = $this->getPartnerData();

        $email = null;
        
        // if email is defined in the partner table, use it. If not - take the email of the restaurant
        if (is_null($partnerData) || (strlen($partnerData->getEmail())==0)) {
            $email = $this->getEmail();
        }
        else {
            $email = $partnerData->getEmail();                        
        }
        
        return $email;
    }
    
    /**
     * Returns mobile phone number of restaurant in the partner table if available, else - the mobile number of the restaurant as defined in getMobile()
     * @author Alex Vait
     * @since 01.08.2012
     */
    public function getPartnerMobile() {
        $partnerData = $this->getPartnerData();

        $mobile = null;
        
        // if mobile phone number is defined in the partner table, use it. If not - take the mobile phone of the restaurant
        if (is_null($partnerData) || (strlen($partnerData->getMobile())==0)) {
            $mobile = $this->getMobile();
        }
        else {
            $mobile = $partnerData->getMobile();                        
        }
        
        return $mobile;
    }
    
    /**
     * Sends temporary password to the partner restaurant per email or per sms
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.08.2012
     * @param $type string 'email' oder 'mobile'
     * @param $typeValue string value of email or mobile phone number
     * @return boolean success of failure while sending password
     */
    public function sendPartnerTemporaryPassword($type, $typeValue, $saveNewPartnerData = false) {

        // generate password
        $password = Default_Helpers_Password::generatePassword(8, null, 2, 2, 2);
        
        // if no entry is set yet in 'partner_restaurants' table, create one
        $partnerData = $this->getPartnerData();
        if (is_null($partnerData) || ($partnerData->getId()==0)) {
            $partnerData = new Yourdelivery_Model_Servicetype_Partner();
            $partnerData->setRestaurantId($this->getId());
        }            
        
        switch ($type) {
            case 'email':

                $email = $typeValue;
                
                // assemble the email
                $emailSubject = __b("Lieferando - Ihr temporäres Passwort fürs Partner Backend");
                $emailBody = __b("Sehr geehrter Restaurant-Partner,                        

Ihr temporäres Passwort fürs Lieferando Partner Backend lautet: %s

Mit freundlichen Grüßen,
Ihr Lieferando Team

lieferando
Chausseestr. 86
10115 Berlin

Website http://www.lieferando.de
Blog http://blog.lieferando.de
Twitter www.twitter.com/lieferando

Amtsgericht Berlin-Charlottenburg: HRB 118099 B
Geschäftsführer: Christoph Gerber, Jörg Gerbig, Kai Hansen", $password);                    

                $emailSender = new Yourdelivery_Sender_Email();
                $emailSender->setBodyText($emailBody);
                $emailSender->setSubject($emailSubject);
                $emailSender->addTo(IS_PRODUCTION ? $email : $this->config->testing->email);
                $state = $emailSender->send();                
                
                // sending email failed
                if (!$state) {
                    return false;
                }
                
                if ($saveNewPartnerData) {
                    // email was send, so if we have no partner data yet or if email is empty, set it now
                    if (strlen($partnerData->getEmail()) == 0) {
                        $partnerData->setEmail($email);
                    }                    
                }

                // set temporary password
                $partnerData->setTemporarypassword(md5($password));
                // save the time when the password was send
                $partnerData->setTemporarypasswordsend(date('Y-m-d H:i:s'));
                $partnerData->save();

                $this->logger->adminInfo(sprintf("Temporary password was send for the restaurant #%s to the email %s", $this->getId(), $email));                
                
                break;

            case 'mobile':
                
                $mobile = $typeValue;
                
                // send sms with temporary pasword
                $sms = new Yourdelivery_Sender_Sms();
                $state = $sms->send($mobile, __b('Ihr temporäres Passwort fürs Lieferando Partner Backend lautet: %s', $password));

                // sending sms failed
                if (!$state) {
                    return false;
                }
                
                
                if ($saveNewPartnerData) {
                    // if we have no partner data yet or if mobile number is empty, set it now
                    if (strlen($partnerData->getMobile()) == 0) {
                        $partnerData->setMobile($mobile);
                    }                    
                }

                // set temporary password
                $partnerData->setTemporarypassword(md5($password));
                // save the time when the password was send
                $partnerData->setTemporarypasswordsend(date('Y-m-d H:i:s'));
                $partnerData->save();

                $this->logger->adminInfo(sprintf("Temporary password was send for the restaurant #%s to the mobile number %s", $this->getId(), $mobile));
                
                break;

            default:
                $this->logger->adminInfo(sprintf("Undefined type was given on sending temporary password for the restaurant #%s: %s", $this->getId(), $type));
                return false;
        }
        
        return true;        
    }   
    
    /**
     * get salted has to identify service;
     * @author mlaug
     * @since 28.08.2012
     * @return string
     */
    public function getSalt() {
        return md5($this->getId() . SALT);
    }
}
