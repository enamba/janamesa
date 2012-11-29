<?php

class Yourdelivery_Form_User_Socialnetworks extends Default_Forms_Base {

    public function init() {
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $this->addElement('checkbox', 'facebookId', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
        $this->addElement('checkbox', 'facebookPost', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

    }
}