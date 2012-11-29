<?php

class Yourdelivery_Security_Request {

    /**
     * store pass
     * @var string
     */
    private $_pass;

    public function __construct() {
        $this->_pass = 'PASS';
    }

    /**
     * get singleton
     * @staticvar Yourdelivery_Security_Request $instance
     * @return Yourdelivery_Security_Request
     */
    public static function getInstance() {
        static $instance;
        if (!is_object($instance)) {
            $instance = new Yourdelivery_Security_Request();
        }
        return $instance;
    }

    /**
     * get current pass
     * @author mlaug
     * @return string
     */
    public function getPass() {
        return $this->_pass;
    }

    /**
     * get current pass
     * @author mlaug
     */
    public function createPassword() {
        // Set a cookie with an encrypted version of the password for one day
        //@todo: in testing we cannot set cookies, mh fuck them
        if (APPLICATION_ENV != "testing") {
            Default_Helpers_Web::setCookie('yd-request', $this->getPass());
        }
    }

    /**
     * verify the password
     * @author mlaug
     * @return boolean
     */
    public function verifyPassword() {
        return true;
        //oh come on
        if (APPLICATION_ENV == "testing") {
            return true;
        }
        if (Default_Helpers_Web::getCookie('yd-request') == $this->getPass()) {
            return true;
        } else {
            if (isset($_POST['YOURDELIVERY_SECURE']) && $_POST['YOURDELIVERY_SECURE'] == $this->getPass()) {
                return true;
            }
            return false;
        }
    }

}
