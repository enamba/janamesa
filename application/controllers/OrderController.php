<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OrderController
 *
 * @author mlaug
 */
class OrderController extends Default_Controller_Base {

    public function startAction() {
        $this->_redirect('/order_private/start');
    }

    /**
     * get "bestellzettel" from order and display in a own window
     * alot of checks, wether this user is allowed to view
     * or not
     * @return mixed
     */
    public function bestellzettelAction() {
        
        $allowed = false;
        
        $request = $this->getRequest();
        $orderId = $request->getParam('order');
        $hash = $request->getParam('hash');
        
        // this request open a new window, we want to close that window now
        if ($orderId === null && $hash === null) {
            return $this->_redirect('/error/notfound');
        }

        if ($orderId !== null) {
            try {
                $order = new Yourdelivery_Model_Order((integer) $orderId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return $this->_redirect('/error/notfound');
            }
        } else {
            $order = Yourdelivery_Model_Order::createFromHash(htmlentities($hash));
            if (!is_object($order)) {
                return $this->_redirect('/error/notfound');
            }
        }

        $backend_session = new Zend_Session_Namespace('Administration');
        if (!is_object($backend_session->admin)) {

            $customer = $this->getCustomer();
            if (is_object($this->session->currentRestaurant)) {
                if ($this->session->currentRestaurant->getId() == $order->getRestaurantId()) {
                    $allowed = true;
                }
            }
            elseif ($this->session->partnerRestaurantId !== null) {
                if ($this->session->partnerRestaurantId == $order->getRestaurantId()) {
                    $allowed = true;
                }
            }
            elseif ($order->getCustomer()->getEmail() == $this->getCustomer()->getEmail()) {
                $allowed = true;
            }
            elseif ($order->getCustomer()->getId() == $customer->getId()) {
                $allowed = true;
            }
            elseif ($order->getKind() == "comp" && $customer->isEmployee() ) {
                if (is_null($customer->getCompany())) {
                    $this->logger->err(sprintf('Could not find associated company for customer #%d', $customer->getId()));
                }
                else if (is_null($order->getCustomer()->getCompany())) {
                    $this->logger->err(sprintf('Could not find associated company for customer of order #%d', $order->getId()));
                }
                else {
                    if ($customer->getCompany()->getId() == $order->getCustomer()->getCompany()->getId()) {
                        if ($customer->isCompanyAdmin()) {
                            $allowed = true;
                        }
                        else {
                            foreach ($order->getCompanyGroupMembers() as $member) {
                                if (is_object($member[0]) && $member[0]->getId() == $customer->getId()) {
                                    $allowed = true;
                                }
                            }
                        }
                    }                    
                }
            }
        } else {
            $allowed = true;
        }       
        
        if ($allowed) {
            $this->view->order = $order;
             if ($this->session->partnerRestaurantId == $order->getRestaurantId()) {
                    $this->view->isServiceView = true;
             }
        }
        else {
            $this->_disableView();
        }
    }

}
