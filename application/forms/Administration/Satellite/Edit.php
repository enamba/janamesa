<?php

/**
 * Satellite form
 *
 * @author vpriem
 */
class Yourdelivery_Form_Administration_Satellite_Edit extends Default_Forms_Base {

    public function init() {
        
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $satelliteId = (integer) $request->getParam('id', 0);
        $url = $request->getParam('url','');
        $domain = $request->getParam('domain','');
        
        $this->addElement('text', 'restaurantId', array(
            'required'   => true,
            'validators' => array(
                array('NotEmpty', true, array('messages' => __b("Kein Restaurant wurde ausgewählt!")))
            )
        ));

        $this->addElement('text', 'domain', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array('messages' => __b("Das Feld 'Domain' wurde nicht ausgefüllt!"))),
                array(  'validator' => 'Db_NoRecordExists', 
                        'options' => array(
                                'table' => 'satellites', 
                                'field' => 'domain', 
                                'messages' => __b("Diese Domain-URL Kombination ist bereits bei einem anderen Satelliten in Datenbank vorhanden"),                                                            
                                'exclude' => sprintf('id != %d and domain="%s" and url="%s"', $satelliteId, $domain, $url)
                            )
                    )
                )));

        $this->addElement('text', 'url', array(
            'filters' => array('StringTrim')
        ));
        
        $this->addElement('text', 'title', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'keywords', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'description', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'robots', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('file', '_logo', array(
            'validators' => array(
                array('validator' => 'Count', 'options' => array(false, 1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,png,gif'))
            )
        ));

        $this->addElement('file', '_background', array(
            'validators' => array(
                array('validator' => 'Count', 'options' => array(false, 1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,png,gif'))
            )
        ));
        
        $this->addElement('file', '_certification', array(
            'validators' => array(
                array('validator' => 'Count', 'options' => array(false, 1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,png,gif'))
            )
        ));
        
        $this->addElement('text', 'impressum', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'dynamicText', array(
            'filters' => array('StringTrim')
        ));
        
        $this->addElement('checkbox', 'disabled', array(
            'checkedValue'   => 1,
            'uncheckedValue' => 0
        ));
        
        $this->addElement('checkbox', 'showOpinions', array(
            'checkedValue'   => 1,
            'uncheckedValue' => 0
        ));
        
        $this->addElement('checkbox', 'showJobs', array(
            'checkedValue'   => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'showFacebookLink', array(
            'checkedValue'   => 1,
            'uncheckedValue' => 0
        ));
        
        $this->addElement('text', 'facebookLink', array(
            'filters' => array('StringTrim')
        ));  
              
        $this->addElement('text', 'kommSat', array());

        $this->addElement('text', 'feeSat', array('filters' => array('Digits')));

        $this->addElement('text', 'itemSat', array('filters' => array('Digits')));
    }

}