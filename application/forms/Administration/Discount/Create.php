<?php

class Yourdelivery_Form_Administration_Discount_Create extends Yourdelivery_Form_Administration_Discount_Base {

    public function init() {
        parent::init();
        $this->addElement('text', 'number', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte geben Sie die Anzahl der Codes ein"))
                )
            )
        ));
        
        $this->addElement('text', 'fakeCode', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));
                
        $this->addElement('text', 'referer', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'Db_NoRecordExists', 'options' =>
                    array('table' => 'rabatt', 'field' => 'referer', 'messages' =>
                        __b("Diese URL ist bereits bei einer anderen Rabattaktion eingetragen")))
            )
        ));

    }
}
