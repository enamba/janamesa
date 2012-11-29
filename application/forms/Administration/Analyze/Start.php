<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Form for analyze uploaded sales data
 *
 * @author abriliano
 */
class Yourdelivery_Form_Administration_Analyze_Start extends Default_Forms_Base {


    public function init() {

        
        $this->addElement('file', 'file', array(
            'required'   => false,
            'validators'    => array(

                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Size', 'options' => array(false, '100MB')),
                array('validator' => 'Extension', 'options' => array(false, false, 'csv,xls,xlsx'))
            )
        ));


    }
}
