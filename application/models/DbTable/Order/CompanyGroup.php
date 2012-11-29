<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_OrderCompanyGroup.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Order_CompanyGroup extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'order_company_group';

    protected $_referenceMap    = array(
        'Order' => array(
            'columns'           => 'orderId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Order',
            'refColumns'        => 'id'
        ),
        'Customer' => array(
            'columns'           => 'customerId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Customer',
            'refColumns'        => 'id'
        ),
        'Company' => array(
            'columns'           => 'companyId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Company',
            'refColumns'        => 'id'
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
    public static function edit($id, $data)
    {        
        $db = Zend_Registry::get('dbAdapter');
        $db->update('order_company_group', $data, 'order_company_group.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('order_company_group', 'order_company_group.id = ' . $id);
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
                    ->from( array("%ftable%" => "order_company_group") );
                    
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
                    ->from( array("o" => "order_company_group") )                           
                    ->where( "o.id = " . $id );

        return $db->fetchRow($query); 
    }
    
    /**
     * get a row matching OrderId by given value
     * @param int $orderId
     */
    public static function findByOrderId($orderId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "order_company_group") )                           
                    ->where( "o.orderId = " . $orderId );

        return $db->fetchRow($query); 
    }
    
    
    /**
     * get all rows matching OrderId by given value
     * @param int $orderId
     */
    public static function findAllByOrderId($orderId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "order_company_group") )                           
                    ->where( "o.orderId = " . $orderId );

        return $db->fetchAll($query); 
    }
        /**
     * get a rows matching CustomerId by given value
     * @param int $customerId
     */
    public static function findByCustomerId($customerId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "order_company_group") )                           
                    ->where( "o.customerId = " . $customerId );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Amount by given value
     * @param int $amount
     */
    public static function findByAmount($amount)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "order_company_group") )                           
                    ->where( "o.amount = " . $amount );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Code by given value
     * @param varchar $code
     */
    public static function findByCode($code)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "order_company_group") )                           
                    ->where( "o.code = " . $code );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching CompanyId by given value
     * @param int $companyId
     */
    public static function findByCompanyId($companyId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("o" => "order_company_group") )                           
                    ->where( "o.companyId = " . $companyId );

        return $db->fetchRow($query); 
    }
        
    
}
