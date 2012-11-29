<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 02.05.2011
 */
class Yourdelivery_Form_Info_Proposal extends Default_Forms_Base {

    public function init() {
        $this->addElement('text', 'service', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array(3, 50))
            )
        ));
        $this->getElement('service')->getValidator('NotEmpty')
                ->setMessage(__('Bitte geben Sie einen Restaurantnamen an.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('service')->getValidator('StringLength')
                ->setMessage(__('Der Restaurantname ist zu kurz. (mind. 3 Zeichen)'), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('service')->getValidator('StringLength')
                ->setMessage(__('Der Restaurantname ist zu lang. (max. 50 Zeichen)'), Zend_Validate_StringLength::TOO_LONG);

        $this->addElement('text', 'street', array(
            'required' => false
        ));

        $this->addElement('text', 'telefon', array(
            'required' => false
        ));

        $this->addElement('hidden', 'category', array(
            'required' => false
        ));

        $this->addElement('text', 'ort', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('ort')->getValidator('NotEmpty')
                ->setMessage(__('Bitte geben Sie einen Ort an.'), Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'name', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(3, 50))
            )
        ));
        $this->getElement('name')->getValidator('StringLength')
                ->setMessage(__('Der Name ist zu kurz. (mind. 3 Zeichen)'), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('name')->getValidator('StringLength')
                ->setMessage(__('Der Name ist zu lang. (max. 50 Zeichen)'), Zend_Validate_StringLength::TOO_LONG);

    }

}