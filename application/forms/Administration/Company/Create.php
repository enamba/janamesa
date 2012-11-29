<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Create
 *
 * @author mlaug
 */
class Yourdelivery_Form_Administration_Company_Create extends Default_Forms_Base {

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
        
        $this->addElement('text', 'industry', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'website', array(
            'required'   => false,
            'filters'    => array('StringTrim')
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
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte wählen Sie eine PLZ aus"))
                )
            )
        ));

        $this->addElement('text', 'comment', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('select', 'selContactId', array(
            'registerInArrayValidator'    => false
        ));

        $this->addElement('text', 'contact_name', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        
        $this->addElement('text', 'contact_prename', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'contact_street', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'contact_hausnr', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'contact_cityId', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'contact_email', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'EmailAddress', 'NotEmpty'
            )
        ));

        $this->addElement('text', 'contact_tel', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'contact_fax', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'contact_position', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'selBillingContactId', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'bill_name', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'bill_prename', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'bill_positon', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'bill_street', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'bill_hausnr', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'bill_cityId', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'bill_email', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'EmailAddress', 'NotEmpty'
            )
        ));

        $this->addElement('text', 'bill_tel', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'bill_fax', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'agb', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('checkbox', 'bill_as_contact',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('text', 'ktoName', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoNr', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoBlz', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'steuerNr', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'billDeliver', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('checkbox', 'debit', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('text', 'billInterval', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));
    }
}
