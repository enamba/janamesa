<?php
/**
 * @author alex
 * @since 11.04.2011
 */
class Yourdelivery_Form_Administration_City_Create extends Default_Forms_Base {
    /**
     * @author alex
     * @since 11.04.2011
     * @return void
     */
    public function init() {

        $this->addElement('text', 'plz', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine PLZ ein"))
                )
            )
        ));

        $this->addElement('text', 'city', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Stadt ein"))
                )
            )
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
        
        $this->addElement('text', 'restUrl', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'Db_NoRecordExists', 'options' => array('table' => 'city', 'field' => 'restUrl', 'messages' => __b("Diese restUrl ist bereits in der Datenbank vorhanden")))
            )
        ));
        
        $this->addElement('text', 'caterUrl', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'Db_NoRecordExists', 'options' => array('table' => 'city', 'field' => 'caterUrl', 'messages' => __b("Diese caterUrl ist bereits in der Datenbank vorhanden")))
            )
        ));        

        $this->addElement('text', 'greatUrl', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'Db_NoRecordExists', 'options' => array('table' => 'city', 'field' => 'greatUrl', 'messages' => __b("Diese greatUrl ist bereits in der Datenbank vorhanden")))
            )
        ));        
    }
}