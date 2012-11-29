<?php

ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 

class NotificationRequest {

    public $notificationItems;
    public $live;

}

class NotificationRequestItem {

    public $amount;
    public $eventCode;
    public $eventDate;
    public $merchantAccountCode;
    public $merchantReference;
    public $originalReference;
    public $pspReference;
    public $reason;
    public $success;
    public $paymentMethod;
    public $operations;
    public $additionalData;

}

class Amount {

    public $currency;
    public $value;

}

/**
 * handle each item, send to the soap server
 * 
 * @author Matthias Laug <laug@lieferando.de>
 * @since 21.05.2012
 * @param type $item 
 */
function handleItem($item) {

    $logger = Zend_Registry::get('logger');
    $logger->info('ADYEN NOTIFICATION: processing Psp Reference ' . $item->pspReference);
    $approvedStates = array(
        Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED,
        Yourdelivery_Model_Order_Abstract::PAYMENT_PENDING
    );

    switch ($item->eventCode) {

        default:
            $logger->info('ADYEN NOTIFICATION: Unknown-Command : ' . $item->eventCode);
            break;

        case 'AUTHORISATION':
            $pspData = explode('-', (string) $item->merchantReference);
            if (is_array($pspData) && count($pspData) == 2) {
                $orderNr = $pspData[0];
                $unique = $pspData[1];
                $logger->info('ADYEN NOTIFICATION: getting nr ' . $orderNr . ' from psp data');
                try {
                    $orderData = Yourdelivery_Model_DbTable_Order::findByNr($orderNr);
                    if (!$orderData) {
                        throw new Yourdelivery_Exception_Database_Inconsistency('could not find order by nr ' . $orderNr);
                    }

                    if (!in_array($orderData['state'], $approvedStates)) {
                        $logger->info('ADYEN NOTIFICATION: ignore order nr ' . $orderNr . ' cause in state' . $orderData['state']);
                        break;
                    }

                    $logger->debug('ADYEN NOTIFICATION: authorisation event occured');

                    $orderId = (integer) $orderData['id'];
                    if ($orderId <= 0) {
                        throw new Yourdelivery_Exception_Database_Inconsistency('could not find order by id ' . $orderId);
                    }

                    $order = new Yourdelivery_Model_Order($orderId);
                    if ($item->success) {
                        if (in_array($order->getStatus(), $approvedStates) && $order->getLastStateStatus() < Yourdelivery_Model_Order_Abstract::NOTAFFIRMED) {
                            $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, 
                                     new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_SUCCESS_ADYEN)
                               );
                            $order->finalizeOrderAfterPayment($order->getPayment());
                            $logger->info(sprintf('ADYEN NOTIFICATION: Changed order(#%s) from state(%s) to notaffirmed', $orderId, $order->getStatus()));
                            
                        } else {
                            //this case should never happen
                            $logger->info(sprintf('ADYEN NOTIFICATION: Could not change order(#%s) to affirmed because the state(%s)/current state(%s) wrong', $orderId, $order->getState(), $order->getLastStateStatus()));
                        }
                    } else {
                        $logger->info(sprintf('ADYEN NOTIFICATION: Could not change order(#%s) to affirmed because :%s', $orderId, $item->reason));
                        $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED,
                                 new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAIL_ADYEN, $item->reason)
                                );
                    }
                    break;
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $logger->warn('ADYEN NOTIFICATION: (error) ' . $e->getMessage());
                    break;
                }
            }
            $logger->info('ADYEN NOTIFICATION: Merchant-Reference was not valid: ' . $item->merchantReference);
            break;
    }
}

/**
 * handle the request from the soap server
 * 
 * @author Matthias Laug <laug@lieferando.de>
 * @since 21.05.2012
 * @param stdClass $request
 * @return array 
 */
function sendNotification($request) {

    if (is_array($request->notification->notificationItems->NotificationRequestItem)) {
        foreach ($request->notification->notificationItems->NotificationRequestItem as $item) {
            handleItem($item);
        }
    } else {
        $item = $request->notification->notificationItems->NotificationRequestItem;
        handleItem($item);
    }

    return array("notificationResponse" => "[accepted]");
}

