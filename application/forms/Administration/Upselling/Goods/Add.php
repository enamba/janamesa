<?php
/**
 * @author vpriem
 * @since 27.06.2011
 */
class Yourdelivery_Form_Administration_Upselling_Goods_Add extends Default_Forms_Base {

    /**
     * @author vpriem
     * @since 27.06.2011
     */
    public function init() {

        $this->addElement('text', 'restaurantId', array());
        $this->addElement('text', 'countCanton2626', array());
        $this->addElement('text', 'costCanton2626', array('filters' => array('Digits'),));
        $this->addElement('text', 'countCanton2626N', array());
        $this->addElement('text', 'costCanton2626N', array('filters' => array('Digits'),));
        $this->addElement('text', 'countCanton2626D', array());
        $this->addElement('text', 'costCanton2626D', array('filters' => array('Digits'),));
        $this->addElement('text', 'countCanton2626S', array());
        $this->addElement('text', 'costCanton2626S', array('filters' => array('Digits'),));
        $this->addElement('text', 'countCanton2626H', array());
        $this->addElement('text', 'costCanton2626H', array('filters' => array('Digits'),));
        $this->addElement('text', 'countCanton2828', array());
        $this->addElement('text', 'costCanton2828', array('filters' => array('Digits'),));
        $this->addElement('text', 'countCanton3232', array());
        $this->addElement('text', 'costCanton3232', array('filters' => array('Digits'),));
        $this->addElement('text', 'countServicing', array());
        $this->addElement('text', 'costServicing', array('filters' => array('Digits'),));
        $this->addElement('text', 'countBags', array());
        $this->addElement('text', 'costBags', array('filters' => array('Digits'),));
        $this->addElement('text', 'countSticks', array());
        $this->addElement('text', 'costSticks', array('filters' => array('Digits'),));
        $this->addElement('text', 'comment', array());
        
    }

}