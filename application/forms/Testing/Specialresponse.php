<?php

/**
 * Description of a special response
 *
 * @author Allen Frank <frank@lieferando.de>
 * @since 03.01.12
 */
class Yourdelivery_Form_Testing_Specialresponse extends Yourdelivery_Form_Testing_Response {

    public function init() {
        parent::init();
        $this->addElement('file', 'imagePath', array(
            'required' => false
        ));
        $this->getElement('response')->setRequired(true);
        $this->getElement('response')->setValidators(array('NotEmpty'));
        $this->getElement('response')->getValidator('NotEmpty')
                ->setMessage('Response cannot be null.<br />', Zend_Validate_NotEmpty::IS_EMPTY);
    }

}

?>
