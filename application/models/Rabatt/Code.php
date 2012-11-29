<?php

/**
 * @author mlaug
 * @package order
 * @subpackage discount
 */
class Yourdelivery_Model_Rabatt_Code extends Default_Model_Base {

    /**
     * order amount minus discount
     * @var int
     */
    protected $_newAmount = null;

    /**
     * order amount minus newAmount
     * @var int
     */
    protected $_diff = null;

    /**
     * order object
     * @var $order
     */
    protected $_order = null;

    /**
     * parent object
     * @var Yourdelivery_Model_Rabatt 
     */
    protected $_parent = null;

    /**
     *
     * @param string  $code rabattCode to find
     * @param integer $id   id of rabattCode
     * @throws Yourdelivery_Exception_Database_Inconsistency
     */
    public function __construct($code = null, $id = null) {

        if ((integer) $id > 0) {
            parent::__construct($id);
        } elseif (strlen($code) > 0) {
            $row = Yourdelivery_Model_DbTable_RabattCodes::findByCode($code);
            if ($row === false) {
                throw new Yourdelivery_Exception_Database_Inconsistency('Code does not exist');
            }
            parent::__construct($row['id']);
        }
    }

    /**
     * get all informations
     * @author mlaug
     * @return Yourdelivery_Model_Rabatt
     */
    public function getParent() {
        if ($this->_parent === null) {
            $parentTableRow = $this->getTable()->getParent();
            try {
                $this->_parent = new Yourdelivery_Model_Rabatt($parentTableRow->id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
        return $this->_parent;
    }

    /**
     * get order that this code was used for
     * if this code is used for more than one order,
     * this function returns null to avoid returning the wrong order
     *
     * @return null|Yourdelivery_Model_Order
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 04.04.2012
     */
    public function getOrder() {

        if ($this->_order instanceof Yourdelivery_Model_Order_Abstract) {
            return $this->_order;
        }

        $order = null;

        try {
            $orderRows = Yourdelivery_Model_DbTable_Order::findByRabattCodeId($this->getId());
            if (count($orderRows) > 1) {
                $this->logger->debug("there are more than one orders which use this rabatt code - returning null to don't take the wrong one");
                return null;
            }

            if ((!is_array($orderRows)) || (count($orderRows) == 0)) {
                return null;
            }

            $order = new Yourdelivery_Model_Order($orderRows[0]['id']);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
        return $order;
    }

    /**
     * check wether this code is still usable
     * @author mlaug
     * @return boolean
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 04.04.2012
     */
    public function isUsable($apicall = null) {

        /**
         * if $apicall is not set explicit, we check depending order for Uuid
         */
        if (!is_null($apicall)) {
            $order = $this->getOrder();
            if (!is_null($order) && strlen($order->getUuid()) > 0) {
                $apicall = true;
            }
        }


        //check if interval is allowed
        $startTime = $this->getParent()->getStart();
        $endTime = $this->getParent()->getEnd();

        if ($apicall === false && $this->getParent()->isOnlyIphone()) {
            return false;
        }

        if (time() < $startTime || (time() > $endTime && $endTime > 0)) {
            return false;
        }

        // count of usage is given
        if ($this->getParent()->getRrepeat() == 2) {
            if ($this->getParent()->getCountUsage() > $this->getCountUsed()) {
                return true;
            } else {
                return false;
            }
        }

        $row = Yourdelivery_Model_DbTable_RabattCodes::findById($this->getId());

        if ($row['used'] == 0 && $this->getParent()->getStatus() == 1) {
            return true;
        }

        return false;
    }

    /**
     * check wether this code has exprired
     * @author alex
     * @return boolean
     */
    public function hasExpired() {

        //check if interval is allowed
        $startTime = $this->getParent()->getStart();
        $endTime = $this->getParent()->getEnd();

        if (time() < $startTime || time() > $endTime) {
            return true;
        }

        return false;
    }

    /**
     * check wether the usage count of this code is reached
     * @author mlaug
     * @return boolean
     */
    public function usageCountIsReached() {
        // count of usage is given
        if ($this->getParent()->getRrepeat() == 2) {
            if ($this->getParent()->getCountUsage() > $this->getCountUsed()) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * check wether this discount need fraud detection
     * @author mlaug
     * @since 19.01.2011
     * @return boolean
     * @todo: maybe activate that via admin backend
     */
    public function needFraudDetection() {
        return false;
    }

    /**
     * set code to used (Used = 1)
     * @author mlaug, fhaferkorn
     * @return boolean
     */
    public function setCodeUsed(Yourdelivery_Model_Order_Abstract $order = null) {

        if ($order instanceof Yourdelivery_Model_Order_Abstract) {
            $this->_order = $order;
        }

        if ($this->isUsable()) {
            if ($this->getParent()->getRrepeat() == 1) {
                return true;
            }

            $this->setUsed(true);

            if ($this->getParent()->getRrepeat() == 2) {
                $this->setCountUsed($this->getCountUsed() + 1);
            }

            $this->save();

            return true;
        }
        return false;
    }

    /**
     * set code unused (Used = 0)
     * and undo a fidelity transaction if exists
     * @author mlaug, fhaferkorn
     * @since 09.11.2010, 18.07.2011
     * @return boolean
     */
    public function setCodeUnused(Yourdelivery_Model_Order_Abstract $order = null) {

        $this->setUsed(false);

        if ($this->getParent()->getRrepeat() == 2) {
            $countUsed = $this->getCountUsed();
            if ($countUsed > 0) {
                $this->setCountUsed($countUsed - 1);
            }
        }
        $this->save();

        return true;
    }

    /**
     * how often this code was used
     * // TODO get correct values - depending on rrepeat value.
     * @author alex
     * @since 28.09.2010
     * @return int
     */
    public function getUsedCountByOrders() {
        if ($this->getUsed() == 0) {
            return 0;
        }

        return $this->getTable()->getUsedCountByOrders();
    }

    /**
     * get value of this code from parent
     * kind 0 = %
     * kind 1 = absolute
     *
     * @author mlaug
     * @param int $amount
     * @return array (diff, newAmount)
     */
    public function calcDiff($amount = 0) {

        $diff = null;
        $newAmount = null;

        $rabatt = $this->getParent()->getRabatt();

        if ($this->getKind() == Yourdelivery_Model_Rabatt::ABSOLUTE) {
            $newAmount = $amount - $rabatt;
        } else if ($this->getKind() == Yourdelivery_Model_Rabatt::RELATIVE) {
            if ($rabatt > 0) {
                if ($rabatt >= 100) {
                    $newAmount = 0;
                } else {
                    $newAmount = $amount - ($amount / 100 * $rabatt);
                }
            } else {
                $newAmount = $amount;
            }
        }

        /**
         * if new amount negative , $newAmount = 0
         */
        if ($newAmount < 0) {
            $newAmount = 0;
        }

        /**
         * @author Felix Haferkorn <haferkorn@lieferando.de>
         * @since 30.06.2011
         * new: round()
         */
        $newAmount = round($newAmount);

        $diff = $amount - $newAmount;

        //store for later usage
        $this->_diff = $diff;
        $this->_newAmount = $newAmount;

        return array($diff, $newAmount);
    }

    /**
     * get calculated diff
     * @author mlaug
     * @return int
     */
    public function getDiff() {
        return $this->_diff;
    }

    /**
     * get new calculated amount
     * @author mlaug
     * @return int
     */
    public function getNewAmount() {
        return $this->_newAmount;
    }

    /**
     * get name if this code from parent
     * @author mlaug
     * @return string
     */
    public function getName() {
        if (!is_null($this->_id)) {
            return $this->getParent()->getName();
        }
        return null;
    }

    /**
     * get description of discount
     * @author mlaug
     * @return string description
     */
    public function getInfo() {
        if (!is_null($this->_id)) {
            return $this->getParent()->getInfo();
        }
        return null;
    }

    /**
     * get text when rabatt has expired
     * @author mlaug
     * @return string
     */
    public function getExpirationInfo() {
        if (!is_null($this->_id)) {
            return $this->getParent()->getExpirationInfo();
        }
        return null;
    }

    /**
     * get kind of rabatt code from parent
     * 0 = use only 1 time
     * 1 = use everytime
     * @author mlaug
     * @return int
     */
    public function getKind() {
        if (!is_null($this->_id)) {
            return $this->getParent()->getKind();
        }
        return null;
    }

    /**
     * get minimum Amount
     * @author mlaug
     * @return int
     */
    public function getMinAmount() {
        if (!is_null($this->_id)) {
            return intval($this->getParent()->getMinAmount());
        }
        return null;
    }

    /**
     * remove a rabatt from a customer
     * @author mlaug
     * @param Yourdelivery_Model_Customer_Abstract $customer
     * @return boolean
     */
    public function removeFromCustomer(Yourdelivery_Model_Customer_Abstract $customer) {
        foreach ($customer->getRabatt() as $obj) {
            if ($obj->getId() == $this->getId()) {
                $customer->getRabatt()->detach($obj);
                return true;
            }
        }
        return false;
    }

    /**
     * get table class
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_RabattCodes
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_RabattCodes();
        }
        return $this->_table;
    }
    
    /**
     * get count of orders where this code was used
     * @author alex
     * @since 15.07.2011
     */
    public function getOrdersCount() {
        return $this->getTable()->getOrdersCount();
    }

}
