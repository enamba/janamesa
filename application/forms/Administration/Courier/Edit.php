<?php
/**
 * @author vait
 * @since 01.08.2010
 */
class Yourdelivery_Form_Administration_Courier_Edit extends Default_Forms_Base {

    /**
     * Initialize form
     * @author vait
     * @since 01.08.2010
     * @return void
     */
    public function init() {
        
        $config = Zend_Registry::get('configuration');

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Namen ein"))
                )
            )
        ));

        $this->addElement('text', 'street', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Strasse ein"))
                )
            )
        ));

        // for Brasil we need the plz input, for other domains we need a cityId
        if (strpos($config->domain->base, "janamesa") !== false) {        
            $this->addElement('text', 'plz', array(
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('NotEmpty', false, array(
                            'messages' => __b("Bitte geben Sie eine PLZ ein"))
                    )
                )
            ));
        }
        else {
            $this->addElement('text', 'cityId', array(
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('NotEmpty', false, array(
                            'messages' => __b("Bitte wählen Sie eine PLZ aus"))
                    )
                )
            ));            
        }

        $this->addElement('text', 'hausnr', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Hausnummer ein"))
                )
            )
        ));

        $this->addElement('text', 'contactId', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'EmailAddress'
            )
        ));

        $this->addElement('text', 'mobile', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Telefonnummer ein"))
                )
            )
        ));

        $this->addElement('text', 'fax', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'faxService', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'billDeliver', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'notify', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'komm', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'digits', array('lessThan', false, array(
                    101,
                    'messages' => sprintf(__b("Provision darf nicht größer als 100%% sein")))) // use sprintf to escape %
            )
        ));

        $this->addElement('text', 'subvention', array(
            'filters'    => array('StringTrim')
        ));
        
        $this->addElement('text', 'api', array(
            'filters'    => array('StringTrim')
        ));
    }

}