<?php

class Yourdelivery_Form_User_RateOrder extends Default_Forms_Base {

    public function init() {

        $this->addElement('radio', 'advise', array(
            'registerInArrayValidator' => false,
        ));

        $this->addElement('radio', 'advise', array(
            'required' => true,
            'validators' => array(
                array('validator' => 'InArray', 'options' => array(array(
                            '0' => 0,
                            '1' => 1
                        )
                ))
            )
        ));
        $this->getElement('advise')->setErrorMessages(array(
            Zend_Validate_InArray::NOT_IN_ARRAY =>
            __('Bitte wähle aus, ob Du diesen Lieferdienst Freunden empfehlen würdest')
        ));

        $this->addElement('radio', 'rate-1', array(
            'required' => true,
            'validators' => array(
                array('validator' => 'InArray', 'options' => array(array(
                            '0' => 0,
                            '1' => 1,
                            '2' => 2,
                            '3' => 3,
                            '4' => 4,
                            '5' => 5
                        )
                ))
            )
        ));
        $this->getElement('rate-1')->setErrorMessages(array(
            Zend_Validate_InArray::NOT_IN_ARRAY =>
            __('Bitte wähle eine Bewertung für die Qualität des Essens')
        ));

        $this->addElement('radio', 'rate-2', array(
            'required' => true,
            'validators' => array(
                array('validator' => 'InArray', 'options' => array(array(
                            '0' => 0,
                            '1' => 1,
                            '2' => 2,
                            '3' => 3,
                            '4' => 4,
                            '5' => 5
                        )
                ))
            )
        ));
        $this->getElement('rate-2')->setErrorMessages(array(
            Zend_Validate_InArray::NOT_IN_ARRAY =>
            __('Bitte wähle eine Bewertung für die Lieferung')
        ));


        $this->addElement('text', 'author', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'title', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'comment', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));
    }

}