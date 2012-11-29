<?php

class Yourdelivery_Form_Partner_Login extends Default_Forms_Base {


    public function init() {

        $this->addElement('text', 'nr', array(
            'label' => __p('Kundennummer'),
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->getElement('nr')->getValidator('NotEmpty')
            ->setMessage(__p('Bitte geben Sie Ihre Kundennummer ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('password', 'pass', array(
            'label' => __p('Passwort'),
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->getElement('pass')->getValidator('NotEmpty')
            ->setMessage(__p('Bitte geben Sie Ihr Passwort ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('submit', __p('Anmelden'));

        $this->setActionRoute(array('action' => 'login'), 'partnerRoute');

    }
    
    /**
     * get the auth adapter for the partner login
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     * @return \Zend_Auth_Adapter_DbTable
     */
    public function getAuthAdapter(){      
        $registry = Zend_Registry::get('dbAdapter');
        $auth = new Zend_Auth_Adapter_DbTable($registry);
        $auth->setTableName('restaurants')
                ->setIdentityColumn('customerNr')
                ->setCredentialColumn('password')
                ->setCredentialTreatment('MD5(?)');
        return $auth;
    }
}