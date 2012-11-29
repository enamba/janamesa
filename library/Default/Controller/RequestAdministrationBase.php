<?php

/**
 * Description of Default_Controller_RequestAdministrationBase
 * @package core
 * @subpackage controller
 * @author vpriem
 * @since 10.11.2010
 */
class Default_Controller_RequestAdministrationBase extends Default_Controller_RequestBase {

    /**
     * Initializer
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 13.06.2012
     *
     * @return void
     */
    public function init() {
        parent::init();
        
        // Language overriding works only for controllers where _isLocaleFrozen returns false
        $this->_overrideLocale();
    }

    public function preDispatch() {

        if (APPLICATION_ENV == "testing") {
            return;
        }

        // check if user logged in
        if ($this->session_admin->admin === null) {
 
            //throw new Yourdelivery_Exception_Insecure('Admin session has expired, administrator object has gone away :(');
            $this->getResponse()->setHttpResponseCode(501);
            $this->_disableView();
            $this->getRequest()->setDispatched(true);
            return;   
        }

        parent::preDispatch();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 30.09.2011
     * @param string $action
     * @param string $modelType
     * @param int $id
     */
    protected function _trackUserMove($action, $modelType = false, $id = false) {

        if (is_null($this->session_admin->admin))  {
            return;
        }
        
        $dbTable = new Yourdelivery_Model_DbTable_Admin_Access_Tracking();

        $insert = array(
            'action' => $action,
            'adminId' => $this->session_admin->admin->getId(),
        );
        if ($modelType && $id) {
            $insert['modelType'] = $modelType;
            $insert['modelId'] = $id;
        }

        if ($action) {
            $dbTable->insert($insert);
        }
    }

    /**
     * Locale cookie name for backend
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 6.07.2012
     *
     * @return string
     */
    protected function _getLocaleCookieName() {
        return 'yd-be-locale';
    }

    /**
     * Allows language changing in backend requests
     * Warning! For some administration controllers it should be overriden!
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 6.07.2012
     *
     * @return boolean
     */
    protected function _isLocaleFrozen() {
        return false;
    }
}
