<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Optivo
 *
 * @author daniel
 */
class Yourdelivery_Form_Administration_Mailing_Optivo extends Default_Forms_Base {

    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.07.2012 
     */
    public function init() {
            
        $this->addElement('text', 'name', 
                array( 'label' => __b('Name'),
                          'required' => true,
                          'filters' => array('StringTrim')
              ));
        
        
        $this->addElement('select', 'status', array(
            'label' => __b('Status'),
            'filters' => array('StringTrim'),
            'multioptions' => array(0 => __b('deaktiviert'),
                                                 1 => __b('aktiviert')
                                                ) 
        ));
        
        
        $this->addElement('text', 'start', array(
            'required' => true,
            'filters' => array('StringTrim')
        ));
        
        $this->addElement('text', 'end', array(
            'required' => true,
            'filters' => array('StringTrim')
        ));
        
        $this->addElement('text', 'mailingId', array(
            'label' => __b('MailingId'),
            'required' => true,
            'filters' => array('StringTrim')
        ));
                
        
        $this->addElement('multiselect', 'parameters', array(
            'label' => __b('Parameter'),
            'filters' => array('StringTrim'),      
            'id' => 'yd-mailing-parameters',
            'multioptions' => array('UserPrename' => __b('Vorname des Kunden'),
                                                 'LastOrderServiceName' => __b('Name des Restaurants bei dem der Kunde seine letzte Bestellung getätigt hatte')
                                                ) 
        ));
        
         $this->addElement('text', 'customerOrderCount', array(
            'label' => __b('Bestellanzahl'),
            'description' => __b('nach Semikolon getrennte Liste mit Bestellanzahl'),
            'filters' => array('StringTrim')           
        ));
      
         
        $this->addElement('select', 'cityIds',array(
             'registerInArrayValidator'    => false
        ));
        
        $this->addElement('checkbox', 'invertCity', array(
            'label' => __b('alle Städte ausser den ausgewählten')
        ));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.07.2012 
     * @param array $values
     * @return boolean 
     */
    public function isValid($values) {
        
         $values['start'] = substr($values['startTimeD'], 6, 4) . "-" . substr($values['startTimeD'], 3, 2) . "-" . substr($values['startTimeD'], 0, 2) . " " . substr($values['startTimeT'], 0, 2) . ":" . substr($values['startTimeT'], 3, 2) . ":00";
         $values['end'] = substr($values['endTimeD'], 6, 4) . "-" . substr($values['endTimeD'], 3, 2) . "-" . substr($values['endTimeD'], 0, 2) . " " . substr($values['endTimeT'], 0, 2) . ":" . substr($values['endTimeT'], 3, 2) . ":00";
        
         if(strtotime($values['start'])>=strtotime($values['end'])){
            $this->setErrorMessages(array(__b('Das Ende der Aktion darf nicht vor dem Startzeitpunkt liegen!')));
            $this->_errorsExist = true;                       
            return false;
         }
        
        return  parent::isValid($values);
    }

}

