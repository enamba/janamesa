<?php

/**
 * Extern controller
 * @author vpriem
 * @since 14.07.2011
 */
class Default_Controller_Auth extends Default_Controller_Base {
    
    /**
     * Set http auth
     * @author vpriem
     * @since 23.02.2011
     */
    public function preDispatch() {

        parent::preDispatch();
        
        $request = $this->getRequest();
        $response = $this->getResponse();

        if ($request->getActionName() != "required") {
            $auth = new Zend_Auth_Adapter_Http(array(
                'accept_schemes' => 'basic',
                'realm' => 'auth'
            ));

            $basicResolver = new Zend_Auth_Adapter_Http_Resolver_File();
            $basicResolver->setFile(APPLICATION_PATH . "/configs/htpasswd");

            $auth->setBasicResolver($basicResolver);

            $auth->setRequest($request);
            $auth->setResponse($response);

            $result = $auth->authenticate();
            if (!$result->isValid()) {
                return $this->_forward("required");
            }
        }
    }
    
    /**
     * 401 Authorization Required
     * @author vpriem
     * @since 23.02.2011
     */
    public function requiredAction() {
        $this->_disableView(true);
        $this->getResponse()
             ->setHttpResponseCode(401);
    }
    
}