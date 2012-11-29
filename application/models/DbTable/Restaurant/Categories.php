<?php

/**
 * DbTable Restaurant Categories
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Restaurant_Categories extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'restaurant_categories';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Dependent tables
     * @var string
     */
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Restaurant'
    );

    /**
     *
     * @param integer $id
     * @param array $data
     * @return void
     */
    public static function edit($id, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('restaurant_categories', $data, 'restaurant_categories.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_categories', 'restaurant_categories.id = ' . $id);
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
                ->from(array("%ftable%" => "restaurant_categories"));

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
                ->from(array("r" => "restaurant_categories"))
                ->where("r.id = ?", $id);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Name by given value
     * @param varchar $name
     */
    public static function findByName($name) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("r" => "restaurant_categories"))
                ->where("r.name = ?", $name);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Description by given value
     * @param text $description
     */
    public static function findByDescription($description) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("r" => "restaurant_categories"))
                ->where("r.description = ?", $description);

        return $db->fetchRow($query);
    }

    /**
     * Get all
     * @author vpriem
     * @since 21.03.2011
     * @return array
     */
    public static function getAll() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAll(
                        "SELECT *
            FROM `restaurant_categories`
            ORDER BY `name` ASC"
        );
    }

    /**
     * Get all categories by plz
     * @author vpriem
     * @since 21.03.2011
     * @param mixed array|integer $cityIds
     * @param int $servicetypeId
     * @return array
     */
    public function getCategoriesByCityId($cityIds, $servicetypeId = 1) {
        
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
                
        $in = array_filter(array_merge($cityIds, $parentCityIds, $childrenCityIds), function($item){ return $item > 0; });
        
        $select = $db
                ->select()
                ->from(array("r" => "restaurants"), array(
                    "r.name"
                ))
                ->join(array("rp" => "restaurant_plz"), "r.id = rp.restaurantId", array())
                ->join(array("c" => "restaurant_categories"), "r.categoryId = c.id", array(
                    "c.id", "c.name", "c.description", 'restcount' => new Zend_Db_Expr("COUNT(c.id)")
                ))
                ->join(array("st" => "restaurant_servicetype"), "r.id = st.restaurantId", array())
                ->where('rp.cityId IN (?)', array_unique($in))
                ->where("r.deleted = 0")
                ->where("r.isOnline = 1")
                ->where("st.servicetypeId = ?", $servicetypeId)
                ->group("c.id")
                ->order("c.name");

        return $db->fetchAll($select);
    }

}
