<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WiercikListener
 * SMS printer SOAP message receiver class
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 04.05.2012
 */
class Yourdelivery_Soap_WiercikListener {

    protected $logger = null;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    /**
     * Sets expected order as received
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param string $orderPrinterKey
     * @return boolean
     */
    public function orderReceived($orderPrinterKey) {



        $this->raiseBegin(__METHOD__, array($orderPrinterKey));
        try {

            // order retrieving
            $order = $this->getOrderByPrinterKey($orderPrinterKey);
            if (!isset($order)) {
                throw new UnexpectedValueException("No order with printer key: `$orderPrinterKey` found");
            }
            $orderId = $order->getId();
            // order updating
            $order->setStatus($order->getStatus(),
                     new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::WIERCIK_RECEIVED )
             );


            $this->logger->info( sprintf(
                            'Order with id: `%d` and printer key: `%s` has been received', $orderId, $orderPrinterKey
                    ));
            return $this->raiseSuccess(__METHOD__, array($orderPrinterKey));
        } catch (Exception $ex) {
            if ($ex instanceof DomainException) {
                $this->logger->crit(sprintf(
                                'Order with id: `%d` and printer key: `%s` recieving - consistency checking failure: %s', $orderId, $orderPrinterKey, $ex->getMessage()
                        ));
            } else {
                $this->logger->crit( sprintf(
                                'Order with id: `%d` and printer key: `%s` recieving error: %s', $orderId, $orderPrinterKey, $ex->getMessage()
                        ), Zend_Log::ERR);
                $this->logger->debug(sprintf('order: %s', $ex));
            }
            return $this->raiseFailure(__METHOD__, array($orderPrinterKey));
        }
    }

    /**
     * Sets expected order as lost (due to response timeout)
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param string $orderPrinterKey
     * @return boolean
     */
    public function orderLost($orderPrinterKey) {
        $this->raiseBegin(__METHOD__, array($orderPrinterKey));
        try {

            // order retrieving
            $order = $this->getOrderByPrinterKey($orderPrinterKey);
            if (!isset($order)) {
                throw new UnexpectedValueException("No order with printer key: `$orderPrinterKey` found");
            }
            $orderId = $order->getId();
            // order updating
            $order->setStatus(Yourdelivery_Model_Order_Abstract::DELIVERERROR,
                                             new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::WIERCIK_LOST )
                    );

            $this->logger->info(sprintf(
                            'Order with id: `%d` and printer key: `%s` has been lost', $orderId, $orderPrinterKey
                    ));
            return $this->raiseSuccess(__METHOD__, array($orderPrinterKey));
        } catch (Exception $ex) {
            if ($ex instanceof DomainException) {
                $this->logger->crit( sprintf(
                                'Order with id: `%d` and printer key: `%s` losing - consistency checking failure: %s', $orderId, $orderPrinterKey, $ex->getMessage()
                        ));
            } else {
                $this->logger->crit(sprintf(
                                'Order with id: `%d` and printer key: `%s` losing error: %s', $orderId, $orderPrinterKey, $ex->getMessage()
                        ));
                $this->logger->debug(sprintf('order: %s', $ex));
            }

            return $this->raiseFailure(__METHOD__, array($orderPrinterKey));
        }
    }

    /**
     * Sets expected order as accepted, also sets its planned delivery time
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param string $orderPrinterKey
     * @param integer $deliveryDelay
     * @return boolean
     */
    public function orderAccepted($orderPrinterKey, $deliveryDelay) {
        $this->raiseBegin(__METHOD__, array($orderPrinterKey, $deliveryDelay));
        try {

            // order retrieving
            $order = $this->getOrderByPrinterKey($orderPrinterKey);
            if (!isset($order)) {
                throw new UnexpectedValueException("No order with printer key: `$orderPrinterKey` found");
            }
            $orderId = $order->getId();
            
            // order updating
            $deliverDelay = $order->getDeliverDelay();
            $deliverDelay->setServiceDeliverDelay($deliveryDelay)
                         ->save();

            $customer = $order->getCustomer();
            $phoneNumber = $order->getLocation()->getTel();
            if ($phoneNumber) {
                $phoneNumber = Default_Helpers_Normalize::telephone($phoneNumber);
                if (Default_Helpers_Phone::isMobile($phoneNumber)) {
                    $sms = new Yourdelivery_Sender_Sms_Template("printer_notify");
                    $sms->assign('order', $order);
                    if ($isSmsSent = $sms->send($phoneNumber)) {
                        $this->logger->info(sprintf('Successfully send sms to %s for order #%s', $phoneNumber, $orderId));
                    } else {
                        $this->logger->info(sprintf('Could not send sms to %s for order #%s', $phoneNumber, $orderId));
                    }
                } else {
                    $this->logger->info(sprintf('Phone number %s is not valid for order #%s', $phoneNumber, $orderId));
                }
            }

            if (!$isSmsSent) {
                $this->logger->info('Send email to %s for order #%s', $customer->getEmail(), $orderId);

                $email = new Yourdelivery_Sender_Email_Template("printer_notify.txt");
                $email->setSubject($order->isPreOrder() 
                        ? __("Voraussichtliche Lieferzeit %s", $deliverDelay->computeDelayFormated())
                        : __("Voraussichtliche Lieferzeit am %s", date(__("d.m.Y H:i"), $order->computeArrivalTime())))
                      ->addTo($customer->getEmail())
                      ->assign('order', $order)
                      ->send();
            }


            $order->setStatus(Yourdelivery_Model_Order_Abstract::AFFIRMED,
                                            new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::WIERCIK_CONFIRM ) 
              );

            $this->logger->info(sprintf(
                'Order with id: `%d` and printer key: `%s` has been accepted with delivery time: %s', $orderId, $orderPrinterKey, $order->getDeliverTime()
            ));

            return $this->raiseSuccess(__METHOD__, array($orderPrinterKey, $deliveryDelay));
        } catch (Exception $ex) {
            if ($ex instanceof DomainException) {
                $this->logger->crit( sprintf(
                    'Order with id: `%d` and printer key: `%s` accepting - consistency checking failure: %s', $orderId, $orderPrinterKey, $ex->getMessage()
                ));
            } else {
                $this->logger->crit(sprintf(
                    'Order with id: `%d` and printer key: `%s` accepting error: %s', $orderId, $orderPrinterKey, $ex->getMessage()
                ));
                $this->logger->debug(sprintf('order: %s', $ex));
            }
            
            return $this->raiseFailure(__METHOD__, array($orderPrinterKey, $deliveryDelay));
        }
    }

    /**
     * Sets expected order as rejected, also sets rejection code
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param string $orderPrinterKey
     * @param integer $reasonCode
     * @return boolean
     */
    public function orderRejected($orderPrinterKey, $reasonCode) {
        $this->raiseBegin(__METHOD__, array($orderPrinterKey, $reasonCode));
        try {

            // order retrieving
            $order = $this->getOrderByPrinterKey($orderPrinterKey);
            if (!isset($order)) {
                throw new UnexpectedValueException("No order with printer key: `$orderPrinterKey` found");
            }
            $orderId = $order->getId();
            // order updating
            $order->setStatus(Yourdelivery_Model_Order::REJECTED,
                                             new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::WIERCIK_REJECTED ) 
             );


            $payment = $order->getPayment();

            $messages = array();
            $messages[] = __b("Bestellung wurde erfolgreich storniert");

            switch ($payment) {
                case 'paypal': Yourdelivery_Helpers_Payment::refundPaypal($order, $this->logger, $messages);
                    break;
                case 'ebanking': Yourdelivery_Helpers_Payment::refundEbanking($order, $this->logger, $messages, sprintf("order has been rejected by restaurant %s", $order->getService()->getId()));
                    break;
                case 'credit': Yourdelivery_Helpers_Payment::refundCredit($order, $this->logger, $messages);
                    break;
                default: break;
            }

            $order->sendStornoEmailToUser();

            $this->logger->info(sprintf(
                            'Order with id: `%d` and printer key: `%s` has been rejected with reason code: `%d`', $orderId, $orderPrinterKey, $reasonCode
                    ));
            return $this->raiseSuccess(__METHOD__, array($orderPrinterKey, $reasonCode));
        } catch (Exception $ex) {
            if ($ex instanceof DomainException) {
                $this->logger->crit( sprintf(
                                'Order with id: `%d` and printer key: `%s` rejecting - consistency checking failure: %s', $orderId, $orderPrinterKey, $ex->getMessage()
                        ));
            } elseif ($ex instanceof ErrorException) {
                $this->logger->crit( sprintf(
                                'Order with id: `%d` and printer key: `%s` rejecting - cancellation request error: %s', $orderId, $orderPrinterKey, $ex->getMessage()
                        ));
            } else {
                $this->logger->crit(sprintf(
                                'Order with id: `%d` and printer key: `%s` rejecting error: %s', $orderId, $orderPrinterKey, $ex->getMessage()
                        ));
                $this->logger->debug(sprintf('order: %s', $ex));
            }

            return $this->raiseFailure(__METHOD__, array($orderPrinterKey, $reasonCode));
        }
    }

    /**
     * Updated SMS printer status (whether is or is not currently online)
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param string $restaurantPrinterKey
     * @param integer $isOnline
     * @return boolean
     */
    public function printerStatus($restaurantPrinterKey, $isOnline) {
        $this->raiseBegin(__METHOD__, array($restaurantPrinterKey, $isOnline));

        return $this->raiseSuccess(__METHOD__, array($restaurantPrinterKey, $isOnline));
    }

    /**
     * Updated SMS printer status (whether is or is not currently online) - collection dedicated version
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param array $restaurantPrinterKeyStatuses
     * @return boolean
     */
    public function printersStatuses($restaurantPrinterKeyStatuses) {
        $this->raiseBegin(__METHOD__, array($restaurantPrinterKeyStatuses));

        if (!is_array($restaurantPrinterKeyStatuses)) {
            $this->logger->err(sprintf(
                            'Invalid restaurant key/status list: `%s`', json_encode($restaurantPrinterKeyStatuses)
                    ));
            return $this->raiseFailure(__METHOD__, array($restaurantPrinterKeyStatuses));
        }

        $printerIds = array_keys($restaurantPrinterKeyStatuses);

        foreach ($printerIds as $printerId) {

            $printer = new Yourdelivery_Model_Printer_Wiercik($printerId);
            if ($restaurantPrinterKeyStatuses[$printerId] === 1) {
                $printer->setOnline(1);
                $printer->save();
            } else {
                $printer->setOnline(0);
                $printer->save();
            }
        }

        return $this->raiseSuccess(__METHOD__, array($restaurantPrinterKeyStatuses));
    }

    /**
     * Updates SMS printer statuses
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param array $restaurantPrinterKeyList
     * @return boolean
     *
     */
    public function printersOnline($restaurantPrinterKeyList) {
        $this->raiseBegin(__METHOD__, array($restaurantPrinterKeyList));

        return $this->raiseSuccess(__METHOD__, array($restaurantPrinterKeyList));
    }

    /**
     * Retrieves order instance (must be already confirmed) by its notify printer key
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param string $orderPrinterKey
     * @return PropelOrder
     */
    protected function getOrderByPrinterKey($orderPrinterKey) {

        try {
            $order = new Yourdelivery_Model_Order($orderPrinterKey);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->crit(sprintf('orderkey %s from printer could not be found', $orderPrinterKey));
            return null;
        }

        return $order;
    }

    /**
     * Method called on every call beginning
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param string $method
     * @param array $args
     * @return boolean
     */
    protected function raiseBegin($method, $args) {
        $this->logger->debug(sprintf(
                        'Printer listener method `%s` with arguments: %s - call invoked', $method, json_encode($args)
                ));
    }

    /**
     * Method called on every success
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param string $method
     * @param array $args
     * @return boolean
     */
    protected function raiseSuccess($method, $args) {
        $this->logger->debug( sprintf(
                        'Printer listener method `%s` with arguments: %s - call success', $method, json_encode($args)
                ));
        return true;
    }

    /**
     * Method called on every failure
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param string $method
     * @param array $args
     * @return boolean
     */
    protected function raiseFailure($method, $args) {
        $this->logger->debug( sprintf(
                        'Printer listener method `%s` with arguments: %s - call failure', $method, json_encode($args)
                ));
        return false;
    }

}

