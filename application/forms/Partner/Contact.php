<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Yourdelivery_Form_Contact
 *
 * @author daniel
 */
class Yourdelivery_Form_Partner_Contact extends Default_Forms_Base{
    //put your code here
    public function init() {

        // subject
        $this->addElement('select','subject' , array(
            'required' => true,
            'label' => __p('Betreff'),
            'multiOptions' => array(__p('Bitte um Rückruf'), __p('Änderung meiner Daten'),__p('Öffnungszeiten'),__p('Kartenänderung'), __p('Sonstiges')),
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __p("Bitte wählen Sie den Betreff aus"))
                )
            )
        ));

        // subject
        $this->addElement('textarea','message' , array(
            'required' => true,
            'label' => __p('Nachricht'),
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __p("Bitte geben Sie eine Nachricht ein"))
                )
            )
        ));


        // subject
        $this->addElement('file','attachment' , array(
            'required' => false,
            'label' => __p('Anhang'),
            'filters' => array('StringTrim'),
            'validators' => array(
                 array('validator' => 'Size', 'options' => array(false, 10485760))
            )
        ));

        $this->addElement('submit','absenden' , array(
            'required' => false,
            'label' => __p('Kontaktanfrage abschicken'),
            'filters' => array('StringTrim')
        ));

    }


}

?>
