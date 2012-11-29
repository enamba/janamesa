<?php

/**
 * Description of Restaurant
 *
 * @author mlaug
 */
class Yourdelivery_Model_Servicetype_Restaurant extends Yourdelivery_Model_Servicetype_Abstract {

    /**
     * our service type is a restaurant
     * @var int
     */
    protected $_type = self::RESTAURANT;
    protected $_typeId = 1;

    /**
     * Variable for saving Old Rest Url to History
     * @var string 
     */
    protected $_oldRestUrl = null;

    /**
     * Variable for saving Old Cater Url to History
     * @var string 
     */
    protected $_oldCaterUrl = null;

    /**
     * Variable for saving Old Great Url to History
     * @var string 
     */
    protected $_oldGreatUrl = null;

    /**
     * provide a current location and get all
     * service within range
     * @todo: implement
     * @param string $lat
     * @param string $lon
     * @return SplObjectStorage
     */
    static public function getNearBy($lat, $lon, $range) {
        $services = new SplObjectStorage();
        //TODO
        return $services;
    }

    /**
     * Returns the restaurant name
     * @author mlaug
     * @return string
     */
    public function getRestaurantName() {
        return $this->getName();
    }

    /**
     * @author mlaug
     * @return string
     */
    public function getServiceName() {
        return __('Restaurant');
    }

    /**
     * @author mlaug
     * @return string
     */
    public function getSellingName() {
        return __('Speisen');
    }

    /**
     * copy opening times from this restaurant to another
     * @author mlaug
     * @param int $dstRestaurantId
     */
    public function copyOpenings($dstRestaurantId) {
        foreach ($this->getRegularOpenings() as $o) {
            $opening = new Yourdelivery_Model_Servicetype_Openings();
            $opening->setRestaurantId($dstRestaurantId);
            $opening->setDay($o['day']);
            $opening->setFrom($o['from']);
            $opening->setUntil($o['until']);
            $opening->save();
        }
    }

    /**
     * copy special opening times from this restaurant to another
     * @author alex
     * @param int $dstRestaurantId
     */
    public function copySpecialOpenings($dstRestaurantId) {
        foreach ($this->getSpecialOpenings() as $o) {
            if (count(Yourdelivery_Model_DbTable_Restaurant_Openings_Special::getOpeningsAtSqlDate($dstRestaurantId, $o['specialDate'])) > 0) {
                continue;
            } else {
                $opening = new Yourdelivery_Model_Servicetype_OpeningsSpecial();
                $opening->setRestaurantId($dstRestaurantId);
                $opening->setSpecialDate($o['specialDate']);
                $opening->setFrom($o['from']);
                $opening->setUntil($o['until']);
                $opening->setClosed($o['closed']);
                $opening->setDescription($o['description']);
                $opening->save();
            }
        }
    }

    /**
     * if all meal categories of this restaurant have valid categories pictures
     * @author alex
     * @return boolean
     */
    public function hasAllCategoryPictures() {
        foreach ($this->getMealCategories() as $mcat) {
            if (!$mcat->hasAllCategoryPictures()) {
                return false;
            }
        }
        return true;
    }

    /**
     * get the satellite of this restaurant
     * @return array
     */
    public function getSatellites() {
        return $this->getTable()->getSatellites();
    }

    /**
     * Get all restaurants having satellite and the corresponding domains
     *
     * @author alex
     * @since 23.09.2010
     * @return Zend_Db_Select
     */
    public static function getRestaurantsWithSatellite() {
        $data = Yourdelivery_Model_DbTable_Restaurant::getRestaurantsWithSatellite();
        $citiesDone = array();

        $result = array();
        foreach ($data as $r) {
            if (!in_array($r['city'], $citiesDone)) {
                $result[$r['city']] = array();
                $citiesDone[] = $r['city'];
            }

            $result[$r['city']][] = $r;
        }

        return $result;
    }

    /**
     * Get all restaurants without satellite
     * @author alex
     * @since 23.09.2010
     * @return Zend_Db_Select
     */
    public static function getRestaurantsWithoutSatellite() {
        $data = Yourdelivery_Model_DbTable_Restaurant::getRestaurantsWithoutSatellite();
        $citiesDone = array();

        $result = array();
        foreach ($data as $r) {
            if (!in_array($r['city'], $citiesDone)) {
                $result[$r['city']] = array();
                $citiesDone[] = $r['city'];
            }

            $result[$r['city']][] = $r;
        }

        return $result;
    }

    /**
     * Delete the cache for restaurant
     * @author alex
     * @since 28.07.2011
     */
    public function uncache($setMealFlags = false) {

        $config = Zend_Registry::get('configuration');

        $this->logger->info(sprintf("Yourdelivery_Model_Servicetype_Restaurant: Starting uncache restaurant #%d", $this->getId()));

        $locations = array();
        
        $subdomain = IS_PRODUCTION? 'www.':'staging.';

        $restLocation = APPLICATION_PATH . "/../public/cache/html/" . $subdomain . $config->domain->base . "/" . $this->getRestUrl() . ".html";
        $caterLocation = APPLICATION_PATH . "/../public/cache/html/" . $subdomain . $config->domain->base . "/" . $this->getCaterUrl() . ".html";
        $greatLocation = APPLICATION_PATH . "/../public/cache/html/" . $subdomain . $config->domain->base . "/" . $this->getGreatUrl() . ".html";
        
        if (file_exists($restLocation)) {
            unlink($restLocation);
        }

        if (file_exists($caterLocation)) {
            unlink($caterLocation);
        }

        if (file_exists($greatLocation)) {
            unlink($greatLocation);
        }

        if ($setMealFlags) {
            foreach ($this->getMeals() as $m) {
                try {
                    $meal = new Yourdelivery_Model_Meals($m['id']);
                    if (intval($meal->getDeleted()) == 1) {
                        continue;
                    }
                    $meal->updateHasSpecials();
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    
                }
            }
        }
        
        // cache API menu
        Default_Helpers_Cache::remove(md5('servicemenu' . $this->getId()));
            
        $this->logger->info(sprintf("Yourdelivery_Model_Servicetype_Restaurant: Cache was flushed for restaurant #%d", $this->getId()));

        //purge varnish cache
        if ($config->varnish->enabled) {       

            $locations[] = $this->getRestUrl();
            $locations[] = $this->getCaterUrl();
            $locations[] = $this->getGreatUrl();
            
            $this->logger->info(sprintf("Triggering purging of varnish cache for service #%d", $this->getId()));
            $varnishPurger = new Yourdelivery_Api_Varnish_Purger();
            
            $satellites = $this->getSatellites();
            foreach($satellites as $satelitte){
                $varnishPurger->addUrl(sprintf('http://%s%s', $satelitte['domain'], $satelitte['url']));
                $varnishPurger->addUrl(sprintf('http://%s%s/menu', $satelitte['domain'], $satelitte['url']));
            }
            
            foreach ($locations as $url) {
                $varnishPurger->addUrl($url);
            }
            if ($varnishPurger->executePurge()) {
                $this->logger->info(sprintf('successfully triggered purging of varnish cache for service #%d', $this->getId()));
            } else {
                $this->logger->warn(sprintf('could not triggered purging of all caches for service #%d', $this->getId()));
            }
        }
    }

    /**
     * Delete the rating cache for restaurant
     * @author alex
     * @since 29.11.2010
     */
    public function uncacheRating() {
        $files = glob(APPLICATION_PATH . "/views/smarty/cache/ratings" . $this->getId() . "*");

        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Clear chache for all ranges of this restaurtant
     * @author alex
     * @since 15.11.2011
     */
    public function uncacheRanges() {
        $config = Zend_Registry::get('configuration');
        $varnishPurger = new Yourdelivery_Api_Varnish_Purger();

        $ranges = $this->getRanges(100000);
        $districtIds = array();
        $regionIds = array();
        foreach ($ranges as $r) {
            if ($r['districtId']) {
                $districtIds[$r['districtId']] = $r['districtId'];
            }
            if ($r['regionId']) {
                $regionIds[$r['regionId']] = $r['regionId'];
            }
        }
        $districts = Yourdelivery_Model_DbTable_Districts::getUrlsForIds($districtIds);
        if($districts) {
            $ranges = array_merge($ranges, $districts);
        }
        $regions = Yourdelivery_Model_DbTable_Regions::getUrlsForIds($regionIds);
        if($regions) {
            $ranges = array_merge($ranges, $regions);
        }
        
        $subdomain = IS_PRODUCTION? 'www.':'staging.';

        foreach ($ranges as $r) {
            $restUrl = APPLICATION_PATH . "/../public/cache/html/" . $subdomain . $config->domain->base . "/" . $r['restUrl'] . ".html";
            if (file_exists($restUrl)) {
                unlink($restUrl);
            }

            $caterUrl = APPLICATION_PATH . "/../public/cache/html/" . $subdomain . $config->domain->base . "/" . $r['caterUrl'] . ".html";
            if (file_exists($caterUrl)) {
                unlink($caterUrl);
            }

            $greatUrl = APPLICATION_PATH . "/../public/cache/html/" . $subdomain . $config->domain->base . "/" . $r['greatUrl'] . ".html";
            if (file_exists($greatUrl)) {
                unlink($greatUrl);
            }

            if ($config->varnish->enabled) {
                $varnishPurger->addUrl($r['restUrl']);
                $varnishPurger->addUrl($r['caterUrl']);
                $varnishPurger->addUrl($r['greatUrl']);
            }
        }

        if ($config->varnish->enabled) {
            $this->logger->info(sprintf("Triggering purging of varnish cache for service #%d", $this->getId()));
            if ($varnishPurger->executePurge()) {
                $this->logger->info(sprintf('successfully triggered purging of varnish cache for service #%d', $this->getId()));
            } else {
                $this->logger->warn(sprintf('could not triggered purging of all caches for service #%d', $this->getId()));
            }
        }
    }

    /**
     * Check for historic Urls, if they match return false if its not from the same restaurant, ugly but compact
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean 
     */
    public function checkOldUrls($restUrl = null, $caterUrl = null, $greatUrl = null) {
        $history = new Yourdelivery_Model_DbTable_Restaurant_UrlHistory();

        foreach (array('restUrl' => $restUrl, 'caterUrl' => $caterUrl, 'greatUrl' => $greatUrl) as $key => $url) {

            $oldUrl = $this->{"get" . ucfirst($key)}();

            if (!empty($url) && strcmp($oldUrl, $url) != 0) {

                $entries = $history->findByUrl($oldUrl);
                //check for existing Inconsistencies, there should never be a duplicate Url in History and Restaurant Table        
                if ($entries->count() > 0) {
                    $this->logger->err(sprintf('Inconsistency: Url could not be saved: %s , with history Url %s ', $this->getRestUrl(), $entries->current()->url));
                    return false;
                }

                //check if new Url is in History, Ok if it is  the same RestaurantId
                $entries = $history->findByUrl($url);
                foreach ($entries as $entry) {
                    if ($entry->restaurantId != $this->getId() && $entry->url === $url) {
                        $this->logger->err(sprintf('Conflict: trying to change to redirect url  %s from restaurant %d', $url, $entry->restaurantId));
                        return false;
                    }
                }

                $this->{"_old" . ucfirst($key)} = $oldUrl;
            }
        }

        return true;
    }

    /**
     * 
     * Check here if there is a url to be put in history
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function save() {
        $id = parent::save();

        $modes = array('rest', 'cater', 'great');

        foreach ($modes as $mode) {

            $oldUrl = $this->{"_old" . ucfirst($mode) . "Url"};

            if (!empty($oldUrl)) {
                try {
                    $history = new Yourdelivery_Model_DbTable_Restaurant_UrlHistory();

                    $existingUrls = $history->findByUrl($this->{"get" . ucfirst($mode) . "Url"}());

                    //delete existing row if id is from the same restaurant
                    if ($existingUrls->count() > 0) {
                        foreach ($existingUrls as $urlRow) {
                            if ($urlRow->restaurantId == $this->getId()) {
                                $filename = APPLICATION_PATH . "/../public/cache/html/" . HOSTNAME . "/" . $urlRow->url . ".php";
                                if (file_exists($filename)) {
                                    unlink($filename);
                                }
                                $urlRow->delete();
                            }
                        }
                    }
                    $history->insert(array('restaurantId' => $this->getId(), 'url' => $oldUrl, 'mode' => $mode));
                } catch (Exception $e) {
                    $this->logger->err('Url could not be saved: ' . $e->getMessage());
                }
            }
        }

        return $id;
    }

    /**
     * TODO
     * Refactor - only for backend
     * @author alex
     * @since 14.07.2011
     */
    public function getEditlink() {
        return sprintf("<a href=\"/administration_service_edit/index/id/%s\">%s</a>", $this->getId(), $this->getName());
    }

    /**
     * @author dhahn
     * @since 27.10.2011
     * @return array
     */
    public static function all($offset = 0, $limit = 10000) {
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("SELECT  r.qypeId, r.name, r.plz, r.tel,r.fax, r.restUrl,r.onlycash,r.paymentbar, r.street, r.hausnr, if(r.franchiseTypeId=3, 1, 0) as premium, r.ratingQuality, r.ratingDelivery, r.ratingAdvisePercentPositive, r.id as serviceId, rc.name as categoryName, rcomp.id, cr.courierId,
                    (SELECT count(`id`) AS `count`
                               FROM `restaurant_servicetype`rs
                               WHERE rs.servicetypeId = 1  AND rs.restaurantId = r.id
                               LIMIT 1) as servicetypeCount,
                    (SELECT IF (c.parentCityId > 0, CONCAT(cp.city, ' (', c.city, ')'), c.city)
                        FROM city c
                        LEFT JOIN city cp ON c.parentCityId = cp.id
                        WHERE c.id = r.cityId) as cityName
                    FROM `restaurants`r
                    LEFT JOIN `restaurant_categories` rc ON r.categoryId = rc.id
                    LEFT JOIN `restaurant_company` rcomp ON rcomp.restaurantId=r.id
                    LEFT JOIN `courier_restaurant` cr ON cr.restaurantId=r.id
                    WHERE rcomp.id IS NULL AND r.isOnline=1 and r.deleted=0
                    HAVING servicetypeCount > 0
                    ORDER BY serviceId ASC LIMIT %d OFFSET %d", $limit, $offset);

        return $db->fetchAll($sql);
    }

    /**
     * copy deliver ranges from this restaurant to another
     * @since 05.04.2012
     * @author Alex Vait <vait@lieferando.de>
     */
    public function copyDeliverRanges($dstRestaurantId, &$countErrors, &$countDuplicates, &$countSuccess) {
        foreach ($this->getTable()->getPlainDeliverRanges() as $r) {
            try {
                $range = new Yourdelivery_Model_Servicetype_Plz($r['id']);
                
                $alreadyAssigned = Yourdelivery_Model_DbTable_Restaurant_Plz::findByRestaurantIdAndCityId($dstRestaurantId, $r['cityId']);
                if (!is_null($alreadyAssigned) && is_array($alreadyAssigned)) {
                    $dstRange = new Yourdelivery_Model_Servicetype_Plz($alreadyAssigned['id']);
                    $dstRange->setData($range->getData());
                    $countDuplicates++;
                }
                else {
                    $dstRange = $range;
                    $dstRange->setId(null);
                }
                
                $dstRange->setRestaurantId($dstRestaurantId);
                $dstRange->save();
                $countSuccess++;
            } 
            catch (Zend_Db_Statement_Exception $e) {
                $countErrors += 1;
            }
        }
        $countSuccess -= $countDuplicates;
    }

}

