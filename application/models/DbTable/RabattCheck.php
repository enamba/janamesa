<?php

/**
 * Database interface for Yourdelivery_Models_DbTable_RabattCodes.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
 */
class Yourdelivery_Model_DbTable_RabattCheck extends Default_Model_DbTable_Base {

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'rabatt_check';
    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    protected $_referenceMap = array(
        'Rabatt' => array(
            'columns' => 'rabattId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Rabatt',
            'refColumns' => 'id'
        )
    );

    /**
     * generate a row and return the generated code
     * @author mlaug
     * @since 10.03.2011
     * @param string $email
     * @return string
     */
    public function generateRow($email, $tel, $id) {
        $row = $this->createRow();
        $row->customerId = $id;
        $row->email = $email;
        $row->tel = $tel;
        $row->codeTel = $code = Default_Helper::generateRandomString(6,'1234567890');
        $row->codeEmail = md5($code . SALT);
        $row->save();
        return $row;
    }

    /**
     * get a row by given code
     * @author mlaug
     * @since 10.03.2011
     * @param string $code
     * @return array
     */
    public function findByCodeTel($code) {
        return $this->select()->where('codeTel=?', $code)->query()->fetch();
    }

    /**
     * get a row by given code
     * @author mlaug
     * @since 10.03.2011
     * @param string $code
     * @return array
     */
    public function findByCodeEmail($code) {
        return $this->select()->where('codeEmail=?', $code)->query()->fetch();
    }

    /**
     * append an rabatt code
     * @author mlaug
     * @since 10.03.2011
     * @param int $id
     * @param int $rabattId 
     */
    public function setRabattId($id, $rabattId) {
        $where = $this->getAdapter()->quoteInto('id=?', $id);
        $this->update(array('rabattCodeId' => $rabattId), $where);
    }
    
    /**
     * check for any tel or email, so that these get not send our 
     * multiple times
     * @author mlaug
     * @since 10.03.2011
     * @param string $email
     * @param string $tel
     * @return boolean
     */
    public function findByEmailOrTel($email = "",$tel = -1){  
        $table = new Yourdelivery_Model_DbTable_Order_Customer();
        $checkOne = $table->select()->where('email=?', $email)->orWhere('tel=?',$tel)->query()->rowCount() > 0 ? true : false;    
        $checkTwo = $this->select()->where('email=?', $email)->orWhere('tel=?',$tel)->query()->rowCount() > 0 ? true : false;
    
              
        return $checkOne || $checkTwo;
    }
    
    
    /**
     * Daniel Hahn <hahn@lieferando.de>
     * @param type $id
     * @return type 
     */
    public static function findByRabattVerificationId($id) {
        $db =  Zend_Registry::get('dbAdapterReadOnly');
        
        
        $select = $db->select()->from('rabatt_check')->where('rabattVerificationId = ?', $id);
        return $db->fetchRow($select);
        
    }
    
    /**
     * find enty by tel or email or customer id
     * @author Alex Vait <vait@lieferando.de>
     * @since 18.01.2012
     * @param string $email
     * @param string $tel
     * @param string $customerId
     * @return array
     */
    public static function findByEmailOrTelOrCustomerOrVerificationcode($email = null, $tel = null, $customerId = null, $verificationCode = null){  
        $db =  Zend_Registry::get('dbAdapterReadOnly');
        
        $row = null;
        if (!is_null($email) && strlen(trim($email))>0) {
            $select = $db->select()->from('rabatt_check')->where('email = ?', $email);
            $row = $db->fetchRow($select);            
        }

        if (is_null($row) && !is_null($tel) && strlen(trim($tel))>0) {
            $select = $db->select()->from('rabatt_check')->where('tel = ?', $tel);
            $row = $db->fetchRow($select);            
        }

        if (is_null($row) && !is_null($customerId) && strlen(trim($customerId))>0) {
            $select = $db->select()->from('rabatt_check')->where('customerId = ?', $customerId);
            $row = $db->fetchRow($select);            
        }

        if (is_null($row) && !is_null($verificationCode) && strlen(trim($verificationCode))>0) {
            $vselect = $db->select()->from('rabatt_codes_verification')->where('registrationCode = ?', $verificationCode);
            $vrow = $db->fetchRow($vselect);
            
            if (is_array($vrow)) {
                $select = $db->select()->from('rabatt_check')->where('rabattVerificationId = ?', $vrow['id']);
                $row = $db->fetchRow($select);                            
            }
        }
        
        return $row;
    }    
    
}
