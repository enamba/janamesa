<?php

/**
 * Description of city table
 * @author Alex Vait <vait@lieferando.de>
 * @since 01.03.2011
 */
class Yourdelivery_Model_DbTable_City extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = "city";
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Regions',
        'Yourdelivery_Model_DbTable_Districts',
    );

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    protected $_referenceMap = array(
        'Customer_Locations' => array(
            'columns' => 'id',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Locations',
            'refColumns' => 'cityId'
        ),
        'Company_Addresses' => array(
            'columns' => 'id',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Company_Addresses',
            'refColumns' => 'cityId'
        ),
    );

    /**
     * get all entries from city table where plz is starting with the defined number
     *
     * @param string $startingWith city string to start with search
     * @param string $orderBy      addition to order result by something
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 13.04.2011
     *
     * @return array
     */
    public static function allStartingAt($startingWith, $orderBy) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAll('SELECT * FROM `city` WHERE `plz` LIKE "' . $startingWith . '%" ORDER BY ' . $orderBy);
    }

    /**
     * get all entries from city table where entries has the same plz as the given one, except this one
     *
     * @param integer $cityId  cityId to find possible parents for
     * @param string  $orderBy addition to order result by something
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 06.06.2011
     *
     * @return array
     */
    public static function possibleParentsForCityId($cityId, $orderBy) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAll('SELECT * FROM `city` WHERE parentCityId=0 AND `plz` = (select plz from city where id=' . $cityId . ')  and id <> ' . $cityId . ' AND (select count(id) from city where parentCityId=' . $cityId . ')=0 ORDER BY ' . $orderBy);
    }

    /**
     * get all entries from city table
     *
     * @param string $orderBy addition to order result by something
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 08.03.2011
     *
     * @return array
     */
    public static function all($orderBy = 'plz') {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAll('SELECT * FROM `city` ORDER BY ' . $orderBy);
    }

    /**
     * get all distinct plz
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.03.2011
     *
     * @return array
     */
    public static function allPlz() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAll("SELECT DISTINCT(`plz`) FROM `city`");
    }

    /**
     * get all distinct cities
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.03.2011
     *
     * @return array
     */
    public static function getAllCities() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchCol("SELECT DISTINCT(`city`) FROM `city` ORDER BY `city`");
    }

    /**
     * get all distinct cities by priority
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 18.04.2012
     *
     * @return array
     */
    public static function getAllCitiesByPriority($limit = 10) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->from(array('c' => 'city'), array(
                    'id' => 'c.id',
                    'city' => 'c.city',
                    'count' => new Zend_Db_Expr('(select count(*) from city_verbose cv where cv.cityId=c.id)')
                ))
                ->join(array('r' => 'restaurants'), 'c.id=r.cityId', array())
                ->group('c.city')
                ->order('count(*) DESC')
                ->limit($limit);
        return $select->query()->fetchAll();
    }

    /**
     * get all distinct states
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.03.2011
     *
     * @return array
     */
    public static function allStates() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAll("SELECT DISTINCT(`state`), stateId FROM `city` order by state");
    }

    /**
     * edit a city entry
     *
     * @param integer $id   id of city to edit
     * @param array   $data data to update
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.03.2011
     *
     * @return void
     */
    public static function edit($id, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('city', $data, 'city.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     *
     * @param integer $id id of city to remove
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.03.2011
     *
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('city', 'city.id = ' . $id);
    }

    /**
     * get rows
     *
     * @param string  $order  addition to order result by something
     * @param integer $limit  some result limit
     * @param string  $offset some result offset
     *
     * @author Alex Vait <vait@lieferando.de>
     *
     * @return Zend_DbTable_Rowset_Abstract
     */
    public static function get($order = null, $limit = 0, $offset = 0) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("%ftable%" => "city"));

        if ($order !== null) {
            $query->order($order);
        }

        if ($limit != 0) {
            $query->limit($limit, $offset);
        }

        return $db->fetchAll($query);
    }

    /**
     * get a rows matching plz
     *
     * @param string $plz plz to find city by
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.03.2011
     *
     * @return Zend_DbTable_Rowset_Abstract
     */
    public static function findByPlz($plz) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("o" => "city"))
                ->where("o.plz = '" . $plz . "'");
        return $db->fetchAll($query);
    }

    /**
     * get a rows matching city by given value
     *
     * @param string $city city to find entry by
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.03.2011
     *
     * @return Zend_DbTable_Rowset_Abstract
     */
    public static function findByCity($city) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("o" => "city"))
                ->where("o.city = '" . $city . "'");
        return $db->fetchAll($query);
    }

    /**
     * get a rows matching plz and city
     *
     * @param string $plz  plz to find city by
     * @param string $city city to find entry by
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 11.04.2011
     *
     * @return Zend_DbTable_Rowset_Abstract
     */
    public static function findByPlzAndCity($plz, $city) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("o" => "city"))
                ->where("o.plz = ?", $plz)
                ->where("o.city = ?", $city);
        return $db->fetchAll($query);
    }

    /**
     * get a rows matching state by given value
     *
     * @param string $state state to find cities by
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.03.2011
     *
     * @return Zend_DbTable_Rowset_Abstract
     */
    public static function findByState($state) {

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("o" => "city"))
                ->where("o.state = " . $state);
        return $db->fetchAll($query);
    }

    /**
     * Get all names of federal states
     *
     * @author Alex Vait <vait@lieferando.de>
     *
     * @return Zend_DbTable_Rowset_Abstract
     */
    public static function getAllStates() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()->distinct()
                ->from(array('c' => 'city'), array(
                    'c.stateId',
                    'c.state'
                ))
                ->where("LENGTH(c.state)>0")
                ->order('c.state');
        return $db->fetchAll($query);
    }

    /**
     * Full name - the name of this city or <name of parent> (<city name>)
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 05.08.2011
     *
     * @return string
     */
    public function getFullName() {

        return $this->getAdapter()
                        ->fetchOne(
                                "SELECT IF (c.parentCityId > 0, CONCAT(cp.city, ' (', c.city, ')'), c.city)
                        FROM city c
                        LEFT JOIN city cp ON c.parentCityId = cp.id
                        WHERE c.id = ?", $this->getId()
        );
    }

    /**
     * Get a row matching direct plz link
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.07.2011
     * @param string $uri URI to find city by
     * @return Zend_DbTable_Row_Abstract
     */
    public static function findByDirectLink($uri, $type = null) {

        if (empty($uri) || $uri == "/") {
            return null;
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');

        if (strcmp($type, 'cater')==0) {
            $query = $db->select()->from(array('c' => 'city'), array('c.id'))->where("c.caterUrl = ?", $uri);
        }
        else if (strcmp($type, 'great')==0) {
            $query = $db->select()->from(array('c' => 'city'), array('c.id'))->where("c.greatUrl = ?", $uri);
        }
        else {
            $query = $db->select()->from(array('c' => 'city'), array('c.id'))->where("c.restUrl = ?", $uri);
        }

        $row = $db->fetchRow($query);
        
        if ($row) {
            return $row;
        }

        return null;
    }

}
