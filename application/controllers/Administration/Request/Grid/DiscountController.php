<?php

/**
 * DiscountController for Discount Lightbox in order grid
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 13.06.2012
 */
class Administration_Request_Grid_DiscountController extends Default_Controller_RequestAdministrationBase {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 13.06.2012
     */
    public function infoboxAction() {

        $request = $this->getRequest();
        $rabattCodeId = $request->getParam('rabattCodeId');
        $orderId = $request->getParam('orderId');

        if ($rabattCodeId > 0) {
            try {
                $rabattCode =
                    new Yourdelivery_Model_Rabatt_Code(false, $rabattCodeId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                die;
            }
        }

        $this->view->rabattCode = $rabattCode;
        $this->view->orderId = $orderId;
    }

}
