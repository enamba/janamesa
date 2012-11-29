<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RabattCodeVerification
 *
 * @author daniel
 */
class  Yourdelivery_Model_DbTable_RabattCodesVerification extends Default_Model_DbTable_Base {
    //put your code here
    protected $_name = "rabatt_codes_verification";

    protected $_referenceMap    = array(
        'Rabatt' => array(
            'columns'           => 'rabattId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Rabatt',
            'refColumns'        => 'id'
        )
    );    

    public function findByUqRabattVerification($code, $rabattId) {
        $select = $this->select()->where("registrationCode = ?", $code)->where('rabattId= ?', $rabattId);
        
        return $this->fetchRow($select);        
    }

    public function findById($id) {
        return $this->fetchRow($this->getAdapter()->quoteInto("id=?", $id));
    }

    /**
     * Find verification codes by rabattId
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 17.01.2012
     * @return array
     */     
    public static function findByRabattId($rabattId) {
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from("rabatt_codes_verification")->where('rabattId= ?', $rabattId);        
        return $db->fetchAll($select);        
    }    
    
    /**
     * Find verification code by code
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 17.01.2012
     * @param   string $code
     * @return  array
     */
    public static function findByCode ($code) {
        // remove special chars
        $code = str_replace(array("'", '"'), "", $code);
        
        $db = Zend_Registry::get('dbAdapter');
        return $db->fetchRow(
            "SELECT *
            FROM `rabatt_codes_verification` r
            WHERE r.registrationCode = ?
            LIMIT 1", $code
        ); 
    }
    
    /**
     * Return the count of registration codes in this discount action
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 17.01.2012
     * @return int
     */
    public static function getCodesCount($rabattId) {        
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->fetchRow(sprintf('select count(id) count from rabatt_codes_verification where rabattId=%d', $rabattId));
        return $result['count'];
    }
    
    /**
     * Return the DBTable of parent discount
     * @author Alex Vait <vait@lieferando.de>
     * @since 17.01.2012
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getParent(){
        if ( is_null($this->getId()) ){
            return null;
        }
        return $this->getCurrent()->findParentRow('Yourdelivery_Model_DbTable_Rabatt');
    }    
}

?>
