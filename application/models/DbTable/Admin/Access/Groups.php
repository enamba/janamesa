<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_Admin_Access_Groups.
 *
 * @copyright   Yourdelivery
 * @author	Jan Oliver Oelerich
*/

class Yourdelivery_Model_DbTable_Admin_Access_Groups extends Default_Model_DbTable_Base
{

    protected $_referenceMap    = array(
        'User' => array(
            'columns'           => 'id',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Admin_Access_Users',
            'refColumns'        => 'groupId'
        )
    );
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Admin_Access_Rights'
    );
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'admin_access_groups';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * get the list of all distinct groups
     * @return Zend_Db_Table_Row_Abstract
     */
    public static function getDistinctId(){
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('select id, name from admin_access_groups order by name');
        $fields = $db->fetchAll($sql);
        return $fields;
    }

    /**
     * get the resources this group has aces to
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getResources() {
        $sql = sprintf('select resourceId from admin_access_rights where groupId=%d', $this->getId());
        $query = $this->getAdapter()->query($sql);
        return $query->fetchAll();
    }

    /**
     * add resource right to this group
     * @return Zend_Db_Table_Row_Abstract
     */
    public function addResource($resId) {
        $rightsTable = new Yourdelivery_Model_DbTable_Admin_Access_Rights();
        $rightsTable->insert(array('groupId' => $this->getId(), 'resourceId' => $resId));
    }

    /**
     * remove all resources right for this group
     * @return Zend_Db_Table_Row_Abstract
     */
    public function clearResources() {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('admin_access_rights', 'groupId = ' . $this->getId());
    }

    /**
     * get id of the group based on the name
     * @return int
     */
    public static function getGroupId($name) {
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('select id from admin_access_groups where name=\'%s\'', $name);
        $query = $db->query($sql);
        return $query->fetchColumn();
    }

    /**
     * get all admin groups
     * 
     * @author Alex Vait <priem@lieferando.de>
     * @updated 28.08.2012
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     */    
    public static function getAllGroups() {
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                ->from(array("agn" => 'admin_access_groups'))
                ->order("name");;
        return $db->fetchAll($query);
    }
    
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
        $db->delete('admin_access_groups', 'id = ' . $id);
    }    
    
}