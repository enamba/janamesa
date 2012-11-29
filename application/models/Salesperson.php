<?php

/**
 * salers model
 * @package backend
 * @subpackage salesperson
 * @author vait
 */

class Yourdelivery_Model_Salesperson extends Default_Model_Base{
    /**
     * get associated table
     * @return Yourdelivery_Model_DbTable_Salesperson
     */
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Salesperson();
        }
        return $this->_table;
    }

    /**
     * get Salesperson by email
     * @return Yourdelivery_Model_Salesperson
     */
    public static function getByEmail($email) {
        $id = Yourdelivery_Model_DbTable_Salesperson::getIdByEmail($email);
        return new Yourdelivery_Model_Salesperson($id);
    }
    
    /**
     * get the list  of restaurants this saler is responsible for
     * @return array of Yourdelivery_Model_Servicetype_Restaurant
     */
    public function getRestaurants() {
        $rs = Yourdelivery_Model_DbTable_Salesperson_Restaurant::getRestaurantsForSalesperson($this->getId());
        
        $storage = new SplObjectStorage();
        foreach($rs as $r){
            try {
                $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($r['restaurantId']);
                $storage->attach($restaurant);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            }
        }
        return $storage;
    }

    /**
     * test if this email is already assotiated with admin account
     * @return boolean
     */
    public static function registeredAsAdmin($email) {
        $admin = new Yourdelivery_Model_Admin(null, $email);

        if ($admin->getId() == 0 ) {
            return false;
        }
        
        return true;
    }

    /**
     * get all salespersons from database
     * @return SplObjectStorage
     */
    public static function all(){
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->query('select id from salespersons')->fetchAll();
        $salespersons = new SplObjectStorage();
        foreach($result as $r){
            if ( intval($r['id']) == 0 ){
                continue;
            }
            $person = new Yourdelivery_Model_Salesperson($r['id']);
            $salespersons->attach($person);
        }
        return $salespersons;
    }
    
    /**
     * get association for certain restaurant
     * @author alex
     * @since 18.05.2011
     * @return array
     */
    public function getContractForRestaurant($restaurantId) {
        $data = $this->getTable()->getContractForRestaurant($restaurantId);
        
        if (is_null($data) || ($data['id']==0) ) {
            return null;
        }
           
        try {
            $contract = new Yourdelivery_Model_Salesperson_Restaurant($data['id']);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
        
        return $contract;
    }
    
}


?>
