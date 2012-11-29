<?php
/**
 * Description of OrderSheet
 * @package order
 * @subpackage pdf
 * @author mlaug
 */
class Yourdelivery_Model_Order_Pdf_Private_Single_FaxCourier extends Yourdelivery_Model_Order_Pdf_FaxAbstract{

    /**
     * @var string
     */
    protected $_filename = 'orderfax_courier.pdf';

    /**
     * Generate pdf for fax transmission
     * @author mlaug, vpriem
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
        $this->_latex->setTpl('order/private/single/bestellzettel_courier');

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
