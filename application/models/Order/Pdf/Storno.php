<?php
/**
 * Description of StornorSheet
 * @package order
 * @subpackage pdf
 * @author Allen Frank <frank@lieferando.de>
 */
class Yourdelivery_Model_Order_Pdf_Storno extends Yourdelivery_Model_Order_Pdf_FaxAbstract{


    /**
     * Generate pdf for fax transmission
     * @author Allen Frank <frank@lieferando.de>
     * @return string
     */
    public function generatePdf(){

        $order = $this->getOrder();
        if (!is_object($order)) {
            return null;
        }

        $service  = $order->getService();
        $location = $order->getLocation();
        $customer = $order->getCustomer();

        if (!is_object($service) || !is_object($location) || !is_object($customer)) {
            return null;
        }

        //set pdf latex template
        $this->_latex->setTpl('storno');

        //append order
        $this->assign('order', $order);

        //compile template using smarty
        $pdf = $this->_latex->compile();

        //if file does not exists just return null
        if (!file_exists($pdf)) {
            return null;
        }

        return $pdf;
    }

}