<?php
/**
 * New Address Form
 * @author vpriem
 * @since 16.03.2011
 */
class Yourdelivery_Form_NewAddress extends Default_Forms_Base {

    /**
     * @author vpriem
     * @since 16.03.2011
     */
    public function init() {
        
        $config = Zend_Registry::get('configuration');
        $minHouseNoLength = $config->locale->housenumber->min;
        $maxHouseNoLength = $config->locale->housenumber->max;
        
        // street
        $this->addElement('text', 'street', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'               
            )
        ));
        $this->getElement('street')->getValidator('NotEmpty')
            ->setMessage(__('Bitte gib eine Straße ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
       
        
        // hasunr
        $this->addElement('text', 'hausnr', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array($minHouseNoLength, $maxHouseNoLength))
            )
        ));
        $this->getElement('hausnr')->getValidator('StringLength')
            ->setMessage(__('Die Hausnummer ist zu lang. (max. %d Zeichen)', $maxHouseNoLength), Zend_Validate_StringLength::TOO_LONG);
        $this->getElement('hausnr')->getValidator('StringLength')
            ->setMessage(__('Die Hausnummer ist zu kurz. (min. %d Zeichen)', $minHouseNoLength), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('hausnr')->getValidator('NotEmpty')
            ->setMessage(__('Bitte gib eine Hausnummer ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        // cityId
        $this->addElement('text', 'cityId', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'Db_RecordExists', 'options' => array('city', 'id'))
            )
        ));
        $this->getElement('cityId')->getValidator('NotEmpty')
            ->setMessage(__('Bitte gib eine PLZ ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('cityId')->getValidator('Db_RecordExists')
            ->setMessage(__('Diese PLZ ist nicht korrekt.'), Zend_Validate_Db_NoRecordExists::ERROR_NO_RECORD_FOUND);

        // plz
        $this->addElement('text', 'plz', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('plz')->getValidator('NotEmpty')
            ->setMessage(__('Bitte gib eine PLZ ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        // tel
        $this->addElement('text', 'tel', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                 array('validator' => 'StringLength', 'options' => array(7,25))
            )
        ));
        $this->getElement('tel')->getValidator('NotEmpty')
            ->setMessage(__('Bitte gib eine Telefonnummer ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
         $this->getElement('tel')->getValidator('StringLength')
            ->setMessage(__('7 bis 25 Zeichen erlaubt'), Zend_Validate_StringLength::TOO_SHORT);
        // companyNmae
        $this->addElement('text', 'companyName', array(
            'filters'    => array('StringTrim')
        ));

        // etage
        $this->addElement('text', 'etage', array(
            'filters'    => array('StringTrim')
        ));

        // comment
        $this->addElement('text', 'comment', array(
            'filters'    => array('StringTrim')
        ));
 
    }

}