<?php

/**
 * Data for resetting partner password
 *
 * @author Alex Vait
 * @since 31.07.2012
 */
class Yourdelivery_Form_Partner_Resetpassword extends Default_Forms_Base{

    /**
     * @author Alex Vait
     * @since 02.08.2012
     */    
     public function init() {

        $this->initSetpassword(false);

        $this->addElement('submit','absenden' , array(
            'required' => false,
            'label' => __('Setzen'),
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
