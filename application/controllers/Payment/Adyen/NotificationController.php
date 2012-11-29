<?php

require_once(APPLICATION_PATH . '/../library/Yourdelivery/Payment/Adyen/Notifications.php');

/**
 * @author Matthias Laug <laug@lieferando.de> 
 */
class Payment_Adyen_NotificationController extends Default_Controller_Auth {

    const SOAP_ENCODING = 'UTF-8';

    /**
     * @author Matthias Laug <laug@lieferando.de> 
     */
    public function init() {
        
        parent::init();
        
        $this->_disableView();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de> 
     */
    public function indexAction() {

        $request = $this->getRequest();
        if ($request->isPost()) {

            $classmap = array(
                'NotificationRequest' => 'NotificationRequest',
                'NotificationRequestItem' => 'NotificationRequestItem',
                'Amount' => 'Amount');

            $server = new SoapServer(APPLICATION_PATH . '/templates/adyen/Notification.wsdl', array(
                'login' => "",
                'password' => "",
                'soap_version' => SOAP_1_1,
                'style' => SOAP_DOCUMENT,
                'encoding' => SOAP_LITERAL,
                'location' => IS_PRODUCTION ? "https://ca-live.adyen.com/ca/services/Notification" : "https://ca-test.adyen.com/ca/services/Notification",
                'trace' => false,
                'classmap' => $classmap));
            $server->addFunction('sendNotification');
            $server->handle();
        }
    }

}
