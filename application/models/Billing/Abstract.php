<?php

/**
 * Description of Abstract
 * @package billing
 * @author mlaug
 */
abstract class Yourdelivery_Model_Billing_Abstract extends Default_Model_Base {
    /**
     * mark bill to be attended for service
     * @param string
     */
    const BILLING_RESTAURANT = 'rest';

    /**
     * mark bill to be attended for company
     * @param string
     */
    const BILLING_COMPANY = 'company';

    /**
     * mark bill to be attended for courier
     * @param string
     */
    const BILLING_COURIER = 'courier';

    /**
     * mark bill to be attended for customer
     * @param string
     */
    const BILLING_CUSTOMER = 'customer';

    /**
     * missing banking data for this company
     * @param int
     */
    const MISSINGBANKDATA = -5;
    
    /**
     * wrong banking data for this company
     * @param int
     */
    const WRONGBANKDATA = -4;
    
    /**
     * bill has been outfalled for some reason
     * @param int
     */
    const OUTFALL = -3;

    /**
     * bill has been refunded
     * @param int
     */
    const REFUND = -2;

    /**
     * bill has been sent. The status must not be saved, it's only for grid functions
     * @param int
     */
    const SENT = -1;
    
    /**
     * bill has not been sent yet
     * @param int
     */
    const NOTSEND = 0;

    /**
     * bill is not yet payed
     * @param int
     */
    const UNPAYED = 1;

    /**
     * bill has been payed
     * @param int
     */
    const PAYED = 2;

    /**
     * bill has been partly payed
     * @param int
     */
    const PARTLYPAYED = 3;


    /**
     * bill has been canceled
     * @param int
     */
    const STORNO = 4;

    /*
     * constant for @var mode
     * 0 -> Bill each month
     */
    const BILL_PER_MONTH = 0;

    /*
     * constant for @var mode
     * 1 -> Bill each two weeks
     */
    const BILL_PER_TWO_WEEKS = 1;

    /*
     * constant for @var mode
     * 2 -> Bill each day
     */
    const BILL_PER_DAY = 2;

    /*
     * constant for @var mode
     * 2 -> Bill each day
     */
    const BILL_PER_TRANSACTION = 3;

    /**
     * file storage
     * @var Default_File_Storage
     */
    protected $_storage = null;
    /**
     * latex compiler
     * @var Yourdelivery_Pdf_Latex
     */
    protected $_latex = null;
    /**
     * start
     * @var int
     */
    public $until = null;
    /**
     * until
     * @var int
     */
    public $from = null;
    /**
     * get all orders
     * @var SplObjectStorage
     */
    public $orders = null;
    /**
     * get number of billing
     * @var string
     */
    public $number = null;
    /**
     * if this is a mahung we add some extra content
     * @var boolean
     */
    public $mahnung = false;
    /**
     * a mahungs text in the heading section
     * @var string
     */
    public $mahnungText = null;
    /**
     * load all custom fields for this bill
     * @author mlaug
     * @since 07.08.2010
     * @var array
     */
    public $custom = null;
    /**
     * check wether this is a voucher or not
     * @var boolean
     */
    public $isVoucher = false;
    /**
     * once an amount has been calculated, it will be remebered
     * until a new project or costcenter (or none) is set
     * @var array
     */
    protected $_remember = array();

    /**
     * get all valid stati
     * @author alex
     * @return array
     */
    static function getStatusse() {
        
        return array(
            self::NOTSEND => __b("Nicht versand"),
            self::UNPAYED => __b("Unbezahlt"),
            self::SENT => __b("Versand"),
            self::PAYED => __b("Bezahlt"),
            self::PARTLYPAYED => __b("Teilbezahlt"),
            self::STORNO => __b("Storno"),
            self::OUTFALL => __b("Ausfall"),
            self::REFUND => __b("Rücküberweisung"),            
            self::WRONGBANKDATA => __b("Falsche Bankdaten"),
            self::MISSINGBANKDATA => __b("Keine Bankdaten"),
        );
    }
    
    /**
     * @author boris
     * @since 16.06.2011
     * @return array
     */
    public function getStatusWording() {
        $arr = $this->getStatusse();
        return $arr[$this->getStatus()];
    }

    /**
     * init latex compiler
     * @param int $id
     * @param Zend_Db_Row_Abstract $current
     */
    public function __construct($id = null, $current = null) {
        //init latex engine
        $this->_latex = new Yourdelivery_Pdf_Latex();
        parent::__construct($id, $current);
    }

    /*
     * to show starting bill date with format "date-month-year"
     * @author mlaug
     * @return string
     */

    public function getFromDate() {
        $a = $this->from;
        $getFrom = date('d-m-y', $a);
        return $getFrom;
    }

    /*
     * to show ending bill date with format "date-month-year"
     * @author mlaug
     * @return string
     */

    public function getUntilDate() {
        $b = $this->until;
        $getUntil = date('d-m-y', $b);
        return $getUntil;
    }

    /**
     * Do a prefly check just to make sure the numbers match!
     * @author mlaug
     * @since 12.11.2010
     * @return boolean
     */
    public function preflyCheck($warnings = array()) {
        foreach ($this->getOrders() as $order) {
            $check = 0;
            foreach ($this->config->tax->types->toArray() as $taxtype) {
                $check += $order->getTax($taxtype) + $order->getItem($taxtype);
            }

            $total = $order->getTotal()
                    + $order->getServiceDeliverCost()
                    + $order->getCourierCost()
                    - $order->getCourierDiscount();

            if (abs(round($check) - $total) > 1) {
                $warn = $warnings[] = sprintf("Order %s is not matching numbers Real:%d Consolidated:%d", $order->getId(), $total, round($check));
                $this->logger->warn($warn);
            }
        }

        //send out an email to me, to check that billings!
        if (count($warnings) > 0) {
            Yourdelivery_Sender_Email::quickSend('Please check: ' . $this->getNumber(), implode('\n', $warnings), null, 'springer@lieferando.de');
            return false;
        }
        
        return true;
    }

    /**
     * set the time period for the next bill
     * @author mlaug
     * @todo: calculate time period dynamically (and correct :D)
     * @param int $from
     * @param int $until
     * @param int $mode
     */
    public function setPeriod($from=null, $until=null, $mode=0) {
        if ($until > 0 && $from > 0) {
            $this->until = $until;
            $this->from = $from;
        }
        else {
            $now = time();
            switch ($mode) {
                default:
                    break;
                    
                case self::BILL_PER_MONTH:
                    $this->from = strtotime('first day of last month 00:00:00');
                    $this->until = strtotime('last day of last month 23:59:59');
                    break;

                case self::BILL_PER_TWO_WEEKS:
                    if (date('d', $now) > 15) {
                        $this->from = strtotime('first day of this month 00:00:00');
                        $this->until = strtotime(date('15.m.Y 23:59:59', time()));
                    }
                    else {
                        $this->from = strtotime('+15 day', strtotime('first day of last month 00:00:00'));
                        $this->until = strtotime('last day of last month 23:59:59');
                    }
                    break;

                case self::BILL_PER_DAY:
                    $this->from = strtotime('yesterday 00:00:00');
                    $this->until = strtotime('yesterday 23:59:59');
                    break;

                case self::BILL_PER_TRANSACTION:
                    $this->from = strtotime('yesterday 23:59:59');
                    $this->until = $this->from;
                    break;
            }
        }
    }

    /**
     * set number to be used for this bill
     * @author mlaug
     * @since 19.08.2010
     * @param string $number
     */
    public function setNumber($number) {
        $this->number = $number;
    }

    /**
     * must be implemented to create pdf and rows
     * @author mlaug
     * @since 19.08.2010
     * @abstract
     */
    abstract function create();

    /**
     * must be implemented to get all orders
     * @author mlaug
     * @since 19.08.2010
     * @abstract
     */
    abstract function getOrders();

    /**
     * @author mlaug
     * @since 19.08.2010
     * @abstract
     */
    abstract function getObject();

    /**
     * get heading for bill
     * @author mlaug
     * @since 19.08.2010
     * @return array
     */
    public function getCustomized() {

        $obj = $this->getObject();

        if (!is_object($obj)) {
            return array();
        }
        
        // get template
        $template = $this->getTemplateName();

        //set defaults
        $default = array(
            'heading' => $obj->getName(),
            'street' => $obj->getStreet(),
            'hausnr' => $obj->getHausnr(),
            'zHd' => null,
            'plz' => $obj->getPlz(),
            'city' => $obj->getOrt()->getOrt(),
            'ktoName' => $obj->getKtoName(),
            'ktoNr' => $obj->getKtoNr(),
            'ktoBlz' => $obj->getKtoBlz(),
            'ktoIban' => $obj->getKtoIban(),
            'ktoSwift' => $obj->getKtoSwift(),
            'ktoBank' => $obj->getKtoBank(),
            'ktoAgentur' => $obj->getKtoAgentur(),
            'ktoDigit' => $obj->getKtoDigit(),
            'ustIdNr' => $obj->getUstIdNr(),
            'showCostcenter' => true,
            'showProject' => true,
            'showEmployee' => true,
            'verbose' => false,
            'projectSub' => false,
            'costcenterSub' => false,
            'template' => $template? $template : 'standard',
            'reminder' => 14,
            'content' => '',
            'addition' => '',
            'preamble' => __('Für die Vermittlung von Speisen und Getränken der in der Anlage aufgelisteten Dienstleister,')
        );

        //load customized from object
        $customized = array_merge($default, $obj->getBillingCustomizedData());

        //load data (if any) for this bill
        if (is_integer($this->getOldId()) || $this->isPersistent()) {
            $customized_bill = $this->getSingleCustomized($this->getOldId());
            if ($customized_bill instanceof Yourdelivery_Model_Billing_Customized_Single) {
                $customized = array_merge($customized, $customized_bill->getData());
            }
        }

        $this->custom = $customized;
        return $customized;
    }

    /**
     * check if there is a single customization for this bill
     * @author mlaug
     * @since 07.08.2010
     * @param int $oldId
     * @return Yourdelivery_Model_Billing_Customized_Single
     */
    public function getSingleCustomized($oldId = null) {

        if (is_null($oldId) && is_null($this->getId())) {
            return new Yourdelivery_Model_Billing_Customized_Single();
        }

        if (is_null($oldId)) {
            $oldId = $this->getId();
        }

        $cust = $this->getTable()->getCustomized($oldId);
        if (!is_null($cust)) {
            try {
                return new Yourdelivery_Model_Billing_Customized_Single($cust->id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return new Yourdelivery_Model_Billing_Customized_Single();
            }
        }
        return new Yourdelivery_Model_Billing_Customized_Single();
    }

    /**
     * get assiciated table
     * @author mlaug
     * @since 19.08.2010
     * @return Yourdelivery_Model_DbTable_Billing
     */
    public function getTable() {
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Billing();
        }
        return $this->_table;
    }

    /**
     * @author mlaug
     * @since 30.08.2010
     * @return Zend_Db_Rowset_Abstract
     */
    public function getSendFiles() {
        return $this->getTable()
                ->getCurrent()
                ->findDependentRowset('Yourdelivery_Model_DbTable_Billing_Sent');
    }

    
    /**
     * get history of status
     * @author Alex Vait <vait@lieferando.de>
     * @since 19.06.2012
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getStateHistory() {
        return $this->getTable()->getStateHistory();
    }        

    /**
     * get top used PLZ of orders of this billing
     * @author jens naie <naie@lieferando.de>
     * @since 04.07.2012
     * @param int
     * @return array
     */
    public function getTopPlz($limit = 10) {
        // use cached orders
        $topPlz = array();
        $orders = $this->getOrders();
        foreach($orders as $order) {
            $plz = $order->getLocation()->getPlz();
            $topPlz[$plz] = isSet($topPlz[$plz])? $topPlz[$plz]+1 : 1;
        }
        arsort($topPlz);
        return array_slice($topPlz, 0, $limit);
    }

    /**
     * get top used meals of orders of this billing
     * @author jens naie <naie@lieferando.de>
     * @since 04.07.2012
     * @param int
     * @return array
     */
    public function getTopMeals($limit = 10) {
        // use cached orders
        $topMeals = array();
        $orders = $this->getOrders();
        foreach($orders as $order) {
            $meals = $order->getMeals();
            foreach ($meals as $meal) {
                $topMeals[$meal['name']] = 
                        isSet($topMeals[$meal['name']])? 
                            $topMeals[$meal['name']]+1 
                            : 1;
            }
        }
        arsort($topMeals);
        return array_slice($topMeals, 0, $limit);
    }

    /**
     * get top frequentated time of orders of this billing
     * @author jens naie <naie@lieferando.de>
     * @since 04.07.2012
     * @param int
     * @return array
     */
    public function getTopTimes($limit = 10) {
        // use cached orders
        $topTime = array();
        $orders = $this->getOrders();
        foreach($orders as $order) {
            $h = date('H', $order->getTime()).':00';
            $topTime[$h] = isSet($topTime[$h])? $topTime[$h]+1 : 1;
        }
        arsort($topTime, SORT_STRING);
        return array_slice($topTime, 0, $limit);
    }
}
