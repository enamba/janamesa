<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_UserRights.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_UserRights extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'user_rights';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * the reference array to map on dependent tables
     * @var array
     */
    protected $_referenceMap    = array(
        'Customer' => array(
            'columns'           => 'customerId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Customer',
            'refColumns'        => 'id'
        ),
    );
    
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
        $db->update('user_rights', $data, 'user_rights.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('user_rights', 'user_rights.id = ' . $id);
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
                    ->from( array("%ftable%" => "user_rights") );
                    
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
                    ->from( array("u" => "user_rights") )                           
                    ->where( "u.id = " . $id );

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
                    ->from( array("u" => "user_rights") )                           
                    ->where( "u.customerId = " . $customerId );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Kind by given value
     * @param char $kind
     */
    public static function findByKind($kind)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("u" => "user_rights") )                           
                    ->where( "u.kind = " . $kind );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Status by given value
     * @param int $status
     */
    public static function findByStatus($status)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("u" => "user_rights") )                           
                    ->where( "u.status = " . $status );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching RefId by given value
     * @param int $refId
     */
    public static function findByRefId($refId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("u" => "user_rights") )                           
                    ->where( "u.refId = " . $refId );

        return $db->fetchRow($query); 
    }
    
    public function editByCustomerId($customerId, $data)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('user_rights', $data, 'user_rights.customerId = ' . $customerId);
    }

    public function removeByCustomerId($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('user_rights', 'user_rights.customerId = ' . $id);
    }
}
