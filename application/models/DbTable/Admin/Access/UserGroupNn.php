<?php
/**
 * Database interface for Yourdelivery_Model_DbTable_Admin_Access_UserGroupNn.
 *
 * @author Alex Vait
 * @since 28.08.2012
*/

class Yourdelivery_Model_DbTable_Admin_Access_UserGroupNn extends Default_Model_DbTable_Base
{

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'admin_access_user_groups_nn';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    
    /**
     * delete all entries for specified user
     * 
     * @author Alex Vait <priem@lieferando.de>
     * @since 28.08.2012
     */
    public static function removeAllForUser($userId)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('admin_access_user_groups_nn', 'userId = ' . $userId);
    }    
}