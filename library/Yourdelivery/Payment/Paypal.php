<?php

/**
 * Paypal Payment
 * Make a transaction through the paypal API
 * @package payment
 * @subpackage paypal
 * @author Vincent Priem <priem@lieferando.de>
 * @since 17.11.2010
 */
class Yourdelivery_Payment_Paypal extends Yourdelivery_Payment_Abstract {

    /**
     * The nvp url
     * @var string
     */
    private $_url;

    /**
     * The web interface url
     * @var string
     */
    private $_webscr;

    /**
     * The username
     * @var string
     */
    private $_username;

    /**
     * The password
     * @var string
     */
    private $_password;

    /**
     * The signature
     * @var string
     */
    private $_signature;

    /**
     * The API version we use
     * @var string
     */
    private $_version = "65.0";

    /**
     * The brandname
     * @var string
     */
    private $_brandname = "lieferando.de";

    /**
     * Constructor
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.11.2010
     */
    public function __construct($forceProduction = false) {

        $config = Zend_Registry::get('configuration');

        // TODO: throw exception
        if (!$config->payment->paypal->enabled) {
            return;
        }

        // read config
        $this->_brandname = $config->domain->base;

        if (IS_PRODUCTION || $forceProduction) {
            $this->_url = "https://api-3t.paypal.com/nvp";
            $this->_webscr = "https://www.paypal.com/cgi-bin/webscr";
            $this->_username = $config->payment->paypal->username;
            $this->_password = $config->payment->paypal->password;
            $this->_signature = $config->payment->paypal->signature;
        } else {
            $this->_url = "https://api-3t.sandbox.paypal.com/nvp";
            $this->_webscr = "https://www.sandbox.paypal.com/cgi-bin/webscr";
            $this->_username = "TESTUSER";
            $this->_password = "TESTPASSWORD";
            $this->_signature = "TESTSIGNATURE";
        }
    }

    /**
     * Redirect the user to paypal
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.11.2010
     * @param string $token
     * @return void
     */
    public function redirectUser($token) {

        $data = array(
            'cmd' => "_express-checkout",
            'useraction' => "commit",
            'token' => $token,
        );
        //header("Location: " . $this->_webscr . "?" . http_build_query($data));
        return $this->_webscr . "?" . http_build_query($data);
    }

    /**
     * Redirect the user to giropay through paypal
     * @author Vincent Priem <priem@lieferando.de>
     * @since 23.03.2011
     * @param string $token
     * @return void
     */
    public function redirectUserToGiropay($token) {

        $data = array(
            'cmd' => "_complete-express-checkout",
            'useraction' => "commit",
            'token' => $token,
        );
        header("Location: " . $this->_webscr . "?" . http_build_query($data));
    }

    /**
     * Verify IPN request
     * 
     * @param array $data
     * @return string
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 23.03.2011
     * @modified Daniel Hahn, 27.06.2012
     */
    public function notifyValidate($data) {

        $data = array_merge(array(
            'cmd' => "_notify-validate"
                ), $data);
        $query = http_build_query($data);
        $url = parse_url($this->_webscr);

        $header = "POST " . $url['path'] . " HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($query) . "\r\n\r\n";

        for ($i = 0; $i < 5; $i++) {
            $fp = @fsockopen("ssl://" . $url['host'], 443, $errno, $errstr, 30);
            if ($fp) {
                break;
            }
        }

        if (!$fp) {
            return "SOCKET UNAVAILABLE";
        }
        
        fputs($fp, $header . $query);
        $res = "";
        while (!feof($fp)) {
            $res .= fgets($fp, 1024);            
        }
        //return total response
        if(strlen($res) > 0) {
            return $res;
        }
        
        fclose($fp);
        return "NO DATA";
    }

    /**
     * Sets up the Express Checkout transaction
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.11.2010
     * @throws Yourdelivery_Payment_Paypal_Exception
     * @param Yourdelivery_Model_Order $order
     * @param string $returnUrl
     * @param string $cancelUrl
     * @return array|boolean
     */
    public function setExpressCheckout(Yourdelivery_Model_Order $order, $returnUrl, $cancelUrl, $giropaySuccessUrl = null, $giropayCancelUrl = null) {

        if (strpos($returnUrl, "://") === false) {
            $returnUrl = "http://" . HOSTNAME . $returnUrl;
        }

        if (strpos($cancelUrl, "://") === false) {
            $cancelUrl = "http://" . HOSTNAME . $cancelUrl;
        }

        // use giropay workaround
        // in the iPhone app we set no giropay url
        $useGiropayWorkaround = $giropaySuccessUrl === null;

        // get some stuff
        $customer = $order->getCustomer();
        $location = $order->getLocation();

        // default data
        $data = array(
            'METHOD' => "SetExpressCheckout",
            'RETURNURL' => $returnUrl,
            'CANCELURL' => $cancelUrl,
            'NOSHIPPING' => 1,
            'ADDROVERRIDE' => 1,
            'LOCALECODE' => $this->getCountryCode(),
            'EMAIL' => IS_PRODUCTION ? $customer->getEmail() : "samson_1334247257_pre@yourdelivery.de",
            'BRANDNAME' => $this->_brandname,
            'PAYMENTREQUEST_0_AMT' => inttoprice($order->getAbsTotal(), 2, "."),
            'PAYMENTREQUEST_0_CURRENCYCODE' => $this->getCurrencyCode(),
            'PAYMENTREQUEST_0_PAYMENTACTION' => $useGiropayWorkaround ? "Authorization" : "Sale",
            'PAYMENTREQUEST_0_ITEMAMT' => inttoprice($order->getAbsTotal(false, true, false), 2, "."),
            'PAYMENTREQUEST_0_SHIPPINGAMT' => inttoprice($order->getDeliverCost(), 2, "."),
            'PAYMENTREQUEST_0_SHIPTONAME' => $customer->getPrename() . " " . $customer->getName(),
            'PAYMENTREQUEST_0_SHIPTOSTREET' => $location->getStreet() . " " . $location->getHausnr(),
            'PAYMENTREQUEST_0_SHIPTOCITY' => $location->getOrt()->getOrt(),
            'PAYMENTREQUEST_0_SHIPTOSTATE' => $location->getOrt()->getOrt(),
            'PAYMENTREQUEST_0_SHIPTOZIP' => $location->getOrt()->getPlz(),
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => $this->getCountryCode(),
            'PAYMENTREQUEST_0_SHIPTOPHONENUM' => $location->getTel(),
            'PAGESTYLE' => $this->getCountryCode()
        );

        // giropay for DE only
        if ($this->getCountryCode() == "DE") {
            if ($giropaySuccessUrl !== null) {
                if (strpos($giropaySuccessUrl, "://") === false) {
                    $giropaySuccessUrl = "http://" . HOSTNAME . $giropaySuccessUrl;
                }
                $data['GIROPAYSUCCESSURL'] = $giropaySuccessUrl;
                $data['BANKTXNPENDINGURL'] = $giropaySuccessUrl;
            }
            if ($giropayCancelUrl !== null) {
                if (strpos($giropayCancelUrl, "://") === false) {
                    $giropayCancelUrl = "http://" . HOSTNAME . $giropayCancelUrl;
                }
                $data['GIROPAYCANCELURL'] = $giropayCancelUrl;
            }
        }

        /**
         * item discount is not supported by paypal,
         * only shipping discount, we have to use a trick!
         * discount can be cover the shipping amount
         * so we include this last one back to the item amount
         * and remove it from the parameters
         */
        $discount = $order->getDiscountAmount();
        $budget = $order->getBudgetAmount();
        if ($discount > 0 || $budget > 0) {
            $data['PAYMENTREQUEST_0_ITEMAMT'] = inttoprice($order->getAbsTotal(false), 2, ".");
            unset($data['PAYMENTREQUEST_0_SHIPPINGAMT']);
            $data['L_PAYMENTREQUEST_0_NAME0'] = __("Bestellung-Nr. %s", $order->getNr());
            $data['L_PAYMENTREQUEST_0_AMT0'] = inttoprice($order->getAbsTotal(false), 2, ".");
            $data['L_PAYMENTREQUEST_0_QTY0'] = 1;
        } else {
            $i = 0;
            $card = $order->getCard();
            foreach ($card['bucket'] as $items) {
                foreach ($items as $item) {
                    $data['L_PAYMENTREQUEST_0_NAME' . $i] = $item['meal']->getName();
                    $data['L_PAYMENTREQUEST_0_AMT' . $i] = inttoprice($item['meal']->getAllCosts(), 2, ".");
                    $data['L_PAYMENTREQUEST_0_QTY' . $i] = $item['count'];
                    $i++;
                }
            }
        }

        return $this->_process($order, $data);
    }

    /**
     * Obtains information about the buyer from PayPal, including shipping information.
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.11.2010
     * @throws Yourdelivery_Payment_Paypal_Exception
     * @param Yourdelivery_Model_Order $order
     * @param string $token
     * @return array|boolean
     */
    public function getExpressCheckoutDetails(Yourdelivery_Model_Order $order, $token) {

        return $this->_process($order, array(
            'METHOD' => "GetExpressCheckoutDetails",
            'TOKEN' => $token,
        ));
    }

    /**
     * Completes the Express Checkout transaction, including the actual total amount of the order.
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.11.2010
     * @throws Yourdelivery_Payment_Paypal_Exception
     * @param Yourdelivery_Model_Order $order
     * @param string $token
     * @param string $payerId
     * @param string $amount
     * @return array|boolean
     */
    public function doExpressCheckoutPayment(Yourdelivery_Model_Order $order, $token, $payerId, $notifyUrl = null) {

        $data = array(
            'METHOD' => "DoExpressCheckoutPayment",
            'TOKEN' => $token,
            'PAYERID' => $payerId,
            'PAYMENTREQUEST_0_AMT' => inttoprice($order->getAbsTotal(), 2, "."),
            'PAYMENTREQUEST_0_CURRENCYCODE' => $this->getCurrencyCode(),
            'PAYMENTREQUEST_0_PAYMENTACTION' => "Sale",
        );

        // use this to confirm giropay payments
        // for DE only
        if ($this->getCountryCode() == "DE" && $notifyUrl !== null) {
            if (strpos($notifyUrl, "://") === false) {
                $notifyUrl = "http://" . HOSTNAME . $notifyUrl;
            }
            $data['PAYMENTREQUEST_0_NOTIFYURL'] = $notifyUrl . "/id/" . $order->getId();
        }

        return $this->_process($order, $data);
    }

    /**
     * Issue a refund to the PayPal account holder associated with a transaction.
     * @author Vincent Priem <priem@lieferando.de>
     * @since 16.01.2011
     * @throws Yourdelivery_Payment_Paypal_Exception
     * @param Yourdelivery_Model_Order $order
     * @return array|boolean
     */
    public function refundTransaction(Yourdelivery_Model_Order_Abstract $order) {

        $transactionId = null;

        $transactions = $order->getTable()
                ->getPaypalTransactions();
        foreach ($transactions as $transaction) {
            $params = $transaction->getParams();
            $response = $transaction->getResponse();
            if ($params['METHOD'] == "DoExpressCheckoutPayment" && $response['ACK'] == "Success") {
                $transactionId = $response['PAYMENTINFO_0_TRANSACTIONID'];
                break;
            }
        }

        if ($transactionId === null) {
            throw new Yourdelivery_Payment_Paypal_Exception("No TRANSACTIONID could be found");
        }

        $data = array(
            'METHOD' => "RefundTransaction",
            'TRANSACTIONID' => $transactionId,
            'REFUNDTYPE' => "Full",
            'CURRENCYCODE' => $this->getCurrencyCode(),
        );
        
        return $this->_process($order, $data);
    }

    /**
     * Build, post and parse the request
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.11.2010
     * @throws Yourdelivery_Payment_Paypal_Exception
     * @param Yourdelivery_Model_Order $order
     * @param array $data
     * @return array|boolean
     */
    private function _process($order, $data) {

        // log every transactions
        $dbTable = new Yourdelivery_Model_DbTable_Paypal_Transactions();
        $dbRow = $dbTable->createRow(array(
            'orderId' => $order->getId(),
            'params' => serialize($data),
                ));
        $dbRow->payerId = isset($data['PAYERID']) ? $data['PAYERID'] : "";

        // build request
        $data = array_merge(array(
            'USER' => $this->_username,
            'PWD' => $this->_password,
            'SIGNATURE' => $this->_signature,
            'VERSION' => $this->_version,
                ), $data);
        $request = $this->_url . "?" . http_build_query($data);

        // post request
        $try = 0;
        $response = false;
        while ($response === false && $try < 3) { // try 3 times on internal error
            if ($try) {
                usleep(100000);
            }
            $response = @file_get_contents($request);
            if ($response !== false) {
                parse_str($response, $response);
                if ($response['ACK'] == "Failure" && $response['L_ERRORCODE0'] == "10001") {
                    $response = false;
                }
            }
            $try++;
        }

        $dbRow->response = $response !== false ? serialize($response) : "";
        $dbRow->token = $response !== false ? $response['TOKEN'] : null;
        $dbRow->save();

        if ($response === false) {
            throw new Yourdelivery_Payment_Paypal_Exception("Paypal: Failed to connect to API");
        }

        return $response;
    }

    /**
     * return URL for testcase
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 10.11.2011
     * @return string
     */
    public function getRedirectUrl() {
        return $this->_webscr;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.04.2012
     * @param int $orderId
     * @return boolean 
     */
    public function setTokenInvalid($orderId) {

        $dbTable = new Yourdelivery_Model_DbTable_Paypal_Transactions();

        $rows = $dbTable->getByOrder($orderId);
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $row->tokenValid = 0;
                $row->save();
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.04.2012
     * @param string $token
     * @return boolean|int
     */
    public function getOrderIdFromToken($token) {

        $dbTable = new Yourdelivery_Model_DbTable_Paypal_Transactions();

        $orderId = false;
        $rows = $dbTable->getByToken($token);
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                if ($row->tokenValid == 0) {
                    return false;
                }
                $orderId = $row->orderId;
            }
        }

        return $orderId;
    }

}
