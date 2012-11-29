<?php

/**
 * TopUp64 frame
 * @author vpriem
 * @since 27.07.2011
 */
class Yourdelivery_Printer_Topup_Frame {
    
    private $_config;
    
    private $_stx = 0x00;
    private $_msgId = 0x00;
    private $_cryptKey = 0x00;
    private $_termId = 0;
    private $_len;
    private $_data;
    private $_lrc;
   
    /**
     * @author vpriem
     * @since 02.08.2011
     */
    public function __construct() {
        
        $this->_config = Zend_Registry::get('configuration');
    }
    
    /**
     * @author vpriem
     * @since 27.07.2011
     * @param string $frame
     * @return boolean
     */
    public function read($frame) {
        
        $checksum = 0;
        for ($i = 0, $n = strlen($frame); $i < $n; $i++) {
            $checksum = $checksum ^ ord($frame[$i]);
        }
        
        // disable checksum
        $checksum = 0;
		
        if ($checksum == 0){
            for ($i = 0, $n = strlen($frame); $i < $n; $i++) {
                if (ord($frame[$i]) == 0) {
                    $this->_msgId = ord($frame[$i + 1]);
                    $this->_cryptKey = ord($frame[$i + 2]);
                    $this->_termId = ord($frame[$i + 3]) + (ord($frame[$i + 4]) << 8) + (ord($frame[$i + 5]) << 16) + (ord($frame[$i + 6]) << 24);
                    $this->_len = ord($frame[$i + 7]) + (ord($frame[$i + 8]) << 8);
                    $this->_data = substr($frame, $i + 9, $this->_len);
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * @author vpriem
     * @since 27.07.2011
     * @return string
     */
    public function create() {
        
        $frame = pack('C3VvA' . $this->_len, $this->_stx, $this->_msgId, $this->_cryptKey, $this->_termId, $this->_len, $this->_data);
        
        $checksum = 0;
        for ($i = 0, $n = strlen($frame); $i < $n; $i++) {
            $checksum = $checksum ^ ord($frame[$i]);
        }
        $frame .= chr($checksum);
        return $frame;
    }
    
    /**
     * @author vpriem
     * @since 02.08.2011
     * @param resource $socket
     * @return boolean
     */
    public function send($socket) {
        
        $frame = $this->create();
        if (socket_send($socket, $frame, strlen($frame), null) === false) {
            return false;
        }
        return true;
    }
    
    /**
     * @author vpriem
     * @since 02.08.2011
     * @param Yourdelivery_Model_Order $order
     * @return Yourdelivery_Printer_Topup_Frame
     */
    public function addOrder(Yourdelivery_Model_Order $order) {
        
        $customer = $order->getCustomer();
        $location = $order->getLocation();
        $service  = $order->getService();
        $courier  = $service->getCourier();
        
        $data = array('req' => array(
            'id' => $order->getId(),
            'tn' => $order->getNr(),
            'ot' => date(__("d.m.y H:i"), $order->getTime()),
            'bt' => ($order->getDeliverTime() <= time()
                ? __("sofort")
                : date(__("d.m.y H:i"), $order->getDeliverTime())),
            'an' => $customer->getPrename() . " " . $customer->getName(),
            'as' => $location->getStreet() . " " . $location->getHausnr(),
            'ac' => $location->getCity()->getPlz() . " " . $location->getCity()->getCity(),
            'au' => $location->getCompanyName(),
            'af' => $location->getEtage(),
            'ap' => $location->getTel(),
            'ah' => $location->getComment(),
            'n1' => (is_object($courier) 
                ? __('Bestellung wird von %s abgeholt!', $courier->getName()) 
                : ($order->getCashAmount() 
                    ? ($order->getPaymentAddition()
                        ? __("%s, bitte %s EUR kassieren", $order->getPaymentAdditionReadable(), inttoprice($order->getCashAmount()))
                        : __("Barzahlung, bitte %s EUR kassieren", inttoprice($order->getCashAmount()))) 
                    : __("Achtung: Online bezahlt, bitte NICHT kassieren!"))),
            'n2' => $service->getName() . " " . __("KdNr: %s", $service->getCustomerNr()), 
            'pos' => array(),
            'dc' => $order->getDeliverCost(),
            'vat' => array(),
            'tt' => $order->getPayedAmount(),
            'pe' => (is_object($courier) ? 1 : 0)
                + ($order->getState() == Yourdelivery_Model_Order::STORNO ? 2 : 0)
                + (($service->isAvanti() && $order->getDomain() == 'www.avanti.de' ? 1 : 0) * 256),
        ));
        
        $taxes = $this->_config->tax->types->toArray();
        foreach ($taxes as $tax) {
            if ($order->getTax($tax)) {
                $data['req']['vat'][] = array(
                    'vp' => $tax * 100,
                    'tv' => intval($order->getTax($tax)),
                );
            }
        }
        
        $card = $order->getCard();
        foreach ($card['bucket'] as $items) {
            foreach ($items as $item) {
                $meal = $item['meal'];

                $options = array();
                if ($meal->getCurrentOptionsCount()) {
                    foreach ($meal->getCurrentOptions() as $option) {
                        $options[] = $option->getName() . ($option->getCost() ? ">" . __("%s EUR", inttoprice($option->getCost())) : "");
                    }
                }

                $extras = array();
                if ($meal->getCurrentExtrasCount()) {
                    foreach ($meal->getCurrentExtras() as $extra) {
                        $extras[] = ($extra->getCount() > 1 ? $extra->getCount() . " x " : "") . $extra->getName() . ($extra->getCost() ? ">" . __("%s EUR", inttoprice($extra->getCost())) : "");
                    }
                }

                $data['req']['pos'][] = array(
                    'it' => $meal->getName() . " " . $meal->getCurrentSizeName() . 
                            (count($options) ? "\n+ " . implode("\n+ ", $options) : "") . 
                            (count($extras) ? "\n+ " . implode("\n+ ", $extras) : "") . 
                            ($meal->getSpecial() ? "\n" . "*** " . $meal->getSpecial() . " ***" : ""),
                    'in' => $meal->getNr(),
                    'ic' => $item['count'],
                    'pr' => $meal->getAllCosts(),
                );
            }
        }
        
        return $this->setData($data);
    }
    
    /**
     * @author vpriem
     * @since 27.07.2011
     * @return string
     */
    public function getMsgId() {
        
        return $this->_msgId;
    }
    
    /**
     * @author vpriem
     * @since 27.07.2011
     * @return Yourdelivery_Printer_Topup_Frame
     */
    public function setMsgId($msgId) {
        
        $this->_msgId = $msgId;
        return $this;
    }
    
    /**
     * @author vpriem
     * @since 27.07.2011
     * @return string
     */
    public function getTermId() {
        
        return $this->_termId;
    }
    
    /**
     * @author vpriem
     * @since 27.07.2011
     * @return Yourdelivery_Printer_Topup_Frame
     */
    public function setTermId($termId) {
        
        $this->_termId = $termId;
        return $this;
    }
    
    /**
     * @author vpriem
     * @since 27.07.2011
     * @return array
     */
    public function getData() {
        
        return json_decode($this->_data, true);
    }
    
    /**
     * @author vpriem
     * @since 16.11.07.2011
     * @return string
     */
    public function getRawData() {
        
        return $this->_data;
    }
    
    /**
     * @author vpriem
     * @since 16.11.2011
     * @return string
     */
    public function getJsonLastError() {
        
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return 'JSON_ERROR_NONE';
                
            case JSON_ERROR_DEPTH:
                return 'JSON_ERROR_DEPTH';
                
            case JSON_ERROR_STATE_MISMATCH:
                return 'JSON_ERROR_STATE_MISMATCH';
                
            case JSON_ERROR_CTRL_CHAR:
                return 'JSON_ERROR_CTRL_CHAR';
                
            case JSON_ERROR_SYNTAX:
                return 'JSON_ERROR_SYNTAX';
                
            case JSON_ERROR_UTF8:
                return 'JSON_ERROR_UTF8';
                
            default:
                return 'JSON_ERROR_UNKNOW';
        }
    }
    
    /**
     * @author vpriem
     * @since 27.07.2011
     * @return Yourdelivery_Printer_Topup_Frame
     */
    public function setData($data) {
        
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $this->_data = $data;
        $this->_len = strlen($data);
        return $this;
    }
    
    /**
     * @author vpriem
     * @since 02.08.2011
     * @return int
     */
    public function getLen() {
        
        return $this->_len;
    }
}
