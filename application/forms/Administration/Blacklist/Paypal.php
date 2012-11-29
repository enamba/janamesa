<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Paypal
 *
 * @author daniel
 */
class Yourdelivery_Form_Administration_Blacklist_Paypal extends Default_Forms_Base {

    protected $defaults = array();


    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since14.06.2012
     * @return type 
     */
    public function init() {
               
        
        
                
        $this->addElement('text', 'bl_paypal_email', array(
            'filters' => array('StringTrim'),     
            'value' => $this->defaults['bl_paypal_email'] ,
            'required' => true,
            'label' => __b('Paypal Email'),
            'validators' => array(
                'NotEmpty',
                'EmailAddress'
            ),
            'Attribs' => array(
                'class' => "yd-empty-text",
                'title' => __b("Paypal Email"),
            )
        ));
                
                
        $this->addElement('text', 'bl_payerId', array(
            'filters' => array('StringTrim'),
         
            'required' => true,
            'label' => __b('Paypal PayerId'),
            'validators' => array(
                'NotEmpty'
            ),
            'Attribs' => array(
                'class' => "yd-empty-text",
                'title' => __b("Paypal PayerId"),
            )
        ));
        
        $this->addElement('text', 'bl_orderId', array(
            'filters' => array('StringTrim'),          
            'required' => false,
            'label' => __b('Bestell Id')   ,
            'Attribs' => array(
                'class' => "yd-empty-text",
                'title' => __b("Bestell Id"),
            ) 
        ));
        
        $this->addElement('select', 'bl_behaviour', array(
            'filters' => array('StringTrim'),
            'multioptions' => array(Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_BLACKLIST => __b('Blacklist'),
                                                 Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_WHITELIST => __b('Whitelist')
                                                ) 
        ));
        
        
        $this->addElement('textarea', 'bl_comment', array(
            'filters' => array('StringTrim'),            
            'required' => true,
            'label' => __b('Begründung'),
            'validators' => array(
                'NotEmpty'
            ),
            'Attribs' => array(
                'class' => "yd-empty-text",
                'title' => __b("Wieso Blacklist?"),
            ) 
        ));
        
        
        $this->addElement('checkbox', 'bl_cancelorder', array(
            'label' => __b('Bestellung stornieren?'),
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
        
        $this->addElement('submit', 'save', array(
            'label' => __b('Speichern'),
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        
    }

    public function isValid($data) {
        
        $data = array_diff($data, $this->defaults);
        return parent::isValid($data);
        
    }


}

?>
