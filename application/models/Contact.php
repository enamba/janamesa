<?php

class Yourdelivery_Model_Contact extends Default_Model_Base {

    public function __construct($id = null) {
        if (is_null($id))
            return $this;
        parent::__construct($id);
    }

    public function getFullname() {
        return $this->getPrename() . " " . $this->getName();
    }

    /**
     * get related table
     * @return Yourdelivery_Model_DbTable_Cms
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Contact();
        }
        return $this->_table;
    }

    /**
     * get the companys that are related to this contact
     * @return RowSet
     */
    public function getCompanys() {
        $cTable = new Yourdelivery_Model_DbTable_Company();

        return $cTable->fetchAll(sprintf('contactId = %d OR billingContactId = %d',$this->getId(), $this->getId()));
    }

    /**
     * get ort object of location
     * @return Yourdelivery_Model_City
     */
    public function getOrt() {

        $cid = $this->getCityId();
        try {
            return new Yourdelivery_Model_City($cid);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
    }

    /**
     * @author alex
     * @since 13.04.2011
     * @return Yourdelivery_Model_City
     */
    public function getCity() {
        if ($this->_city !== null) {
            return $this->_city;
        }

        $cid = (integer) $this->getCityId();
        if ($cid <= 0) {
            return null;
        }

        return $this->_city = new Yourdelivery_Model_City($cid);
    }

    /**
     * get the services that are related to this contact
     * @return RowSet
     */
    public function getServices() {
        $cTable = new Yourdelivery_Model_DbTable_Restaurant();

        return $cTable->fetchAll(sprintf('contactId =  %d', $this->getId()));
    }

    /**
     * get the contact id by given email
     * @param string $email
     * @return id
     */
    public static function getByEmail($email) {
        $db = Zend_Registry::get('dbAdapter');
        $sql = 'select id from contacts where email="' . $email . '"';
        $query = $db->query($sql);
        $id = $query->fetchColumn();
        return $id;
    }

}

?>