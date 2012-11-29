<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * form for editing a company
 *
 * @author vait
 */
class Yourdelivery_Form_Administration_Company_Edit extends Default_Forms_Base {


    public function init() {

        $config = Zend_Registry::get('configuration');
        
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

        $this->addElement('text', 'steuerNr', array(
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

        $this->addElement('text', 'billInterval', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'status', array(
            'required'   => false,
            'filters'    => array('StringTrim')
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

        // for Brasil we need the plz input, for other domains we need a cityId
        if (strpos($config->domain->base, "janamesa") !== false) {        
            $this->addElement('text', 'plz', array(
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('NotEmpty', false, array(
                            'messages' => __b("Bitte geben Sie eine PLZ ein"))
                    )
                )
            ));
        }
        else {
            $this->addElement('text', 'cityId', array(
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('NotEmpty', false, array(
                            'messages' => __b("Bitte wählen Sie eine PLZ aus"))
                    )
                )
            ));            
        }

        $this->addElement('text', 'comment', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'agb', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'serviceListMode', array(
            'filters'    => array('StringTrim'),
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

        $this->addElement('text', 'billDeliver', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('checkbox', 'debit', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
    }

}
