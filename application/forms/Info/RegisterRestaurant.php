<?php
/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 25.05.2011
 */
class Yourdelivery_Form_Info_RegisterRestaurant extends Default_Forms_Base {

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

        
         $this->addElement('text', 'name', array(
            'required' => true,
             'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty')
        ));
         $this->getElement('name')->getValidator('NotEmpty')
                ->setMessage(__('Bitte geben Sie Ihren Namen an.'), Zend_Validate_NotEmpty::IS_EMPTY);
        
       $this->addElement('text', 'street', array(
            'required' => true,
             'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty')
        ));
         $this->getElement('street')->getValidator('NotEmpty')
                ->setMessage(__('Bitte geben Sie Ihre Straße an.'), Zend_Validate_NotEmpty::IS_EMPTY);
         
         $this->addElement('text', 'ort', array(
            'required' => true,
             'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty')
        ));
         $this->getElement('ort')->getValidator('NotEmpty')
                ->setMessage(__('Bitte geben Sie einen Ort an.'), Zend_Validate_NotEmpty::IS_EMPTY);
        
         
        $this->addElement('text', 'telefon', array(
            'required' => false
        ));
        
        $this->addElement('text', 'mobil', array(
            'required' => false
        ));
        
        $this->addElement('text', 'email', array(
            'required' => false
        ));
        
        
        $this->addElement('text', 'contacttime', array(
            'required' => false
        ));
    }

}