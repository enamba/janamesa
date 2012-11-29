<?php
/**
 * Form for creating/editing service category
 *
 * @author alex
 */
class Yourdelivery_Form_Administration_Service_Category_Edit extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Namen für die Kategorie ein"))
                )
            )
        ));

        $this->addElement('text', 'description', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));
    }
}
