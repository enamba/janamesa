<?php

/**
 * Use for heidelpay transactions
 * @author vpriem
 * @since 18.05.2011
 */
require_once(APPLICATION_PATH . '/controllers/Payment/Abstract.php');

class Payment_HeidelpayController extends Payment_Abstract {

    /**
     * Registration
     * Call back from heidelpay, for logged in Users that have their card numbers registered
     * called after User has entered Card Data in Heidelpay Form
     * redirects to debitAction when registration was successfull
     * @author vpriem
     * @modified Daniel Hahn <hahn@lieferando.de>
     * @since 18.05.2011
     */
    public function registerAction() {

        // no view
        $this->_disableView();

        // is it post
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->logger->err('Heidelpay RG: called with get method');
            echo "http://" . HOSTNAME . "/error/throwpayment";
            return;
        }

        // get post
        $post = $request->getPost();
        $secret = $request->getParam('secret');

        // get parameters
        $orderId = $post['IDENTIFICATION_TRANSACTIONID'];
        $orderId = explode("-", $orderId);
        $orderId = (integer) $orderId[0];

        // check order id
        if (!$orderId) {
            $this->logger->err('Heidelpay RG: called without order id');
            echo "http://" . HOSTNAME . "/error/throwpayment";
            return;
        }

        // create order
        try {
            $order = new Yourdelivery_Model_Order($orderId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err('Heidelpay RG: cannot find order #' . $orderId);
            echo "http://" . HOSTNAME . "/error/throwpayment";
            return;
        }

        // create log table
        $dbTable = new Yourdelivery_Model_DbTable_Heidelpay_Wpf_Transactions();
        $transId = $dbTable->createRow(array(
                    'response' => http_build_query($post),
                    'orderId' => $orderId,
                ))->save();


        // set the default redirect url
        $redirect = "/order_basis/payment";

        // secure test
        if ($secret != sha1($order->getNr() . SALT)) {
            $redirect = "/error/throwpayment";

            $this->logger->err('Heidelpay RG: called with wrong control key for order #' . $orderId);
        }
        // finalize the transaction
        elseif (isset($post['PROCESSING_RESULT']) && strstr($post['PROCESSING_RESULT'], "ACK")) {
            $this->logger->info('Heidelpay RG: receive ACK for order #' . $orderId);

            // save uniqueId
            $creditCard = new Yourdelivery_Model_Customer_Creditcard();
            $creditCard->setCustomerId($order->getCustomerId());
            $creditCard->setUniqueId($post['IDENTIFICATION_UNIQUEID']);
            $creditCard->setName(__("Kreditkarte %s", substr(time(), 0, 6)));
            $creditCard->setBrand($post['ACCOUNT_BRAND']);
            $creditCard->setNumber($post['ACCOUNT_NUMBER']);
            $creditCard->save();

            $redirect = "/payment_heidelpay/debit/transaction/" . $transId . "/secret/" . sha1($order->getNr() . SALT);
        }
        // inform user
        else {
            $redirect .= "?crediterror=2";
            $this->logger->err('Heidelpay RG: transaction failed for order #' . $orderId . ' because: ' . $post['PROCESSING_REASON'] . ": " . $post['PROCESSING_RETURN']);
        }

        // redirect user
        echo "http://" . HOSTNAME . $redirect;
    }

    /**
     * 
     * action for submitting debit after register,
     * called by Heidelpay
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 15.12.2011
     */
    public function debitAction() {

        $this->_disableView();

        $request = $this->getRequest();
        $transId = $request->getParam('transaction');
        $secret = $request->getParam('secret');

        // get the transaction
        $dbTable = new Yourdelivery_Model_DbTable_Heidelpay_Wpf_Transactions();
        $dbRows = $dbTable->find($transId);
        $dbRow = $dbRows->current();
        if (!$dbRow) {
            $this->logger->err('Heidelpay DB: no transaction for transactionId: ' . $transId);
            return $this->_redirect("/error/throwpayment");
        }

        $orderId = $dbRow->orderId;
        $response = $dbRow->getResponse();
        $uniqueId = $response['IDENTIFICATION_UNIQUEID'];

        // check order id
        if (!$orderId) {
            $this->logger->err('Heidelpay DB: called without order id');
            return $this->_redirect("/error/throwpayment");
        }

        // set the default redirect url
        $redirect = "/order_basis/payment";

        // create order
        try {
            $order = new Yourdelivery_Model_Order($orderId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err('Heidelpay DB: cannot find order #' . $orderId);
            return $this->_redirect("/error/throwpayment");
        }

        // secure test
        if ($secret != sha1($order->getNr() . SALT)) {
            $this->logger->err('Heidelpay DB: called with wrong control key for order #' . $orderId);
            return $this->_redirect("/error/throwpayment");
        }

        $heidelpay = new Yourdelivery_Payment_Heidelpay_Xml();

        $resp = false;
        try {
            $resp = $heidelpay->request($uniqueId, $order, true);
        } catch (Yourdelivery_Payment_Heidelpay_Exception $e) {
            $this->logger->err('Heidelpay DB Error: ' . $e->getMessage());
            return $this->_redirect("/error/throwpayment");
        }

        // ack
        if (isset($resp['Result']) && strstr($resp['Result'], "ACK") && strstr($resp['Status'], "NEW")) {
            $this->logger->info('Heidelpay XML: receive ACK with status NEW for order #' . $orderId);

            $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, 
                     new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_SUCCESS_CREDIT)     
                    , true);
            $order->finalizeOrderAfterPayment('credit');

            $redirect = "/order_private/success";
            if ($order->getKind() == 'comp') {
                $redirect = "/order_company/success";
            }
        }
        // case for 3DSecure async redirect 
        elseif ($resp['Result'] && strstr($resp['Result'], "ACK") && $resp['Status'] == "WAITING") {
            $this->logger->info('Heidelpay XML: receive ACK with status WAITING for order #' . $orderId);

            // save values in session
            if ($resp['RedirectUrl'] && $resp['RedirectParams']) {
                $this->session->CreditRedirectUrl = $resp['RedirectUrl'];
                $this->session->CreditRedirectParams = $resp['RedirectParams'];
            }
            $redirect = "/payment_heidelpay/redirect";
        }
        // fake ?
        elseif (isset($resp['Return']) && Yourdelivery_Payment_Heidelpay::isFake($resp['Return'])) {
            $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAKE_CREDIT, $orderId, $resp['Reason'] , $resp['Return']);      
            $this->logger->warn($msg->getRawMessage());
            $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg, true);
            $order->finalizeOrderAfterPayment('credit', true);

            $redirect = "/order_private/success";
            if ($order->getKind() == 'comp') {
                $redirect = "/order_company/success";
            }
        }
        // inform user
        else {
            $redirect .= "?crediterror=1";
            $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_ERROR_CREDIT, $orderId, $resp['Reason'] , $resp['Return']);                  
            $this->logger->err($msg->getRawMessage());
            $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg);
        }

        return $this->_redirect($redirect);
    }

    /**
     * Call back from heidelpay
     * @author vpriem
     * @since 18.05.2011
     */
    public function callbackAction() {

        // no view
        $this->_disableView();

        // is it post
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->logger->err('Heidelpay: called with get method');
            echo "http://" . HOSTNAME . "/error/throwpayment";
            return;
        }

        // get post
        $post = $request->getPost();
        $secret = $request->getParam('secret');

        // get parameters
        $orderId = $post['IDENTIFICATION_TRANSACTIONID'];
        $orderId = explode("-", $orderId);
        $orderId = (integer) $orderId[0];

        // check order id
        if (!$orderId) {
            $this->logger->err('Heidelpay: called without order id');
            echo "http://" . HOSTNAME . "/error/throwpayment";
            return;
        }

        // create order
        try {
            $order = new Yourdelivery_Model_Order($orderId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err('Heidelpay: cannot find order #' . $orderId);
            echo "http://" . HOSTNAME . "/error/throwpayment";
            return;
        }

        // create log table
        $dbTable = new Yourdelivery_Model_DbTable_Heidelpay_Wpf_Transactions();
        $dbTable->createRow(array(
            'response' => http_build_query($post),
            'orderId' => $orderId,
        ))->save();

        // set the default redirect url
        $redirect = "/order_basis/payment";

        // secure test
        if ($secret != sha1($order->getNr() . SALT)) {
            $redirect = "/error/throwpayment";

            $this->logger->err('Heidelpay: called with wrong control key for order #' . $orderId);
        }
        // finalize the transaction
        elseif (isset($post['PROCESSING_RESULT']) && strstr($post['PROCESSING_RESULT'], "ACK")) {
            $this->logger->info('Heidelpay: receive ACK for order #' . $orderId);

            $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, 
                     new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_SUCCESS_CREDIT)     
                    , true);
            $order->finalizeOrderAfterPayment('credit');

            $redirect = "/order_private/success";
            if ($order->getKind() == 'comp') {
                $redirect = "/order_company/success";
            }
        }
        // canceled from user
        elseif (isset($post['FRONTEND_REQUEST_CANCELLED']) && $post['FRONTEND_REQUEST_CANCELLED'] == "true") {
            $this->logger->warn('Heidelpay: user cancels order #' . $orderId);
        }
        // hail to the thief !
        elseif (isset($post['PROCESSING_RETURN_CODE']) && Yourdelivery_Payment_Heidelpay::isFake($post['PROCESSING_RETURN_CODE'])) {            
            $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAKE_CREDIT, $orderId, $post['PROCESSING_REASON'] , $post['PROCESSING_RETURN']);      
            $this->logger->warn($msg->getRawMessage());
      
            $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg);
            $order->finalizeOrderAfterPayment('credit', true);

            $redirect = "/order_private/success";
            if ($order->getKind() == 'comp') {
                $redirect = "/order_company/success";
            }
        }
        // inform user
        else {
            $redirect .= "?crediterror=1";
            $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_ERROR_CREDIT, $orderId, $post['PROCESSING_REASON'] , $post['PROCESSING_RETURN']);                  
            $this->logger->err($msg->getRawMessage());            
            $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg);
        }

        // redirect user
        echo "http://" . HOSTNAME . $redirect;
    }

    /**
     * Redirect Page for 3D Secure
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function redirectAction() {

        $url = $this->session->CreditRedirectUrl;
        $params = $this->session->CreditRedirectParams;
        unset($this->session->CreditRedirectUrl);
        unset($this->session->CreditRedirectParams);

        $this->view->assign("url", $url);
        $this->view->assign("params", $params);
    }

    /**
     * Finalization Callback for 3DSecure
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function finalizeAction() {

        $this->_disableView();
        $request = $this->getRequest();
        $response = urldecode($request->getParam("response"));
        $secret = $request->getParam('secret');
        $uniqueId = $request->getParam('uniqueId');

        $heidelpay = new Yourdelivery_Payment_Heidelpay_Xml();
        $resp = $heidelpay->finalize($response);
        $orderId = explode("-", $resp['TransactionID']);
        $orderId = $orderId[0];

        try {
            $order = new Yourdelivery_Model_Order($orderId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err($e->getMessage());
            $this->_redirect("/error/throwpayment");
        }

        $dbTable = new Yourdelivery_Model_DbTable_Heidelpay_Xml_Transactions();
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'params' => '',
            'response' => $response
        ))->save();

        $redirect = "/order_basis/payment";
        if ($secret != sha1($order->getNr() . SALT)) {
            $redirect = "/error/throwpayment";
            $this->logger->err('Heidelpay XML: called with wrong control key for order #' . $orderId);
        } else {
            if (isset($resp['Result']) && strstr($resp['Result'], "ACK")) {
                $this->logger->info('Heidelpay XML: receive ACK for order #' . $orderId);

                $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, 
                        new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_SUCCESS_CREDIT)     
                        , true);
                $order->finalizeOrderAfterPayment('credit');

                $redirect = "/order_private/success";
                if ($order->getKind() == 'comp') {
                    $redirect = "/order_company/success";
                }
                // 3DSecure Error
            } elseif (isset($resp['Result']) && strstr($resp['Result'], "NOK") && isset($resp['Reason']) && $resp['Reason'] == "3DSECURE_ERROR") {
                if (isset($uniqueId)) {
                    $table = new Yourdelivery_Model_DbTable_Customer_Creditcard();
                    $row = $table->fincByUniqueId($uniqueId);
                    $row->delete();
                }
                $msg = 'Heidelpay XML: order #' . $orderId . ' 3DSecure Fehler: ' . $resp['Reason'] . ": " . $resp['Return'];
                $this->logger->warn($msg);
                $redirect .= "/?crediterror=3";
            }
            // fake ?
            elseif (isset($resp['Return']) && Yourdelivery_Payment_Heidelpay::isFake($resp['Return'])) {             
                $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAKE_CREDIT, $orderId, $resp['Reason'] , $resp['Return']);      
                $this->logger->warn($msg->getRawMessage());               
                $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg, true);
                $order->finalizeOrderAfterPayment('credit', true);

                $redirect = "/order_private/success";
                if ($order->getKind() == 'comp') {
                    $redirect = "/order_company/success";
                }
            }
            // inform user
            else {
                $redirect .= "?crediterror=1";

                $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_ERROR_CREDIT, $orderId, $resp['Reason'] , $resp['Return']);                  
                $this->logger->err($msg->getRawMessage());
                $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg);
            }
        }

        $this->_redirect($redirect);
    }

}
