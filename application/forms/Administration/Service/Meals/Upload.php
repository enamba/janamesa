<?php
/**
 * Form for meal picture editing
 *
 * @author alex
 * @since 14.12.2010
 */
class Yourdelivery_Form_Administration_Service_Meals_Upload extends Default_Forms_Base {

    public function init() {
        $this->addElement('file', 'img', array(
            'required'   => true,
            'validators'    => array(
                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,jpeg,png'))
            )
        ));

        $this->getElement('img')->getValidator('Extension')
            ->setMessage(__b('Falsches Dateiformat (erlaubt: jpg, jpeg, png)'), Zend_Validate_File_Extension::FALSE_EXTENSION);

    }
}
