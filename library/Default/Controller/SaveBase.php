<?php
/**
 * Description of RequestBase
 * @package core
 * @subpackage controller
 * @author mlaug
 */
class Default_Controller_SaveBase extends Default_Controller_Base{

    const IDENT = "dslsbkjvbiflashlsalh384nassdz391js";

    const ERROR = 0;

    const SUCCESS = 1;

    public function preDispatch(){
        parent::preDispatch();

        // get ident
        $request = $this->getRequest();
        $ident = $request->getParam('ident');

        if (($ident === null || $ident != self::IDENT) && APPLICATION_ENV == "production"){
            $this->error(__('Sie haben auf diesen Bereich keinen Zugriff'));
            $this->_redirect($this->config->hostname);
        }

        // standard code
        $this->getResponse()->setHeader('Content-Type', 'text/xml');
        $this->view->assign('code', self::SUCCESS);

    }

}
