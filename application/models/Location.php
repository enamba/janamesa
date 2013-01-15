<?php

/**
 * Model Location
 * @author vpriem
 * @package location
 * @since 10.08.2010
 */
class Yourdelivery_Model_Location extends Default_Model_Base {

    /**
     * Latitude Longitude
     * @author vpriem
     * @var array
     */
    private $_latlng = null;

    /**
     * City
     * @author vpriem
     * @var Yourdelivery_Model_City
     */
    private $_city = null;

    /**
     * Region
     * @author Jens Naie <naie@lieferando.de>
     * @var Zend_Db_Table_Row
     */
    private $_region = null;

    /**
     * District
     * @author Jens Naie <naie@lieferando.de>
     * @var Zend_Db_Table_Row
     */
    private $_district = null;

    /**
     * all best services
     * @var array
     */
    private $_best = null;

    /**
     * Save
     * @author vpriem
     * @since 21.03.2011
     * @return boolean|int
     */
    public function save() {

        try {
            $this->setPlz($this->getCity()->getPlz());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }

        return parent::save();
    }

    /**
     * Get longitude from coordinate
     * @author mlaug
     * @since 01.08.2010
     * @return float
     */
    public function getLongitude() {

        list($lat, $lng) = $this->getLatLng();
        return $lng;
    }

    public function getLng() {
        return $this->getLongitude();
    }

    /**
     * Get latitude from coordinat
     * @author mlaug
     * @since 01.08.2010
     * @return float
     */
    public function getLatitude() {

        list($lat, $lng) = $this->getLatLng();
        return $lat;
    }

    public function getLat() {
        return $this->getLatitude();
    }

    /**
     * Get coordinates via google maps
     * @author vpriem
     * @since 10.08.2010
     * @return array
     */
    public function getLatLng() {

        // retrieve coords if defined
        if ($this->_latlng !== null) {
            return $this->_latlng;
        }

        // get lat lng
        $lat = $this->_data['latitude'];
        $lng = $this->_data['longitude'];
        $district = null;

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
     * Get district via google maps
     * @author enamba
     * @since 14.01.2013
     * @return string
     */
    public function getDistrictByGoogleMaps() {

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
            return $geo->getDistrict();
        } else {
            return '';
        }
    }

    /**
     * @author mlaug
     * @since 09.03.2011
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
     * Get ort object of location
     * @author mlaug
     * @since 01.08.2010
     * @return Yourdelivery_Model_City
     */
    public function getOrt() {
        try {
            return $this->getCity();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
    }

    /**
     * @author mlaug
     * @since 09.03.2011
     * @return string
     */
    public function getPlz() {
        try {
            return $this->getCity()->getPlz();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
    }

    /**
     * Get address
     * @author vpriem
     * @since 15.03.2010
     * @return string
     */
    public function getAddress() {
        $address = array();
        $address[] = $this->getPlz();
        $address[] = $this->getOrt()->getOrt();
        $address[] = $this->getStreet();
        $address[] = $this->getHausnr();
        return implode(" ", $address);
    }

    /**
     * Get company, etage and comment in one string
     * @author vpriem
     * @since 15.03.2011
     * @return string
     */
    public function getAddition() {

        $addition = array();

        $company = $this->getCompanyName();
        if (!empty($company)) {
            $addition[] = $company;
        }

        $etage = $this->getEtage();
        if (!empty($etage)) {
            $addition[] = $etage;
        }

        $comment = $this->getComment();
        if (!empty($comment)) {
            $addition[] = $comment;
        }

        return implode(", ", $addition);
    }

    /**
     * Get associated table
     * @author vpriem
     * @since 15.03.2011
     * @return Yourdelivery_Model_DbTable_Locations
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Locations();
        }
        return $this->_table;
    }

    /**
     * Remove
     * @author vpriem
     * @since 16.03.2011
     * @return boolean
     */
    public function remove() {

        // remove company_location-relation
        $budgets = $this->getBudgets();
        if (!is_null($budgets) && $budgets->count() > 0) {
            foreach ($budgets as $budget) {

                $budget->removeLocation($this->getId());
            }
        }

        return (boolean) Yourdelivery_Model_DbTable_Locations::remove($this->getId());
    }

    /**
     * get associated budgets for location
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.03.2011
     * @return SplObjectStorage
     */
    public function getBudgets() {
        if (is_null($this->getId())) {
            return null;
        }

        $table = new Yourdelivery_Model_DbTable_Company_Locations();
        $relationRows = $table->fetchAll(sprintf('locationId = %d', $this->getId()));

        $budgets = new SplObjectStorage();

        foreach ($relationRows as $relationRow) {
            try {
                $budgets->attach(new Yourdelivery_Model_Budget($relationRow['budgetId']));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->_logger->err(sprintf('Could not create budget #%d', $relationRow['budgetId']));
                continue;
            }
        }

        return $budgets;
    }

    /**
     * get $count best services based on current rating
     * @author mlaug
     * @since 23.08.2011
     * @param integer $count
     * @return SplObjectStorage
     */
    public function getBestServices($count = 3, $asArray = false) {

        if (!is_array($this->_best)) {
            $this->_best = array(array());
        }

        if (is_array($this->_best) && isset($this->_best[$count][$asArray])) {
            return $this->_best[$count][$asArray];
        }

        $hash = md5('bestservices' . $count . $this->getPlz());
        $result = Default_Helpers_Cache::load($hash);

        if (!$result) {
            $result = $this->getTable()->getBestServices($count, $this->getPlz());
            Default_Helpers_Cache::store($hash, $result);
        }

        if ($asArray) {
            return $result;
        }

        $services = new SplObjectStorage();
        foreach ($result as $data) {
            try {
                $services->attach(new Yourdelivery_Model_Servicetype_Restaurant($data['restaurantId']));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                // mh ...
            }
        }

        $this->_best[$count][$asArray] = $services;
        return $services;
    }

    /**
     * get a company name from company object, if no company name is set
     * in this location
     * @author mlaug
     * @since 27.09.2011
     * @return string
     */
    public function getCompanyName() {
        $name = $this->_data['companyName'];
        if (strlen($name) == 0 && $this->getCompany() instanceof Yourdelivery_Model_Company) {
            return $this->getCompany()->getName();
        }
        return $name;
    }

    /**
     * we need to mark all others as NOT primary
     * if we want this one to be
     * @author mlaug
     * @since 10.11.2011
     */
    public function setPrimary($primary) {
        $primary = (boolean) $primary;
        if ($primary) {
            $this->getTable()->resetPrimaryAddress($this->getCustomerId());
        }
        $this->_data['primary'] = $primary;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 13.12.2011
     *
     * @todo implement
     * @see http://ticket.yourdelivery.local/browse/YD-557
     */
    public function getCountOrders() {
        return 0;
    }

    /**
     * @author Jens Naie <naie@lieferando.de>
     * @since 10.06.2012
     * @return string
     */
    public function getHausnr($stripAppartment = false) {
        if ($stripAppartment === false) {
            return $this->_data['hausnr'];
        } else {
            $strips = preg_match('/(.+)\/(.+)/', $this->_data['hausnr'], $matches);
            if (count($matches) == 3) {
                return $matches[1];
            }
            return $this->_data['hausnr'];
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.05.2012
     * @return string
     */
    public function getAppartment() {
        $strips = preg_match('/(.+)\/(.+)/', $this->_data['hausnr'], $matches);
        if (count($matches) == 3) {
            return $matches[2];
        }
        return "";
    }

    /**
     * Get current regions 1 - 4
     * @author Jens Naie <naie@lieferando.de>
     * @since 16.07.2012
     * @return array
     */
    public function getRegion() {
        if (($regionId = $this->getCity()->getRegionId())) {
            $regionTable = new Yourdelivery_Model_DbTable_Regions();
            $region = $regionTable->find($regionId);
            return $this->_region = $region[0];
        } else {
            return null;
        }
    }

    /**
     * Get current district
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     * @return array
     */
    public function getDistrict() {
        if (($districtId = $this->getCity()->getDistrictId())) {
            $districtTable = new Yourdelivery_Model_DbTable_Districts();
            $district = $districtTable->find($districtId);
            return $this->_district = $district[0];
        } else {
            return null;
        }
    }

    /**
     * Get other districts within the same region or city
     * @author Jens Naie <naie@lieferando.de>
     * @since 16.07.2012
     * @return array
     */
    public function getNearAreas($limit = 50) {
        if (!$this->_region) {
            $this->getRegion();
        }
        if ($this->_region) {
            $districtTable = new Yourdelivery_Model_DbTable_Districts();
            $select = $districtTable->select()
                    ->where('regionId = ?', $this->_region->id)
                    ->where('used = 1')
                    ->order(new Zend_Db_Expr('RAND()'))
                    ->limit($limit);
            return $districtTable->fetchAll($select);
        } else {
            return null;
        }
    }

    /**
     * Get other plzs within the same region or city
     * @author Jens Naie <naie@lieferando.de>
     * @since 16.07.2012
     * @return array
     */
    public function getNearPlzs($limit = 50) {
        if (!$this->_region) {
            $this->getRegion();
        }
        if ($this->_region) {
            $cityTable = new Yourdelivery_Model_DbTable_City();
            $select = $cityTable->select()
                    ->where('regionId = ?', $this->_region->id)
                    ->order(new Zend_Db_Expr('RAND()'))
                    ->limit($limit);
            return $cityTable->fetchAll($select);
        } else {
            return null;
        }
    }

    /**
     * Get route detail ('region', 'district', 'city' or 'plz')
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     * @return string
     */
    public function getDepth() {
        $ctrl = Zend_Controller_Front::getInstance();
        $routeName = $ctrl->getRouter()->getCurrentRouteName();
        switch ($routeName) {
            default: return null;
            case 'listPlzServices': return 'plz';
            case 'listDistrictServices': return 'district';
            case 'listRegionServices': return 'region';
            case 'listCityServices': return 'city';
        }
    }

    /**
     * Get location title depending on the depth ('region', 'district', 'city' or 'plz')
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     * @return string
     */
    public function getTitle($showRichSnippets = false) {
        switch ($this->getDepth()) {
            default: return '';
            case 'plz':
                if ($showRichSnippets) {
                    return sprintf('<span itemprop="postalCode">%s</span> <span itemprop="addressLocality">%s</span>', $this->getPLz(), $this->getCity()->getCity());
                } else {
                    return sprintf('%s %s', $this->getPLz(), $this->getCity()->getCity());
                }
            case 'district':
                if ($showRichSnippets) {
                    if ($this->getDistrict()->district2) {
                        return sprintf('<span itemprop="addressRegion">%s</span> <span itemprop="addressLocality">%s</span>', $this->getDistrict()->district1, $this->getDistrict()->district2);
                    } else {
                        return sprintf('<span itemprop="addressLocality">%s</span>', $this->getDistrict()->district1);
                    }
                } else {
                    return sprintf('%s %s', $this->getDistrict()->district1, $this->getDistrict()->district2);
                }
            case 'region':
                if ($showRichSnippets) {
                    return sprintf('<span itemprop="addressRegion">%s</span>', $this->getRegion()->region3);
                } else {
                    return $this->getRegion()->region3;
                }
            case 'city':
                if ($showRichSnippets) {
                    return sprintf('<span itemprop="addressLocality">%s</span>', $this->getCity()->getCity());
                } else {
                    return $this->getCity()->getCity();
                }
        }
    }

    /**
     * Get last ratings of location
     *
     * @param integer $limit
     *
     * @return array
     *
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     *
     */
    public function getRatings($limit = null) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->from(array('ol' => 'orders_location'), array())
                ->distinct()
                ->where('ol.cityId IN (?)', $this->getCityId())
                ->join(array('rr' => 'restaurant_ratings'), 'ol.orderId=rr.orderId', array('author', 'comment', 'created'))
                ->join(array('r' => 'restaurants'), 'rr.restaurantId=r.id', array('restUrl', 'name'))
                ->where('rr.status = 1')
                ->where('LENGTH(rr.comment) > 10')
                ->where('LENGTH(rr.author) > 3')
                ->order('rr.created DESC')
                ->limit($limit);

        $result = $db->fetchAll($select);
        return $result;
    }

    /**
     * Get aggregate overall rating of location
     *
     * @return array
     *
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     *
     */
    public function getAggregateRating() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->from(array('ol' => 'orders_location'), array())
                ->distinct()
                ->where('ol.cityId IN (?)', $this->getCityId())
                ->join(array('rr' => 'restaurant_ratings'), 'ol.orderId=rr.orderId', array('points' => new Zend_Db_Expr('AVG((rr.quality+rr.delivery)/2)'),
                    'count' => new Zend_Db_Expr('COUNT(*)')))
                ->join(array('r' => 'restaurants'), 'rr.restaurantId=r.id', array('restUrl', 'name'));

        $result = $db->fetchRow($select);
        return $result;
    }

    /**
     * Get other regions
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     * @return array
     */
    public function getOtherRegions($limitImportant = 4, $limitOther = 11) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select1 = $db->select()
                ->from(array('r1' => 'regions'))
                ->where('r1.used = 1')
                ->where('r1.important = 1')
                ->order('RAND()')
                ->limit($limitImportant);
        if (($regionId = $this->getCity()->getRegionId())) {
            $select1->where('r1.id != ?', $regionId);
        }
        $select2 = $db->select()
                ->from(array('r2' => 'regions'))
                ->where('r2.used = 1')
                ->where('r2.important = 0')
                ->order('RAND()')
                ->limit($limitOther);
        if (($regionId = $this->getCity()->getRegionId())) {
            $select2->where('r2.id != ?', $regionId);
        }

        // Workaround for limit bug (http://framework.zend.com/issues/browse/ZF-4338)
        $select = $db->select()->union(array('(' . $select1 . ')', '(' . $select2 . ')'));

        return $db->fetchAll($select);
    }

    /**
     * Get seo text depending on the depth ('region', 'district', 'city' or 'plz')
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     * @return array
     */
    public function getSeoText() {
        switch ($this->getDepth()) {
            default: return '';
            case 'district': return array('headline' => $this->getDistrict()->seoHeadline, 'text' => $this->getDistrict()->seoText);
            case 'region': return array('headline' => $this->getRegion()->seoHeadline, 'text' => $this->getRegion()->seoText);
            case 'plz':
            case 'city': return array('headline' => $this->getCity()->getSeoHeadline(), 'text' => $this->getCity()->getSeoText());
        }
    }

    /**
     * Get geo coordinates depending on the depth ('region', 'district', 'city' or 'plz')
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     * @return array
     */
    public function getGeoCoordinates() {
        switch ($this->getDepth()) {
            default: return '';
            case 'district': return array('lat' => $this->getDistrict()->lat, 'lng' => $this->getDistrict()->lng);
            case 'region': return array('lat' => $this->getRegion()->lat, 'lng' => $this->getRegion()->lng);
            case 'plz':
            case 'city': return array('lat' => $this->getCity()->getLat(), 'lng' => $this->getCity()->getLng());
        }
    }

}
