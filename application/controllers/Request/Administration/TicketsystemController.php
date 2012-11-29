<?php

/**
 * Request triggered by support ticket system to provide a fluent 
 * support interface with multilple supporter support :)
 *
 * @author Matthias Laug <laug@lieferando.de>
 * @since 21.01.2011
 */
class Request_Administration_TicketsystemController extends Default_Controller_RequestAdministrationBase {

    /**
     * 
     * overwrite constructor to implement a new logger
     * @author Matthias Laug <laug@lieferando.de>
     * @since 26.01.2011
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     * @return void
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {

        parent::__construct($request, $response, $invokeArgs);

        //do not use yet
        return;

        //overwrite the existing logger with a new one
        $config = Zend_Registry::get('configuration');
        $file_logger = new Zend_Log_Writer_Stream(
                        sprintf($config->logging->ticket, date('d-m-Y', time()))
        );
        $logger = new Yourdelivery_Log($file_logger);

        $this->_logger = $logger;
    }

    /**
     * a cronjob which is called every 10 seconds to check for a timeout and 
     * check for new tickets 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 23.01.2011
     */
    public function cronjobAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        
        $ticketQueue = new Yourdelivery_Model_Heyho_TicketQueue();
                
        $currentTicketId = (integer) $this->getRequest()->getParam('current', null);
        $currentMessageId = (integer) $this->getRequest()->getParam('message', null);
        $filter = $this->getRequest()->getParam('filter', null);
     
        $heyhoMessages = new Yourdelivery_Model_Heyho_Messages();
        $messages = $heyhoMessages->getMessages($currentMessageId);
        
        /**
         * start the loop with the count of messages available
         * so that we only get 7 - messages tickets
         * @todo need to priorize those as well
         */
        $i = count($messages);
        
        $tickets = $ticketQueue->getTickets($currentTicketId, $i, $filter);
        $stats = $ticketQueue->getStats();
        $timeout = $ticketQueue->getTimeout($currentTicketId);
        
      
        $this->view->tickets = $tickets;
        $this->view->messages = $messages;
        $html = $this->view->fetch('request/administration/ticketsystem/cronjob.htm');        
        echo json_encode(array(
            'html' => $html,
            'timeout' => (integer) $timeout,
            'stats' => $stats
        ));
    }

    /**
     * pull any ticket and reserve for this current supporter 
     * @author Matthias Laug <laug@lieferando.de>,daniel
     * @since 24.01.2011
     */
    public function pullAction() {
        $ticketId = $this->getRequest()->getParam('id', null);
        if ($ticketId === null) {
            $this->getResponse()->setHttpResponseCode(404);
            $this->logger->info(sprintf("TicketSystem: Could not find ticket %d by %s (%d)", $ticketId, $this->session_admin->admin->getName(), $this->session_admin->admin->getId()));
            return;
        }

        try {
            $ticket = new Yourdelivery_Model_Order_Ticket($ticketId);

            if (!$this->session_admin->admin) {
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                $this->getResponse()->setHttpResponseCode(404);
                $this->logger->info(sprintf("TicketSystem: Lost admin session while loading ticket %d", $ticketId));
                return;
            }

            if ($ticket->lock($this->session_admin->admin->getId()) === false) {
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                $this->getResponse()->setHttpResponseCode(409);
                $this->logger->info(sprintf("TicketSystem: Conflict while loading ticket %d by %s (%d)", $ticketId, $this->session_admin->admin->getName(), $this->session_admin->admin->getId()));
                return;
            }

            $this->logger->info(sprintf("TicketSystem: Succesfully loading ticket %d by %s (%d)", $ticketId, $this->session_admin->admin->getName(), $this->session_admin->admin->getId()));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            $this->getResponse()->setHttpResponseCode(404);
            return;
        }

        $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::ORDER_PULL, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $ticket->getId());

        $this->view->ticket = $ticket;
        
      
       $this->view->openingTimes = $this->view->formatOpeningsMerged($ticket->getService()->getOpening()->getIntervalOfDay(strtotime('today')));
       $this->view->serviceIsOpen = $ticket->getService()->getOpening()->isOpen();
        
        
    }

    /**
     * push back a ticket and open it again for another supporter
     * @author Matthias Laug <laug@lieferando.de>, daniel
     * @since 24.01.2011
     */
    public function pushAction() {

        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $ticketId = $this->getRequest()->getParam('id');
        if ($ticketId === null) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            $this->getResponse()->setHttpResponseCode(404);

            $this->logger->info(sprintf("TicketSystem: Could not find ticket %d by %s (%d)", $ticketId, $this->session_admin->admin->getName(), $this->session_admin->admin->getId()));
            return;
        }

        try {
            $ticket = new Yourdelivery_Model_Order_Ticket($ticketId);
            $ticket->release();
            /**
                * @author Daniel Hahn <hahn@lieferando.de>
                * track action
                * 1 - Ticket gepusht
                * 2 - Ticket Timeout
                */
            $track = (integer) $this->getRequest()->getParam('track');
            if ($track == 1) {
                $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::ORDER_PUSH, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $ticket->getId());
            } elseif ($track == 2) {
                $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::TICKET_TIMEOUT, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $ticket->getId());
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            $this->getResponse()->setHttpResponseCode(404);
            return;
        }
    }

    /**
     * create a support ticket based on given key
     * support.yourdelivery.de
     * @author Matthias Laug <laug@lieferando.de>, daniel
     * @since 25.01.2011
     */
    public function ticketAction() {

        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $ticketId = (integer) $request->getParam('id');
            $action = $request->getParam('command');
            $body = $request->getParam('body', "");

            if (!$ticketId) {
                $this->getResponse()->setHttpResponseCode(404);
                $this->logger->info(sprintf("TicketSystem: Could not find ticket %d by %s (%d)", $ticketId, $this->session_admin->admin->getName(), $this->session_admin->admin->getId()));
                return;
            }

            try {
                $ticket = new Yourdelivery_Model_Order_Ticket($ticketId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->getResponse()->setHttpResponseCode(404);
                $this->logger->info(sprintf("TicketSystem: Could not find ticket %d by %s (%d)", $ticketId, $this->session_admin->admin->getName(), $this->session_admin->admin->getId()));
                return;
            }

            $body = "#" . $ticket->getService()->getId() . " " . $ticket->getService()->getName() . ($body ? LF . $body : "");
            switch ($action) {
                case 'changecard':
                    // Using config-based locale during composing and sending e-mail
                    $this->_restoreLocale();
                    Yourdelivery_Sender_Email::osTicket("backoffice", __b("DL anrufen wegen Kartenänderung"), $body);
                    $this->_overrideLocale();

                    $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::TICKET_CHANGECARD, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $ticket->getId());
                    $this->getResponse()->setHttpResponseCode(201);
                    return;

                case 'location':
                    // Using config-based locale during composing and sending e-mail
                    $this->_restoreLocale();
                    Yourdelivery_Sender_Email::osTicket("backoffice", __b("DL anrufen wegen Liefergebieten"), $body);
                    $this->_overrideLocale();

                    $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::TICKET_LOCATION, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $ticket->getId());
                    $this->getResponse()->setHttpResponseCode(201);
                    return;

                case 'bill':
                    // Using config-based locale during composing and sending e-mail
                    $this->_restoreLocale();
                    Yourdelivery_Sender_Email::osTicket("buchhaltung", __b("DL anrufen wegen Abrechnung"), $body);
                    $this->_overrideLocale();

                    $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::TICKET_BILL, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $ticket->getId());
                    $this->getResponse()->setHttpResponseCode(201);
                    return;

                case 'payment':
                    // Using config-based locale during composing and sending e-mail
                    $this->_restoreLocale();
                    Yourdelivery_Sender_Email::osTicket("backofficeHead", __b("DL anrufen wegen bargeldloser Zahlung"), $body);
                    $this->_overrideLocale();

                    $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::TICKET_PAYMENT, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $ticket->getId());
                    $this->getResponse()->setHttpResponseCode(201);
                    return;

                default:
                    $this->getResponse()->setHttpResponseCode(405);
                    $this->logger->info(sprintf("TicketSystem: Could not find action %s for %d by %s (%d)", $action, $ticketId, $this->session_admin->admin->getName(), $this->session_admin->admin->getId()));
                    return;
            }
        }
    }
    
    /**
     * Filter Duplicates caused by left join with restaurant_notepad_tickets, because its faster in php
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 29.09.2011
     */
    protected function filterTickets($tickets) {
        $return = array();

        foreach ($tickets as $key => $ticket) {

            if (!empty($return[$ticket['id']])) {
                if ($ticket['notepad_created'] > $return[$ticket['id']]['notepad_created']) {
                    $return[$ticket['id']] = $ticket;
                }
            } else {
                $return[$ticket['id']] = $ticket;
            }
        }

        uasort($return, function($a, $b) {
                    return $a['prio'] < $b['prio'];
                });


        return $return;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 28.09.2011
     * @reviewer vpriem, 06.10.2011
     */
    public function commentAction() {

        $this->_disableView();

        $request = $this->getRequest();
        $id = $request->getParam('id');
        $allwaysCall = (integer) $request->getParam('allwayscall');
        $comment = (string) $request->getParam('comment');

        $this->logger->info(sprintf("TicketSystem: Service %d was commented by %s (%d)", $id, $this->session_admin->admin->getName(), $this->session_admin->admin->getId()));

        $serviceComment = new Yourdelivery_Model_DbTable_Restaurant_Notepad_Ticket();
        $serviceComment->insert(array(
            'restaurantId' => $id,
            'comment' => $comment,
            'adminId' => $this->session_admin->admin->getId(),
            'allwaysCall' => $allwaysCall
        ));

        $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::SERVICE_COMMENT, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_SERVICE, $id);
    }

}
