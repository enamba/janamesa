<?php
/**
 * Use for paypal/giropay transactions
 * @author vpriem
 * @since 23.03.2011
 */
require_once(APPLICATION_PATH . '/controllers/Payment/Abstract.php');
class Payment_Paypal_GiropayController extends Payment_Abstract{

    /**
     * Call back by paypal
     * finish the transaction
     * @author vpriem
     * @since 23.03.2011
     */
    public function finishAction(){
        
        // get order
        $order = $this->_getCurrentOrder();
        
        // not finalizing the transaction
        // this will be done trough the paypal IPN
        if ($order->getKind() == 'comp') {
            return $this->_redirect('/order_company/success');
        }
        return $this->_redirect('/order_private/success');
    }

}
