<?php

/**
 * Description of Location
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
class Request_User_LocationController extends Default_Controller_RequestBase {

    /**
     * @todo rework, that we do not need the session here
     * @author vpriem
     * @modified Daniel Hahn <hahn@lieferando.de>
     * @since 09.02.2011
     */
    public function editAction() {

        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $location = $this->getLocation();
        if (!$location) {
            return;
        }

        // print json on post
        if ($this->getRequest()->isPost()) {

            $form = new Yourdelivery_Form_NewAddress();
            //no street and hausnr for poland
            if (preg_match("/\.pl$/", $this->config->domain->base)) {
                $form->removeElement('street');
                $form->removeElement('hausnr');
            }
            $post = $this->getRequest()->getPost();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                if ($this->getCustomer()->editAddress($values, $location->getId())) {
                    return;
                }

                //error editing
                $this->getResponse()->setHttpResponseCode(406);
                return;
            } else {
                $this->_disableView();
                $this->getResponse()->setHttpResponseCode(406);
                $errors = $form->getMessages();
                foreach ($errors as $key => $error) {
                    $message[$key] = array_shift($error);
                }

                echo json_encode($message);
                return;
            }
        }
    }

    /**
     * @todo: rework that we do not need the session here
     * @author vpriem
     * @modified Daniel Hahn <hahn@lieferando.de>
     * @since 10.02.2011
     */
    public function createAction() {
        $this->view->location = null;
        $customer = $this->getCustomer();

        $request = $this->getRequest();
        if ($request->isPost()) {

            $post = $request->getPost();
            $cityId = (integer) $post['cityId'];
            if ($cityId <= 0) {
                $form = new Yourdelivery_Form_Order_Start_Citystreet();
                if ($form->isValid($request->getPost())) {
                    $cityVerbose = new Yourdelivery_Model_City_Verbose();
                    $matches = $cityVerbose->findmatch(
                            $form->getValue('city'), $form->getValue('street'), $form->getValue('hausnr', null)
                    );
                    if (count($matches) == 1) {
                        $data = array_pop($matches);
                        $post['cityId'] = (integer) $data['cityId'];
                    }
                } else {
                    $this->logger->err('LOCATION CREATE: Yourdelivery_Form_Order_Start_Citystreet is not valid'); 
                }
            }

            $form = new Yourdelivery_Form_NewAddress();
            if ($form->isValid($post)) {
                $locationId = (integer) $customer->addAddress($form->getValues());
                try {
                    $this->view->location = new Yourdelivery_Model_Location($locationId);
                    return;
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->logger->err('LOCATION CREATE: error: ' . $e->getMessage());                   
                }
            } else {
                $this->_disableView();
                $this->getResponse()->setHttpResponseCode(406);
                $message = array();
                $errors = $form->getMessages();
                foreach ($errors as $key => $error) {
                    $this->logger->err('LOCATION CREATE: msg: ' . $error);
                    $message[$key] = array_pop($error);
                }

                echo json_encode($message);
                return;
            }
        }

        $this->getResponse()->setHttpResponseCode(406);
    }

    /**
     * mark a location as primary
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.11.2011
     */
    public function primaryAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $toggle = (boolean) $this->getRequest()->getParam('toggle');

        $location = $this->getLocation();
        if (!$location) {
            return;
        }

        $location->setPrimary($toggle);
        $location->save();
    }

    public function getAction() {

        $mode = $this->getRequest()->getParam('mode');



        $this->view->cust = $this->getCustomer();
    }

    /**
     * get a location based on given id
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.11.2011
     * @return Yourdelivery_Model_Location
     */
    private function getLocation() {

        $id = (integer) $this->getRequest()->getParam('id');

        if ($id === null) {
            $this->getResponse()->setHttpResponseCode(404);
            return null;
        }

        try {
            $location = new Yourdelivery_Model_Location($id);
            if ($location->getCustomerId() != $this->getCustomer()->getId()) {
                $this->getResponse()->setHttpResponseCode(403);
            }
            $this->view->location = $location;
            return $location;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->getResponse()->setHttpResponseCode(404);
            return null;
        }

        return null;
    }

}

?>
