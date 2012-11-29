<?php

/**
 * @author vait
 * @since 01.08.2010
 */
class Yourdelivery_Form_Administration_Inventory_Create extends Default_Forms_Base {

    /**
     * Initialize form
     * @author vait
     * @since 01.08.2010
     * @return void
     */
    public function init() {

        $item = new Zend_Form_SubForm();
        $item->addElement('text', 'restaurantId', array());
        $item->addElement('text', 'countCanton2626', array());
        $item->addElement('text', 'countCanton2828', array());
        $item->addElement('text', 'countCanton3232', array());
        $item->addElement('text', 'specialCostCanton2626', array());
        $item->addElement('text', 'specialCostCanton2828', array());
        $item->addElement('text', 'specialCostCanton3232', array());
        $item->addElement('text', 'countServicing', array());
        $item->addElement('text', 'countBags', array());
        $item->addElement('text', 'countSticks', array());

        $item->addElement('text', 'countFlyer', array());
        $item->addElement('text', 'typeFlyer', array());
        $item->addElement('text', 'colorOneFlyer', array());
        $item->addElement('text', 'colorTwoFlyer', array());
        $item->addElement('text', 'colorThreeFlyer', array());

        $item->addElement('text', 'printerCostShare', array());
        $item->addElement('text', 'printerCostPercent', array());
        $item->addElement('text', 'printerOwn', array());
        $item->addElement('text', 'printerFormat', array());
        $item->addElement('text', 'printerPrio', array());
        $item->addElement('text', 'printerNextDate', array());

        $item->addElement('text', 'website', array());
        $item->addElement('text', 'websiteCost', array());
        $item->addElement('text', 'colorOneWebsite', array());
        $item->addElement('text', 'colorTwoWebsite', array());
        $item->addElement('text', 'colorThreeWebsite', array());

        $item->addElement('text', 'terminal', array());
        $item->addElement('text', 'terminalBail', array());

        $status = new Zend_Form_SubForm();
        $status->addElement('text', 'statusNeeds', array());
        $status->addElement('text', 'statusCommentNeeds', array());
        $status->addElement('text', 'statusPrinterCost', array());
        $status->addElement('text', 'statusCommentPrinterCost', array());
        $status->addElement('text', 'statusWebsite', array());
        $status->addElement('text', 'statusCommentWebsite', array());
        $status->addElement('text', 'statusTerminal', array());
        $status->addElement('text', 'statusCommentTerminal', array());
        $status->addElement('text', 'statusFlyer', array());
        $status->addElement('text', 'statusCommentFlyer', array());

        $this->addSubForms(array(
            'item' => $item,
            'status' => $status)
        );
    }

}