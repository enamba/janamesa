<?php

/**
 * Data of the partner - email and mobile phone number
 *
 * @author Alex Vait
 * @since 31.07.2012
 */
class Yourdelivery_Form_Partner_Data extends Default_Forms_Base{
    
    /**
     * @author Alex Vait
     * @since 31.07.2012
     */    
    public function init() {

        // email
        $this->initEmail();
        $this->getElement('email')
            ->setLabel(__p('Email'));
        
        $this->initMobile(false);
        $this->getElement('mobile')
            ->setLabel(__p('Mobilnummer'));
        
        $this->addElement('submit', 'speichern', array(
            'required' => false,
            'label' => __p('Speichern'),
            'filters' => array('StringTrim')
        ));        
    }

}
