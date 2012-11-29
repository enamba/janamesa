<?php

/**
 * Description of Cms
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Locations extends Default_Model_DbTable_Base {

    protected $_name = "locations";
    protected $_dependentTables = array('Yourdelivery_Model_DbTable_Orte');

    /**
     * the reference array to map on dependent tables
     * @var array
     */
    protected $_referenceMap = array(
        'Customer' => array(
            'columns' => 'customerId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Customer',
            'refColumns' => 'id'
        ),
        'Company' => array(
            'columns' => 'companyId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Company',
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
        $db->update('locations', $data, 'locations.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        return $db->update('locations', array('deleted' => true), 'locations.id = ' . $id);
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
                ->from(array("%ftable%" => "locations"));

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
                ->from(array("c" => "locations"))
                ->where("c.id = " . $id);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching CustomerId by given value
     * @param int $customerId
     */
    public static function findByCustomerId($customerId) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "locations"))
                ->where("c.customerId = " . $customerId);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Street by given value
     * @param varchar $street
     */
    public static function findByStreet($street) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "locations"))
                ->where("c.street = " . $street);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Hausnr by given value
     * @param varchar $hausnr
     */
    public static function findByHausnr($hausnr) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "locations"))
                ->where("c.hausnr = " . $hausnr);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Plz by given value
     * @param int $plz
     */
    public static function findByPlz($plz) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "locations"))
                ->where("c.plz = " . $plz);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Home by given value
     * @param tinyint $home
     */
    public static function findByHome($home) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "locations"))
                ->where("c.home = " . $home);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Tel by given value
     * @param varchar $tel
     */
    public static function findByTel($tel) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "locations"))
                ->where("c.tel = " . $tel);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Comment by given value
     * @param text $comment
     */
    public static function findByComment($comment) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "locations"))
                ->where("c.comment = " . $comment);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Latitude by given value
     * @param float(10,7) $latitude
     */
    public static function findByLatitude($latitude) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "locations"))
                ->where("c.latitude = " . $latitude);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Longitude by given value
     * @param float(10,7) $longitude
     */
    public static function findByLongitude($longitude) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "locations"))
                ->where("c.longitude = " . $longitude);

        return $db->fetchRow($query);
    }

    /**
     * get plz (primary key) of orte
     * @return int
     */
    public function getOrt() {
        if (is_null($this->getId())) {
            return null;
        }
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Orte')->current()->plz;
    }

    /**
     * Get best services, but only restaurant
     * @author Vincent Priem <priem@lieferando.de>
     * @since 06.02.2012
     * @param int $count
     * @param int $plz
     * @return array
     */
    public function getBestServices($count, $plz) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $select = $db->select()
                ->from(array('r' => "restaurants"), array(
                    "r.name", "r.restUrl", 'rating' => new Zend_Db_Expr("r.ratingQuality + r.ratingDelivery")))
                ->join(array('rs' => "restaurant_servicetype"), "rs.restaurantId = r.id")
                ->join(array('rp' => "restaurant_plz"), "rp.restaurantId = r.id")
                ->join(array('rr' => "restaurant_ratings"), "rr.restaurantId = r.id", array(
                    "rr.restaurantId",
                    'ratingCount' => new Zend_Db_Expr("COUNT(DISTINCT rr.id)")))
                ->where("rs.servicetypeId = 1")
                ->where("rp.plz = ?", $plz)
                ->where("rr.status = 1")
                ->where("r.isOnline = 1")
                ->where('rr.created > ?', date('Y-m-d', strtotime('-1 month')))
                ->group("r.id")
                ->having('ratingCount >= 10')
                ->order(array("rating DESC", "ratingCount DESC"))
                ->limit($count);        

        return $db->fetchAll($select);
    }

    /**
     * mark all addresses as NOT primary for this customer
     * make sure, we do not touch any company addresses
     * @author mlaug
     * @since 10.11.2011
     * @param integer $customerId 
     */
    public function resetPrimaryAddress($customerId) {
        $this->getAdapter()->query('UPDATE locations SET `primary`=0 WHERE customerId=? AND (companyId=0 OR companyId is NULL)', $customerId);
    }

}
