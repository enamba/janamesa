<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class Request_InfoController extends Default_Controller_RequestBase {

    /**
     * send email with message from customer to support
     * used in info-section
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.04.2011
     */
    public function sendcontactformAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        return;
        // get params from jquery
        $name = $this->getRequest()->getParam('name', null);
        $email = $this->getRequest()->getParam('email', null);
        $tel = $this->getRequest()->getParam('tel', null);
        $comp = $this->getRequest()->getParam('comp', null);
        $ort = $this->getRequest()->getParam('ort', null);
        $message = $this->getRequest()->getParam('message', null);

        if (is_null($name) || is_null($email) || is_null($message)) {
            return null;
        }

        // send mail to support
        $body = 'neue Nachricht (Infobereich) von ' . $name . ' (' . $email . ') : ' . $message . ' // Tel: ' . $tel . ' // Company: ' . $comp . ' // Ort: ' . $ort . '// Nachricht: ' . $message;

        $mail = new Yourdelivery_Sender_Email();
        $mail->addTo('support@lieferando.de')
                ->setSubject('neue Nachricht (Infobereich) von ' . $name)
                ->setFrom($email, $name)
                ->setBodyText($body)
                ->send('system');
    }

    /**
     * submit contact company form and send to backoffice
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 02.05.2011
     * @return json
     */
    public function contactcompanyAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form = new Yourdelivery_Form_Info_ContactCompany();
            if ($form->isValid($request->getPost())) {
                $data = $form->getValues();

                $body = 'neue Nachricht (Infobereich - Firma anmelden) von ' . $data['name'] . ' // Email: ' . $data['email'] . ') // Tel: ' . $data['tel'] . ' // Company: ' . $data['comp'] . ' // Ort: ' . $data['ort'] . ' // Nachricht: ' . $data['message'];
                $email = new Yourdelivery_Sender_Email();
                $email->addTo('gia@lieferando.de');
                $email->addTo('spott@lieferando.de');
                
                $email->setSubject('neue Nachricht (Infobereich - Firma anmelden) von ' . $data['name'])
                        ->setFrom($data['email'], $data['name'])
                        ->setBodyText($body)
                        ->send('system');

                echo json_encode(array(
                    'result' => true,
                    'msg' => __('Ihre Nachricht wurde versendet. In Kürze erhalten Sie eine Bestätigung per E-Mail')
                ));
                return;
            } else {

                echo json_encode(array(
                    'result' => false,
                    'msg' => Default_View_Notification::array2html($form->getMessages())
                ));
                return;
            }
        }
    }

    /**
     * submit contact company form and send to backoffice
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 02.05.2011
     * @return string
     */
    public function proposalAction() {
        $this->_disableView();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form = new Yourdelivery_Form_Info_Proposal();
            if ($form->isValid($request->getPost())) {
                $data = $form->getValues();
                
                switch($this->config->domain->base){
                    case 'lieferando.de':
                        $mailto = 'anmeldung@lieferando.de';
                        break;
                    
                    case 'taxiresto.fr':
                        $mailto = 'support@taxiresto.fr';
                        break;
                    
                    case 'pyszne.pl':
                        $mailto = $this->config->locale->email->support;
                        break;
                    
                    default:
                        $mailto = $this->config->locale->email->support;
                        break;
                }

                $body = 'neue Nachricht (Infocenter - Lieferdienst vorschlagen) Restaurant: ' . $data['service'] . 
                    ' // Street: ' . $data['street'] . 
                    ' // Ort: ' . $data['ort'] . 
                    ' // Kategorie: ' . $data['category'] . 
                    ' // Telefon des Lieferdienstes: '. $data['telefon'];
                
                if($data['name']) {
                    $body .= " // Restaurant Besitzer: ".$data['name'];
                }
                
                $this->logger->info($body);
                
                $email = new Yourdelivery_Sender_Email();
                $email->addTo($mailto)
                      ->setSubject('neue Nachricht (Infobereich - Lieferdienst vorschlagen)')
                      ->setBodyText($body)
                      ->send('system');
                
                echo json_encode(array(
                    'result' => true,
                    'msg' => __('Vielen Dank. Ihre Nachricht wurde versendet.')
                ));
                return;
            }
            
            echo json_encode(array(
                'result' => false,
                'msg' => Default_View_Notification::array2html($form->getMessages())
            ));
            return;
        }
    }
    
    
    
    /**
     * submit contact company form and send to backoffice
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 02.05.2011
     * @return json
     */
    public function registerrestaurantAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form = new Yourdelivery_Form_Info_RegisterRestaurant();
            if ($form->isValid($request->getPost())) {
                $data = $form->getValues();           

                $body = 'neue Nachricht (Infocenter - Lieferdienst anmelden) Restaurant: ' . $data['service'] . ' // Name: ' . $data['name'] . ' // Street: ' . $data['street'] . ' // Ort: ' . $data['ort'] . ' // Telefon: '.$data['telefon']. ' // Mobil: ' . $data['mobil'] . ' // Email: '.$data['email']. ' // Erreichbar: ' . $data['contacttime'];
                $this->logger->info($body);
                
                $email = new Yourdelivery_Sender_Email();
                $email->addTo($this->config->locale->email->support)
                        ->setSubject('neue Nachricht (Infobereich - Lieferdienst anmelden)')
                        ->setFrom($data['email'], $data['name'])
                        ->setBodyText($body)
                        ->send('system');
                
                echo json_encode(array(
                    'result' => true,
                    'msg' => __('Vielen Dank. Ihre Nachricht wurde versendet.')
                ));
                return;
            }
            
            echo json_encode(array(
                'result' => false,
                'msg' => Default_View_Notification::array2html($form->getMessages())
            ));
            return;
        }
    }

    /**
     * submit contact form and send to selected department
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.04.2011
     * 
     * @return json
     * 
     * wird in der FAQ und im Kundenservice verwendet
     */
    public function contactformAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form = new Yourdelivery_Form_Info_Contact();
            if ($form->isValid($request->getPost())) {
                $data = $form->getValues();
                $mailto = null;
                switch ($data['mailto']) {
                    case 1:
                    case 2:
                    case 3:
                    case 14: {
                            $mailto = $this->config->locale->email->support;
                            break;
                        }
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9: {
                            $mailto = $this->config->osTicket->backoffice->email;
                            break;
                        }
                    case 10:
                    case 11:
                    case 12:
                    case 13: {
                            $mailto = $this->config->osTicket->buchhaltung->email;
                            break;
                        }
                    default: {
                            $mailto = $this->config->locale->email->support;
                            break;
                        }
                }

                $body = 'neue Nachricht (Infobereich) von ' . $data['name'] . ' // Email: ' . $data['email'] . ') // Tel: ' . $data['tel'] . ' // Company: ' . $data['comp'] . ' // Ort: ' . $data['ort'] . ' // Nachricht: ' . $data['message'];
                $email = new Yourdelivery_Sender_Email();
                $email->addTo($mailto)
                        ->setSubject('neue Nachricht (Infobereich) von ' . $data['name'])
                        ->setFrom($data['email'], $data['name'])
                        ->setBodyText($body)
                        ->send('system');

                echo json_encode(array(
                    'result' => true,
                    'msg' => __('Ihre Nachricht wurde versendet. In Kürze erhalten Sie eine Bestätigung per E-Mail.')
                ));
                return;
            } else {

                echo json_encode(array(
                    'result' => false,
                    'msg' => Default_View_Notification::array2html($form->getMessages())
                ));
                return;
            }
        }
    }

}

