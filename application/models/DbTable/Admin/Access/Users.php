<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_Admin_Access_Users.
 *
 * @copyright   Yourdelivery
 * @author	Jan Oliver Oelerich
*/
class Yourdelivery_Model_DbTable_Admin_Access_Users extends Default_Model_DbTable_Base{

    protected $_dependentTables = array('Yourdelivery_Model_DbTable_Admin_Access_Groups');
    
    /**
     * Table name
     * @param string
     */
    protected $_name = 'admin_access_users';

    /**
     * Primary key name
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
        $db->update('admin_access_users', $data, $_name . '.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('admin_access_users', 'id = ' . $id);
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
                    ->from( 'admin_access_users' );

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
                    ->from( array("b" => 'admin_access_users') )
                    ->where( "b.id = " . $id );

        return $db->fetchRow($query);
    }
    
    /**
     * Get a rows matching email by given value
     * @author vpriem
     * @since 30.06.2011
     * @param string $email
     */
    public static function findByEmail($email) {
        $db = Zend_Registry::get('dbAdapter');

        return $db->fetchRow(
            "SELECT a.*
            FROM `admin_access_users` a
            WHERE a.email = ?", $email
        );
    }

    /**
     * Get the names of resources, this use has access to
     * @return array
     */
    public function getAccessResources(){
        $query = $this->getAdapter()
                    ->select()
                    ->from(array('ugnn' => 'admin_access_user_groups_nn'), array())
                    ->join(array('g'    => 'admin_access_groups'), 'ugnn.groupId = g.id', array())
                    ->join(array('r'    => 'admin_access_rights'), 'r.groupId = g.id', array())
                    ->join(array('rc'   => 'admin_access_resources'), 'r.resourceId = rc.id', array('rc.action'))
                    ->where( "ugnn.userId = " . $this->getId());
        
        $query->distinct();
        
        $str = $query->__toString();
        return $this->getAdapter()->fetchAll($query);        
    }

    /**
     * Get the names of all resources
     * @return array
     */
    public function getAvailableAccessResources(){
        return $this->getAdapter()->fetchCol("SELECT action FROM `admin_access_resources`");
    }

    /**
     * Add resource
     * @author alex
     * @since 01.12.2010
     */
    public function addResource($action) {
        try {
            $this->getAdapter()->query("INSERT INTO `admin_access_resources` (`action`) VALUES (?)", $action);
        }
        catch (Exception $e) {
        }
    }
    
    /**
     * Get access groups of this user
     * @author Alex Vait
     * @since 28.08.2012
     */
    public function getGroupNns() {
        $query = $this->getAdapter()
                    ->select()
                    ->from(array("agn" => 'admin_access_user_groups_nn'), array('groupId' => 'g.id', 'groupName' => 'g.name'))
                    ->join(array('g' => 'admin_access_groups'), 'g.id=agn.groupId', array())
                    ->where( "agn.userId = " . $this->getId());

        return $this->getAdapter()->fetchAll($query);
    }
}
