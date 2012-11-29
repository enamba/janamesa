<?php

/**
 * Description of InfoController
 *
 * @author mlaug
 */
class InfoController extends Default_Controller_Base {

    public function preDispatch() {
        parent::preDispatch();

        $action = $this->getRequest()->getActionName();
        if ($action == "index") {
            $action = "";
        }
        $meta = array();
        $meta[] = sprintf('<link rel="canoncial" href="http://www.lieferando.de/info/%s" />', $action);
        $this->view->assign('additionalMetatags', $meta);
    }

    public function __call($name, $arguments) {
        $this->_redirect('/info/index');
    }

    public function indexAction() {
        $this->_redirect('/ueber-uns');
    }


    public function impressumAction() {
        $this->_redirect('/about');
    }

    public function kundenAction() {
        $this->_redirect('/kunden');
    }

    public function individuelleloesungAction() {
        $this->_redirect('/firmenloesungen.html');
    }

    public function kostenvorteilAction() {
        $this->_redirect('/firmenloesungen/kostenvorteil');
    }

    public function vorteilefirmaAction() {
        $this->_redirect('/firmenloesungen/vorteile');
    }

    public function kostenrechnerAction() {
        $this->_redirect('/firmenloesungen/rechner');
    }

    public function firmaanmeldenAction() {
        $this->_redirect('/firmenloesungen/anmelden');
    }

    public function kontaktAction() {
        $this->_redirect('/kontakt');
    }

    public function presseAction() {
        $this->_redirect('/presse');
    }
    
    /**
     * sofortueberweisung/b2b will send an email to Flo
     * @author Allen Frank <frank@lieferando.de>
     * @since 02.05.2011
     * @return json
     */

    public function b2bAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form = new Yourdelivery_Form_Info_B2b();
            if ($form->isValid($request->getPost())) {
            $data = $form->getValues();
                
                $body = sprintf('neue Nachricht (b2b) von %s %s // Email: %s // ',$data['prename'], $data['name'], $data['email']).
                        sprintf('Firma: %s // Branche: %s', $data['comp'], $data['branch']);
                $this->logger->info($body);
                
                $email = new Yourdelivery_Sender_Email();
                $email->addTo('eckelt@lieferando.de')
                        ->setSubject('neue Nachricht (b2b)')
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
}
