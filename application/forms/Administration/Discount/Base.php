<?php

class Yourdelivery_Form_Administration_Discount_Base extends Default_Forms_Base {


    public function init() {

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Namen ein"))
                )
            )
        ));

        $this->addElement('text', 'status', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'rrepeat', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'countUsage', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('checkbox', 'onlyPrivate',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'onlyCompany',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'onlyRestaurant',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'onlyCustomer',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'onlyPremium',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'onlyIphone',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
        
        $this->addElement('checkbox', 'newCustomerCheck',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'noCash',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('text', 'kind', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'rabatt', array(
            'required'   => true,
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Rabatt ein"))
                )
            )
        ));

        $this->addElement('text', 'minAmount', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'info', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'notStartedInfo', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'expirationInfo', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'startTimeD', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'startTimeT', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'endTimeD', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'endTimeT', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'type', array(
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
        
        $this->addElement('file', 'img', array(
            'validators'    => array(
                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,png,gif'))
            )
        ));
        
        $this->addElement('text', 'email', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));        
        
        $this->addElement('multiselect', 'restaurantIds', array(
            'required' => false,     
            'registerInArrayValidator' => false
        ));
        
        $this->addElement('multiselect', 'cityIds', array(
            'required' => false,     
            'registerInArrayValidator' => false
        ));
        
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.06.2012
     * @param array $data
     * @return boolean 
     */
    public function isValid($data) {
        
        if(!in_array($data['type'],array(1,2,3))  && $data['newCustomerCheck'] == 0 ) {           
            $this->setErrorMessages(array(__b('Neukundenverifizierung darf nur bei den Typen 1-3 abgeschaltet werden.')));
            $this->_errorsExist = true;                       
            return false;
        }
        if(strtotime($data['startTimeD'].' '.$data['startTimeT']) > strtotime($data['endTimeD'].' '.$data['endTimeT'])){
            $this->setErrorMessages(array(__b('Das Ende der Rabattaktion darf nicht vor dem Startzeitpunkt liegen!')));
            $this->_errorsExist = true;                       
            return false;
        } 
         
        return parent::isValid($data);
        
    }
    
    
}

