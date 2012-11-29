<?php


class Yourdelivery_Form_Company_Projectnumber extends Default_Forms_Base{

    public function init(){

         $this->addElement('text', 'projectnumber', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'comment', array(
            'filters'    => array('StringTrim')
        ));

        #$this->addElement('text', 'intern', array(
        #    'required'   => true,
        #    'filters'    => array('StringTrim'),
        #    'validators' => array(
        #        'NotEmpty'
        #    )
        #));

        #$this->addElement('text', 'department', array(
        #    'required'   => true,
        #    'filters'    => array('StringTrim'),
        #    'validators' => array(
        #        'NotEmpty'
        #    )
        #));
    }

}