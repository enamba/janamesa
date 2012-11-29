<?php
/**
 * Ftp helper
 *
 * @author Vincent Priem <priem@lieferando.de>
 * @since 22.12.2011
 */
class Default_Ftp {
    
    /**
     * @var ressource
     */
    private $_conn;
    
    /**
     * @var boolean
     */
    private $_loggedIn = false;
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     */
    public function __construct($host = null, $port = 21, $username = null, $password = null) {
        
        if ($host !== null) {
            if ($this->connect($host, $port)) {
                if ($password !== null) {
                    $this->login($username, $password);
                }
            }
        }
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return boolean 
     */
    public function connect($host, $port = 21, $timeout = 90) {
        
        $this->close();
        return $this->_conn = ftp_connect($host, $port, $timeout); 
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @return boolean 
     */
    public function isConnected() {
        
        return is_resource($this->_conn);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @param type $username
     * @param type $password
     * @return boolean 
     */
    public function login($username, $password) {
        
        return $this->_loggedIn = @ftp_login($this->_conn, $username, $password);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @return boolean 
     */
    public function isLoggedIn() {
        
        return $this->_loggedIn;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @param boolean $pasv
     * @return boolean 
     */
    public function pasv($pasv) {
        
        return ftp_pasv($this->_conn, $pasv);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @param string $directory
     * @return boolean 
     */
    public function chdir($directory) {
        
        return @ftp_chdir($this->_conn, $directory);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @param string $directory
     * @return boolean 
     */
    public function mkdir($directory) {
        
        return ftp_mkdir($this->_conn, $directory);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @param string $directory
     * @return boolean 
     */
    public function rmdir($directory) {
        
        return ftp_rmdir($this->_conn, $directory);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @return string 
     */
    public function pwd() {
        
        return ftp_pwd($this->_conn);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @param string $remote_file
     * @param string $local_file
     * @param int $mode FTP_ASCII|FTP_BINARY.
     * @param int $startpos
     * @return boolean 
     */
    public function put($remote_file, $local_file, $mode = FTP_BINARY, $startpos = 0) {
        
        return ftp_put($this->_conn, $remote_file, $local_file, $mode, $startpos);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     */
    public function close() {
        
        if ($this->isConnected()) {
            $this->_loggedIn = false;
            ftp_close($this->_conn);
        }
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     */
    public function __destruct() {
        
        $this->close();
    }
}

