<?php

/**
 * Description of Default_Form_Base
 *
 * @author oli
 */
class Default_Forms_Base extends Zend_Form {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     */
    public function __construct(array $fields = null, $options = null) {
        parent::__construct($options);

        if (is_array($fields)) {
            foreach ($fields as $field => $values) {

                if (!isset($values['required']) || !isset($values['validate']) || !isset($values['customMessages'])) {
                    throw new Zend_Form_Exception(sprintf("Some value missing for element %s", $field));
                }

                $field = 'init' . ucfirst($field);
                try {
                    $this->$field($values['required'], $values['validate'], $values['customMessages']);
                } catch (Zend_Form_Exception $e) {
                    /**
                     * init-Method for field / element does not exist
                     * ignore that
                     */
                    continue;
                }
            }
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     */
    public function initPrename($required = true, $validate = true, $customMessages = true) {

        if ($validate) {
            $minLength = 3;
            $maxLength = 50;

            $this->addElement('text', 'prename', array(
                'label' => __('Vorname'),
                'required' => $required,
                'filters' => array('StringTrim'),
                'validators' => array(
                    'NotEmpty',
                    array('validator' => 'StringLength', 'options' => array($minLength, $maxLength))
                )
            ));

            if ($customMessages) {
                $this->getElement('prename')->getValidator('NotEmpty')
                        ->setMessage(__('Der Vorname darf nicht leer sein.'), Zend_Validate_NotEmpty::IS_EMPTY);
                $this->getElement('prename')->getValidator('StringLength')
                        ->setMessage(__('Der Vorname ist zu kurz. (mind. %d Zeichen)', $minLength), Zend_Validate_StringLength::TOO_SHORT);
                $this->getElement('prename')->getValidator('StringLength')
                        ->setMessage(__('Der Vorname ist zu lang. (max. %d Zeichen)', $maxLength), Zend_Validate_StringLength::TOO_LONG);
            }
        } else {
            $this->addElement('text', 'prename', array(
                'label' => __('Vorname'),
                'required' => $required,
                'filters' => array('StringTrim')
            ));
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     */
    public function initName($required = true, $validate = true, $customMessages = true) {

        if ($validate) {
            $minLength = 3;
            $maxLength = 50;

            $this->addElement('text', 'name', array(
                'label' => __('Name'),
                'required' => $required,
                'filters' => array('StringTrim'),
                'validators' => array(
                    'NotEmpty',
                    array('validator' => 'StringLength', 'options' => array($minLength, $maxLength))
                )
            ));

            if ($customMessages) {
                $this->getElement('name')->getValidator('NotEmpty')
                        ->setMessage(__('Der Nachname darf nicht leer sein.'), Zend_Validate_NotEmpty::IS_EMPTY);
                $this->getElement('name')->getValidator('StringLength')
                        ->setMessage(__('Der Nachname ist zu kurz. (mind. %d Zeichen)', $minLength), Zend_Validate_StringLength::TOO_SHORT);
                $this->getElement('name')->getValidator('StringLength')
                        ->setMessage(__('Der Nachname ist zu lang. (max. %d Zeichen)', $maxLength), Zend_Validate_StringLength::TOO_LONG);
            }
        } else {
            $this->addElement('text', 'name', array(
                'label' => __('Name'),
                'required' => $required,
                'filters' => array('StringTrim')
            ));
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     */
    public function initCompanyName($required = true) {

        $this->addElement('text', 'companyName', array(
            'label' => __('Firma'),
            'required' => $required,
            'filters' => array('StringTrim')
        ));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     * @return Zend_Form_Element_Text
     */
    public function initEmail($required = true, $validate = true, $customMessages = true, $checkBlacklist = true, $checkExisting = false, $excludeCustomerId = null, $askKindly = false) {

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim')
        ));
        $element = $this->getElement('email');

        if ($required) {
            $element->setRequired(true);
        }

        if ($validate) {
            $element->addValidator('EmailAddress', true);
            $element->addValidator('NotEmpty', true);

            // check blacklisted email
            if ($checkBlacklist) {
                $element->addValidator(
                        new Default_Forms_Validate_EmailBlacklisted()
                );
                if ($askKindly) {
                    $element->getValidator('EmailBlacklisted')
                            ->setMessage(__('Ihre E-Mail-Adresse konnte nicht verifiziert werden'), Default_Forms_Validate_EmailBlacklisted::BLACKLISTED);
                }
                else {
                    $element->getValidator('EmailBlacklisted')
                            ->setMessage(__('Deine E-Mail-Adresse konnte nicht verifiziert werden'), Default_Forms_Validate_EmailBlacklisted::BLACKLISTED);
                }
            }

            // check existing email
            if ($checkExisting) {
                if (!is_null($excludeCustomerId)) {

                    // select write adapter here to avoid time delays and duplicxate entries in master / slave combination
                    $db = Zend_Registry::get('dbAdapter');
                    $element->addValidator('Db_NoRecordExists', false, array(
                        'table' => 'customers',
                        'field' => 'email',
                        'exclude' => $db->quoteInto("deleted = 0 AND id != ?", $excludeCustomerId)
                    ));
                } else {
                    $element->addValidator('Db_NoRecordExists', false, array(
                        'table' => 'customers',
                        'field' => 'email',
                        'exclude' => "deleted = 0"
                    ));
                }
                $element->getValidator('Db_NoRecordExists')
                        ->setMessage(__('Diese E-Mail-Adresse ist bereits registriert.'), Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);
            }

            if ($customMessages) {
                if ($askKindly) {
                    $message = __('Bitte geben Sie eine gültige E-Mail-Adresse ein');
                }
                else {
                    $message = __('Bitte gib eine gültige E-Mail-Adresse ein');
                }


                //set to empty string to overide default message, still error message shown because of email validator, maybe hack
                $element->getValidator('NotEmpty')
                        ->setMessage($message, Zend_Validate_NotEmpty::IS_EMPTY);
                $element->getValidator('EmailAddress')
                        ->setMessage($message, Zend_Validate_EmailAddress::INVALID)
                        ->setMessage($message, Zend_Validate_EmailAddress::INVALID_FORMAT)
                        ->setMessage($message, Zend_Validate_EmailAddress::INVALID_HOSTNAME)
                        ->setMessage($message, Zend_Validate_EmailAddress::INVALID_LOCAL_PART)
                        ->setMessage($message, Zend_Validate_EmailAddress::INVALID_MX_RECORD)
                        ->setMessage($message, Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED);
            }
        }
        
        return $element;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     */
    public function initTel($required = true, $validate = true, $customMessages = true) {
        if ($validate) {
            $minLength = 7;
            $maxLength = 25;

            $this->addElement('text', 'tel', array(
                'required' => $required,
                'filters' => array('StringTrim'),
                'validators' => array(
                    'NotEmpty',
                    array('validator' => 'StringLength', 'options' => array($minLength, $maxLength))
                )
            ));

            if ($customMessages) {
                $this->getElement('tel')->getValidator('NotEmpty')
                        ->setMessage(__('Bitte gib eine Telefonnummer ein. Wird für evtl. Rückfragen benötigt.'), Zend_Validate_NotEmpty::IS_EMPTY);
                $this->getElement('tel')->getValidator('StringLength')
                        ->setMessage(__('Die Telefonnummer ist zu kurz. (mind. %d Zeichen)', $minLength), Zend_Validate_StringLength::TOO_SHORT);
                $this->getElement('tel')->getValidator('StringLength')
                        ->setMessage(__('Die Telefonnummer ist zu lang. (max. %d Zeichen)', $maxLength), Zend_Validate_StringLength::TOO_LONG);
            }
        } else {
            $this->addElement('text', 'tel', array(
                'required' => $required,
                'filters' => array('StringTrim')
            ));
        }
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 31.07.2012
     * @return Zend_Form_Element_Text
     */
    public function initMobile($required = true, $validate = true, $customMessages = true) {
        if ($validate) {
            $minLength = 7;
            $maxLength = 25;

            $this->addElement('text', 'mobile', array(
                'required' => $required,
                'filters' => array('StringTrim'),
                'validators' => array(
                    'NotEmpty',
                    array('validator' => 'StringLength', 'options' => array($minLength, $maxLength))
                )
            ));
            $element = $this->getElement('mobile');

            $element->addValidator(
                    new Default_Forms_Validate_MobileNumber()
            );

            if ($customMessages) {
                $element->getValidator('NotEmpty')
                        ->setMessage(__('Bitte gib eine Telefonnummer ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
                $element->getValidator('StringLength')
                        ->setMessage(__('Die Telefonnummer ist zu kurz. (mind. %d Zeichen)', $minLength), Zend_Validate_StringLength::TOO_SHORT)
                        ->setMessage(__('Die Telefonnummer ist zu lang. (max. %d Zeichen)', $maxLength), Zend_Validate_StringLength::TOO_LONG);
                $element->getValidator('MobileNumber')
                        ->setMessage(__('Bitte geben Sie eine gültige Mobilnummer ein'), Default_Forms_Validate_MobileNumber::NOT_MOBILE);
            }
        }
        else {
            $this->addElement('text', 'mobile', array(
                'required' => $required,
                'filters' => array('StringTrim')
            ));
            $element = $this->getElement('mobile');
        }
        
        return $element;
    }


    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.08.2012
     */
    public function initSetpassword($askOldPassword = true, $customMessages = true) {
        $minLength = 5;
        $maxLength = 20;

        if ($askOldPassword) {
            $this->addElement('password', 'passwordOld',array(
                'required' => true,
                'label' => __('Altes Passwort'),
                'validators' => array(
                    'NotEmpty'
                )
            ));

            $this->getElement('passwordOld')->getValidator('NotEmpty')
                    ->setMessage(__('Bitte geben Sie Ihr Passwort ein'), Zend_Validate_NotEmpty::IS_EMPTY);
        }

        $this->addElement('password', 'passwordOne', array(
            'required' => true,
            'label' => __('Neues Passwort'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array($minLength, $maxLength))
                ),            
             'autocomplete' => 'off'     
            ));

        $this->addElement('password', 'passwordTwo', array(
            'required' => true,
            'label' => __('Neues Passwort wiederholen'),
            'validators' => array(
                'NotEmpty',
                array('Identical', false, array('token' => 'passwordOne'))
            )
        ));

        if ($customMessages) {
            $this->getElement('passwordOne')->getValidator('NotEmpty')
                    ->setMessage(__('Bitte geben Sie ein Passwort ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
            $this->getElement('passwordOne')->getValidator('StringLength')
                    ->setMessage(__('Ihr Passwort ist zu lang. (max. %d Zeichen)', $maxLength), Zend_Validate_StringLength::TOO_LONG);
            $this->getElement('passwordOne')->getValidator('StringLength')
                    ->setMessage(__('Ihr Passwort ist zu kurz. (mind. %d Zeichen)', $minLength), Zend_Validate_StringLength::TOO_SHORT);
            $this->getElement('passwordTwo')->getValidator('NotEmpty')
                    ->setMessage(__('Bitte geben Sie ein Passwort ein.'),Zend_Validate_NotEmpty::IS_EMPTY );
            $this->getElement('passwordTwo')->getValidator('Identical')
                    ->setMessage(__('Die Passwörter stimmen nicht überein.'), Zend_Validate_Identical::NOT_SAME);

        }

    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.01.2012
     */
    public function initPassword($required = true, $validate = true, $customMessages = true) {
        if ($validate) {
            $minLength = 5;
            $maxLength = 20;

            $this->addElement('password', 'password', array(
                'required' => $required,
                'filters' => array('StringTrim'),
                'validators' => array(
                    'NotEmpty',
                    array('validator' => 'StringLength', 'options' => array($minLength, $maxLength))
                )
            ));

            if ($customMessages) {
                $this->getElement('password')->getValidator('StringLength')
                        ->setMessage(__('Dein Passwort ist zu lang. (max. %d Zeichen)', $maxLength), Zend_Validate_StringLength::TOO_LONG);
                $this->getElement('password')->getValidator('StringLength')
                        ->setMessage(__('Dein Passwort ist zu kurz. (mind. %d Zeichen)', $minLength), Zend_Validate_StringLength::TOO_SHORT);
                $this->getElement('password')->getValidator('NotEmpty')
                        ->setMessage(__('Dein Passwort darf nicht leer sein.'), Zend_Validate_NotEmpty::IS_EMPTY);
            }
        } else {
            $this->addElement('password', 'password', array(
                'filters' => array('StringTrim')
            ));
        }
    }

    /**
     * @param boolean $required
     * @param boolean $validate
     * @param boolean $customMessages
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 14.03.2012
     */
    public function initStreetHausNr($required = true, $validate = true, $customMessages = true) {
        if ($validate) {
            $minLength = 3;
            $maxLength = 30;$config = Zend_Registry::get('configuration');
            $minHouseNoLength = $config->locale->housenumber->min;
            $maxHouseNoLength = $config->locale->housenumber->max;

            $this->addElement('text', 'street', array(
                'label' => __('Straße'),
                'required' => $required,
                'filters' => array('StringTrim'),
                'validators' => array(
                    'NotEmpty',
                    array('validator' => 'StringLength', 'options' => array($minLength, $maxLength))
                )
            ));

            $this->addElement('text', 'hausnr', array(
                'label' => __('Nr.'),
                'required' => $required,
                'filters' => array('StringTrim'),
                'validators' => array(
                    'NotEmpty',
                    array('validator' => 'StringLength', 'options' => array($minHouseNoLength, $maxHouseNoLength))
                )
            ));

            if ($customMessages) {

                $this->getElement('street')->getValidator('StringLength')
                        ->setMessage(__('Die Straße ist zu lang. (max. %d Zeichen)', $maxLength), Zend_Validate_StringLength::TOO_LONG);
                $this->getElement('street')->getValidator('StringLength')
                        ->setMessage(__('Die Straße ist zu kurz. (mind. %d Zeichen)', $minLength), Zend_Validate_StringLength::TOO_SHORT);
                $this->getElement('street')->getValidator('NotEmpty')
                        ->setMessage(__('Bitte gib eine Straße an.'), Zend_Validate_NotEmpty::IS_EMPTY);

                $this->getElement('hausnr')->getValidator('StringLength')
                        ->setMessage(__('Die Hausnummer ist zu lang. (max. %d Zeichen)', $maxHouseNoLength), Zend_Validate_StringLength::TOO_LONG);
                $this->getElement('hausnr')->getValidator('StringLength')
                        ->setMessage(__('Die Hausnummer ist zu kurz. (mind. %d Zeichen)', $minHouseNoLength), Zend_Validate_StringLength::TOO_SHORT);
                $this->getElement('hausnr')->getValidator('NotEmpty')
                        ->setMessage(__('Bitte gib eine Hausnummer an.'), Zend_Validate_NotEmpty::IS_EMPTY);
            }
        } else {
            $this->addElement('text', 'street', array(
                'label' => __('Straße'),
                'filters' => array('StringTrim')
            ));

            $this->addElement('text', 'hausnr', array(
                'label' => __('Nr.'),
                'filters' => array('StringTrim')
            ));
        }
    }

    /**
     * @param boolean $required
     * @param boolean $validate
     * @param boolean $customMessages
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 29.03.2012
     */
    public function initPlz($required = true, $validate = true, $customMessages = true) {

        if ($validate) {
            if ($customMessages) {
                $this->addElement('text', 'plz', array(
                    'label' => __('PLZ'),
                    'required' => true,
                    'filters' => array('StringTrim'),
                    'validators' => array(
                        'NotEmpty',
                        array('validator' => 'Db_RecordExists', 'options' => array('city', 'plz'))
                    )
                ));

                $this->getElement('plz')->getValidator('NotEmpty')
                        ->setMessage(__('Bitte gib eine gültige PLZ ein'), Zend_Validate_NotEmpty::IS_EMPTY);
                $this->getElement('plz')->getValidator('Db_RecordExists')
                        ->setMessage(__('Die PLZ wurde nicht gefunden'));
            }
        } else {
            $this->addElement('text', 'plz', array(
                'label' => __('Plz'),
                'required' => $required,
                'filters' => array('StringTrim')
            ));
        }
    }

    /**
     * append autocomplete plz
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 03.07.2012
     */
    public function initAutocompletePlz() {
        $cityId = new Zend_Form_Element_Hidden('cityId');
        $cityId->setRequired(true)
        ->setAttrib('id', 'cityId')
        ->setLabel(__('Plz'))
        ->removeDecorator('HtmlTag');

        $this->addElement($cityId);

        $this->addElement('text', 'plz', array(
            'label' => __('Plz'),
            'required' => true,
            'filters' => array('StringTrim'),
            'class' => 'yd-plz-autocomplete yd-only-nr'
        ));
        $this->plz->removeDecorator('label')
                  ->removeDecorator('HtmlTag')
                  ->addDecorator('callback', array('callback'=>function($content, $element, array $options) {
                        return '<br/><br/>';
                  })
        );
    }

    /**
     * log all invalid forms
     *
     * @since 29.02.2012
     * @author Matthias Laug <laug@lieferando.de>
     * @param array $data
     * @return boolean
     */
    public function isValid($data) {

        $cityId = $this->getElement('cityId');
        $plz = $this->getElement('plz');
        if ($cityId instanceof Zend_Form_Element && $plz instanceof Zend_Form_Element) {
            if (!$cityId->isValid($data['cityId'])) {
                $plz = $data['plz'];
                $cityIdProposal = Yourdelivery_Model_City::getByPlz($plz);
                if (is_object($cityIdProposal) && $cityIdProposal->count() > 0) {
                    $data['cityId'] = $cityIdProposal->current()->id;
                }
            } elseif (!$plz->isValid($data['plz'])) {
                $cityId = (integer) $data['cityId'];
                if ($cityId > 0) {
                    try {
                        $plzProposal = new Yourdelivery_Model_City($cityId);
                        $data['plz'] = $plzProposal->getPlz();
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

                    }
                }
            }
        }


        $result = parent::isValid($data);

        if (!$result) {
            $logger = Zend_Registry::get('logger');
            $message = sprintf('could not validate form %s with data %s', get_class($this), print_r($this->getValues(), true));
            foreach ($this->getMessages() as $key => $msg) {
                foreach ($msg as $m) {
                    $message .= sprintf('form input field "%s" failed with message: %s', $key, $m);
                }
            }
            $logger->warn($message);
        }

        return $result;
    }

    /**
     * Sets the form action to the url of the route
     *
     * @author Andre Ponert <ponert@theqserver.de>
     * @since 23.08.2012
     *
     * @param array $params Parameters
     * @param string $routeName Name of the route
     * @param boolean $reset Reset current params
     */
    public function setActionRoute($params, $routeName = null, $reset=true) {
        $this->setAction($this->getView()->url($params, $routeName, $reset));
    }
}
