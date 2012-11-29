<?php
/**
 * Yourdelivery_Form_Administration_Email_Test
 * @author vpriem
 * @since
 */
class Yourdelivery_Form_Administration_Email_Test extends Default_Forms_Base{

    public function init(){
        
        $this->addElement('text', 'to', array(
            'required' => true,
            'filters'  => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Das Feld 'Empfänger' wurde nicht ausgefüllt"))),
            )
        ));

        $this->addElement('text', 'subject', array(
            'required' => true,
            'filters'  => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Das Feld 'Betreff' wurde nicht ausgefüllt"))),
            )
        ));

        $this->addElement('text', 'text', array(
            'filters'  => array('StringTrim'),
        ));

        $this->addElement('text', 'html', array(
            'filters'  => array('StringTrim'),
        ));

    }

}