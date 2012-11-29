<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MessageQueue
 *
 * @author daniel
 */
class Yourdelivery_Model_Heyho_TicketQueue extends Default_Model_Base {

    /**
     *
     * @var array
     */
    protected $ticketsTotal = array();

    /**
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 07.12.2011
     * @param type $currentTicketId 
     * @return array
     */
    public function getTickets($currentTicketId, $messageCount = 0, $filter= null) {

        $this->ticketsTotal = array();
        $tickets = array();

        try {
            $orders = $this->getTable()->getTickets();
        } catch (Zend_Db_Exception $e) {
            //fallback to old query
            $orders = Yourdelivery_Model_DbTable_Order::getOpenOrdersByPrio();
        }

        $orders = $this->filterTickets($orders);

        foreach ($orders as $ticket_raw) {

            $id = (integer) $ticket_raw['orderId'];
            $prio = (integer) $ticket_raw['prio'];


            //do not show our current one
            if ($currentTicketId > 0 && $id == $currentTicketId) {
                continue;
            }


            try {
                //load lightweight order
                $ticket = new Yourdelivery_Model_Order_Ticket($id, false);

                //hack for allwaysCall
                if ($ticket_raw['allwaysCall'] == 1) {
                    $ticket->setTimediff(11);
                } else {
                    $ticket->setTimediff($ticket_raw['timediff']);
                }

                $ticket->setPrio($prio);
                $ticket->setNotifyPaid($ticket_raw['notifyPaid']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->adminError('Could not find order by given id ' . $ticket_raw['orderId']);
                continue;
            }

            //do not take those, who are currently in use
            if ($ticket->getSupporter() > 0) {
                continue;
            }
            
            
            

            //Filter out tickets that are already closed
            if (in_array($ticket->getState(), array(-22, -4, -3, -15, -1, 0, 1))) {

                if ($messageCount < 11) {
                    $ticket->getCard();
                    //if filter is set, check with filter
                    if(!empty($filter)  && method_exists($this, "_is".$filter) && $this->{'_is'.$filter}($ticket)) {                    
                        $tickets[] = $ticket;
                        $messageCount++;
                    }elseif(empty($filter) || !method_exists($this, "_is".$filter)) {
                        $tickets[] = $ticket;
                        $messageCount++;
                    }                    
                }
                $this->ticketsTotal[] = $ticket;
            }
        }

        return $tickets;
    }

    /**
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 07.12.2011     
     * @return array
     */
    public function getStats() {

        $stats = array();
        $stats['All'] = 0;
        $stats['Premium'] = 0;
        $stats['Fraud'] = 0;
        $stats['Error'] = 0;
        $stats['NotAffirmed'] = 0;
        $stats['NotifyPaid'] = 0;

        if (count($this->ticketsTotal) == 0) {
            return $stats;
        }

        foreach ($this->ticketsTotal as $ticket) {
            
            $stats['All'] += 1;
            
            if ($this->_isFraud($ticket)) {
                $stats['Fraud'] +=1;
                continue;
            }
            if ($this->_isError($ticket)) {
                $stats['Error'] +=1;
                continue;
            }           
            if ($this->_isPremium($ticket)) {
                $stats['Premium'] += 1;
                continue;
            }
            if ($this->_isNotAffirmed($ticket)) {
                $stats['NotAffirmed'] += 1;
                continue;
            }
            if ($this->_isNotifyPaid($ticket)) {
                $stats['NotifyPaid'] += 1;
                continue;
            }
        }

        return $stats;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 07.12.2011
     * @param int $currentTicketId
     * @return int 
     */
    public function getTimeout($currentTicketId) {
        $timeout = 0;
        if ($currentTicketId > 0) {
            try {
                $currentTicket = new Yourdelivery_Model_Order_Ticket($currentTicketId, false);
                $pulledOn = strtotime($currentTicket->getPulledOn());
                $timeout = time() - $pulledOn;
                if ($timeout > 300) {
                    $this->logger->adminWarn(sprintf('Releasing ticket %d due to timeout', $currentTicketId));
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
        return $timeout;
    }

    /**
     * Get table
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 07.12.2011
     * @return Yourdelivery_Model_DbTable_Heyho_Messages
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Heyho_TicketQueue();
        }

        return $this->_table;
    }

    /**
     * Filter Duplicates caused by left join with restaurant_notepad_tickets, because its faster in php
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param array $tickets
     * @since 29.09.2011
     */
    protected function filterTickets($tickets) {
        $return = array();

        foreach ($tickets as $key => $ticket) {

            if (isset($return[$ticket['orderId']])) {
                if ($ticket['notepad_created'] > $return[$ticket['id']]['notepad_created']) {
                    $return[$ticket['orderId']] = $ticket;
                }
            } else {
                $return[$ticket['orderId']] = $ticket;
            }
        }

        uasort($return, function($a, $b) {
                    return $a['prio'] < $b['prio'];
                });


        return $return;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.06.2012
     * @param type $ticket
     * @return boolean 
     */
    public function getTicketsTotal() {
        return $this->ticketsTotal;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.06.2012
     * @param type $ticket
     * @return boolean 
     */
    protected function _isPremium($ticket) {
        if ($ticket->getService()->isPremium() && $ticket->getMode() == 'rest' && in_array($ticket->getState(), array(-1, -3, -4, 0, 1))) {
            return true;
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.06.2012
     * @param type $ticket
     * @return boolean 
     */
    protected function _isFraud($ticket) {
        if (!$ticket->getService()->isPremium() && ($ticket->getState() == -3)) {
            return true;
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.06.2012
     * @param type $ticket
     * @return boolean 
     */
    protected function _isError($ticket) {
        if (!$ticket->getService()->isPremium() && ($ticket->getState() == -1 || $ticket->getState() == -22)) {
            return true;
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.06.2012
     * @param type $ticket
     * @return boolean 
     */
    protected function _isNotAffirmed($ticket) {
        if (!$ticket->getService()->isPremium() && ($ticket->getTimediff() > 10 && $ticket->getState() == 0 || $ticket->getState() == -15)) {
            return true;
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.06.2012
     * @param type $ticket
     * @return boolean 
     */
    protected function _isNotifyPaid($ticket) {
        if (!$ticket->getService()->isPremium() && ($ticket->getNotifyPaid() > 0 && $ticket->getMode() == 'rest' && in_array($ticket->getState(), array(-1, -3, -4, 0, 1)))) {
            return true;
        }

        return false;
    }

}

