<?php

/**
 * Description of FidelityTransaction
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class Yourdelivery_Model_DbTable_Customer_FidelityTransaction extends Default_Model_DbTable_Base {

    protected $_name = "customer_fidelity_transaction";

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * get row matching given email-address
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.08.2010
     * @param string $email
     * @return Zend_Db_Table_Rowset
     */
    public static function findAllByEmail($email, $action = null, $limit = null) {
        if (is_null($email)) {
            return null;
        }
        
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("f" => "customer_fidelity_transaction"))
                ->where("f.email = '" . $email . "'")
                ->where("f.status <> -2")
                ->order("created DESC");
                
        if ($action != null) {
            $query->where('f.action=?', $action);
        }
        
        if($limit != null){
            $query->limit($limit);
        }
        
        return $db->fetchAll($query);
    }

    /**
     * get all rows matching given orderId
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.11.2010
     * @param int $orderId
     * @return Zend_Db_Table_Rowset
     */
    public static function findByOrderId($orderId, $action = 'order') {
        if (is_null($orderId)) {
            return null;
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select('id')
                ->from(array("f" => "customer_fidelity_transaction"))
                ->where("f.transactionData = '" . $orderId . "'")
                ->where("f.action = '" . $action . "'");

        return $db->fetchOne($query);
    }

    /**
     * get all rows matching given action and data
     * @author mlaug
     * @since 20.11.2010
     * @param string $action
     * @param string $email
     * @return array
     */
    public static function findByAction($action, $email, $valid = false) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("f" => "customer_fidelity_transaction"))
                ->where("f.email = '" . $email . "'")
                ->where("f.action = '" . $action . "'")
                ->order('f.created DESC');

        if ( $valid ){
            $query->where('status>=0');
        }
        
        return $db->fetchRow($query);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.11.2011
     *
     * @param string $email
     * @param string $transactionData
     * @param string $action  
     * @return integer transactionId
     */
    public static function findByTransactionDataAction($email, $transactionData, $action) {      
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("f" => "customer_fidelity_transaction"))
                ->where("f.email = '" . $email . "'")
                ->where("f.transactionData = '" . $transactionData . "'")
                ->where("f.action LIKE '%" . $action . "%'");

        return $db->fetchRow($query);
    }
    
    /**
     * migrate all fidelity transactions from one email to another
     *
     * @param type $oldEmail old email of transaction
     * @param type $newEmail email to megrate all transactions to
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.12.2011
     * 
     * @return integer number of affected rows
     */
    public function migrateToEmail($oldEmail, $newEmail){
        $db = Zend_Registry::get('dbAdapter');
        return $db->update('customer_fidelity_transaction', array('email' => $newEmail), 'email = "'.$oldEmail.'"');
    }
    
    /**
     * get amount of points the customer had before this transaction 
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function getPointsUntil($email, $time){
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("t" => "customer_fidelity_transaction"),array('sum' => 'SUM(t.points)'))
                ->where("t.email = '" . $email . "'")
                ->where("t.created < '" . date('Y-m-d H:i:s', $time) . "'")
                ->where("t.status = 0");
        return $db->fetchRow($query);
    }

}
