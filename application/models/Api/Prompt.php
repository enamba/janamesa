<?php
/**
 * API to Prompt
 * @since 03.09.2010, 24.09.2010 (vpriem)
 * @author mlaug
 * @package api
 * @subpackage prompt
 */
class Yourdelivery_Model_Api_Prompt{

    /**
     * server URL
     * @var string
     */
    private $_server = null;

    /**
     * api key
     * @var string
     */
    private $_api = null;

    /**
     * logger
     * @var Zend_Log
     */
    private $_logger = null;

    /**
     * option of an delivery
     * @var array
     */
    private $_deliveryOpts = array();

    /**
     * alerts of an delivery
     * @var array
     */
    private $_alerts = array();

    /**
     * current order
     * @var Yourdelivery_Model_Order_Abstract
     */
    private $_order = null;

    /**
     * create a connection to the api of prompt
     * @author mlaug
     * @since 05.09.2010
     * @param Yourdelivery_Model_Order_Abstract $order
     * @todo: add live key!
     */
    public function __construct (Yourdelivery_Model_Order_Abstract $order) {
        $this->_order  = $order;
        $this->_logger = Zend_Registry::get('logger');
        
        // set api url and key
        $service  = $order->getService();
        $courier = $service->getCourier($order);
        if (is_object($courier)) {
            switch ($courier->getName()) {
                case "Prompt":
                    $this->_server = IS_PRODUCTION ? 'http://frankfurt.fahrradkuriere.mobi/api/php' : 'http://test.fahrradkuriere.mobi/api/php';
                    $this->_api    = IS_PRODUCTION ? 'KW3nm4GOIr5J3Zr6eGFN' : '9J88pHAoCTCQpqP7LzXJ';
                    break;

                case "Rotrunner":
                    $this->_server = IS_PRODUCTION ? 'http://rotrunner.fahrradkuriere.mobi/api/php' : 'http://test.rotrunner.fahrradkuriere.mobi/api/php';
                    $this->_api    = IS_PRODUCTION ? 'shei2ikai0cu1eejooWa' : 'shei2ikai0cu1eejooWa';
                    break;
            }
        }
        
        if (empty($this->_server)) {
            throw new Yourdelivery_Exception("Prompt API: No url defined");
        }
        
        if (empty($this->_api)) {
            throw new Yourdelivery_Exception("Prompt API: No key defined");
        }
        
        /**
         * standard delivery options
         */
        $this->_deliveryOpts = array(
            'person'    => true,  //deliver in person
            'postbox'   => false, //do not put the delivery in a postbox, yaks
            'lodge'     => false, //do not put the delivery on the doorstep
            'retry'     => false, //do not retry delivery
            'retrytm'   => false, //do not retry delivery
            'return'    => true,  //do not return to sender
        );

        /**
         * @todo: who is to be alerted
         */
        $this->_alerts = array(
        );

    }

    /**
     * get the waypoints for this order
     * @author mlaug
     * @since 05.09.2010
     * @return array
     */
    public function getWaypoints(){

        if (!is_object($this->_order)) {
            $this->_logger->err('Prompt API: Could not create waypoints, no order object given');
            return array();
        }

        $service  = $this->_order->getService();
        $customer = $this->_order->getCustomer();
        $location = $this->_order->getLocation();

        if (!is_object($service) || !is_object($customer) || !is_object($location)) {
            $this->_logger->err(sprintf('Prompt API: Could not call "rates", not all informations are provided. Service:%d, Customer:%d, Location:%d',
                is_object($service),
                is_object($customer),
                is_object($location)
            ));
            return array();
        }

        $infos = array();
        if ($this->_order->getId()) {
            $infos[] = "ID: " . Yourdelivery_Model_DbTable_Prompt_Nr::getNr($this->_order);
        }
        $card = $this->_order->getCard();
        foreach ($card['bucket'] as $items) {
            foreach ($items as $item) {
                $infos[] = $item['count'] . " x " . $item['meal']->getName() . " " . $item['meal']->getCurrentSizeName();
            }
        }

        // get parent of the location city
        $locationCity = $location->getCity();
        if ($locationCity->getParentCityId()) {
            try {
                $locationCity = new Yourdelivery_Model_City($locationCity->getParentCityId());
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
        }
        
        return array(
            array(
                "name"     => $service->getName(),
                "street"   => $service->getStreet(),
                "nr"       => $service->getHausnr(),
                "postcode" => $service->getPlz(),
                "city"     => $service->getCity()->getCity(),
                "country"  => 'DE',
                "phone"    => $service->getTel(),
                "contact"  => $service->getName(),
                "info"     => implode(", ", $infos)
            ),
            array(
                "name"     => $customer->getFullname() . ($location->getCompanyName() ? ", " . $location->getCompanyName() : ""),
                "street"   => $location->getStreet(),
                "nr"       => $location->getHausnr(),
                "postcode" => $location->getPlz(),
                "city"     => $locationCity->getCity(),
                "country"  => 'DE',
                "phone"    => $location->getTel(),
                "contact"  => $customer->getFullname(),
                "info"     => $location->getAddition()
            )
        );

    }

    /**
     * test the api
     * @author mlaug
     * @since 03.09.2010
     */
    public function test(){
        return $this->_call('test');
    }

    /**
     * get rates of tour
     * @author mlaug
     * @since 03.09.2010
     * @return mixed
     */
    public function rates() {
        if (!is_object($this->_order)) {
            return false;
        }

        $waypoints = $this->getWaypoints();
        if (count($waypoints) != 2) {
            return false;
        }

        /**
         * is this the correct time?
         */
        $pickupTimestamp = $this->_order->computePickUpTime();
        $date = date("Y-m-d H:i:s", $pickupTimestamp); // Today or tomorrow, 12:00
        
        $rates = $this->_call('rates', array(
            'datetime'  => $date,
            'waypoints' => $waypoints
        ));

        if ($rates === false) {
            $this->_logger->err('Promptf API: Could not determine rates');
            return false;
        }

        if ($rates['status']) {
            //get the found rate
            return $rates['rates'][0]['id'];
        }

        if ($rates['code']) {
            $notify = $rates['code'] . ": " . $rates['msg'] . " // Bestellungsnr.: " . $this->_order->getNr();
            
            Yourdelivery_Sender_Email::notify(
                $notify, null, false, "Prompt API Fehler", array(
                "dreber@lieferando.de",
                "Prompt-001@mobileemail.vodafone.de",
                "prompt001@mobileemail.vodafone.de",
                "prompt011@mobileemail.vodafone.de",
                "prompt027@mobileemail.vodafone.de",
                "kurierdienstschwane@mobileemail.vodafone.de"
            ));

            $sms = new Yourdelivery_Sender_Sms();
            $sms->send2support($notify);
        }

        return false;
    }

    /**
     * book a tour
     * @author mlaug
     * @since 03.09.2010
     * @param int $rateId
     */
    public function book($rateId = null) {

        if (!is_object($this->_order)) {
            return false;
        }

        if ($rateId === null) {
            return false;
        }

        $waypoints = $this->getWaypoints();
        if (count($waypoints) != 2) {
            return false;
        }

        $pickupTimestamp = $this->_order->computePickUpTime();
        $date = date("Y-m-d H:i:s", $pickupTimestamp);
        
        $data = array(
            "datetime"     => $date,
            "waypoints"    => $waypoints,
            "rate"         => $rateId,
            "deliveryopts" => $this->_deliveryOpts,
            "alerts"       => $this->_alerts,
            "reference"    => "OrderNr: " . $this->_order->getNr()
        );

        $book = $this->_call('book', $data);

        if ($book === false) {
            $this->_logger->err('Promptf API: Could not determine book');
            return false;
        }

        if ($book['status']) {
            $table = new Yourdelivery_Model_DbTable_Prompt_Tracking();
            $row = $table->createRow();
            $row->orderId = $this->_order->getId();
            $row->trackingId = $book['trackingid'];
            $row->save();
            return $book['trackingid'];
        }

        if ($book['code']) {
            $notify = $book['code'] . ": " . $book['msg'] . " // Bestellungsnr.: " . $this->_order->getNr();
            
            Yourdelivery_Sender_Email::notify(
                $notify, null, false, "Prompt API Fehler", array(
                "dreber@lieferando.de",
                "Prompt-001@mobileemail.vodafone.de",
                "prompt001@mobileemail.vodafone.de",
                "prompt011@mobileemail.vodafone.de",
                "prompt027@mobileemail.vodafone.de",
                "kurierdienstschwane@mobileemail.vodafone.de"
            ));

            $sms = new Yourdelivery_Sender_Sms();
            $sms->send2support($notify);
        }

        return false;
    }

    /**
     * get status of order
     * @author mlaug
     * @since 03.09.2010
     * @param int $trackingId
     */
    public function status ($trackingId) {
        $ret = $this->_call('status', array('trackingid' => $trackingId));
        if ($ret['status']) {
            return $ret;
        }
        return false;
    }

    /**
     * cancel an order
     * @author mlaug
     * @since 03.09.2010
     * @param int $trackingId
     */
    public function cancel ($trackingId) {
        $ret = $this->_call('cancel', array('trackingid' => $trackingId));
        if ($ret['status']) {
            return $ret;
        }
        return false;
    }

    /**
     * get log of order
     * @author mlaug
     * @since 03.09.2010
     * @param int $trackingId
     */
    public function log ($trackingId) {
        $ret = $this->_call('log', array('trackingid' => $trackingId));
        if ($ret['status']) {
            return $ret;
        }
        return false;
    }

    /**
     * get total costs of order
     * @author mlaug
     * @since 03.09.2010
     * @param int $trackingId
     */
    public function total ($trackingId) {
        $ret = $this->_call('total', array('trackingid' => $trackingId));
        if ($ret['status']) {
            return $ret;
        }
        return false;
    }
    
    /**
     * Check address
     * @author vpriem
     * @since 24.09.2010
     * @param string $type client|service
     */
    public function geocode ($type = "client") {
        
        if (!is_object($this->_order)) {
            return false;
        }

        $waypoints = $this->getWaypoints();
        if (count($waypoints) != 2) {
            return false;
        }
        
        if ($type == "client") {
            $waypoint = $waypoints[1];
        }
        else {
            $waypoint = $waypoints[0];
        }
        $waypoint = array(
            "street"   => $waypoint['street'],
            "nr"       => $waypoint['nr'],
            "postcode" => $waypoint['postcode'],
            "city"     => $waypoint['city'],
            "country"  => $waypoint['country'],
        );
        
        $ret = $this->_call('geocode', $waypoint);
        if ($ret['status']) {
            return $ret;
        }
        
        return false;
    }
    
    /**
     * Call API method, append api key for each call
     * @author mlaug
     * @param  string $method Name of the method we want to call
     * @param  array $params Array of parameters for the method
     * @return mixed
     */
    private function _call ($method, $params = array()) {

        //parse in api key
        $params['apikey'] = $this->_api;

        //create a transaction
        $table = new Yourdelivery_Model_DbTable_Prompt_Transactions();
        $row = $table->createRow();
        $row->orderId = $this->_order->getId();
        $row->params = serialize($params);
        $row->action = $method;

        $result = false;
        $resp = @file_get_contents($this->_server . "/" . $method . "?" . http_build_query($params));
        if ($resp !== false) {
            $result = @unserialize($resp);
        }
        if ($result !== false) {
            $row->result  = serialize($result);
            $row->success = true;
            $row->save();
            return $result;
        }
        
        $row->result  = $resp;
        $row->success = false;
        $row->save();        

        $msg = sprintf('Prompt Api: DOWN ??? Error calling method %s (%s) with result "%s"', $method, print_r($params, true), $resp);
        $this->_logger->err($msg);
        Yourdelivery_Sender_Email::error($msg);
        
        return false;
    }

}
