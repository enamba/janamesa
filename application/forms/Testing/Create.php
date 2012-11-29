<?php

/**
 * Description of Response
 *
 * @author mpantar,afrank
 * @since 28.3.2011
 */
class Yourdelivery_Form_Testing_Create extends Zend_Form {

    public function init() {

        $this->addElement('text', 'title', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('title')->getValidator('NotEmpty')
                ->setMessage('<br />Titel darf nicht leer sein', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'author', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('author')->getValidator('NotEmpty')
                ->setMessage('<br />Autor darf nicht leer sein', Zend_Validate_NotEmpty::IS_EMPTY);
            
        $this->addElement('text', 'description', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));
        
        $this->addElement('text', 'priority', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));
        
        $this->addElement('text', 'tag', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('hidden', 'proceed', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

    }
}

?>
