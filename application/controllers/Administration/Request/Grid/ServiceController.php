<?php

/**
 * Description of ServiceController
 *
 * @author mlaug
 */
class Administration_Request_Grid_ServiceController extends Default_Controller_RequestAdministrationBase {

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.06.2012
     */
    public function optionsAction() {
        $request = $this->getRequest();
        $serviceId = (integer) $request->getParam('serviceId', null);
        $service = null;
        try {
            if ( $serviceId <= 0 ){
                throw new Yourdelivery_Exception_Database_Inconsistency('no valid serviceId given');
            }
            $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }
        $this->view->service = $service;
    }
    
    /**
     * @author Daniel Hahn
     * @since 10.07.2012 
     */
    public function faxAction() {
        $request = $this->getRequest();
        $serviceId = (integer) $request->getParam('serviceId', null);
        
        $service = null;
        try {
            if ( $serviceId <= 0 ){
                throw new Yourdelivery_Exception_Database_Inconsistency('no valid serviceId given');
            }
            $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }
        $this->view->service = $service;
    }

}
