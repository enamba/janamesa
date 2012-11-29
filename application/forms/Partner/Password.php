<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Password
 *
 * @author daniel
 * @modified Alex Vait <vait@lieferando.de> 02.08.2012
 */
class Yourdelivery_Form_Partner_Password extends Default_Forms_Base{
    //put your code here
     public function init() {

        $this->setActionRoute(array('action' => 'account'), 'partnerRoute');
        $this->setMethod("POST");

        $this->initSetpassword();

        $this->addElement('hidden', 'type', array(
            'value' => 'password'
        ));

        $this->addElement('submit','absenden' , array(
            'required' => false,
            'label' => __('Ändern'),
            'filters' => array('StringTrim')
        ));
     }

     public function isValid($data) {
         $passCheck = $this->getElement('passwordTwo');
         $passCheck->getValidator('Identical')->setToken($data['passwordOne']);

         return parent::isValid($data);

     }

}

?>
