<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 26.06.2012
 */
abstract class Administration_Request_Autocomplete_Abstract extends Default_Controller_RequestAdministrationBase {

    /**
     * @var string
     */
    protected $_term = null;
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.06.2012
     */
    public function init() {

        parent::init();
        
        // print only json
        $this->_disableView();

        // set term
        $request = $this->getRequest();
        $this->_term = $request->getParam('term');
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.06.2012
     * @return string
     */
    protected function _getTerm() {
        
        return $this->_term;
    }
    
}
