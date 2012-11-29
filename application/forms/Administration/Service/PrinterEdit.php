<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Form for creating/editing GPRS printer for restaurants
 * @author alex
 * @since 18.10.2011
 *
 */
class Yourdelivery_Form_Administration_Service_PrinterEdit extends Default_Forms_Base {
    /**
     * Soft mode boolean flag - if true, SIM/PUK fields are no longer required (useful for Poland)
     * @var boolean
     */
    protected $softMode;

    /**
     * Form constructor - extended by soft mode flag setting
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 28.05.2012
     *
     * @param boolean $softMode
     * @param array $fields
     * @param mixed $options
     */
    public function __construct($softMode = false, array $fields = null, $options = null) {
        $this->softMode = $softMode;
        parent::__construct($fields, $options);

    }

    public function init() {

        $this->addElement('text', 'id', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine ID ein"))
                )
            )
        ));

        $this->addElement('select', 'type', array(
            'value' => 'topup',
            'multiOptions' => array('topup' => __b('Topup Drucker'), 'wiercik' => __b('Wiercik Drucker (Polen)'))
        ));

        $this->addElement('text', 'serialNumber', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Seriennummer ein"))
                )
            )
        ));

        $this->addElement('text', 'simNumber', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine SIM Nummer ein"))
                )
            )
        ));

        $this->addElement('text', 'simPin1', array(
            'required'   => !$this->softMode,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine SIM PIN1 ein"))
                )
            )
        ));

        $this->addElement('text', 'simPin2', array(
            'required'   => !$this->softMode,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine SIM PIN2 ein"))
                )
            )
        ));

        $this->addElement('text', 'simPuk1', array(
            'required'   => !$this->softMode,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine SIM PUK1 ein"))
                )
            )
        ));

        $this->addElement('text', 'simPuk2', array(
            'required'   => !$this->softMode,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine SIM PUK2 ein"))
                )
            )
        ));

        $this->addElement('checkbox', 'notify',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0,
        ));
        
        $this->addElement('text', 'stateId', array(
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
    }
}
