<?php
/**
 * Email management
 * @author avait, fhaferkorn
 */
class Administration_EmailController extends Default_Controller_AdministrationBase{

    /**
     * Show this email in pop up window
     * @author alex
     */
    public function showAction() {
        
        // disable view
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        // get id
        $request = $this->getRequest();
        $id = $request->getParam('id');

        // print email content
        $email = new Yourdelivery_Model_Emails($id);
        echo $email->getContent();
        
    }

    /**
     * Send Test mail
     * @author vpriem
     * @since 16.11.2010
     */
    public function testAction() {
        
        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            // form
            $form = new Yourdelivery_Form_Administration_Email_Test();
            if ($form->isValid($post)) {
                // Using config-based locale during composing and sending e-mail (however, it seems not to be necessary here)
                $this->_restoreLocale();

                // get values
                $values = $form->getValues();

                $email = new Yourdelivery_Sender_Email();
                $email
                    ->addTo($values['to'])
                    ->setSubject($values['subject']);

                if ($values['text']) {
                    $email->setBodyText($values['text']);
                }

                if ($values['html']) {
                    $email->setBodyHtml($values['html']);
                }

                $email->send();
                $this->_overrideLocale();

                // redirect
                $this->success(__b("Email wurde erfolgreich gesendet"));
                $this->_redirect('/administration_email/test');

            }
            else { // error
                $this->error($form->getMessages());
                $this->view->assign('post', $post);
            }

        }
        
    }


}
