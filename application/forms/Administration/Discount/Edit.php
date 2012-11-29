<?php

class Yourdelivery_Form_Administration_Discount_Edit extends Yourdelivery_Form_Administration_Discount_Base {

    public function init() {
        parent::init();
        
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $rabattId = $request->getParam('id', 0);
        
        $this->addElement('text', 'referer', array(
            'required' => false,
        ));
    }

}
