<?php
/**
 * @author vpriem
 * @since 29.06.2011
 */
class Administration_Request_Upselling_GoodsController extends Default_Controller_RequestAdministrationBase {

    /**
     * Get voucher of a service
     * @author vpriem
     * @since 29.06.2011
     */
    public function voucherAction(){
        
        $this->_disableView();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            
            if (isset($post['id'])) {
                try {
                    $service = new Yourdelivery_Model_Servicetype_Restaurant($post['id']);
                    if ($service->getId()) {
                        return $this->_json(array(
                            'voucher' => Yourdelivery_Model_Billing::getVoucherAmounts($service->getId(), 2) + $service->getBalance()->getAmount()
                        ));
                    }
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                }
            }
        }
        
        return $this->_json(array(
            'error' => __b("Ein Fehler ist aufgetreten.")
        ));

    }
    
    /**
     * Tells whether locale overriding is forbidden within whole controller
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 6.07.2012
     *
     * @return boolean
     */
    protected function _isLocaleFrozen() {
        return true;
    }
}
