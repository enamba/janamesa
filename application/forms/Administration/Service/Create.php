<?php

/**
 * Form for service creation
 * @author vait
 */
class Yourdelivery_Form_Administration_Service_Create extends Default_Forms_Base {

    public function init() {
        $config = Zend_Registry::get('configuration');

        $this->addElement('text', 'name', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte geben Sie einen Namen ein"))
                )
            )
        ));

        $this->addElement('text', 'franchiseTypeId', array(
            'required' => false,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'franchiseName', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'street', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte geben Sie eine Strasse ein"))
                )
            )
        ));

        $this->addElement('text', 'hausnr', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte geben Sie eine Hausnummer ein"))
                )
            )
        ));

        $this->addElement('text', 'plz', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte geben Sie eine PLZ ein"))
                )
            )
        ));

        $this->addElement('text', 'cityId', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte wählen Sie eine PLZ aus"))
                )
            )
        ));

        $this->addElement('text', 'description', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'specialComment', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'isOnline', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'statecomment', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'tel', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte geben Sie eine Telefonnummer ein"))
                ),
                array('Digits', false, array(
                        'messages' => __b("Bitte geben Sie nur Ziffern in der Telefonnummer 1 ein"))
                )                
            )
        ));

        $this->addElement('text', 'tel2', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('Digits', false, array(
                        'messages' => __b("Bitte geben Sie nur Ziffern in der Telefonnummer 2 ein"))
                )
            )
        ));

        $this->addElement('text', 'tel3', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('Digits', false, array(
                        'messages' => __b("Bitte geben Sie nur Ziffern in der Telefonnummer 3 ein"))
                )
            )            
        ));
        
        $this->addElement('text', 'fax', array(
            'required' => $config->domain->base != 'janamesa.com.br',
            'filters' => array('StringTrim'),
            'validators' => array(
                $config->domain->base != 'janamesa.com.br' ? array('NotEmpty', false, array('messages' => __b("Bitte geben Sie eine Faxnummer ein"))) : array()
            )
                )
        );

        $this->addElement('text', 'faxService', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'email', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            )
        ));

        $this->addElement('text', 'notify', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'categoryId', array(
            'required' => false,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'qypeId', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'acceptsPfand', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('file', 'img', array(
            'validators' => array(
                array('validator' => 'Count', 'options' => array(false, 1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,png,gif'))
            )
        ));

        $this->addElement('checkbox', 'isLogo', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('text', 'komm', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'fee', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'item', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoName', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'billingName', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoNr', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoBlz', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoIban', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoSwift', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoBank', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoAgentur', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoDigit', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'ustIdNr', array(
            'filters' => array('StringTrim'),
        ));
        
        $this->addElement('text', 'billInterval', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'billDeliver', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('checkbox', 'partnerDetailedStats', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0,
        ));
        
        $this->addElement('checkbox', 'onlycash', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'debit', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'paymentbar', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'sodexo', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'express', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'onlyPickup', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'billDeliverCost', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('text', 'floorfee', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'metaTitle', array(
            'required' => false,
            'filters' => array(array('StringTrim'),
                array('PregReplace', array('match' => '/' . gettext('%s Lieferservice %s %s, %s bestellen') . '/',
                        'replace' => '')
                ),
                array('Null')
            )
        ));

        $this->addElement('text', 'metaKeywords', array(
            'required' => false,
            'filters' => array(array('StringTrim'),
                array('PregReplace', array('match' => '/' . gettext('%s Lieferservice %s %s essen bestellen Kreditkarte bargeldlos Heimservice Bringdienst') . '/',
                        'replace' => '')
                ),
                array('Null')
            )
        ));

        $this->addElement('text', 'metaDescription', array(
            'required' => false,
            'filters' => array(array('StringTrim'),
                array('PregReplace', array('match' => '/' . gettext('%s %s Lieferservice %s im Überblick. Alle Informationen auf einen Blick. Bequem %s bestellen, bargeldlos zahlen bei %s.') . '/',
                        'replace' => '')
                ),
                array('Null')
            )
        ));

        $this->addElement('text', 'metaRobots', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('checkbox', 'use_as_admin', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('select', 'selContactId', array(
            'registerInArrayValidator' => false
        ));

        $this->addElement('text', 'contact_name', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'contact_prename', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'contact_position', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'contact_street', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'contact_hausnr', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'contact_plz', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'Db_RecordExists', 'options' => array('city', 'plz'))
            )
        ));

        $this->addElement('text', 'contact_cityId', array(
            'required' => false,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'contact_email', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress'
            )
        ));

        $this->addElement('text', 'contact_tel', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'contact_fax', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('checkbox', 'bill_as_contact', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('select', 'selBillingContactId', array(
            'registerInArrayValidator' => false
        ));

        $this->addElement('text', 'bill_name', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'bill_prename', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'bill_positon', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'bill_street', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'bill_hausnr', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'bill_plz', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'bill_cityId', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'bill_email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress', 'NotEmpty'
            )
        ));

        $this->addElement('text', 'bill_tel', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'bill_fax', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'service_courier', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'service_company', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'service_salesperson', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte gib einen Vertriebler an"))
                )
            )
        ));

        $this->addElement('text', 'signed', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'restUrl', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'Db_NoRecordExists', 'options' => array('table' => 'restaurants', 'field' => 'restUrl', 'messages' => __b("Diese restUrl ist bereits in Datenbank vorhanden")))
            )
        ));

        $this->addElement('text', 'caterUrl', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'Db_NoRecordExists', 'options' => array('table' => 'restaurants', 'field' => 'caterUrl', 'messages' => __b("Diese caterUrl ist bereits in Datenbank vorhanden")))
            )
        ));

        $this->addElement('text', 'greatUrl', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'Db_NoRecordExists', 'options' => array('table' => 'restaurants', 'field' => 'greatUrl', 'messages' => __b("Diese greatUrl ist bereits in Datenbank vorhanden")))
            )
        ));

        $this->addElement('checkbox', 'laxContract', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
    }

}
