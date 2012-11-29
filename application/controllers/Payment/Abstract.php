<?php

/**
 * @author vpriem
 * @since 17.11.2010
 */
abstract class Payment_Abstract extends Default_Controller_Base {

    /**
     * Call back by paypal
     * if the transaction was aborded
     * @author vpriem
     * @since 17.11.2010
     */
    public function cancelAction() {

        return $this->_redirect('/order_basis/payment');
    }

    /**
     * this will return current order
     * @author mlaug
     * @since 19.07.2011
     * @param int $orderId
     * @return Yourdelivery_Model_Order 
     */
    protected function _getCurrentOrder($orderId = null) {

        if ($orderId === null) {
            $orderId = $this->session->currentOrderId;
        }

        if (!$orderId) {
            $this->logger->warn('tryed to access payment page without any orderId in session');
            unset($this->session->currentOrderId);
            throw new Yourdelivery_Exception_NoPaymentData('No orderId provided');
        }

        try {
            $order = new Yourdelivery_Model_Order($orderId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->crit(sprintf('could not find order by id #%d on payment page', $orderId));
            throw new Yourdelivery_Exception_NoPaymentData('Could not find order #' . $orderId);
        }

        return $order;
    }

}
