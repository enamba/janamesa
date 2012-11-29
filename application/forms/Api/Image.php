<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Image
 *
 * @author mlaug
 */
class Yourdelivery_Form_Api_Image extends Default_Forms_Base {

    public function init() {
        $this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
        $logo = $this->createElement('file', 'img');
        $logo->setMaxFileSize(5242880) // 5mb
                ->addValidator('Count', false, 1)
                ->addValidator('Size', false, array('max' => '5242880'))
                ->addValidator('Extension', false, array('jpg', 'jpeg'));
        $this->addElement($logo);
    }

}

?>
