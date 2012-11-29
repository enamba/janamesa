<?php

/**
 * Heidelpay Xml Transaction Db Table
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.05.2011
 */
class Yourdelivery_Model_DbTable_Heidelpay_Xml_Transactions extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'heidelpay_xml_transactions';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * @var string 
     */
    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Heidelpay_Xml_Transactions';
    
    /**
     * @var array 
     */
    protected $_referenceMap = array(
        'Order' => array(
            'columns' => 'orderId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Order',
            'refColumns' => 'id'
        )
    );
    
    /**
     * Get transactions by orderId
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.05.2011
     * @param int $orderId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getByOrder($orderId) {

        return $this->fetchAll(
            $this->select()
                 ->where("`orderId` = ?", $orderId)
        );
    }
}

/**
 * Heidelpay Transaction Db Table Row
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.01.2012
 */
class Yourdelivery_Model_DbTableRow_Heidelpay_Xml_Transactions extends Zend_Db_Table_Row_Abstract {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.01.2012
     * @return string
     */
    public function getResponseUniqueId() {
        
        $xml = simplexml_load_string($this->response);
        
        if ($xml && isset($xml->Transaction->Identification->UniqueID)) {
            return (string) $xml->Transaction->Identification->UniqueID;
        } 
        
        return null;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.01.2012
     * @return boolean
     */
    public function isResponseSuccessful() {
        
        $xml = simplexml_load_string($this->response);
        
        if ($xml && $xml->Transaction->Processing->Result == "ACK" && $xml->Transaction->Processing->Status == "NEW"  && $xml->Transaction->Payment->attributes()->code == "CC.DB") {
            return true;
        } 
        
        return false;
    }
}
