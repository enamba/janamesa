<?php

/**
 * Description of RequestController
 *
 * @author mlaug
 */
class RequestController extends Default_Controller_RequestBase {

    /**
     * @author mlaug
     */
    public function checkAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }

    /**
     * @author vpriem
     * @since 10.02.2011
     */
    public function newpassAction() {

        $form = new Yourdelivery_Form_Request_NewPass();

        $request = $this->getRequest();

        if ($request->isPost()) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            $post = $request->getPost();
            if ($form->isValid($post)) {

                $anonymous = new Yourdelivery_Model_Customer_Anonym();
                $status = $anonymous->forgottenPass($request->getParam('email'));

                switch ($status) {
                    case 0:
                        echo json_encode(array(
                            'success' => __('Ihr Passwort wurde geändert und an Ihre eMail-Adresse gesandt.')
                        ));
                        return;
                    default:
                        echo json_encode(array(
                            'error' => __('Diese eMail-Adresse ist nicht gültig. Bitte geben eine korrekte eMail-Adresse ein.')
                        ));
                }
            } else {
                echo json_encode(array(
                    'error' => __('Diese eMail-Adresse ist nicht gültig. Bitte geben eine korrekte eMail-Adresse ein.')
                ));
            }
        }
    }

    /**
     * @author mlaug
     */
    public function addzettelAction() {
        $this->getHelper('viewRenderer')->setNoRender();
        $params = $this->getRequest()->getParams();
    }

    /**
     * shows xml data for the services map of the admin panel
     */
    public function adminrestmapAction() {
        $this->getResponse()->setHeader('Content-Type', 'text/xml');
        $this->view->assign('xmlstr', $this->session->xmlstr);
    }

    public function rabattcodesAction() {
        $rabatt = new Yourdelivery_Model_Rabatt($this->getRequest()->getParam('id'));
        $this->view->assign('codes', $rabatt->getCodes());
    }

    public function getchartAction() {
        if (is_null($this->getRequest()->getParam('class', null)))
            return null;

        $class = 'Yourdelivery_Chart_Charts_' . $this->getRequest()->getParam('class');
        if (!class_exists($class))
            return null;

        $start = strtotime(str_replace('-', ' ', $this->getRequest()->getParam('start', '01.04.2009-00:00')));
        $end = strtotime(str_replace('-', ' ', $this->getRequest()->getParam('end', 'now')));

        $width = $this->getRequest()->getParam('width', 875);
        $height = $this->getRequest()->getParam('height', 400);

        $stat = new $class($width, $height);
        $stat->setStartTime($start);
        $stat->setEndTime($end);

        $stat->create();
        exit;
    }

    /**
     * rate an order
     * @author mlaug
     */
    public function rateAction() {
        $request = $this->getRequest();
        $orderId = $request->getParam('id', null);
        $order = null;
        try {
            $order = new Yourdelivery_Model_Order($orderId);
            $this->view->assign('ord', $order);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->view->assign('msg', __('Konnte Bestellung nicht finden'));
        }

        if ($request->isPost()) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            try {
                $orderId = $request->getParam('orderId', null);
                if (is_null($orderId)) {
                    return;
                }
                $order = new Yourdelivery_Model_Order($orderId);
                $custId = null;
                if ($order->getCustomer()->isLoggedIn()) {
                    $custId = $order->getCustomer()->getId();
                } else {
                    $custId = null;
                }
                $order->rate(
                        $custId, $request->getParam('r1', 5), $request->getParam('r2', 5), $request->getParam('comment', '')
                );
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
    }
    
    /**
     * @author mlaug
     */
    public function notificationAction() {
        $request = $this->getRequest();
        $this->view->msg = $request->getParam('message');
        $this->view->type = $request->getParam('type', 'success');
    }

    /**
     * get an ort by given plz
     * @author vpriem
     * @since 10.02.2011
     * @deprecated
     */
    public function ortAction() {

        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        $plz = $request->getParam('plz');

        if (strlen($plz) >= 4) {
            $city = Yourdelivery_Model_City::getByPlz($plz);
            if ($city[0]) {
                echo json_encode(array(
                    'ort' => $city[0]
                ));
                return;
            }
        }

        // not found
        $this->getResponse()
                ->setHttpResponseCode(404);
    }

    /**
     * Validation engine language for
     * the validation engine jQuery plugin
     * @author vpriem
     * @since 11.04.2011
     */
    public function validationAction() {

        // print only json
        $this->_helper->viewRenderer->setNoRender(true);

        $json = json_encode(array(
            "required" => array(
                "regex" => "none",
                "alertText" => __("Dieses Feld wird benötigt"),
                "alertTextCheckboxMultiple" => __("Bitte wählen Sie eine Option"),
                "alertTextCheckboxe" => __("Bitte bestätigen Sie diese Checkbox")
            ),
            "length" => array(
                "regex" => "none",
                "alertText" => __("%s bis %s Zeichen erlaubt"),
            ),
            "maxCheckbox" => array(
                "regex" => "none",
                "alertText" => __("Maximale Auswahlmöglichkeit erreicht")
            ),
            "minCheckbox" => array(
                "regex" => "none",
                "alertText" => __("Bitte wählen Sie "),
                "alertText2" => __(" Optionen")
            ),
            "confirm" => array(
                "regex" => "none",
                "alertText" => __("Keine Übereinstimmung")
            ),
            "telephone" => array(
                "regex" => "/^[0-9\\-\\(\\)\\ ]+$/",
                "alertText" => __("Bitte geben Sie eine gültige Telefonnummer ein")
            ),
            "prename" => array(
                "regex" => "none",
                "alertText" => __("Bitte geben Sie ihren Vornamen ein")
            ),
            "password" => array(
                "regex" => "none",
                "alertText" => __("Bitte geben Sie ein Passwort ein")
            ),
            "name" => array(
                "regex" => "none",
                "alertText" => __("Bitte geben Sie ihren Nachnamen ein")
            ),
            "street" => array(
                "regex" => "none",
                "alertText" => __("Bitte geben Sie ihre Straße ein")
            ),
            "hausnr" => array(
                "regex" => "none",
                "alertText" => __("Bitte geben Sie ihre Hausnummer ein")
            ),
            "telefon" => array(
                "regex" => "none",
                "alertText" => __("Bitte geben Sie eine gültige Telefonnummer ein")
            ),
            "message" => array(
                "regex" => "none",
                "alertText" => __("Bitte geben Sie ihre Nachricht ein")
            ),
            "birthday" => array(
                "regex" => "/(^$)|(^(((0[1-9])|([1-9])|([1-2][0-9])|(3[01])).((0[1-9])|([1-9])|(1[0-2])).((19)|(20))([0-9]{2}))$)/",
                "alertText" => __("Bitte geben Sie ein gültiges Geburtsdatum (z.B.: 05.11.1984).")
            ),
            "company" => array(
                "regex" => "none",
                "alertText" => __("Bitte geben Sie einen Firmennamen ein")
            ),
            "projectnumber" => array(
                "regex" => "none",
                "alertText" => __("Bitte geben Sie eine Projektnummer ein")
            ),
            "agb" => array(
                "regex" => "none",
                "alertTextCheckboxe" => __("Bitte bestätigen Sie die AGBs und Datenschutzbestimmungen")
            ),
            "email" => array(
                "regex" => "/^[a-zA-Z0-9_\\.\\-]+\\@([a-zA-Z0-9\\-]+\\.)+[a-zA-Z0-9]{2,4}$/",
                "alertText" => __("Bitte geben Sie eine gültige Emailadresse ein")
            ),
            "date" => array(
                "regex" => "/^[0-9]{4}\\-\\[0-9]{1,2}\\-\\[0-9]{1,2}$/",
                "alertText" => __("Ungültges Datum")
            ),
            "onlyNumber" => array(
                "regex" => "/^[0-9\\ ]+$/",
                "alertText" => __("Bitte geben Sie nur Zahlen ein")
            ),
            "noSpecialCaracters" => array(
                "regex" => "/^[0-9a-zA-Z]+$/",
                "alertText" => __("Bitte geben Sie keine Sonderzeichen ein.")
            ),
            "onlyLetter" => array(
                "regex" => "/^[a-zA-Z\\ \\']+$/",
                "alertText" => __("Bitte geben Sie nur Buchstaben ein")
            ),
            "validate2fields" => array(
                "nname" => "validate2fields",
                "alertText" => __("* You must have a firstname and a lastname")
            ),
            "validateplz" => array(
                "nname" => "validateplz",
                "alertText" => __("Diese PLZ existiert nicht")
            ),
            "plz" => array(
                "regex" => "/^[0-9]{4,5}/",
                "alertText" => __("Diese PLZ existiert nicht")
            ),
            "title" => array(
                "regex" => "none",
                "alertText" => __("Bitte geben Sie einen Titel ein")
            ),
            "scholzhh" => array(
                "regex" => "/^[0-9]{5}/",
                "alertText" => __("Bitte geben Sie genau 5 Zahlen ein.")
            ),
            "scholzhhtext" => array(
                "regex" => "/^([a-zA-Z0-9!@#$%^*()-_=+;:'?/{}]{5,50})$/",
                "alertText" => __("Bitte geben Sie einen Text ein.")
            ),
            "floorfee" => array(
                "regex" => "/^[0-9]{1,2}/",
                "alertText" => __("Bitte geben Sie eine Etage an.")
            ),
            "privacy" => array(
                "regex" => "none",
                "alertTextCheckboxe" => __("Bitte bestätigen Sie die Datenschutzbestimmungen")
            ),
            "payment" => array(
               "regex" => "none",
               "alertTextCheckboxMultiple" => __("Bitte wählen Sie eine Zahlungsmethode aus.")
            ),
            "yd-payment-bar" => array(
               "regex" => "none",
               "alertTextCheckboxMultiple" => __("Bitte wählen Sie eine Zahlungsmethode aus.")
            ),
            "paymentAddition" => array(
               "regex" => "none",
               "alertTextCheckboxMultiple" => __("Bitte wählen Sie eine Zahlungsmethode aus.")
            )
                ));
        if (!is_dir(APPLICATION_PATH . "/../public/cache/json")) {
            mkdir(APPLICATION_PATH . "/../public/cache/json");
        }
        file_put_contents(APPLICATION_PATH . "/../public/cache/json/validation.json", $json);
        echo $json;
    }
    
    public function couponAction(){}
    public function howitworksAction(){}
    public function postalrequestAction(){}

}
