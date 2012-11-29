<?php
/**
 * Seo crawler blacklist form
 * @author vpriem
 * @since 08.09.2010
 */
class Yourdelivery_Form_Administration_Seo_Links_Blacklist extends Default_Forms_Base {

    public function init() {
        
        $this->addElement('text', 'blacklist', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Das Feld 'Blacklist' wurde nicht ausgefüllt!"))),
            )
        ));

    }

}