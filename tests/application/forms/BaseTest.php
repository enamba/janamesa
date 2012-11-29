<?php

/**
 * First tests for forms in this application
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 05.01.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class FormBaseTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     */
    public function testInitViaConstructSuccess() {
        $form = new Default_Forms_Base(
                        array(
                            'name' => array('required' => true, 'validate' => true, 'customMessages' => true),
                            'prename' => array('required' => true, 'validate' => false, 'customMessages' => true),
                            'email' => array('required' => false, 'validate' => false, 'customMessages' => false),
                            'tel' => array('required' => true, 'validate' => true, 'customMessages' => true),
                            'some-non-existing-elememt' => array('required' => false, 'validate' => false, 'customMessages' => false)
                        )
        );

        $this->assertInstanceof('Default_Forms_Base', $form);

        $this->assertInstanceof(Zend_Form_Element_Text, $form->getElement('name'));
        $this->assertInstanceof(Zend_Form_Element_Text, $form->getElement('prename'));
        $this->assertInstanceof(Zend_Form_Element_Text, $form->getElement('email'));
        $this->assertInstanceof(Zend_Form_Element_Text, $form->getElement('tel'));

        $this->assertNull($form->getElement('some-non-existing-elememt'));

        $this->assertInstanceof(Zend_Validate_NotEmpty, $form->getElement('name')->getValidator('NotEmpty'));
        $this->assertInstanceof(Zend_Validate_StringLength, $form->getElement('name')->getValidator('StringLength'));
        $this->assertInstanceof(Zend_Filter_StringTrim, $form->getElement('name')->getFilter('StringTrim'));

        $this->assertEquals(0, count($form->getElement('prename')->getValidators()));

        $this->assertEquals(0, count($form->getElement('email')->getValidators()));

        $this->assertInstanceof(Zend_Validate_NotEmpty, $form->getElement('tel')->getValidator('NotEmpty'));
        $this->assertInstanceof(Zend_Filter_StringTrim, $form->getElement('tel')->getFilter('StringTrim'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     *
     * @expectedException Zend_Form_Exception
     */
    public function testInitWithMissingParamsThrowsException() {
        $form = new Default_Forms_Base(
                        array(
                            'tel' => array(),
                        )
        );
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     */
    public function testInitSingleSuccess() {
        $form = new Default_Forms_Base();
        $form->initName();
        $form->initPrename();
        $this->assertInstanceof(Zend_Form_Element_Text, $form->initEmail());
        $form->initTel();

        $this->assertInstanceof('Default_Forms_Base', $form);

        $this->assertInstanceof(Zend_Form_Element_Text, $form->getElement('name'));
        $this->assertInstanceof(Zend_Form_Element_Text, $form->getElement('prename'));
        $this->assertInstanceof(Zend_Form_Element_Text, $form->getElement('email'));
        $this->assertInstanceof(Zend_Form_Element_Text, $form->getElement('tel'));

        $this->assertInstanceof(Zend_Validate_NotEmpty, $form->getElement('name')->getValidator('NotEmpty'));
        $this->assertInstanceof(Zend_Validate_StringLength, $form->getElement('name')->getValidator('StringLength'));
        $this->assertInstanceof(Zend_Filter_StringTrim, $form->getElement('name')->getFilter('StringTrim'));

        $this->assertInstanceof(Zend_Validate_NotEmpty, $form->getElement('prename')->getValidator('NotEmpty'));
        $this->assertInstanceof(Zend_Validate_StringLength, $form->getElement('prename')->getValidator('StringLength'));
        $this->assertInstanceof(Zend_Filter_StringTrim, $form->getElement('prename')->getFilter('StringTrim'));

        $this->assertInstanceof(Zend_Validate_NotEmpty, $form->getElement('email')->getValidator('NotEmpty'));
        $this->assertInstanceof(Zend_Validate_EmailAddress, $form->getElement('email')->getValidator('EmailAddress'));
        $this->assertInstanceof(Zend_Filter_StringTrim, $form->getElement('email')->getFilter('StringTrim'));

        $this->assertInstanceof(Zend_Validate_NotEmpty, $form->getElement('tel')->getValidator('NotEmpty'));
        $this->assertInstanceof(Zend_Filter_StringTrim, $form->getElement('tel')->getFilter('StringTrim'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     */
    public function testFormValuesCorrect() {
        $form = new Default_Forms_Base();
        $form->initName();
        $form->initPrename();
        $this->assertInstanceof(Zend_Form_Element_Text, $form->initEmail());
        $form->initTel();
        $values = array(
            'name' => 'Felix',
            'prename' => 'Haferkorn',
            'tel' => '0123456798',
            'email' => 'haferkorn@lieferando.de'
        );
        $this->assertTrue($form->isValid($values));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     */
    public function testBlacklistedEmailNotValid() {
        /* @deprecated BLACKLIST */
        $email = 'my-unique-email@' . time() . '.de';
        // set emailto blacklist
        $fp = fopen(BLACKLIST, 'w+');
        fputs($fp, $email);
        fclose($fp);

        $form = new Default_Forms_Base();
        $form->initEmail();
        $values = array(
            'email' => $email
        );
        $this->assertFalse($form->isValid($values));
        $messages = $form->getMessages();
        $this->assertEquals(__('Deine E-Mail-Adresse konnte nicht verifiziert werden'), $messages['email']['emailIsBlacklisted']);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 15.03.2012
     */
    public function testCityFallback(){
        $form = new Default_Forms_Base();
        $form->addElement('text', 'cityId', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'Db_RecordExists', 'options' => array('city', 'id'))
            )
        ));
        $form->addElement('text', 'plz', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        // get plz with more than one cityId
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = "SELECT plz FROM city GROUP BY plz HAVING count(plz) > 1 ORDER BY rand() LIMIT 1";
        $plz = $db->fetchOne($sql);

        $values = array(
            'cityId' => 'non-existant',
            'plz' => $plz
            );
        $this->assertTrue($form->isValid($values));

        // check wrong values
        $values = array(
            'cityId' => 'non-existing-cityId',
            'plz' => '987654321'
            );
        $this->assertFalse($form->isValid($values));

    }

}