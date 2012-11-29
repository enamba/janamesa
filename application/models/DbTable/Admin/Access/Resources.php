<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_Admin_Access_Resources.
 *
 * @copyright   Yourdelivery
 * @author	Jan Oliver Oelerich
*/

class Yourdelivery_Model_DbTable_Admin_Access_Resources extends Default_Model_DbTable_Base
{

    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Admin_Access_Rights'
    );
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'admin_access_resources';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * delete a table row by given primary key
     * 
     * @author Alex Vait
     * @since 29.08.2012
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('admin_access_resources', 'id = ' . $id);
    }
    
    /**
     * get the list of all distinct resources
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctId(){
        $sql = sprintf('select id, action, description from admin_access_resources order by action');
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }
}