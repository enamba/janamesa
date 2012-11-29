<?php

/**
 * Use for ebanking transactions
 * @author vpriem
 * @since 19.11.2010
 */
require_once(APPLICATION_PATH . '/controllers/Payment/Abstract.php');

class Payment_EbankingController extends Payment_Abstract {

    /**
     * Callback  by ebanking to confirm payment and set order status appropriatly, finish transaction
     * 1. check if response from ebanking is okay
     * 1.1 if okay send out messages and fax
     * 2. check if NCD is used
     * 2.1 if NCD is used, set status Storno, send email or sms to user, mark ebanking Transaction for Refund
     * 
     * @author vpriem
     * @since 04.10.2011
     */
    public function notifyAction() {

        $this->_disableView();

        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->logger->err('Ebanking: called with get method');
            return;
        }

        $post = $request->getPost();
        $orderId = (integer) $post['user_variable_0'];
        $hash = $post['hash'];
        $success = (integer) $post['security_criteria'];
        
        // get order
        if (!$orderId) {
            $this->logger->err('Ebanking: called without order id');
            return;
        }
        try {
            $order = new Yourdelivery_Model_Order($orderId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err('Ebanking: cannot find order #' . $orderId);
            return;
        }

        // check state
        if ($order->getState() != Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED) {
            $this->logger->warn(sprintf('Ebanking: ignore order #%s cause is not in state -5, but %s', $order->getId(), $order->getState()));
            return;
        }

        // check
        $eBanking = new Yourdelivery_Payment_Ebanking();
        if ($eBanking->getHash($post) != $hash) {            
            $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAIL_EBANKING, $orderId); 
            $this->logger->warn($msg->getRawMessage());
            $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg);
            return;
        }

        // success
        if ($success) {            
            // new customer discount check
            $discount = $order->getDiscount();
            if (Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($eBanking->getPayerId(), $order)) {

                $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_DISCOUNT_FAIL_EBANKING, $orderId, $discount->getCode(), $eBanking->getPayerId());                 
                $this->logger->warn($msg->getRawMessage());
                $order->setStatus(Yourdelivery_Model_Order_Abstract::STORNO, $msg);
                Yourdelivery_Helpers_Payment::refundEbanking($order, $this->logger, $messages = array(), __("Storniert von system da kein Neukunde"));
                
                // important to call absTotal before removing discount !!!
                $absTotal = $order->getAbsTotal();
                
                $order->removeDiscount(); // remove after refund

                $customer = $order->getCustomer();
                $phoneNumber = $order->getLocation()->getTel();
                if ($phoneNumber) {
                    $phoneNumber = Default_Helpers_Normalize::telephone($phoneNumber);
                    if (Default_Helpers_Phone::isMobile($phoneNumber)) {                      
                        $sms = new Yourdelivery_Sender_Sms_Template('storno_nc');
                        $sms->assign('order', $order);
                        $sms->assign('absTotal', $absTotal);
                        if ($sms->send($phoneNumber)) {
                            $this->logger->info(sprintf('Ebanking: send storno sms to %s for order #%s with refund amount %s', $phoneNumber, $order->getId(), intToPrice($absTotal, 2)));
                        } else {
                            $this->logger->err(sprintf('Ebanking: failed to send storno sms to %s for order #%s with refund amount %s', $phoneNumber, $order->getId(), intToPrice($absTotal, 2)));
                        }
                    }
                }

                $this->logger->info(sprintf('Ebanking: send storno email to %s for order #%s with refund amount %s', $customer->getEmail(), $order->getId(), intToPrice($absTotal, 2)));

                $email = new Yourdelivery_Sender_Email_Template("storno_nc.txt");
                $email->setSubject(__('Wichtige Information zu Deiner Bestellung vom %s: Storno', date(__("d.m.Y"), $order->getTime())))
                      ->addTo($customer->getEmail())
                      ->assign('order', $order)
                      ->assign('absTotal', $absTotal)
                      ->send();

                if ($this->config->domain->base != "lieferando.de") {
                    $this->logger->info(sprintf('Ebanking: create heyho message for oder #%s', $order->getId()));
                    
                    $message = new Yourdelivery_Model_Heyho_Messages();
                    $message->setMessage("Kein Neukunde, Bank Konto wurde bereits verwendet. Kunde wurde per email benachrichtigt");
                    $message->addCallbackAvailable("showorder/oid/" . $order->getId());
                    $message->save();
                }
                
                return;
            }
            $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_SUCCESS_EBANKING, $orderId);                 
            $this->logger->info($msg->getRawMessage());
            $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg);
            $order->finalizeOrderAfterPayment('ebanking');
            return;
        }
        $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAIL_EBANKING_NOK, $orderId);                        
        $this->logger->warn($msg->getRawMessage());
        $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg);
    }

    /**
     * Redirected from direct ebanking, no confirmation of payment here, just redirect to success
     * 
     * @author vpriem
     * @since 19.11.2010
     */
    public function finishAction() {

        // get parameters
        $request = $this->getRequest();
        $success = $request->getParam('success');

        // get order
        $kind = "priv";
        try {
            $order = $this->_getCurrentOrder();
            $kind = $order->getKind();
        } catch (Yourdelivery_Exception_NoPaymentData $e) {
            
        }

        // success, we got it
        if ($success) {
            if ($kind == 'comp') {
                return $this->_redirect('/order_company/success');
            }
            return $this->_redirect('/order_private/success');
        }

        $this->error(__("Die Überweisung ist fehlgeschlagen."));
        return $this->_redirect('/order_basis/payment');
    }

}
