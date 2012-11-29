<?php
/**
 * eBanking Payment
 * Make a transaction through the sofortueberweisung API
 * @package payment
 * @author Vincent Priem <priem@lieferando.de>
 * @since 19.11.2010
 */
class Yourdelivery_Payment_Ebanking extends Yourdelivery_Payment_Abstract {

    /**
     * The url
     * @var string
     */
    private $_url = "https://www.sofortueberweisung.de/payment/start";

    /**
     * The user id
     * @var string
     */
    private $_user_id = "USER ID";

    /**
     * The project id
     * @var string
     */
    private $_project_id = "PROJECT ID";

    /**
     * The project pass
     * @var string
     */
    private $_project_pass = 'PASSWORD';
    
    /**
     * @var string
     */
    private $_payerId;

    /**
     * Constructor
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.11.2010
     */
    public function __construct() {

        $config = Zend_Registry::get('configuration');

        // is enabled?
        if (!$config->payment->ebanking->enabled) {
            return;
        }

        // read config
        if (IS_PRODUCTION) {
            $this->_project_id   = $config->payment->ebanking->project->id;
            $this->_project_pass = $config->payment->ebanking->project->pass;
        }
    }

    /**
     * Get redirect url
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.11.2010
     * @param Yourdelivery_Model_Order $order
     * @return string
     */
    public function redirectUser(Yourdelivery_Model_Order $order) {

        $data = array(
            'user_id'               => $this->_user_id,
            'project_id'            => $this->_project_id,
            'sender_holder'         => "",
            'sender_account_number' => "",
            'sender_bank_code'      => "",
            'sender_country_id'     => $this->getCountryCode(),
            'amount'                => inttoprice($order->getAbsTotal(), 2, "."),
            'currency_id'           => $this->getCurrencyCode(),
            'language_id'           => $this->getCountryCode(),
            'reason_1'              => __("Bestellung-Nr. %s", $order->getNr()),
            'reason_2'              => "",
            'user_variable_0'       => $order->getId(),
            'user_variable_1'       => HOSTNAME . "/payment_ebanking/finish",
            'user_variable_2'       => HOSTNAME . "/payment_ebanking/cancel",
            'user_variable_3'       => HOSTNAME . "/payment_ebanking/notify",
            'user_variable_4'       => "",
            'user_variable_5'       => "",
        );
        
        return $this->_url . "?" . http_build_query($data) . "&hash=" . $this->getHash($data);
    }

    /**
     * Get hash
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.11.2010
     * @param array
     * @return string
     */
    public function getHash($data) {

        unset($data['language_id']);
        unset($data['email_recipient']);
        unset($data['hash']);
        
        $dbTable = new Yourdelivery_Model_DbTable_Ebanking_Transactions();
        $dbRow = $dbTable->createRow(array(
            'orderId' => $data['user_variable_0'],
            'data'    => serialize($data),
        ));
        $dbRow->save();
        $this->_payerId = $dbRow->payerId;
        
        $data['project_password'] = $this->_project_pass;
        
        return sha1(implode("|", $data));

    }
    
    /**
     * return URL for testcase
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 10.11.2011
     * @return string
     */
    public function getRedirectUrl() {
        
        return $this->_url;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.02.2012
     * @return string
     */
    public function getPayerId() {
        
        return $this->_payerId;
    }
}
