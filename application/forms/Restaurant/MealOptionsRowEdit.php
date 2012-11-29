<?php

class Yourdelivery_Form_Restaurant_MealOptionsRowEdit extends Default_Forms_Base {

    /**
     * @var Yourdelivery_Model_Servicetype_Abstract
     */
    protected $_service = null;

    public function setService(Yourdelivery_Model_Servicetype_Abstract $service) {
        $this->_service = $service;
        $this->_init();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 05.06.2012 
     */
    protected function _init() {

        $this->setAction('/restaurant_options/creategroup')
                ->setMethod('post');

        $this->addElement('text', 'name', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            ),
            'label' => __b('Name im Frontend')
        ));

        $this->addElement('text', 'internalName', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'label' => __b('Interner Name')
        ));

        $this->addElement('select', 'minChoices', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'label' => __b('Minimale Auswahl'),
            'multiOptions' => array(-1 => __b('genau so wie "Maximale Auswahl"'), 0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)
        ));

        $this->addElement('select', 'choices', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'label' => __b('Maximale Auswahl'),
            'multiOptions' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)
        ));

        $this->addElement('text', 'description', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'label' => __b('Beschreibung')
        ));

        $categories = $this->_service->getMealCategoriesSorted();
        $categories[0] = __b(' -- ');
        $this->addElement('select', 'categoryId', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'label' => __b('Kategorie'),
            'multiOptions' => $categories
        ));

        $this->setDefault("categoryId", 0);
        
        $this->addElement('text', 'rank', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'label' => __b('Rang'),
            'default' => 0
        ));

        $this->addElement('submit', __b('speichern'));
    }

    /**
     * check if min is not above max
     * check if min is zero, in that case we use max value
     * 
     * @author Mattthias Laug <laug@lieferando.de>
     * @param type $data
     * @return type 
     */
    public function isValid($data) {
        $min = (integer) $data['minChoices'];
        $max = (integer) $data['choices'];

        if ($min < 0) {
            $data['minChoices'] = $max;
        }

        if ($min > $max) {
            $minElement = $this->getElement('minChoices');
            $minElement->addError(__b('Achtung! Der Minimumwert ist größer als der Maximumwert. Bitte ändern.'));
            return false;
        }

        return parent::isValid($data);
    }

}
