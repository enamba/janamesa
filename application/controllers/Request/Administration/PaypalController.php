<?php

/**
 * Backend PaypalController for ordergrid
 *
 * @author daniel
 */
class Request_Administration_PaypalController extends Default_Controller_RequestAdministrationBase {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 15.06.2012
     */
    public function infoAction() {

        $request = $this->getRequest();

        $orderId = $request->getParam('orderId');

        $this->_getUserData($orderId);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 15.06.2012
     */
    public function blacklistAction() {

        $request = $this->getRequest();
        $orderId = $request->getParam('orderId');

        $form = new Yourdelivery_Form_Administration_Blacklist_Paypal();
        $form->setAction("/administration_blacklist/paypal");
        $this->view->form = $form;
        $this->_getUserData($orderId);

        $form->getElement('bl_payerId')->setValue($this->view->payerId);

        $form->getElement('bl_paypal_email')->setValue($this->view->email);
        $form->getElement('bl_orderId')->setValue($orderId);
        $form->getElement('bl_orderId')->setRequired(true);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 15.06.2012
     */
    public function discountsAction() {
        $request = $this->getRequest();
        $payerId = $request->getParam('payerId');
        
        if ( strlen($payerId) <= 0 ){
            $this->getResponse()->setHttpResponseCode(404);
            $this->_disableView(true);
            return;
        }

        $transaction = new Yourdelivery_Model_DbTable_Paypal_Transactions();

        $results = $transaction->findDiscountsByPayerId($payerId);

        foreach ($results as &$entry) {

            $discountId = $entry['rabattCodeId'];
            try {
                $discount =
                    new Yourdelivery_Model_Rabatt_Code(false, $discountId);
                $entry['rabattCode'] = $discount;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

            }
        }

        $this->view->discounts = $results;
        $this->view->payerId = $payerId;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 15.06.2012
     */
    protected function _getUserData($orderId) {
        $transaction = new Yourdelivery_Model_DbTable_Paypal_Transactions();

        $response = $transaction->getUserData($orderId);

        if ($response['EMAIL']) {
            $this->view->email = $response['EMAIL'];
        }

        if ($response['PAYERID']) {
            $this->view->payerId = $response['PAYERID'];
        }

        if ($response['FIRSTNAME']) {
            $this->view->prename = $response['FIRSTNAME'];
        }

        if ($response['LASTNAME']) {
            $this->view->name = $response['LASTNAME'];
        }

        if ($response['PAYERSTATUS']) {
            $this->view->status = $response['PAYERSTATUS'];
        }
    }

}

?>
