<?php
/**
 * Optivo Soap API
 * @author vpriem
 * @since 20.06.2011
 */
class Yourdelivery_Api_Optivo {
    
    /**
     * Session webservice
     * @var Yourdelivery_Api_Optivo_Session
     */
    private $_session;
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @return void
     */
    public function __construct($id = null, $username = null, $password = null){
        
        if ($id !== null && $username !== null && $password !== null) {
            $this->getSession()->login($id, $username, $password);
        }
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @return Yourdelivery_Api_Optivo_Session
     */
    public function getSession() {
        
        if ($this->_session === null) {
            $this->_session = new Yourdelivery_Api_Optivo_Session();
        }
        
        return $this->_session;
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @return Yourdelivery_Api_Optivo_Mailing
     */
    public function createMailing() {
        
        if ($this->_session === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Session is empty');
        }
        
        return new Yourdelivery_Api_Optivo_Mailing($this->_session);
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @return Yourdelivery_Api_Optivo_Attachment
     */
    public function createAttachment() {
        
        if ($this->_session === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Session is empty');
        }
        
        return new Yourdelivery_Api_Optivo_Attachment($this->_session);
    }
    
}
