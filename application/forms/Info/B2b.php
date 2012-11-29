<?php
/**
 * @author Allen Frank <frank@lieferando.de>
 * @since 03.08.2011
 */
class Yourdelivery_Form_Info_B2b extends Default_Forms_Base {
  
    public function init() {

        parent::initName();
        
        parent::initPrename();
        
        parent::initEmail(true, true, true, false);


        $this->addElement('text', 'comp', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
                )
        ));
        $this->getElement('comp')->getValidator('NotEmpty')
                ->setMessage(__('Firmen-Name darf nicht leer sein.'), Zend_Validate_NotEmpty::IS_EMPTY);


        $this->addElement('text', 'branch', array(
            'required' => false
        ));
    }
}

?>
