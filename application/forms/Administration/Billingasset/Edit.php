<?php

class Yourdelivery_Form_Administration_Billingasset_Edit extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'companyId', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte wählen Sie eine Firma"))
                )
            )
        ));

        $this->addElement('text', 'restaurantId', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'courierId', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'departmentId', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'projectnumberId', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'total', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Betrag ein"))
                )
            )
        ));

        $this->addElement('text', 'mwst', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'fee', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'timeFrom', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'timeUntil', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'description', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'brutto-checkbox', array(
            'filters'    => array('StringTrim'),
        ));
    }

}