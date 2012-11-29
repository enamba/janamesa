<?php

/**
 * @author mlaug
 */
class Request_Administration_Ticketsystem_MessageController extends Default_Controller_RequestAdministrationBase {
    
    /**
     * @var Yourdelivery_Model_Heyho_Messages
     */
    protected $_message = null;
    
    /**
     * @var Yourdelivery_Model_Servicetype_Restaurant
     */
    protected $_restaurant = null;

    /**
     * @var Yourdelivery_Model_Printer_Topup
     */
    protected $_printer = null;

    /**
     * @var Yourdelivery_Model_Order
     */
    protected $_order = null;
    
    /**
     * @var Yourdelivery_Model_Rabatt_Code 
     */
    protected $_discount = null;

    /**
     * Get the restaurant
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     * @return Yourdelivery_Model_Servicetype_Restaurant
     */
    public function preDispatch() {
        
        parent::preDispatch();
        
        $request = $this->getRequest();
        if ($request->getActionName() == "error") {
            return;
        }
        
        $message = $this->_getMessage();
        if (!$message instanceof Yourdelivery_Model_Heyho_Messages) {
            $this->getResponse()
                 ->setHttpResponseCode(404);
            return $this->_forward("error");
        }
        
        if ($message->isLocked($this->session_admin->admin->getId())) {
            $this->getResponse()
                        ->setHttpResponseCode(409);
            return $this->_forward("error");
        }
        
        if ($request->isPost()) {
            $this->_disableView();
            // update message state
            $message->setState(1);
            $message->addCallbackTriggered($request->getActionName());
            $message->setUpdated(date(DATETIME_DB));
            $message->save();
            return;
        }
        
        $this->view->message = $message;
    }
    
    /**
     * Get the current heyho message
     * 
     * @author mlaug
     * @since 17.11.2011
     * @return Yourdelivery_Model_Heyho_Messages
     */
    private function _getMessage() {

        if ($this->_message !== null) {
            return $this->_message;
        }

        $request = $this->getRequest();
        $mid = (integer) $request->getParam('mid');
        if (!$mid) {
            return;
        }
        
        try {
            $message = new Yourdelivery_Model_Heyho_Messages($mid);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        return $this->_message = $message;
    }
    
    /**
     * Get the printer
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     * @return Yourdelivery_Model_Printer_Topup
     */
    private function _getPrinter() {

        if ($this->_printer !== null) {
            return $this->_printer;
        }

        $request = $this->getRequest();
        $pid = (integer) $request->getParam('pid');
        if (!$pid) {
            return;
        }
        
        try {
            $printer = Yourdelivery_Model_Printer_Abstract::factory ($pid);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        return $this->_printer = $printer;
    }
    
    /**
     * Get the order
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 21.02.2012
     * @return Yourdelivery_Model_Order
     */
    private function _getOrder() {

        if ($this->_order !== null) {
            return $this->_order;
        }

        $request = $this->getRequest();
        $oid = (integer) $request->getParam('oid');
        if (!$oid) {
            return;
        }
        
        try {
            $order = new Yourdelivery_Model_Order($oid);
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        return $this->_order = $order;
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.03.2012
     * @return Yourdelivery_Model_Rabatt_Code 
     */
    public function _getDiscount(){
        if ( $this->_discount !== null ){
            return $this->_discount;
        }
        $request = $this->getRequest();
        $did = (integer) $request->getParam('did');
        if ( !$did ){
            return;
        }
        
        try{
            $discount = new Yourdelivery_Model_Rabatt_Code(null, $did);
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            return;
        }
        
        return $this->_discount = $discount;
    }
    
    /**
     * Log whats hapen
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     * @param string $msg
     * @return void
     */
    private function _log($msg) {
        
        $params = func_get_args();
        if (count($params) > 1) {
            $msg = vsprintf($msg, array_slice($params, 1));
        }
        
        $this->logger->info(sprintf("TicketSystem: %s (%d) %s", $this->session_admin->admin->getName(), $this->session_admin->admin->getId(), $msg));
    }
    
    /**
     * Basis template, to fill in the callbacks
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     */
    public function indexAction() {
        
    }

    /**
     * EXAMPLE: you need to create a view and an action based on the callback
     * name. This action makes the callback "samson" available, which can be 
     * stored comma seperated in the field callbackAvailable
     * @author mlaug
     * @since 17.11.2011
     */
    public function errorAction() {
        
        $this->_disableView();
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     */
    public function pushAction() {
        
        $this->_disableView();
        
        $message = $this->_getMessage();
        $message->release();
    }
    
    /**
     * Show order
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     */
    public function closeAction() {
        
        $this->_disableView();
        
        $message = $this->_getMessage();
        $message->close();
    }
    
    /**
     * Show order
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     */
    public function showorderAction() {
        
        $order = $this->_getOrder();
        if (!$order instanceof Yourdelivery_Model_Order) {
            $this->getResponse()
                 ->setHttpResponseCode(404);
            return $this->_forward("error");
        }
        
        $this->view->order = $order;
    }
    
    /**
     * Show discount
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.03.2012 
     */
    public function showdiscountAction(){
        
        $discount = $this->_getDiscount();
        if (!$discount instanceof Yourdelivery_Model_Rabatt_Code) {
            $this->getResponse()
                 ->setHttpResponseCode(404);
            return $this->_forward("error");
        }
        
        $this->showorderAction();
        
        $this->view->discount = $discount;
    }
    
    /**
     * Close restaurant for today
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     */
    public function closerestaurantfortodayAction() {
        
        $printer = $this->_getPrinter();
        if (!$printer instanceof Yourdelivery_Model_Printer_Abstract) {
            $this->getResponse()
                 ->setHttpResponseCode(404);
            return $this->_forward("error");
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            
            $affected = 0;
            if (is_array($post['restaurants'])) {
                foreach ($post['restaurants'] as $rid) {
                    try {
                        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($rid);
                        $opening = new Yourdelivery_Model_Servicetype_OpeningsSpecial();
                        $opening->setData(array(
                            'restaurantId'=> $restaurant->getId(),
                            'specialDate'=> date(DATE_DB),
                            'closed'=> 1,
                        ));
                        $opening->save();
                        
                        $this->_log(__b("close restaurant #%s for today"), $rid);
                        $affected++;
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    }
                }
            }
            
            if ($affected) {
                return $this->_json(array(
                    'success' => __b("Restaurant wurde erfolgreich für heute geschlossen"),
                ));
            }
            
            return $this->_json(array(
                'error' => __b("Es wurde nichts geändert"),
            ));
        }
        
        $this->view->printer = $printer;
    }
    
    /**
     * Close restaurant for today
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     */
    public function changerestaurantnotificationAction() {
        
        $printer = $this->_getPrinter();
        if (!$printer instanceof Yourdelivery_Model_Printer_Abstract) {
            $this->getResponse()
                 ->setHttpResponseCode(404);
            return $this->_forward("error");
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            
            $affected = 0;
            if (is_array($post['restaurants'])) {
                foreach ($post['restaurants'] as $rid => $notify) {
                    try {
                        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($rid);
                        
                        $oldNotify = $restaurant->getNotify();
                        if ($notify != $oldNotify) {
                            $restaurant->setNotify($notify);
                            $restaurant->save();

                            $this->_log(__b("change restaurant #%s notify from %s to %s"), $rid, $oldNotify, $notify);
                            $affected++;
                        }
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    }
                }
            }
            
            if ($affected) {
                return $this->_json(array(
                    'success' => __b("Versand wurde erfolgreich geändert"),
                ));
            }
            
            return $this->_json(array(
                'error' => __b("Es wurde nichts geändert"),
            ));
        }
        
        $this->view->printer = $printer;
    }
    
    /**
     * Printer is online
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     */
    public function checkprinterAction() {
        
        $printer = $this->_getPrinter();
        if (!$printer instanceof Yourdelivery_Model_Printer_Abstract) {
            $this->getResponse()
                 ->setHttpResponseCode(404);
            return $this->_forward("error");
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            
            $this->_log(__b("check printer #%s status, %s"), $printer->getId(), ($printer->isOnline() ? "online" : "offline"));
            
            if ($printer->isOnline()) {
                return $this->_json(array(
                    'success' => __b("Drucker ist online"),
                ));
            }
            
            return $this->_json(array(
                'error' => __b("Drucker ist immer noch offline"),
            ));
        }
        
        $this->view->printer = $printer;
    }
    
    /**
     * Printer has no papier
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 01.12.2011
     */
    public function checkprinterpaperAction() {
        
        $printer = $this->_getPrinter();
        if (!$printer instanceof Yourdelivery_Model_Printer_Abstract) {
            $this->getResponse()
                 ->setHttpResponseCode(404);
            return $this->_forward("error");
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            
            $this->_log('check printer #%s paper, %s', $printer->getId(), ($printer->getPaperout() ? "no" : "ok"));
            
            if (!$printer->getPaperout()) {
                return $this->_json(array(
                    'success' => __b("Drucker hat wieder papier"),
                ));
            }
            
            return $this->_json(array(
                'error' => __b("Drucker hat immer noch kein Papier"),
            ));
        }
        
        $this->view->printer = $printer;
    }
}
