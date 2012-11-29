<?php

/**
 * Courier Model
 * @author mlaug
 * @package service
 * @subpackage courier
 * @since 01.08.2010
 */
class Yourdelivery_Model_Courier extends Default_Model_Base {

    /**
     * @var Yourdelivery_Model_ServicetypeAbstract
     */
    protected $_currentService = null;
    /**
     * @var Yourdelivery_Model_Location
     */
    protected $_currentLocation = null;

    /**
     * Serialize object
     * @author mlaug, vpriem
     * @since 01.08.2010
     * @return array
     */
    public function __sleep() {

        $default = parent::__sleep();
        $default[] = "_currentService";
        $default[] = "_currentLocation";
        return $default;
    }

    /**
     * @author vpriem
     * @since 03.14.2010
     * @return SplObjectStorage
     */
    public static function all() {
        $db = Zend_Registry::get('dbAdapter');

        $spl = new SplObjectStorage();
        $couriers = $db->fetchAll("SELECT `id` FROM `courier`");
        foreach ($couriers as $c) {
            try {
                $spl->attach(new Yourdelivery_Model_Courier($c['id']));
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
        }
        return $spl;
    }

    /**
     * Get courier list for the data grid
     * @author vpriem
     * @since 01.08.2010
     * @return Zend_Db_Select
     */
    public static function getGrid() {

        // get db
        $db = Zend_Registry::get('dbAdapter');

        //
        return $db
                ->select()
                ->from(array('c' => 'courier'), array(
                    'ID' => 'id',
                    'Registriert' => 'created',
                    'Geändert' => 'updated',
                    'Name' => 'name',
                    'Adresse' => new Zend_Db_Expr("CONCAT (c.street, ' ', c.hausnr)"),
                    'eMail' => 'email',
                    'api',
                ))
                ->order('c.id DESC');
    }

    /**
     * Get all restaurants
     * @author vpriem
     * @since 14.03.2011
     * @return SplObjectStorage
     */
    public function getRestaurants() {

        $spl = new SplObjectStorage();
        $restaurants = $this->getTable()->getRestaurants();
        foreach ($restaurants as $r) {
            try {
                $spl->attach(new Yourdelivery_Model_Servicetype_Restaurant($r['restaurantId']));
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
        }
        return $spl;
    }

    /**
     * Remove
     * @author vpriem
     * @return int
     */
    public function remove() {

        // get db
        $db = Zend_Registry::get('dbAdapter');

        $rows = $db->delete('courier', 'id = ' . ((integer) $this->getId()));
        if ($rows) {
            $db->delete('courier_restaurant', 'courierId = ' . ((integer) $this->getId()));
            $db->delete('courier_location', 'courierId = ' . ((integer) $this->getId()));
            $db->delete('courier_costmodel', 'courierId = ' . ((integer) $this->getId()));
        }
        return $rows;
    }

    /**
     * Remove location
     * @author vpriem
     * @since 09.08.2010
     * @param int $locationId
     * @return boolean
     */
    public function removeLocation($locationId) {

        // get db
        $db = Zend_Registry::get('dbAdapter');

        return (boolean) $db->delete('courier_location', 'courierId = ' . ((integer) $this->getId()) . ' AND id = ' . ((integer) $locationId));
    }

    /**
     * Remove costmodel
     * @author vpriem
     * @since 27.08.2010
     * @param int $modelId
     * @return boolean
     */
    public function removeCostmodel($modelId) {

        // get db
        $db = Zend_Registry::get('dbAdapter');

        return (boolean) $db->delete('courier_costmodel', 'courierId = ' . ((integer) $this->getId()) . ' AND id = ' . ((integer) $modelId));
    }

    /**
     * Set current service
     * @author mlaug
     * @since 01.08.2010
     * @param Yourdelivery_Model_Servicetype_Abstract $service
     * @return boolean
     */
    public function setCurrentService($service = null) {

        if ($service === null) {
            return false;
        }
        if (!($service instanceof Yourdelivery_Model_Servicetype_Abstract)) {
            return false;
        }
        $this->_currentService = $service;
        return true;
    }

    /**
     * Get current service
     * @author mlaug
     * @since 01.08.2010
     * @return Yourdelivery_Model_ServicetypeAbstract
     */
    public function getCurrentService() {

        return $this->_currentService;
    }

    /**
     * Set current location
     * @author mlaug
     * @since 01.08.2010
     * @param mixed Yourdelivery_Model_Location $location
     * @return boolean
     */
    public function setCurrentLocation($location = null) {

        if ($location === null) {
            return false;
        }
        if (!($location instanceof Yourdelivery_Model_Location)) {
            return false;
        }
        $this->_currentLocation = $location;
        return true;
    }

    /**
     * Get current location
     * @author mlaug
     * @since 01.08.2010
     * @return Yourdelivery_Model_Location
     */
    public function getCurrentLocation() {

        return $this->_currentLocation;
    }

    /**
     * Calculate range
     * @author mlaug
     * @since 01.08.2010, 10.08.2010 (vpriem)
     * @return int
     */
    public function calculateRange() {

        $service = $this->getCurrentService();
        $location = $this->getCurrentLocation();

        if ($service === null || $location === null) {
            return 0;
        }

        $lon1 = $service->getLongitude();
        $lat1 = $service->getLatitude();

        $lon2 = $location->getLongitude();
        $lat2 = $location->getLatitude();

        return Default_Api_Google_Geocoding::distance($lon1, $lat1, $lon2, $lat2);
    }

    /**
     * Get deliver time
     * @author mlaug
     * @since 01.08.2010, 29.09.2010 (vpriem)
     * @param string $cityId
     * @return int
     */
    public function getDeliverTime($cityId = null) {

        if ($cityId === null) {
            $location = $this->getCurrentLocation();
            if ($location === null) {
                return 0;
            }
            $cityId = $location->getCityId();
        }

        return $this->getTable()
            ->getDeliverTime($cityId) * 60;
    }

    /**
     * Get deliver cost
     * @author mlaug
     * @since 01.08.2010
     * @param $cityId
     * @return int
     */
    public function getDeliverCost ($cityId = null) {

        if ($cityId === null) {
            $location = $this->getCurrentLocation();
            if ($location === null) {
                return 0;
            }
            $cityId = $location->getCityId();
        }

        $cost = $this->getTable()
            ->getDeliverCost($cityId);

        return round($cost / 10) * 10;
    }

    /**
     * Get discount from this courier. Sometime deliver costs are so 
     * high we need want it to be more attractive, so we create a static
     * discount for this courier. Some companys do not get any courier cost
     * so we check for them and remove it in total. Otherwise there is a fix of
     * 3 Euro currently
     * @author vpriem
     * @since 30.08.2010
     * @param int $total
     * @return int
     */
    public function getDiscount($total, $company = null, $service = null) {

        $subvention = $this->getSubvention();

        /**
         * HACK: for pulse and some companies,
         * that they get no deliver costs!
         */
        $companyMaxSubvention = array(1752);
        $serviceMaxSubvention = array(12139);

        if (is_object($company) && is_object($service)) {
            if (in_array($company->getId(), $companyMaxSubvention)) {
                if (in_array($service->getId(), $serviceMaxSubvention)) {
                    $subvention = 9999;
                }
            }
        }

        if ($subvention > 0) {
            if ($subvention == 9999) {
                return $this->getDeliverCost();
            }
            
            return 300;
        }

        return 0;
    }

    /**
     * Get associated table
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Courier
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Courier();
        }
        return $this->_table;
    }

    /**
     * Returns the currier Object of this service
     * @author mlaug
     * @return Yourdelivery_Model_Currier
     */
    public function getContact() {
        return new Yourdelivery_Model_Contact($this->getContactId());
    }

    /**
     * Get city
     * @author vpriem
     * @since 14.03.2011
     * @return Yourdelivery_Model_City
     */
    public function getCity() {

        $cid = $this->getCityId();
        try {
            return new Yourdelivery_Model_City($cid);
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
    }

    /**
     * Get city
     * @author vpriem
     * @since 14.03.2011
     * @return Yourdelivery_Model_City
     */
    public function getOrt() {

        return $this->getCity();

    }

    /**
     * get next bill based on given start and end point
     * @author mlaug
     * @param int $string
     * @param int $until
     * @return Yourdelivery_Model_Billing_Restaurant
     */
    public function getNextBill($from = 0, $until = 0, $test = false) {
        $mode = $this->getBillInterval();
        return new Yourdelivery_Model_Billing_Courier($this, $from, $until, $mode, $test);
    }

    /**
     * @author mlaug
     * @return array
     */
    public function getBillingCustomizedData() {

        //set defaults
        $default = array(
            'heading' => $this->getName(),
            'street' => $this->getStreet(),
            'hausnr' => $this->getHausnr(),
            'zHd' => null,
            'plz' => $this->getPlz(),
            'city' => (is_null($this->getOrt()) ? null : $this->getOrt()->getOrt()),
            'template' => 'standard',
        );

        $customized = array_merge($default, $this->getBillingCustomized()->getData());
        return $customized;
    }

    /**
     * @author mlaug
     * @return Yourdelivery_Model_Billing_Customized
     */
    public function getBillingCustomized() {
        $customized = new Yourdelivery_Model_Billing_Customized();
        $cid = $this->getTable()->getBillingCustomized();
        if ($cid === false) {
            $customized->setMode('courier');
        } else {
            $customized->load($cid['id']);
        }

        $customized->setCourier($this);
        $customized->setRefId($this->getId());

        return $customized;
    }

    /**
     * get commission of this service
     * @author mlaug
     * @return int
     */
    public function getCommission() {
        return $this->_data['komm'];
    }

    /**
     * gets all Billings of the courier filtered by $filter
     * @author alex
     * @since 22.12.2010
     * @param array $filter
     * @return splStorageObjects
     */
    public function getBillings($filter = null) {
        $billingTable = new Yourdelivery_Model_DbTable_Billing();
        $all = $billingTable->fetchAll('mode="courier" AND refId="' . $this->getId() . '"');
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
     * get a valid fax service, default is retarus
     * @author mlaug
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
     * Get all plz locations of this courier
     * @author vpriem
     * @since 10.03.2011
     * @return array
     */
    public function getRanges() {

        return $this->getTable()->getRanges();

    }

    /**
     * Get all plz locations of this courier
     * @author alex
     * @since 17.02.2011
     * @return array
     */
    public function getAllPlzs() {
        $plzs = array();

        $ranges = $this->getTable()->getRanges();
        foreach ($ranges as $range) {
            $plzs[] = $range['plz'];
        }

        return $plzs;
    }

    /**
     * Get all city id of this courier
     * @author alex
     * @since 09.03.2011
     * @return array
     */
    public function getAllCityId() {
        $cids = array();

        $ranges = $this->getTable()->getRanges();
        foreach ($ranges as $range) {
            $cids[] = $range['cityId'];
        }

        return $cids;
    }

    /**
     * Remove plz
     * @author vpriem
     * @since 31.08.2010
     * @param int $rangeId
     * @param int $cityId
     * @return boolean
     */
    public function removeRange($rangeId, $cityId = null) {

        try {
            // get city id
            if ($cityId === null) {
                $range = new Yourdelivery_Model_Courier_Plz($rangeId);
                $cityId = $range->getCityId();
            }

            // remove this plz from all associated restaurants
            $restaurants = $this->getRestaurants();
            foreach ($restaurants as $r) {
                $r->deleteRangeByCityId($cityId);
            }

        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return false;
        }

        return $this->getTable()->deleteRange($rangeId, $cityId);
    }

    /**
     * @author alex
     * @param int $cityId
     * @param int $deltime
     * @param int $delcost
     * @param int $mincost
     * @return boolean
     */
    public function addRange($cityId, $deltime = 0, $delcost = 0, $mincost = 0) {
        
        try {
            $city = new Yourdelivery_Model_City($cityId);
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->adminInfo(sprintf('cityId % was not found in db', $cityId));
            return false;
        }
        
        if ($city->getId() == 0 ) {
            $this->logger->adminInfo(sprintf('cityId % was not found in db', $cityId));
            return false;
        }

        if (in_array($cityId, $this->getAllCityId())) {
            $this->logger->adminInfo(sprintf('cityId %d is already added to the courier', $cityId, $this->getId()));
            return false;
        }

        $range = new Yourdelivery_Model_Courier_Plz();
        $range->setPlz($city->getPlz());
        $range->setCityId($city->getId());
        $range->setDeliverTime($deltime);
        $range->setDelcost(priceToInt2($delcost));
        $range->setMincost(priceToInt2($mincost));
        $range->setCourierId($this->getId());
        $range->save();

        //add this plz to all associated restaurants
        $restaurants = $this->getRestaurants();
        foreach ($restaurants as $r) {
            $r->createLocation($cityId, $mincost, 0, 30 * 60);
        }

        return true;
    }

    /**
     * Assign service
     * @author vpriem
     * @since 10.03.2011
     * @return boolean
     */
    public function addService (Yourdelivery_Model_Servicetype_Abstract $restaurant) {

        $courierId = $this->getId();
        if ($courierId === null) {
            return false;
        }

        return Yourdelivery_Model_Courier_Restaurant::add($courierId, $restaurant->getId());

    }

    /**
     * Remove service
     * @author vpriem
     * @since 10.03.2011
     * @return boolean
     */
    public function removeService (Yourdelivery_Model_Servicetype_Abstract $restaurant) {

        $courierId = $this->getId();
        if ($courierId === null) {
            return false;
        }

        return Yourdelivery_Model_Courier_Restaurant::delete($courierId, $restaurant->getId());

    }

}
