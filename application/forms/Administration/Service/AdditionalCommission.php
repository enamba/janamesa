<?php
/**
 * Form for service creation
 * @author vait
 */
class Yourdelivery_Form_Administration_Service_AdditionalCommission extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'restaurantId', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Kein Restaurant wurde definiert"))
                )
            )
        ));

        $this->addElement('text', 'startTimeD', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Keine Anfangszeit wurde definiert"))
                )
            )
        ));

        $this->addElement('text', 'endTimeD', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Keine Endzeit wurde definiert"))
                )
            )
        ));

        $this->addElement('text', 'komm', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'fee', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'item', array(
            'filters'    => array('StringTrim'),
        ));
    }
}
