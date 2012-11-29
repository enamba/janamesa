<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Orders
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Order_Storno extends Default_Model_DbTable_Base {

    protected $_name = "orders_storno";


    protected $_dependentTables = array(
                                        'Yourdelivery_Model_DbTable_Order_Favourites',
                                        'Yourdelivery_Model_DbTable_Restaurant_Ratings',
                                        'Yourdelivery_Model_DbTable_Order_Status'
                                       );

    protected $_referenceMap    = array(
        'Customer' => array(
            'columns'           => 'customerId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Customer',
            'refColumns'        => 'id'
        ),
        'Restaurant' => array(
            'columns'           => 'restaurantId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Restaurant',
            'refColumns'        => 'id'
        )
    );

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'rowId';

    /**
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public static function edit($id, $data)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('orders', $data, 'orders.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('orders', 'orders.id = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order=null, $limit=0, $from=0)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("%ftable%" => "orders") );

        if($order != null)
        {
            $query->order($order);
        }

        if($limit != 0)
        {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
    }

        /**
     * get a rows matching Id by given value
     * @param int $id
     */
    public static function findById($id)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.id = " . $id );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching Nr by given value
     * @param varchar $nr
     */
    public static function findByNr($nr)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.nr = " . $nr );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching RestaurantId by given value
     * @param int $restaurantId
     */
    public static function findByRestaurantId($restaurantId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.restaurantId = " . $restaurantId );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching CustomerId by given value
     * @param int $customerId
     */
    public static function findByCustomerId($customerId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.customerId = " . $customerId );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching Time by given value
     * @param int $time
     */
    public static function findByTime($time)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.time = " . $time );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching DeliverTime by given value
     * @param int $deliverTime
     */
    public static function findByDeliverTime($deliverTime)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.deliverTime = " . $deliverTime );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching Pickup by given value
     * @param tinyint $pickup
     */
    public static function findByPickup($pickup)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.pickup = " . $pickup );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching Kind by given value
     * @param varchar $kind
     */
    public static function findByKind($kind)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.kind = " . $kind );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching Temp by given value
     * @param int $temp
     */
    public static function findByTemp($temp)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.temp = " . $temp );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching OrderData by given value
     * @param text $orderData
     */
    public static function findByOrderData($orderData)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.orderData = " . $orderData );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching BillCompany by given value
     * @param int $billCompany
     */
    public static function findByBillCompany($billCompany)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.billCompany = " . $billCompany );

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching BillRest by given value
     * @param int $billRest
     */
    public static function findByBillRest($billRest)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.billRest = " . $billRest );

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching SentGroup by given value
     * @param tinyint $sentGroup
     */
    public static function findBySentGroup($sentGroup)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.sentGroup = " . $sentGroup );

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Pfand by given value
     * @param int $pfand
     */
    public static function findByPfand($pfand)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.pfand = " . $pfand );

        return $db->fetchRow($query);
    }

    /**
     * get status of order
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getState(){
        $states = $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_Status');
        $highest = 0;
        foreach($states AS $state) {
            if($state->time > $highest) {
                $toReturn = $state;
                $highest = $state->time;
            }
        }
        return $toReturn;
    }


    /**
     * get ratings of order
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRating(){
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Restaurant_Ratings');
    }

    /**
     * return favourite attributes
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getFavourite(){
        if ( !is_null($this->getId()) )
            return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_Favourites');
        return null;
    }

    /**
     * check if order is favourite
     * @return boolean
     */
    public function isFavourite(){
        if ( is_null($this->getId()) )
            return false;
        if ( $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Order_Favourites')->count() == 1){
            return true;
        }
        return false;
    }

    /**
     * get ordered restaurant
     * @deprecated use getService instead
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRestaurant(){
        if ( !is_null($this->getId()) )
            return $this->getCurrent()->findParentRow('Yourdelivery_Model_DbTable_Restaurant');
        return null;
    }

    /**
     * get ordered service
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getService(){
        if ( !is_null($this->getId()) )
            return $this->getCurrent()->findParentRow('Yourdelivery_Model_DbTable_Restaurant');
        return null;
    }

    /**
     * get the list of all distinct fields
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctFields($field = 'id'){
        $sql = sprintf('select distinct(' . $field . ') from orders_storno order by ' . $field);
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }
}
?>
