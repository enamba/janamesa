<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 01.08.2012 
 */
class Administration_Request_BillingController extends Default_Controller_RequestAdministrationBase {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 01.08.2012 
     * @return boolean
     */
    protected function _isLocaleFrozen() {
        return true;
    }
    
    /**
     * Send bill via fax, email or post
     * @author vpriem
     * @since 01.08.2010
     * @return void
     */
    public function sendAction() {
        // disable view
        $this->_helper->viewRenderer->setNoRender(true);

        // post request
        $request = $this->getRequest();
        if ($request->isPost()) {

            $id = (integer) $request->getParam('id');
            $viafax = (boolean) $request->getParam('viafax', false);
            $viaemail = (boolean) $request->getParam('viaemail', false);
            $viapost = (boolean) $request->getParam('viapost', false);
            $faxnumber = $request->getParam('faxnumber');
            $emailaddr = $request->getParam('emailaddr');
            $sendcsv = $request->getParam('sendcsv', false);
            $sign = (boolean) $request->getParam('sign', false);

            // get bill
            try {
                if ($id <= 0) {
                    throw new Yourdelivery_Exception_Database_Inconsisten('id smaller zero given');
                }
                $bill = new Yourdelivery_Model_Billing($id);
            } catch (Yourdelivery_Exception_DatabaseInconsistency $e) {
                echo Zend_Json::encode(array('error' => "Die Rechnung konnte nicht gefunden werden"));
                return;
            }

            //send out billings;
            $status = true;
            $status = $status && ($viaemail ? $bill->sendViaEmail($emailaddr, $sendcsv, $sign) : true);
            $status = $status && ($viafax ? $bill->sendViaFax($faxnumber) : true);
            $status = $status && ($viapost ? $bill->sendViaPost() : true);

            //overwrite current selection for later usage
            $billRef = $bill->getReference();
            if (is_object($billRef)) {
                // the case "both" is available only for restaurants
                if (($viafax) && ($viaemail) && $billRef instanceof Yourdelivery_Model_Servicetype_Abstract) {
                    $billRef->setBillDeliver('all');
                } else if ($viafax) {
                    $billRef->setBillDeliver('fax');
                } else if ($viaemail) {
                    $billRef->setBillDeliver('email');
                } else if ($viapost) {
                    $billRef->setBillDeliver('post');
                }
                $billRef->save();
            }

            $json_obj = array(
                "error" => $status ? null : 'could not send bill',
                "success" => $status ? 'successfully send out bill' : null,
                "id" => $bill->getId(),
                "status" => $bill->getStatus());

            echo Zend_Json::encode($json_obj);
        }
    }
    
}
