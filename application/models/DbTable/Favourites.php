<?php

/**
 * Database interface for Yourdelivery_Models_DbTable_Favourites.
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Favourites extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'favourites';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * edit favorite
     * 
     * @param integer $id   row id to update 
     * @param array   $data data to update
     * 
     * @return void
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 06.05.2010
     */
    public static function edit($id, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('favourites', $data, '`id` = ' . ((integer) $id));
    }

    /**
     * Delete a table row by given primary key
     * 
     * @param integer $id row id to remove
     * 
     * @return void
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 06.05.2010
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('favourites', '`id` = ' . ((integer) $id));
    }

    /**
     * get rows
     * 
     * @param string  $order addition to order result
     * @param integer $limit limit for result
     * @param string  $from  offset
     * 
     * @return Zend_DB_Table_Rowset
     */
    public static function get($order = null, $limit = 0, $from = 0) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("%ftable%" => "favourites"));

        if ($order != null) {
            $query->order($order);
        }

        if ($limit != 0) {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
    }

    /**
     * find row by id
     * 
     * @param integer $id id to search for
     * 
     * @return array
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 06.05.2011
     */
    public static function findById($id) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("f" => "favourites"))
                ->where("f.id = ?", $id);

        return $db->fetchRow($query);
    }

    /**
     * Get a rows matching CustomerId by given value
     * 
     * @param integer $customerId id of customer
     * 
     * @return array
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 06.05.2011
     */
    public static function findByCustomerId($customerId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("f" => "favourites"))
                ->where("f.customerId = ?", $customerId);

        return $db->fetchRow($query);
    }

    /**
     * Get a rows matching OrderId by given value
     * 
     * @param integer $orderId id of order
     * 
     * @return array
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 06.05.2011
     */
    public static function findByOrderId($orderId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("f" => "favourites"))
                ->where("f.orderId = ?", $orderId);

        return $db->fetchRow($query);
    }

    /**
     * find favorite by order and customer
     * 
     * @param integer $orderId    id of order
     * @param integer $customerId id of customer
     * 
     * @return array 
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 18.11.2011
     */
    public static function findByOrderAndCustomerId($orderId, $customerId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("f" => "favourites"))
                ->where("f.orderId = ?", $orderId)
                ->where('f.customerId = ?', $customerId);

        return $db->fetchRow($query);
    }

    /**
     * Get a rows matching Name by given value
     * 
     * @param string $name name to search for
     * 
     * @return array
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 06.05.2011
     */
    public static function findByName($name) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("f" => "favourites"))
                ->where("f.name = ?", $name);

        return $db->fetchRow($query);
    }

    //allens stuff...
    /**
     * Get all restaurant ids
     * 
     * @param integer $id    id of favorite
     * @param integer $limit limit of result 
     * 
     * @return Zend_Db_Table_Rowset
     * 
     * @author Allen Frank <frank@lieferando.de>
     */
    public static function getAllRestaurantIds($id, $limit = false) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array("f" => "favourites"), array())
                ->joinLeft(array('o' => "orders"), "f.orderId = o.id", array())
                ->joinLeft(array('ol' => "orders_location"), "o.id = ol.orderId", array('cityId'))
                ->joinLeft(array('r' => 'restaurants'), 'r.id = o.restaurantId', array('id'))
                ->where("f.customerId = ?", $id)
                ->where("r.deleted = 0")
                ->where("r.status IN (0, 2, 3, 4, 5, 6, 7, 10, 14, 15, 16, 17, 18, 20, 21, 24)")
                ->group('r.id');

        if ($limit && is_integer($limit) && $limit > 0) {
            $query->limit($limit);
        }

        $restaurants = $db->fetchAll($query);

        $services = array();

        foreach ($restaurants as $r) {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($r['id']);
            $service->setCurrentCityId($r['cityId']);
            $services[] = $service;
        }

        return $services;
    }

    /**
     * remove all favorites from customer
     * 
     * @param integer $restaurantId id of restaurant
     * @param integer $customerId   id of customer
     * 
     * @return integer
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 22.11.2011
     * 
     * @todo refactor in model customer (Felix Haferkorn, 13.12.2011)
     */
    public static function removeAll($restaurantId, $customerId) {
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                ->from(array("f" => "favourites"), array('id'))
                ->join(array('o' => "orders"), "f.orderId = o.id", array())
                ->join(array('r' => 'restaurants'), 'r.id = o.restaurantId', array())
                ->where('f.customerId = ?', $customerId)
                ->where('r.id = ?', $restaurantId);

        $restIds = $db->fetchAll($query);

        $ids = array();
        foreach ($restIds as $id) {
            $ids[] = $id['id'];
        }

        if (count($ids) > 0) {
            return $db->delete('favourites', '`id` IN (' . implode(',', $ids) . ')');
        }
    }

}
