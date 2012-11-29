<?php

/**
 * Description of FinishNotRegistered
 *
 * @author mlaug
 */
abstract class Yourdelivery_Form_Order_Finish_Abstract extends Default_Forms_Base {

    public function customer() {

        $this->addElement('text', 'prename', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'name', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
    }

    public function contact() {

        $this->addElement('text', 'email', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty', 'EmailAddress'
            )
        ));

        $this->getElement('email')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID_FORMAT);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID_HOSTNAME);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID_LOCAL_PART);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID_MX_RECORD);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Lokale Emailadressen sind nicht gestattet'), Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Unbekannte Emailadress-Endung'), Zend_Validate_Hostname::UNKNOWN_TLD);


        $this->addElement('text', 'telefon', array(
            'required' => true,
            'filters' => arraY('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
    }

    public function location() {

        $this->addElement('text', 'street', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'hausnr', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'appartment', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));
    }

    public function additional() {
        $this->addElement('text', 'discount', array(
            'required' => false,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'deliver-time', array());
        $this->addElement('text', 'deliver-time-day', array());

        $this->addElement('text', 'etage', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'companyName', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'comment', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('text', 'cpf', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.12.2011
     */
    public function payment() {
        $this->addElement('text', 'payment', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array(
                    'validator' => 'InArray',
                    'options' => array(
                        Yourdelivery_Helpers_Payment::getAllowedPayments()
                    )
                )
            )
        ));

        $this->getElement('payment')->setErrorMessages(
                array(
                    Zend_Validate_InArray::NOT_IN_ARRAY =>
                    __('Die gewählte Bezahlmethode steht nicht zur Verfügung')
                )
        );
    }

}

