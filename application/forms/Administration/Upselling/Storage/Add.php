<?php
/**
 * @author vpriem
 * @since 27.06.2011
 */
class Yourdelivery_Form_Administration_Upselling_Storage_Add extends Default_Forms_Base {

    /**
     * @author vpriem
     * @since 27.06.2011
     */
    public function init() {

        $this->addElement('text', 'product', array("required" => true));
        $this->addElement('text', 'count', array("required" => true));
        $this->addElement('text', 'orderedAt', array("required" => true));
        $this->addElement('text', 'producer', array("required" => true));
        $this->addElement('text', 'costProduct', array('filters' => array('Digits'), "required" => true));
        $this->addElement('text', 'costDelivery', array('filters' => array('Digits'), "required" => true));
        $this->addElement('text', 'deliverEstimation', array("required" => true));
        $this->addElement('text', 'delivered', array("required" => true));
        $this->addElement('text', 'comment', array());
        
    }

}