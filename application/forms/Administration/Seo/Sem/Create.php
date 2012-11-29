<?php
/**
 * Seo sem form
 * @author vpriem
 * @since 17.09.2010
 */
class Yourdelivery_Form_Administration_Seo_Sem_Create extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'url', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Das Feld 'URL' wurde nicht ausgefüllt!"))),
                array('Regex', false, array("`^[a-z0-9][a-z0-9\-\/]+(\.htm)?$`",
                    'messages' => __b("Die URL ist nicht korrekt")))
            )
        ));

        $this->addElement('text', 'x', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'y', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'background', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'foreground', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'button', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('file', '_background', array(
            'Destination' => APPLICATION_PATH . '/../storage/sem/images/backgrounds',
            'validators' => array(
                array('Count', false, 1),
//                array('IsImage'),
                array('Extension', false, 'jpg,png,gif')
            )
        ));

        $this->addElement('file', '_foreground', array(
            'Destination' => APPLICATION_PATH . '/../storage/sem/images/foregrounds',
            'validators' => array(
                array('Count', false, 1),
//                array('IsImage'),
                array('Extension', false, 'jpg,png,gif')
            )
        ));

        $this->addElement('file', '_button', array(
            'Destination' => APPLICATION_PATH . '/../storage/sem/images/buttons',
            'validators' => array(
                array('Count', false, 1),
//                array('IsImage'),
                array('Extension', false, 'jpg,png,gif')
            )
        ));

    }

}