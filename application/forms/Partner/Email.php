<?php

/**
 * @author Daniel Hahn <hahn@lieferando.de>
 */
class Yourdelivery_Form_Partner_Email extends Default_Forms_Base {
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 29.08.2012
     * @return boolean
     */
    public function init() {

        $this->setActionRoute(array('action' => 'account'), 'partnerRoute');
        $this->setMethod("POST");

        $this->initEmail(true, true, true, false, false, null, true)
            ->setLabel(__p('E-Mail-Adresse'));

        $this->addElement('text', 'emailConfirm', array(
            'required' => true,
            'label' => __p("Neue E-Mail-Adresse wiederholen"),
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __p("Bitte bestätigen Sie Ihre E-Mail-Adresse."),
                )),
                array('Identical', false, array(
                    'token' => 'email',
                    'messages' => __p("Die E-Mail-Adressen stimmen nicht überein."),
                )),
            ),
        ));
        
        $this->addElement('hidden', 'type', array(
            'value' => 'email',
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

         $this->getElement('emailConfirm')
            ->getValidator('Identical')
            ->setToken($data['email']);

         return parent::isValid($data);
    }
    
}
