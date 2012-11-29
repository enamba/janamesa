<?php

/**
 * Description of Emails
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Emails extends Default_Model_DbTable_Base {

    /**
     * @var string
     */
    protected $_name = "emails";

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 30.01.2012
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getLastRow() {
        
        return $this->fetchRow(
            $this->select()
                 ->order("id DESC")
                 ->limit(1)
        );
    }
    
    /**
     * find email by salted hash of email and time
     * 
     * @param string $hash md5(CONCAT(SALT,email,time)
     * 
     * @return Zend_Db_Table_Row
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.11.2010
     */
    public static function findByEmailTimeHash($hash) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("e" => "emails"))
                ->where("md5(CONCAT('" . SALT . "',e.email,e.id)) = '" . $hash . "'")
                ->limit(1);

        $result = $db->fetchOne($query);
        return $result;
    }

}
