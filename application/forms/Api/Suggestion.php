<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 27.02.2012
 */
class Yourdelivery_Form_Api_Suggestion extends Default_Forms_Base {

    public function init() {

        $minLength = 3;
        $maxLength = 50;

        $this->addElement('text', 'name', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array($minLength, $maxLength))
            )
        ));

        $this->getElement('name')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib einen Namen an.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('name')->getValidator('StringLength')
                ->setMessage(__('Der Name ist zu kurz. (mind. %d Zeichen)', $minLength), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('name')->getValidator('StringLength')
                ->setMessage(__('Der Name ist zu lang. (max. %d Zeichen)', $maxLength), Zend_Validate_StringLength::TOO_LONG);


        $this->addElement('text', 'service', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array($minLength, $maxLength))
            )
        ));
        $this->getElement('service')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib einen Restaurantnamen an.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('service')->getValidator('StringLength')
                ->setMessage(__('Der Restaurantname ist zu kurz. (mind. %s Zeichen)', $minLength), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('service')->getValidator('StringLength')
                ->setMessage(__('Der Restaurantname ist zu lang. (max. %s Zeichen)', $maxLength), Zend_Validate_StringLength::TOO_LONG);



        $this->addElement('text', 'street', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'hausnr', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'comment', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 200))
            )
        ));

        $this->getElement('comment')->getValidator('StringLength')
                ->setMessage(__('Der Kommentar ist zu lang. (max. %s Zeichen)', 200), Zend_Validate_StringLength::TOO_LONG);


        $this->addElement('text', 'ort', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array($minLength, $maxLength))
            )
        ));
        $this->getElement('ort')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib einen Ort an.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('ort')->getValidator('StringLength')
                ->setMessage(__('Der Ort ist zu kurz. (mind. %s Zeichen)', $minLength), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('ort')->getValidator('StringLength')
                ->setMessage(__('Der Ort ist zu lang. (max. %s Zeichen)', $maxLength), Zend_Validate_StringLength::TOO_LONG);
    }

}