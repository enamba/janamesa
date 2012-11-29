<?php
/**
 * @author alex
 * @since 12.07.2011
 */
class Yourdelivery_Form_Administration_Crm_Ticket extends Default_Forms_Base{

    /**
     * Initialize form
     * @author alex
     * @since 12.07.2010
     * @return void
     */
    public function init(){

        $this->addElement('text', 'refType', array(
            'required'   => false,
            'validators' => array(
                'NotEmpty'
            )
        ));
        
        $this->addElement('text', 'refId', array(
            'required'   => false,
            'validators' => array(
                'NotEmpty'
            )
        ));
        
        $this->addElement('text', 'topic', array(
        ));
        
        $this->addElement('text', 'reasonId', array(
        ));
        
        $this->addElement('text', 'message', array(
            'required'   => true,
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte die genaue Erklärung angeben"))
                )
            )
        ));
        
        $this->addElement('checkbox', 'tel', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
        
        $this->addElement('checkbox', 'email', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
        
        $this->addElement('checkbox', 'ticket', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
                        
        $this->addElement('text', 'ticketNr', array(
        ));

        $this->addElement('checkbox', 'closed', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));        
                
        $this->addElement('text', 'assignedToId', array(
        ));

        $this->addElement('text', 'scheduledD', array(
        ));

        $this->addElement('text', 'scheduledT', array(
        ));

        // elements for restaurant status management
        $this->addElement('text', 'status', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'isOnline', array(
            'filters' => array('StringTrim'),
        ));

        // restaurant offline status for batch creation of tickets
        $this->addElement('text', 'offlineStatus', array(
        ));
        
    }
    
}