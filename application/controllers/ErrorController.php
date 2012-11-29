<?php

/**
 * Create a notify
 * @author mlaug
 */
class Yourdelivery_Service_Notifier_Error {

    protected $_environment;
    protected $_mailer;
    protected $_session;
    protected $_error;
    protected $_profiler;

    /**
     * create the basic
     * @author mlaug
     * @since 30.08.2010
     * @param string $environment
     * @param ArrayObject $error
     * @param Zend_Mail $mailer
     * @param Zend_Session_Namespace $session
     * @param Zend_Db_Profiler $profiler
     * @param array $server
     */
    public function __construct(
    $environment, $error, Zend_Mail $mailer, Zend_Session_Namespace $session, Zend_Db_Profiler $profiler, Array $server) {

        $this->_environment = $environment;
        $this->_mailer = $mailer;
        $this->_error = $error;
        $this->_session = $session;
        $this->_profiler = $profiler;
        $this->_server = $server;
    }

    /**
     * @author mlaug
     * @since 30.08.2010
     * @return string
     */
    public function getFullErrorMessage() {
        $message = '';

        if (!empty($this->_server['SERVER_ADDR'])) {
            $message .= "<p>Server IP: " . $this->_server['SERVER_ADDR'] . "</p>";
        }

        if (!empty($this->_server['HTTP_USER_AGENT'])) {
            $message .= "<p>User agent: " . $this->_server['HTTP_USER_AGENT'] . "</p>";
        }

        if (!empty($this->_server['HTTP_X_REQUESTED_WITH'])) {
            $message .= "<p>Request type: " . $this->_server['HTTP_X_REQUESTED_WITH'] . "</p>";
        }

        $message .= "<p>Server time: " . date("Y-m-d H:i:s") . "</p>";
        $message .= "<p>RequestURI: " . $this->_error->request->getRequestUri() . "</p>";

        if (!empty($this->_server['HTTP_REFERER'])) {
            $message .= "<p>Referer: " . $this->_server['HTTP_REFERER'] . "</p>";
        }

        $message .= "<p>Message: " . htmlentities($this->_error->exception->getMessage()) . "</p>";
        $message .= "<p>Trace:\n" . $this->_error->exception->getTraceAsString() . "</p>";
        $message .= "<p>Request data: " . var_export($this->_error->request->getParams(), true) . "</p>";

        //only if profiler is enabled
        if (APPLICATION_ENV == "development") {
            if (is_object($this->_profiler) && $this->_profiler->getEnabled()) {
                $query = $this->_profiler->getLastQueryProfile()->getQuery();
                $queryParams = $this->_profiler->getLastQueryProfile()->getQueryParams();
            }

            $message .= "<p>Last database query: " . $query . "</p>";
            $message .= "<p>Last database query params: " . var_export($queryParams, true) . "</p>";
        }

        $it = $this->_session->getIterator();

        /**
         * @todo get according session values
         * and build correctly up
         */
        return $message;
    }

    /**
     * get a short message
     * @author mlaug
     * @since 30.08.2010
     * @return string
     */
    public function getShortErrorMessage() {
        $message .= "Message: " . $this->_error->exception->getMessage() . "\n\n";
        $message .= "Trace:\n" . $this->_error->exception->getTraceAsString() . "\n\n";       
        return $message;
    }

    /**
     * send out a notify to developers
     * @author mlaug
     * @since 30.08.2010
     * @return boolean
     */
    public function notify() {
        if (in_array($this->_environment, array('development', 'staging'))) {
            return false;
        }

        // only developers
        Yourdelivery_Sender_Email::error($this->getFullErrorMessage(), true);
        return true;
    }

}

class ErrorController extends Default_Controller_Base {

    /**
     * notifier class
     * @var Yourdelivery_Service_Notifier_Error
     */
    private $_notifier;
    /**
     * the error handler
     * @var <type>
     */
    private $_error;
    /**
     * current enviroment
     * @var string
     */
    private $_environment;

    /**
     * initialize everything
     * @author mlaug
     * @since 30.08.2010
     */
    public function init() {
        parent::init();

        $bootstrap = $this->getInvokeArg('bootstrap');

        $error = $this->_getParam('error_handler');
        if (APPLICATION_ENV == "testing") {
            die($error->exception->getMessage() . $error->exception->getTraceAsString());
        }
        $environment = $bootstrap->getEnvironment();

        //use default, if nothing else set
        $mailer = new Zend_Mail();

        $session = new Zend_Session_Namespace('Default');
        $database = Zend_Registry::get('dbAdapter');

        //get the profiler if enabled
        $profiler = $database->getProfiler();

        $this->_notifier = new Yourdelivery_Service_Notifier_Error(
                        $environment,
                        $error,
                        $mailer,
                        $session,
                        $profiler,
                        $_SERVER
        );

        $this->_error = $error;
        $this->_environment = $environment;

    }

    /**
     * check what happens?
     * @author mlaug
     * @since 30.08.2010
     */
    public function errorAction() {
        //reset if this is set to true
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', false);

        switch ($this->_error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->logger->err('PAGE NOT FOUND: ' . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI']) . ' - refering from ' . $_SERVER['HTTP_REFERER'];
                
                // for satellite
                if (!isBaseUrl()) {
                    return $this->_forward("notfound", "satellite");
                }
                
                return $this->_forward('notfound');
                break;

            default:
                $currentCode = (integer) $this->getResponse()->getHttpResponseCode();
                if ($currentCode < 500) {
                    $this->getResponse()->setHttpResponseCode(500);
                }
                $this->_applicationError();
                break;
        }

        $this->view->message = $this->message;
        $this->logger->crit('Run into error: ' . $this->message);
        error_log($this->_notifier->getFullErrorMessage());
    }

    /**
     * Display when the session expired
     * @author vpriem
     * @since 13.12.2010
     */
    public function sessionAction() {

        // set view name
        $viewName = "_" . $this->config->domain->base . "/session.htm";
        if (file_exists(APPLICATION_PATH . "/views/smarty/template/" . $this->view->getLayout() . "/error/" . $viewName)) {
            $this->view->setName($viewName);
        }

    }

    /**
     * standard error action without any logging
     */
    public function throwAction() {
        
    }

    /**
     * Standard 404 page
     * @author vpriem
     * @since 13.12.2010
     */
    public function notfoundAction() {
        
        $this->getResponse()->setHttpResponseCode(404);
        
        $this->view->GATrackPageview = "/404" . $_SERVER['REQUEST_URI'];
        
        // set view name
        $viewName = "_" . $this->config->domain->base . "/notfound.htm";
        if (file_exists(APPLICATION_PATH . "/views/smarty/template/" . $this->view->getLayout() . "/error/" . $viewName)) {
            $this->view->setName($viewName);
        }
    }

    /**
     * standard error page for payment
     */
    public function throwpaymentAction() {
        $this->logger->crit('Ran into an payment error');
        $this->_unsetSession();
    }

    /**
     * rest api access denied
     */
    public function accessAction() {
        $this->_helper->ViewRenderer->setNoRender(true);
    }

    private function _unsetSession() {
        $session = new Zend_Session_Namespace('Default');
        if ( !$session->isLocked() ){
            unset($session->currentOrder);
            unset($session->currentOrderToPay);
            unset($session->finishedOrder);
            $session->rememberOrder = array();
        }
    }

    /**
     * do something for the application error
     */
    private function _applicationError() {

        //$this->_unsetSession();

        $fullMessage = $this->_notifier->getFullErrorMessage();
        $shortMessage = $this->_notifier->getShortErrorMessage();

        switch ($this->_environment) {
            case 'production':
                $this->message = $shortMessage;
                break;
            case 'development':
                $this->message = $shortMessage;
                break;
            default:
                $this->message = nl2br($fullMessage);
        }

        $this->_notifier->notify();
    }

}
