<?php

/**
 * Heidelpay Wpf Transaction Db Table
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.05.2011
 */
class Yourdelivery_Model_DbTable_Heidelpay_Wpf_Transactions extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'heidelpay_wpf_transactions';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * @var string 
     */
    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Heidelpay_Wpf_Transactions';

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
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 08.09.2011
 */
class Yourdelivery_Model_DbTableRow_Heidelpay_Wpf_Transactions extends Zend_Db_Table_Row_Abstract {

    /**
     * Get parameters as array
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 08.09.2011
     * @return array
     */
    public function getParams() {

        parse_str($this->params, $return);
        return $return;
    }

    /**
     * Get response as array
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 08.09.2011
     * @return array
     */
    public function getResponse() {
        
        parse_str($this->response, $return);
        return $return;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.01.2012
     * @return boolean
     */
    public function isResponseSuccessful() {
        
        $response = $this->getResponse();
        if ($response["PAYMENT_CODE"] == "CC.DB" && $response['PROCESSING_STATUS_CODE'] == '90' && $response['PROCESSING_RESULT'] == "ACK" ) {
            return true;
        }
        
        return false;
    }
}

