<?php

/**
 * Description of FidelityController
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
class Request_User_FidelityController extends Default_Controller_RequestBase {

    /**
     * add a new transaction to the customer
     * 
     * @since 15.11.2011
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function addAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        //must be post
        if (!$this->getRequest()->isPost() || $this->getRequest()->getParam('validate') != '__**') {
            $this->getResponse()->setHttpResponseCode(403);
            return;
        }

        $action = $this->getRequest()->getParam('action');

        if ($this->getCustomer()->getFidelity() instanceof Yourdelivery_Model_Customer_Fidelity) {
            switch ($action) {
                default:
                    return;
                case 'facebookfan':
                    $data = urldecode($this->getRequest()->getParam('href'));
                    break;
            }
            $this->getCustomer()->getFidelity()->addTransaction($action, $data);
            $this->getResponse()->setHttpResponseCode(201);
        }
    }

}

?>
