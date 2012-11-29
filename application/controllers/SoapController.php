<?php

/**
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 04.05.2012
 * SOAP handling controller
 */
class SoapController extends Default_Controller_Base {

    const SOAP_ENCODING = 'UTF-8';

    /**
     * Quasi-constructor (initializer) - only some parametters setting is expected
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @return void
     */
    public function init() {
        parent::init();

        $this->_disableView();
    }

    /**
     * SOAP handling action
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @return void
     */
    public function indexAction() {
                              
        if ($this->getRequest()->isPost()) {
            
            $soapServer = new SoapServer(null, array(
                        'uri' => '',
                        'encoding' => self::SOAP_ENCODING
                    ));
            $soapServer->setObject(new Yourdelivery_Soap_WiercikListener($this->logger));
            $soapServer->handle();
        }
    }

    /**
     * Filters client by remote IP making redirection when current IP is not allowed
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @return void
     */
    protected function forceIp() {
                        
        if (IS_PRODUCTION) {           
                if ($_SERVER['REMOTE_ADDR'] == $this->config->pheanstalk->host) {
                    // SOAP service can be accessed
                    return true;
                }           
            // Current remote IP is not allowed
            $this->logger->warn( "Unexpected SOAP access try from IP: {$_SERVER['REMOTE_ADDR']}");
            $this->_redirect("/");
        }
    }

}
