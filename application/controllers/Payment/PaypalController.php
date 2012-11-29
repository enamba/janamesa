<?php

/**
 * Use for paypal transactions
 * @author Vincent Priem <priem@lieferando.de>
 * @since 17.11.2010
 */
require_once(APPLICATION_PATH . '/controllers/Payment/Abstract.php');

class Payment_PaypalController extends Payment_Abstract {

    /**
     * Call back by paypal IPN for giropay
     * 1. called by  paypal to verify Payment, but also for every other action
     * 2. filter is message belongs to order, set appropriate status, no redirect because it is called asynchronous
     * @author Vincent Priem <priem@lieferando.de>
     * @since 23.03.2010
     */
    public function notifyAction() {

        // no view
        $this->_disableView();

        // is it post
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->logger->err('PayPal IPN: called with get method');
            return;
        }
        $post = $request->getPost();

        // get order id
        $orderId = (integer) $request->getParam("id");
        if (!$orderId) {
            $this->logger->err('PayPal IPN: called without order id');
            return;
        }

        // verify
        $paypal = new Yourdelivery_Payment_Paypal();
        $resp = $paypal->notifyValidate($post);

        // logging
        $dbTable = new Yourdelivery_Model_DbTable_Paypal_Notifications();
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'params' => http_build_query($post),
            'response' => $resp,
        ))->save();

        if (empty($resp) || $resp == 'NO DATA' || $resp == 'SOCKET UNAVAILABLE') {
            $this->logger->err(sprintf('PayPal IPN: could not verify request for order #%s', $orderId));
        } else {
            // all right
            $this->logger->info(sprintf('PayPal IPN: receive response "%s" for order #%s', $resp, $orderId));
        }

        if (strpos($resp, "VERIFIED") !== false) {
            $this->logger->info(sprintf('PayPal IPN: receive status "%s" for order #%s', $post['payment_status'], $orderId));

            if (IS_PRODUCTION && $post['receiver_email'] != "gerber@yourdelivery.de") {
                $this->logger->err(sprintf("PayPal IPN: wrong receiver email: %s", $post['receiver_email']));
            }

            // finalize order if completed
            if ($post['payment_status'] == "Completed") {
                try {
                    $order = new Yourdelivery_Model_Order($orderId);
                    // if the oder in prepayment state or fake
                    // and the user was redirected to giropay before
                    if ($order->getState() == Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED || $order->getState() == Yourdelivery_Model_Order_Abstract::FAKE) {
                        $dbTable = new Yourdelivery_Model_DbTable_Paypal_Transactions();

                        $rows = $dbTable->getByOrder($orderId);
                        foreach ($rows as $row) {
                            $resp = $row->getResponse();
                            if (isset($resp['REDIRECTREQUIRED']) && $resp['REDIRECTREQUIRED'] == "true") {
                                $this->logger->info('PayPal IPN: finalize order #' . $orderId);

                                $order->setStatus($order->getState(), new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_SUCCESS_PAYPAL_IPN)
                                );
                                $order->finalizeOrderAfterPayment('paypal', false, $order->getState());
                                return;
                            }
                        }

                        $this->logger->info('PayPal IPN: ignore order #' . $orderId);
                        return;
                    }

                    $this->logger->warn(sprintf('PayPal IPN: ignore order #%s cause is not in state -5, but %s', $order->getId(), $order->getState()));
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->logger->err('PayPal IPN: cannot find order #' . $orderId);
                }
            }
        } else {
            $order = new Yourdelivery_Model_Order($orderId);
            $order->setStatus($order->getState(), new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAIL_PAYPAL_IPN)
            );
        }
    }

    /**
     * Call back by paypal
     * finish the transaction for standard cases
     *
     * 1. check if New Customer Discount is used
     * 1.1 if  NCD is used and discount is not allowed redirect to payment
     * 2. finalize Paypal Transaction
     * 2.1 if paypal  response is giropay, another redirect for user -> callback notifyAction is used
     * 2.2 payment failure, redirect to payment
     * 3. check through Paypal Fraud Filter
     * 3.1 order is fake or blacklisted, redirect to success, send message to user if fake_storno
     * 3.2 order Is Ok, redirect to success
     *
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.11.2010
     */
    public function finishAction() {

        $this->_disableView();

        // get parameters
        $request = $this->getRequest();
        $token = $request->getParam('token');
        $payerId = $request->getParam('PayerID');

        // this was not call back by paypal
        if ($token === null || $payerId === null) {
            $this->logger->err('Paypal: called directly');
            return $this->_redirect('/');
        }

        $paypal = new Yourdelivery_Payment_Paypal();

        // get order id
        // if order id ist 0, try to find it into
        // the transactions table
        $orderId = $paypal->getOrderIdFromToken($token);
        if (!$orderId) {
            $this->logger->err('Paypal: called with invalid token: ' . $token);
            return $this->_redirect('/#pp-session-invalid');
        }

        // get order
        $order = $this->_getCurrentOrder($orderId);

        //check if we have been here before
        if ($order->getStatus() >= 0) {
            $this->logger->err('Paypal: tried to pay again for alreaday paid order: #' . $orderId);
            return $this->_redirect('/#pp-session-invalid');
        }

        // add paypal details
        $details = false;
        try {
            $details = $paypal->getExpressCheckoutDetails($order, $token);
        } catch (Yourdelivery_Payment_Paypal_Exception $e) {
        }
        if ($details) {
            $order->setStatus($order->getStatus(), new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_PAYPAL_PAYER_DETAILS, $details['FIRSTNAME'] , $details['LASTNAME'], $details['EMAIL'] , $details['ADDRESSSTATUS'] , $details['PAYERSTATUS']));
        }

        // new customer discount check
        $discount = $order->getDiscount();
        if (Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order)) {
            $payerId = $payerId === null ? '' : $payerId;
            $this->_markDiscountAsNotUsable($order, $paypal);
            $this->session->newCustomerDiscountError = 1;
            $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAIL_PAYPAL_NC_DISCOUNT, $discount->getCode(), $payerId));
            $this->logger->warn(sprintf('Paypal: payment with discount not possible, account %s already used for order #%s', $payerId, $orderId));
            return $this->_redirect('/order_basis/payment');
        }

        if ($order->hasNewCustomerDiscount() && $details['PAYERSTATUS'] == 'unverified') {
            $this->_markDiscountAsNotUsable($order, $paypal);
            $this->session->newCustomerDiscountError = 2;
            $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAIL_PAYPAL_NOT_VERIFIED, $discount->getCode(), $payerId));
            $this->logger->warn(sprintf('Paypal: payment with discount not possible for order %s, account %s is not yet verified', $orderId, $payerId));
            return $this->_redirect('/order_basis/payment');
        }

        // finalize the transaction
        $resp = false;
        try {
            $resp = $paypal->doExpressCheckoutPayment($order, $token, $payerId, "/payment_paypal/notify");
            if (APPLICATION_ENV == 'testing') {
                $resp['ACK'] = "Success";
            }
        } catch (Yourdelivery_Payment_Paypal_Exception $e) {
        }

        // success, we got it
        if ($resp && $resp['ACK'] == "Success") {
            $this->logger->info('Paypal: receive ACK for order #' . $orderId);

            // filter
            $fraudMessage = false;
            $status = false;
            if (!Default_Helpers_Fraud_Paypal::isLegit($order, $payerId, $details['EMAIL'])) {
                $fraudMessage = Default_Helpers_Fraud_Paypal::getMessage();
                $status = Default_Helpers_Fraud_Paypal::getStatus();
            }

            // check for giropay redirect for DE only, also check for fakes
            if ($resp['REDIRECTREQUIRED'] == "true" && $paypal->getCountryCode() == "DE" && $status !== Yourdelivery_Model_Order_Abstract::FAKE_STORNO) {
                if ($status === Yourdelivery_Model_Order_Abstract::FAKE) {
                    $order->setStatus(Yourdelivery_Model_Order_Abstract::FAKE, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAKE_PAYPAL, $fraudMessage, true)
                    );
                }
                return $paypal->redirectUserToGiropay($token);
            }

            $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_SUCCESS_PAYPAL)
                    , true);
            $order->finalizeOrderAfterPayment('paypal', false, $status, $fraudMessage);

            if ($order->getKind() == 'comp') {
                return $this->_redirect('/order_company/success');
            }
            return $this->_redirect('/order_private/success');
        }

        // failed
        $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAIL_PAYPAL, $orderId, $resp['L_LONGMESSAGE0']);
        $this->logger->err($msg->getRawMessage());
        $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg);

        if ($resp['L_ERRORCODE0'] == "10417") {
            $this->warn(__("PayPal konnte dir Bezahlung nicht durchführen, bitte wählen Sie ein andere Zahlungsmethode."));
        } else {
            $this->warn(__("PayPal konnte die Bezahlung nicht durchführen. Versuchen Sie es bitte erneut."));
        }
        return $this->_redirect('/order_basis/payment');
    }

    /**
     * mark an order as not usable with this discount
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 23.07.2012
     * @param Yourdelivery_Model_Order $order
     */
    protected function _markDiscountAsNotUsable(Yourdelivery_Model_Order $order, Yourdelivery_Payment_Paypal $paypal) {

        $discount = $order->getDiscount();
        $this->session->newCustomerDiscountId = $discount->getId();
        $this->session->newCustomerDiscountAbsTotal = $order->getAbsTotal();
        $order->removeDiscount();
        $paypal->setTokenInvalid($order->getId());
    }

}
