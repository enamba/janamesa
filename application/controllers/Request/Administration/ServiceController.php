<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ServiceController
 *
 * @author mlaug
 */
class Request_Administration_ServiceController extends Default_Controller_RequestAdministrationBase {

    /**
     * send the test fax
     * @author Matthias Laug <laug@lieferando.de>
     * @since 17.11.2010
     * @modified 02.03.2011
     */
    public function testfaxAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $request = $this->getRequest();

        if ($request->isPost()) {
            $faxnr = $request->getParam('fax', null);
            $faxService = $request->getParam('faxService', Yourdelivery_Sender_Fax::RETARUS); //default is retarus
            // Using config-based locale during composing and sending fax
            $this->_restoreLocale();
            $fax = new Yourdelivery_Sender_Fax();
            $ret = $fax->test($faxnr, $faxService);
            $this->_overrideLocale();

            echo json_encode(array(
                'sendto' => $faxnr,
                'service' => $faxService,
                'time' => time(),
                'result' => $ret,
                'message' => $ret ? __b('Testfax erfolgreich an %s verschickt', $faxnr) : __b('Beim versenden des Fax ist ein Fehler aufgeteten')
            ));
        }
    }

    /**
     * send out a test email to service
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.03.2012
     */
    public function testemailAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $request = $this->getRequest();

        $message = 'Testemail: ' . $this->config->domain->base;
        if ( $this->config->domain->base == 'janamesa.com.br' ){
        $message = "Bem vindo no Jánamesa!

Podemos começar a transferir seus pedidos da nossa página janamesa.com.br !

Veja aqui seu menu: Link zum DL

Qualquer dúvida ou alteração, entre em contato com nosso SAC:
Segunda à Sexta: das 9:00 até as 23:00 horas
Sábado e Domingo: das 11:00 até as 23:00 horas

Telefone: (011) 4063-1731
Email: sac@janamesa.com.br


Por favor, atentar-se a forma de pagamento que vai estar escrito no pedido. Caso de \"o pedido já está pago via internet\", o cliente já efetuou pagamento online na página da web. O pedido já está pago. Não sendo nescessário o pagamento a vista. Jánamesa vai depositar o dinheiro nas primeiras duas semanas do mês seguinte.

Desejamos bons negócios e uma excelente parceria!

Seu time Jánamesa
www.janamesa.com.br";
        }


        if ($request->isPost()) {
            $to = $request->getParam('email',null);

            // Using config-based locale during composing and sending e-mail
            $this->_restoreLocale();
            //$ret = Yourdelivery_Sender_Email::quickSend(__b('Testemail von %s', $this->config->domain->base), $message, null, $email, $this->config->testing->email);

            $email = new Yourdelivery_Sender_Email();
            $email->addTo($to);
            $email->setSubject('Yourdelivery: ' . __b('Testemail von %s', $this->config->domain->base));
            $email->setBodyText($message);
            $email->setFrom('noreply@' . $this->config->domain->base, $this->config->domain->base);
            $ret = $email->send('system');

            $this->_overrideLocale();

            echo json_encode(array(
                'sendto' => $to,
                'time' => time(),
                'result' => $ret,
                'message' => $ret ? __b('Testemail erfolgreich an %s verschickt', $to) : __b('Beim versenden der eMail ist ein Fehler aufgeteten')
            ));
        }
    }

    /**
     * @author Daniel Hahn  <hahn@lieferando.de>
     * @since 15.06.2012
     * @return string
     */
    public function faxserviceAction() {

        $this->_disableView();
        $request = $this->getRequest();

        $serviceId = $request ->getParam('serviceId');
        $faxService = $request ->getParam('faxService');


        if(!in_array($faxService, array('interfax', 'retarus'))) {
            echo "Error";
            return ;
        }

        try{
            $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);

            $service->setFaxService($faxService);
            $service->save();
            echo "OK";
            return ;

        }catch(Yourdelivery_Exception_Database_Inconsistency $e) {
            echo "Error";
            return ;
        }
    }
    
    /**
     * set new firmware for printer
     * @author Alex Vait <vait@lieferando.de>
     * @since 26.07.2012
     */
    public function setfirmwareAction() {

        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $request = $this->getRequest();

        if ($request->isPost()) {
            $newFirmware = intval($request->getParam('firmware', null));
            $printerId = $request->getParam('printerId', null);            

            $printer = Yourdelivery_Model_Printer_Abstract::factory($printerId);
            $actualFirmware = intval($printer->getFirmware());
            
            if ($newFirmware < $actualFirmware) {
                echo json_encode(array(
                    'error' => 1,
                    'message' => sprintf(__b('Die Update Version ist kleiner als die aktuelle Version bei dem Printer %s'), $printerId)
                ));                
            }
            else if ($newFirmware == $actualFirmware) {
                echo json_encode(array(
                    'error' => 1,
                    'message' => sprintf(__b('Die Update Version ist die gleiche wie die aktuelle Version bei dem Printer %s'), $printerId)
                ));                
            }
            else if ($newFirmware > $actualFirmware+50) {
                echo json_encode(array(
                    'error' => 1,
                    'message' => sprintf(__b('Die Update Version ist zu groß bei dem Printer %s'), $printerId)
                ));                
            }
            else {
                $printer->setUpgrade($newFirmware);                
                $printer->save();
                echo json_encode(array(
                    'success' => 1,
                    'message' => sprintf(__b('Die Update Version wurde gesetzt für den Printer %s'), $printerId)
                ));                
            }

        }
    }    
    
        
    /**
     * send the password to the partner restaurant
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.08.2012
     */
    public function sendpasswordAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $request = $this->getRequest();

        if ($request->isPost()) {
            $restaurantId = $request->getParam('restaurantId', null);
            $kind = $request->getParam('kind', null);

            try{
                $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
            }
            catch(Yourdelivery_Exception_Database_Inconsistency $e) {
                echo json_encode(array(
                    'type' => 'error',
                    'message' =>  __b('Der Dienstleister konnte nicht initialisiert werden')
                ));
                return;
            }

            $partnerData = new Yourdelivery_Model_Servicetype_Partner(null, $restaurant->getId());

            switch ($kind) {
                case 'email':

                    $email = $restaurant->getPartnerEmail();

                    // email is not defined nowhere
                    if (is_null($email)) {
                        echo json_encode(array(
                            'type' => 'error',
                            'message' => __b('Es wurde keine Email Adresse bei diesem Dienstleister gefunden!')
                        ));
                        return;
                    }

                    $state = $restaurant->sendPartnerTemporaryPassword('email', $email, true);

                    // sending email failed
                    if (!$state) {
                        echo json_encode(array(
                            'type' => 'error',
                            'message' => __b('Email konnte nicht verschickt werden. Temporäres passwort wurde in der Datenbank nicht gesetzt!')
                        ));
                        $this->logger->adminInfo(sprintf("Email sending error. Temporary password was not send to the restaurant #%s to the email %s", $restaurant->getId(), $email));
                        return;
                    }
                    else {
                        echo json_encode(array(
                            'type' => 'success',
                            'message' => __b('Ein temporäres Passwort wurde an die Email ' . $email . ' verschickt.')
                        ));
                        $this->logger->adminInfo(sprintf("Temporary password was send to the restaurant #%s to the email %s", $restaurant->getId(), $email));
                    }

                    break;

                case 'mobile':

                    $mobile = $restaurant->getPartnerMobile();

                    // mobile number is not defined nowhere
                    if (is_null($mobile)) {
                        echo json_encode(array(
                            'type' => 'error',
                            'message' => __b('Es wurde keine Mobilnummer bei diesem Dienstleister gefunden!')
                        ));
                        return;
                    }

                    $state = $restaurant->sendPartnerTemporaryPassword('mobile', $mobile, true);

                    // sending sms failed
                    if (!$state) {
                        echo json_encode(array(
                            'type' => 'error',
                            'message' => __b('SMS konnte nicht verschickt werden. Temporäres passwort wurde in der Datenbank nicht gesetzt!')
                        ));
                        $this->logger->adminInfo(__b("SMS sending error. Temporary password was not send to the restaurant #%s to the mobile number %s", $restaurant->getId(), $mobile));
                        return;
                    }
                    else {
                        echo json_encode(array(
                            'type' => 'success',
                            'message' => __b('Ein temporäres Passwort wurde per SMS an die Nummer ' . $mobile . ' verschickt.')
                        ));
                        $this->logger->adminInfo(__b("Temporary password was send to the restaurant #%s to the mobile number %s", $restaurant->getId(), $mobile));
                    }

                    break;

                default:
                    echo json_encode(array(
                        'type' => 'error',
                        'message' => __b('Diese Versandart für Passwort ist nicht implementiert!')
                    ));
                    break;
            }
        }
    }

    /**
     * edit partner data for this restaurant
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.08.2012
     */
    public function changepartnerdataAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $request = $this->getRequest();

        if ($request->isPost()) {
            $restaurantId = $request->getParam('restaurantId', null);
            $kind = $request->getParam('kind', null);
            $newvalue = $request->getParam('newvalue', null);

            try{
                $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
            }
            catch(Yourdelivery_Exception_Database_Inconsistency $e) {
                echo json_encode(array(
                    'type' => 'error',
                    'message' =>  __b('Der Dienstleister konnte nicht initialisiert werden')
                ));
                return;
            }

            $partnerData = new Yourdelivery_Model_Servicetype_Partner(null, $restaurant->getId());
            if (is_null($partnerData) || ($partnerData->getId()==0)) {
                $partnerData = new Yourdelivery_Model_Servicetype_Partner();
                $partnerData->setRestaurantId($restaurant->getId());
            }

            switch ($kind) {
                case 'email':
                    $email = Default_Helpers_Normalize::email($newvalue);

                    // check if the email is available
                    if (strlen($email) == 0) {
                        echo json_encode(array(
                            'type' => 'error',
                            'message' => __b('Bitte geben Sie eine E-Mail ein!')
                        ));
                        return;
                    }

                    $emailForm = new Yourdelivery_Form_Partner_Email();
                    if (!$emailForm->isValid(array('email' => $email, 'emailConfirm' => $email))) {
                        // get ther first element of error array, it's the easiest way, because $emailForm->getMessages returns an associative array
                        $message = reset($emailForm->getMessages('email'));

                        echo json_encode(array(
                            'type' => 'error',
                            'message' => $message
                        ));
                        return;
                    }


                    // overwrite the email in the partner data
                    $partnerData->setEmail($email);

                    // also change billing contact YD-3133
                    $billingContact = $restaurant->getBillingContact();
                    $billingContact->setEmail($emailForm->getValue('email'));
                    $billingContact->save();

                    try{
                        $partnerData->save();
                        $this->logger->adminInfo(sprintf("Partner email for restaurant #%s was set to %s", $restaurant->getId(), $email));

                        echo json_encode(array(
                            'type' => 'success',
                            'message' => __b('Neue Partner-E-Mail "' . $email . '" wurde gespeichert.'),
                            'setvalue' => $email
                        ));
                    }
                    catch(Yourdelivery_Exception_Database_Inconsistency $e) {
                        echo json_encode(array(
                            'type' => 'error',
                            'message' => __b('Neue Partner-E-Mail konnte nicht gespeichert werden: %s', $e->getMessage())
                        ));
                        return;
                    }

                    break;

                case 'mobile':

                    $mobile = Default_Helpers_Normalize::telephone($newvalue);

                    // check if the mobile phone is available and it's number format
                    if (strlen($mobile) == 0) {
                        echo json_encode(array(
                            'type' => 'error',
                            'message' => __b('Bitte geben Sie eine Mobilnummer ein!')
                        ));
                        return;
                    }
                    if (!Default_Helpers_Phone::isMobile($mobile)) {
                        echo json_encode(array(
                            'type' => 'error',
                            'message' => __b('Bitte geben Sie eine Mobilnummer in richtigen Format ein!')
                        ));
                        return;
                    }

                    // overwrite mobiel number in the partner data
                    $partnerData->setMobile($mobile);

                    // also change billing contact YD-3133
                    $billingContact = $restaurant->getBillingContact();
                    $billingContact->setMobile($mobile);
                    $billingContact->save();

                    try{
                        $partnerData->save();
                        $this->logger->adminInfo(sprintf("Partner mobile number for restaurant #%s was set to %s", $restaurant->getId(), $mobile));

                        echo json_encode(array(
                            'type' => 'success',
                            'message' => __b('Neue Partner Mobilnummer "' . $mobile . '" wurde gespeichert.'),
                            'setvalue' => $mobile
                        ));
                    }
                    catch(Yourdelivery_Exception_Database_Inconsistency $e) {
                        echo json_encode(array(
                            'type' => 'error',
                            'message' => __b('Neue Partner-Mobilnummer konnte nicht gespeichert werden: %s', $e->getMessage())
                        ));
                        return;
                    }

                    break;

                default:
                    echo json_encode(array(
                        'type' => 'error',
                        'message' => __b('Dieser Parameter für Partner Restaurant ist nicht implementiert!')
                    ));
                    break;
            }
        }
    }
}
