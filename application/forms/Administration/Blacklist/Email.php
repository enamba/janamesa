<?php

/**
 * Description of Email
 *
 * @author matthias laug <laug@lieferando.de>
 */
class Yourdelivery_Form_Administration_Blacklist_Email extends Default_Forms_Base {

    public function init() {               
        
        $this->setAction('/administration_blacklist/email');
        
        $this->addElement('text', 'bl_email', array(
            'filters' => array('StringTrim'),     
            'required' => true,
            'validators' => array(
                'NotEmpty',
                'EmailAddress'
            ),
            'label' => __b('Email'),
            'Attribs' => array(
                'class' => "yd-empty-text",
                'title' => __b("Email"),
            )
        ));

        $this->addElement('text', 'bl_orderId', array(
            'filters' => array('StringTrim'),            
            'required' => false,
            'label' => __b('Bestell Id'),
            'Attribs' => array(
                'class' => "yd-empty-text",
                'title' => __b("Bestell Id?"),
            )
        ));
      

        $this->addElement('textarea', 'bl_comment', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                'NotEmpty'
            ),
            'label' => __b('Wieso?'),
            'Attribs' => array(
                'class' => "yd-empty-text",
                'title' => __b("Wieso Blacklist?"),
            )
        ));

        $this->addElement('checkbox', 'bl_minutemailer', array(
            'label' => __b('ist Minutemailer?'),
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
        
        $this->addElement('checkbox', 'bl_cancelorder', array(
            'label' => __b('Bestellung stornieren?'),
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('submit', 'save', array(
            'label' => __b('Speichern'),
        ));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.06.2012
     * @param array $values 
     */
    public function populate(array $values) {
        
        parent::populate($values);
        
        $parts = explode('@', $values['bl_email']);    
        $element =  $this->getElement('bl_minutemailer');
        $element->setLabel(sprintf('<a href="http://%s" target="_blank">', $parts[1]) . __b('ist Minutemailer?') . '</a>');
        $element->getDecorator('Label')->setOption('escape', false);
        
        return $this;
    }

}
