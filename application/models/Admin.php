<?php
class Yourdelivery_Model_Admin extends Default_Model_Base{

    /**
     * @var Yourdelivery_Model_DbTable_Admin_Access_Users
     */
    protected $_table = null;

    /**
     * array of resources this user has access to
     * @var array
     */
    protected $_resources = array();

    /**
     * array of all available resources
     * @var array
     */
    protected $_resourcesAvailable = array();
    
    /**
     * @var array of Yourdelivery_Model_Admin_Access_Group
     */
    protected $_groups = array();

    /**
     * Get a admin by given id or email
     * @author vpriem
     * @since 30.06.2011
     * @param int $id
     * @param string $email
     */
    function __construct($id = null, $email = null) {

        // if email is set we try to gather it
        if ($email !== null) {
            $row = $this->getTable()->findByEmail($email);
            if (is_array($row)) {
                parent::__construct($row['id']);
            }
        }
        // if id is set we try to gather it
        else if ($id !== null) {
            parent::__construct($id);
        }
    }

    /**
     * Alex Vait <vait@lieferando.de>
     * @since 28.08.2012
     * @return array of Yourdelivery_Model_Admin_Access_Group 
     */
    public function getGroups(){
        if (count($this->_groups) > 0) {
            return $this->_groups;
        }
                
        foreach ($this->getTable()->getGroupNns() as $grRow) {
            try {
                $group = new Yourdelivery_Model_Admin_Access_Group($grRow['groupId']);
                $this->_groups[] = $group;
            } 
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }            
        }
        
        return $this->_groups;
    }
    
    /**
     * Alex Vait <vait@lieferando.de>
     * @since 28.08.2012
     * @return boolean
     */
    public function isAdmin(){
        foreach ($this->getGroups() as $group) {
            if ($group->getIsAdmin() == 1){
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get related table
     * @author vpriem
     * @since 03.11.2010
     * @return Yourdelivery_Model_DbTable_Admin_Access_Users
     */
    public function getTable(){

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Admin_Access_Users();
        }
        return $this->_table;

    }

    /**
     * get the names of resources, this user has access to
     * @author alex
     * @return array
     */
    public function getAccessResources() {
        //in this case we can change the rights without loggin out
        return $this->getTable()->getAccessResources();        
    }

    /**
     * get the names of all resources
     * @author alex
     * @return array
     */
    public function getAvailableAccessResources() {
        //in this case we can change the rights without loggin out
        return $this->getTable()->getAvailableAccessResources();

    }

    /**
     * has the user access to this resource?
     * @return boolean
     */
    public function hasAccessToResource ($resource) {

        // save all resources in array so we can check if this resource is already saved in the resources table
        if (count($this->_resourcesAvailable) == 0) {
            $this->_resourcesAvailable = $this->getAvailableAccessResources();
        }

        // if this action is not already in the resources table, save it's name
        if (!in_array($resource, $this->_resourcesAvailable)) {
            $this->getTable()->addResource($resource);
        }

        // if user belongs to the admin group, allow everything
        if ($this->isAdmin()) {
            return true;
        }

        // save all resources for this user, so we don't have to load them every time we call some page
        if (count($this->_resources) == 0) {
            $this->_resources = $this->getAccessResources();
        }

        // test if this resource is in the table of allowed resources
        foreach ($this->_resources as $r) {
            if (strcmp($r['action'], $resource) == 0) {
                return true;
            }
        }
        return false;
    }
            
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 31.08.2012
     * @param type $groupName
     * @return boolean
     */
    public function hasGroup($groupName) {
        
        $groups = $this->getGroups();                
        foreach ($groups as $group) {
            if($group->getName() == $groupName  ) {
                return true;                
            }
        }
        
        return false;        
    }
}
