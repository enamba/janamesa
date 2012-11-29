<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Form for editing rating
 *
 * @author vait
 */
class Yourdelivery_Form_Administration_Service_Rating_Edit extends Default_Forms_Base {


    public function init() {

        $this->addElement('text', 'author', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'title', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'comment', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'delivery', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'quality', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'advise', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

    }
}
