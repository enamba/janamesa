<?php

/**
 * Description of Response
 *
 * @author mpantar,afrank
 * @since 28.3.2011
 */
class Yourdelivery_Form_Testing_Response extends Zend_Form {

    public function init() {


      
        $this->addElement('text', 'executor', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('executor')->getValidator('NotEmpty')
                ->setMessage('Please enter a name.<br />', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'response', array(
            'required' => false
        ));

         $this->addElement('checkbox', 'responseCheckbox', array(
            'required' => false,
            'checkedValue' => 1
        ));


    }
}
?>
