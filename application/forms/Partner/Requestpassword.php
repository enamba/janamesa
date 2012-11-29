<?php

/**
 * Enter partner email or mobile number to which the new password will be send
 *
 * @author Alex Vait
 * @since 02.08.2012
 */

class Yourdelivery_Form_Partner_Requestpassword extends Default_Forms_Base {

    /**
     * @author Alex Vait
     * @since 02.08.2012
     */
    public function init() {

        $this->addElement('text', 'customerNr', array(
            'label' => __p('Kundennummer'),
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->getElement('customerNr')->getValidator('NotEmpty')
            ->setMessage(__p('Bitte geben Sie Ihre Kundennummer ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        // no email validator, because this string will be compared to the email in the database, so either the strings are not the same
        // or the email in the database is in wrong format and here will be not the right place to correct it
        $this->addElement('text', 'email', array(
            'label' => __p('E-Mail'),
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('email')->getValidator('NotEmpty')
            ->setMessage(__p('Bitte geben Sie eine gültige E-Mail-Adresse ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'mobile', array(
            'label' => __p('Mobilnummer'),
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('submit', __p('Anfordern'));

        $this->setActionRoute(array('action' => 'requestpassword'), 'partnerRoute');

    }
}