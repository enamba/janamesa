<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Fotm for editing company contact
 *
 * @author vait
 */
class Yourdelivery_Form_Administration_Company_ContactEdit extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Namen ein"))
                )
            )
        ));

        $this->addElement('text', 'prename', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Vornamen ein"))
                )
            )
        ));

        $this->addElement('text', 'street', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Strasse ein"))
                )
            )
        ));

        $this->addElement('text', 'hausnr', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Hausnummer ein"))
                )
            )
        ));

        $this->addElement('text', 'cityId', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'email', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Email Adresse ein"))
                ),
                'EmailAddress'
            )
        ));

        $this->addElement('text', 'tel', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'fax', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'position', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

    }
}
