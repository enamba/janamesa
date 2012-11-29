<?php

/**
 * Description of Response
 *
 * @author mpantar,afrank
 * @since 28.3.2011
 */
class Yourdelivery_Form_Testing_Add extends Zend_Form {

    public function init() {


        $this->addElement('text', 'mission', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('mission')->getValidator('NotEmpty')
                ->setMessage('<br />Bitte geben Sie einen Auftrag ein.', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('file', 'imagePath', array(
            'required' => false
        ));

        $this->addElement('text', 'expectation', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
            )
        ));
        $this->getElement('expectation')->getValidator('NotEmpty')
                ->setMessage('<br />Bitte geben Sie eine Erwartung ein.', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'description', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));
    }

}

?>
