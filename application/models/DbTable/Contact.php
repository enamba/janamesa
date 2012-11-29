<?php

/**
 * Description of Contacts
 *
 * @author joelerich
 */
class Yourdelivery_Model_DbTable_Contact extends Default_Model_DbTable_Base {

    /**
     * table name
     * @var string
     */
    protected $_name = "contacts";
    
    /**
     * depending tables
     * @var array
     */
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Company',
        'Yourdelivery_Model_DbTable_Restaurant'
    );

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * edit data of contact
     * 
     * @param integer $id   id of contact
     * @param array   $data data to update
     *
     * @return void
     */
    public static function edit($id, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('contacts', $data, 'contacts.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * associations will be removed too
     * 
     * @param integer $id id of contact to remove
     * 
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('contacts', 'contacts.id = ' . $id);
        $db->update('restaurants', array('contactId' => '0'), 'contactId = ' . $id);
        $db->update('restaurants', array('billingContactId' => '0'), 'billingContactId = ' . $id);
        $db->update('companys', array('contactId' => '0'), 'contactId = ' . $id);
        $db->update('companys', array('billingContactId' => '0'), 'billingContactId = ' . $id);
        $db->update('courier', array('contactId' => '0'), 'contactId = ' . $id);
    }

    /**
     * get the list of all distinct fields
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getDistinctNameId() {
        $sql = "SELECT distinct(id), name, prename FROM contacts ORDER BY name";
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * temp function - only until the creation of model works correctly
     * 
     * @param integer $id id of row
     * 
     * @return Zend_Db_Table_Row_Abstract
     */
    public static function findById($id) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "select * from contacts where id=" . $id;
        $result = $db->fetchAll($sql);

        return $result[0];
    }

}