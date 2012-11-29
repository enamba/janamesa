<?php

/**
 * order object to store and work with orders
 * @copyright yourdelivery
 * @author Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Model_Order extends Yourdelivery_Model_Order_Abstract {

    /**
     * @var Yourdelivery_Model_Location
     */
    protected $_location = null;

    /**
     * @var SplObjectStorage
     */
    protected $_projects = null;

    /**
     * @var SplObjectStorage
     */
    protected $_costcenters = null;

    /**
     * @var Yourdelivery_Model_Customer_Abstract
     */
    protected $_customer = null;

    /**
     * @since 14.08.2010
     * @var array
     */
    public $amounts = null;

    /**
     * check if an order is finished
     * @author vpriem
     * @since 08.09.2010
     * @return boolean
     */
    public function isFinished() {
        return true;
    }

    /**
     * get human readable state
     * @author Matthias Laug <laug@lieferando.de>
     * @author Jens Naie <naie@lieferando.de>
     * @param int $state
     * @return string
     */
    static function stateName($state) {
        switch ($state) {
            default:
            case Yourdelivery_Model_Order::AFFIRMED: {
                    return __('Bestätigt');
                }
            case Yourdelivery_Model_Order::DELIVERED: {
                    return __('Ausgeliefert');
                }
            case Yourdelivery_Model_Order::NOTAFFIRMED: {
                    return __('Nicht bestätigt');
                }
            case Yourdelivery_Model_Order::DELIVERERROR: {
                    return __('Fehler');
                }
            case Yourdelivery_Model_Order::FAX_ERROR_NO_TRAIN: {
                    return __('Fax eventuell durchgegangen (NO_TRAIN)');
                }
            case Yourdelivery_Model_Order::STORNO: {
                    return __('Storniert');
                }
            case Yourdelivery_Model_Order::COMPANYORDER: {
                    return __('Private Firmenbestellung, erwarte Bestätigung');
                }
            case Yourdelivery_Model_Order::FAKE: {
                    return __('Fake Bestellung');
                }
            case Yourdelivery_Model_Order::PAYMENT_NOT_AFFIRMED: {
                    return __('Bezahlung unbestätigt');
                }
            case Yourdelivery_Model_Order::INVALID_DISCOUNT: {
                    return __('Storniert wegen ungültigem Gutschein');
                }
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @tested models/OrderTest.php/testGetAllOrders()
     * @return SplObjectStorage
     */
    static function all() {
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->query('select id from orders')->fetchAll();
        $orders = new SplObjectStorage();
        foreach ($result as $c) {
            $order = new Yourdelivery_Model_Order($c['id']);
            $orders->attach($order);
        }
        return $orders;
    }

    /**
     * get all orders
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.08.2010
     * @tested models/OrderTest.php/testGetAllOrdersFast()
     * @return array
     */
    static function allFast() {
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                ->from('orders');
        return $db->fetchAll($query);
    }

    /**
     * get unregistered orders
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 04.08.2010
     * @param int $days
     * @return SplObjectStorage
     */
    static function allPrivateUnregistered($days = 0) {
        $db = Zend_Registry::get('dbAdapter');
        $table = new Yourdelivery_Model_DbTable_Order();

        $result = $table->allPrivateUnregisteredFidelityArray($days);
        $orders = new SplObjectStorage();
        foreach ($result as $o) {
            $order = new Yourdelivery_Model_Order($o['id']);
            $orders->attach($order);
        }
        return $orders;
    }

    /**
     * get all company orders
     * @author Matthias Laug <laug@lieferando.de>
     * @return SplObjectStorage
     */
    static function allCompany() {
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->query('select id from orders where kind="comp"')->fetchAll();
        $orders = new SplObjectStorage();
        foreach ($result as $c) {
            $order = new Yourdelivery_Model_Order($c['id']);
            if ($order->getState() >= 0) {
                $orders->attach($order);
            }
        }
        return $orders;
    }

    /**
     * get orders from sales channel
     * @author vpriem
     * @since 23.02.2011
     * @param string $channel
     * @return arrray
     */
    static function allSaleChannel($channel) {
        $db = Zend_Registry::get('dbAdapter');
        return $db->fetchAll(
                        "SELECT SUBSTRING_INDEX(o.saleChannel, ?, 1) `saleChannel`, DATE(o.time) `date`, COUNT(o.id) `orders`
            FROM `orders` o
            WHERE o.state > 0
                AND o.saleChannel LIKE ?
            GROUP BY `saleChannel`, `date`", array("?", "%" . $channel . "%")
        );
    }

    /**
     * Create order from hash for downloading
     * @author vpriem
     * @since 08.10.2010
     * @return Yourdelivery_Model_Order|boolean
     */
    static function createFromHash($hash) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $row = $db->fetchRow(
                "SELECT `id`
            FROM `orders`
            WHERE hashtag = ?
            LIMIT 1", array($hash)
        );
        if ($row['id']) {
            try {
                return new Yourdelivery_Model_Order($row['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

            }
        }
        return false;
    }

    /**
     * get last 3 customer orders to display in admin
     * @author jnaie
     * @since 22.06.2012
     * @return array
     */
    static function latestFromCustomer($customerId, $limit = 3) {
        if (empty($customerId)) {
            return null;
        }
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()->from('orders', array('id'))
                ->limit($limit)
                ->where('orders.customerId = ?', $customerId)
                ->order('time DESC');


        $orders = $db->fetchAll($query);
        foreach ($orders as $i => $orderArr) {
            $order = new Yourdelivery_Model_Order($orderArr['id']);
            $orders[$i]['ID'] = $order->getId();
            $orders[$i]['Bestellnummer'] = $order->getNr();
            $orders[$i]['restaurantId'] = $order->getRestaurantId();
            $orders[$i]['Dienstleister'] = $order->getService()->getName();
            $orders[$i]['Preis'] = $order->getTotal() / 100;
            $orders[$i]['History'] = $order->getTable()->getStateHistory();
            if ($orders[$i]['History']) {
                foreach ($orders[$i]['History'] as $j => $history) {
                    $orders[$i]['History'][$j]['status'] = self::stateName($orders[$i]['History'][$j]['status']);
                }
            }
        }

        return $orders;
    }

    /**
     * this constructor must get an valid integer AND
     * should call getCard at least once
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.03.2011
     * @param integer $id
     */
    public function __construct($id, $withCard = true) {
        parent::__construct($id);
        if ($withCard) {
            $this->getCard();
            if (is_null($this->_card)) {
                $this->logger->crit('Could not load cart for order ' . $id);
                throw new Yourdelivery_Exception_Database_Inconsistency('Could not load cart for order ' . $id);
            }
        }
    }

    /**
     * Get hash for downloading
     * @author vpriem
     * @since 08.10.2010
     * @return string
     */
    public function getHash() {
        return Default_Helpers_Crypt::hash($this->getId());
    }

    public function getHashFromNr() {
        /**
         * @todo: migrate to getHash (this is just a temporary helper
         */
        return md5(SALT . $this->getNr());
    }

    /**
     * check if order is a favourite of customer
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function isFavourite() {
        return $this->getTable()
                        ->isFavourite();
    }

    /**
     * @todo: implement once we use ranges instead of plz
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function calculateDeliverRange() {
        return 10;
    }

    /**
     * get favourite name
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getFavName() {
        if ($this->isFavourite()) {
            $name = $this->getTable()
                            ->getFavourite()
                            ->current()
                    ->name;
            return lcfirst($name);
        }
        return null;
    }

    /**
     * get favourite id
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getFavId() {
        if ($this->isFavourite()) {
            return $this->getTable()
                            ->getFavourite()
                            ->current()
                    ->id;
        }
        return null;
    }

    /**
     * get rating of completed order
     * @todo should return the Rating Object not the rowset...
     * @author Matthias Laug <laug@lieferando.de>
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRating() {
        return $this->getTable()->getRating();
    }

    /**
     * Rate this order if has been finished
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $customerId
     * @param int $delivery
     * @param int $quality
     * @param string $comment
     * @return boolean
     */
    public function rate($customerId = null, $quality = null, $delivery = null, $comment = null, $title = null, $advise = null, $author = null) {
        //only add fidelity points, if this order has not been rated before
        if (!$this->isRated()) {
            $action = 'rate_low';

            //string must be greater 50, must contain more than 5 words and more than 10 unique chars
            if (strlen($comment) >= 50 && count(array_unique(str_split($comment))) > 10 && str_word_count($comment) > 5) {
                $action = 'rate_high';
            }

            //if no comment is given, activate this rating at once
            $status = (strlen($comment) == 0) ? 0 : -1;

            $this->getCustomer()->getFidelity()->addTransaction($action, $this->getId(), $this->getCustomer()->getFidelity()->getPointsForAction($action), $status);
        }
        return $this->getTable()->rate($customerId, $delivery, $quality, $comment, $title, $advise, $author);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @return string
     * @since 19.06.2012
     * @return array|string
     */
    public function getLastState($key = null) {

        $last = $this->getStateHistory()->current();
        return $key === null ? $last : $last[$key];
    }

    /**
     * get last timestamp, where a state changed
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getLastStateChange() {

        return strtotime($this->getLastState('created'));
    }

    /**
     * get last comment, when the state was changed
     * @author alex
     * @return string
     * @since 28.02.2011
     */
    public function getLastStateComment() {
        $message = $this->getLastState('message');

        if ($message) {
            return Yourdelivery_Model_Order_StatusMessage::createFromString($message)->getTranslateMessage();
        } else {
            return $this->getLastState('comment');
        }
    }

    /**
     * get last state, when the state was changed
     * @author Allen Frank <frank@lieferando.de>
     * @return string
     * @since 11.05.2012
     */
    public function getLastStateStatus() {

        return $this->getLastState('status');
    }

    /**
     * get state history
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getStateHistory() {

        return $this->getTable()
                        ->getStateHistory();
    }

    /**
     * Get send by history
     *
     * @author Vincent Priem <priem@lieferando.de>
     * @since 15.12.2011
     * @return Zend_Db_Table_Rowset
     */
    public function getSendbyHistory() {

        return $this->getTable()
                        ->getCurrent()
                        ->findDependentRowset("Yourdelivery_Model_DbTable_Order_Sendby");
    }

    /**
     * get certains state from the states history
     * @author alex
     * @since 20.01.2011
     * @return string
     */
    public function getStateFromHistory($status) {
        $history = $this->getStateHistory();
        foreach ($history as $st) {
            if ($st['status'] == $status) {
                //remove wrongly described reason, usually : "this order has been marked as fraud, reason: Grund:"
                if ($ind = strpos($st['comment'], 'Grund:')) {
                    $st['comment'] = substr($st['comment'], $ind + 6);
                }
                return $st;
            }
        }

        return null;
    }

    /**
     * get human readable state
     * @author Matthias Laug <laug@lieferando.de>
     * @author Jens Naie <naie@lieferando.de>
     * @return string
     */
    public function getStateName() {
        $state = $this->getState();
        return self::stateName($state);
    }

    /**
     * get all ids of meals from card
     * @author Matthias Laug <laug@lieferando.de>
     * @since 17.06.2011
     * @return integer
     */
    public function getMealIds() {
        $meals = $this->getTable()->getMeals();
        $mealIds = array();
        foreach ($meals as $meal) {
            $mealIds[] = $meal->mealId;
        }
        return $mealIds;
    }

    /**
     * get all meals from order
     * @author Jens Naie <naie@lieferando.de>
     * @since 17.06.2011
     * @return integer
     */
    public function getMeals() {
        return $this->getTable()->getMeals();
    }

    /**
     * get current card
     * @author Matthias Laug <laug@lieferando.de>
     * @param boolean $overwrite
     * @param boolean $check_available
     * @return array
     */
    public function getCard($overwrite = true, $check_available = false, $msg = false) {
        if (!is_null($this->_card) && !$check_available) {
            return $this->_card;
        } elseif ($check_available) {
            $this->clearBucket();
        }

        $meals = $this->getTable()->getMeals();
        if ($meals === null || ($meals instanceof Zend_Db_Table_Rowset_Abstract && $meals->count() == 0 )) {
            return $this->_card;
        }

        foreach ($meals as $meal) {
            try {
                $m = new Yourdelivery_Model_Meals($meal->mealId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                //fuck the meal is lost, but that should work
                $m = new Yourdelivery_Model_Meals();
                $m->setService($this->getService());
            }

            //overwrite with original name
            $m->setName($meal->name);

            //check if still available
            if ($check_available && !$m->isAvailable()) {
                if ($msg) {
                    $this->error(__(sprintf('Das Gericht "%s" ist leider nicht mehr verfügbar', $m->getName())));
                }
                continue;
            }

            $customer = null;
            if ($meal->customerId) {
                try {
                    $customer = new Yourdelivery_Model_Customer($meal->customerId);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    //what to do here
                }
            } else {
                $data = $this->getTable()->getCustomer();
                if (!is_null($data)) {
                    $customer = new Yourdelivery_Model_Customer_Anonym();
                    $customer->setData($data->toArray());
                }
            }

            //set cost saved in order, not in meal row
            if ($overwrite) {
                try {
                    $m->setCurrentSize($meal->sizeId);
                } catch (Exception $e) {
                    //uff the size is no longer attached to this meal, lets take the overwritten
                    //values, but we cannot get any further information
                    $this->logger->warn(sprintf('could not connect meal %s with size %s, but using written variables', $meal->mealId, $meal->sizeId));
                }
                $m->setCost($meal->cost);
                $m->setCurrentTax($meal->tax);
                $m->setCurrentPfand($meal->pfand);
            } else {
                //should be determined by current data
                try {
                    $m->setCurrentSize($meal->sizeId);
                } catch (Exception $e) {
                    $this->logger->crit(sprintf('could not connect meal %s with size %s', $meal->mealId, $meal->sizeId));
                    continue;
                }
                $m->setDeleted(true);
                $m->setCost(null);
            }

            //store extras and options
            $opt_ext = array();
            //get all options
            $options = $this->getTable()->getMealOptions($meal->id);
            $opt_ext['options'] = array();
            if (!is_null($options)) {
                foreach ($options as $opt) {
                    $opt_ext['options'][] = $opt->optionId;
                    $opt_ext['options_cost'][$opt->optionId] = $opt->cost;
                    $opt_ext['options_name'][$opt->optionId] = $opt->name;
                    $opt_ext['options_mwst'][$opt->optionId] = $opt->tax;
                }
            }
            //get all mealoptions (half pizza)
            $mealoptions = $this->getTable()->getMealMealOptions($meal->id);
            $opt_ext['mealoptions'] = array();
            if (!is_null($mealoptions)) {
                foreach ($mealoptions as $opt) {
                    $opt_ext['mealoptions'][] = $opt->mealId;
                    $opt_ext['mealoptions_cost'][$opt->mealId] = $opt->cost;
                    $opt_ext['mealoptions_name'][$opt->mealId] = $opt->name;
                    $opt_ext['mealoptions_mwst'][$opt->mealId] = $opt->tax;
                }
            }
            //get all extras
            $opt_ext['extras'] = array();
            $extras = $this->getTable()->getMealExtras($meal->id);
            if (!is_null($extras)) {
                foreach ($extras as $ext) {
                    $opt_ext['extras'][] = array('id' => $ext->extraId, 'count' => $ext->count);
                    $opt_ext['extras_cost'][$ext->extraId] = $ext->cost;
                    $opt_ext['extra_mwst'][$ext->extraId] = $ext->tax;
                    $opt_ext['extra_name'][$ext->extraId] = $ext->name;
                }
            }

            $opt_ext['special'] = $meal->special;
            //add meal to card
            $this->addMeal($m, $opt_ext, $meal->count, $customer, $meal->id);
        }
        return $this->_card;
    }

    /**
     * Get order location
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.09.2010 (vpriem)
     * @return Yourdelivery_Model_Location
     */
    public function getLocation() {
        if ($this->_location !== null) {
            return $this->_location;
        }

        $location = new Yourdelivery_Model_Location();

        $data = $this->getTable()->getLocation();
        if ($data !== null && $data !== false) {
            return $this->_location = $location->setData($data->toArray());
        }
        return null;
    }

    /**
     * amount of ordered items
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCardCount() {
        $count = 0;
        $meals = $this->getTable()->getMeals();
        foreach ($meals as $meal) {
            $count += $meal->count;
        }
        return $count;
    }

    /**
     * get courier if any
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.09.2010
     * @return Yourdelivery_Model_Courier
     */
    public function getCourier() {
        if (!is_null($this->getCourierId())) {
            try {
                return new Yourdelivery_Model_Courier($this->getCourierId());
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * get brutto commission of courier
     * @since 29.09.2010
     * @author Matthias Laug <laug@lieferando.de>
     * @return float
     */
    public function getCommissionBruttoCourier() {
        return $this->getCommissionCourier() + $this->getCommissionTaxCourier();
    }

    /**
     * get commission from courier
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.09.2010
     * @return float
     */
    public function getCommissionCourier() {
        $netto = $this->getCourierCost() + $this->getCourierDiscount();
        $comm = $this->getCourier()->getCommission();
        return ($netto / 100) * $comm;
    }

    /**
     * get tax of commission (19%)
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.09.2010
     * @return float
     */
    public function getCommissionTaxCourier() {
        return $this->getCommissionCourier() * 0.19;
    }

    /**
     * get commission sum of all commission types
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCommission() {
        if (!is_null($this->com)) {
            return $this->com;
        }
        $this->com = $this->getCommissionEach() + $this->getCommissionPercent() + $this->getCommissionItem();
        return $this->com;
    }

    /**
     * get brutto of commission
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCommissionBrutto() {
        return $this->getCommission() + $this->getCommissionTax();
    }

    /**
     * get tax of brutto
     * @author Matthias Laug <laug@lieferando.de>
     * @return float
     */
    public function getCommissionTax() {
        $netto = floatval($this->getCommission());
        return $netto * ($this->config->tax->provision / 100);
    }

    /**
     * get commission per sold item
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCommissionItem() {
        if (strlen($this->isSatellite()) > 0) {
            $com = $this->getService()->getItemSat();
        } else {
            $com = $this->getService()->getItem($this->getDeliverTime());
        }
        return $this->getContract() * ($com * $this->getCardCount());
    }

    /**
     * get relative commission of all sold items
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCommissionPercent() {
        if (strlen($this->isSatellite()) > 0) {
            $percent = $this->getService()->getKommSat();
        } else {
            $percent = $this->getService()->getCommission($this->getDeliverTime());
        }

        $deliver = $this->getService()->getBillDeliverCost();
        $total = $this->getTotal()
                + ($this->getServiceDeliverCost() * $deliver)
                - $this->getSoldPfand();
        return $this->getContract() * ($total / 100 * $percent);
    }

    /**
     * get commission per order
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCommissionEach() {
        if (strlen($this->isSatellite()) > 0) {
            return $this->getContract() * $this->getService()->getFeeSat();
        }
        return $this->getContract() * $this->getService()->getFee($this->getDeliverTime());
    }

    /**
     * get used budget for this order, if it is an company order
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getBudgetAmount($customer = null, $ownBudget = true) {
        if ($this->getKind() != "comp") {
            return 0;
        } else {
            $amount = 0;
            foreach ($this->getCompanyGroupMembers() as $member) {
                $currentM = $member[0];
                if (!is_null($customer) && $customer->getId() == $currentM->getId()) {
                    return intval($member[1]);
                } else {
                    if (!$ownBudget && $customer->getId() == $this->getcustomer()->getId()) {
                        continue;
                    }
                    $amount += intval($member[1]);
                }
            }
            return $amount;
        }
    }

    /**
     * get the amount, the company has to pay
     * @author Matthias Laug <laug@lieferando.de>
     * @param Yourdelivery_Model_Customer $customer
     * @return int
     */
    public function getCompanyAmount($customer = null) {
        if ($this->getKind() == "comp") {
            if ($this->getMode() == "rest") {
                return $this->getBudgetAmount();
            } else {
                return $this->getAbsTotal();
            }
        }
        return 0;
    }

    /**
     * get mode of order as human readable value
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getModeReadable() {
        switch ($this->getMode()) {
            default: {
                    return "Unbekannt";
                }
            case 'rest': {
                    return "Lieferservice";
                }
            case 'cater': {
                    return "Catering";
                }
            case 'great': {
                    return "Großhandel";
                }
            case 'fruit': {
                    return "Obsthandel";
                }
        }
    }

    /**
     * Get deliver cost
     * @author vpriem
     * @since 26.11.2010
     * @return int
     */
    public function getDeliverCost() {

        return $this->getServiceDeliverCost()
                + $this->getCourierCost()
                - $this->getCourierDiscount();
    }

    /**
     * set payment and change data according
     * @author Matthias Laug <laug@lieferando.de>
     * @since 19.07.2011
     */
    public function setPayment($payment = 'bar', $checkPayment = true) {
        if (!in_array($payment, array('bar', 'credit', 'bill', 'paypal', 'debit', 'ebanking'))) {
            $payment = 'bar';
        }

        $orderRow = $this->getRow();

        if ($this->getKind() == "priv") {
            $this->_data['payment'] = $payment;
            $orderRow->payment = $payment;
        } else {
            $rowCompany = $this->getTable()->getCompanyGroupOrderRow($this->getCustomer()->getId());
            $rowCompany->payment = $payment;
            $rowCompany->save();
        }

        $this->_data['currentPayment'] = $payment;

        $orderRow->charge = $this->getService()->getTransactionCost($payment, $this->getBucketTotal() + $this->getServiceDeliverCost());
        $orderRow->save();
        $this->setCharge($orderRow->charge);
    }

    /**
     *
     * get absolute total, all group members together
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.10.2010
     * @param boolean $charge
     * @param boolean $discount
     * @param boolean $deliver
     * @param boolean $credit
     * @param boolean $budget
     * @param boolean $ownBudget
     * @param boolean $floorfee
     * @param boolean $customer
     * @return int
     */
    public function getAbsTotal($charge = false, $discount = true, $deliver = true, $credit = true, $budget = true, $ownBudget = true, $floorfee = true, $customer = null) {

        $total = 0;
        if (is_object($customer)) {
            $groupRow = $this->getTable()->getGroupRow($customer->getId());
            $total = $groupRow->total;

            if ($charge) {
                $total += $groupRow->charge;
            }

            if ($discount) {
                $total -= $groupRow->discountAmount;
            }

            $currentCust = $this->getCustomer();
            if ($currentCust instanceof Yourdelivery_Model_Customer_Anonym || ( $customer->getId() == $currentCust->getId())) {
                if ($deliver) {
                    $total += $this->getDeliverCost();
                    /**
                     * adding floorfee / etagenzuschlag
                     * @author Felix Haferkorn <haferkorn@lieferando.de>
                     * @since 30.03.2011
                     */
                    if ($floorfee) {
                        $total += $this->getFloorFeeCost();
                    }
                }
            }

            if ($budget) {
                $total -= $this->getBudgetAmount($customer);
            }
        } else {
            $total = $this->getTotal();

            if ($charge) {
                $total += $this->getCharge();
            }

            if ($discount) {
                $total -= $this->getDiscountAmount();
            }

            if ($deliver) {
                $total += $this->getDeliverCost();
                /**
                 * adding floorfee / etagenzuschlag
                 * @author Felix Haferkorn <haferkorn@lieferando.de>
                 * @since 30.03.2011
                 */
                if ($floorfee) {
                    $total += $this->getFloorFeeCost();
                }
            }

            if ($budget) {
                $total -= $this->getBudgetAmount(null, $ownBudget);
            }
        }
        return $total;
    }

    /**
     * get total amount without taxes
     * @deprecated
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getTotalNoTax() {
        return $this->getItem(ALL_TAX);
    }

    /**
     * get pfand sold to customer
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getSoldPfand() {
        return $this->getTable()->getSoldPfand();
    }

    /**
     * get pfand of this order
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getPfand() {
        $pfand = $this->_data['pfand'];
        if (empty($pfand) || is_null($pfand)) {
            return 0;
        }
        return intval($pfand);
    }

    /**
     * get associated table
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_DbTable_Order
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Order();
        }
        return $this->_table;
    }

    /**
     * get order number as string
     * @deprecated
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getNumber() {
        return $this->getNr();
    }

    /**
     * check if order is an group order
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function isGroupOrder() {
        return false;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function isSingleOrder() {
        return true;
    }

    /**
     * check if this is a company group order
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function isCompanyGroupOrder() {
        /**
         * there are no group orders any longer
         *
         * @author Felix Haferkorn
         * @since 07.12.2011
         */
        return false;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function isCompanyOrder() {
        return ( $this->getKind() == self::COMPANYORDER );
    }

    /**
     * @deprecated there is no such thing as a group title any more
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getGroupTitle() {
        return $this->getTable()->getGroupData()->name;
    }

    /**
     * get the customer either as registered from database
     * or as unregistered. in this case we reconstruct it
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Customer
     */
    public function getCustomer() {
        if ($this->_customer == null) {
            $customer = null;
            $customer = $this->getTable()->getCustomer();
            if (!is_object($customer)) {
                return new Yourdelivery_Model_Customer_Anonym();
            }
            $data = $customer->toArray();

            if ((integer) $this->_data['customerId'] == 0) {
                $customer = new Yourdelivery_Model_Customer_Anonym();
                $customer->setData($data);
            } else {
                try {
                    $customer = new Yourdelivery_Model_Customer($this->_data['customerId']);
                    if ($customer->isEmployee()) {
                        $customer = new Yourdelivery_Model_Customer_Company(
                                        $customer->getId(),
                                        $customer->getCompany()->getId()
                        );
                    }

                    $customer->setName($data['name']);
                    $customer->setPrename($data['prename']);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $customer = new Yourdelivery_Model_Customer_Anonym();
                    $customer->setData($data);
                }
            }
            $this->_customer = $customer;
        }

        return $this->_customer;
    }

    /**
     * get original data of customer
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Customer_Anonym
     */
    public function getOrigCustomer() {
        $data = $this->getTable()->getCustomer();
        $customer = new Yourdelivery_Model_Customer_Anonym();
        if (!is_null($data)) {
            //reconstruct user
            $customer->setData($data->toArray());
        }
        return $customer;
    }

    /**
     * get the discount fo an order
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Rabatt_Code
     */
    public function getDiscount() {
        $discount = $this->_data['rabattCodeId'];

        if (empty($discount) || is_null($discount)) {
            return null;
        }

        try {
            $rabatt = new Yourdelivery_Model_Rabatt_Code(null, $discount);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
        return $rabatt;
    }

    /**
     *
     * add Discount to Order
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param Yourdelivery_Model_Rabatt_Code $discount
     */
    public function addDiscount(Yourdelivery_Model_Rabatt_Code $discount) {
        if (is_object($discount) && $discount->isUsable(true)) {
            $total = $this->getAbsTotal(false, false, true, false, false, false);
            $discount->calcDiff($total);
            $this->setDiscountAmount($discount->getDiff());
            $this->setRabattCodeId($discount->getId());

            if ($discount->getParent()->isFidelity() && $this->getCustomer()->getFidelity()) {
                $this->getCustomer()->addFidelityPoint('usage', $this->getId(), (-1) * $this->getCustomer()->getFidelity()->getCashInNeed());
            }
        } else {
            $this->setDiscountAmount(0);
            $this->setRabattCodeId(null);
        }

        // save again to set discount values
        $row = $this->getTable()->getCurrent();
        $row->discountAmount = $this->getDiscountAmount();
        $row->rabattCodeId = $this->getRabattCodeId();
        $row->save();
    }

    /**
     *
     * remove Discount from Order
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean
     */
    public function removeDiscount() {
        $discount = $this->getDiscount();
        if ($discount) {
            try {
                $row = $this->getTable()->getCurrent();
                $row->rabattCodeId = null;
                $row->discountAmount = 0;
                $row->save();
                $discount->setCodeUnused();
            } catch (Zend_Db_Exception $e) {
                $this->logger->warn(sprintf('could not remove discount from order %s', $this->getId()));
                return false;
            }
            $this->_data['rabattCodeId'] = null;
            $this->_data['discountAmount'] = 0;
        }
        return true;
    }

    /**
     * get associated projectnumber
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Projectnumbers
     */
    public function getProject() {
        if (is_null($this->_project)) {
            $this->_projects = new SplObjectStorage();
            $projects = $this->getTable()->getProject();
            if (is_array($projects)) {
                foreach ($projects as $p) {
                    try {
                        if (intval($p['projectId']) <= 0) {
                            continue;
                        }

                        $found = false;
                        foreach ($this->_projects as $_p) {
                            if ($_p->getId() == $p['projectId']) {
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            $project = new Yourdelivery_Model_Projectnumbers($p['projectId']);
                            $project->setAddition($p['addition']);
                            $this->_projects->attach($project);
                        }
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        continue;
                    }
                }
            }
        }
        return $this->_projects;
    }

    /**
     * get associated costcenters
     * @author Matthias Laug <laug@lieferando.de>
     * @return SplObjectStorage
     */
    public function getCostcenter() {
        if (is_null($this->_costcenters)) {
            $this->_costcenters = new SplObjectStorage();
            $costcenters = $this->getTable()->getCostcenter();
            if (is_array($costcenters)) {
                foreach ($costcenters as $c) {
                    try {
                        if ($c['costcenterId'] <= 0) {
                            continue;
                        }

                        $found = false;
                        foreach ($this->_costcenters as $_c) {
                            if ($_c->getId() == $c['costcenterId']) {
                                $found = true;
                            }
                        }
                        if (!$found) {
                            $costcenter = new Yourdelivery_Model_Department($c['costcenterId']);
                            $this->_costcenters->attach($costcenter);
                        }
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        continue;
                    }
                }
            }
        }
        return $this->_costcenters;
    }

    /**
     * check if this order is marked as billed for company and restaurant
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    public function isPayed() {
        $billR = $this->getBillRest();
        $billC = $this->getBillCompany();
        $billCourier = $this->getBillCourier();
        return array(
            !empty($billR) || !is_null($billR) || $billR > 0,
            !empty($billC) || !is_null($billC) || $billC > 0,
            !empty($billCourier) || !is_null($billCourier) || $billCourier > 0
        );
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $paramId
     * @param string $mode
     */
    public function billMe($billId, $mode) {
        $this->getTable()->billMe($billId, $mode);
    }

    /**
     * Override this methode cause the parent is strange and tricky
     * @author Vincent Priem <priem@lieferando.de>
     * @param string|int $deliverTime
     */
    public function setDeliverTime($deliverTime, $day = NULL) {

        if (is_int($deliverTime)) {
            $deliverTime = date("Y-m-d H:i:s", $deliverTime);
        }

        $this->_data['deliverTime'] = $deliverTime;
    }

    /**
     * Return delivertTime as timestamp
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getDeliverTime() {

        return strtotime($this->_data['deliverTime']);
    }

    /**
     * Return time as timestamp
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getTime() {

        return strtotime($this->_data['time']);
    }

    /**
     * check if all group members applied to the group order
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function allGroupMembersApplied() {

        /**
         * check for timeout first
         */
        $start = $this->getTime();

        /**
         * then check if all members already applied
         */
        foreach ($this->getTable()->getGroupMembers() as $row) {
            //at least one must be set to 0, if this group order is not yet finished
            if ($row->status == 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * store this order in our matching table
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.01.2011
     */
    public function addToFraudMatching(array $post = array()) {
        $matchingRow = new Yourdelivery_Model_DbTable_Order_FraudMatching();
        $orderData = array();
        $orderData['orderId'] = $this->getId();
        $orderData['prename'] = $this->getCustomer()->getPrename();
        $orderData['name'] = $this->getCustomer()->getName();
        $orderData['email'] = $this->getCustomer()->getEmail();
        $orderData['tel'] = $this->getLocation()->getTel();
        $orderData['streetHausNr'] = $this->getLocation()->getStreet() . ' ' . $this->getLocation()->getHausnr();
        $orderData['plz'] = $this->getLocation()->getPlz();
        $orderData['comment'] = $this->getLocation()->getComment();
        $orderData['ip'] = $this->getIpAddr();
        $orderData['payment'] = $this->getPayment();

        if (isset($post['holder']) && isset($post['number'])) {
            $orderData['paymentName'] = $post['holder'];
            $orderData['paymentNumber'] = substr($post['number'], -4) . '-' . $post['verification'];
        }

        $orderData['paypalId'] = '';
        $orderData['created'] = null;

        return $matchingRow->createRow($orderData)->save();
    }

    /**
     * should be implemented to avoid errors
     * if any process fails
     */
    public function gesundheitsVorsorge() {

    }

    /**
     * end a running group
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function endGroupOrder() {
        $this->getTable()->endGroupOrder();
    }

    /**
     * get payed amount. this would be totals
     * which are payed using paypal, creditcard, credit or budget
     * or discount
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     * @modified 06.10.2011 fix for discount amount
     * @modified 27.11.2011 mlaug fix for discount and company amount
     */
    public function getPayedAmount($inclDeliverCost = true, $inclCourierCost = true, $inclDiscount = true) {
        $amount = 0;

        if ($this->getKind() == "priv") {
            if ($this->getPayment() != "bar") {
                $amount = $this->getTotal() + $this->getDeliverCost();
            } else {
                if ($inclDiscount) {
                    return $this->getDiscountAmount($inclCourierCost);
                }
                return 0;
            }
        } else {

            $members = $this->getCompanyGroupMembers();
            foreach ($members as $member) {
                $amount += intval($member[1]) + intval($member[6]);
                if (in_array($member[2], array('paypal', 'credit', 'ebanking'))) {
                    $amount += intval($member[3]);
                }
            }

            //first we add this amount
            $amount += $this->getDiscountAmount();
        }

        //no deliver cost
        if (!$inclDeliverCost) {
            $amount -= $this->getServiceDeliverCost();
        }

        //no courier cost
        if (!$inclCourierCost) {
            $amount -= ( $this->getCourierCost() - $this->getCourierDiscount());
        }

        //remove the discounts
        if (!$inclDiscount) {
            $amount -= $this->getDiscountAmount();
        }
        return $amount;
    }

    /**
     * get Discount Amount and check if we need to merge
     * it with the courier discount.
     * @param boolean $merge
     * @return int
     */
    public function getDiscountAmount($merge = true) {
        $discount = $this->_data['discountAmount'];
        if ($merge === false && $discount > ($this->getTotal()) + $this->getServiceDeliverCost()) {
            //substract discount for services (billings) so that we do not get
            //discounts above entire brutto
            $discount -= $this->getCourierCost();
            $discount += $this->getCourierDiscount();
            if ($discount < 0) {
                $discount = 0;
            }
        }
        return $discount;
    }

    /**
     * get the row of this order
     * @author Matthias Laug <laug@lieferando.de>
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getRow() {
        return $this->getTable()->getCurrent();
    }

    /**
     * get all group members
     * @author Matthias Laug <laug@lieferando.de>
     * @return SplObjectStorage
     */
    public function getGroupMembers() {
        /**
         * there are no group orders any longer
         *
         * @author Felix Haferkorn
         * @since 07.12.2011
         */
        return new SplObjectStorage();
    }

    /**
     * get all members how have to share their budget
     * @todo rework that!!!!
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    public function getCompanyGroupMembers() {
        $budget = array();
        foreach ($this->getTable()->getCompanyGroupMembers() as $memberRow) {
            try {
                $customer = new Yourdelivery_Model_Customer($memberRow->customerId); //do not depend on customer relation here
                $amount = $memberRow->amount;
                $payment = $memberRow->payment;
                $privAmount = $memberRow->privAmount;
                $costcenterId = $memberRow->costcenterId;
                $coveredAmount = $memberRow->coveredAmount;
                $projectId = $memberRow->projectId;
                $addition = $memberRow->projectAddition;
                try {
                    $code = new Yourdelivery_Model_Projectnumbers($projectId);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    continue;
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }

            $budget[] = array(
                $customer,
                $amount,
                $payment,
                $privAmount,
                $projectId,
                $costcenterId,
                $coveredAmount,
                $code,
                $addition,
                $memberRow->companyId);
        }
        return $budget;
    }

    /**
     * get cash amount of this order
     *
     * @author Matthias Laug <laug@lieferando.de>
     *
     * @return int
     */
    public function getCashAmount() {
        $cash = 0;
        if ($this->getKind() == "priv") {
            if ($this->getPayment() == "bar") {
                return $this->getAbsTotal(false);
            }
        }

        if ($this->getKind() == "comp") {
            $cash = 0;
            foreach ($this->getCompanyGroupMembers() as $member) {
                $customer = $member[0];
                $payment = $member[2];
                $priv = intval($member[3]);
                if ($payment == "bar") {
                    $cash += $priv;
                }
            }
            return $cash;
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.11.2010
     * @return Yourdelivery_Model_Order_Pdf_FaxAbstract
     */
    public function getFaxClass() {
        switch ($this->getKind()) {
            case 'comp': {
                    $pdf = new Yourdelivery_Model_Order_Pdf_Company_Single_Fax();
                    break;
                }
            case 'priv': {
                    $pdf = new Yourdelivery_Model_Order_Pdf_Private_Single_Fax();
                    break;
                }
        }
        return $pdf;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.11.2010
     * @return Yourdelivery_Model_Order_Pdf_FaxAbstract
     */
    public function getCourierFaxClass() {
        switch ($this->getKind()) {
            case 'comp': {
                    $pdf = new Yourdelivery_Model_Order_Pdf_Company_Single_FaxCourier();
                    break;
                }
            case 'priv': {
                    $pdf = new Yourdelivery_Model_Order_Pdf_Private_Single_FaxCourier();
                    break;
                }
        }
        return $pdf;
    }

    /*     * *** Editing an order **** */

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function renewOrder() {

        /**
         * we may think about calling preFinish again!
         * this should do all the jobs needed to recreate
         * the correct structure
         */
        $this->setTax7($this->getTax7());
        $this->setTax19($this->getTax19());

        $this->setItem7($this->getItem7());
        $this->setItem19($this->getItem19());

        $this->setTotal($this->getBucketTotal());

        /**
         * @todo: calculate new discount amount
         * @todo: edit budget
         * @todo: what else may be different?
         * @todo: create job for new bill (if any)
         */
        $this->save();
    }

    /**
     * change the tax (MwSt) of a meal
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @param Yourdelivery_Model_Order_BucketMeals $meal
     * @param int $tax
     * @return boolean
     */
    public function changeTaxOfMeal($meal, $tax) {
        if (!is_object($meal) || !in_array($tax, array(7, 19))) {
            return false;
        }
        try {
            $meal->setTax($tax);
            $meal->save();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return false;
        }
        return true;
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function changeTaxOfExtra($meal, $extraId, $tax) {
        if (!is_object($meal) || !in_array($tax, array(7, 19))) {
            return false;
        }
        return true;
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function changeTaxOfOption($meal, $optionId, $tax) {
        if (!is_object($meal) || !in_array($tax, array(7, 19))) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param Yourdelivery_Model_Order_BucketMeals $meal
     * @param int $count
     * @return boolean
     */
    public function changeCountOfMeal($meal, $count) {
        if (!is_object($meal) || $count < 1) {
            return false;
        }

        try {
            $meal->setCount($count);
            $meal->save();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return false;
        }
        return true;
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function deleteMeal($meal) {
        if (!is_object($meal)) {
            return false;
        }
        $meal->delete();
        return true;
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function addBucketMeal($meal) {
        if (!is_object($meal)) {
            return false;
        }
        return true;
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function changeOption($meal, $oldOptionId, $newOptionId) {
        if (!is_object($meal) || is_null($oldOptionId) || is_null($newOptionId)) {
            return false;
        }
        // check if old option is assigned to meal
        if (!$meal->hasOptionInBucket($oldOptionId) || !$meal->hasExtra(new Yourdelivery_Model_Meal_Option($newOptionId))) {
            return false;
        }

        try {
            $meal->addOption($newOptionId);
            $meal->deleteOption($oldOptionId);
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function changeSpecial($meal, $special) {
        if (!is_object($meal) || is_null($special)) {
            return false;
        }

        try {
            $meal->setSpecial($special);
            $meal->save();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return false;
        }
        return true;
    }

    /**
     * Change order location
     * @author vpriem
     * @since 30.09.2010
     * @param Yourdelivery_Model_Location $location
     * @return boolean
     */
    public function changeLocation($location) {
        if (!is_object($location) || !($location instanceof Yourdelivery_Model_Location)) {
            return false;
        }

        try {
            $this->getTable()->updateLocation($location);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function changeComment($comment) {
        if (is_null($comment)) {
            return false;
        }

        try {
            $loc = $this->getLocation();
            $loc->setComment($comment);
            $this->getTable()->updateLocation($loc);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * get tracking id for prompt if any
     * @author Matthias Laug <laug@lieferando.de>
     * @since 09.09.2010
     * @return int
     */
    public function getPromptTrackingId() {
        $track = $this->getTable()
                ->getCurrent()
                ->findDependentRowset('Yourdelivery_Model_DbTable_Prompt_Tracking');
        if (is_object($track) && $track->count() > 0) {
            return $track->current()->trackingId;
        } else {
            return null;
        }
    }

    /**
     * use prompt api
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.09.2010
     * @param string $action
     * @param string $trackingId
     */
    public function prompt($action = 'status', $trackingId = '') {
        try {
            //initialize prompt api
            $prompt = new Yourdelivery_Model_Api_Prompt($this);

            switch ($action) {
                default:
                case 'status': {
                        return $prompt->status($trackingId);
                        break;
                    }

                case 'log': {
                        return $prompt->log($trackingId);
                        break;
                    }

                case 'book': {
                        return $prompt->book();
                        break;
                    }

                case 'rates': {
                        return $prompt->rates();
                        break;
                    }

                case 'cancel': {
                        return $prompt->cancel($trackingId);
                        break;
                    }
            }
        } catch (Exception $e) {
            $this->logger->err($e->getMessage() . $e->getTraceAsString());
            Yourdelivery_Sender_Email::error($e->getMessage() . $e->getTraceAsString(), true);
        }
    }

    /**
     * we generate a secret at the beginning, so that
     * we can validate if the user who finished is realy
     * the one who started the order. this is important for
     * credit card payment
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public final function getSecret() {
        return $this->_data['ident'];
    }

    /**
     * checks if payment for company order over budget is credit
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean
     */
    public function isCompanyCredit() {
        $db = Zend_Registry::get("dbAdapter");

        if ($this->getKind() != "comp") {
            return false;
        }

        $select = $db->select()->from('order_company_group')
                ->where('orderId=?', $this->getId())
                ->where('privAmount > 0')
                ->where("payment = 'credit'");

        $result = $db->fetchAll($select);

        if (count($result) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return boolean
     */
    public function isRefunded() {
        switch ($this->getPayment()) {
            case 'paypal':
                $transactions = $this->getTable()
                        ->getPaypalTransactions();
                foreach ($transactions as $transaction) {
                    $params = $transaction->getParams();
                    $response = $transaction->getResponse();
                    if ($params['METHOD'] == "RefundTransaction" && $response['ACK'] == "Success") {
                        return true;
                    }
                }
                break;

            case 'credit':
                // heidelpay
                $transactions = $this->getTable()
                        ->getHeidelpayWpfTransactions();
                foreach ($transactions as $transaction) {
                    $params = $transaction->getParams();
                    $response = $transaction->getResponse();
                    if ($params['PAYMENT_CODE'] == "CC.RF" && $response['PROCESSING_RESULT'] == "ACK") {
                        return true;
                    }
                }

                // adyen
                $transactions = $this->getTable()
                        ->getAdyenTransaction();
                foreach ($transactions as $transaction) {
                    if ($transaction->refunded) {
                        return true;
                    }
                }

                break;

            case 'ebanking':
                $transactions = $this->getTable()
                        ->getEbankingRefundTransactions();
                foreach ($transactions as $transaction) {
                    if ($transaction->isStatusOk()) {
                        return true;
                    }
                }
                break;
        }
        return false;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function getCharge() {
        if ($this->getTime() > strtotime('01.03.2012')) {
            return $this->_data['charge'];
        }
        return 0;
    }

    /**
     * Check if Order is older than 30 days
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean
     */
    public function isRateable() {
        if ($this->isRated()) {
            return false;
        }

        $deliverTime = new DateTime(date('Y-m-d', $this->getDeliverTime()));
        $dateCompare = new DateTime();
        $dateCompare->sub(new DateInterval('P30D'));

        if ($deliverTime < $dateCompare) {
            return false;
        }
        return true;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.07.2012
     * @return boolean
     */
    public function isPreOrder() {

        return $this->getDeliverTime() > $this->getTime();
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.07.2012
     * @return Yourdelivery_Model_Order_Deliverdelay
     */
    public function getDeliverDelay() {

        if ($this->_deliverDelay instanceof Yourdelivery_Model_Order_Deliverdelay) {
            return $this->_deliverDelay;
        }

        $rows = $this->getTable()
                     ->getCurrent()
                     ->findDependentRowset('Yourdelivery_Model_DbTable_Order_Deliverdelay');
        foreach ($rows as $row) {
            try {
                return $this->_deliverDelay = new Yourdelivery_Model_Order_Deliverdelay($row->id);
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
        }

        return new Yourdelivery_Model_Order_Deliverdelay();
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.07.2012
     * @return int
     */
    public function computeArrivalTime() {

        // if the service got a courier
        // we compute the pickuptime and add his develiry delay
        $service = $this->getService();
        if ($service->hasCourier()) {
            return $this->computePickUpTime() + $this->getDeliverDelay()->getCourierDeliverDelay();
        }

        $time = $this->getTime();
        $deliverTime = $this->getDeliverTime();
        $deliverDelay = $this->getDeliverDelay()->computeDelay();

        // for pre-order
        if ($deliverTime > $time) {
            if (($deliverTime - $time) >= $deliverDelay) {
                return $deliverTime;
            }
        }

        return $time + $deliverDelay;
    }

    /**
     * Get courier pickup time
     * @author vpriem
     * @since 11.08.2010
     * @param int $serviceDeliverDelay
     * @return int
     */
    public function computePickUpTime() {
        $orderTime = $this->getTime();
        $orderDeliverTime = $this->getDeliverTime();

        $service = $this->getService();
        $deliverDelay = $this->getDeliverDelay();

        $serviceDeliverDelay = $deliverDelay->getServiceDeliverDelay(); // in secs
        $courierDeliverDelay = $deliverDelay->getCourierDeliverDelay(); // in secs

        // for pre-order
        if ($orderDeliverTime > $orderTime) {
            // get opening for this day
            $openings = $service->getOpening()->getIntervalOfDay($orderDeliverTime);

            // if restaurant has enough time to prepare food
            // and courier enough time to bring it
            if (($orderDeliverTime - $orderTime) >= ($serviceDeliverDelay + $courierDeliverDelay)) {
                $courierPickUpTime = $orderDeliverTime - $courierDeliverDelay;

                // check if the restaurant is open at this time
                foreach ($openings as $op) {
                    foreach ($op as $o) {
                        $from = $o['timestamp_from'];
                        $until = $o['timestamp_until'];

                        if ($from <= $courierPickUpTime && $courierPickUpTime <= $until) {
                            return $courierPickUpTime;
                        }
                        // take the next opening
                        elseif ($courierPickUpTime <= $from) {
                            return $from + $serviceDeliverDelay;
                        }
                    }
                }

                // a little fallback
                return $orderDeliverTime + $serviceDeliverDelay;
            }

            // otherwise
            // check if the restaurant is open at this time
            foreach ($openings as $op) {
                foreach ($op as $o) {
                    $from = $o['timestamp_from'];
                    $until = $o['timestamp_until'];

                    if ($from <= $orderTime && $orderTime <= $until) {
                        return $orderTime + $serviceDeliverDelay;
                    }
                    // take the next opening
                    elseif ($orderTime <= $from) {
                        return $from + $serviceDeliverDelay;
                    }
                }
            }
            // a little fallback
            return $orderTime + $serviceDeliverDelay;
        }
        // for immediately order (sofort)
        return $orderTime + $serviceDeliverDelay;
    }

    /**
     * check if this rating is repeatable.
     * A order is not repeatable, if any element from the cart has been
     * deleted over time
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 31.07.2012
     * @return boolean
     */
    public function isRepeatable() {
        foreach ($this->getCard() as $customerBucket) {
            foreach ($customerBucket as $bucket) {
                foreach ($bucket as $item) {

                    $meal = $item['meal'];                            
                    if ($meal->isDeleted() || $meal->getStatus() == 0) {
                        $this->logger->warn(sprintf('order #%d is not repeatable, because meal #%d has been deleted or is offline', $this->getId(), $meal->getId()));
                        return false;
                    }

                    foreach ($meal->getCurrentExtras() as $extra) {

                        if ($extra->isDeleted()) {
                            $this->logger->warn(sprintf('order #%d is not repeatable, because meal EXTRA #%d has been deleted', $this->getId(), $extra->getId()));
                            return false;
                        }
                    }

                    foreach ($meal->getCurrentOptions() as $option) {

                        if ($option->isDeleted()) {
                            $this->logger->warn(sprintf('order #%d is not repeatable, because meal OPTION #%d has been deleted', $this->getId(), $option->getId()));
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
}
