<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Form for company_employee creation using upload form
 *
 * @author abriliano
 */
class Yourdelivery_Form_Administration_Import_Company_Start extends Default_Forms_Base {


    public function init() {

        $this->addElement('select', 'company', array(
            'registerInArrayValidator'    => false,
            'validators' => array(
                'NotEmpty'
            )
        ));

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
