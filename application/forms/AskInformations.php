<?php

class Yourdelivery_Form_AskInformations extends Default_Forms_Base {

    
    public function init() {

            $this->addElement('text', 'street', array(
                'required'   => true,
                'filters'    => array('StringTrim'),
                'validators' => array(
                    'NotEmpty'
                )
            ));

            $this->addElement('text', 'hausnr', array(
                'required'   => true,
                'filters'    => array('StringTrim'),
                'validators' => array(
                    'NotEmpty',
                    array('validator' => 'StringLength', 'options' => array(1, 5))
                )
            ));

            $this->addElement('text', 'plz', array(
                'required'   => true,
                'filters'    => array('StringTrim'),
                'validators' => array(
                    'NotEmpty',
                    array('validator' => 'Db_RecordExists', 'options' => array('orte','plz'))
                )
            ));

            $this->addElement('text', 'comment', array(
                'filters'    => array('StringTrim')
            ));
 
    }

}