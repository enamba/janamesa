<?php

/**
 * Description of Customer
 * @package billing
 * @subpackage restaurant
 */
class Yourdelivery_Model_Billing_Restaurant extends Yourdelivery_Model_Billing_Abstract {

    /**
     * amount to be refunded by yourdelivery to server
     * @var int
     */
    public $voucherAmount = 0;

    /**
     * cache order by payment for faster access
     * @var array
     */
    public $ordersByPayment = array();

    /**
     * @var Yourdelivery_Model_Billing_Restaurant_assets
     */
    public $asset = null;

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param Yourdelivery_Model_Servicetype_Abstract $service
     * @param int $from
     * @param int $until
     * @param string $mode
     * @param boolean $test
     */
    public function __construct(Yourdelivery_Model_Servicetype_Abstract $service, $from = 0, $until = 0, $mode = null, $test = 0) {
        parent::__construct();
        $this->setPeriod($from, $until, $mode);
        $this->setService($service);
        $this->asset = new Yourdelivery_Model_Billing_Restaurant_Assets($service, $from, $until, $mode, $test);
    }

    /**
     * get corresponding billing number, based on starting date,
     * customer number and next billing number
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getNumber() {
        if (is_null($this->number)) {
            $number = $this->getTable()->getNextBillingNumber($this->getService(), 'rest');
            $this->number = "R-" . date('y', $this->from) . date('m', $this->from) . "-" . $this->getService()->getCustomerNr() . "-" . $number;
        }
        return $this->number;
    }

    /**
     * get voucher number
     * this just replaces an R with a G
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getNumberVoucher() {
        return str_replace('R', 'G', $this->number);
    }

    /**
     * get the taxes to charge
     * 
     * @author Matthias Laug
     * @since 26.04.2012
     * @return decimal 
     */
    public function getTax() {
        return (1 + ($this->config->tax->provision / 100));
    }
    
    /**
     * get the latex template name for restaurant bills if set in config. else return 'standard'
     * 
     * @author Jens Naie
     * @since 11.06.2012
     * @return string 
     */
    public function getTemplateName() {
        return $this->config->locale->latex->template? $this->config->locale->latex->template : 'standard';
    }

    /**
     * set service
     * @author Matthias Laug <laug@lieferando.de>
     * @param Yourdelivery_Model_Servicetype_Abstract $service
     */
    public function setService($service = null) {
        switch ($service->getId()) {
            default: {
                    $template = $this->getTemplateName();
                    $this->_latex->setTpl('bill/service/' . $template);
                    break;
                }
        }
        $this->_service = $service;
    }

    /**
     * get service
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Servicetype_Abstract $service
     */
    public function getService() {
        return $this->_service;
    }

    /**
     * check if this service has any dependencies which are taken into account
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.06.2011
     * @return boolean
     */
    public function hasChildren() {
        if ($this->getService()->getBillingChildren()->count() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 17.03.2011
     * @param string $payment
     * @param float $taxtype 
     */
    public function calculateItem($payment = 'all', $taxtype = ALL_TAX) {
        $total = 0;
        $orders = $this->getOrdersByPayment($payment);

        if ($taxtype == ALL_TAX) {
            $check = array();
            foreach ($this->config->tax->types->toArray() as $_taxtype) {
                $total = 0;
                foreach ($orders as $order) {
                    $total += $order->getItem($_taxtype, $this->inclDeliver($order), false, false);
                }
                $check[] = round($total);
            }
            return array_sum($check);
        } else {
            foreach ($orders as $order) {
                $total += $order->getItem($taxtype, $this->inclDeliver($order), false, false);
            }

            return $total;
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 17.03.2011
     * @param string $payment
     * @param float $taxtype 
     */
    public function calculateTax($payment = 'all', $taxtype = ALL_TAX) {
        $total = 0;
        $orders = $this->getOrdersByPayment($payment);

        if ($taxtype == ALL_TAX) {
            //if we choose ALL_TAX, we need to fix the sum
            $check = array();
            foreach ($this->config->tax->types->toArray() as $_taxtype) {
                $total = 0;
                foreach ($orders as $order) {
                    $total += $order->getTax($_taxtype, $this->inclDeliver($order), false, false);
                }
                $check[] = round($total);
            }
            return array_sum($check);
        } else {
            foreach ($orders as $order) {
                $total += $order->getTax($taxtype, $this->inclDeliver($order), false, false);
            }

            return $total;
        }
    }

    /**
     * get the commission amount from a special commission
     * interval
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.12.2010
     * @param integer $from
     * @param integer $until
     * @return integer 
     */
    public function calculateCommissionSpecial($from, $until, $payment = 'all') {
        $total = 0;
        $orders = $this->getOrdersByPayment($payment, true, false);
        foreach ($orders as $order) {
            $deliverTime = $order->getDeliverTime();
            if ($deliverTime >= $from && $deliverTime <= $until) {
                $total += $order->getCommissionPercent();
            }
        }
        return $total;
    }

    /**
     * get intervals, where we have a different commission
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.12.2010
     * @return array
     */
    public function getCommissionsInterval() {
        $special = Yourdelivery_Model_DbTable_Restaurant_Commission::getAdditionalCommissions($this->getService()->getId());
        $_special = array();
        if (is_array($special)) {
            foreach ($special as $s) {
                $from = strtotime($s['from']);
                $until = strtotime($s['until']);
                if ($from >= $this->from || $until <= $this->until) {
                    $s['from'] = $from;
                    $s['until'] = $until;
                    $_special[] = $s;
                }
            }
        }
        return $_special;
    }

    /**
     * calculate the commission based on the statc value in restaurant table
     * check if any special intervals interfere
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     * @param boolean $include_base
     * @param boolean $include_satellite 
     * @since 22.12.2010
     * @return interger
     */
    public function calculateCommissionPercentStatic($payment = 'all', $include_base = true, $include_satellite = true) {
        $special = Yourdelivery_Model_DbTable_Restaurant_Commission::getAdditionalCommissions($this->getService()->getId());

        $orders = $this->getOrdersByPayment($payment, $include_base, $include_satellite);
        $total = 0;
        foreach ($orders as $order) {
            $nope = false;
            $deliverTime = $order->getDeliverTime();
            foreach ($special as $s) {
                $from = strtotime($s['from']);
                $until = strtotime($s['until']);
                if ($deliverTime >= $from && $deliverTime <= $until) {
                    $nope = true;
                    break;
                }
            }

            if ($nope === false) {
                $total += $order->getCommissionPercent();
            }
        }
        return $total;
    }

    /**
     * calculate fee of each order
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     * @param boolean $include_base
     * @param boolean $include_satellite 
     * @return int
     */
    public function calculateCommissionPercent($payment = 'all', $include_base = true, $include_satellite = true) {
        $total = 0;

        $orders = $this->getOrdersByPayment($payment, $include_base, $include_satellite);
        foreach ($orders as $order) {
            $total += $order->getCommissionPercent();
        }

        return $total;
    }

    /**
     * calculate fee of each order
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     * @param boolean $include_base
     * @param boolean $include_satellite 
     * @return int
     */
    public function calculateCommissionEach($payment = 'all', $include_base = true, $include_satellite = true) {
        $orders = $this->getOrdersByPayment($payment, $include_base, $include_satellite);
        $total = 0;
        foreach ($orders as $order) {
            $total += $order->getCommissionEach();
        }

        return $total;
    }

    /**
     * count orders which are charged each
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     * @param boolean $include_base
     * @param boolean $include_satellite 
     * @since 18.12.2011
     * @return int
     */
    public function countOrdersCommissionEach($payment = 'all', $include_base = true, $include_satellite = true) {
        $orders = $this->getOrdersByPayment($payment, $include_base, $include_satellite);
        $total = 0;
        foreach ($orders as $order) {
            $total += $order->getCommissionEach() > 0 ? 1 : 0;
        }

        return $total;
    }

    /**
     * calculate commission on each article
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     * @param boolean $include_base
     * @param boolean $include_satellite 
     * @return int
     */
    public function calculateCommissionItem($payment = 'all', $include_base = true, $include_satellite = true) {
        $orders = $this->getOrdersByPayment($payment, $include_base, $include_satellite);
        $total = 0;
        foreach ($orders as $order) {
            $total += $order->getCommissionItem();
        }
        return $total;
    }

    /**
     * get count of articles of those articles which are charged
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     * @param boolean $include_base
     * @param boolean $include_satellite 
     * @return int
     */
    public function getArticleCount($payment = 'all', $include_base = true, $include_satellite = true) {
        $total = 0;
        $orders = $this->getOrdersByPayment($payment, $include_base, $include_satellite);
        foreach ($orders as $order) {
            $total += $order->getCommissionItem() > 0 ? $order->getCardCount() : 0;
        }
        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 31.01.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return integer
     */
    public function getBruttoAmountOfOrder(Yourdelivery_Model_Order_Abstract $order, $forceInclDeliver = false) {
        return $order->getTotal() + ($order->getServiceDeliverCost() * $this->inclDeliver($order, $forceInclDeliver));
    }

    /**
     * get entire amount of all orders
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     * @param boolean $include_base
     * @param boolean $include_satellite 
     * @return int
     */
    public function getBrutto($payment = 'all', $include_base = true, $include_satellite = true, $forceInclDeliver = false) {
        $total = 0;
        $orders = $this->getOrdersByPayment($payment, $include_base, $include_satellite);
        foreach ($orders as $order) {
            $total += $this->getBruttoAmountOfOrder($order, $forceInclDeliver);
        }

        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     * @param boolean $include_base
     * @param boolean $include_satellite 
     */
    public function calculateTransactionCost($payment = 'all', $include_base = true, $include_satellite = true) {
        $total = 0;
        $orders = $this->getOrdersByPayment($payment, $include_base, $include_satellite);
        foreach ($orders as $order) {
            $total += (integer) $order->getCharge();
        }
        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     * @param boolean $include_base
     * @param boolean $include_satellite 
     */
    public function calculateTransactionCostBrutto($payment = 'all', $include_base = true, $include_satellite = true) {
        return $this->calculateTransactionCost($payment, $include_base, $include_satellite) * $this->getTax();
    }

    /**
     * @author Matthias Laug
     * @since 26.04.2012
     * @return integer 
     */
    public function calculateFee() {
        return $this->getService()->getBasefee();
    }

    /**
     * @author Matthias Laug
     * @since 26.04.2012
     * @return integer 
     */
    public function calculateFeeBrutto() {
        return $this->getService()->getBasefee() * $this->getTax();
    }

    /**
     * calcualte the voucher amount if any
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.06.2011
     */
    public function getVoucherAmount($withBalance = false) {
        $amount = $this->getAlreadyPayed($withBalance) - ($this->getCommBruttoTotal() + $this->calculateFeeBrutto() + $this->calculateTransactionCostBrutto());
        if ($amount < 0) {
            return 0;
        } else {
            return $amount;
        }
    }

    /**
     * calculate billing amount, which has to be payed by service
     * @author Matthias Laug <laug@lieferando.de>
     * @param boolean $withBalance
     * @param boolean $zeroBalance
     * @return integer
     */
    public function calculateBillingAmount($withBalance = false, $zeroBalance = true, $showNegativeAmount = false) {
        $prov = $this->getCommBruttoTotal('all') + $this->calculateFeeBrutto() + $this->calculateTransactionCostBrutto();
        $payed = $this->getAlreadyPayed($withBalance, $zeroBalance);

        $bill = $prov - $payed;
        if ($bill < 0 && !$showNegativeAmount) {
            return 0;
        } else {
            return $bill;
        }
    }

    /**
     * get all orders which have been payed with a certian payment type
     * payment type can also be "all" or "online". Online means "all" without "bar" 
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     * @return array
     */
    public function getOrdersByPayment($payment = 'all', $include_base = true, $include_satellite = true) {
        $hash = $payment;
        $hash .= $include_base ? "y" : "n";
        $hash .= $include_satellite ? "y" : "n";
        if (isset($this->ordersByPayment[$hash])) {
            return $this->ordersByPayment[$hash];
        }
        $orders = array();
        $cOrders = $this->getOrders($include_base, $include_satellite);
        if ($payment != 'all') {
            foreach ($cOrders as $order) {
                if ($order->getPayment() == $payment || $order->getPayment() != 'bar' && $payment == 'online') {
                    $orders[] = $order;
                }
            }
            $this->ordersByPayment[$hash] = $orders;
            return $orders;
        } else {
            $this->ordersByPayment[$hash] = $cOrders;
            return $cOrders;
        }
    }

    /**
     * get all assets
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.10.2010
     * @return array
     */
    public function getBillingAssets() {
        $assetsTable = new Yourdelivery_Model_DbTable_BillingAsset();
        $select = $assetsTable->select()->where('billRest is Null or billRest<=0');
        $rows = $this->getService()
                ->getTable()
                ->getCurrent()
                ->findDependentRowset('Yourdelivery_Model_DbTable_BillingAsset', null, $select);
        $assets = array();

        foreach ($rows as $row) {

            $assetId = (integer) $row['id'];
            if (is_array($this->old_assets) && count($this->old_assets) > 0 && !in_array($assetId, $this->old_assets)) {
                $this->logger->debug(sprintf('Ignoring asset %d, this has not been in this bill before', $assetId));
                continue;
            }

            if (strtotime($row['timeFrom'] < $this->from || strtotime($row['timeFrom']) > $this->until)) {
                $this->logger->debug(sprintf('Ignoring asset %d, not in this time interval', $assetId));
                continue;
            }

            $assets[] = new Yourdelivery_Model_BillingAsset($row['id']);
        }

        return $assets;
    }

    /**
     * get all orders in a certian time range
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    public function getOrders($include_base = true, $include_satellite = true) {

        //check for merges
        $db = $this->getTable()->getAdapter();
        $sid = $this->getService()->getId();
        $parent = $db->fetchAll("select * from billing_merge where child=? and kind='rest'", $sid);
        if (count($parent) > 0) {
            $this->logger->info('Creating no bill because we have a parent element');
            return array();
        }

        if (is_null($this->orders)) {

            $rows = $db->fetchAll("select id from orders
                            where (billRest=0 or billRest is NULL)
                            and (restaurantId = ? or
                            restaurantId in (select child from billing_merge where parent=? and kind='rest'))", array($sid, $sid));
            $orders = array();
            foreach ($rows as $o) {
                try {

                    $orderId = $o['id'];

                    if (is_array($this->old_orders) && count($this->old_orders) > 0 && !in_array($orderId, $this->old_orders)) {
                        //$this->logger->debug(sprintf('Ignoring order %d, this has not been in this bill before', $orderId));
                        continue;
                    }

                    $order = new Yourdelivery_Model_Order($orderId);

                    if ($order->getService()->isAcceptsPfand() && $order->getState() != 2 && $order->getPayment() == 'bill') {
                        $this->logger->debug(sprintf('Ignoring order %d because of state %d, payment bill and no pfand yet set', $order->getId(), $order->getState()));
                        continue;
                    }

                    if ($order->getState() < 0) {
                        $this->logger->debug(sprintf('Ignoring order %d of restId %d because of state %d', $order->getId(), $sid, $order->getState()));
                        continue;
                    }

                    if ($order->getDeliverTime() > $this->until) {
                        $this->logger->debug(sprintf('Ignoring order %d because of wrong time', $order->getId(), $order->getState()));
                        continue;
                    }

                    $orders[] = $order;
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    
                }
            }
            $this->orders = $orders;
        }

        $returnOrders = array();
        foreach ($this->orders as $order) {
            //ignore base orders
            if (!$include_base && !$order->isSatellite()) {
                continue;
            }

            //ignore satellite orders
            if (!$include_satellite && $order->isSatellite()) {
                continue;
            }

            $returnOrders[] = $order;
        }

        return $returnOrders;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function getCashTotal($billType = 'all', $include_base = true, $include_satellite = true) {
        $total = 0;
        $orders = $this->getOrdersByPayment($billType, $include_base, $include_satellite);
        if (count($orders) > 0) {
            foreach ($this->getOrdersByPayment($billType) as $order) {
                $total += $order->getCashAmount();
            }
        }
        return $total;
    }

    /**
     * check if we need to include deliver cost
     * we may overwrite this check
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.10.2010
     * @param Yourdelivery_Model_Order $order
     * @param boolean $overwrite
     * @return boolean
     * @see http://ticket.yourdelivery.local/browse/SP-1462
     */
    public function inclDeliver(Yourdelivery_Model_Order $order, $overwrite = false) {
        return true; //I do not know, why ever this functions has been for :( because it is
        //used to remove deliver costs from different locations
        if ($order->getCourierId() > 0) {
            return false;
        }
        return $overwrite || $this->getService()->isBillDeliverCost();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function getPayedAmountTotal($billType = 'all') {
        if ($billType == 'bar') {
            return 0;
        }
        $total = 0;
        $orders = $this->getOrdersByPayment($billType);
        if (count($orders) > 0) {
            foreach ($orders as $order) {
                //we need to ignore bar payments here
                if ($order->getPayment() == 'bar') {
                    continue;
                }
                $total += $order->getPayedAmount(true, false, false);
            }
        }

        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function getTotalTotal($billType = 'all') {
        $total = 0;
        $orders = $this->getOrders();
        if (count($orders) > 0) {
            foreach ($orders as $order) {

                /**
                 * if all billtypes are demanded, we add up everything, dispite the discount
                 * from courier and deliver cost, if not suited
                 */
                if ($billType == 'all') {
                    $total += $order->getTotal()
                            + ($order->getServiceDeliverCost() * $this->inclDeliver($order))
                            - $order->getDiscountAmount(false);
                    continue;
                }

                /**
                 * we want all bill amounts and the current amount is 
                 * bill too, so we just add all coveredAmounts and budgets
                 */
                if ($order->getPayment() == 'bill' && $billType == 'bill') {
                    foreach ($order->getCompanyGroupMembers() as $member) {
                        $total += intval($member[1]) + intval($member[6]);
                        if ($member[2] == 'bill') {
                            $total += intval($member[3]);
                        }
                    }
                    $total -= ( $order->getCourierCost() - $order->getCourierDiscount());
                    continue;
                }

                /**
                 * if the current order is an company order, but inherits private
                 * amounts, we check for that payment and add to the corresponding payment
                 */
                if ($order->getPayment() == 'bill' && $billType != 'bill') {
                    foreach ($order->getCompanyGroupMembers() as $member) {
                        if ($member[2] == $billType) {
                            $total += intval($member[3]);
                        }
                    }
                    continue;
                }

                /**
                 * in any other case, we just crosscheck the payment
                 * and add up if matching
                 */
                if ($order->getPayment() == $billType) {
                    $total += $order->getTotal()
                            + ($order->getServiceDeliverCost() * $this->inclDeliver($order))
                            - $order->getDiscountAmount(false);
                }
            }
        }


        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function getDeliverCostTotal($billType = 'all', $include_base = true, $include_satellite = true) {
        $total = 0;
        $orders = $this->getOrdersByPayment($billType, $include_base, $include_satellite);
        if (count($orders) > 0) {
            foreach ($orders as $order) {
                $total += $order->getServiceDeliverCost();
            }
        }
        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function getSoldPfandTotal($billType = 'all', $include_base = true, $include_satellite = true) {
        $total = 0;
        $orders = $this->getOrdersByPayment($billType, $include_base, $include_satellite);
        if (count($orders) > 0) {
            foreach ($orders as $order) {
                $total += $order->getSoldPfand();
            }
        }
        return $total;
    }

    /**
     * return the amount which will be charged for this service
     * @author Matthias Laug <laug@lieferando.de>
     * @since 31.01.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return integer
     */
    public function getProvAmountOfOrder(Yourdelivery_Model_Order_Abstract $order) {
        $total = $order->getTotal() - $order->getSoldPfand();
        if ($order->getService()->isBillDeliverCost()) {
            $total += $order->getServiceDeliverCost();
        }
        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function getProvTotal($billType = 'all', $include_base = true, $include_satellite = true) {
        $total = 0;

        $orders = $this->getOrdersByPayment($billType, $include_base, $include_satellite);
        if (count($orders) > 0) {
            foreach ($orders as $order) {
                $total += $this->getProvAmountOfOrder($order);
            }
        }

        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function getCommTotal($billType = 'all', $include_base = true, $include_satellite = true) {
        $total = 0;
        $orders = $this->getOrdersByPayment($billType, $include_base, $include_satellite);
        if (count($orders) > 0) {
            foreach ($orders as $order) {
                $total += $order->getCommission();
            }
        }

        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function getCommTaxTotal($billType = 'all', $include_base = true, $include_satellite = true) {
        $total = 0;

        $orders = $this->getOrdersByPayment($billType, $include_base, $include_satellite);
        if (count($orders) > 0) {
            foreach ($orders as $order) {
                $total += $order->getCommissionTax();
            }
        }

        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function calculateInvoiceNetto($billType = 'all', $include_base = true, $include_satellite = true, $include_fee = true, $include_transaction = true) {
        $total = 0;
        $orders = $this->getOrdersByPayment($billType, $include_base, $include_satellite);
        if (count($orders) > 0) {
            foreach ($orders as $order) {
                $total += $order->getCommission();

                if ($include_transaction) {
                    $total += $order->getCharge();
                }
            }
        }

        if ($include_fee) {
            $total += $this->calculateFee();
        }

        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function calculateInvoiceBrutto($billType = 'all', $include_base = true, $include_satellite = true, $include_fee = true, $include_transaction = true) {
        return $this->calculateInvoiceNetto($billType, $include_base, $include_satellite, $include_fee, $include_transaction) * $this->getTax();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function calculateInvoiceTax($billType = 'all', $include_base = true, $include_satellite = true, $include_fee = true, $include_transaction = true) {
        return $this->calculateInvoiceBrutto($billType, $include_base, $include_satellite, $include_fee, $include_transaction) -
                $this->calculateInvoiceNetto($billType, $include_base, $include_satellite, $include_fee, $include_transaction);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function getCommBruttoTotal($billType = 'all', $include_base = true, $include_satellite = true) {
        $total = 0;
        $orders = $this->getOrdersByPayment($billType, $include_base, $include_satellite);
        if (count($orders) > 0) {
            foreach ($orders as $order) {
                $total += $order->getCommissionBrutto();
            }
        }

        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function getPfandTotal($billType = 'all') {
        $total = 0;
        $orders = $this->getOrdersByPayment($billType);
        if (count($orders) > 0) {
            foreach ($orders as $order) {
                $total += $order->getPfand();
            }
        }
        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $billType
     * @return int
     */
    public function getDiscountTotal($billType = 'all') {
        $total = 0;
        $orders = $this->getOrdersByPayment($billType);
        if (count($orders) > 0) {
            foreach ($orders as $order) {
                $total += $order->getDiscountAmount(false);
            }
        }
        return $total;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getAlreadyPayed($withBalance = false, $zeroBalance = true) {
        $balanceAmount = $this->getBalanceAmount();
        $payed = $this->getBrutto()
                - $this->getCashTotal('all')
                - $this->getPfandTotal()
                + ($balanceAmount * $withBalance);
        if ($payed < 0 && $zeroBalance) {
            return 0;
        }
        return $payed;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 15.06.2011
     * @return interger
     */
    public function getBalanceAmount($boundary = 10000000) {
        $balanceAmount = $this->getService()->getBalance()->getAmount();
        if ($balanceAmount < 0 && $balanceAmount < (-1) * $boundary) {
            return (-1) * $boundary;
        } elseif ($balanceAmount > 0 && $balanceAmount > $boundary) {
            return $boundary;
        }
        return $balanceAmount;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 04.09.2011
     * @return array
     */
    public function getBalanceList() {
        $list = $this->getService()->getBalance()->getList();
        $final_list = array();
        foreach ($list as $elem) {

            /* if ( strstr($elem['comment'], 'reset billing') ){
              continue;
              } */

            $bill = null;
            if ((integer) $elem['billingId'] > 0) {
                try {
                    $bill = new Yourdelivery_Model_Billing((integer) $elem['billingId']);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    
                }
            }

            $amount = $elem['amount'];
            if ($amount < 0) {
                $amount = '(' . intToPrice(abs($amount)) . ')';
            } else {
                $amount = intToPrice($amount);
            }
            $final_list[] = array(
                'amount' => $amount,
                'comment' => $elem['comment'],
                'created' => $elem['created'],
                'reference' => $bill
            );
        }
        return $final_list;
    }

    public function getNewBalanceAmount() {

        $balance = $this->getService()->getBalance();
        $balanceAmount = $balance->getAmount();
        $voucherAmount = $this->getVoucherAmount(false);
        $billingAmount = $this->calculateBillingAmount(false, false);
        $alreadyPayed = $this->getAlreadyPayed();

        $amount = 0;

        //CASE 1: The balance is positive and we would have got an invoice either ways
        if ($balanceAmount > 0 && $billingAmount > 0 && $balanceAmount < $billingAmount) {
            $amount = (-1) * $billingAmount;
        }

        //CASE 2: The balance is positive and we only get an invoice, because of this balance
        //THIS CASE CAN NEVER HAPPEN       
        //CASE 3: The balance is positive and we would have got a voucher either ways
        //in this case, the voucher amount gets higher and the balance will be tild
        if ($balanceAmount > 0 && $voucherAmount > 0 && $billingAmount == 0) {
            $amount = (-1) * $balanceAmount;
        }

        //CASE 4: The balance is positive and we only get an voucher, because the billing amount
        //is smaller than the positiv balance amount
        if ($balanceAmount > 0 && $billingAmount < $balanceAmount) {
            $amount = (-1) * $balanceAmount;
        }

        //CASE 5: The balance is negative and we would have got an invoice either ways
        //in this case, the invoice gets higher and the balance will be tild
        if ($balanceAmount < 0 && $billingAmount > 0) {
            $amount = (-1) * $balanceAmount;
        }

        //CASE 6: The balance is negative and we only get an invoice, because of this balance
        //this means, the voucher amount is smaller then the balance amount. We tild
        //the balance with the given voucher amount
        if ($balanceAmount < 0 && $voucherAmount > 0 && $voucherAmount < abs($balanceAmount)) {
            $amount = $voucherAmount;
        }

        //CASE 7: The balance is negative and we would have got a voucher either ways
        //in this case, the voucher amount is bigger than the negative balance amount.
        //Balance gets tild and voucher gets bigger
        if ($balanceAmount < 0 && $voucherAmount > 0 && $voucherAmount > abs($balanceAmount)) {
            $amount = (-1) * $balanceAmount;
        }

        //CASE 8: The balance is negative and we would have get an voucher because of that
        //THIS CASE CAN NEVER HAPPEN

        return $amount;
    }

    /**
     * if there is any open balance, recalculate it
     * @author Matthias Laug <laug@lieferando.de>
     * @param integer $id
     * @return boolean
     * @since 12.06.2011
     */
    public function updateBalance($id) {

        $amount = $this->getNewBalanceAmount();

        if ($amount == 0) {
            return true;
        }

        $balance = $this->getService()->getBalance();
        return $balance->addBalance($amount, __('Verrechnung aus Rechnung %s', $this->getNumber()), true, $id, $this->until, $this->from);
    }

    /**
     * return the object associated to this bill
     * @return Yourdelivery_Model_Servicetype_Abstract
     */
    public function getObject() {
        return $this->getService();
    }

    /**
     * generate pdf
     * @author Matthias Laug <laug@lieferando.de>
     * @param boolean $test
     * @param Zend_Db_Table_Row_Abstract $row
     * @param array $orders
     * @param array $assets
     * $param boolean $createPdf
     * @return boolean
     */
    public function create($test = false, Zend_Db_Table_Row_Abstract $row = null, array $orders = array(), array $assets = array(), $createPdf = true, $checkForZeroOrder = true, $crefo = true) {
        try {

            $this->orders = null;
            $this->old_orders = $orders;
            $this->old_assets = $assets;

            $service = $this->getService();

            if (!is_object($service)) {
                return false;
            }

            if ($this->until >= time()) {
                return false;
            }

            if ($checkForZeroOrder && count($this->getOrders()) == 0 && count($this->asset->getBillingAssets()) == 0) {
                $this->info('Für diese Zeitraum wurden keine Bestellungen oder Rechnungsposten gefunden');
                $this->logger->info('no orders found for service ' . $service->getName());
                return false;
            }

            $this->logger->debug('Creating bill for service ' . $service->getName());
            $this->preflyCheck();

            //generate bill
            $this->_latex->assign('service', $service);
            $this->_latex->assign('bill', $this);
            $customized = $this->getCustomized();
            $this->_latex->assign('header', $customized); //deprecated
            $this->_latex->assign('custom', $customized);

            $file_exists = true;
            if ($createPdf) {
                $file = $this->_latex->compile(true, true);
                $file_exists = file_exists($file);
                if ($file_exists) {
                    $this->_storage = new Default_File_Storage();
                    $this->_storage->setSubFolder('billing');
                    $this->_storage->setSubFolder('service');
                    $this->_storage->setSubFolder(substr($this->getNumber(), 2, 4));
                    $this->_storage->store($this->getNumber() . '.pdf', file_get_contents($file), null, true);
                } else {
                    $this->logger->err('Could not create bill pdf');
                    return false;
                }
            }

            //store in database
            if (is_null($row) || !$row instanceof Zend_Db_Table_Row_Abstract) {
                $row = $this->getTable()->createRow();
                $row->status = 0;
            }

            $row->mode = 'rest';
            $row->refId = $this->getService()->getId();
            $row->from = date('Y-m-d H:i:s', $this->from);
            $row->until = date('Y-m-d H:i:s', $this->until);
            $row->number = $this->getNumber();

            $row->brutto = (integer) $this->getBrutto();

            /**
             * @author Matthias Laug <laug@lieferando.de>
             * here we store our tax and item values. since we do not know
             * which tax types are currently set we have two free configurable
             * slots to hold the values
             */
            $currentItemStack = 1;
            foreach ($this->config->tax->types->toArray() as $taxtype) {
                $itemRow = sprintf('item%dValue', $currentItemStack);
                $taxRow = sprintf('tax%dValue', $currentItemStack);
                $keyRow = sprintf('item%dKey', $currentItemStack);

                $row->$itemRow = $this->calculateItem('all', $taxtype);
                $row->$taxRow = $this->calculateTax('all', $taxtype);
                $row->$keyRow = $taxtype;

                $currentItemStack++;
            }

            $row->discount = (integer) $this->getDiscountTotal();
            $row->pfand = (integer) $this->getPfandTotal();

            //commissions
            $row->prov = $this->getCommBruttoTotal();

            //store payment methods
            $row->paypal = (integer) $this->getTotalTotal('paypal');
            $row->credit = (integer) $this->getTotalTotal('credit');
            $row->bill = (integer) $this->getTotalTotal('bill');
            $row->debit = (integer) $this->getTotalTotal('debit');
            $row->ebanking = (integer) $this->getTotalTotal('ebanking');
            $row->cash = (integer) $this->getCashTotal('all');
            $row->balance = (integer) $this->getBalanceAmount();

            $row->amount = $this->calculateBillingAmount(true, false);
            $row->voucher = 0;

            //only store if this is not a test
            $billId = $row->save();
            $this->logger->debug(sprintf('[NMB] Saving billId:%d', $billId ));
            if (!$billId) {
                return false;
            }

            //if we have any depth, we should pay them
            if ($this->getVoucherAmount(true) > 0) {

                $this->isVoucher = true;

                $file_exists = true;
                if ($createPdf) {
                    //generate voucher
                    $this->_latex->setTpl('bill/service/standard_voucher');
                    $file = $this->_latex->compile(true, true);
                    $file_exists = file_exists($file);
                    if ($file_exists) {
                        $this->_storage->store($this->getNumberVoucher() . '.pdf', file_get_contents($file), null, true);
                    }
                }

                if ($file_exists) {
                    //only store if this is not a test
                    $row->voucher = $this->getVoucherAmount(true);
                    $row->save();
                } else {
                    $this->logger->err('could not create voucher pdf');
                    return false;
                }
            }

            //append all orders to this bill
            foreach ($this->getOrders() as $order) {
                $order->billMe($billId, 'rest');
            }

            $this->updateBalance($billId);

            if (count($this->asset->getBillingAssets()) > 0) {
                return $this->asset->create($test, $row, $assets, $createPdf, $checkForZeroOrder);
            }

            return true;
        } catch (Exception $e) {
            $this->error($e->getMessage());
            $this->logger->crit('Could not generate service bill' . $e->getMessage());
            return false;
        }
    }

}
