<?php

/**
 * Description of OrderSheet
 *
 * @package order
 * @subpackage pdf
 * @author mlaug
 */
class Yourdelivery_Model_Order_Pdf_Company_Single_Fax extends Yourdelivery_Model_Order_Pdf_FaxAbstract{

    protected $_filename = 'orderfax.pdf';

    /**
     * generate pdf for fax transmission
     * @author mlaug
     * @return string
     */
    public function generatePdf(){

        if ( !is_object($this->getOrder()) ){
            return null;
        }
        
        $service = $this->getOrder()->getService();
        $location = $this->getOrder()->getLocation();
        $customer = $this->getOrder()->getCustomer();
        
        if ( !is_object($service) || !is_object($location) || !is_object($customer) ){
            return null;
        }

        //set pdf latex template
        $this->_latex->setTpl('order/company/single/bestellzettel');

        //append order
        $this->assign('order',$this->getOrder());

        //compile template using smarty
        $pdf = $this->_latex->compile();

        //if file does not exists just return null
        if ( !file_exists($pdf) ){
            return null;
        }
        
        return $pdf;
    }


}
