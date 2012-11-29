<?php

class Yourdelivery_Form_Api_Location extends Yourdelivery_Form_NewAddress {


    public function init() {
        parent::init();
        $this->getElement('tel')->setRequired(false);
        $this->addElement('text', 'primary');
        // primary can only be 0 (false) or 1 (true)
        $this->getElement('primary')->addValidators(array(
            new Zend_Validate_Int(),
            new Zend_Validate_Between(array('min' => 0, 'max' => 1)),
        ));
    }

}
