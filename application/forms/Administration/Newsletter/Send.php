<?php
/**
 * Form for Newsletter Send
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class Yourdelivery_Form_Administration_Newsletter_Send extends Default_Forms_Base {


    public function init() {

        $this->addElement('text', 'html', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Der Quellcode ist leer"))
                )
            )
        ));

        $this->addElement('text', 'campaign', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Kampagne ein"))
                )
            )
        ));

        $this->addElement('text', 'subject', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Betreff ein"))
                )
            )
        ));

        $this->addElement('text', 'replacement', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'patternname', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('select', 'recipients[]', array(
            'filters' => array('StringTrim')
        ));
    }
}
