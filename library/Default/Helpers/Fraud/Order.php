<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.06.2012
 */
class Default_Helpers_Fraud_Order {

    /**
     * @var Zend_Config 
     */
    protected $_config;

    /**
     * @var Zend_Log 
     */
    protected $_logger;

    /**
     * @var Yourdelivery_Model_Order 
     */
    protected $_order;

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     * @param Yourdelivery_Model_Order_Abstract $order 
     */
    public function __construct(Yourdelivery_Model_Order_Abstract $order) {

        $this->_config = Zend_Registry::get('configuration');
        $this->_logger = Zend_Registry::get('logger');
        $this->_order = $order;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     * @param string $matching
     * @param string $value1 haystack
     * @param string $value2 needle
     */
    public function mark($behaviour, $reason) {

        $order = $this->_order;
        $state = Yourdelivery_Model_Support_Blacklist::getBehaviourToOrderState($behaviour);

        $order->setStatus(
                $state, new Yourdelivery_Model_Order_StatusMessage(
                        Yourdelivery_Model_Order_StatusMessage::ORDER_FRAUD_MESSAGE,
                        $behaviour, $reason
                        )
        );
        $this->_logger->warn(sprintf("BLACKLIST: order #%s marked as %s because %s", $order->getId(), $behaviour, $reason));
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     * @return boolean|null
     */
    public function checkPayment() {

        $order = $this->_order;
        $orderPayment = $order->getPayment();

        // ebanking never fraud, (c) Cristoph
        if (in_array($orderPayment, array("ebanking", "bill"))) {
            $this->_logger->info(sprintf("BLACKLIST: ignore order #%s cause of payment", $order->getId(), $orderPayment));
            return false;
        }

        return null;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     * @return boolean|null
     */
    public function checkCustomer() {

        $order = $this->_order;
        $orderCustomer = $order->getCustomer();

        if ($orderCustomer->isWhitelist()) {
            $this->_logger->info(sprintf("BLACKLIST: ignore order #%s cause customer #%s in whitelist", $order->getId(), $orderCustomer->getId()));
            return false;
        }

        return null;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     * @return boolean|null
     */
    public function checkValues() {

        $order = $this->_order;
        $orderCustomer = $order->getCustomer();
        $orderLocation = $order->getLocation();

        $entries = Yourdelivery_Model_Support_Blacklist::getList(array('email', 'keyword'));
        foreach ($entries as $entry) {

            if ($entry->isDeprecated()) {
                $entry->delete();
                continue;
            }

            $needle = "";
            switch ($entry['type']) {
                case Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_ADDRESS:
                    $needle = $orderLocation->getStreet() . " " .
                            $orderLocation->getHausnr() . " " .
                            $orderLocation->getPlz() . " " .
                            $orderLocation->getCity()->getCity();
                    break;

                case Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_COMPANY:
                    $needle = $orderLocation->getCompanyName();
                    break;

                case Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_CUSTOMER:
                    $needle = $orderCustomer->getFullname();
                    break;

                case Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_IP:
                    $needle = $order->getIpAddr();
                    break;

                case Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_TEL:
                    $needle = $orderLocation->getTel();
                    break;

                case Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_UUID:
                    $needle = $order->getUuid();
                    break;

                case Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL:
                    $needle = $orderCustomer->getEmail();
                    break;

                case Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL_MINUTEMAILER:
                    $parts = explode('@', $orderCustomer->getEmail());
                    $needle = $parts[1];
                    break;

                default:
                    continue;
            }

            if (strlen($needle) > 0 && Yourdelivery_Model_Support_Blacklist::isMatching($entry['matching'], $entry['value'], $needle)) {
                $reason = __b("keyword '%s' %s found in column '%s'", $needle, $entry['matching'], $entry['type']);
                $this->mark($entry['behaviour'], $reason);

                $entry->addMatching($order->getId());

                return true;
            }
        }

        return null;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     * @return boolean|null
     */
    public function checkBucket() {

        $order = $this->_order;
        $orderBucketTotal = $order->getBucketTotal();
        $orderAbsTotal = $order->getAbsTotal();
        $orderMode = $order->getMode();
        $orderPayment = $order->getPayment();

        $barMax = $this->_config->payment->bar->max;
        $creditMax = $this->_config->payment->credit->max;
        $paypalMax = $this->_config->payment->paypal->max;


        if ($orderBucketTotal > $barMax && $orderMode == "rest" && $orderPayment == "bar") {
            $reason = sprintf("bar amount %s € above %s €", intToPrice($orderBucketTotal), intToPrice($barMax));
            $this->mark(Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_FAKE, $reason);
            return true;
        }

        if ($orderBucketTotal > $creditMax && $orderPayment == "credit") {
            $reason = sprintf("credit amount %s € above %s €", intToPrice($orderBucketTotal), intToPrice($creditMax));
            $this->mark(Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_FAKE, $reason);
            return true;
        }

        if ($orderPayment == "paypal" && $orderAbsTotal > $paypalMax) {
            $reason = sprintf("paypal amount %s € above %s €", intToPrice($orderAbsTotal), intToPrice($paypalMax));
            $this->mark(Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_FAKE, $reason);
            return true;
        }

        return null;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return boolean
     */
    public static function detect(Yourdelivery_Model_Order_Abstract $order) {

        $fraud = new Default_Helpers_Fraud_Order($order);

        $methods = array(
            "checkPayment",
            "checkCustomer",
            "checkValues",
            "checkBucket",
        );
        foreach ($methods as $method) {
            $res = $fraud->$method();
            if ($res !== null) {
                return $res;
            }
        }

        return false;
    }

}
