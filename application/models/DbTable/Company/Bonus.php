<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_CompanyBonus.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Company_Bonus extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'company_bonus';

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
        $db->update('company_bonus', $data, 'company_bonus.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('company_bonus', 'company_bonus.id = ' . $id);
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
                    ->from( array("%ftable%" => "company_bonus") );
                    
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
                    ->from( array("c" => "company_bonus") )                           
                    ->where( "c.id = " . $id );

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
                    ->from( array("c" => "company_bonus") )                           
                    ->where( "c.customerId = " . $customerId );

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
                    ->from( array("c" => "company_bonus") )                           
                    ->where( "c.companyId = " . $companyId );

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
                    ->from( array("c" => "company_bonus") )                           
                    ->where( "c.kind = " . $kind );

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
                    ->from( array("c" => "company_bonus") )                           
                    ->where( "c.amount = " . $amount );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Absused by given value
     * @param int $absused
     */
    public static function findByAbsused($absused)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "company_bonus") )                           
                    ->where( "c.absused = " . $absused );

        return $db->fetchRow($query); 
    }
    
    
}
