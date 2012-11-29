<?php

/**
 * Description of Sms
 * @package sender
 * @subpackage sms
 * @author mlaug
 * @author dscain
 */
class Yourdelivery_Sender_Sms {

    /**
     * @var Zend_Config 
     */
    private $_config;

    /**
     * @var Zend_Log
     */
    private $_logger;

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 09.08.2011
     */
    public function __construct() {

        $this->_config = Zend_Registry::get('configuration');
        $this->_logger = Zend_Registry::get('logger');
    }

    /**
     * Send out sms
     * @author Vincent Priem <priem@lieferando.de>
     * @author Daniel Scain <dscain@lieferando.de>
     * @param string $to
     * @param string $text
     * @param string $gateway
     * @return boolean
     */
    public function send($to, $text, $gateway = null) {

        IS_PRODUCTION ? null : $to = $this->_config->testing->sms;

        if ($gateway === null) {
            $gateway = $this->_config->sender->sms->gateway;
        }

        if (empty($to)) {
            $this->_logger->info(sprintf("SMS: successfully send to nobody via %s", $gateway));

            if (!IS_PRODUCTION) {
                return true;
            }

            return false;
        }

        $_to = $to;
        $to = Default_Helpers_Normalize::telephone($to);
        if ($to === false) {
            $this->_logger->info(sprintf("SMS: cannot send via %s, wrong phone number %s", $gateway, $_to));
            return false;
        }
        $to = preg_replace("/^00/", "+", $to);

        // get config from gateway
        $config = $this->_config->sender->sms->toArray();
        $config = $config[$gateway];

        switch ($gateway) {

            case 'smstrade':
                $url = "http://gateway.smstrade.de";
                $params = array(
                    'from' => 'janamesa',
                    'message' => $text,
                    'to' => $to,
                    'route' => 'gold',
                    'key' => 'KEY'
                );
                break;

            case 'kannel':
                $call = new Yourdelivery_Sender_Sms_Kannel();
                return $call->sendSmsMessage($to, $text);
            case 'lox24':
                $url = "https://www.lox24.eu/API/httpsms.php";
                $params = array(
                    'konto' => $config['account'],
                    'password' => $config['password'],
                    'service' => $config['service'],
                    'text' => $text,
                    'from' => $this->_config->domain->base,
                    'to' => $to,
                    'httphead' => 0,
                );
                break;
            case 'SMSNewsMedia':
                $call = new Yourdelivery_Sender_Sms_SmsNewsMedia();
                return $call->sendSmsMessage($to, $text);
            case 'mobilant':
            default:
                $url = "https://gateway2.mobilant.net/index.php";
                $params = array(
                    'key' => $config['key'],
                    'service' => "sms",
                    'originator' => $this->_config->domain->base,
                    'receiver' => $to,
                    'message' => $text,
                );

                if (!IS_PRODUCTION) {
                    $params['service'] = "test";
                }
                break;
        }

        $callback = $url . "?" . http_build_query($params, "", "&");
        $this->_logger->debug(sprintf('calling url of sms gateway: %s', $callback));
        $resp = file_get_contents($callback);
        $this->_logger->debug(sprintf('response from sms gateway: %s', $resp));

        //return code handlers, if available
        switch ($gateway) {

            default:
                $this->_logger->info('SMS: no return code handler available');
                break;

            case 'smstrade':
                if ($resp == '100') {
                    $this->_logger->info(sprintf("SMS: successfully send via %s to %s with response %s", $gateway, $to, $resp));
                    return true;
                }
                $this->_logger->info(sprintf("SMS: failed send via %s to %s with response %s", $gateway, $to, $resp));
                return false;

            case 'mobilant':
                if (preg_match("/Status:([0-9]+)/", $resp, $matches)) {
                    if ($matches[1] == 100) {
                        $this->_logger->info(sprintf("SMS: successfully send via %s to %s with response %s", $gateway, $to, $resp));
                        return true;
                    }
                }
                $this->_logger->info(sprintf("SMS: failed send via %s to %s with response %s", $gateway, $to, $resp));
                return false;
        }

        $this->_logger->info(sprintf("SMS: successfully send via %s to %s with response %s", $gateway, $to, $resp));
        return true;
    }

    /**
     * Send out sms to all active supporter
     * @author Vincent Priem <priem@lieferando.de>
     * @since 09.08.2011
     * @return void
     */
    public function send2support($text) {
        $supporters = Yourdelivery_Model_Support::allActive();
        foreach ($supporters as $s) {
            $this->send($s['number'], $text);
        }
    }

}
