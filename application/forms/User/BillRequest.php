<?php

/**
 * Description of BillRequest
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
class  Yourdelivery_Form_User_BillRequest extends Default_Forms_Base {
    
    public function init(){
        
        $this->initName(true);
        $this->initPrename(true);
        $this->initStreetHausNr(true);
        $this->initAutocompletePlz();
        $this->initCompanyName(false);
        $this->addElement('submit', __('anfordern'));
    }
    
}
