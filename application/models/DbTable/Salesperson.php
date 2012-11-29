<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Salesperson
 *
 * @author vait
 */
class Yourdelivery_Model_DbTable_Salesperson extends Default_Model_DbTable_Base {

    protected $_name = "salespersons";

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('salespersons', 'salespersons.id = ' . $id);
    }

    /**
     * get the list of all distinct fields
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctNameId(){
        $sql = sprintf('select distinct(id), prename, name from salespersons order by name');
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * get Salesperson by email
     * @return id
     */
    public static function getIdByEmail($email) {
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('select id from salespersons where email=\'%s\'', $email);
        return $db->fetchOne($sql);
    }


    /**
     * get salesperson id from 'salespersons' table correcponding this adminId from admin_access_users table
     * the assotiation is based on the email value
     * @return array
     */
    public static function getIdFromAdminId($adminId) {
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('select id from salespersons where email=(select email from admin_access_users where id=%d)', $adminId);
        return $db->fetchOne($sql);
    }
    
    /**
     * get association for certain restaurant
     * @author alex
     * @since 18.05.2011
     * @return array
     */
    public function getContractForRestaurant($restaurantId) {
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('select id from salesperson_restaurant where salespersonId=%d and restaurantId=%d', $this->getId(), $restaurantId);
        return $db->fetchRow($sql);
    }
    
}
?>
