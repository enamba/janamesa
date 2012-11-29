<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Connect
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
class Woopla_Connect {

    /**
     * default parameters
     * @var array
     */
    protected $_params = array(
        'userDetails' => array(
            'login' => 'USER',
            'password' => 'PASS'
        ),
        'callDetails' => array(
            'numberToCall' => null,
            'numberToDisplay' => 'TELEPHONE',
            'launchTimeMinutes' => '0',
            'serviceID' => 'ID',
            'externalID' => null
        ),
        'callParameters' => array(
        )
    );

    /**
     * @var Yourdelivery_Model_Servicetype_Abstract
     */
    protected $_service = null;

    /**
     * @var Yourdelivery_Model_Order
     */
    protected $_order = null;

    public function __construct() {
        $config = Zend_Registry::get('configuration');
        if (IS_PRODUCTION) {
            $this->_params['callParameters'][] = array(
                'name' => 'url',
                'value' => 'http://www.' . $config->domain->base . '/get_call'
            );
        } else {
            $this->_params['callParameters'][] = array(
                'name' => 'url',
                'value' => 'http://USER:PASS@staging.' . $config->domain->base . '/get_call'
            );
        }

        $this->logger = Zend_Registry::get('logger');
    }

    /**
     * set the service which should be affirmed
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 27.03.2011
     * @param Yourdelivery_Model_Order $order 
     */
    public function setOrder(Yourdelivery_Model_Order $order) {
        $this->_order = $order;
        $this->_params['callDetails']['externalID'] = $order->getId();
        $this->setService($order->getService());
        $this->logger->debug(sprintf('WOOPLA: setting order #%d', $order->getId()));
        return $this;
    }

    /**
     * set the service which should be called
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 27.03.2011
     * @param Yourdelivery_Model_Servicetype_Abstract $service
     */
    private function setService(Yourdelivery_Model_Servicetype_Abstract $service) {
        $this->_service = $service;
        $this->_params['callDetails']['numberToCall'] = $this->transformToE164($service->getTel());
        $this->logger->debug(sprintf('WOOPLA: setting service #%d', $service->getId()));
        return $this;
    }

    /**
     * checks if call has been placed
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 27.03.2011
     * @return boolean
     */
    public function call() {
        if ($this->_service === null || $this->_order === null) {
            $this->logger->warn('WOOPLA: order or service object is missing');
            return false;
        }

        if ($this->_params['callDetails']['numberToCall'] === null || $this->_params['callDetails']['externalID'] === null) {
            $this->logger->warn('WOOPLA: numberToCall or externalID is missing');
            return false;
        }
        $client = null;
        try {
            $client = new Zend_Soap_Client('https://woop.la/mywoopla/systemInterfaces/webservices/AlertCall/alertCall.asmx?WSDL');
        } catch (Exception $exc) {
            $this->logger->error('WOOPLA: SOAP-Object could not be created: ' . $exc->getTraceAsString());
            return false;
        }

        try {
            $response = $client->scheduleDialog($this->_params);
        } catch (Exception $e) {
            $this->logger->error('WOOPLA: Could not shedule Dialog: ' . $e->getTraceAsString());
            return false;
        }

        if ($response->scheduleDialogResult->resultCode == 200) {
            $this->logger->info(sprintf('WOOPLA: succesfully placed call to %s for order #%d', $this->_params['callDetails']['numberToCall'], $this->_order->getId()));
            return true;
        }
        $this->logger->error(sprintf('WOOPLA: failed to place call to %s for order #%d', $this->_params['callDetails']['numberToCall'], $this->_order->getId()));
        return false;
    }

    /**
     * parse the number to apply E164
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 27.03.2011
     * @param string $tel
     * @return string
     */
    private function transformToE164($tel) {
        if (IS_PRODUCTION) {
            if (substr($tel, 0, 2) == '33') {
                return $tel;
            } else if (substr($tel, 0, 1) == '0') {
                $tel = '33' . substr($tel, 1);
                return $tel;
            }
        }
        return '491758042039'; // nr nicht existent
    }

}
