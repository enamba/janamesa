<?php

/**
 * Info container for topup printer
 *
 * @author Alex Vait 
 * @since 30.08.2012
 */
class Administration_Request_Grid_PrinterController extends Default_Controller_RequestAdministrationBase {

    /**
     * create info box for grid to display printer states history
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 30.08.2012 
     */
    public function infoboxAction() {

        $request = $this->getRequest();
        $printerId = (integer) $request->getParam('printerId', null);
        
        if (!is_null($printerId)) {
            $states_history = Yourdelivery_Model_DbTable_Printer_StatesHistory::getStatesHistory($printerId);
        }
        
        $this->view->states_history = $states_history;
    }

}
