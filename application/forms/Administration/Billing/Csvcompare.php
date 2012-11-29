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
class Yourdelivery_Form_Administration_Billing_Csvcompare extends Default_Forms_Base {

    public function init() {

        $this->setMethod('post');
        $this->setAction('/administration_billing/csvcompare');
        $this->addElement('file', 'csv', array(
            'validators'    => array(
                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Size', 'options' => array(false, 4096000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'csv'))
            )
        ));

        
    }
}
