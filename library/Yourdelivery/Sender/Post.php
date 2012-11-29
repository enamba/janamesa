<?php
/**
 * Yourdelivery_Sender_Post
 * @package sernder
 * @subpackage post
 * @author vpriem
 */
class Yourdelivery_Sender_Post {

    /**
     * FTP connection
     * @var resource
     */
    protected $_conn = false;

    /**
     * Opens an FTP connection
     * @author vpriem
     */
    public function __construct() {

        if (APPLICATION_ENV == "production") {
            // get config
            $config = Zend_Registry::get('configuration');
            $conn = $config->sender->post->toArray();

            // ftp connection
            $this->_conn = ftp_connect($conn['server']);
            if (!$this->_conn) {
                throw new Yourdelivery_Exception_NoConnection("Could not connect to FTP server");
            }

            // ftp login
            if (!ftp_login($this->_conn, $conn['username'], $conn['password'])) {
                throw new Yourdelivery_Exeption_NoConnection("Could not login into FTP server");
            }
            ftp_pasv($this->_conn, true);
        }
    }

    /**
     * Uploads a file to the FTP server
     * send billing every friday
     * @author vpriem
     */
    public function send ($file) {

        if (!file_exists($file)) {
            Yourdelivery_Sender_Email::error(
                "Rechnung ist nicht rausgegangen: PDF nicht gefunden"
            );
            return false;
        }

        // send file to ftp server
        if (APPLICATION_ENV == "production") {
            if (!$this->_conn) {
                Yourdelivery_Sender_Email::error(
                    "Rechnung ist nicht rausgegangen: Keine Verbindung zum FTP Server"
                );
                return false;
            }

            $res = ftp_put($this->_conn, basename($file), $file, FTP_BINARY);
            if (!$res) {
                Yourdelivery_Sender_Email::error(
                    "Rechnung ist nicht rausgegangen: PDF konnte nicht hochgeladen werden"
                );
            }

            return $res;
        }

        // send as mail
        $email = new Yourdelivery_Sender_Email();
        return $email
            ->addTo('samson@tiffy.de')
            ->setSubject('RECHNUNGSVERSAND')
            ->setBodyText('testing')
            ->AttachPdf($file)
            ->send();
    }

    /**
     * Tell provider to send all files out
     * @author vpriem
     * @since 01.07.2011
     */
    public function ende() {
        
        // create file
        $tmp = tempnam("/tmp", "ENDE");
        file_put_contents($tmp, "Ende.");
        
        // send file to ftp server
        if (APPLICATION_ENV == "production") {
            $res = ftp_put($this->_conn, "ende.txt", $tmp, FTP_BINARY);
        }
        // send as mail
        else {
            $email = new Yourdelivery_Sender_Email();
            $res = $email
                ->addTo('samson@tiffy.de')
                ->setSubject('RECHNUNGSVERSAND: ENDE')
                ->setBodyText('testing')
                ->attachTxt($tmp, "ende.txt")
                ->send();
        }
        
        unlink($tmp);
        return $res;
    }
    
    /**
     * Closes an FTP connection
     * @author vpriem
     */
    public function __destruct() {

        if (is_resource($this->_conn)) {
            ftp_close($this->_conn);
        }
    }

}