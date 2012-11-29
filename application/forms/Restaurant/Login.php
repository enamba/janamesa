<?php

class Yourdelivery_Form_Restaurant_Login extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'user', array(
            'label' => __b('E-Mail'),
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        
        $this->getElement('user')->getValidator('NotEmpty')
            ->setMessage(__b('Bitte geben Sie eine gültige E-Mail-Adresse ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('password', 'pass', array(
            'label' => __b('Passwort'),
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        
        $this->getElement('pass')->getValidator('NotEmpty')
            ->setMessage(__b('Bitte geben Sie Ihr Passwort ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
        
        $this->addElement('text', 'restaurantId', array(
            'label' => __b('RestaurantID'),
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        
        $this->getElement('restaurantId')->getValidator('NotEmpty')
            ->setMessage(__b('Bitte geben Sie Ihre RestaurantID ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
        
        $this->addElement('submit', __b('Anmelden'));
        
        $this->setAction('/restaurant/login');
        
    }
}