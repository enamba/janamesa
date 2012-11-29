<?php
/**
 * Description of AddDepartment
 *
 * @author mlaug
 */
class Yourdelivery_Form_Company_AddDepartment extends Default_Forms_Base {

    public function init(){

         $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'identNr', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('checkbox', 'billing',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));
    
        #$this->addElement('select', 'department', array(
        #    'registerInArrayValidator'    => false
        #));
    }

}
?>
