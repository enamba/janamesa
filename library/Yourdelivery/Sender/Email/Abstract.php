<?php

/**
 * Description of Yourdelivery_Sender_Email_Abstract
 * @author mlaug
 */
abstract class Yourdelivery_Sender_Email_Abstract extends Zend_Mail {

    /**
     * The configuration
     * @var Zend_Config
     */
    protected $_config;

    /**
     * The sender name
     * @var string
     */
    private $_fromName;

    /**
     * Public constructor
     * @author Vincent Priem <priem@lieferando.de>
     * @since 12.11.2010
     */
    public function __construct() {

        $this->_config = Zend_Registry::get('configuration');
        parent::__construct('UTF-8');
    }

    /**
     * Sets From-header and sender of the message
     * @author Vincent Priem <priem@lieferando.de>
     * @since 21.06.2011
     * @param type $email
     * @param type $name
     * @return Zend_Mail 
     */
    public function setFrom($email, $name = null) {

        $this->_fromName = $name;
        return parent::setFrom($email, $name);
    }

    /**
     * Get sender name
     * @author Vincent Priem <priem@lieferando.de>
     * @since 21.06.2011
     * @return string 
     */
    public function getFromName() {

        return $this->_fromName;
    }

    /**
     * Sets the subject of the message
     * @author mlaug
     * @param string $subject
     * @return Zend_Mail
     */
    public function setSubject($subject) {

        if (APPLICATION_ENV == "development") {
            $subject = "!DEVEL! " . $subject;
        } elseif (APPLICATION_ENV == "testing") {
            $subject = "!TESTING! " . $subject;
        }

        return parent::setSubject($subject);
    }

    /**
     * Adds To-header and recipient
     * @author Vincent Priem <priem@lieferando.de>
     * @since 12.11.2010
     * @param string|array $email
     * @param string $name
     * @return Zend_Mail
     */
    public function addTo($email = 'developers', $name = '') {

        // no empty email is allowed!
        if (empty($email)) {
            $email = "developers";
        }

        // list of the yd geeks
        if ($email == "developers") {
            $email = array(
                'Lieferando IT' => 'EMAIL'
            );
        } elseif ($email == "seo") { // seo
            $email = array(
                'Lieferando SEO' => 'EMAIL',
            );
        }

        if (APPLICATION_ENV == "production") {
            parent::addTo($email, $name);
        } else {
            parent::addTo($this->_config->testing->email);
        }

        return $this;
    }

    /**
     * Adds Cc-header and recipient
     * @author Vincent Priem <priem@lieferando.de>
     * @since 12.11.2010
     * @param string|array $email
     * @param string $name
     * @return Zend_Mail
     */
    public function addCc($email, $name = '') {

        if (APPLICATION_ENV == "production") {
            parent::addCc($email, $name);
        } else {
            parent::addCc($this->_config->testing->email);
        }

        return $this;
    }

    /**
     * Adds Bcc recipient
     * @author Vincent Priem <priem@lieferando.de>
     * @since 12.11.2010
     * @param string|array $email
     * @return Zend_Mail
     */
    public function addBcc($email) {

        if (APPLICATION_ENV == "production") {
            parent::addBcc($email);
        } else {
            parent::addBcc($this->_config->testing->email);
        }

        return $this;
    }

    /**
     * Creates a PDF attachment
     * @author Vincent Priem <priem@lieferando.de>
     * @since 12.11.2010
     * @param string $pdf
     * @param string $filename
     * @return Zend_Mail
     */
    public function attachPdf($pdf, $filename = null) {
        try {
            return $this->attachFile($pdf, 'application/pdf', $filename);
        } catch (Zend_Mail_Exception $e) {
            //TODO: logging
            return $this;
        }
    }

    /**
     * Creates a CSV attachment
     * @author Vincent Priem <priem@lieferando.de>
     * @since 12.11.2010
     * @param string $csv
     * @param string $filename
     * @return Zend_Mail
     */
    public function attachCsv($csv, $filename = null) {
        try {
            return $this->attachFile($csv, 'text/comma-separated-values', $filename);
        } catch (Zend_Mail_Exception $e) {
            //TODO: logging
            return $this;
        }
    }

    /**
     * Creates a ZIP attachment
     * @author Vincent Priem <priem@lieferando.de>
     * @since 12.11.2010
     * @param string $zip
     * @param string $filename
     * @return Zend_Mail
     */
    public function attachZip($zip, $filename = null) {
        try {
            return $this->attachFile($zip, 'application/zip', $filename);
        } catch (Zend_Mail_Exception $e) {
            //TODO: logging
            return $this;
        }
    }

    /**
     * Creates a ZIP attachment
     * @author Vincent Priem <priem@lieferando.de>
     * @since 12.11.2010
     * @param string $txt
     * @param string $filename
     * @return Zend_Mail
     */
    public function attachTxt($txt, $filename = null) {
        try {
            return $this->attachFile($txt, 'text/plain', $filename);
        } catch (Zend_Mail_Exception $e) {
            //TODO: logging
            return $this;
        }
    }

    /**
     * Creates an attachment
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.06.2011
     * @param string $file
     * @param string $mime
     * @param string $filename
     * @return Zend_Mail
     */
    public function attachFile($file, $mime = 'application/octet-stream', $filename = null) {

        if (!is_file($file)) {
            throw new Zend_Mail_Exception('Could not attach file ' . $file . ($filename !== null ? ' ' . $filename : ''));
        }

        $this->createAttachment(
                file_get_contents($file), $mime, Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $filename === null ? basename($file) : $filename
        );

        return $this;
    }

    /**
     * Get body content
     * @author Vincent Priem <priem@lieferando.de>
     * @since 21.06.2011
     * @return string
     */
    public function getBodyRaw() {

        $body = $this->getBodyHtml();
        if (!$body) {
            $body = $this->getBodyText();
        }

        if ($body) {
            return $body->getContentRaw();
        }

        return "";
    }

    /**
     * Get body mime
     * @author Vincent Priem <priem@lieferando.de>
     * @since 21.06.2011
     * @return string
     */
    public function getBodyMime() {

        $body = $this->getBodyHtml();
        if (!$body) {
            $body = $this->getBodyText();
        }

        if ($body) {
            return $body->type;
        }

        return "";
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 30.12.2011
     * @return Zend_Mail_Transport_Abstract
     */
    private function _getTransport() {
        
        $transport = $this->_config->sender->email->transport;
        switch ($transport) {
            case "smtp":
                if ($this->_config->sender->email->smtp) {
                    $smtp = $this->_config->sender->email->smtp->toArray();
                    return new Zend_Mail_Transport_Smtp($smtp['host'], $smtp);
                }
                break;
        }
        
        return new Zend_Mail_Transport_Sendmail();
    }
    
    /**
     * Sends this email
     * @author Vincent Priem <priem@lieferando.de>
     * @since 12.11.2010
     * @return boolean
     */
    public function send($type = "customer", $transport = null) {

        $status = 1;
        $error = "";
        
        // send
        try {
            if ($type == "customer" && $transport === null) {
                $transport = $this->_getTransport();
            }
            
            parent::send($transport);
            
        } catch (Exception $e) {
            $status = 0;
            $error = $e->getMessage();
        }

        $this->_save($type, $status, $error);
        
        return (boolean) $status;
    }
    
    /**
     * Save email in db and storage
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.01.2012
     * @return int
     */
    protected function _save($type = "customer", $status = 1, $error = "") {
        
        // in db
        $dbTable = new Yourdelivery_Model_DbTable_Emails();
        $dbRow = $dbTable->createRow(array(
            'type' => $type,
            'email' => implode(', ', $this->getRecipients()),
            'status' => $status,
            'error' => $error
        ));
        $dbRow->save();
        
        // in storage
        $storage = new Default_File_Storage();

        $recipients = $this->getRecipients();
        foreach ($recipients as $recipient) {
            $storage->resetSubFolder();
            $storage->setSubFolder('emails');
            $storage->setLetterFolder($recipient, 2);
            $storage->store($recipient . '-' . $dbRow->id . '.html', $this->getBodyRaw());
        }
        
        return $dbRow->id;
    }
    
    /**
     * Save email in storage and in database
     * @author Alex Vait <vait@lieferando.de>
     * @since 26.01.2012
     * @return int
     */
    public function save($type = "customer") {
        
        return $this->_save($type);
    }    

}
