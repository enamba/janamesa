<?php

/**
 * Mobile tel of the partner restaurant
 *
 * @author Alex Vait <vait@lieferando.de>
 * @since 31.07.2012
 */
class Yourdelivery_Form_Partner_Mobile extends Default_Forms_Base {
    
    /**
     * @author Alex Vait
     * @since 31.07.2012
     */    
    public function init() {

        $this->initMobile()
            ->setLabel(__p('Mobilnummer'))
            ->getValidator('NotEmpty')
            ->setMessage(__p('Bitte geben Sie eine Mobilnummer ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
        
        $this->addElement('text', 'mobileConfirm', array(
            'required' => true,
            'label' => __p("Neue Mobilnummer wiederholen"),
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __p("Bitte bestätigen Sie Ihre Mobilnummer."),
                )),
                array('Identical', false, array(
                    'token' => 'mobile',
                    'messages' => __p("Die Mobilnummern stimmen nicht überein."),
                )),
            ),
        ));
        
        $this->addElement('hidden', 'type', array(
            'value' => 'mobile',
        ));

        $this->addElement('submit', 'absenden', array(
            'required' => false,
            'label' => __p('Ändern'),
            'filters' => array('StringTrim'),
        ));
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 29.08.2012
     * @return boolean
     */
    public function isValid($data) {

         $this->getElement('mobileConfirm')
            ->getValidator('Identical')
            ->setToken($data['mobile']);

         return parent::isValid($data);
    }

}
