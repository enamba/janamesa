<?php
/**
 * @author alex
 * @since 03.05.2011
 */
class Yourdelivery_Form_Administration_Salechannel_Editcost extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'saleChannel', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen saleChannel ein"))
                )
            )
        ));

        $this->addElement('text', 'subSaleChannel', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'cost', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'name', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'fromTimeD', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'fromTimeT', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'untilTimeD', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'untilTimeT', array(
            'filters'    => array('StringTrim'),
        ));
    }
}