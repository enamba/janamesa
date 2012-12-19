<?php

/**
 * Index Controller
 *
 * @author mlaug
 * @copyright Yourdelivery
 */
class IndexController extends Default_Controller_Base {

    /**
     * @author mlaug
     */
    public function indexAction() {
        
        $this->view->enableCache();
        $this->view->extra_css = 'home';

        // clear cooperation in session
        unset($this->session->partner);
        
        if(is_null(Default_Helpers_Web::getCookie('yd-referer'))){
            if(isset($_SERVER['HTTP_REFERER'])){
                Default_Helpers_Web::setCookie('yd-referer', $_SERVER['HTTP_REFERER']);
            }
        }
        
        $this->view->news = Default_Helpers_Web::getBlogNews($this->config->domain->base);
        $this->setCache(86400);
    }

    /**
     * For Taxiresto only, MIWIM coop
     * @deprecated
     * @author Vincent Priem <priem@lieferando.de>
     * @since 10.01.2012
     */
    public function miwimAction() {
        
        $this->_redirect('/');
    }
    
    /**
     * check if there is a viewCustomer in session. If so, we unset the
     * current session and init a new session with this customer. This is needed
     * for the company view login
     * @author mlaug
     * @since 25.11.2010
     */
    public function resetloginAction() {
        if (!is_object($this->session->viewCustomer)) {
            $this->session->unsetAll();
        } else {
            $customer = $this->session->viewCustomer;
            $this->session->unsetAll();
            $this->session->customerId = $customer->getId();
        }
        $this->_redirect('/');
    }

    /**
     * just a blank page
     * @author mlaug
     * @since 30.11.2010
     */
    public function blankAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }

    /**
     * just a blank page, but we may do something with the session
     * 
     * try to fix session problems with IE here, so we redirect from the page
     * where we want to insert the iframe to this page and redirect back to 
     * where we startet from. This way we can collect our cookie and prevent
     * problems with cross domain cookies
     * @author mlaug
     * @since 30.11.2010
     */
    public function startsessionAction() {
        $redirect = $this->getRequest()->getParam('redirect', null);
        if ($redirect !== null) {
            $this->logger->debug('Redirect after starting session to ' . $redirect);
            $this->_redirect($redirect);
        }
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }

    /**
     * A dummmy page to validate ab tests
     * @author vpriem
     * @since 29.11.2010
     */
    public function successAction() {
        
    }

}

