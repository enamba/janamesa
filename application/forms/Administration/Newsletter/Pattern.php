<?php
/**
 * Yourdelivery_Form_Administration_Email_Test
 * @author vpriem
 * @since
 */
class Yourdelivery_Form_Administration_Newsletter_Pattern extends Default_Forms_Base{

    public function init(){
        
         $this->addElement('file', 'patternupload', array(
            'validators'    => array(
                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'html,htm'))
            )
        ));

    }

}