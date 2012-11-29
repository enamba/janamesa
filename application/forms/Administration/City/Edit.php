<?php
/**
 * @author alex
 * @since 11.04.2011
 */
class Yourdelivery_Form_Administration_City_Edit extends Default_Forms_Base {
    /**
     * @author alex
     * @since 11.04.2011
     * @return void
     */
    public function init() {

        $this->addElement('text', 'city', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Stadt ein"))
                )
            )
        ));
        
        $this->addElement('text', 'restUrl', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
        
        $this->addElement('text', 'caterUrl', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
        
        $this->addElement('text', 'greatUrl', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'state_stateId', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bundesland fehlt!"))
                )
            )
        ));
        
        $this->addElement('text', 'parentCityId', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
        
        $this->addElement('checkbox', 'assembleurls', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        )); 
        
    }
}