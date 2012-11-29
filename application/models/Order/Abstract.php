<?php

/**
 * Description of OrderAbstract
 * @package order
 * @author Matthias Laug <laug@lieferando.de>
 */
abstract class Yourdelivery_Model_Order_Abstract extends Default_Model_Base {
    /**
     * constants for order status
     */
    //the discount is not usabled after payment

    const REJECTED = -22;
    const FAX_ERROR_NO_TRAIN = -15;
    const PAYMENT_PENDING = -8;
    const INVALID_DISCOUNT = -7;
    const FAKE_STORNO = -6;
    const PAYMENT_NOT_AFFIRMED = -5;
    const BILL_NOT_AFFIRMED = -4;
    const FAKE = -3;
    const STORNO = -2;
    const DELIVERERROR = -1;
    const NOTAFFIRMED = 0;
    const AFFIRMED = 1;
    const DELIVERED = 2;

    /**
     * private or company
     */
    const COMPANYORDER = 'comp';
    const PRIVATEORDER = 'priv';
    const NOTIFY_AMOUNT = 0;

    /**
     * single or group
     */
    const GROUPORDER = 0;
    const SINGLEORDER = 1;

    /**
     * a string to be generated secretly, to determine wethere
     * the user is authenticated to call the finish step
     * this is a security issue for credit card payment
     * @var string
     */
    protected $_secret = null;

    /**
     * stores als the items
     * @var array
     */
    protected $_card = null;

    /**
     * service pdf
     * @var string
     */
    protected $_pdf = null;

    /**
     * courier pdf
     * @var string
     */
    protected $_courierPdf = null;

    /**
     * another check up to avoid repetitive faxes (even though
     * I do not believe this is an application error)
     * @var boolean
     */
    protected $_hasBeenSend = false;

    /**
     *
     * @var boolean
     */
    protected $_hasBeenSaved = false;

    /**
     * @var int
     */
    protected $_timestamp;

    abstract function getCourierFaxClass();

    abstract function getFaxClass();

    /**
     * Get stati of services
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    static function getStornoReasons() {
        return array(
            1 => __b('Kunde möchte nicht mehr (ohne Grund)'),
            2 => __b('Kunde will nicht mehr wegen zu langer Wartezeit'),
            3 => __b('Kunde wusste nicht, dass es nur Barzahlung gibt'),
            4 => __b('Kunde: versehentlich Barzahlung (will nochmal bestellen)'),
            5 => __b('Kunde hat telefonisch selbst storniert'),
            6 => __b('Kunde: doppelte Bestellung'),
            7 => __b('Kunde wollte Gutschein einlösen -> Storno, Neubestellung'),
            8 => __b('Kunde: sonstiges'),
            9 => __b('Fake'),
            10 => __b('DL Fax ist nicht oder zu spät angekommen'),
            11 => __b('DL Liefergebiet falsch'),
            12 => __b('DL akzeptiert keine Onlinezahlung mehr oder generell nicht'),
            13 => __b('DL hat geschlossen ohne Benachrichtigung'),
            14 => __b('DL kann nicht mehr liefern'),
            15 => __b('DL weigert sich aufgrund von Unstimmigkeiten'),
            16 => __b('DL storniert weil Kunde nicht anzutreffen war'),
            17 => __b('Prepayment'),
            18 => __b('Testbestellung erfolgreich'),
            19 => __b('Testbestellung nicht erfolgreich'),
            20 => __b('Kunde machte falsche Angaben'),
        );
    }

    /**
     * Get human readable mode
     * @author vpriem
     * @since 11.04.2011
     * @param boolean $type
     * @return string
     */
    public function getModeToReadable($asType = false) {
        switch ($this->getMode()) {
            case 'rest': return __('Restaurant');
            case 'cater': return $asType ? __('Caterer') : __('Catering');
            case 'fruit': return $asType ? __('Obstlieferant') : __('Obst');
            case 'great' : return $asType ? __('Getränkehändler') : __('Großhandel');
            case 'canteen': return __('Kantine');
            default: return __('Unbekannt');
        }
    }

    /**
     * Set timestamp
     * use for canteen
     * @author vpriem
     * @since 07.09.2010
     * @param int $timestamp
     * @return void
     */
    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
    }

    /**
     * Get timestamp
     * use for canteen
     * @author vpriem
     * @since 07.09.2010
     * @return int
     */
    public function getTimestamp() {
        return $this->_timestamp;
    }

    /**
     * save order and return new id, this method is only
     * allowed to be called once!
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.12.2011
     * @see http://ticket.yourdelivery.local/browse/YD-819
     * @return int
     */
    public function save() {
        if ($this->_hasBeenSaved === true) {
            $this->logger->crit(sprintf('tried to save order object twice: %s', debug_backtrace()));
            return $this->getId();
        }
        $time = $this->getTime();
        $deliver = $this->getDeliverTime();
        $this->_data['time'] = date('Y-m-d H:i:s', $time);
        $this->_data['deliverTime'] = date('Y-m-d H:i:s', $deliver);
        $id = parent::save();
        $this->_hasBeenSaved = true;
        return $id;
    }

    /**
     * get all services delivering in a certian area
     * @param mixed integer|array $cityId
     * @param string $mode
     * @return array
     */
    static public function getServicesByCityId($cityId = null, $mode = null, $limit = null) {
        return Yourdelivery_Model_Servicetype_Restaurant::getByCityId($cityId, $mode, false, $limit);
    }

    /**
     * get offline services delivering in a certain area
     * @author daniel
     * @param int $cityId
     * @param string $mode
     * @return  array
     */
    static public function getOfflineServicesByCityId($cityId = null, $mode = null, $limit = null) {
        return Yourdelivery_Model_Servicetype_Restaurant::getByCityId($cityId, $mode, true, $limit);
    }

    /**
     * get all taxes or one taxtype explicitly
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.02.2011
     * @return float
     */
    public function getTax($taxtype = ALL_TAX, $checkDeliverCost = true, $checkCourierCost = true, $courierDiscount = true) {
        $tax = 0;

        if (is_null($this->_card['bucket']) || !is_array($this->_card['bucket'])) {
            return $tax;
        }

        foreach ($this->_card['bucket'] as $citems) {
            foreach ($citems as $item) {
                $meal = $item['meal'];
                $tax += $item['count'] * $meal->getTax($taxtype);
            }
        }

        if ($checkDeliverCost && ($taxtype == ALL_TAX || $taxtype == $this->config->tax->deliver)) {
            $tax += Default_Helpers_Money::getTax($this->getServiceDeliverCost(), $this->config->tax->deliver);
        }

        if ($checkCourierCost && ($taxtype == ALL_TAX || $taxtype == $this->config->tax->deliver)) {
            $courierCost = $this->getCourierCost();
            if ($courierDiscount) {
                $courierCost -= $this->getCourierDiscount();
            }

            $tax += Default_Helpers_Money::getTax($courierCost, $this->config->tax->deliver);
        }
        return $tax;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 11.07.2011
     * @return Yourdelivery_Model_Servicetype_Restaurant
     */
    public function getServiceClass() {
        switch ($this->getMode()) {
            case Yourdelivery_Model_Servicetype_Abstract::RESTAURANT :
                return new Yourdelivery_Model_Servicetype_Restaurant();
            case Yourdelivery_Model_Servicetype_Abstract::CATER :
                return new Yourdelivery_Model_Servicetype_Cater();
            case Yourdelivery_Model_Servicetype_Abstract::GREAT :
                return new Yourdelivery_Model_Servicetype_Great();
        }
    }

    /**
     * get all netto or one taxtype explicitly
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.02.2011
     * @return float
     */
    public function getItem($taxtype = ALL_TAX, $checkDeliverCost = true, $checkCourierCost = true, $courierDiscount = true) {
        $total = 0;
        foreach ($this->_card['bucket'] as $citems) {
            foreach ($citems as $item) {
                $meal = $item['meal'];
                $total += $item['count'] * $meal->getItem($taxtype);
            }
        }

        if ($checkDeliverCost && ($taxtype == ALL_TAX || $taxtype == $this->config->tax->deliver)) {
            $total += Default_Helpers_Money::getNetto($this->getServiceDeliverCost(), $this->config->tax->deliver);
        }

        if ($checkCourierCost && ($taxtype == ALL_TAX || $taxtype == $this->config->tax->deliver)) {
            $courierCost = $this->getCourierCost();
            if ($courierDiscount) {
                $courierCost -= $this->getCourierDiscount();
            }
            $total += Default_Helpers_Money::getNetto($courierCost, $this->config->tax->deliver);
        }
        return $total;
    }

    /**
     * this is a hack to provide correct settings of foreign key
     * we abstract all, restaurant, caterer etc, as services
     * but database stores it as restaurant
     * save method corrects key restaurant to restaurantId
     * @param Yourdelivery_Model_ServicetypeAbstract $service
     * @throws Yourdelivery_Exception_InvalidAction
     */
    public function setService($service) {
        $this->_data['service'] = $service;
        $this->_data['restaurant'] = $service;
    }

    /**
     * set current file to use as pdf
     * @param string $file
     * @return boolean
     */
    public function setPdf($file) {
        if (file_exists($file)) {
            $this->_pdf = $file;
            return true;
        }
        return false;
    }

    /**
     * Set current file to use as courier pdf
     * @author vpriem
     * @since 30.09.2010
     * @param string $file
     * @return boolean
     */
    public function setCourierPdf($file) {
        if (file_exists($file)) {
            $this->_courierPdf = $file;
            return true;
        }
        return false;
    }

    /**
     * return either a string or an timestamp
     * @return mixed int|string
     */
    public function getDeliverTimeFormated() {
        $time = $this->getDeliverTime();
        $ordertime = $this->getTime();
        if ($time <= $ordertime) {
            return __('sofort');
        }
        return $time;
    }

    /**
     * set deliver time based on choosen values in form
     * @param string $time
     * @param string $day
     */
    public function setDeliverTime($time, $day = null) {
        // create timestamp from data
        if ($time == __("sofort")) {
            $time = time();
        } else {
            // french date format include a /
            // but we need to read the european format d-m-y
            $day = str_replace('/', '.', $day);
            // this is for normal restaurant, we may go only some days ahead
            if (in_array($day, array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16))) {
                $time = strtotime(sprintf("%s:00 + %d day", $time, $day));
            }
            // for catering and great you can choose a specific day
            else {
                $time = strtotime(sprintf("%s %s:00", $day, $time));
            }
        }

        //this is not possible, fix it
        if ($time < time()) {
            $time = time();
        }

        //this is wrong, fix it
        if ($time > strtotime("+1 year", time())) {
            $time = time();
        }
        $this->_data['deliverTime'] = $time;
    }

    /**
     * Get courier deliver time
     * @author vpriem
     * @since 11.08.2010
     * @return int
     * @deprecated
     */
    public function getCourierDeliverTime() {

        return $this->getDeliverTimestamp()
                + $this->getService()->getDeliverTime($this->getLocation()->getPlz()); // courier deliverTime is already included
    }

    /**
     * Return deliver timestamp
     * @author vpriem
     * @since 11.08.2010
     * @return int
     */
    public function getDeliverTimestamp() {
        return strtotime($this->_data['deliverTime']);
    }

    /**
     * get the discount of an order
     * @return Yourdelivery_Model_Rabatt_Code
     */
    public function getDiscount() {
        $discount = null;
        if (array_key_exists('discount', $this->_data)) {
            $discount = $this->_data['discount'];
        }
        if (!is_null($discount) && is_object($discount)) {
            return $discount;
        }
        return null;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.02.2012
     * @return boolean
     */
    public function hasNewCustomerDiscount() {
        $discount = $this->getDiscount();

        if ($discount && $discount->getParent()->isNewCustomerDiscount()) {
            return true;
        }
        return false;
    }

    /**
     * get discount object
     * @param Yourdelivery_Model_Rabatt_Code $discountObj
     */
    public function setDiscount($discount) {
        $this->_data['discount'] = $discount;
    }

    /**
     * add a meal to order
     * we use a hashbucket to notice duplicates
     * @param Yourdelivery_Model_Meals $meal
     * @param array $opt_ext
     * @param integer $count
     * @param Yourdelivery_Model_Customer_Abstract $customer
     *
     * @throws Yourdelivery_Exception_InvalidAction
     * @return mixed boolean|string
     */
    public function addMeal($meal = null, $opt_ext = array(), $count = 1, $customer = null, $bucketId = null) {

        if (is_null($this->_card)) {
            $this->clearBucket();
        }

        //must be an object
        if (!is_object($meal)) {
            return false;
        }

        //do not allow to add meals from foreign services
        if ($meal->getService()->getId() != $this->getService()->getId()) {
            //here should be a logger message but that fails sadly :( do not know why
            return false;
        }

        if (is_null($customer)) {
            $customer = $this->getCustomer();
            if (!is_object($customer)) {
                return false;
            }
        }

        //do not allow any negative or zero count
        if ($count < 1) {
            $count = 1;
        }

        //get sizeId of selected meal
        $sizeId = (integer) $meal->getCurrentSize();

        //get special infos
        $special = $opt_ext['special'];

        //add special
        $meal->setSpecial($special);

        // use to store ids
        $options = array();
        $mealoptions = array();
        $extras = array();

        //YD-1408: check if options match requirements
        //options
        foreach ($opt_ext['options'] as $opt) {
            if ($opt == '') {
                continue;
            }

            $appended = false;
            try {
                $option = new Yourdelivery_Model_Meal_Option($opt);
                if (isset($opt_ext['options_cost'][$opt])) {
                    $option->setCost($opt_ext['options_cost'][$opt]);
                }
                $appended = $meal->appendOption($option);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                //repair missing option
                if (isset($opt_ext['options_name'][$opt])) {
                    $option = new Yourdelivery_Model_Meal_Option();
                    $option->setName($opt_ext['options_name'][$opt]);
                    $option->setCost($opt_ext['options_cost'][$opt]);
                    $option->setMwst($opt_ext['options_mwst'][$opt]);
                    $option->setDeleted(true);
                    $appended = $meal->appendOption($option);
                }
            }

            if ($appended) {
                $options[] = $opt;
            }
        }

        //mealoptions (half pizza)
        $recalculateMealOptionsCosts = false;
        $currMealOptions = array();
        foreach ($opt_ext['mealoptions'] as $opt) {
            if ($opt == '') {
                continue;
            }

            $addable = false;
            try { 
                $mealoption = new Yourdelivery_Model_Meals($opt);
                $mealoption->setCurrentSizeByName($meal->getCurrentSizeName());
                if (isset($opt_ext['mealoptions_cost'][$opt])) {
                    $mealoption->setCost($opt_ext['mealoptions_cost'][$opt]);
                } else {
                    $recalculateMealOptionsCosts = true;
                }
                $addable = true;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                //repair missing option
                if (isset($opt_ext['mealoptions_name'][$opt])) {
                    $mealoption = new Yourdelivery_Model_Meals();
                    $mealoption->setCurrentSizeByName($meal->getCurrentSizeName());
                    $mealoption->setName($opt_ext['mealoptions_name'][$opt]);
                    $mealoption->setCost($opt_ext['mealoptions_cost'][$opt]);
                    $mealoption->setMwst($opt_ext['mealoptions_mwst'][$opt]);
                    $mealoption->setDeleted(true);
                    $addable = true;
                }
            }
            if ($addable) {
                $currMealOptions[] = $mealoption;
            }
        }
        $mealOptionsCount = count($currMealOptions);
        if ($mealOptionsCount > 0 && $recalculateMealOptionsCosts) {
            if ($meal->getPriceType() == 'options_max') {
                $maxCostI = -1;
                $maxCost = 0;
                for ($i = 0; $i < $mealOptionsCount; $i++) {
                    if ($maxCost < ($currCost = $currMealOptions[$i]->getCost())) {
                        $maxCost = $currCost;
                        $maxCostI = $i;
                    }
                }
                for ($i = 0; $i < $mealOptionsCount; $i++) {
                    if ($i != $maxCostI) {
                        $currMealOptions[$i]->setCost(0);
                    }
                }
            } elseif ($meal->getPriceType() == 'options_avg') {
                for ($i = 0; $i < $mealOptionsCount; $i++) {
                    $currMealOptions[$i]->setCost($currMealOptions[$i]->getCost()/$mealOptionsCount);
                }
            }
        }
        for ($i = 0; $i < $mealOptionsCount; $i++) {
            if($meal->appendMealOption($currMealOptions[$i])) {
                $mealoptions[] = $mealoption->getId();
            }
        }


        //extras
        foreach ($opt_ext['extras'] as $extra) {

            $extraId = (integer) $extra['id'];
            $extraCount = (integer) $extra['count'];

            if ($extraId <= 0) {
                continue;
            }

            $appended = false;
            try {
                $extra = new Yourdelivery_Model_Meal_Extra($extraId);
                $extra->setSize($meal->getCurrentSize());
                $extra->setMeal($meal->getId());
                $extra->setCount($extraCount);
                if (isset($opt_ext['extras_cost'][$extraId])) {
                    $extra->setMwst($opt_ext['extra_mwst'][$extraId]);
                    $extra->setCost($opt_ext['extras_cost'][$extraId]);
                }
                $appended = $meal->appendExtra($extra);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                //repair missing extra
                if (isset($opt_ext['extra_name'][$extraId])) {
                    $extra = new Yourdelivery_Model_Meal_Extra();
                    $extra->setName($opt_ext['extra_name'][$extraId]);
                    $extra->setCost($opt_ext['extras_cost'][$extraId]);
                    $extra->setMwst($opt_ext['extra_mwst'][$extraId]);
                    $extra->setCount($extraCount);
                    $extra->setDeleted(true);
                    $appended = $meal->appendExtra($extra);
                }
            }

            if ($appended) {
                $extras[] = $extraId;
            }
        }

        //sort extras and options to provide same order
        asort($extras);
        asort($options);
        asort($mealoptions);

        //create a key for hash key
        $opt_string = implode(":", $options);
        $mealopt_string = implode(":", $mealoptions);
        $ext_string = implode(":", $extras);
        //add up all information in one single string
        $string = $meal->getId() . $sizeId . $opt_string . $mealopt_string . $ext_string . $special;
        //create a hash depending on generated string
        $hash = sha1($string);

        //check if user has already a initlized bucket
        if (!array_key_exists($customer->getId(), $this->_card['bucket'])) {
            //no bucket? create one for the poor little bastard
            $this->_card['bucket'][$customer->getId()] = array();
        }

        //check if this item already exists
        if (array_key_exists($hash, $this->_card['bucket'][$customer->getId()])) {
            //yes it does, so let us increase it
            $this->_card['bucket'][$customer->getId()][$hash]["count"] += $count;
        } else {
            //no it does not, so we add it with its corresponding hash key
            //@think: do we need to store options and extras really in that array
            //isn't it stored in meal object?
            $this->_card['bucket'][$customer->getId()][$hash] = array(
                "count" => $count,
                "cost" => $meal->getCost(),
                "meal" => $meal,
                "size" => $sizeId,
                "options" => $options,
                "mealoptions" => $mealoptions,
                "extras" => $extras,
                "customer" => $customer,
                "bucketId" => $bucketId
            );
        }

        //reset total to be calculated once again
        $this->_total = null;

        //return hash
        return array($hash, $meal);
    }

    /**
     * Update assigned customer
     * this is only needed, if there was no user before
     * @author vpriem
     * @since 06.04.2011
     * @param Yourdelivery_Model_Customer_Abstract $customer
     * @return boolean
     */
    public function updateCustomer(Yourdelivery_Model_Customer_Abstract $customer) {
        if ($this->getKind() == 'priv' && $this->getCustomerId() === null) {
            return (boolean) $this->getTable()->updateCustomer($customer->getId());
        }
        return false;
    }

    /**
     * allow only certian modes
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.09.2011
     * @param string $mode
     */
    public function setMode($mode) {
        if (!in_array($mode, array('rest', 'cater', 'great'))) {
            $mode = 'rest';
        }
        $this->_data['mode'] = $mode;
    }

    /**
     * get current card
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    public function getCard() {
        return $this->_card;
    }

    public function setCard($card) {
        $this->_card = $card;
    }

    /**
     * get bucket of current user or given user
     * @author Matthias Laug <laug@lieferando.de>
     * @param Yourdelivery_Model_Customer $customer
     * @return array
     */
    public function getBucket($customer = null) {
        //if no customer has been provided
        //get bucket from current customer
        if (is_null($customer)) {
            $customer = $this->getCustomer();
        }

        $bucket = $this->_card['bucket'][$customer->getId()];
        return $bucket;
    }

    /**
     * clear bucket and reset card
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function clearBucket() {
        $this->_total = null;
        $this->_card = array(
            'bucket' => array()
        );
    }

    /**
     * get order table zend_db class
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
     * identify type of order
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getIdentity() {
        return "1-" . $this->getMode() . "-" . $this->getKind();
    }

    /**
     * get floorfee cost for this order
     * - calculation: <floor> * <floorfee> * <bucketItemCount>
     * - if this order has lift, there will be a floorfee of 0
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.03.2010
     * @return integer
     */
    public function getFloorFeeCost() {
        if ($this->getLift()) {
            return 0;
        }

        $fee = $this->getService()->getFloorfee();

        if ($fee < 1) {
            return 0;
        }
        return $fee * $this->getFloor() * $this->getBucketItemCount();
    }

    /**
     * get count of items in bucket including multiple count of items
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.03.2011
     * @return integer
     */
    public function getBucketItemCount() {
        $bucket = $this->getBucket();
        if (is_null($bucket)) {
            return 0;
        }
        $countItems = 0;
        foreach ($bucket as $item) {
            $countItems += (integer) $item['count'];
        }
        return $countItems;
    }

    /**
     * get absolute total of this order
     * define which sums should be added or removed
     * @author Matthias Laug <laug@lieferando.de>
     * @param boolean $charge deprecated
     * @param boolean $discount
     * @param boolean $deliver
     * @param boolean $credit
     * @param boolean $budget
     * @param boolean $ownBudget
     * @return int
     */
    public function getAbsTotal($charge = false, $discount = true, $deliver = true, $credit = true, $budget = true, $ownBudget = true, $floorfee = true, $customer = null) {
        //init total
        $total = 0;

        //get meal costs
        $total = $this->getBucketTotal();

        //add deliver cost
        if ($deliver) {
            $total += $this->getService()->getDeliverCost(null, $total);
            /**
             * adding floorfee / etagenzuschlag
             * @author Felix Haferkorn <haferkorn@lieferando.de>
             * @since 30.03.2011
             */
            if ($floorfee) {
                $total += $this->getFloorFeeCost();
            }
        }

        //remove discount
        if ($discount) {
            $discount = $this->getDiscount();
            if (is_object($discount)) {
                //calculate new amount and set it as new total
                $discount->calcDiff($total);
                $total = $discount->getNewAmount();
            }
        }
        return $total;
    }

    /**
     * add payment addition if valid
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 27.03.2012
     * @param string $addition
     */
    public function setPaymentAddition($addition) {

        $additions = Yourdelivery_Payment_Abstract::getAdditions();
        if (array_key_exists($addition, $additions) && $this->getPayment() == 'bar') {
            $this->_data['paymentAddition'] = $addition;
        }
    }

    /**
     * Get payment addition for humans
     *
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.08.2012
     * @return string
     */
    public function getPaymentAdditionReadable() {

        $addition = $this->getPaymentAddition();
        $additions = Yourdelivery_Payment_Abstract::getAdditions();
        if (array_key_exists($addition, $additions)) {
            return $additions[$addition];
        }

        return $addition;
    }

    /**
     * set payment
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     */
    public function setPayment($payment, $checkPayment = true) {
        if (!in_array($payment, array('bar', 'credit', 'bill', 'paypal', 'debit', 'ebanking'))) {
            $payment = 'bar';
        }

        if ($checkPayment && !Yourdelivery_Helpers_Payment::allowPayment($this, $payment)) {
            throw new Yourdelivery_Exception(sprintf('cannot set payment %s, not allowed: %s', $payment, Yourdelivery_Helpers_Payment::getLastReason()));
        }

        if ($payment != 'bar') {
            $this->_data['paymentAddition'] = null;
        }

        $this->setCurrentPayment($payment);
        $this->_data['payment'] = $payment;
    }

    /**
     * amount of ordered items ( distinct )
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCardCount() {
        $count = 0;
        foreach ($this->_card['bucket'] as $userElem) {
            foreach ($userElem as $elem) {
                $count += $elem['count'];
            }
        }
        return $count;
    }

    /**
     * get bucket
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.08.2010
     * @param Yourdelivery_Model_Customer $customer
     * @return int
     */
    public function getBucketTotal($customer = null, $excludeMincost = false) {
        $card = $this->getCard();

        if (!is_array($card)) {
            $this->clearBucket();
            $card = $this->getCard();
        }

        $total = 0;
        //loop through the bucket
        foreach ($card['bucket'] as $custId => $card) {

            if (!is_null($customer) && $custId != $customer->getId()) {
                continue;
            }

            foreach ($card as $meal) {
                $mealObj = $meal['meal'];
                $mealCount = (integer) $meal['count'];
                //check if this meal should be included in the min costs
                if ($excludeMincost) {
                    if ($mealObj->isExcludeFromMinCost()) {
                        continue;
                    }

                    if ($mealObj->getCategory()->isExcludeFromMinCost()) {
                        continue;
                    }
                }

                if ($mealObj->hasPfand()) {
                    $total -= $mealCount * $mealObj->getPfand();
                }

                $total += $meal['count'] * $mealObj->getAllCosts();
            }
        }
        return $total;
    }

    /**
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $cityId
     * @return boolean
     */
    public function addressInRange($cityId) {
        if ($cityId === null) {
            return false;
        }

        $service = $this->getService();
        if (is_object($service)) {

            $ranges = $service->getRanges();
            foreach ($ranges as $range) {
                if ($range['cityId'] == $cityId) {
                    // hooray, we can get fat in this street as well
                    return true;
                }
            }
            // oh dear, no food for this street
            return false;
        }
        // return true here, because no service has been choosen so far
        return true;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $plz
     * @return boolean
     */
    public function addressOutOfRange($plz = null) {
        return !$this->addressInRange($plz);
    }

    /**
     * we generate a secret at the beginning, so that
     * we can validate if the user who finished is realy
     * the one who started the order. this is important for
     * credit card payment
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getSecret() {
        return $this->_secret;
    }

    /**
     * set current secret
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $sec
     */
    public function setSecret($sec) {
        $this->_secret = $sec;
    }

    /**
     * check order before storing in database
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    protected function preFinish() {
        if ($this->getId()) {
            throw new Yourdelivery_Exception_FailedFinishingOrder('could not finish order, this one seems already to be a finish order');
        }

        $customer = $this->getCustomer();
        $location = $this->getLocation();
        $service = $this->getService();
        $deliverDelay = $this->getDeliverDelay();

        if (!is_object($customer) || !is_object($location) || !is_object($service)) {
            throw new Yourdelivery_Exception_FailedFinishingOrder('Could not finish preFinish due to missing data');
        }

        $this->generateOrderNumber();

        //store payments
        $this->setTotal($this->getAbsTotal(false, false, false, false, false, false, false));
        $deliverDelay->setServiceDeliverDelay($service->getRealDeliverTime($location->getCityId()));

        // store courier costs
        // but not for canteen orders
        $courier = $service->getCourier();
        if (is_object($courier)) {
            $this->setCourier($courier);
            $this->setCourierCost($courier->getDeliverCost($location->getCityId()));
            $this->setCourierDiscount(
                    $courier->getDiscount(
                            $this->getBucketTotal(), $this->getCustomer()->getCompany(), $service
                    ));
            $this->setServiceDeliverCost(0);
            $deliverDelay->setCourierDeliverDelay($courier->getDeliverTime($location->getCityId()));
        } else {
            $this->setCourier(null);
            $this->setCourierCost(0);
            $deliverDelay->setCourierDeliverDelay(0);

            /**
             * add floorfee, if service has one
             */
            $this->setServiceDeliverCost($service->getDeliverCost($this->getLocation()->getCityId(), $this->getBucketTotal(), $this) + $this->getFloorFeeCost());
        }

        //set the charge
        $this->_data['charge'] = $this->getService()->getTransactionCost($this->getPayment(), $this->getBucketTotal() + $this->getServiceDeliverCost());

        //set current time as order time
        $this->_data['time'] = time();
        $this->_data['contract'] = !$service->isNoContract();
        return true;
    }

    /**
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function finish() {
        $db = Zend_Registry::get('dbAdapter');
        $db->beginTransaction();
        try {
            //setup all the data and prepate for database commit
            $this->preFinish() && $this->postFinish();
        } catch (Exception $e) {
            $db->rollback();
            throw new Yourdelivery_Exception_FailedFinishingOrder($e->getMessage());
        }

        //we call it here, so we can check if it compiles
        if ($this->generatePdf() === false || $this->generateCourierPdf() === false) {
            $db->rollback();
            throw new Yourdelivery_Exception_FailedFinishingOrder('Could not generate PDF for this order');
        }
        $db->commit();

        //we do that outside the transaction, to avoid a deadlock
        //count down notifyPayed
        if ($this->getPayment() != 'bar') {
            $payed = $this->getService()->getNotifyPayed();
            $this->getService()->setNotifyPayed(max(0, --$payed));

            // somtimes we get a lock exception in this place. So try three times to save the service data
            try {
                $this->getService()->save();
            } catch (Exception $e) {
                $this->logger->crit('failed to update notifypayed ' . $e->getMessage());
            }
        }
        // Send a warning to backoffice every defined order
        // if the restaurant has franchiseType NoContract
        if ($this->config->ordering->warn->nocontract->count && ($this->getService()->isNoContract() || $this->getService()->isNoOnlinePaymentNow())) {
            try {
                $count = $this->getService()->getOrdersCount(true);
                if ($count % $this->config->ordering->warn->nocontract->count == 0) {
                    $this->logger->info(sprintf('send warning email to backoffice because partner #%s has no contract and has the next %s orders', $this->getService()->getId(), $this->config->ordering->warn->nocontract->count));

                    $email = new Yourdelivery_Sender_Email();
                    if ($this->config->ordering->warn->nocontract->email) {
                        $email->addTo($this->config->ordering->warn->nocontract->email);
                    }
                    $text = __b('Warnung: Bei NoContract Restaurant %s (#%s) sind die nächsten %s Bestellungen eingegangen', $this->getService()->getName(), $this->getService()->getId(), $this->config->ordering->warn->nocontract->count);
                    $email->setSubject($text)
                            ->setBodyText($text)
                            ->send();
                }
            } catch (Exception $e) {
                $this->logger->warn('failed to send out warning email ' . $e->getMessage());
            }
        }
        $this->socialNetworkCommit();
        return true;
    }

    /**
     * send order to social networks (for now only facebook)
     * @author Jens Naie <naie@lieferando.de>
     * @return void
     */
    protected function socialNetworkCommit() {
        if ($this->getCustomer()->getFacebookPost() && $this->getMode() == Yourdelivery_Model_Servicetype_Abstract::RESTAURANT) {
            $fb = new Yourdelivery_Connect_Facebook();
            if ($fb->isSessionValid()) {
                $card = $this->getCard();
                $maxCost = 0;
                $meal = '';
                foreach ($card['bucket'] as $item) {
                    foreach ($item as $m) {
                        $cost = $m['meal']->getCost();
                        if ($cost > $maxCost) {
                            $maxCost = $cost;
                            $meal = $m['meal']->getName();
                        }
                    }
                }
                $discount = $this->getDiscount();
                if (is_object($discount) && $discount->getParent()->isFidelity()) {
                    if ($this->config->domain->base == 'pyszne.pl' && $this->getCustomer()->getSex() != 'n') {
                        if ($this->getCustomer()->getSex() == 'f') {
                            $message = __("sie hat %s Treuepunkte eingelöst und %s bestellt.", $this->config->domain->base, $meal);
                        } else {
                            $message = __("er hat %s Treuepunkte eingelöst und %s bestellt.", $this->config->domain->base, $meal);
                        }
                    } else {
                        $message = __("hat %s Treuepunkte eingelöst und %s bestellt.", $this->config->domain->base, $meal);
                    }
                } else {
                    if ($this->config->domain->base == 'pyszne.pl' && $this->getCustomer()->getSex() != 'n') {
                        if ($this->getCustomer()->getSex() == 'f') {
                            $message = __("sie hat gerade über %s %s bestellt.", $this->config->domain->base, $meal);
                        } else {
                            $message = __("er hat gerade über %s %s bestellt.", $this->config->domain->base, $meal);
                        }
                    } else {
                        $message = __("hat gerade über %s %s bestellt.", $this->config->domain->base, $meal);
                    }
                }
                $restaurantCount = Yourdelivery_Model_Servicetype_Abstract::countAll(Yourdelivery_Model_Servicetype_Abstract::RESTAURANT_IND);
                $restaurantCount = floor($restaurantCount / 100) * 100;
                $description = __("Eins von mehr als %u Restaurants auf %s", $restaurantCount, $this->config->domain->base);
                if ($this->config->facebook->postimage) {
                    $picture = $icon = $this->config->facebook->postimage;
                } else {
                    $picture = $this->getService()->getImg('facebook');
                    $icon = $this->getService()->getImg('tiny');
                }
                $link = $this->config->hostname . '/' . $this->getService()->getRestUrl();
                if ($fb->postOnUsersWall($this->getService()->getName(), $link, $message, $description, $picture, $icon)) {
                    $this->getCustomer()->addFidelityPoint('facebookpost', $this->getCustomer()->getPrename() . ' ' . $this->getCustomer()->getName() . ' ' . $message);
                }
            }
        }
    }

    /**
     * check order after storing in database
     * @return boolean
     */
    protected function postFinish() {
        //save to database
        $customer = $this->getCustomer();
        if (!$customer->isLoggedIn()) {
            //remove customer, so its not saved to database
            $this->setCustomer(null);
            $this->setCustomerId(null);
        }
        //save ip of customer
        $this->setIpAddr(Default_Helpers_Web::getClientIp());

        //add domain or default if not set
        $this->setDomain(Default_Helpers_Web::getHostname());

        try {
            $id = (integer) $this->save();
            if ($id <= 0) {
                throw Exception('false returned as primary key');
            }
        } catch (Exception $e) {
            throw new Yourdelivery_Exception_FailedFinishingOrder('Could not save order: ' . $e->getMessage() . $e->getTraceAsString());
        }

        //store discount
        $discount = $this->getDiscount();
        if (is_object($discount) && $discount->isUsable(true)) {
            $total = $this->getAbsTotal(false, false, true, false, false, false);
            $discount->calcDiff($total);
            $this->setDiscountAmount($discount->getDiff());
            $this->setRabattCodeId($discount->getId());

            if ($discount->getParent()->isFidelity() && $this->getCustomer()->getFidelity()) {
                $this->getCustomer()->addFidelityPoint('usage', $this->getId(), (-1) * $this->getCustomer()->getFidelity()->getCashInNeed());
            }

            //overwrite deliver time
            $this->setDeliverTime(__('sofort'));
        } else {
            $this->setDiscountAmount(0);
            $this->setRabattCodeId(null);
        }

        // save again to set discount values
        $row = $this->getTable()->getCurrent();
        $row->discountAmount = $this->getDiscountAmount();
        $row->rabattCodeId = $this->getRabattCodeId();

        //store hashtag
        $row->hashtag = Default_Helpers_Crypt::hash($row->id);

        //save row
        $row->save();

        //insert customer
        $this->setCustomer($customer);

        //try to store order data
        try {
            //save location information
            $this->getTable()->storeLocation($this->getLocation());
            $this->getTable()->storeBucket($this->getCard());
            //store original data of customer
            $this->getTable()->storeCustomer($customer);

            // store deliverdelay
            $this->getDeliverDelay()
                    ->setOrderId($this->getId())
                    ->save();

            //store commission
            $tableComm = new Yourdelivery_Model_DbTable_Order_Provission();
            $tableComm->create($this, $this->isSatellite());
        } catch (Exception $e) {
            throw new Yourdelivery_Exception_FailedFinishingOrder('Could not store order data: ' . $e->getMessage() . $e->getTraceAsString());
        }

        //add fidelity point for this order
        //add fidelity points for this order, if no discount has been used
        if ($this->getDiscount() == null) {
            $this->getCustomer()->addFidelityPoint('order', $this->getId());
        }
        return true;
    }

    /**
     * After payment we send out everything like the fax and the email
     * to the customer and set the status to zero
     *
     * we also take care of discount handling here
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @param boolean $fake
     * @param boolean $status
     * @param boolean $fraudMessage
     * @param boolean $check -> Check if Fraud in send
     * @since 16.10.2010
     */
    public function finalizeOrderAfterPayment($payment = 'bar', $fake = false, $status = false, $fraudMessage = false, $check = true) {
        $message = ($fraudMessage) ? $fraudMessage : 'Tagging order as fake, no further information given';

        if ($fake || $status == Yourdelivery_Model_Order_Abstract::FAKE) {

            $this->setStatus(
                    Yourdelivery_Model_Order_Abstract::FAKE, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_FRAUD_MESSAGE, __b("fake"), $message)
            );
        } elseif ($status == Yourdelivery_Model_Order_Abstract::FAKE_STORNO) {
            $this->setStatus(
                    Yourdelivery_Model_Order_Abstract::FAKE_STORNO, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_FRAUD_MESSAGE, __b('blacklist'), $message)
            );
        } else {

            $discount = $this->getDiscount();
            if ($discount instanceof Yourdelivery_Model_Rabatt_Code) {
                if ($this->_finalizeDiscount($payment, $discount)) {
                    $this->setStatus(
                            $this->getState(), new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::DISCOUNT_VALID, $discount->getId()));
                    $this->send($check);
                } else {
                    //inform the customer
                    $this->setStatus(
                            Yourdelivery_Model_Order_Abstract::INVALID_DISCOUNT, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::DISCOUNT_INVALID, $discount->getId())
                    );
                    return false;
                }
            } else {
                //no discount checks needed, just send out notifications and order to service
                $this->send($check);
            }
        }

        if ($status == Yourdelivery_Model_Order_Abstract::FAKE_STORNO) {
            $this->sendStornoEmailToUser();
            return false;
        } else {
            $this->sendEmailToUser();
            return true;
        }
    }

    /**
     * finalize the discount and check again otherwise redirect
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.03.2012
     * @param integer $orderId
     * @return boolean
     */
    protected function _finalizeDiscount($payment, Yourdelivery_Model_Rabatt_Code $discount) {

        $apicall = strlen($this->getUuid()) > 0 ? true : false;
        if ($discount->isUsable($apicall)) {
            $discount->setCodeUsed($this);
            return true;
        } else {
            $this->logger->warn(sprintf('failed to double validate discount #%s, removing this discount and cancel order #%s', $discount->getId(), $this->getId()));
            switch ($payment) {
                default :
                    $this->logger->crit(sprintf('tried to finalize discount in online payment with invalid payment %s', $payment));
                    return false;

                case 'bar':
                    return true;

                case 'paypal':
                    $message = array(sprintf('discount #%s not valid anymore, refund paypal', $discount->getId()));
                    Yourdelivery_Helpers_Payment::refundPaypal($this, $this->logger, $message);
                    $this->_informCustomerOfStornoDiscount($payment, $discount);
                    return false;

                case 'credit':
                    $message = array(sprintf('discount #%s not valid anymore, refund credit payment', $discount->getId()));
                    Yourdelivery_Helpers_Payment::refundCredit($this, $this->logger, $message);
                    $this->_informCustomerOfStornoDiscount($payment, $discount);
                    return false;

                case 'ebanking':
                    $message = array(sprintf('discount #%s not valid anymore, refund credit ebanking', $discount->getId()));
                    Yourdelivery_Helpers_Payment::refundEbanking($this, $this->logger, $message);
                    $this->_informCustomerOfStornoDiscount($payment, $discount);
                    return false;
            }
        }
    }

    /**
     * send sms or email to customer once the order has been canceld
     * due to already used discount
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.03.2012
     * @param string $payment
     * @param Yourdelivery_Model_Rabatt_Code $discount
     */
    protected function _informCustomerOfStornoDiscount($payment, $discount) {
        $isSmsSent = false;
        $customer = $this->getCustomer();
        $phoneNumber = $this->getLocation()->getTel();
        if ($phoneNumber) {
            $phoneNumber = Default_Helpers_Normalize::telephone($phoneNumber);
            if (Default_Helpers_Phone::isMobile($phoneNumber)) {
                $sms = new Yourdelivery_Sender_Sms();
                $isSmsSent = $sms->send($phoneNumber, __('Der eingelöste Gutschein ist leider nicht mehr gültig. Die Bestellung wurde storniert und der Betrag von %s€ wird Dir zurückgebucht. Das %s-Team.', intToPrice($this->getAbsTotal()), $this->config->domain->base));
                $this->logger->info(sprintf('send storno sms to %s for order #%s because of already used discount', $phoneNumber, $this->getId()));
            }
        }

        if (!$isSmsSent) {
            $this->logger->info(sprintf('send storno email to %s for order #%s, because of already used discount', $customer->getEmail(), $this->getId()));

            $email = new Yourdelivery_Sender_Email_Template("storno_discount.txt");
            $email->setSubject(__('Wichtige Information zu Deiner Bestellung vom %s: Storno', date(__("d.m.Y"), $this->getTime())))
                    ->addTo($customer->getEmail())
                    ->assign('absTotal', intToPrice($this->getAbsTotal()))
                    ->send();

            $this->logger->info(sprintf('create heyho message for oder #%s because of used discount', $this->getId()));

            $message = new Yourdelivery_Model_Heyho_Messages();
            $message->setMessage(__b('Kunde hat versucht mit der Bezahlart %s und einem Gutschein zu bestellen. Der Gutschein war zum Zeitpunkt der Zahlung aber dann nicht mehr gültiog', $payment));
            $message->addCallbackAvailable("showdiscount/did/" . $discount->getId() . '/oid/' . $this->getId());
            $message->save();
        }
    }

    /**
     * Send out email or fax to service and courier
     * we may set check offline and also decide which
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.11.2010
     * @param boolean $toService
     * @param boolean $toCourier
     * @return boolean
     */
    public function send($check = true, $toService = true, $toCourier = true, $statusMessage = "") {
        if ($check && Default_Helpers_Fraud_Order::detect($this)) {

            //set discount unused again, if it has been set used before
            $discount = $this->getDiscount();
            if ($discount instanceof Yourdelivery_Model_Rabatt_Code && !$discount->isUsable()) {
                $discount->setCodeUnused();
            }
            return true;
        }

        /**
         * We suppose that $check = true means send from frontend
         */
        if ($check) {
            $this->notifications();
        }

        // send to courier
        if ($toCourier) {
            $courier = $this->getService()->getCourier();
            if ($courier instanceof Yourdelivery_Model_Courier && $this->generateCourierPdf()) {
                // email notify
                if ($courier->getNotify() & 1) {
                    $email = new Yourdelivery_Sender_Email_Template('ordercourier');
                    $email->assign('order', $this);
                    $email->setSubject(__('Neue Bestellung von Lieferando'));
                    $email->attachPdf($this->getCourierPdf(), 'bestellzettel_courier.pdf');
                    $email->addTo($courier->getEmail());
                    $email->send();
                }

                // fax notify
                if ($courier->getNotify() & 2) {
                    try {
                        $fax = new Yourdelivery_Sender_Fax();
                        $fax->send($courier->getFax(), $this->getCourierPdf(), $courier->getFaxService(), 'order', $this->getId());
                    } catch (Yourdelivery_Exception_NoConnection $e) {
                        Yourdelivery_Sender_Email::error(
                                "Kurier Benachrichtigung von Bestellung #" . $this->getId() . " von " . $this->getCustomer()->getFullname() . " konnte nicht per Fax verschickt werden: " . $e->getMesssage()
                        );
                    }
                }
            }
        }

        // send to service
        if ($toService) {
            if ($this->_hasBeenSend) {
                $this->logger->warn('Trying to send order, but has been send before. Ignoring this time');
                return true;
            }

            $this->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_OUT_BY, $this->getService()->getNotify(), $statusMessage), true);

            // save the order send method
            $notify = $this->getService()->getNotify();

            $sendBy = new Yourdelivery_Model_Order_Sendby();
            $sendBy->setData(array(
                'orderId' => $this->getId(),
                'sendBy' => $notify,
            ));
            $sendBy->save();

            $this->getRow()->sendBy = $notify;
            $this->getRow()->save();

            switch ($notify) {
                case 'acom':
                    $this->_sendNotificationPerAcom();
                    break;

                case 'all':
                    $this->_sendNotificationPerFax();
                    $this->_sendNotificationPerEmail(false);
                    break;

                case 'email':
                    $this->_sendNotificationPerEmail();
                    break;

                case 'sms':
                    $this->_sendNotificationPerSms();
                    break;

                case 'smsemail':
                    $this->_sendNotificationPerSms();
                    $this->_sendNotificationPerEmail();
                    break;

                case 'mobile':
                    $this->_sendNotificationPerMobile();
                    break;

                case 'ecletica':
                    $this->_sendNotificationPerEcletica();
                    break;

                case 'phone':
                    $this->setStatus(
                            Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_BY_PHONE)
                    );
                    break;

                case 'charisma':
                    $this->_sendNotificationPerCharisma();
                    break;

                default:
                case 'fax':
                    $this->_sendNotificationPerFax();
                    break;
            }
            $this->_hasBeenSend = true;
        }
        return true;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.11.2010
     */
    protected function sendEmailToUser() {
        $order = new Yourdelivery_Model_Order($this->getId());
        $email = new Yourdelivery_Sender_Email_Template('order');
        $email->setSubject(__('%s: Deine Bestellung bei %s.', $this->config->domain->base, $order->getService()->getName()));
        // give hash of order number per HTTP-GET in email
        // every customer gets this link to rate an order no matter, if he is logged in or not
        $email->assign('rateorderlink', __('%s: Deine Bestellung wurde an %s übermittelt.', $this->config->domain->base, $order->getService()->getName()));

        //load piwik goal for opening email
        $piwik = Yourdelivery_Model_Piwik_Tracker::getInstance();
        $goalId = $piwik->createGoal('email_order_open');

        $email->addTo($this->getCustomer()->getEmail());
        $email->assign('order', $order);
        $email->assign('goal', $goalId);
        $email->send();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 10.11.2011
     */
    public function sendStornoEmailToUser() {

        $order = new Yourdelivery_Model_Order($this->getId());

        $email = new Yourdelivery_Sender_Email_Template('storno.txt');
        $email->setSubject(__('Bestätigung über Stornierung Ihrer Bestellung vom %s', date(__("d.m.Y"), $order->getTime())));
        $email->addTo($this->getCustomer()->getEmail());
        $email->assign('order', $order);
        $email->assign('customer', $this->getCustomer());
        $email->send();
    }

    /**
     * sends a notification about a storno to the restaurant
     *
     * @author Allen Frank <frank@lieferando.de>
     * @since 21-03-12
     * @see http://ticket/browse/YD-1564
     */
    public function sendStornoNotificationToRestaurant() {
        $order = new Yourdelivery_Model_Order($this->getId());
        $service = $order->getService();

        switch ($service->getNotify()) {
            default :
            case 'fax':
                $this->_sendStornoFaxToRestaurant();
                break;
            case 'sms':
                $this->_sendStornoSmsToRestaurant();
                break;
            case 'smsemail':
                $this->_sendStornoSmsToRestaurant();
                $this->_sendStornoEmailToRestaurant();
                break;
            case 'email':
                $this->_sendStornoEmailToRestaurant();
                break;
            case 'all':
                $this->_sendStornoEmailToRestaurant();
                $this->_sendStornoFaxToRestaurant();
                break;
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de> 
     * @since 12.07.2012
     */
    protected function _sendNotificationPerCharisma() {
        $order = new Yourdelivery_Model_Order($this->getId());
        $charisma = new Yourdelivery_Api_Charisma_Soap();
        $charisma->placeOrder($order);
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 21-03-12
     * @see http://ticket/browse/YD-1564
     */
    public function _sendStornoEmailToRestaurant() {
        $order = new Yourdelivery_Model_Order($this->getId());
        $email = new Yourdelivery_Sender_Email_Template('storno_restaurant');
        $email->setSubject(__('Stornierung der Bestellung vom %s', date(__("d.m.Y"), $order->getTime())));
        $email->addTo($order->getService()->getCourier() ? $order->getService()->getCourier()->getEmail() : $order->getService()->getEmail());
        $email->assign('order', $order);
        $email->assign('customer', $order->getCustomer());
        $email->send();
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 21-03-12
     * @see http://ticket/browse/YD-1564
     */
    public function _sendStornoFaxToRestaurant() {
        $order = new Yourdelivery_Model_Order($this->getId());
        $faxNr = $order->getService()->getFax();
        $faxService = $order->getService()->getFaxService();

        try {
            $fax = new Yourdelivery_Sender_Fax();
            if ($order->getService()->getCourier()) {
                $faxNr = $order->getService()->getCourier()->getFax();
                $faxService = $order->getService()->getCourier()->getFaxService();
            }
            $fax->send($faxNr, $this->generateStornoPdf(), $faxService, 'storno', $order->getId());
        } catch (Yourdelivery_Exception_NoConnection $e) {
            Yourdelivery_Sender_Email::error(
                    "Stornierung der Bestellung #" . $order->getId() . " von " . $order->getCustomer()->getFullname() . " konnte nicht per Fax verschickt werden: " . $e->getMesssage()
            );
        }
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 21-03-12
     * @see http://ticket/browse/YD-1564
     */
    public function _sendStornoSmsToRestaurant() {

        $order = new Yourdelivery_Model_Order($this->getId());
        $printer = $order->getService()->getSmsPrinter();
        if ($printer instanceof Yourdelivery_Model_Printer_Topup) {
            $printer->pushOrder($order->getId());
        }
    }

    /**
     * send out notifications to support
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @see http://ticket/browse/YD-640
     */
    protected function notifications() {
        // send out notification
        $service = $this->getService();
        if ($service->isPremium() || $service->isBloomsburys()) {
            $cc = array("dreber@lieferando.de", "gerbig@lieferando.de");

            if ($service->isInCity('München')) {
                $cc[] = "tammena@lieferando.de";
            }

            $courier = $service->getCourier();
            if (is_object($courier)) {
                switch ($courier->getName()) {
                    case "Prompt":
                        $cc[] = "Service@promptonline.de";
                        $cc[] = "kurierdienstschwane@o2.blackberry.de";
                        break;

                    case "Rotrunner":
                        break;

                    case "Interkep":
                        $cc[] = "stark@lieferando.de";
                        $cc[] = "lieferando_inbound@interkep.de";
                        $cc[] = "operations@interkep.de";
                        $cc[] = "muenzenhofer@interkep.de";
                        $cc[] = "serviceteam@interkep.de";
                        break;
                }
            }
            Yourdelivery_Sender_Email::notify(__("Neue Bestellung"), $this->getPdf(), false, __("Neue PREMIUM Bestellung bei %s", $service->getName()), $cc);
        }
    }

    /**
     * We do not want to have any incremental numbers
     * so just create a random String
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function generateOrderNumber() {
        // this is just a generator for the moment
        $nr = null;
        $unique = false;
        do {
            $nr = Default_Helper::generateRandomString(8, "0123456789");
            $unique = (boolean) $this->getTable()
                            ->checkUniqueNr($nr);
        } while (!$unique);
        $this->setNr($nr);
        return $nr;
    }

    /**
     * get the row of this order
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @return Zend_Db_Table_Row_Abstract
     */
    protected function getRow() {
        return $this->getTable()->getCurrent();
    }

    /**
     * Gives type of payment in formated form
     * @author vpriem
     * @since 05.04.2011
     * @return string
     */
    public function getPaymentFormated() {
        switch ($this->getPayment()) {
            case 'bar':
                return __('Barzahlung');

            case 'paypal':
                return __('PayPal');

            case 'bill':
                return __('Rechnung');

            case 'credit':
                return __('Kreditkarte');

            case 'ebanking':
                return __('Sofortüberweisung');

            case 'debit':
                return __('Lastschrift');
        }
        return null;
    }

    /**
     * get cost of most expensive item
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 11.08.2010
     * @param boolean $inclExtra
     * @return integer
     */
    public function getMostExpensiveCost($limit, $inclExtra = true) {
        $card = $this->getCard();
        $maxCost = 0;
        foreach ($card['bucket'] as $item) {
            foreach ($item as $m) {
                $cost = $inclExtra ? $m['meal']->getAllCosts() : $m['meal']->getCost();
                if ($cost <= $limit && ( $maxCost == 0 || $cost > $maxCost )) {
                    $maxCost = $cost;
                }
            }
        }
        return $maxCost;
    }

    /**
     * get cost of cheapest item
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 11.08.2010
     * @param boolean $inclExtra
     * @return integer
     */
    public function getCheapestCost($inclExtra = true) {
        $card = $this->getCard();
        $minCost = 10000;
        switch ($inclExtra) {
            case true: {
                    foreach ($card['bucket'] as $item) {
                        foreach ($item as $m) {
                            $cost = intval($m['meal']->getAllCosts());
                            if (is_null($cost)) {
                                continue;
                            }
                            if (intval($cost) < intval($minCost)) {
                                $minCost = $cost;
                            }
                        }
                    }
                    return $minCost;
                    break;
                }
            case false: {
                    foreach ($card['bucket'] as $item) {
                        foreach ($item as $m) {

                            $cost = intval($m['cost']);
                            if (intval($cost) < intval($minCost)) {
                                $minCost = $cost;
                            }
                        }
                    }
                    return $minCost;
                    break;
                }
            default:
                break;
        }
    }

    /**
     * get the courier pdf
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.05.2011
     * @return string
     */
    public function getCourierPdf() {
        if (file_exists($this->_courierPdf)) {
            $this->logger->debug(sprintf('order courier pdf %s already exists, returning but not regenerating', $this->_courierPdf));
            return $this->_courierPdf;
        }

        $this->logger->debug(sprintf('order courier pdf for order %s does not exists yet, generating and returning', $this->getId()));
        $this->_courierPdf = $this->generateCourierPdf();
        return $this->_courierPdf;
    }

    /**
     * generate courier fax for this order
     * @author Matthias Laug <laug@lieferando.de>
     * @return string|boolean
     */
    public function generateCourierPdf() {
        $courierPdf = sprintf('%d-ordersheet-courier.pdf', $this->getId());

        $pdf = $this->getCourierFaxClass();
        $order = new Yourdelivery_Model_Order($this->getId());
        if ($order->getService()->hasCourier()) {

            $this->_storage = new Default_File_Storage();
            $this->_storage->setSubFolder('orders/');
            $this->_storage->setTimeStampFolder(strtotime($this->_data['time']));
            $filepath = $this->_storage->getCurrentFolder() . '/' . $courierPdf;

            if ($this->_storage->exists($courierPdf)) {
                return $courierPdf;
            }

            $pdf->setOrder($order);
            $generatedFile = $pdf->generatePdf();
            $this->_storage->store($courierPdf, file_get_contents($generatedFile));

            return $filepath;
        } else {
            return true;
        }
    }

    /**
     * get fax file, generate if needed
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getPdf() {
        if (file_exists($this->_pdf)) {
            $this->logger->debug(sprintf('order pdf %s already exists, returning but not regenerating', $this->_pdf));
            return $this->_pdf;
        }

        $this->logger->debug(sprintf('order pdf for order %s does not exists yet, generating and returning', $this->getId()));
        $this->_pdf = $this->generatePdf();
        return $this->_pdf;
    }

    /**
     * generate fax for this order
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function generatePdf() {
        //init storage
        $this->_storage = new Default_File_Storage();
        $this->_storage->setSubFolder('orders/');
        $this->_storage->setTimeStampFolder(strtotime($this->_data['time']));

        //init files
        $servicePdf = sprintf('%d-ordersheet-restaurant.pdf', $this->getId());
        $filepath = $this->_storage->getCurrentFolder() . '/' . $servicePdf;

        $pdf = $this->getFaxClass();

        try {
            $order = new Yourdelivery_Model_Order($this->getId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->crit(sprintf('falied to create order %d', $this->getId()));
            return false;
        }

        $pdf->setOrder($order);
        $generatedFile = $pdf->generatePdf();

        //store on file system
        $this->_storage->store($servicePdf, file_get_contents($generatedFile));
        return $filepath;
    }

    /**
     * generate storno-fax for this order
     * @author Allen Frank <frank@lieferando.de>
     * @return string
     */
    public function generateStornoPdf() {
        //init storage
        $this->_storage = new Default_File_Storage();
        $this->_storage->setSubFolder('stornos/');
        $this->_storage->setTimeStampFolder(strtotime($this->_data['time']));

        //init files
        $servicePdf = sprintf('%d-stornosheet-restaurant.pdf', $this->getId());
        $filepath = $this->_storage->getCurrentFolder() . '/' . $servicePdf;

        $pdf = new Yourdelivery_Model_Order_Pdf_Storno();

        try {
            $order = new Yourdelivery_Model_Order($this->getId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->crit(sprintf('falied to create order %d', $this->getId()));
            return false;
        }

        $pdf->setOrder($order);
        $generatedFile = $pdf->generatePdf();

        //store on file system
        $this->_storage->store($servicePdf, file_get_contents($generatedFile));
        return $filepath;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 01.10.2011
     * @return string
     */
    public function getPayerId() {
        if ($this->getPayment() == "paypal") {

            $db = Zend_Registry::get('dbAdapterReadOnly');

            $select = $db->select()->from('paypal_transactions')
                    ->where('orderId = ?', $this->getId())
                    ->where('LENGTH(payerId) > 0')
                    ->where('payerId IS NOT NULL');

            $result = $db->query($select)->fetchAll();
            if ($result[0]) {
                return $result[0]['payerId'];
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 01.10.2011
     * @return string
     */
    public function isBlacklisted() {
        $list = new Yourdelivery_Model_DbTable_Paypal_BlackWhiteList();
        return $list->isBlack($this->getPayerId());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 01.10.2011
     * @return string
     */
    public function isWhitelisted() {
        $list = new Yourdelivery_Model_DbTable_Paypal_BlackWhiteList();
        return $list->isWhite($this->getPayerId());
    }

    /**
     * get the url of the current selected location, based on the mode
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.11.2011
     * @return string
     */
    public function getCityUrl($mode = null) {
        if (!$this->getLocation() instanceof Yourdelivery_Model_Location) {
            return '/';
        }
        if ($mode === null) {
            $mode = $this->getMode();
        }
        switch ($this->getMode()) {
            default:
            case 'rest':
                return $this->getLocation()->getCity()->getRestUrl();
            case 'cater':
                return $this->getLocation()->getCity()->getCaterUrl();
            case 'great':
                return $this->getLocation()->getCity()->getGreatUrl();
        }
    }

    /**
     * get the url of the current selected service, based on the mode
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.11.2011
     * @return string
     */
    public function getServiceUrl($mode = null) {
        if (!$this->getService() instanceof Yourdelivery_Model_Servicetype_Abstract) {
            return '/';
        }
        if ($mode === null) {
            $mode = $this->getMode();
        }
        switch ($mode) {
            default:
            case 'rest':
                return $this->getService()->getRestUrl();
            case 'cater':
                return $this->getService()->getCaterUrl();
            case 'great':
                return $this->getService()->getGreatUrl();
        }
    }

    /**
     * add this order to customer favorite
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     *
     * @return boolean
     */
    public function addToFavorite(Yourdelivery_Model_Customer $customer = null, $name = null) {

        if (is_object($customer) && $customer->isPersistent()) {
            $fav = new Yourdelivery_Model_Order_Favorite();
            return $fav->add($this->getId(), $customer->getId(), $name);
        }
        return false;
    }

    /**
     * delete this order from customer favorite
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 13.12.2011
     *
     * @return boolean
     */
    public function deleteFromFavorite() {
        $customer = $this->getCustomer();
        if (is_object($customer) && $customer->isPersistent()) {
            $favRow = Yourdelivery_Model_DbTable_Favourites::findByOrderAndCustomerId($this->getId(), $customer->getId());
            if (!$favRow) {
                $this->logger->warn(sprintf("could not find favorite by given orderId and customerId"));
                return false;
            }

            $fav = null;
            try {
                $fav = new Yourdelivery_Model_Order_Favorite($favRow['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->warn(sprintf("could not create favorite with given id #%s", $favRow['id']));
                return false;
            }
            return $fav->delete($customer->getId()) > 0 ? true : false;
        }
        $this->logger->warn(sprintf("try to delete order favorite which has no registered customer"));
    }

    /**
     * Send order notification per acom
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.11.2011
     * @return
     */
    protected function _sendNotificationPerAcom() {
        $this->logger->info('order #' . $this->getId() . ' has to be send via acom, doing nothing in send method');
    }

    /**
     * Send order notification per fax
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.11.2011
     * @return
     */
    protected function _sendNotificationPerFax() {

        $this->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_BY_FAX, $this->getService()->getNotify(), $this->getService()->getFaxService(), $this->getService()->getFax()), true);

        try {
            $fax = new Yourdelivery_Sender_Fax();
            if (!$fax->send($this->getService()->getFax(), $this->getPdf(), $this->getService()->getFaxService(), 'order', $this->getId())) {
                // cannot comfirm
                $this->setStatus(
                        Yourdelivery_Model_Order_Abstract::DELIVERERROR, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_FAX_ERROR)
                );
            }
        } catch (Yourdelivery_Exception_NoConnection $e) {// cannot comfirm
            $this->setStatus(
                    Yourdelivery_Model_Order_Abstract::DELIVERERROR, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_FAX_ERROR_NO_CONNECTION)
            );
        }
    }

    /**
     * Send order notification per email
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.11.2011
     * @return
     */
    protected function _sendNotificationPerEmail($woopla = true) {
        $order = new Yourdelivery_Model_Order($this->getId());
        $email = new Yourdelivery_Sender_Email_Template('orderrest');
        $email->assign('order', $order)
                ->setSubject(__('Neue Bestellung von Lieferando'))
                ->attachPdf($this->getPdf(), __('bestellzettel.pdf'))
                ->addTo($this->getService()->getEmail())
                ->send();

        // cannot comfirm email
        $this->setStatus(
                Yourdelivery_Model_Order_Abstract::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_BY_EMAIL, $this->getService()->getEmail())
        );

        if ($this->config->domain->base == 'taxiresto.fr' && $woopla) {
            $wooplaService = new Woopla_Connect();
            $wooplaService->setOrder($order);
            $wooplaService->call();
        }
    }

    /**
     * Send order notification per sms printer
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.11.2011
     */
    protected function _sendNotificationPerSms() {

        // get service and sms printer
        $order = new Yourdelivery_Model_Order($this->getId());
        $printer = $order->getService()->getSmsPrinter();

        // topup printer
        if ($printer instanceof Yourdelivery_Model_Printer_Abstract) {
            if ($printer->pushOrder($this->getId())) {
                $this->setStatus(
                        Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_BY_SMS, $printer->getType(), $printer->getId())
                );
            } else {
                $this->setStatus(
                        Yourdelivery_Model_Order_Abstract::DELIVERERROR, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_BY_SMS_ERROR_1, $printer->getType(), $printer->getId())
                );
            }
        } else {
            $this->setStatus(
                    Yourdelivery_Model_Order_Abstract::DELIVERERROR, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_BY_SMS_ERROR_2)
            );
        }
    }

    /**
     * Send out a notification via mobile sms
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.12.2011
     */
    protected function _sendNotificationPerMobile() {
        $order = new Yourdelivery_Model_Order($this->getId());

        //prepare message for sms printer
        $view = Zend_Registry::get('view');
        $view->setDir(APPLICATION_PATH . '/templates/sms/');
        $view->order = $order;
        $in_msg .= $view->render('order.htm');
        $view->setDir(null);

        //cleanup
        $in_msg = Default_Helpers_String::replaceUmlaute($in_msg);
        $in_msg = preg_replace("%[^\040-\176\r\n\t\243]%", '', $in_msg);

        $sms = new Yourdelivery_Sender_Sms();
        if ($sms->send($order->getService()->getTel(), $in_msg)) {
            $this->setStatus(
                    Yourdelivery_Model_Order_Abstract::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_BY_MOBILE, $order->getService()->getTel())
            );
        } else {
            $this->setStatus(
                    Yourdelivery_Model_Order_Abstract::DELIVERERROR, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_BY_MOBILE_ERROR, $order->getService()->getTel())
            );
        }
    }

    /**
     * send out order via ecletica api
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.03.2012
     */
    protected function _sendNotificationPerEcletica() {
        $order = new Yourdelivery_Model_Order($this->getId());
        $ecletica = new Janamesa_Api_Ecletica();
        if ($ecletica->send($order)) {
            $this->setStatus(
                    Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_BY_ECCLECTICA)
            );
        } else {
            $this->setStatus(
                    Yourdelivery_Model_Order_Abstract::DELIVERERROR, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_SENT_BY_ECCLECTICA_ERROR)
            );
        }
    }

    /**
     * check if this order has any ratings
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 15.12.2011
     */
    public function isRated() {
        $rating = $this->getTable()->getRating();
        return $rating->count() > 0 ? true : false;
    }

    /**
     * decide, wheather to show link for rating or not
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 15.12.2011
     *
     * @return boolean
     */
    public function showRatingLink() {
        if ($this->getState() <= 0) {
            $this->logger->debug(sprintf("don't show ratingLink for order #%s, because state <= 0 - state = %s", $this->getId(), $this->getState()));
            return false;
        }

        if ($this->isRated()) {
            $this->logger->debug(sprintf("don't show ratingLink for order #%s, because order is rated", $this->getId()));
            return false;
        }

        if (is_object($this->getCustomer()) && $this->getCustomer()->isPersistent() && $this->getCustomer()->hasRated($this)) {
            $this->logger->debug(sprintf("don't show ratingLink for order #%s, because customer #%s %s has rated", $this->getId(), $this->getCustomer()->getId(), $this->getCustomer()->getFullname()));
            return false;
        }
        $this->logger->debug(sprintf("show ratingLink for order #%s", $this->getId()));
        return true;
    }

    /**
     * get current state
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getState() {
        return $this->_data['state'];
    }

    /**
     * get current state (just to get rid of naming confusion)
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getStatus() {
        return $this->getState();
    }

    /**
     * this will change the state of the order directly in the database
     * without the need of calling the save methode
     *
     * @author mlaug, fhaferkorn (12.07.2011)
     * @param int $status
     *  const DISCOUNT_INVALID = -7;
     *  const FAKE_STORNO = -6;
     *  const PAYMENT_NOT_AFFIRMED = -5;
     *  const BILL_NOT_AFFIRMED = -4;
     *  const FAKE = -3;
     *  const STORNO = -2;
     *  const FAX_ERROR_NO_TRAIN = -15;
     *  const DELIVERERROR = -1;
     *  const NOTAFFIRMED = 0;
     *  const AFFIRMED = 1;
     *  const DELIVERED = 2;
     * @see http://ticket.yourdelivery.local/browse/SP-1567
     * @return boolean
     */
    public function setStatus($status, $comment, $useTransaction = false) {

        if (!($comment instanceof Yourdelivery_Model_Order_StatusMessage)) {
            throw new Exception("Comment is in wrong format");
        }

        $status = (integer) $status;
        $oldStatus = $this->getStatus();

        if (!$this->getTable()->_setStatus($status, $comment, $useTransaction)) {
            return false;
        }

        if ($oldStatus != $status && $this->getCustomer() && method_exists($this->getCustomer(), "clearCache")) {
            $this->getCustomer()->clearCache();
        }

        $this->_data['state'] = $status;

        // sent transation to prompt
        if ($status >= 1) {
            hook_after_fax_is_ok($this);
        }

        //discount actions on status change
        $discount = $this->getDiscount();
        if (is_object($discount)) {
            $discount->getParent()->handleStorno($oldStatus, $status, $this);
            $discount->getParent()->handleAffiliprint($oldStatus, $status, $this);
        }

        // handle fidelity facility here
        $this->handleFidelity($status);
        $this->handlePartnerLocation($status);
        return true;
    }

    /**
     * handle fidelity facility
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.12.2011
     */
    protected function handleFidelity($newStatus) {
        $transactionId = (integer) Yourdelivery_Model_DbTable_Customer_FidelityTransaction::findByOrderId($this->getId());
        $transactionUsageId = (integer) Yourdelivery_Model_DbTable_Customer_FidelityTransaction::findByOrderId($this->getId(), 'usage');
        if ($transactionId <= 0 && $transactionUsageId <= 0) {
            return false;
        }
        switch ($newStatus) {
            case Yourdelivery_Model_Order::FAKE_STORNO:
            case Yourdelivery_Model_Order::FAKE:
            case Yourdelivery_Model_Order::STORNO:
                //storno transaction
                $result = true;
                if ($transactionId > 0) {
                    $result = $this->getCustomer()->getFidelity()->modifyTransaction($transactionId, -1);
                }
                if ($transactionUsageId > 0) {
                    $result = $result && $this->getCustomer()->getFidelity()->modifyTransaction($transactionUsageId, -1);
                }
                return $result;
            case Yourdelivery_Model_Order::DELIVERED:
            case Yourdelivery_Model_Order::AFFIRMED:
            case Yourdelivery_Model_Order::NOTAFFIRMED:
                $result = true;
                if ($transactionId > 0) {
                    $result = $this->getCustomer()->getFidelity()->modifyTransaction($transactionId, 0);
                }
                if ($transactionUsageId > 0) {
                    $result = $result && $this->getCustomer()->getFidelity()->modifyTransaction($transactionUsageId, 0);
                }
                return $result;
            //confirm transaction
            case Yourdelivery_Model_Order::PAYMENT_NOT_AFFIRMED:
                //we do not want to give the customer any fidelity points, if only prepayment
                if ($transactionId > 0) {
                    $result = $this->getCustomer()->getFidelity()->modifyTransaction($transactionId, -1);
                }
                break;
            case Yourdelivery_Model_Order::FAX_ERROR_NO_TRAIN:
                //do nothing
                break;
        }
        return true;
    }

    /**
     * transform this status into an status inside partner geolocation
     * 
     * @todo: go through all status changes and do according to current
     * driver/partner status sth. Maybe inform the partner, that the
     * order has been cancled or stuff...
     * 
     * already defined states:
     *  - 0 - order is affirmed and ready for pickup by partner API
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 30.08.2012
     * @param integer $status
     */
    protected function handlePartnerLocation($status) {
        $partnerStatus = new Yourdelivery_Model_DbTable_Order_Geolocation_StatusLog();

        $lastStatus = $partnerStatus->getLastStatus($this->getId());

        $this->logger->info(sprintf('handlePartnerLocation: last status for order #%s is %s', $this->getId(), $lastStatus));
        switch ($status) {
            /* once the order has reached the deliver service 
             * and no initial status is present we create the 
             * first one. 
             */
            case Yourdelivery_Model_Order::AFFIRMED:
            case Yourdelivery_Model_Order::DELIVERED:
                //no status has been set so far
                if ($lastStatus === false) {
                    $rowId = $partnerStatus->createRow(array(
                        'orderId' => $this->getId(),
                        'statusId' => 0, //TODO: make this integer a constant
                    ))->save();
                    $this->logger->info(sprintf('handlePartnerLocation: created new row #%s for order #%s', $rowId, $this->getId()));
                }
                break;
        }
    }

    /**
     * check if this is a satellite order. If this does not match
     * up with the base domain, we consider it as a base domain
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.10.2011
     * @return boolean
     */
    public function isSatellite() {
        if (!$this->getDomain() || strstr($this->getDomain(), $this->config->domain->base)) {
            return false;
        }

        //we do not see eat-star.de as a satellite
        if (strstr($this->getDomain(), 'eat-star.de')) {
            return false;
        }

        //this is a local fix
        if (strstr($this->getDomain(), 'yourdelivery.local')) {
            return false;
        }

        return true;
    }

    /**
     * @var Yourdelivery_Model_Order_Deliverdelay 
     */
    protected $_deliverDelay = null;

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.07.2012
     * @return Yourdelivery_Model_Order_Deliverdelay
     */
    public function getDeliverDelay() {

        if ($this->_deliverDelay instanceof Yourdelivery_Model_Order_Deliverdelay) {
            return $this->_deliverDelay;
        }

        return $this->_deliverDelay = new Yourdelivery_Model_Order_Deliverdelay();
    }

}
