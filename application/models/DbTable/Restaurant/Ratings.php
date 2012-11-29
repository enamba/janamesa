<?php

/**
 * @author Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Model_DbTable_Restaurant_Ratings extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'restaurant_ratings';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * @var array 
     */
    protected $_referenceMap = array(
        'Order' => array(
            'columns' => 'orderId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Order',
            'refColumns' => 'id'
        )
    );

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.05.2012
     * @param integer $customerId
     * @return array 
     */
    public function getListFromCustomer($customerId) {
        return $this->select()
                        ->where('customerId=? and customerId is not NULL', (integer) $customerId)
                        ->query()
                        ->fetchAll();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.05.2012
     * @param integer $customerId
     * @return array 
     */
    public function getListFromService($serviceId, $onlyConfirmed = false, $withComment = false, $lastThirtyDays = false, $limit = null) {
        $select = $this->getAdapter()->select()
                ->from(array('rr' => 'restaurant_ratings'))
                ->joinLeft(array('c' => 'customers'), 'c.id=rr.customerId', array('image' => 'c.id'))
                ->where('rr.restaurantId = ?', (integer) $serviceId)
                ->where('restaurantId is not NULL')
                ->order('rr.created DESC');

        if($onlyConfirmed){
            $select->where('rr.status = 1');
        }
        
        if($withComment){
            $select->where('LENGTH(rr.comment) > 5');
        }
        
        /*if ( $lastThirtyDays ){
            $select->where('rr.created > DATE_SUB(NOW(),INTERVAL 30 DAY)')->query();
        }*/
        
        if ($limit) {
            $select->limit($limit);
        }
        
        return $select->query()->fetchAll();
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
        $db->update('restaurant_ratings', $data, 'restaurant_ratings.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('restaurant_ratings', array('status' => '-2'), 'restaurant_ratings.id = ' . $id);
        //$db->delete('restaurant_ratings', 'restaurant_ratings.id = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order = null, $limit = 0, $from = 0) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("%ftable%" => "restaurant_ratings"));

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
                ->from(array("r" => "restaurant_ratings"))
                ->where("r.id = " . $id);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching OrderId by given value
     * @param int $orderId
     */
    public static function findByOrderId($orderId) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("r" => "restaurant_ratings"))
                ->where("r.orderId = " . $orderId);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Quality by given value
     * @param int $quality
     */
    public static function findByQuality($quality) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("r" => "restaurant_ratings"))
                ->where("r.quality = " . $quality);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Delivery by given value
     * @param int $delivery
     */
    public static function findByDelivery($delivery) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("r" => "restaurant_ratings"))
                ->where("r.delivery = " . $delivery);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching CustomerId by given value
     * @param int $customerId
     */
    public static function findByCustomerId($customerId) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("r" => "restaurant_ratings"))
                ->where("r.customerId = " . $customerId);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Comment by given value
     * @param text $comment
     */
    public static function findByComment($comment) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("r" => "restaurant_ratings"))
                ->where("r.comment = " . $comment);

        return $db->fetchRow($query);
    }

    /**
     * get all ratings in certain time slot, with specified advise state
     * @author Alex Vait <vait@lieferando.de>
     * @since 09.01.2012
     * @param date $from 
     * @param date $until
     * @param int $advise 0 - negativ, 1 - positiv, 2 - all
     */
    public static function getRatingsInTimeslot($from, $until, $advise) {
        $adviseSql = "";

        $db = Zend_Registry::get('dbAdapterReadOnly');

        if ($advise != 2) {
            $adviseSql = " and advise = " . $advise;
        }

        $query = $db->select()
                ->from(array("r" => "restaurant_ratings"))
                ->where("status=0 and r.created between '" . $from . " 00:00:00' and '" . $until . " 23:59:59' " . $adviseSql);

        return $db->fetchAll($query);
    }

}
