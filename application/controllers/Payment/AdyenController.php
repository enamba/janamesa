<?php

/**
 * Use for adyen transactions
 * @author Matthias Laug <laug@lieferando.de>
 * @since 19.03.2012
 */
require_once(APPLICATION_PATH . '/controllers/Payment/Abstract.php');

class Payment_AdyenController extends Payment_Abstract {

    /**
     * initialize the order payment. we create a html post form and
     * submit it onload
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 19.03.2012 
     */
    public function initializeAction() {
        $order = $this->_getCurrentOrder();
        
        /**
         * we check that state because back button might lead us back to
         * this page and we do not want to initiate an order twice if a payment
         * has already been processed
         */
        if ( $order->getState() != Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED ){
            $this->logger->info(sprintf('ADYEN: tried to initalize order %s for adyen, but state is already %s', $order->getId(), $order->getState()));
            return $this->_redirect('/'); //redirect to index page in that case, if customer is logged in he will be redirected once again
        }
        
        $adyen = new Yourdelivery_Payment_Adyen();
        $this->view->data = $adyen->initPayment($order);
        $this->logger->info(sprintf('ADYEN: initializing order %s for adyen', $order->getId()));
    }

    /**
     * finalize the payment
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 19.03.2012 
     */
    public function processAction() {

        $this->_disableView();
        $request = $this->getRequest();
        $order = $this->_getCurrentOrder();

        $this->logger->debug(sprintf('ADYEN: process result of order %s', $order->getId()));

        $merchantReference = $request->getParam('merchantReference');
        $paymentResult = $request->getParam('authResult');
        $signature = $request->getParam('merchantSig');

        $this->logger->info(sprintf('ADYEN: got result %s for order %s', $paymentResult, $order->getId()));

        //get the number
        $nr = array_pop(array_reverse((explode('-', $merchantReference))));
        if ($nr != $order->getNr()) {
            $this->logger->warn(sprintf('ADYEN: got number %s, but expected %s for order %s', $nr, $order->getNr(), $order->getId()));
            return $this->cancelAction();
        }

        $adyen = new Yourdelivery_Payment_Adyen();
        if (!$adyen->processPayment($order, $request)) {
            $this->logger->warn(sprintf('ADYEN: got invalid signature %s from response, expected %s for order %s', $signature, $hmac, $order->getId()));
            return $this->cancelAction();
        }

        //process result codes
        switch ($paymentResult) {
            default:
                $this->logger->warn(sprintf('ADYEN: redirecting to payment page, unknow result for order %s', $order->getId()));
                return $this->cancelAction();
                break;

            case 'AUTHORISED':
                if ($order->getLastStateStatus() < Yourdelivery_Model_Order_Abstract::NOTAFFIRMED) {
                    $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, 
                            new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_SUCCESS_ADYEN) 
                            );
                    $order->finalizeOrderAfterPayment($order->getPayment());
                    $this->logger->info(sprintf('ADYEN: finalizing order %s', $order->getId()));
                } else {
                    $this->logger->info(sprintf('ADYEN: order %s already finalized', $order->getId()));
                }

                // success, we got it
                if ($order->getKind() == 'comp') {
                    $this->logger->debug(sprintf('ADYEN: redirecting to company success page'));
                    return $this->_redirect('/order_company/success');
                }

                $this->logger->debug(sprintf('ADYEN: redirecting to private success page'));
                return $this->_redirect('/order_private/success');

            case 'REFUSED':
                $this->error(__('Der Bezahlvorgang konnte nicht abgeschlossen werden. Der Betrag wurde nicht abgebucht'));
                $this->logger->warn(sprintf('ADYEN: redirecting to payment page, payment refused for order %s', $order->getId()));
                return $this->cancelAction();
                break;

            case 'CANCELLED':
                $this->warn(__('Du hast den Bezahlvorgang abgebrochen'));
                $this->logger->warn(sprintf('ADYEN: redirecting to payment page, payment cancled for order %s', $order->getId()));
                return $this->cancelAction();
                break;

            case 'PENDING':
                $order->getLastState() != Yourdelivery_Model_Order_Abstract::PAYMENT_PENDING ? 
                    $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_PENDING, 
                             new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_PENDING_ADYEN) 
                            ) :
                    null;
                return $this->_redirect('/order_private/success');
                break;

            case 'ERROR':
                $this->error(__('Während des Bezahlvorgang ist ein Fehler aufgetreten. Der Betrag wurde nicht abgebucht'));
                $this->logger->warn(sprintf('ADYEN: redirecting to payment page, payment had an error for order %s', $order->getId()));
                return $this->cancelAction();
                break;
        }
    }

}