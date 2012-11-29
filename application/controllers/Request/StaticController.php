<?php

/**
 * Description of InfoController
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class Request_StaticController extends Default_Controller_RequestBase {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function obstkorbformAction(){
        $this->getResponse()->setHeader('Content-Type', 'text/xml');

        $this->view->stat = 1;

        $vals = array();

        
        $vals['company'] = $this->getRequest()->getParam('company',null);
        $vals['name'] = $this->getRequest()->getParam('name',null);
        $vals['prename'] = $this->getRequest()->getParam('prename',null);
        $vals['email'] = $this->getRequest()->getParam('email',null);
        $vals['tel'] = $this->getRequest()->getParam('tel',null);
        $vals['plz'] = $this->getRequest()->getParam('plz',null);
        $vals['street'] = $this->getRequest()->getParam('street',null);
        
            
        $form = new Yourdelivery_Form_Registerobstkorb();
        


        if(!$form->isValid($vals)){
            
            
            $errorsex = null;
            $errorcompany = null;
            $errorname = null;
            $errorprename = null;
            $erroremail = null;
            $errortel = null;
            $errorpasword = null;
            $erroragb = null;

            $array = $form->getMessages();

            if(!is_null($array[sex])){
                foreach ($array[sex] as $val) {
                    $errorsex .= '<p class="yd-obstkorb-error">'.$val.'</p>';
                }
            }

            if(!is_null($array[company])){
                foreach ($array[company] as $val) {
                    $errorcompany .= '<p class="yd-obstkorb-error">'.$val.'</p>';
                }
            }

            if(!is_null($array[name])){
                foreach ($array[name] as $val) {
                    $errorname .= '<p class="yd-obstkorb-error">'.$val.'</p>';
                }
            }

            if(!is_null($array[prename])){
                foreach ($array[prename] as $val) {
                    $errorprename .= '<p class="yd-obstkorb-error">'.$val.'</p>';
                }
            }

            if(!is_null($array[email])){
                foreach ($array[email] as $val) {
                     $erroremail .= '<p class="yd-obstkorb-error">'.$val.'</p>';
                }
            }

            if(!is_null($array[tel])){
                foreach ($array[tel] as $val) {
                     $errortel .= '<p class="yd-obstkorb-error">'.$val.'</p>';
                }
            }

            if(!is_null($array[street])){
                foreach ($array[street] as $val) {
                    $errorstreet .= '<p class="yd-obstkorb-error">'.$val.'</p>';
                }
            }

            if(!is_null($array[plz])){
                foreach ($array[plz] as $val) {
                     $errorplz .= '<p class="yd-obstkorb-error">'.$val.'</p>';
                }
            }

            $this->view->errors = $errorsex;
            $this->view->errorcompany = $errorcompany;
            $this->view->errorname = $errorname;
            $this->view->errorprename = $errorprename;
            $this->view->erroremail = $erroremail;
            $this->view->errortel = $errortel;
            $this->view->errorstreet = $errorstreet;
            $this->view->errorplz = $errorplz;
            
        }else{
            $this->view->stat = 0;
        }

    }


}

