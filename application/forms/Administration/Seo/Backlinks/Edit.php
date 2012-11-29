<?php
/**
 * Seo backlinks form
 * @author vpriem
 * @since 22.09.2010
 */
class Yourdelivery_Form_Administration_Seo_Backlinks_Edit extends Default_Forms_Base{

    public function init(){

        $this->addElement('text', 'domain', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Das Feld 'Domain' wurde nicht ausgefüllt!")))
            )
        ));

        $this->addElement('text', 'url', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Das Feld 'URL' wurde nicht ausgefüllt!")))
            )
        ));

    }

}