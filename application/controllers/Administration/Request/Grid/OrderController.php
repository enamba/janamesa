<?php

/**
 * Description of CustomerController
 *
 * @author mlaug
 */
class Administration_Request_Grid_OrderController extends Default_Controller_RequestAdministrationBase {

    /**
     * create info box for grid to display verbose order information
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.06.2012 
     */
    public function optionsAction() {

        $request = $this->getRequest();
        $orderId = (integer) $request->getParam('orderId', 0);

        $order = null;
        //try to find according to customerId
        if ($orderId > 0) {
            try {
                $order = new Yourdelivery_Model_Order($orderId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
      
        $this->view->order = $order;
    }
    
    /**
     * create info box for grid to display verbose paypal information
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.06.2012 
     */
    public function paypalAction() {

        $request = $this->getRequest();
        $orderId = (integer) $request->getParam('orderId', 0);

        $order = null;
        //try to find according to customerId
        if ($orderId > 0) {
            try {
                $order = new Yourdelivery_Model_Order($orderId);
                $transaction = new Yourdelivery_Model_DbTable_Paypal_Transactions();
                $this->view->paypal = $transaction->getByOrder($order->getId());
                $this->view->payerId = $transaction->getPayerId($order->getId());
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
      
        $this->view->order = $order;
    }

}
