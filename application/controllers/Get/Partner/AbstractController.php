<?php

/**
 * @package Yourdelivery
 * @subpackage PartnerAPI
 */

/**
 * @author Matthias Laug <laug@lieferando.de>
 * @since 28.08.2012
 */
abstract class AbstractApiPartnerController extends Default_Controller_RestBase {
   
    protected $_service = null;
    
    /**
     * deactivate output of fidelity points
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function preDispatch() {
        parent::preDispatch();
        $this->show_fidelity = false;
    }
    

    /**
     * get the service based on given access key
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     *
     * @param stdClass $json
     * @throws Yourdelivery_Exception_Database_Inconsistency
     * @return Yourdelivery_Model_Servicetype_Restaurant
     */
    protected function _getService(stdClass $json = null) {
        if ($this->_service === null) {
            
            if ($json === null || $json->access === null) {
                $request = $this->getRequest();
                if ($request->getParam('access')) {
                    try {
                        $this->_service = new Yourdelivery_Model_Servicetype_Restaurant(null, $request->getParam('access'));
                        $this->logger->debug(sprintf('API - _getService: get access for service #%s %s via POST with access %s', $this->_service->getId(), $this->_service->getName(), $request->getParam('access')));
                        return $this->_service;
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->logger->debug(sprintf("API _getService: didn't get access for any service via POST with access %s", $request->getParam('access')));
                        throw new Yourdelivery_Exception_Database_Inconsistency();
                    }
                }
                $this->logger->debug(sprintf("API _getService: didn't get access for any service via POST without access"));
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }

            try {
                $this->_service = new Yourdelivery_Model_Servicetype_Restaurant(null, $json->access);
                $this->logger->debug(sprintf('API - _getService: get access for service #%s via JSON with access %s', $this->_service->getId(), $json->access));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->debug(sprintf("API - _getService: didn't get access for service via JSON with access %s", $json->access));
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }
            
        } elseif ($this->_service === null && $json === null) {
            $this->logger->debug(sprintf("API - _getService: didn't get access for service without json"));
            throw new Yourdelivery_Exception_Database_Inconsistency();
        }
        
        return $this->_service;
    }
    
}

?>
