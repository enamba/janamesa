<?php
/**
 * Optivo Soap API
 * Session webservice
 * @author vpriem
 * @since 20.06.2011
 */
class Yourdelivery_Api_Optivo_Session extends Yourdelivery_Api_Optivo_Abstract {
    
    /**
     * Service name
     * @var string
     */
    protected $_serviceName = "Session";
    
    /**
     * Session id
     * @var int
     */
    private $_sessionId;
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @return int
     */
    public function getId(){
        
        return $this->_sessionId;
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @param string $id
     * @param string $username
     * @param string $password
     * @return string
     */
    public function login($id, $username, $password){
        
        return $this->_sessionId = $this->_call("login", $this->_long($id), $username, $password);
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @return ?
     */
    public function logout(){
        
        if ($this->_sessionId === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Session is empty');
        }
        
        return $this->_call("logout", $this->_sessionId);
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @param string $locale
     * @return ?
     */
    public function setLocale($locale) {
        
        if ($this->_sessionId === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Session is empty');
        }
        
        return $this->_call("setLocale", $this->_sessionId, $locale);
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @return void
     */
    public function __destruct() {
        
        if ($this->_sessionId !== null) {
            $this->logout();
        }
    }
    
}
