<?php

/**
 * TopUp64 server
 * @author Vincent Priem <priem@lieferando.de>
 * @since 27.07.2011
 */
class Yourdelivery_Printer_Topup_Server {
    
    private $_server = "192.168.2.105";
    private $_port;
    
    private $_domain = "lieferando.de";
    private $_locale = "de";
    
    /**
     * Gronic update server
     * @var string
     */
    private $_gronic = "186.202.70.30:80";
    
    /**
     * Database
     * @var Zend_Db_Adapter_Abstract
     */
    private $_db;
    
    /**
     * @var Zend_Log
     */
    private $_logger = array();
    private $_loggerFilename = array();
    
    /**
     * All socket
     * => socket
     * @var array
     */
    private $_sockets = array();
    
    /**
     * All socket terminal
     * termId => socket
     * @var array
     */
    private $_clients = array();
    
    /**
     * Socket timeout
     * socketId => timestamp of last call
     * @var array
     */
    private $_timeout = array();
    
    /**
     * @var array
     */
    private $_notify = array(
        "EMAIL"
    );
    
    /**
     * @var Yourdelivery_Model_Printer_Topup_Queue
     */
    private $_queue;
    
    /**
     * @var array
     */
    private $_dbErrors = array();
    
    /**
     * Start the TCP server
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     */
    public function __construct() {
        
        ini_set('error_reporting', E_ALL ^ E_NOTICE);
        ini_set('display_errors', true);
        ini_set('display_startup_errors', true);
        ini_set('error_log', APPLICATION_PATH . sprintf('/logs/phplog-%s.log', date('Ymd')));
        restore_error_handler();
        
        // set db
        $this->_db = Zend_Registry::get('dbAdapter');
        
        // read config
        $config = Zend_Registry::get('configuration');
        
        // get ip address and port
        $this->_server = $config->printer->server;
        $this->_port = $config->printer->port;
        $this->_domain = isset($config->domain->base) ? $config->domain->base : $this->_domain;
        $this->_locale = isset($config->locale->name) ? strtolower(substr($config->locale->name, 0, 2)) : $this->_locale;
        
        // init socket
        $this->_log("Starting server at %s:%s", $this->_server, $this->_port);
        
        if (!($server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
            return $this->_log("Failed:1 " . $this->_getSocketError());
        }
        socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
        if (!@socket_bind($server, $this->_server, $this->_port)) {
            return $this->_log("Failed:2 " . $this->_getSocketError($server));
        }
        if (!@socket_listen($server)) {
            return $this->_log("Failed:3 " . $this->_getSocketError($server));
        }
        $this->_sockets[] = $server;
        $this->_log("Ok");
        $this->_log("Use domain name %s", $this->_domain);
        $this->_log("Use printer locale %s", $this->_locale);
        
        $this->_queue = new Yourdelivery_Model_Printer_Topup_Queue();
        $this->_startLoop();
    }
    
    /**
     * Start an endless loop
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     */
    private function _startLoop() {
        
        while (true) {
            $this->_checkForSocketTimeout();
            $this->_checkOrderQueue();
            $this->_readSockets();
        }
    }
    
    /**
     * Search for socket timeout
     * and offline printer
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     */
    private function _checkForSocketTimeout() {
        
        foreach ($this->_sockets as $socket) {
            $socketId = intval($socket);
            if (!array_key_exists($socketId, $this->_timeout)) {
                continue;
            }

            $ts = $this->_timeout[$socketId];
            if ((time() - $ts) < 360) {
                continue;
            }
            
            $this->_log("Timeout for socket #%s", $socketId);
            $termId = $this->_findTerminal($socket);
            $this->_removeSocket($socket);

            if ($termId === false) {
                continue;
            }
            $this->_log("Terminal #%s goes offline", $termId);

            try {
                $printer = new Yourdelivery_Model_Printer_Topup($termId);
                $printer->setOnline(0);
                $printer->save();

                $restaurants = $printer->getRestaurants();
                foreach ($restaurants as $restaurant) {
                    if ($restaurant->getNotify() == "sms") {
                        $message = new Yourdelivery_Model_Heyho_Messages();
                        $message->setMessage(__b("Drucker ging offline"));
                        $message->addCallbackAvailable("checkprinter/pid/" . $termId);
                        $message->addCallbackAvailable("changerestaurantnotification/pid/" . $termId);
                        $message->addCallbackAvailable("closerestaurantfortoday/pid/" . $termId);
                        $message->setState(1); // disable message so we could make stats
                        $message->save();
                        break; // avoid duplicate messages
                    }
                }
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            catch (Zend_Db_Exception $e) {
                $this->_logDbException($e);
                continue;
            }
        }
    }
    
    /**
     * Check the order queue
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     */
    private function _checkOrderQueue() {
        
        $hold = array();

        try {
            $rows = $this->_queue->getQueue();
        
            foreach ($rows as $row) {
                if ($row->printerId < 100000) {
                    continue;
                }

                $socket = $this->_findSocket($row->printerId);
                // we can send to terminal
                if ($socket !== false) {

                    if (in_array($row->printerId, $hold)) {
                        continue;
                    }

                    // after three time, give up
                    if ($row->state > 3) {
                        $this->_log("Try 3 times to send order #%s to terminal #%s, close socket", $row->orderId, $row->printerId);
                        $this->_removeSocket($socket);
                        continue;
                    }

                    // resend after 1 minutes
                    // if no ack was receive
                    if ($row->state > 0 && (strtotime($row->updated) + 60) > time()) {
                        $hold[] = $row->printerId;
                        continue;
                    }

                    $this->_log("Pull order #%s for terminal #%s", $row->orderId, $row->printerId);

                    try {
                        $order = new Yourdelivery_Model_Order($row->orderId);
                    }
                    catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->_log("Cannot create order #%s for terminal #%s", $row->orderId, $row->printerId);
                        $row->delete();
                        continue;
                    }

                    $this->_log("Send order #%s to terminal #%s via socket #%s", $row->orderId, $row->printerId, intval($socket));

                    $frame = new Yourdelivery_Printer_Topup_Frame();
                    $frame->setTermId($row->printerId)
                          ->addOrder($order)
                          ->send($socket);

                    $hold[] = $row->printerId;

                    $row->state = $row->state + 1;
                    $row->updated = date("Y-m-d H:i:s");
                    $row->save();
                    continue;
                }

                // terminal is offline
                // update order state
                try {
                    $order = new Yourdelivery_Model_Order($row->orderId);
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->_log("Cannot create order #%s for terminal #%s", $row->orderId, $row->printerId);
                    $row->delete();
                    continue;
                }

                $this->_log("Cannot send order #%s to terminal #%s, no socket found", $row->orderId, $row->printerId);

                $order->setStatus(Yourdelivery_Model_Order_Abstract::DELIVERERROR,
                    new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::TOPUP_ERROR)
                );

                $row->state = -1;
                $row->updated = date("Y-m-d H:i:s");
                $row->save();
            }
            
        }
        catch (Zend_Db_Exception $e) {
            $this->_logDbException($e);
        }
    }
    
    /**
     * Read sockets
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     */
    private function _readSockets() {
        
        $sockets = $this->_sockets;
        if (socket_select($sockets, $write = null, $except = null, 4) === false) {
            $this->_log("Could not select socket: " . $this->_getSocketError());
            return;
        }
        
        $this->_log("readSocket: " . print_r($sockets,1));

        foreach ($sockets as $socket) {
            if ($socket == $this->_sockets[0]) {
                if (!($client = @socket_accept($this->_sockets[0]))) {
                    $this->_log("Could not accept socket: " . $this->_getSocketError($this->_sockets[0]));
                    continue;
                }
                $this->_sockets[] = $client;

                $this->_log("Accept socket #%s", intval($client));
                continue;
            }

            $this->_timeout[intval($socket)] = time();

            $data = @socket_read($socket, 1250);
            if ($data === false) {
                $this->_log("Could not read socket: " . $this->_getSocketError($socket));
                $this->_removeSocket($socket);
                continue;
            }
            $this->_log("DATA: %s", $data);

            // for debugging
            if (!IS_PRODUCTION) {
                if (substr($data, 0, 5) == "exit:") {
                    $this->_sendToSocket($socket, "Bye\n");
                    $this->_removeSocket($socket);
                    continue;
                }

                if (substr($data, 0, 3) == "ls:") {
                    foreach ($this->_sockets as $s) {
                        $termId = $this->_findTerminal($s);
                        $this->_sendToSocket($socket, "Socket #%s, assigned to printer #%s\n", intval($s), $termId);
                    }
                    continue;
                }

                if (substr($data, 0, 6) == "queue:") {
                    $d = explode(":", $data);
                    
                    try {
                        $q = new Yourdelivery_Model_Printer_Topup_Queue();
                        $q->setData(array(
                            'printerId' => (integer) $d[1],
                            'orderId' => (integer) $d[2],
                        ));
                        $q->save();
                    }
                    catch (Zend_Db_Exception $e) {
                        $this->_logDbException($e);
                    }

                    $this->_sendToSocket($socket, "ACK\n");
                    continue;
                }
            }

            if ($data === '') {
                $this->_log("Empty data");
                $this->_removeSocket($socket);
                continue;
            }

            // read frame
            $frame = new Yourdelivery_Printer_Topup_Frame();
            if (!$frame->read($data)) {
                $this->_log("Could not read frame '%s'", $data);
                $this->_removeSocket($socket);
                continue;
            }

            // get treminal id
            if (!$frame->getTermId()) {
                $this->_log("No terminal provided");
                $this->_removeSocket($socket);
                continue;
            }

            // get printer
            $printer = new Yourdelivery_Model_Printer_Topup();
            try {
                $printer = new Yourdelivery_Model_Printer_Topup($frame->getTermId());
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $frame->setData(array('err' => array(
                    'em' => __("Unbekannter Terminal"),
                )))->send($socket);

                $this->_log("Unknown terminal #%s", $frame->getTermId());
                $this->_removeSocket($socket);
                continue;
            }
            catch (Zend_Db_Exception $e) {
                $this->_logDbException($e);
            }
            $this->_assignSocket($frame->getTermId(), $socket);

            // ping
            if (!$frame->getLen()) {
                $this->_log("Ping from terminal #%s", $frame->getTermId());
                
                try {
                    $printer->setOnline(1);
                    $printer->getId() ? $printer->save() : null;
                }
                catch (Zend_Db_Exception $e) {
                    $this->_logDbException($e);
                }

                if ($printer->getUpgrade()) {
                    $this->_log("Upgrading terminal #%s from version %s to %s on %s", $frame->getTermId(), $printer->getFirmware(), $printer->getUpgrade(), $this->_gronic);

                    $frame->setData(array('reg' => array(
                        'ts' => time() + date("Z"), // timestamp
                        'fu' => $printer->getUpgrade(), // firmware upgrade
                        'us' => $this->_gronic, // update server
                    )))->send($socket);
                    
                    continue;
                }
                
                // send pong back
                if ($printer->getFirmware() >= 270) {
                    $frame->setData("")
                        ->send($socket);
                    $this->_log("Pong to terminal #%s", $frame->getTermId());
                }
                
                continue;
            }

            // process
            $this->_log("Receive frame from terminal #%s", $frame->getTermId());

            $data = $frame->getData();
            if (!is_array($data)) {
                $this->_log("Received bad JSON from terminal #%s, %s, '%s'", $frame->getTermId(), $frame->getJsonLastError(), $frame->getRawData());

                $frame->setData(array('err' => array(
                    'em' => __("Fehlerhafter JSON"),
                )))->send($socket);
                continue;
            }

            foreach ($data as $command => $params) {
                $this->_log("Receive command '%s' from terminal #%s", $command, $frame->getTermId());

                switch ($command) {

                    // Registrierung
                    case 'reg':
                        $this->_log("Terminal #%s goes online, signal %s, cell %s", $frame->getTermId(), $params['sg'], $params['lc']);

                        if (isset($params['log']) && is_array($params['log'])) {
                            foreach ($params['log'] as $log) {
                                $this->_log("Terminal #%s error %s %s", $frame->getTermId(), date("Y-m-d H:i:s", $log['ls']), $log['lt']);
                            }
                        }
                        
                        $upgrade = $printer->getUpgrade();
                        if ($upgrade == $params['fw']) {
                            $upgrade = "";
                        }
                        if ($upgrade) {
                            $this->_log("Upgrading terminal #%s from version %s to %s", $frame->getTermId(), $params['fw'], $upgrade);
                        }

                        $notify = $printer->getNotify();
                        if ($notify) {
                            $notify = 0;
                            $this->_log("Notify that terminal #%s goes online", $frame->getTermId());

                            $email = new Yourdelivery_Sender_Email();
                            $email->addTo($this->_notify)
                                  ->setSubject(sprintf(__b("Drucker #%s ist angeschaltet"), $frame->getTermId()))
                                  ->setBodyText(__b("DL kann jetzt online gestellt werden."))
                                  ->send();
                        }

                        $frame->setData(array('reg' => array(
                            'ts' => time() + date("Z"), // timestamp
                            'il' => $this->_domain, // last line on the display
                            'lg' => $this->_locale, // language code
                            'cu' => __("EUR"), // currency sign
                            'hl' => __("03060988548"), // hotline
                            'fu' => $upgrade, // firmware upgrade
                            'us' => $this->_gronic, // update server
                        )))->send($socket);

                        try {
                            // Printer has no paper anymore
                            if ($params['po'] == "paperout") {
                                $this->_log("Terminal #%s has no paper", $frame->getTermId());

                                if (!$printer->getPaperout()) {
                                    $message = new Yourdelivery_Model_Heyho_Messages();
                                    $message->setMessage(__b("Drucker hat kein Papier mehr"));
                                    $message->addCallbackAvailable("checkprinterpaper/pid/" . $frame->getTermId());
                                    $message->addCallbackAvailable("changerestaurantnotification/pid/" . $frame->getTermId());
                                    $message->addCallbackAvailable("closerestaurantfortoday/pid/" . $frame->getTermId());
                                    $message->save();
                                }
                            }
                            
                            $printer->setOnline(1);
                            $printer->setSignal($params['sg'] > 31 ? 31 : $params['sg']);
                            $printer->setFirmware($params['fw']);
                            $printer->setUpgrade($upgrade);
                            $printer->setNotify($notify);
                            $printer->setPaperout($params['po'] == "paperout");
                            $printer->getId() ? $printer->save() : null;
                            
                            $this->_queue->repush($frame->getTermId());
                            
                        }
                        catch (Zend_Db_Exception $e) {
                            $this->_logDbException($e);
                        }
                        break;

                    // Abmeldung
                    case 'don':
                        try {
                            $printer->setOnline(0);
                            $printer->getId() ? $printer->save() : null;
                        }
                        catch (Zend_Db_Exception $e) {
                            $this->_logDbException($e);
                        }

                        $this->_log("Terminal #%s goes offline", $frame->getTermId());
                        $this->_removeSocket($socket);
                        break;

                    // Bestätigung
                    case 'con':
                        $orderId = $params['id'];
                        $waitingTime = $params['wt']; // 0 for preorder

                        try {
                            try {
                                $order = new Yourdelivery_Model_Order($orderId);
                            }
                            catch(Yourdelivery_Exception_Database_Inconsistency $e) {
                                $frame->setData(array('err' => array(
                                    'em' => __("Bestellung nicht gefunden"),
                                )))->send($socket);

                                $this->_log("Cannot create order #%s for terminal #%s", $orderId, $frame->getTermId());
                                break;
                            }

                            // Remove order from queue
                            $this->_queue->deleteFrom($orderId);
                            
                            // Storno, not implemented yet
                            if ($waitingTime == 9999) {
                                $this->_log("Terminal #%s cancels order #%s", $frame->getTermId(), $orderId);
                            }
                            // Timeout, sent from printer automatically
                            elseif ($waitingTime == 9998) {
                                if ($order->getState() == Yourdelivery_Model_Order_Abstract::STORNO) {
                                    $frame->setData(array('can' => array(
                                        'id' => $orderId, // order id
                                        'ft' => __("Achtung: Bestellung wurde von %s storniert", $this->_domain),
                                    )))->send($socket);
                                }

                                $this->_log("Terminal #%s did not accept order #%s, timeout", $frame->getTermId(), $orderId);
                            }
                            // Accept cancelation
                            elseif ($waitingTime == 9990) {
                                $this->_log("Terminal #%s accepts cancelation for order #%s", $frame->getTermId(), $orderId);
                            }
                            // Wartezeit
                            else {
                                $deliverDelay = $order->getDeliverDelay();
                                $deliverDelay->setServiceDeliverDelay($waitingTime * 60)
                                             ->save();
                                
                                $arrivalTime = $order->computeArrivalTime();
                                $service = $order->getService();
                                if ($service->hasCourier()) {
                                    $arrivalTime = $order->computePickUpTime();
                                }
                                
                                $frame->setData(array('ord' => array(
                                    'id' => $orderId,
                                    'bt' => date(__("d.m.y H:i"), $arrivalTime), // deliver time
                                    'ft' => "",
                                )))->send($socket);

                                // send user notification
                                $isSmsSent = false;
                                
                                $customer = $order->getCustomer();
                                $phoneNumber = $order->getLocation()->getTel();
                                if ($phoneNumber) {
                                    $phoneNumber = Default_Helpers_Normalize::telephone($phoneNumber);
                                    if (Default_Helpers_Phone::isMobile($phoneNumber)) {
                                        $sms = new Yourdelivery_Sender_Sms_Template("printer_notify");
                                        $sms->assign('order', $order);
                                        if ($isSmsSent = $sms->send($phoneNumber)) {
                                            $this->_log('Successfully send sms to %s for order #%s', $phoneNumber, $orderId);
                                        } else {
                                            $this->_log('Could not send sms to %s for order #%s', $phoneNumber, $orderId);
                                        }
                                    } else {
                                        $this->_log('Phone number %s is not valid for order #%s', $phoneNumber, $orderId);
                                    }
                                }

                                if (!$isSmsSent) {
                                    $this->_log('Send email to %s for order #%s', $customer->getEmail(), $orderId);

                                    $email = new Yourdelivery_Sender_Email_Template("printer_notify.txt");
                                    $email->setSubject($order->isPreOrder() 
                                                ? __("Voraussichtliche Lieferzeit %s", $deliverDelay->computeDelayFormated())
                                                : __("Voraussichtliche Lieferzeit am %s", date(__("d.m.Y H:i"), $order->computeArrivalTime())))
                                            ->addTo($customer->getEmail())
                                            ->assign('order', $order)
                                            ->send();
                                } 
                                
                                // call hook_after_fax_is_ok before setStatus
                                // cause setStatus call hook_after_fax_is_ok too
                                hook_after_fax_is_ok($order);
                                $order->setStatus(Yourdelivery_Model_Order_Abstract::AFFIRMED,
                                    new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::TOPUP_OK, $waitingTime)
                                );

                                $this->_log("Terminal #%s accepts order #%s with %s mins", $frame->getTermId(), $orderId, $waitingTime);
                            }
                        }
                        catch (Zend_Db_Exception $e) {
                            $frame->setData(array('can' => array(
                                'id' => $orderId,
                                'ft' => __("Achtung: Bestellung konnte nicht bestätigt werden"),
                            )))->send($socket);
                            
                            $this->_logDbException($e);
                        }
                        break;

                    // Fertigmeldung
                    case 'rdy':
                        break;

                    // Empfang
                    case 'ack':
                        $orderId = $params['id'];
                        
                        try {
                            try {
                                $order = new Yourdelivery_Model_Order($orderId);
                            }
                            catch(Yourdelivery_Exception_Database_Inconsistency $e) {
                                $frame->setData(array('err' => array(
                                    'em' => __("Bestellung nicht gefunden"),
                                )))->send($socket);

                                $this->_log("Cannot create order #%s for terminal #%s", $orderId, $frame->getTermId());
                                break;
                            }

                            if ($params['fg'] == 1) {
                                $this->_log("Terminal #%s confirms reception of order #%s", $frame->getTermId(), $orderId);

                                // remove from queue
                                $this->_queue->deleteFrom($orderId);
                            }
                            else {
                                $this->_log("Terminal #%s declines order #%s", $frame->getTermId(), $orderId);
                            }

                            // Printer has no paper anymore
                            if ($params['po'] == "paperout") {
                                $this->_log("Terminal #%s has no paper", $frame->getTermId());

                                if (!$printer->getPaperout()) {
                                    $printer->setPaperout(1);
                                    $printer->getId() ? $printer->save() : null;

                                    $message = new Yourdelivery_Model_Heyho_Messages();
                                    $message->setMessage(__b("Drucker hat kein Papier mehr"));
                                    $message->addCallbackAvailable("checkprinterpaper/pid/" . $frame->getTermId());
                                    $message->addCallbackAvailable("changerestaurantnotification/pid/" . $frame->getTermId());
                                    $message->addCallbackAvailable("closerestaurantfortoday/pid/" . $frame->getTermId());
                                    $message->save();
                                }
                            }
                        }
                        catch (Zend_Db_Exception $e) {
                            $this->_logDbException($e);
                        }
                        
                        // send pong back
                        if ($printer->getFirmware() >= 270) {
                            $frame->setData("")
                                ->send($socket);
                            $this->_log("Pong to terminal #%s", $frame->getTermId());
                        }
                        
                        break;

                    default:
                        $this->_log("Unknow command '%s' from terminal #%s", $command, $frame->getTermId());
                }
            }
        }
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.06.2012
     * @return Zend_Log
     */
    private function _getLogger($prefix = "printer") {
        
        $loggerFilename = APPLICATION_PATH . "/logs/" . $prefix . "-" . date("Ymd") . ".log";
        
        if (!array_key_exists($prefix, $this->_logger) || ($this->_loggerFilename[$prefix] != $loggerFilename)) {
            $this->_logger[$prefix] = new Zend_Log(new Zend_Log_Writer_Stream(
                $this->_loggerFilename[$prefix] = $loggerFilename
            ));
            
            ini_set('error_log', APPLICATION_PATH . sprintf('/logs/phplog-%s.log', date('Ymd')));
        }
        
        return $this->_logger[$prefix];
    }
    
    /**
     * Really close the connection when the server gone away
     * it will automatically try to reconnect on the next query
     * @author Vincent Priem <priem@lieferando.de>
     * @since 28.12.2011
     */
    private function _logDbException(Zend_Db_Exception $e) {
        
        $this->_db->closeConnection();
        
        $this->_getLogger("db")->crit($error = sprintf("Exception [%s] %s", $e->getCode(), $e->getMessage()));
        fwrite(STDERR, $error .= sprintf("\n%s\n\n", $e->getTraceAsString()));
        
        $this->_dbErrors[] = sprintf("[%s] %s", date("Y-m-d H:i:s"), $error);
        if (count($this->_dbErrors) > 10) {
            $email = new Yourdelivery_Sender_Email();
            $email->addTo("it@lieferando.de")
                  ->setSubject("Topup Critical Error")
                  ->setBodyText(implode("", $this->_dbErrors));
            // cause the email body will be save in the database
            // we have to take care here about exceptions too
            try {
                $email->send('system');
            } catch (Zend_Db_Exception $e) {
            }
                  
            $this->_dbErrors[] = array();
        }
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     * @param string $msg
     */
    private function _log($msg) {
       
        $params = func_get_args();
        if (count($params) > 1) {
            $msg = vsprintf($msg, array_slice($params, 1));
        }
        
        $this->_getLogger()->info($msg);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     * @param int $termId
     * @param resource $socket
     */
    private function _assignSocket($termId, $socket) {
	
        $s = $this->_findSocket($termId);
        if ($s === false) {
            $this->_log("Assign socket #%s to printer #%s", intval($socket), $termId);
            $this->_clients[$termId] = $socket;
        }
        elseif ($s != $socket) {
            $this->_removeSocket($s);
            $this->_log("Assign socket #%s to printer #%s", intval($socket), $termId);
            $this->_clients[$termId] = $socket;
        }
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     * @param int $termId
     * @return resource|boolean
     */
    private function _findSocket($termId) {
	
        if (array_key_exists($termId, $this->_clients)) {
            $socket = $this->_clients[$termId];
            if (is_resource($socket)) {
                return $socket;
            }
        }
        
        return false;
    }
 
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     * @param resource $socket
     * @return int|boolean
     */
    private function _findTerminal($socket) {
	
        $termId = array_search($socket, $this->_clients);
        if ($termId !== false) {
            return $termId;
        }
        
        return false;
    }
    
    /**
     * Use for debugging only
     * @author Vincent Priem <priem@lieferando.de>
     * @since 23.11.2011
     * @param resource $socket
     * @param string $msg
     */
    private function _sendToSocket($socket, $msg) {
	
        $params = func_get_args();
        if (count($params) > 2) {
            $msg = vsprintf($msg, array_slice($params, 2));
        }
        
        socket_send($socket, $msg, strlen($msg), null);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     * @param resource $socket
     */
    private function _removeSocket($socket) {
        
        $termId = array_search($socket, $this->_clients);
        if ($termId !== false) {
            unset($this->_clients[$termId]);
        }
        
        $socketId = intval($socket);
        if (array_key_exists($socketId, $this->_timeout)) {
            unset($this->_timeout[$socketId]);
        }
        
        $key = array_search($socket, $this->_sockets);
        if ($key !== false) {
            unset($this->_sockets[$key]);
        }
        
        $this->_log("Close socket #%s", intval($socket));
        socket_close($socket);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 01.09.2011
     * @param resource $socket
     * @return string
     */
    private function _getSocketError($socket = null) {
	
        if ($socket === null || !is_resource($socket)) {
            return socket_strerror(socket_last_error());
        }
        
        return socket_strerror(socket_last_error($socket));
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.07.2011
     */
    public function __destruct() {
        
        foreach ($this->_sockets as $socket) {
            socket_close($socket);
        }
    }
}
