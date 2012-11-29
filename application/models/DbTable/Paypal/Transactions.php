<?php

/**
 * Paypal Transaction Db Table
 * @author Vincent Priem <priem@lieferando.de>
 * @since 15.11.2010
 */
class Yourdelivery_Model_DbTable_Paypal_Transactions extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'paypal_transactions';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * @var string
     */
    protected $_rowClass =
        'Yourdelivery_Model_DbTableRow_Paypal_Transactions';

    /**
     * @var array
     */
    protected $_referenceMap =
        array(
            'Order' => array('columns' => 'orderId',
                'refTableClass' => 'Yourdelivery_Model_DbTable_Order',
                'refColumns' => 'id'));

    /**
     * Get transactions by orderId
     * @author Vincent Priem <priem@lieferando.de>
     * @since 15.11.2010
     * @param int $orderId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getByOrder($orderId) {

        return $this->fetchAll($this->select()->where("`orderId` = ?", $orderId)->order('id DESC'));
    }

    /**
     * Get transactions by token
     * @author Vincent Priem <priem@lieferando.de>
     * @since 15.11.2010
     * @param string $token
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getByToken($token) {

        return $this->fetchAll($this->select()->where("`token` = ?", $token));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 28.09.2011
     * @return int|boolean
     */
    public function getPayerId($orderId) {

        $result =
            $this->fetchAll($this->select()->where("`orderId` = ?", $orderId)->where("LENGTH(`payerId`) > 0"));

        if ($result[0]) {
            return $result[0]['payerId'];
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 28.09.2011
     */
    public function findByPayerId($payerId) {
        $result =
            $this->fetchAll($this->select()->where("`payerId` = ?", $payerId));

        if (count($result)) {
            return $result;
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 28.09.2011
     */
    public function findDiscountsByPayerId($payerId) {
        $select =
            $this->getAdapter()->select()->from(array(
                'pt' => 'paypal_transactions'), array('orderId' => 'o.id',
                'o.time', 'o.rabattCodeId'))->join(array('o' => 'orders'), "o.id = pt.orderId", array())->where('pt.payerId = ?', $payerId)->where('o.rabattCodeId Is NOT NULL');

        return $this->getAdapter()->fetchAll($select);

    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 15.06.2012
     * @param type $orderId
     * @return boolean
     */
    public function getUserData($orderId) {
        $data = $this->getByOrder($orderId);

        foreach ($data as $entry) {

            $response = $entry->getResponse();

            if ($response['EMAIL'] && $response['PAYERID']
                && $response['PAYERSTATUS']) {
                return $response;
            }

        }

        return false;
    }

}

/**
 * Paypal Transaction Db Table Row
 * @author Vincent Priem <priem@lieferando.de>
 * @since 21.01.2011
 */
class Yourdelivery_Model_DbTableRow_Paypal_Transactions extends Zend_Db_Table_Row_Abstract {

    /**
     * Get parameters as array
     * @author Vincent Priem <priem@lieferando.de>
     * @since 21.01.2011
     * @return array
     */
    public function getParams() {

        return unserialize($this->params);
    }

    /**
     * Get response as array
     * @author Vincent Priem <priem@lieferando.de>
     * @since 21.01.2011
     * @return array
     */
    public function getResponse() {

        return unserialize($this->response);
    }

}
