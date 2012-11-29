<?php

class Yourdelivery_Form_Info_Contact extends Default_Forms_Base {

    public function init() {

        $this->addElement('select', 'mailto', array(
            'registerInArrayValidator' => false
        ));

        parent::initName();
        
        parent::initEmail(true, true, true, false);
        
        $this->addElement('text', 'message', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('message')->getValidator('NotEmpty')
                ->setMessage(__('Bitte geben Sie eine Nachricht ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'tel', array(
            'required' => false
        ));

        $this->addElement('text', 'comp', array(
            'required' => false
        ));

        $this->addElement('text', 'ort', array(
            'required' => false
        ));
    }

}