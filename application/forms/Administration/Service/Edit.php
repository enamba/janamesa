<?php

/**
 * Form for service editing
 * @author vait
 */
class Yourdelivery_Form_Administration_Service_Edit extends Default_Forms_Base {

    public function init() {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $serviceId = $request->getParam('id', 0);

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

        $this->addElement('text', 'status', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'isOnline', array(
            'filters' => array('StringTrim'),
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
        } else {
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

        $this->addElement('text', 'description', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'specialComment', array(
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
        
        $this->addElement('text', 'statecomment', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'topUntil', array(
            'required' => false,
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
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'notify', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'categoryId', array(
            'required' => true,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'qypeId', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'acceptsPfand', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('checkbox', 'isLogo', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
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


        $this->addElement('text', 'chargePercentage', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'chargeFix', array(
            'required' => true,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'chargeStart', array(
            'required' => true,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'komm', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'notifyPayed', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('checkbox', 'noNotification', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('text', 'fee', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
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

        $this->addElement('checkbox', 'paymentbar', array(
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

        $this->addElement('text', 'restUrl', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'Db_NoRecordExists', 'options' => array('table' => 'restaurants', 'field' => 'restUrl', 'exclude' => array('field' => 'id', 'value' => $serviceId)))
            )
        ));

        $this->addElement('text', 'caterUrl', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'Db_NoRecordExists', 'options' => array('table' => 'restaurants', 'field' => 'caterUrl', 'exclude' => array('field' => 'id', 'value' => $serviceId)))
            )
        ));

        $this->addElement('text', 'greatUrl', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'Db_NoRecordExists', 'options' => array('table' => 'restaurants', 'field' => 'greatUrl', 'exclude' => array('field' => 'id', 'value' => $serviceId)))
            )
        ));

        $this->addElement('text', 'offline-change-reason-text', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'offlineStatusUntil', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('checkbox', 'laxContract', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
    }

}
