<?php

/**
 * Seo links form
 *
 * @author vpriem
 */
class Yourdelivery_Form_Administration_Seo_Links_Edit extends Default_Forms_Base {

    public function init() {
        
        $this->addElement('text', 'url', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Das Feld 'URL' wurde nicht ausgefüllt!"))),
                array('Regex', false, array("`^[a-z0-9][a-z0-9\-\/]+(\.htm)?$`",
                    'messages' => __b("Die URL ist nicht korrekt")))
            )
        ));

        $this->addElement('text', 'domain', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'tab', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'categoryId', array(
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

        $this->addElement('text', 'headline1', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'headline2', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'content', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'inputPosition', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'inputValue', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('file', '_image', array(
            'Destination' => APPLICATION_PATH . '/../storage/landingpages/images/backgrounds',
            'validators' => array(
                array('Count', false, 1),
//                array('IsImage'),
                array('Extension', false, 'jpg,png,gif')
            )
        ));

        $this->addElement('text', 'backgroundImage', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('file', '_button', array(
            'Destination' => APPLICATION_PATH . '/../storage/landingpages/images/buttons',
            'validators' => array(
                array('Count', false, 1),
//                array('IsImage'),
                array('Extension', false, 'jpg,png,gif')
            )
        ));

        $this->addElement('text', 'buttonImage', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'previewText', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'previewImage', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('file', '_preview', array(
            'Destination' => APPLICATION_PATH . '/../storage/landingpages/images/previews',
            'validators' => array(
                array('Count', false, 1),
//                array('IsImage'),
                array('Extension', false, 'jpg,png,gif')
            )
        ));

        $this->addElement('text', 'addNavigationLinks', array(
        ));

        $this->addElement('text', 'rmNavigationLinks', array(
        ));

        $this->addElement('text', 'addListLinks', array(
        ));

        $this->addElement('text', 'rmListLinks', array(
        ));

        $this->addElement('text', 'addRelatedLinks', array(
        ));

        $this->addElement('text', 'rmRelatedLinks', array(
        ));

        $this->addElement('text', 'addRestaurants', array(
        ));

        $this->addElement('text', 'rmRestaurants', array(
        ));

        $this->addElement('text', 'scriptTop', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'scriptBottom', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'payments', array(
        ));

    }

}