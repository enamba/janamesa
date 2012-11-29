<?php

/**
 * Form for category picture editing
 *
 * @author vait
 */
class Yourdelivery_Form_Administration_Category_Picture_Edit extends Default_Forms_Base {

    public function init() {

        $this->addElement('file', 'img', array(
            'validators' => array(
                array('validator' => 'Count', 'options' => array(false, 1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,jpeg')),
                array('validator' => 'ImageSize', 'options' => array('minwidth' => 500,'maxwidth' => 1000,'minheight' => 90,'maxheight' => 300))
            )
        ));

        $this->getElement('img')->getValidator('Extension')
             ->setMessage(__b('Falsches Dateiformat'), Zend_Validate_File_Extension::FALSE_EXTENSION);
        
        $this->getElement('img')->getValidator('Size')
             ->setMessage(__b('Unzulässige Größe der Datei'), Zend_Validate_File_Size::TOO_BIG);
        
        $this->getElement('img')->getValidator('ImageSize')
             ->setMessage(__b('Bildbreite zu hoch'), Zend_Validate_File_ImageSize::WIDTH_TOO_BIG);
        
        $this->getElement('img')->getValidator('ImageSize')
             ->setMessage(__b('Bildbreite zu klein'), Zend_Validate_File_ImageSize::WIDTH_TOO_SMALL);
        
        $this->getElement('img')->getValidator('ImageSize')
             ->setMessage(__b('Bildhöhe zu hoch'), Zend_Validate_File_ImageSize::HEIGHT_TOO_BIG);
        
        $this->getElement('img')->getValidator('ImageSize')
             ->setMessage(__b('Bildhöhe zu klein'), Zend_Validate_File_ImageSize::HEIGHT_TOO_SMALL);
    }

}
