<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 14.06.2012 
 */
class Yourdelivery_Form_Administration_Blacklist_Keyword extends Default_Forms_Base {

    public function init() {

        $this->setAction("/administration_blacklist/keywords");

        $this->addElement('select', 'type', array(
            'required' => true,
            'label' => __b('Spalte'),
            'multiOptions' => Yourdelivery_Model_Support_Blacklist::getTypes("KEYWORD"),
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte wählen eine Spalte aus"))
                )
            )
        ));

        $this->addElement('text', 'value', array(
            'required' => true,
            'label' => __b('Wert'),
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte geben Sie eine Wert ein"))
                )
            )
        ));

        $this->addElement('text', 'orderId', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => __b('Bestell Id'),
            'Attribs' => array(
                'class' => "yd-empty-text",
                'title' => __b("Bestell Id?"),
            )
        ));


        $this->addElement('select', 'matching', array(
            'required' => true,
            'multiOptions' => Yourdelivery_Model_Support_Blacklist::getMatchings(),
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte wählen eine Matching aus"))
                )
            )
        ));

        $this->addElement('textarea', 'comment', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Geben Sie bitte ein Grund ein"))
                )
            ),
            'Attribs' => array(
                'class' => "yd-empty-text",
                'title' => __b("Wieso Blacklist?"),
            )
        ));

        $this->addElement('checkbox', 'cancelOrder', array(
            'label' => __b('Bestellung stornieren?'),
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
        
        $this->addElement('submit', 'save', array(
            'label' => __b('Speichern')
        ));
    }

}
