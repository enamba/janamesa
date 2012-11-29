<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_Main.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Main extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'main';

    /**
     * primary key
     * @param string
     */
    protected $_primary = '';
    
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
        $db->update('main', $data, 'main. = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('main', 'main. = ' . $id);
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
                    ->from( array("%ftable%" => "main") );
                    
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
     * get a rows matching IsOnline by given value
     * @param tinyint $isOnline
     */
    public static function findByIsOnline($isOnline)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "main") )                           
                    ->where( "m.isOnline = " . $isOnline );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Contact by given value
     * @param varchar $contact
     */
    public static function findByContact($contact)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "main") )                           
                    ->where( "m.contact = " . $contact );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching OfflineMsg by given value
     * @param text $offlineMsg
     */
    public static function findByOfflineMsg($offlineMsg)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "main") )                           
                    ->where( "m.offlineMsg = " . $offlineMsg );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Url by given value
     * @param varchar $url
     */
    public static function findByUrl($url)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "main") )                           
                    ->where( "m.url = " . $url );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Root by given value
     * @param varchar $root
     */
    public static function findByRoot($root)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "main") )                           
                    ->where( "m.root = " . $root );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Default_lang by given value
     * @param varchar $default_lang
     */
    public static function findByDefault_lang($default_lang)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "main") )                           
                    ->where( "m.default_lang = " . $default_lang );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Errormail by given value
     * @param varchar $errormail
     */
    public static function findByErrormail($errormail)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "main") )                           
                    ->where( "m.errormail = " . $errormail );

        return $db->fetchRow($query); 
    }
    
    
}
