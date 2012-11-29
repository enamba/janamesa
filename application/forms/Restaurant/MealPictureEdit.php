<?php
/**
 * Form for meal picture editing
 *
 * @author alex
 * @since 14.12.2010
 */
class Yourdelivery_Form_Restaurant_MealPictureEdit extends Default_Forms_Base {

    public function init() {
        $this->addElement('file', 'img', array(
            'required'   => true,
            'validators'    => array(
                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,jpeg,gif,png'))
            )
        ));

        $this->addElement('text', 'mealId', array(
            'filters'    => array('StringTrim'),
        ));

        $this->getElement('img')->getValidator('Extension')
             ->setMessage(__b('Falsches Dateiformat'), Zend_Validate_File_Extension::FALSE_EXTENSION);

        if (APPLICATION_ENV == "production") {
            $this->getElement('img')->getValidator('Size')
                 ->setMessage(__b('Unzulässige Größe der Datei'), Zend_Validate_File_Size::TOO_BIG);
        }
    }
}
