<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Form for editing ratings in batch process
 *
 * @author Alex Vait <vait@lieferando.de>
 * @since 09.01.2012
 */
class Yourdelivery_Form_Administration_Service_Rating_Editbatch extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'from', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte geben Sie das Anfangsdatum ein"))
                )
            )
        ));

        $this->addElement('text', 'until', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte geben Sie das Enddatum ein"))
                )
            )
        ));

        $this->addElement('text', 'advise', array(
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
    }
}
