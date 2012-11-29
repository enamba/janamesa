<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Form for service creation
 *
 * @author vait
 */
class Yourdelivery_Form_Administration_Newsletter_Recipients extends Default_Forms_Base {


    public function init() {

        $this->addElement('file', 'csv', array(
            'validators'    => array(
                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'csv'))
            )
        ));
    }
}
