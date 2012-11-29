<?php
/**
 * Optivo Soap API
 * Attachment webservice
 * @author vpriem
 * @since 20.06.2011
 */
class Yourdelivery_Api_Optivo_Attachment extends Yourdelivery_Api_Optivo_Abstract{
    
    /**
     * Service name
     * @var string
     */
    protected $_serviceName = "Attachment";
    
    /**
     * Session webservice
     * @var Yourdelivery_Api_Optivo_Session
     */
    private $_session;
    
    /**
     * Session id
     * @var int
     */
    private $_sessionId;
    
    /**
     * Attachment id
     * @var int
     */
    private $_attachmentId;
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @param Yourdelivery_Api_Optivo_Session $session
     * @return void
     */
    public function __construct(Yourdelivery_Api_Optivo_Session $session){
        
        $this->_session = $session;
        $this->_sessionId = $session->getId();
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @return int
     */
    public function getId(){
        
        return $this->_attachmentId;
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @param string $name
     * @param string $mimeType
     * @param string $filename
     * @param string $content
     * @return int
     */
    public function create($name, $mimeType, $filename, $content) {
        
        if ($this->_sessionId === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Session is empty');
        }
        
        if ($name === null) {
            $name = "attachment" . uniqid();
        }
        
        return $this->_attachmentId = $this->_call("create", $this->_sessionId, $name, $mimeType, $filename, $this->_byte($content));
    }

}
