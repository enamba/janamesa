<?php
/**
 * Optivo Soap API
 * Mailing webservice
 * @author vpriem
 * @since 20.06.2011
 */
class Yourdelivery_Api_Optivo_Mailing extends Yourdelivery_Api_Optivo_Abstract{
    
    /**
     * Service name
     * @var string
     */
    protected $_serviceName = "Mailing";
    
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
     * Mailing id
     * @var int
     */
    private $_mailingId;
    
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
        
        return $this->_mailingId;
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @param string $mailingType regular|event|confirmation|template
     * @param string $name
     * @param string $mimeType
     * @param array|string $recipientListIds
     * @param string $senderEmailPrefix
     * @param string $senderName
     * @param string $charset
     * @return int
     */
    public function create($mailingType, $name, $mimeType, $recipientListIds, $senderEmailPrefix, $senderName, $charset = "UTF-8") {
        
        if ($this->_sessionId === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Session is empty');
        }
        
        if ($name === null) {
            $name = "mail" . uniqid();
        }
        
        if (!is_array($recipientListIds)) {
            $recipientListIds = array($recipientListIds);
        }
        
        if (strpos($senderEmailPrefix, '@')) {
            $senderEmailPrefix = strstr($senderEmailPrefix, '@', true);
        }
        
        return $this->_mailingId = $this->_call("create", $this->_sessionId, $mailingType, $name, $mimeType, $this->_long($recipientListIds), $senderEmailPrefix, $senderName, $charset);
    }

    /**
     * @author vpriem
     * @since 20.06.2011
     * @param string $subject
     * @return ?
     */
    public function setSubject($subject) {
        
        if ($this->_mailingId === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Mailing is empty');
        }
        
        return $this->_call("setSubject", $this->_sessionId, $this->_long($this->_mailingId), $subject);
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @param string $mimeType text/plain|text/html
     * @param string $content
     * @return ?
     */
    public function setContent($mimeType, $content) {
        
        if ($this->_mailingId === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Mailing is empty');
        }
        
        return $this->_call("setContent", $this->_sessionId, $this->_long($this->_mailingId), $mimeType, $content);
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @return ?
     */
    public function start() {
        
        if ($this->_mailingId === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Mailing is empty');
        }
        
        return $this->_call("start", $this->_sessionId, $this->_long($this->_mailingId));
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @return string NEW|SENDING|DONE|CANCELLED
     */
    public function getStatus() {
        
        if ($this->_mailingId === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Mailing is empty');
        }
        
        return $this->_call("getStatus", $this->_sessionId, $this->_long($this->_mailingId));
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @param array|int $attachmentIds
     * @return ?
     */
    public function setAttachmentIds($attachmentIds) {
        
        if ($this->_mailingId === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Mailing is empty');
        }
        
        if (!is_array($attachmentIds)) {
            $attachmentIds = array($attachmentIds);
        }
        
        return $this->_call("setAttachmentIds", $this->_sessionId, $this->_long($this->_mailingId), $this->_long($attachmentIds));
    }
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @param Yourdelivery_Api_Optivo_Attachment $attachment
     * @return ?
     */
    public function addAttachment(Yourdelivery_Api_Optivo_Attachment $attachment) {
        
        $attachmentId = $attachment->getId();
        if ($attachmentId === null) {
            throw new Yourdelivery_Api_Optivo_Exception('Attachment is empty');
        }
        
        return $this->setAttachmentIds($attachmentId);
    }
}
