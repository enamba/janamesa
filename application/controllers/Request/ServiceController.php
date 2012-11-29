<?php

/**
 * Request Service Controller
 * @author mlaug
 */
class Request_ServiceController extends Default_Controller_RequestBase {

    /**
     * @var Yourdelivery_Model_Servicetype_Restaurant
     */
    private $_service = null;

    /**
     * @author mlaug, vpriem
     */
    public function preDispatch() {

        parent::preDispatch();

        // get id
        $id = $this->_request->getParam('id');

        // create service
        if ($id !== null) {
            try {
                $this->_service = new Yourdelivery_Model_Servicetype_Restaurant($id);
                $this->view->service = $this->_service;
                $this->view->mode = "rest";
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->getResponse()->setHttpResponseCode(404);
                die();
            }
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 15.09.2011
     */
    public function ratingAction() {
        $this->view->enableCache();
        $mode = $this->getRequest()->getParam('mode', null);
        switch ($mode) {
            case 'cater':
                $link = $this->_service->getCaterUrl();
                break;
            case 'great':
                $link = $this->_service->getGreatUrl();
                break;
            default:
                $link = $this->_service->getRestUrl();
                break;
        }
        $this->view->servicelink = $link;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 21.12.2011
     */
    public function preorderAction() {
        $this->view->enableCache();

        $request = $this->getRequest();
        $type = $request->getParam('type', "rest");
        
        /**
         * if you are on service page, you should not get a redirect
         * @author Matthias Laug <laug@lieferando.de>
         * @see http://ticket/browse/YD-3083
         */
        $back = (boolean) $request->getParam('back', true);
        $this->view->back = $back;
        
        $handlingTime = $request->getParam('handlingtime');
        $this->view->type = $type;
        $this->view->handlingtime = $handlingTime;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 04.01.2012
     */
    public function offlineAction() {

        $this->view->enableCache();
    }

    /**
     * @author mlaug
     */
    public function infoAction() {
        
    }

    /**
     * Get opening time at given day
     * @since 18.08.2011
     * @author mlaug
     */
    public function openingtimedayAction() {
        
        $request = $this->getRequest();
        $date = $request->getParam('date');
        $cityId = $request->getParam('cityId');
        $mode = $request->getParam('mode', "rest");

        $data_checked = Default_Helpers_Date::isDate($date);
        if ($data_checked === false) {
            $ts = time();
        }
        else {
            $ts = mktime(0, 0, 0, $data_checked['m'], $data_checked['d'], $data_checked['y']);
        }
        
        $this->view->openings = array();
        if ($this->_service !== null) {
            $this->view->openings = $this->_service->getOpening()->getIntervalOfDay($ts);
            $this->view->serviceDeliverTime = $this->_service->getDeliverTime($cityId);
            $this->view->mode = $mode;
        }
    }

    /**
     * @author mlaug
     */
    public function reachableAction() {

        $this->_disableView();

        $request = $this->getRequest();
        $ids = $request->getParam('ids', array());
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $kind = htmlentities($request->getParam('kind', 'priv'));
        $cityId = (integer) $request->getParam('cityId');
        $dbTable = new Yourdelivery_Model_DbTable_Restaurant();

        $toBeCached = json_encode($dbTable->checkForUnreachable(
                        $ids, $this->getCustomer()->getId(), $this->getCustomer()->isEmployee() ? $this->getCustomer()->getCompany()->getId() : null, 'rest', $kind, $cityId
                ));

        echo $toBeCached;
    }

    /**
     * send the test fax
     * @todo this needs to be secured in the backend
     * @author alex
     * @since 17.11.2010
     */
    public function testfaxAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $faxnr = $request->getParam('fax', null);
            $faxService = $request->getParam('faxService', Yourdelivery_Sender_Fax::RETARUS);
            $fax = new Yourdelivery_Sender_Fax();
            echo $fax->test($faxnr, $faxService);
        }
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }

    /**
     * toggle rating status
     * @author alex
     * @since 25.11.2010
     */
    public function toggleratingstatusAction() {
        $request = $this->getRequest();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        if ($request->isPost()) {
            $post = $request->getPost();

            if (isset($post['ratingId'])) {
                $rating = new Yourdelivery_Model_Servicetype_Rating((integer) $post['ratingId']);
                $newState = !($rating->getStatus());
                $rating->setStatus($newState);
                $rating->save();
            }

            echo Zend_Json::encode(array('state' => $newState));
        }
    }

}
