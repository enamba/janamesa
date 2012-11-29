<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NewPassForm
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 */
class Yourdelivery_Form_Request_NewPass extends Default_Forms_Base{
    //put your code here
    public function init() {
        
        $this->addElement('text', 'email', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                'EmailAddress',
                array('validator' => 'Db_RecordExists', 'options' => array('customers', 'email'))
            )
        ));
        
    }
}
?>
