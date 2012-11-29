<?php

/**
 * Database interface for Yourdelivery_Models_DbTable_Restaurants.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
 */
class Yourdelivery_Model_DbTable_Restaurant extends Default_Model_DbTable_Base {

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'restaurants';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Meals',
        'Yourdelivery_Model_DbTable_Meal_Categories',
        'Yourdelivery_Model_DbTable_Order',
        'Yourdelivery_Model_DbTable_BillingAsset',
        'Yourdelivery_Model_DbTable_Restaurant_Plz',
        'Yourdelivery_Model_DbTable_Restaurant_Company',
        'Yourdelivery_Model_DbTable_Billing_Balance'
    );
    protected $_referenceMap = array(
        'Categories' => array(
            'columns' => 'categoryId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Restaurant_Categories',
            'refColumns' => 'id'
        )
    );
    private $_deliverInfo = array();
    private $_isOpen = null;

    /**
     * Get all restaurants having satellite and the corresponding domains
     * @author alex
     * @since 23.09.2010
     * @return Zend_Db_Select
     */
    public static function getRestaurantsWithSatellite() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $sql = "select r.id as restaurantId, r.name as restaurantName, s.id as sateliteId, s.domain as domain, c.city as city  from satellites s join restaurants r on s.restaurantId=r.id join city c on c.id=r.cityId where r.isOnline=1 and r.status=0 and r.deleted=0 order by c.city";
        $result = $db->query($sql)->fetchAll();

        return $result;
    }

    /**
     * Get all restaurants without satellite
     * @author alex
     * @since 23.09.2010
     * @return Zend_Db_Select
     */
    public static function getRestaurantsWithoutSatellite() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $sql = "select r.id as restaurantId, r.name as restaurantName, c.city as city from restaurants r left join satellites s on r.id=s.restaurantId join city c on c.plz=r.plz where s.id is null and r.isOnline=1 and r.status=0 and r.deleted=0 order by c.city";
        $result = $db->query($sql)->fetchAll();

        return $result;
    }

    /**
     * List of all restaurants where at least one order was made, but soem banking data is missing (ktoName, ktoNr, ktoBlz)
     * @author alex
     * @since 23.09.2010
     * @return Zend_Db_Select
     */
    public static function getRestaurantsWithMissingBankingData() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $config = Zend_Registry::get('configuration');

        if ($config->domain->base == 'janamesa.com.br') {
            // There is no "BLZ" in Brasil, but "Bank"
            $sql = "SELECT r.id as restaurantId, r.name as restaurantName, COUNT(o.id) AS ordersCount, REPLACE(r.ktoName, '0', '') as kontoName, REPLACE(r.ktoNr, '0', '') as kontoNr, REPLACE(r.ktoBank, '0', '') as kontoBank FROM restaurants r LEFT JOIN orders o ON r.id=o.restaurantId WHERE r.deleted=0 GROUP BY r.id HAVING ordersCount>0 AND (LENGTH(kontoName)=0 OR LENGTH(kontoNr)=0 OR LENGTH(kontoBank)=0) order by restaurantName";
        } else {
            $sql = "SELECT r.id as restaurantId, r.name as restaurantName, COUNT(o.id) AS ordersCount, REPLACE(r.ktoName, '0', '') as kontoName, REPLACE(r.ktoNr, '0', '') as kontoNr, REPLACE(r.ktoBlz, '0', '') as kontoBlz FROM restaurants r LEFT JOIN orders o ON r.id=o.restaurantId WHERE r.deleted=0 GROUP BY r.id HAVING ordersCount>0 AND (LENGTH(kontoName)=0 OR LENGTH(kontoNr)=0 OR LENGTH(kontoBlz)=0) order by restaurantName";
        }
        $result = $db->query($sql)->fetchAll();

        return $result;
    }

    /**
     * Get all restaurants where at least one category have no category picture
     *
     * @author alex
     * @since 10.11.2010
     * @return Zend_Db_Select
     */
    public static function getRestaurantsWithMissingCategoryPicture() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $sql = "select r.* from restaurants r join meal_categories mc on r.id=mc.restaurantId left join category_picture cp on cp.id=mc.categoryPictureId where cp.id is null and r.deleted=0 group by r.id order by r.name";
        $result = $db->query($sql)->fetchAll();

        return $result;
    }

    /**
     * get the first order of this restaurant
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @param integer $id
     * @return Zend_Db_Table_Row
     */
    public static function getDateOfFirstSale($id) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("o" => "orders"))
                ->where("o.restaurantId=?", (integer) $id)
                ->order('time ASC')
                ->limit(1);
        return $db->fetchRow($query);
    }

    /**
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public static function edit($id, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('restaurants', $data, 'restaurants.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('restaurants', array('deleted' => '1'), 'restaurants.id = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $offset
     */
    public static function get($order = null, $limit = 0, $offset = 0) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("%ftable%" => "restaurants"));

        if ($order != null) {
            $query->order($order);
        }

        if ($limit != 0) {
            $query->limit($limit, $offset);
        }

        return $db->fetchAll($query);
    }

    /**
     * IN USE (StaticController) Felix
     *
     * get a rows matching Id by given value
     * @param int $id
     */
    public static function findById($id) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("r" => "restaurants"))
                ->where("r.id = '" . $id . "'");

        return $db->fetchRow($query);
    }

    /**
     * IN USE (ImportController) Felix
     *
     * get rows matching customerNr
     *
     * @return array
     */
    public static function findByEmptyCustomerNr() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        return $db->query("SELECT * FROM restaurants WHERE length(customerNr)=0")->fetchAll();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param string $custNr
     * @return array
     */
    public static function findByCustomerNr($custNr) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()->from('restaurants')->where('customerNr = ?', $custNr);

        return $db->fetchRow($select);
    }

    /**
     * IN USE (StaticController) Felix
     *
     * get a rows matching Name by given value
     * @param varchar $name
     */
    public static function findByName($name) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("r" => "restaurants"))
                ->where("r.name LIKE '%" . $name . "%' and r.isOnline=0 and deleted=0");

        return $db->fetchRow($query);
    }

    /**
     * Get a row matching direct plz link
     * @author mlaug
     * @since 06.07.2011
     * @param string $uri
     * @return array
     */
    public static function findByDirectLink($uri) {

        if (empty($uri) || $uri == "/") {
            return null;
        }

        $db = Zend_Registry::get('dbAdapter');
        foreach (array('rest', 'cater', 'great') as $mode) {
            $sql = sprintf("SELECT *
            FROM `restaurants` r
            WHERE r.deleted = 0
                AND r.%sUrl = ? ", $mode);
            $row = $db->fetchRow($sql, $uri);
            if ($row) {
                return array($mode, $row);
            }
        }
        return null;
    }

    /**
     * get a rows matching Ort by given value
     * @param varchar $ort
     */
    public static function findByOrt($ort) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("r" => "restaurants"))
                ->where("r.ort = " . $ort);

        return $db->fetchRow($query);
    }

    /**
     * get list of certian servicetype by given
     * plz and type id
     * @author mlaug, vpriem, daniel
     * @param int $cityIds
     * @param int $type
     * @param array $offlineStati
     * @return array
     */
    public static function getList($cityIds, $type = null, $offlineStati = null, $limit = null) {

        $db = Zend_Registry::get('dbAdapterReadOnly');

        if (!is_array($cityIds)) {
            $cityIds = array($cityIds);
        }

        $cList = implode(',', $cityIds);
        $parentCityIds = array_map(function($item) {
                    return (integer) $item['parentCityId'];
                }, $db->fetchAll('SELECT `parentCityId` FROM `city` WHERE `id` in (?)', $cList));

        $childrenCityIds = array_map(function($item) {
                    return (integer) $item['id'];
                }, $db->fetchAll('SELECT `id` FROM `city` WHERE parentCityId in (?)', $cList));

        $in = array_filter(array_merge($cityIds, $parentCityIds, $childrenCityIds), function($item) {
                    return $item > 0;
                });

        // get all services
        $select = $db->select()
                ->from(array('r' => 'restaurants'), array('id'))
                ->join(array('rp' => 'restaurant_plz'), "rp.restaurantId = r.id", array())
                ->join(array('rs' => 'restaurant_servicetype'), "rs.restaurantId = r.id", array('rs.servicetypeId'))
                ->where('rp.cityId IN (?)', array_unique($in))
                ->where('r.deleted = 0')
                ->group('r.id')
                ->group('rs.servicetypeId')
                ->order('r.name');

        if ($type !== null) {
            $select->where('rs.servicetypeId = ?', $type);
        }

        if (!$offlineStati) {
            $select->where('r.isOnline = 1')
                    ->where('rp.status = 1');
        } elseif (is_array($offlineStati)) {
            $select->where('r.isOnline = 0')
                    ->where('r.status IN (?) ', $offlineStati);
        }

        if ($limit) {
            $select->limit($limit);
        }

        return $db->fetchAll($select);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 16.11.2011
     * @param int $cityId
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public static function getListForApi($cityId = null, $offset = 0, $limit = 10000) {
        if ($cityId === null) {
            return array();
        }

        $hash = md5('apiservicelist' . $cityId . $offset . $limit);
        $services = Default_Helpers_Cache::load($hash);
        if ($services === null) {

            $db = Zend_Registry::get('dbAdapterReadOnly');

            $sql = sprintf("SELECT SQL_CALC_FOUND_ROWS r.qypeId, r.name, r.plz, r.tel,r.fax, r.restUrl,r.onlycash,
                r.paymentbar, r.street, r.hausnr, if(r.franchiseTypeId=3, 1, 0) as premium, r.ratingQuality, r.ratingDelivery, r.ratingAdvisePercentPositive, r.id as serviceId, rc.name as categoryName, rcomp.id, cr.courierId,
                    r.description,
                        IF(
                    	(SELECT id FROM restaurant_openings ro
                    		WHERE ro.restaurantId = r.id
                    			AND ro.day = DATE_FORMAT(NOW(), %s)
                    			AND DATE_FORMAT(NOW(), %s) BETWEEN ro.from AND ro.until
                    		LIMIT 1) IS NOT NULL, 1,0) AS open,
                        (SELECT count(`id`) AS `count`
                                   FROM `restaurant_servicetype`rs
                                   WHERE rs.servicetypeId = 1  AND rs.restaurantId = r.id
                                   LIMIT 1) as servicetypeCount,
                        (SELECT IF (c.parentCityId > 0, CONCAT(cp.city, ' (', c.city, ')'), c.city)
                            FROM city c
                            LEFT JOIN city cp ON c.parentCityId = cp.id
                            WHERE c.id = r.cityId) as cityName,
                            rp.deliverTime, rp.delcost, rp.mincost, rp.noDeliverCostAbove, rp.cityId
                        FROM `restaurants`r
                        INNER JOIN `restaurant_plz` rp ON rp.restaurantId = r.id
                        LEFT JOIN `restaurant_categories` rc ON r.categoryId = rc.id
                        LEFT JOIN `restaurant_company` rcomp ON rcomp.restaurantId=r.id
                        LEFT JOIN `courier_restaurant` cr ON cr.restaurantId=r.id
                        JOIN restaurant_servicetype rs ON r.id = rs.restaurantId
                        WHERE
                            rs.servicetypeId = 1
                            AND (rcomp.id IS NULL OR rcomp.exclusive=0)
                            AND r.isOnline=1
                            AND r.deleted=0
                            AND (rp.cityId  = %d 
                                OR rp.cityId in (SELECT `parentCityId` FROM `city` WHERE `id` = %d) 
                                OR rp.cityId in (SELECT `id` FROM `city` WHERE parentCityId = %d))
                            AND rp.status=1
                        GROUP BY r.id
                        HAVING servicetypeCount > 0
                        ORDER BY OPEN DESC, r.name ASC
                        LIMIT %d OFFSET %d", '"%w"', '"%H:%i:%s"', $cityId, $cityId, $cityId, $limit, $offset);

            $services = $db->fetchAll($sql);
            $services['count'] = (integer) $db->fetchOne('SELECT FOUND_ROWS()');
            Default_Helpers_Cache::store($hash, $services);
        }
        return $services;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 16.11.2011
     * @param mixed $courier
     * @return array
     */
    public function getRangesForApi($withCourier = false) {
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

        return $ranges;
    }

    /**
     * get category of restaurant
     * @return Zend_Db_Table_Row_Abstract
     */
    function getCategory() {
        return $this->getCurrent()
                        ->findParentRow('Yourdelivery_Model_DbTable_Restaurant_Categories');
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     * 
     * @param integer $limit
     * @param integer $state
     * @param string $driver
     */
    public function getOrdersFilteredForPartner($limit = 0, $state = null, $driver = null, $lowerThreshold = 'today'){       
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select(array('orderId' => 'o.id'))
                    ->from(array('o' => 'orders'))
                    ->where('o.restaurantId = ?', $this->getId());
        
        if ( $limit > 0 ){
            $query->limit($limit);
        }
        
        if ( $state !== null ){
            $query->join(array('sta' => 'order_geolocation_status_log', 'sta.orderId=o.id'));
            $query->where('statusId=?', (integer) $state);
        }
        
        if ( $driver !== null ){
            $query->join(array('dri' => 'restaurant_partner_drivers', 'dri.restaurantId=o.restaurantId'));
            $query->join(array('driOrd' => 'restaurant_partner_drivers_orders'), 'driOrd.orderId=o.id');
            $query->where('dri.name=?', $driver);
        }    
        
        switch($lowerThreshold){
            default:
            case 'today':
                $query->where('DATE(time) = DATE(NOW())');
                break;
            case 'all':
                break;
        }
        
        $query->group('o.id');
        $query->order('time DESC');
                
        return $db->fetchAll($query);
    }

    /**
     * get orders of restaurant
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getOrders() {
        return $this->getCurrent()
                        ->findDependentRowset('Yourdelivery_Model_DbTable_Order');
    }

    /**
     * get count of all orders of the restaurant
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getOrdersCount($onlyConfirmed = false) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()->from(array('o' => 'orders'), array('count' => 'COUNT(id)'))->where('restaurantId = ?', $this->getId());

        if ($onlyConfirmed) {
            $query->where('state > 0');
        }

        $result = $db->fetchRow($query);

        return $result['count'];
    }

    /**
     * Get Count of all orders of this Restaurant by week
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return array
     */
    public function getOrdersCountPerWeek() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $select = $db->select()
                ->from('orders', array(
                    'count' => new Zend_Db_Expr('SUM(IF(id IS NOT NULL, 1, 0))'),
                    'week' => new Zend_Db_Expr('WEEK(time, 1)')))
                ->where('restaurantId = ?', $this->getId())
                ->where('YEAR(time) = YEAR(NOW())')
                ->group('week');

        return $db->fetchAll($select);
    }

    /**
     * add new meal category
     * @param string $name
     * @param string $decs
     * @param string $mwst
     * @param string $hasPfand
     * @return
     */
    public function addMealCategory($name, $desc, $mwst, $hasPfand, $def) {
        $sql = sprintf('SELECT max(rank) as max FROM meal_categories where restaurantId=%d', $this->getId());
        $result = $this->getAdapter()->fetchRow($sql);
        $max = $result['max'];

        $table = new Yourdelivery_Model_DbTable_Meal_Categories();
        $row = $table->createRow();
        $row->name = $name;
        $row->description = $desc;
        $row->restaurantId = $this->getId();
        $row->mwst = $mwst;
        $row->hasPfand = strval($hasPfand);
        $row->def = $def;
        $row->top = 0;
        $row->rank = $max + 1;

        return $row->save();
    }

    /**
     * delete meal category
     * @param string $id
     * @return
     */
    public function deleteMealCategory($id) {
        try {
            $cat = new Yourdelivery_Model_Meal_Category($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        $rank = $cat->getRank();

        $table = new Yourdelivery_Model_DbTable_Meal_Categories();
        $table->remove($id);

        //update the rank of lower categories
        $sql = sprintf('update meal_categories set rank=rank-1 where rank>%d and restaurantId = %d', $rank, $this->getId());
        $result = $this->getAdapter()->query($sql);
    }

    /**
     * delete options row
     * @param string $id
     * @return
     */
    public function deleteMealOptionsRow($id) {
        Yourdelivery_Model_DbTable_Meal_OptionsRows::remove($id);
    }

    /**
     * delete options option
     * @param string $id
     * @return
     */
    public function deleteMealOption($id) {
        Yourdelivery_Model_DbTable_Meal_Options::remove($id);
    }

    /**
     * delete meal extra group
     * @param string $id
     * @return
     */
    public function deleteMealExtraGroup($id) {
        try {
            $groups = new Yourdelivery_Model_DbTable_Meal_ExtrasGroups();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }
        $groups->remove($id);
    }

    /**
     * delete meal extra
     * @param string $id
     * @return
     */
    public function deleteMealExtra($id) {
        try {
            $extras = new Yourdelivery_Model_DbTable_Meal_Extras();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }
        $extras->remove($id);
    }

    /**
     * move meal category up
     * @param string $catid
     * @return
     */
    public function upCategory($catid) {
        if ($catid == null) {
            return;
        }

        try {
            $cat = new Yourdelivery_Model_Meal_Category($catid);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        $rank = $cat->getRank();

        if ($rank <= Yourdelivery_Model_DbTable_Meal_Categories::getMinRank($this->getId())) {
            return;
        }

        $sql = sprintf('update meal_categories set rank=rank+1 where rank=%d and restaurantId = %d', $rank - 1, $this->getId());
        $result = $this->getAdapter()->query($sql);

        $cat->setRank($rank - 1);
        $cat->save();
    }

    /**
     * move meal category down
     * @param string $id
     * @return
     */
    public function downCategory($catid) {
        if ($catid == null) {
            return;
        }

        try {
            $cat = new Yourdelivery_Model_Meal_Category($catid);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        $rank = $cat->getRank();

        if ($rank >= Yourdelivery_Model_DbTable_Meal_Categories::getMaxRank($this->getId())) {
            return;
        }

        $sql = sprintf('update meal_categories set rank=rank-1 where rank=%d and restaurantId = %d', $rank + 1, $this->getId());
        $result = $this->getAdapter()->query($sql);

        $cat->setRank($rank + 1);
        $cat->save();
    }

    /**
     * move meal size left (up in rank)
     * @param string $id
     * @return
     */
    public function moveMealSizeLeft($sizeId) {
        if ($sizeId == null) {
            return;
        }

        $resultCat = $this->getAdapter()->fetchRow(sprintf('SELECT categoryId FROM meal_sizes where id=%d', $sizeId));
        $categoryId = $resultCat['categoryId'];

        try {
            $size = new Yourdelivery_Model_Meal_Sizes($sizeId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        $rank = $size->getRank();

        if ($rank <= Yourdelivery_Model_DbTable_Meal_Sizes::getMinRank($categoryId)) {
            return;
        }

        $sql = sprintf('update meal_sizes set rank=rank+1 where rank=%d and categoryId= %d', $rank - 1, $categoryId);
        $result = $this->getAdapter()->query($sql);

        $size->setRank($rank - 1);
        $size->save();
    }

    /**
     * move meal size right (down in rank)
     * @param string $id
     * @return
     */
    public function moveMealSizeRight($sizeId) {
        if ($sizeId == null) {
            return;
        }

        $resultCat = $this->getAdapter()->fetchRow(sprintf('SELECT categoryId FROM meal_sizes where id=%d', $sizeId));
        $categoryId = $resultCat['categoryId'];

        try {
            $size = new Yourdelivery_Model_Meal_Sizes($sizeId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        $rank = $size->getRank();

        if ($rank >= Yourdelivery_Model_DbTable_Meal_Sizes::getMaxRank($categoryId)) {
            return;
        }

        $sql = sprintf('update meal_sizes set rank=rank-1 where rank=%d and categoryId= %d', $rank + 1, $categoryId);
        $result = $this->getAdapter()->query($sql);

        $size->setRank($rank + 1);
        $size->save();
    }

    /**
     * move meal up in rank
     * @param string $id
     * @return
     */
    public function moveMealUp($mealId) {
        if ($mealId == null) {
            return;
        }

        $resultCat = $this->getAdapter()->fetchRow(sprintf('SELECT categoryId FROM meals where id=%d', $mealId));
        $categoryId = $resultCat['categoryId'];

        try {
            $meal = new Yourdelivery_Model_Meals($mealId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        //ignore deleted meals
        if ($meal->getDeleted()) {
            return;
        }

        $rank = $meal->getRank();

        if ($rank <= Yourdelivery_Model_DbTable_Meals::getMinRank($categoryId)) {
            return;
        }

        $sql = sprintf('update meals set rank=rank+1 where rank=%d and categoryId= %d and deleted=0', $rank - 1, $categoryId);
        $result = $this->getAdapter()->query($sql);

        $meal->setRank($rank - 1);
        $meal->save();
    }

    /**
     * move meal down in rank
     * @param string $id
     * @return
     */
    public function moveMealDown($mealId) {
        if ($mealId == null) {
            return;
        }

        $resultCat = $this->getAdapter()->fetchRow(sprintf('SELECT categoryId FROM meals where id=%d', $mealId));
        $categoryId = $resultCat['categoryId'];

        try {
            $meal = new Yourdelivery_Model_Meals($mealId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        //ignore deleted meals
        if ($meal->getDeleted()) {
            return;
        }

        $rank = $meal->getRank();

        if ($rank >= Yourdelivery_Model_DbTable_Meals::getMaxRank($categoryId)) {
            return;
        }

        $sql = sprintf('update meals set rank=rank-1 where rank=%d and categoryId= %d', $rank + 1, $categoryId);
        $result = $this->getAdapter()->query($sql);

        $meal->setRank($rank + 1);
        $meal->save();
    }

    /**
     * rearrange meal categories
     * @param array. {rank => categoryId}
     * @author alex
     * @since 27.09.2010
     */
    public function arrangeCategories($categories, $restaurantId) {
        foreach ($categories as $rank => $categoryId) {
            $this->getAdapter()->update('meal_categories', array('rank' => $rank), 'meal_categories.id = ' . $categoryId . ' and meal_categories.restaurantId = ' . $restaurantId);
        }
    }

    /**
     * get meal categories of restaurant
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getMealCategories() {
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Meal_Categories');
    }

    /**
     * get meal categories of restaurant sorted by rank
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getMealCategoriesSorted() {
        $result = array();
        if (!is_null($this->getCurrent())) {
            $orderby = $this->select()->order('rank');
            $result = $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Meal_Categories', null, $orderby)->toArray();
        }
        return $result;
    }

    /**
     * get meal extras groups of this restaurant
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getMealExtrasGroups() {
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Meal_ExtrasGroups');
    }

    /**
     * get count meal extras groups of this restaurant
     * @return int
     */
    public function getMealExtrasGroupsCount() {
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Meal_ExtrasGroups')->count();
    }

    /**
     * get meal options rows of this restaurant
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getMealOptionsRows() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf('
            (select mr.* from meal_options_rows mr where mr.restaurantId=%d)
            UNION
            (select mr.* from meal_options_rows mr left join meal_categories mc on mr.categoryId=mc.id where mc.restaurantId=%d)', $this->getId(), $this->getId());
        return $db->query($sql)->fetchAll();
    }

    /**
     * get meals of restaurant
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getMeals($restaurantId, $includeDeleted) {
        if (!$includeDeleted) {
            return $this->getAdapter()->query('SELECT * FROM meals m WHERE deleted = 0 AND m.restaurantId = ?', $restaurantId)->fetchAll();
        } else {
            return $this->getAdapter()->query('SELECT * FROM meals m WHERE deleted = 0 AND m.restaurantId = ?', $restaurantId)->fetchAll();
        }
    }

    /**
     * Get all services
     * @author vpriem
     * @since 06.12.2010
     * @param int $deleted
     * @return array
     */
    public function getAllDirectLinks($deleted = 0) {

        return $this->getAdapter()
                        ->fetchAll(
                                "SELECT r.id, r.name, r.restUrl, r.caterUrl, r.greatUrl, r. metaRobots, IF(r.franchiseTypeId = 5, 1, 0) `bloomsburys`
                FROM `restaurants` r
                WHERE r.deleted = ?
                ORDER BY r.name", $deleted);
    }

    /**
     * Get three bestseller
     * @author abrillano
     * @since no idea, but a long time ago
     * @return three best selled meals of this restaurant
     * @modyfied alex 04.11.2011
     */
    public function getBestSeller($total = 3, $serviceTypeId = 1) {

        if (is_null($this->getId())) {
            return array();
        }

        $restaurantId = $this->getId();
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf("select obm.mealId, sum(obm.count) as `count` from orders_bucket_meals obm
                            join orders o on obm.orderId=o.id
                            join meals m on obm.mealId=m.id
                            join meal_categories mc on mc.id=m.categoryId
                            join servicetypes_meal_categorys_nn smc on smc.mealCategoryId=mc.id
                                where o.restaurantId=%d and m.deleted=0 and m.status=1 and o.state>0 and smc.servicetypeId=%d and mc.`from`='00:00:00' and mc.`to`='24:00:00' and weekdays=127
                                    group by m.id order by count desc limit %d", $restaurantId, $serviceTypeId, $total);
        $row = $db->query($sql)->fetchAll();
        return $row;
    }

    public function getOpenings($day = null) {
        if ($this->getId() === null) {
            return false;
        }
        $table = new Yourdelivery_Model_DbTable_Restaurant_Openings();
        return $table->getOpenings($this->getId(), $day);
    }

    /**
     * remove certain times when service is open
     * @param int $id opening id
     * @return
     */
    public function deleteOpening($id) {
        if (is_null($id)) {
            return;
        }
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_openings', 'id=' . $id . ' and restaurantId=' . $this->getId());
    }

    public function getOpeningsSpecial($time = null) {
        if (is_null($this->getId())) {
            return false;
        }
        $table = new Yourdelivery_Model_DbTable_Restaurant_Openings_Special();
        return $table->getOpenings($this->getId(), $time);
    }

    public function getOpeningsSpecialAtDate($ts) {
        if ($this->getId() === null) {
            return false;
        }
        $table = new Yourdelivery_Model_DbTable_Restaurant_Openings_Special();
        return $table->getOpeningsAtDate($this->getId(), $ts);
    }

    /**
     * @author mlaug
     * @since 15.08.2010
     * @param int $plz
     * @return int
     */
    public function getMinCost($plz) {
        $row = $this->_getDeliverInfo($plz);
        return $row['mincost'];
    }

    /**
     * @author mlaug
     * @since 15.08.2010
     * @param int $cityId
     * @return int
     */
    public function getDeliverCost($cityId) {
        $row = $this->_getDeliverInfo($cityId);
        return $row['delcost'];
    }

    /**
     * @author mlaug
     * @since 15.08.2010
     * @param int $plz
     * @return int
     */
    public function getNoDeliverCostAbove($cityId) {
        $row = $this->_getDeliverInfo($cityId);
        return $row['noDeliverCostAbove'];
    }

    /**
     * @author mlaug
     * @since 15.08.2010
     * @param int $cityId
     * @return int
     */
    public function getDeliverTime($cityId) {
        $row = $this->_getDeliverInfo($cityId);
        return $row['deliverTime'];
    }

    /**
     * @author alex
     * @since 21.06.2011
     * @param int $plz
     * @return int
     * @TODO  REFACTOR FOR BACKEND
     */
    public function getUniqueDeliverRangeId($cityId) {
        $row = $this->_getUniqueDeliverInfo($cityId);
        return $row['id'];
    }

    /**
     * @author mlaug
     * @since 15.08.2010
     * @param int $plz
     * @return int
     */
    public function getDeliverRangeId($cityId) {
        $row = $this->_getDeliverInfo($cityId);
        return $row['id'];
    }

    /**
     * get all ranges of this service and add children
     * children will be displayed in city (parentCity) so it is
     * better to use the cityname value for display
     * @author vpriem, mlaug
     * @since 10.03.2011
     * @param int $limit
     * @return array
     */
    public function getRanges($limit = 1000) {

        $adapter = Zend_Registry::get('dbAdapterReadOnly');

        // get all ranges
        $baseRange = $adapter->fetchAll(
                "SELECT rp.*, c.*, 
                IF (cc.id IS NULL, CONCAT(c.plz, ' ', c.city), CONCAT(c.plz, ' ', cc.city, ' (', c.city, ')')) `cityname`, 
                c.restUrl, c.caterUrl, c.greatUrl
            FROM `restaurant_plz` rp
            INNER JOIN `city` c ON rp.cityId = c.id
	    LEFT JOIN `city` cc ON c.parentCityId = cc.id
            WHERE rp.restaurantId = ?
                AND rp.status = 1
            ORDER BY rp.plz
            LIMIT " . ((integer) $limit), $this->getId());

        // get all child ranges of the parents ranges
        $childRange = $adapter->fetchAll(
                "SELECT rp.*, cc.id `cityId`, cc.parentCityId,
                CONCAT(cc.plz, ' ', c.city, ' (', cc.city, ')') `cityname`, 
                cc.restUrl, cc.caterUrl, cc.greatUrl, cc.districtId, cc.regionId
            FROM `restaurant_plz` rp
            INNER JOIN `city` c ON c.id = rp.cityId
            INNER JOIN `city` cc ON cc.parentCityId = c.id
            WHERE rp.restaurantId = ?
                AND rp.status = 1
                AND cc.id NOT IN (SELECT `cityId` FROM `restaurant_plz` WHERE `restaurantId` = rp.restaurantId AND `status` = 1)
            ORDER BY rp.plz
            LIMIT " . ((integer) $limit), $this->getId());

        $ranges = array_merge($baseRange, $childRange);
        usort($ranges, 'compare_ranges');
        return $ranges;
    }

    /**
     * delete a range from this service
     * @author mlaug
     * @since anytime
     * @param int $id
     */
    public function deleteRange($id) {
        $sql = sprintf("delete from restaurant_plz where id=%d", $id);
        $this->getAdapter()->query($sql);
    }

    /**
     * delete range based on city id
     * @author alex
     * @since 09.03.2011
     * @param int $cityId
     */
    public function deleteRangeByCityId($cityId) {
        $sql = sprintf("delete from restaurant_plz where restaurantId=%d and cityId=%d", $this->getId(), $cityId);
        $this->getAdapter()->query($sql);
    }

    /**
     * delete all locations for this restaurant
     */
    public function deleteAllRanges() {
        $sql = sprintf("delete from restaurant_plz where restaurantId=%d", $this->getId());
        $this->getAdapter()->query($sql);
    }

    /**
     * get state of deliver range
     *
     * @param integer $cityId id of city
     *
     * @return integer
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.02.2012
     */
    public function getRangeStatus($cityId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array('rp' => 'restaurant_plz'), 'status')
                ->where('restaurantId = ?', $this->getId())
                ->where('cityId = ?', $cityId);

        return (integer) $db->fetchOne($query);
    }

    /**
     * change state of this range
     * @param integer $status
     * @param integer $id
     */
    public function editRange($status, $id) {
        $sql = sprintf("update restaurant_plz set status = %d where id=%d", $status, $id);
        $this->getAdapter()->query($sql);
    }

    // edit all locations with new values, that are not null
    public function editLocationAll($mincost, $delcost, $time, $isOnline) {
        if (is_null($this->getId())) {
            return false;
        }

        if (!empty($mincost) && !is_null($mincost)) {
            $sql = sprintf("update restaurant_plz set mincost=%d where restaurantId=%d", $mincost, $this->getId());
            $this->getAdapter()->query($sql);
        }

        if (!empty($delcost) && !is_null($delcost)) {
            $sql = sprintf("update restaurant_plz set delcost=%d where restaurantId=%d", $delcost, $this->getId());
            $this->getAdapter()->query($sql);
        }

        if (!empty($time) && !is_null($time)) {
            $sql = sprintf("update restaurant_plz set deliverTime=%d where restaurantId=%d", $time, $this->getId());
            $this->getAdapter()->query($sql);
        }

        // 0 is online, 1 is offline
        if (!is_null($isOnline)) {
            $sql = sprintf("update restaurant_plz set status=%d where restaurantId=%d", $isOnline, $this->getId());
            $this->getAdapter()->query($sql);
        }
    }

    /**
     * Get deliver info
     *
     * @author vpriem
     * @return array
     * @modified alex 07.10.2011
     */
    private function _getDeliverInfo($cityId) {

        if (array_key_exists($cityId, $this->_deliverInfo)) {
            return $this->_deliverInfo[$cityId];
        }
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "SELECT rp.*,mincost as maxcost, 0 as child FROM `restaurant_plz` rp WHERE rp.restaurantId = ? AND rp.cityId = ? LIMIT 1";
        $row = $db->fetchRow($sql, array($this->getId(), $cityId));
        if (!$row) {
            //it may be a child
            $parent = $db->fetchRow('SELECT parentCityId FROM city where id=?', array($cityId));
            if ($parent['parentCityId']) {
                $row = $db->fetchRow($sql, array($this->getId(), $parent['parentCityId']));
            } else {
                $row = $db->fetchRow('SELECT rp.cityId, rp.status, min(rp.deliverTime) as deliverTime, min(delcost) as delcost, min(rp.mincost) as mincost, max(rp.mincost) as maxcost, "" as comment, rp.noDeliverCostAbove, c.id, 1 as child FROM city c INNER JOIN restaurant_plz rp on rp.cityId=c.id where c.parentCityId=? and restaurantId=?;', array($cityId, $this->getId()));
            }
        }
        return $this->_deliverInfo[$cityId] = $row;
    }

    /**
     * Get unique deliver info, without children
     * @author alex
     * @return array
     * TODO REFACTOR FOR BACKEND
     */
    private function _getUniqueDeliverInfo($cityId) {
        $sql = "SELECT rp.* FROM `restaurant_plz` rp WHERE rp.restaurantId = ? AND rp.cityId = ? LIMIT 1";
        return $this->getAdapter()->fetchRow($sql, array($this->getId(), $cityId));
    }

    /**
     * @author mlaug
     * @return Zend_Db_Table_Row
     */
    public function getBillingCustomized() {
        $sql = "SELECT id FROM billing_customized WHERE refId=? AND mode='rest'";
        return $this->getAdapter()->fetchRow($sql, array($this->getId()));
    }

    /**
     * Get courier
     * @author vpriem
     * @return array
     */
    public function getCourier() {

        $id = $this->getId();
        if ($id === null) {
            return null;
        }
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAll(
                        "SELECT *
            FROM `courier_restaurant`
            WHERE `restaurantId` = ?", $id);
    }

    /**
     * Get billing parent
     * @author alex
     * @since 28.09.2010
     * @return int
     */
    public function getBillingParentId() {

        $id = $this->getId();
        if ($id === null) {
            return null;
        }

        return $this->getAdapter()->fetchAll(
                        "SELECT parent
            FROM `billing_merge`
            WHERE `child` = ?", $id);
    }

    /**
     * get the salesperson responsible for this restaurant
     * @return array
     */
    public function getSalesperson() {
        if (is_null($this->getId())) {
            return null;
        }

        $sql = sprintf("select * from salesperson_restaurant where restaurantId=" . $this->getId());
        $row = $this->getAdapter()->query($sql)->fetchAll();
        return $row;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.07.2012
     * @return array 
     */
    public function getSatellites() {
        if (is_null($this->getId())) {
            return array();
        }

        $sql = sprintf("select * from satellites where restaurantId=" . $this->getId());
        return $this->getAdapter()->query($sql)->fetchAll();
    }

    /**
     * get the list of all field need by Google Local Business Centre
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getGoogleData($exclude = "''", $sortby = 'name') {
        $sql = sprintf("SELECT distinct(r.id), r.name , r.street, r.hausnr, r.plz , r.tel , r.fax , r.email, r.googleName, o.ort, o.kreis,r.googleCategories,r.description,r.googleExport
                from  restaurants r, orte o where r.plz = o.plz and r.status = 0 and r.isOnline = 0 and r.deleted= 0
                and r.id not in (" . $exclude . ") order by " . $sortby
        );
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /*
     * get list of restaurant that are private
     * @param array
     */

    public function getPrivateRestaurant() {
        $sql = sprintf("SELECT r.id
                FROM restaurant_company rc, restaurants r, orte o
                WHERE r.id = rc.restaurantId
                AND r.plz = o.plz
                AND r.isOnline =0
                AND r.deleted =0
                AND r.status =0
                GROUP BY rc.restaurantId
                HAVING count(rc.id) = 1"
        );
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /*
     * get list of restaurant that are private based on city
     * @param array
     */

    public function getPrivateRestaurantByCity($city) {
        $sql = sprintf("SELECT r.id
                FROM restaurant_company rc, restaurants r, orte o
                WHERE r.id = rc.restaurantId
                AND r.plz = o.plz
                AND o.ort = '" . $city . "'
                AND r.isOnline =0
                AND r.deleted =0
                AND r.status =0
                GROUP BY rc.restaurantId
                HAVING count(rc.id) = 1"
        );
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    public function getGoogleDataByCity($city, $exclude = "''", $sortby = 'name') {
        $sql = sprintf("SELECT distinct(r.id), r.name , r.street, r.hausnr, r.plz , r.tel , r.fax, o.ort, o.kreis, sat.domain as satelliteDomain
                from  restaurants r left join orte o on r.plz=o.plz left join satellites sat on sat.restaurantId=r.id where r.status = 0 and r.isOnline = 0 and r.deleted= 0 and o.ort = '" . $city . "'
                and r.id not in (" . $exclude . ") order by " . $sortby
        );
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    public function getGoogleDataMaps($status) {

        $sql = sprintf("SELECT distinct(r.id), r.name , r.street, r.hausnr, r.plz , r.tel , r.fax , r.email, r.latitude, r.longitude, o.ort, o.kreis, r.description
                    from  restaurants r, orte o where r.plz = o.plz and r.isOnline = " . $status . " and r.status = " . $status . " and "
                . FLAG_NOT_DELETED
        );

        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    public function checkRestaurantMeal($id) {
        $sql = sprintf("SELECT count(m.id)
                    from  meals m where m.restaurantId =" . $id . " group by m.restaurantId having count(m.id) != 0"
        );

        $fields = $this->getAdapter()->fetchRow($sql);
        return $fields;
    }

    /**
     * get the list of all distinct fields
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctNameId($sortby = 'name') {
        $sql = sprintf('select id, name, street, hausnr, plz, customerNr from restaurants where ' . FLAG_NOT_DELETED . ' order by ' . $sortby);
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * get the list of all distinct restaurtants which are not in the table billing merge
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctNameIdForMerge($sortby = 'name') {
        $sql = sprintf('select distinct(id), name from restaurants where id not in (select parent from billing_merge) and id not in (select child from billing_merge) and ' . FLAG_NOT_DELETED . ' order by ' . $sortby);
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * get the list of all distinct restaurtants which are not associated with this gprs printer
     * @author alex
     * @since 09.06.2011
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctNameIdForPrinter($printerId, $sortby = 'name') {
        if (intval($printerId) == 0) {
            return null;
        }

        $sql = sprintf('select id, name from restaurants where id not in (select restaurantId from restaurant_printer_topup) and ' . FLAG_NOT_DELETED . ' order by ' . $sortby);
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * get the list of all distinct plzs
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctPlz() {
        $sql = sprintf('select distinct(plz) from restaurants');
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * get actual maximal customer number
     * @param int $id
     */
    public static function getMaxCustNr() {
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('select max(customerNr) as max from restaurants');
        $result = $db->fetchRow($sql);

        return (integer) $result['max'];
    }

    /**
     * IN USE
     *
     * get latest customerNr of service
     * @return int
     */
    public static function getActualCustNr() {
        $db = Zend_Registry::get('dbAdapter');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        try {
            $sql = sprintf('select max(customerNr) as max from restaurants');
            $result = $db->fetchRow($sql);
        } catch (Zend_Db_Statement_Exception $e) {
            return 0;
        }

        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        return $result->max;
    }

    /**
     * get actual number of restaurants with orders of certain type
     * @param int $count
     */
    public static function getRestaurantsByType($type) {
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('SELECT distinct(restaurants.name), SUBSTRING(restaurants.plz , 1, 1) as plz FROM restaurants INNER JOIN orders on orders.restaurantId=restaurants.id WHERE orders.mode="%s" and orders.state>0 and restaurants.isOnline=0 and restaurants.status=0 and restaurants.deleted=0', $type);
        $result = $db->fetchAll($sql);

        return $result;
    }

    /**
     * get actual number of offline restaurants with orders of certain type and in certain city
     * @param int $count
     */
    public static function getOfflineRestaurantsCountByTypeAndCity($type, $city) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        if (is_null($type)) {
            if (is_null($city)) {
                $sql = sprintf('SELECT count(distinct(restaurants.id)) as count FROM restaurants INNER JOIN restaurant_servicetype on restaurant_servicetype.restaurantId=restaurants.id WHERE (restaurants.isOnline=1 or restaurants.status=1) and restaurants.deleted=0');
            } else {
                $sql = sprintf('SELECT count(distinct(restaurants.id)) as count FROM restaurants INNER JOIN restaurant_servicetype on restaurant_servicetype.restaurantId=restaurants.id join orte on restaurants.plz=orte.plz WHERE (restaurants.isOnline=1 or restaurants.status=1) and restaurants.deleted=0 and orte.ort="%s"', $city);
            }
        } else {
            if (is_null($city)) {
                $sql = sprintf('SELECT count(distinct(restaurants.id)) as count FROM restaurants INNER JOIN restaurant_servicetype on restaurant_servicetype.restaurantId=restaurants.id WHERE restaurant_servicetype.servicetypeId = %d and (restaurants.isOnline=1 or restaurants.status=1) and restaurants.deleted=0', $type);
            } else {
                $sql = sprintf('SELECT count(distinct(restaurants.id)) as count FROM restaurants INNER JOIN restaurant_servicetype on restaurant_servicetype.restaurantId=restaurants.id join orte on restaurants.plz=orte.plz WHERE restaurant_servicetype.servicetypeId = %d and (restaurants.isOnline=1 or restaurants.status=1) and restaurants.deleted=0 and orte.ort="%s"', $type, $city);
            }
        }

        $result = $db->fetchRow($sql);
        return $result['count'];
    }

    /**
     * get all cities with restaurants where orders have been made
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public static function getAllCitiesWithOrders() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf('select distinct(orte.ort) as city from restaurants inner join orders on orders.restaurantId=restaurants.id join orte on restaurants.plz=orte.plz where orders.state>0 and restaurants.isOnline=0 and restaurants.status=0 and restaurants.deleted=0 order by city');
        $result = $db->fetchAll($sql);
        return $result;
    }

    /**
     * get all cities with restaurants
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public static function getAllCities() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf('select distinct(city.city) as city from restaurants inner join city on restaurants.cityId=city.id order by city.city');
        $result = $db->fetchAll($sql);
        return $result;
    }

    /**
     * get restrictions of restaurant
     * any company assigned to restaurant is only allowed to order
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getCompanyRestrictions() {
        if (!is_null($this->getCurrent())) {
            return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Restaurant_Company');
        }
        return null;
    }

    /**
     * set restrictions of restaurant
     * any company assigned to restaurant is only allowed to order
     * @param int $companyId
     * @return
     */
    public function setCompanyRestriction($companyId, $excl) {
        if (is_null($companyId)) {
            return null;
        }

        if (Yourdelivery_Model_DbTable_Restaurant_Company::findByAssoc($this->getId(), $companyId)) {
            return null;
        }

        return Yourdelivery_Model_DbTable_Restaurant_Company::add($this->getId(), $excl, $companyId);
    }

    /**
     * remove company-restaurant relationship
     * @param int $companyId
     * @return
     */
    public function removeCompanyRestriction($companyId) {
        if (is_null($companyId)) {
            return null;
        }
        return Yourdelivery_Model_DbTable_Restaurant_Company::remove($this->getId(), $companyId);
    }

    /**
     * Get all ratings of this service
     * @author vpriem
     * @since 25.05.2011
     * @return array
     */
    public function getRatings($limit = null, $where = null) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        if ($this->getId() === null) {
            return array();
        }

        $query = $db->select()
                ->from(array('restaurant_ratings'))
                ->where('restaurantId = ?', $this->getId())
                ->where('status = 1')
                ->order('created DESC')
                ->limit($limit);
        if (!is_null($where)) {
            $query->where($where);
        }
        return $db->fetchAll($query);
    }

    /**
     * get positiv and negative recommendations for service
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.08.2010
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRatingAdvise() {
        $id = $this->getId();
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $result = $db
                ->query(
                        "SELECT
                    (SELECT count(id) FROM `restaurant_ratings` WHERE status > 0 AND `restaurantId` = " . $id . " AND `advise` = 1 LIMIT 1) AS `positiv`,
                    (SELECT count(id) FROM `restaurant_ratings` WHERE status > 0 AND `restaurantId` = " . $id . " AND `advise` = 0 LIMIT 1) AS `negativ`;")
                ->fetch();

        return $result;
    }

    /**
     * Check if restaurant has plz
     *
     * @author vpriem
     * @param string $plz
     * @return boolean
     */
    public function hasPlz($plz) {

        $id = $this->getId();
        if ($id === null) {
            return false;
        }
        $res = $this->getAdapter()
                ->query(
                        "SELECT `id`
                FROM `restaurant_plz`
                WHERE `restaurantId` = ?
                    AND `plz` = ?
                LIMIT 1", array($id, $plz)
                )
                ->fetch();
        return (boolean) $res;
    }

    /**
     * get small menu with the possiblity to search
     * @return array
     */
    public function getSmallMenu($search = array()) {
        if (is_null($this->getId())) {
            return null;
        }

        if (!is_array($search)) {
            $search = array();
        }
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db
                ->select()
                ->from(array('m' => 'meals'), array(
                    'id' => 'm.id',
                    'name' => 'm.name',
                    'description' => 'm.description',
                    'restaurantId' => 'm.restaurantId'
                ))
                ->join(array('ms' => 'meal_sizes_nn'), 'm.id = ms.mealId', array(
                    'min' => new Zend_Db_Expr('min(ms.cost)'),
                    'max' => new Zend_Db_Expr('max(ms.cost)'),
                ))
                ->join(array('mc' => 'meal_categories'), 'm.categoryId = mc.id', array())
                ->where('CURRENT_TIME BETWEEN mc.`from` and mc.`to`AND POW(2, (WEEKDAY(NOW()))) & mc.weekdays > 0')
                ->where('m.deleted=0');

        $where = array();
        $count = 0;
        foreach ($search as $s) {
            $where[$count][] = $this->getAdapter()->quoteInto('m.name like ?', "%" . $s . "%");
            $where[$count][] = $this->getAdapter()->quoteInto('m.description like ?', "%" . $s . "%");
            $where[$count][] = $this->getAdapter()->quoteInto('m.name like ?', "%" . ucfirst($s) . "%");
            $where[$count++][] = $this->getAdapter()->quoteInto('m.description like ?', "%" . ucfirst($s) . "%");
        }

        if (count($where) > 0) {
            foreach ($where as $w) {
                $select->where(implode(' or ', $w));
            }
        }

        $select->where('m.restaurantId=?', $this->getId());
        $select->order('m.name');
        $select->group('m.id');

        $hash = md5($select->__toString());
        $result = Default_Helpers_Cache::load($hash);
        if ($result) {
            return $result;
        }

        $result = $this->getAdapter()->query($select)->fetchAll();
        Default_Helpers_Cache::store($hash, $result);
        return $result;
    }

    /*
     * find all service that active / inactive.
     * @return array restaurant
     */

    public function findAllService() {

        $sql = "select * from restaurants r, restaurant_servicetype rs where r.deleted = 0 and r.id= rs.restaurantId and rs.servicetypeId = 1 ";
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * update status of restaurant on field exclude ( 1 : exclude in comparison ; 0: include in comparison)
     * @return update
     */

    public function updateExclude($id, $status) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('restaurants', array('exclude' => $status), 'restaurants.id = ' . $id);
    }

    /*
     * find restaurant that include for comparison based on PLZ
     * @return array restaurant
     */

    public function findIncludeByPlz($plz) {

        $sql = "select * from restaurants r, restaurant_servicetype rs where r.deleted = 0 and r.id= rs.restaurantId and rs.servicetypeId = 1 and r.exclude = '0' and r.plz ='" . $plz . "'";
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * find restaurant from each city based on time registered
     * @return array restaurant
     */

    public function findByTimeCity($city, $time) {
        $sql = "select * from restaurants r, orte o , restaurant_servicetype rs where r.id= rs.restaurantId and r.plz=o.plz and rs.servicetypeId = 1 and r.deleted = 0 and r.created <='" . $time . "' and o.ort='" . $city . "'";
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * find registered restaurant from each city between the given time
     * @return array restaurant
     */

    public function findByDiffCity($city, $starttime, $endtime) {
        $sql = "select * from restaurants r, orte o, restaurant_servicetype rs where r.id= rs.restaurantId and r.plz=o.plz and rs.servicetypeId = 1 and r.deleted = 0 and o.ort='" . $city . "' and r.created between " . $starttime . " AND " . $endtime;
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * find restaurant from each plz based on time registered
     * @return array restaurant
     */

    public function findByTimePlz($plz, $time) {

        $sql = "select * from restaurants r, restaurant_servicetype rs where r.id= rs.restaurantId and rs.servicetypeId = 1 and r.deleted = 0 and r.created <=  '" . $time . "' and r.plz = '" . $plz . "'";
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * find registered restaurant from each plz between the given time
     * @return array restaurant
     */

    public function findByDiffPlz($plz, $starttime, $endtime) {

        $sql = "select * from restaurants r, restaurant_servicetype rs where r.id= rs.restaurantId and rs.servicetypeId = 1 and r.deleted = 0 and r.created between " . $starttime . " AND " . $endtime . " and r.plz = '" . $plz . "'";
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * find restaurant that excluded in comparison
     * @return array restaurant
     */

    public function findExcludeByPlz($plz) {

        $sql = "select * from restaurants r, restaurant_servicetype rs where r.id= rs.restaurantId and rs.servicetypeId = 1 and r.deleted = 0 and r.exclude = '1' and r.plz = '" . $plz . "'";
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * find restaurant based on PLZ
     * @return array restaurant
     */

    public function findByPlz($plz) {

        $sql = "select * from restaurants r, restaurant_servicetype rs where r.id= rs.restaurantId and rs.servicetypeId = 1 and r.deleted = 0 and r.plz = '" . $plz . "'";
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /**
     * get billing children of this restaurant
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getBillingChildren() {
        $sql = "select child as childId from billing_merge where kind = 'rest' and parent = " . $this->getId();
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * find restaurant that included on comparison based on city
     * @return array restaurant
     */

    public function findIncludeByCity($city) {
        $sql = "select * from restaurants r, orte o, restaurant_servicetype rs where r.id = rs.restaurantId and r.plz=o.plz and rs.servicetypeId = 1 and r.deleted = 0 and r.exclude = '0' and o.ort='" . $city . "'";
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * find restaurant Excluded from comparison based on city
     * @return array restaurant
     */

    public function findExcludeByCity($city) {
        $sql = "SELECT * from restaurants r, orte o, restaurant_servicetype rs where r.id=rs.restaurantId and o.ort= '" . $city . "' and o.plz = r.plz and r.deleted= 0 and rs.servicetypeId= 1 and r.exclude='1'";
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * find restaurant based on city
     * @return array restaurant
     */

    public function findByCity($city) {
        $sql = "SELECT * from restaurants r, orte o, restaurant_servicetype rs where r.id=rs.restaurantId and o.ort= '" . $city . "' and o.plz = r.plz and r.deleted= 0 and rs.servicetypeId= 1";
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * get the top number of restaurant based on each PLZ
     * @return array restaurant
     */

    public function getTopByPlz($limit) {
        $sql = "SELECT o.ort, count( r.id ) AS count, o.plz AS Plz FROM orte o
                LEFT JOIN restaurants r ON o.plz = r.plz and r.deleted= 0
                LEFT JOIN restaurant_servicetype rs ON r.id = rs.restaurantId and rs.servicetypeId = 1 GROUP BY o.plz ORDER BY count DESC Limit 0 ," . $limit;
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /*
     * get the top number of restaurant based on each CITY
     * @return array restaurant
     */

    public function getTopByCity($limit) {
        $sql = "SELECT o.ort, count( r.id ) AS count, o.plz AS Plz FROM orte o
                LEFT JOIN restaurants r ON o.plz = r.plz and r.deleted= 0
                LEFT JOIN restaurant_servicetype rs  ON r.id = rs.restaurantId and rs.servicetypeId = 1 GROUP BY o.ort ORDER BY count DESC Limit 0 ," . $limit;
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /**
     * check for categorys of type restaurant
     * @return boolean
     */
    public function isRestaurant() {
        if (is_null($this->getId())) {
            return false;
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $row = $db->fetchRow("SELECT count(`id`) AS `count`
                                FROM `restaurant_servicetype`
                                WHERE servicetypeId = 1
                                    AND restaurantId = ?
                                LIMIT 1", $this->getId());
        return $row['count'] > 0;
    }

    /**
     * check for categorys of type catering
     * @return boolean
     */
    public function isCatering() {
        if (is_null($this->getId())) {
            return false;
        }
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $row = $db->fetchRow("SELECT count(`id`) AS `count`
                                FROM `restaurant_servicetype`
                                WHERE servicetypeId = 2
                                    AND restaurantId = ?
                                LIMIT 1", $this->getId());
        return $row['count'] > 0;
    }

    /**
     * check for categorys of type great
     * @return boolean
     */
    public function isGreat() {
        if (is_null($this->getId())) {
            return false;
        }
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $row = $db->fetchRow("SELECT count(`id`) AS `count`
                                FROM `restaurant_servicetype`
                                WHERE servicetypeId = 3
                                    AND restaurantId = ?
                                LIMIT 1", $this->getId());
        return $row['count'] > 0;
    }

    /**
     * check for categorys of type fruit
     * @return boolean
     */
    public function isFruit() {
        if (is_null($this->getId())) {
            return false;
        }
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $row = $db->fetchRow("SELECT count(`id`) AS `count`
                                FROM `restaurant_servicetype`
                                WHERE servicetypeId = 4
                                    AND restaurantId = ?
                                LIMIT 1", $this->getId());
        return $row['count'] > 0;
    }

    /**
     * check restaurant bilder kategorie
     */
    public function checkBilderKategorie($resId) {
        $sql = 'SELECT mc.id,mc.name,mc.description,mc.categoryPictureId
                FROM meals m, meal_categories mc
                WHERE m.restaurantId = ' . $resId . '
                AND mc.id = m.categoryId
                GROUP BY m.categoryId';
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /**
     * get restaurants by CategoryId
     * @param integer $catId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public static function findByRestaurantCategoryIdAndPlz($catId = null, $plz = null) {
        if (is_null($catId)) {
            return null;
        }
        $addition = '';
        if (!is_null($plz)) {
            $addition = 'AND p.plz = ' . $plz;
        }

        $sql = sprintf("SELECT r.id FROM restaurants r JOIN restaurant_plz p ON r.id=p.restaurantId WHERE r.categoryId = %d AND r.deleted = 0 AND r.isOnline = 0 AND r.status = 0 " . $addition . " GROUP BY r.id", $catId);
        $db = Zend_Registry::get('dbAdapter');
        return $db->query($sql)->fetchAll();
    }

    /**
     * @author mlaug
     * @return int
     */
    public function getBalanceOfBillings() {
        //open bill amount
        $sql = "SELECT sum(amount) as amount,sum(voucher) as voucher FROM billing WHERE refId=? AND mode='rest' AND status=0";
        $unsend = $this->getAdapter()->fetchRow($sql, array($this->getId()));
        $sql = "SELECT sum(amount) as amount,sum(voucher) as voucher FROM billing WHERE refId=? AND mode='rest' AND status=1";
        $notpayed = $this->getAdapter()->fetchRow($sql, array($this->getId()));
        $sql = "SELECT sum(amount) as amount,sum(voucher) as voucher FROM billing WHERE refId=? AND mode='rest' AND status=2";
        $payed = $this->getAdapter()->fetchRow($sql, array($this->getId()));

        return array(
            $unsend,
            $notpayed,
            $payed
        );
    }

    /**
     * get rights
     * @return Zend_Db_Table_Rowset
     */
    public function getAdmins() {
        $sql = sprintf("select distinct(customerId) from user_rights where refId=%d and kind='r'", $this->getId());
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * get free slots if this feature is used
     * sometimes we do want to open restaurants only for a certain amount of orders
     * @author mlaug
     * @since 24.09.2010
     * @param int $slotPeriod
     * @return int
     */
    public function getUsedSlots($slotPeriod = 15) {
        $sql = sprintf('SELECT count(*) FROM `orders` WHERE (restaurantId=%d) AND (deliverTime > date_sub(now(),interval %d minute))', $this->getId(), $slotPeriod);
        return $this->getAdapter()->fetchOne($sql);
    }

    /**
     * Remove all tags for this restaurant
     * @author alex
     * @since 05.10.2010
     */
    public function removeAllTags() {
        $id = $this->getId();
        if ($id === null) {
            return;
        }

        $sql = sprintf("delete from restaurant_tags where restaurantId=%d", $this->getId());
        $this->getAdapter()->query($sql);
    }

    /**
     * Set new tag for this restaurant
     * 
     * @param intger $tagId 
     * 
     * @return void
     * 
     * @author alex
     * @since 05.10.2010
     */
    public function addTag($tagId) {
        $id = $this->getId();
        if ($id === null) {
            return;
        }

        $table = new Yourdelivery_Model_DbTable_Restaurant_Tag();
        $row = $table->createRow();
        $row->restaurantId = $id;
        $row->tagId = $tagId;
        $row->save();

        $cacheTag = sprintf('serviceTags%d', $id);
        Default_Helpers_Cache::remove($cacheTag);
    }

    /**
     * get all tags of this service
     * @author mlMattaug
     * @since 06.12.2010
     * @return array of tags
     */
    public function getTags() {

        $id = (integer) $this->getId();
        if ($id <= 0) {
            return array();
        }

        $cacheTag = sprintf('serviceTags%d', $id);
        $tags = Default_Helpers_Cache::load($cacheTag);

        if ($tags == null) {
            
            $db = Zend_Registry::get('dbAdapterReadOnly');
            $select = $db->select()
                    ->from(array('t' => 'tags'), array('tag' => 't.name'))
                    ->join(array('rt' => 'restaurant_tags'), "rt.tagId = t.id", array())
                    ->where("rt.restaurantId = ?", $id);

            $tags = $db->fetchAll($select);            
            Default_Helpers_Cache::store($cacheTag, $tags);
        }

        return $tags;
    }

    /**
     * get all restaurant tags and mark the tags which are set for this restaurant
     * @author Alex Vait
     * @since 16.08.2012
     */
    public function getAllTagsWithFlag() {
        $id = $this->getId();
        if ($id === null) {
            return null;
        }
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->from(array('t' => 'tags'))
                ->joinLeft(array('rt' => 'restaurant_tags'), "rt.tagId = t.id and rt.restaurantId = " . $this->getId(), array('tagAssoc' => 'rt.id'))
                ->limit($limit);

        return $db->fetchAll($select);
    }

    /**
     * get all meal categories servicetypes of this restaurant
     * @author alex
     * @since 12.10.2010
     */
    public function getMealCategoriesServicetypes() {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        try {
            $sql = sprintf("select distinct(servicetypeId) from servicetypes_meal_categorys_nn smc inner join meal_categories mc on smc.mealCategoryId=mc.id where mc.restaurantId=%d", $this->getId());
            $result = $db->fetchAll($sql);
        } catch (Zend_Db_Statement_Exception $e) {
            return 0;
        }

        return $result;
    }

    /**
     * Get all service types og this restaurant
     * @author alex
     * @since 12.01.2011
     * @return array
     */
    public function getAllServiceTypes() {
        return $this->getAdapter()->fetchAll("SELECT servicetypeId FROM restaurant_servicetype WHERE restaurantId = " . $this->getId());
    }

    /**
     * Get online restaurants without menu
     * @author alex
     * @since 27.01.2011
     * @return array
     */
    public static function getOnlineRestaurantsWithoutMenu() {
        $db = Zend_Registry::get('dbAdapter');

        $sql = "SELECT r.id, r.name, r.street, r.hausnr, r.plz FROM restaurants r LEFT JOIN meal_categories mc ON mc.restaurantId=r.id WHERE r.isOnline AND r.deleted=0 AND mc.id IS NULL";
        $result = $db->query($sql)->fetchAll();

        return $result;
    }

    /**
     * Get online restaurants without servicetype
     * @author alex
     * @since 27.01.2011
     * @return array
     */
    public static function getOnlineRestaurantsWithoutServicetype() {
        $db = Zend_Registry::get('dbAdapter');

        $sql = "SELECT r.id, r.name, r.street, r.hausnr, r.plz FROM restaurants r LEFT JOIN restaurant_servicetype rs ON rs.restaurantId=r.id WHERE r.isOnline AND r.deleted=0 AND rs.id IS NULL";
        $result = $db->query($sql)->fetchAll();

        return $result;
    }

    /**
     * Get online restaurants associated with servicetypes, nto havong mela categories of this servicetype
     * @author alex
     * @since 27.01.2011
     * @return array
     */
    public static function getOnlineRestaurantsWithoutCorrespondingMealCategories() {
        $db = Zend_Registry::get('dbAdapter');

        $sql = "SELECT r.id, r.name, r.street, r.hausnr, r.plz, st.name as servicename FROM restaurant_servicetype rs
                        JOIN restaurants r ON r.id=rs.restaurantId
                        JOIN servicetypes st ON st.id=rs.servicetypeId
                            WHERE r.isOnline=1 AND r.deleted=0 AND rs.servicetypeId NOT IN (
                                SELECT DISTINCT(mst.servicetypeId) FROM servicetypes_meal_categorys_nn mst
                                        JOIN meal_categories mc ON mc.id=mst.mealCategoryId
                                            WHERE mc.restaurantId=rs.restaurantId)";
        $result = $db->query($sql)->fetchAll();

        return $result;
    }

    /**
     * Get online restaurants without opening times
     * @author alex
     * @since 09.02.2011
     * @return array
     */
    public static function getOnlineRestaurantsWithoutOpeningTimes() {
        $db = Zend_Registry::get('dbAdapter');

        $sql = "SELECT r.id, r.name, r.street, r.hausnr, r.plz FROM restaurants r LEFT JOIN restaurant_openings ro ON ro.restaurantId=r.id WHERE r.isOnline AND r.deleted=0 AND ro.id IS NULL";
        $result = $db->query($sql)->fetchAll();

        return $result;
    }

    /**
     * Get online restaurants without delivering plz
     * @author alex
     * @since 09.02.2011
     * @return array
     */
    public static function getOnlineRestaurantsWithoutDeliverPlz() {
        $db = Zend_Registry::get('dbAdapter');

        $sql = "SELECT r.id, r.name, r.street, r.hausnr, r.plz FROM restaurants r LEFT JOIN restaurant_plz rp ON rp.restaurantId=r.id WHERE r.isOnline AND r.deleted=0 AND rp.id IS NULL";
        $result = $db->query($sql)->fetchAll();

        return $result;
    }

    /**
     * how many meal categories of certain type are in this restaurant
     * @author alex
     * @since 09.02.2011
     */
    public function countMealCategoriesWithServiceType($serviceTypeId) {
        $id = $this->getId();
        if ($id === null) {
            return false;
        }

        $res = $this->getAdapter()
                ->fetchRow("SELECT count(`servicetypeId`) as stcount
                                    FROM meal_categories mc
                                        JOIN servicetypes_meal_categorys_nn smc ON smc.mealCategoryId=mc.id
                                            WHERE `restaurantId`=? and `servicetypeId`=? GROUP BY servicetypeId", array($this->getId(), $serviceTypeId));
        return $res['stcount'];
    }

    /**
     * reset opening time - for tests to work correctly
     * @author alex
     * @since 23.02.2011
     */
    public function resetOpening() {
        $this->_isOpen = null;
    }

    /**
     * TODO refactor for backend
     * get all restaurnts with certain offline status
     * @author alex
     * @since 14.07.2011
     * @return SplObjectStorage
     */
    public static function getAllByStatus($status) {
        $db = Zend_Registry::get('dbAdapter');

        $sql = sprintf("select id from restaurants where deleted=0 and status=%d", $status);
        return $db->fetchAll($sql);
    }

    /**
     * check if a list of restaurants is available. this includes the following cases
     * 1. has the service been set offline after the caching? remove from list
     * 2. the service is exclusively associated to a company and customer is not associated to that company? remove from list
     * 3. the company is set to allow only a certian set of services? remove all the others
     * @autjor mlaug
     * @since 24.08.2011
     * @param array $ids
     * @param integer $customerId
     * @param integer $companyId
     * @param string $mode
     * @param string $kind
     */
    public function checkForUnreachable($ids, $customerId = 0, $companyId = 0, $mode = 'rest', $kind = 'priv', $cityId = 0) {

        $notReachable = array();
        $notAvailable = array();

        // check for valid input
        if (!is_array($ids) || !count($ids)) {
            return $this->_formatReachable($notReachable, $notAvailable, $customerId, $companyId, $mode, $kind);
        }

        $con = Zend_Registry::get('dbAdapterReadOnly');

        // search for restaurants, which are not online
        if (count($ids) > 0) {
            $result = $con->fetchCol(
                    'SELECT r.id 
                FROM `restaurants` r 
                WHERE r.isOnline = 0 
                    AND r.id IN (' . implode(array_map('intval', $ids), ',') . ')');
            $notReachable = array_merge($notReachable, $result);
            $ids = array_diff($ids, $notReachable); // remove already found ids
        }

        // if the company only allows a certian list of services
        // we need to remove all but the one associated with the company, we are ignoring exclusive flag
        // here, since we expect the backoffice to associate correctly :)
        if ($kind == 'comp' && $companyId > 0 && !is_null($companyId)) {

            try {
                $company = new Yourdelivery_Model_Company($companyId);
                if ($company->getServiceListMode() == 1) {
                    $result = $con->fetchCol(
                            'SELECT rc.restaurantId
                        FROM `restaurant_company` rc 
                        WHERE rc.companyId = ?', $companyId);
                    $notReachable = array_merge($notReachable, array_diff($ids, $result));
                    return $this->_formatReachable($notReachable, $notAvailable, $customerId, $companyId, $mode, $kind);
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }

            if (count($ids) > 0) {
                // add all restaurants, that do not allow online payment
                $result = $con->fetchCol(
                        'SELECT r.id 
                    FROM `restaurants` r
                    WHERE r.onlycash = 1 
                        AND r.id IN (' . implode(array_map('intval', $ids), ',') . ')');
                $notReachable = array_merge($notReachable, $result);
                $ids = array_diff($ids, $notReachable); // remove already found ids
            }

            // remove all services that only deliver to a certain children
            if ($cityId > 0 && count($ids) > 0) {
                $result = $con->fetchCol($con->select()
                                ->from(array('r' => 'restaurants'), array("DISTINCT(r.id)"))
                                ->join(array("rp" => "restaurant_plz"), "rp.restaurantId = r.id", array())
                                ->join(array("c" => "city"), "c.id = rp.cityId", array())
                                ->where("c.parentCityId = ?", $cityId)
                                ->where("r.id NOT IN (SELECT restaurantId FROM restaurant_plz WHERE cityId = ?)", $cityId)
                                ->where("r.id IN (?)", $ids));
                $notReachable = array_merge($notReachable, $result);
                $ids = array_diff($ids, $notReachable); // remove already found ids
            }
        }

        // check for any exclusive assications
        if (count($ids) > 0) {
            $result = $con->fetchAll(
                    'SELECT rc.restaurantId, rc.companyId 
                FROM `restaurant_company` rc
                WHERE rc.exclusive = 1 
                    AND rc.restaurantId IN (' . implode(array_map('intval', $ids), ',') . ')');
            foreach ($result as $r) {
                $add = true;
                // only if the kind is "comp" we check,
                // if the customer is still allowed
                if ($kind == 'comp') {
                    foreach ($result as $s) {
                        if ($s['companyId'] == $companyId) {
                            $add = false;
                            break;
                        }
                    }
                }
                $add ? $notReachable[] = $r['restaurantId'] : null;
            }
            $ids = array_diff($ids, $notReachable); // remove already found ids
        }

        // remove restaurant where the printer is offline
        if (count($ids) > 0) {
            $result = $con->fetchCol(
                    "SELECT r.id
                FROM `restaurants` r
                INNER JOIN `restaurant_printer_topup` rpt ON r.id = rpt.restaurantId
                INNER JOIN `printer_topup` pt ON rpt.printerId = pt.id
                WHERE r.notify IN ('sms', 'smsemail') 
                    AND (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(pt.updated)) >= 360
                    AND r.id IN (" . implode(array_map('intval', $ids), ',') . ")");
            $notAvailable = array_merge($notAvailable, $result);
            $ids = array_diff($ids, $notAvailable); // remove already found ids
        }

        return $this->_formatReachable($notReachable, $notAvailable, $customerId, $companyId, $mode, $kind);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @return type
     * @throws Yourdelivery_Exception_Database_Inconsistency @auth
     */
    private function _formatReachable($notReachable, $notAvailable, $customerId, $companyId, $mode, $kind) {
        try {
            if (!$customerId || !$companyId) {
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }

            $customer = new Yourdelivery_Model_Customer_Company($customerId, $companyId);

            return array(
                'notReachable' => $notReachable,
                'notAvailable' => $notAvailable,
                'permission' => array(
                    'employee' => $customer->isEmployee() && $kind == 'comp',
                    'budget' => $customer->isEmployee() ? $customer->getCurrentBudget() : 0,
                    'cater' => $customer->isEmployee() ? $customer->allowCater() : false,
                    'great' => $customer->isEmployee() ? $customer->allowGreat() : false
                )
            );
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return array(
                'notReachable' => $notReachable,
                'notAvailable' => $notAvailable,
                'permission' => array(
                    'employee' => false,
                    'budget' => 0,
                    'cater' => false,
                    'great' => false
                )
            );
        }
    }

    /**
     * Get all top Ratings of service
     *
     * @param integer $limit
     *
     * @return array
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.01.2012, modified 15.02.2012
     *
     */
    public function getTopRatings($limit = null) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->from(array('rr' => 'restaurant_ratings'))
                ->where('rr.restaurantId = ?', $this->getId())
                ->where('status = 1')
                ->where('topRating = 1')
                ->where('LENGTH(comment) > 10')
                ->where('LENGTH(title) > 3')
                ->where('LENGTH(author) > 3')
                ->order('created DESC')
                ->limit($limit);

        $result = $db->fetchAll($select);
        return $result;
    }

    /**
     * gets the next deliver time
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.03.2012
     * @param integer $curdate
     * @param integer $curtime
     * @return array
     */
    public function getNextDeliverTime($curdate, $curtime, $recursionCount = 0) {
        if ($recursionCount >= 2) {
            return strtotime('01.01.2020 10 pm');
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');

        //check if closed, then set $curdate to next day
        $selectSpecialClosed = $db
                ->select()
                ->from(array("ros" => "restaurant_openings_special"))
                ->where("closed = 1")
                ->where("ros.restaurantId = ?", $this->getId())
                ->order('specialDate ASC');

        $specialRowClosed = $db->fetchAll($selectSpecialClosed);
        
        foreach ($specialRowClosed as $closed) {
            if ($closed['specialDate'] == $curdate) {
                $curdate = date("Y-m-d", strtotime($curdate . " +1 day"));
            }
        }

        $selectSpecial = $db
                ->select()
                ->from(array("ros" => "restaurant_openings_special"), array(
                    "nextopening" => new Zend_Db_Expr(sprintf("UNIX_TIMESTAMP(CONCAT('%s', ' ', ros.from))", $curdate)),
                    "openInTime" => new Zend_Db_Expr(sprintf("IF('%s' BETWEEN ros.from AND ros.until, 1, 0)", $curtime)),
                    "closed" => 'ros.closed'
                        )
                )
                ->where("ros.specialDate= ? AND closed = 0", $curdate)
                ->where("ros.restaurantId = ?", $this->getId());
        
        $specialRows = $db->fetchAll($selectSpecial);
        
        if (!is_null($specialRows)) {
            foreach ($specialRows as $sr) {
                if ((strcmp($sr['openInTime'], '1') == 0) || ($sr['closed']==0)) {
                    return $sr;
                } else {
                    $curdate = date("Y-m-d", strtotime($curdate . " +1 day"));
                    return $this->getNextDeliverTime($curdate, $curtime, ++$recursionCount);
                }
            }
        }

        $selectHoliday = $db
                ->select()
                ->from(array("roh" => "restaurant_openings_holidays"), array(
                    "nextopening" => new Zend_Db_Expr(sprintf("UNIX_TIMESTAMP(CONCAT('%s', ' ', ro.from))", $curdate))
                        )
                )
                ->join(array("c" => "city"), "c.stateId=roh.stateId", array())
                ->join(array("r" => "restaurants"), "r.cityId=c.id", array())
                ->join(array("ro" => "restaurant_openings"), "ro.restaurantId=r.id", array())
                ->where("roh.date= ? ", $curdate)
                ->where("ro.`day`=10")
                ->where("ro.`from` > CURTIME()")
                ->where("r.id =  ?", $this->getId());

        $holidayRow = $db->fetchAll($selectHoliday);

        if (intval($holidayRow[0]) != 0) {
            return $holidayRow[0];
        }

        // select next possible deliver time after "timestamp" parameter
        // don't try to understand it
        $select = $db->select()->from(array('ro' => 'restaurant_openings'), array('nextopening' => new Zend_Db_Expr("UNIX_TIMESTAMP(CONCAT(DATE_ADD('" . $curdate . "', INTERVAL ( ro.day + 6 - WEEKDAY('" . $curdate . "'))%7 DAY), '  ', IF ( ('" . $curtime . "' > ro.`from`) AND ((WEEKDAY('" . $curdate . "')+1)%7 = ro.day), '" . $curtime . "', ro.`from`)))"),
                    'intime' => new Zend_Db_Expr("(('" . $curtime . "' between ro.`from` AND ro.`until`) OR ('" . $curtime . "'<ro.`from`))"),
                    'daydiff' => new Zend_Db_Expr("(ro.day + 6 - WEEKDAY('" . $curdate . "'))%7")
                ))->where("ro.restaurantId= ? and ro.day<>10", $this->getId())
                ->having('(intime+daydiff) > 0')
                ->order('daydiff')
                ->order('ro.from')
                ->limit(1);

        return $db->fetchRow($select);
    }

    /**
     * check if service has holiday at this day
     * @author alex
     * @since 02.12.2010
     * @param $date - date in sql format, e.g '2010-12-02'
     * @return boolean
     */
    public function holidayAtDate($date) {
        if (is_null($date) || is_null($this->getId())) {
            return false;
        }

        $row = $this->getAdapter()->fetchRow("SELECT COUNT(DISTINCT(ro.id)) AS count
            FROM restaurant_openings_holidays ro
            JOIN city c ON c.stateId=ro.stateId
            JOIN restaurants r ON r.cityId=c.id
            WHERE ro.date='" . $date . "' AND r.id = " . $this->getId() . " LIMIT 1");
        return $row['count'] > 0;
    }

    /**
     * 
     * get deliver ranges of this restaurant, just entires in restaurant_plz, without children
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 05.04.2012
     * @return array
     */
    public function getPlainDeliverRanges() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->from(array('rp' => 'restaurant_plz'))
                ->where('rp.restaurantId = ?', $this->getId());

        return $db->fetchAll($select);
    }
    
    /**
     *  @author Daniel Hahn <hahn@lieferando.de>
     * @param type $secure
     * @return array
     */
    public static function findBySecure($secure) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("r" => "restaurants"))
                ->where(sprintf('MD5(CONCAT(id,"%s")) = ? AND deleted=0', SALT), $secure);

        return $db->fetchRow($query);
    }

}
