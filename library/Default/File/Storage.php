<?php

/**
 * Description of Storage
 * @package file
 * @subpackage storage
 * @author Matthias Laug <laug@lieferando.de>
 */
class Default_File_Storage {

    /**
     * base folder
     * @var string 
     */
    protected $_storage = null;

    /**
     * subfolder under storage directory
     * @var array
     */
    protected $_subfolder = array();


    /**
     * init the storage with the usual folder
     * @since 10.01.2012
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function __construct() {
        $this->setStorage(APPLICATION_PATH . '/../storage/');
    }

    /**
     * set storage path
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $storage
     * @return boolean
     */
    public function setStorage($storage) {
        if (is_dir($storage)) {
            //if not readable try to change
            if (!is_writable($storage)) {
                if (!chmod($storage, 0755)) {
                    return false;
                }
            }
            $this->_storage = $storage;
            return true;
        }
        return false;
    }

    /**
     * get current storage directory
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getStorage() {
        return $this->_storage;
    }

    /**
     * set a subfolder
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $folder
     * @return boolean
     */
    public function setSubFolder($folder, $create = true) {
        
        if (in_array($folder, $this->_subfolder)) {
            return true;
        }

        if (is_string($folder)) {
            $this->_subfolder[] = $folder;
        } elseif (is_array($folder)) {
            $this->_subfolder = array_merge($this->_subfolder, $folder);
        } else {
            // what should we do here
        }
        
        // create if not exists
        if ($create) {
            $this->createCurrentFolder();
        }
        
        return true;
    }

    /**
     * create directory if not yet existent
     * @author Matthias Laug <laug@lieferando.de>
     * @since 05.08.2010x
     */
    public function createCurrentFolder() {
        $sub = $this->getCurrentFolder();
        //create directory if not exists
        if (!is_dir($sub)) {
            //set recurive flag true and create all with mode 0777
            @mkdir($sub, 0755, true);
        }
    }

    /**
     * create a folder depending on a given or current timestamp 
     * beneath the current subfolder structure
     * @author Matthias Laug <laug@lieferando.de>
     * @param type $time 
     */
    public function setTimeStampFolder($time = null) {
        if ($time === null) {
            $time = time();
        }
        $folder = date('d-m-Y', $time);
        $this->setSubFolder($folder);
    }
    
    /**
     * create a folder depending on the first two letters 
     * of a given string beneath the current subfolder structure
     * @author Matthias Laug <laug@lieferando.de>
     * @param type $time 
     */
    public function setLetterFolder($str, $count = 2){
        for($i=1;$i<=$count;$i++){
            if ( strlen($str) >= $i ){
                $this->setSubFolder(substr($str, ($i-1), 1));
            }
        }
    }

    /**
     * build path to current folder below storage folder
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getCurrentFolder() {
        if ( count($this->_subfolder) == 0){
            return $this->_storage;
        }
        return $this->_storage . implode('/', $this->_subfolder);
    }

    /**
     * remove all subfolder and return to base storage direcoty
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function resetSubFolder() {
        $this->_subfolder = array();
    }

    /**
     * find all files matching the pattern
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $pattern
     * @return array
     */
    public function find($pattern, $recursive = false) {
        $dir = $this->getCurrentFolder();
        $result = glob($dir . '/' . $pattern);
        return $result;
    }

    /**
     * save a new file to the file storage
     * @author Matthias Laug <laug@lieferando.de>
     * @param filepointer $fp
     * @param mixed $data
     * @param string $filename
     * @param boolean $backup
     * @return SplFileObject
     */
    public function store($filename = null, $data = null, $fp = null, $backup = false) {

        if (is_null($filename)) {
            return false;
        }

        //create file from given filename string and data
        if (!is_null($fp) && is_resource($fp)) {
            //append to given data file content of given file handle
            $data .= stream_get_contents($fp);
            fclose($fp);
        }
        
        if ( strlen($data) <= 0 ){
            return false;
        }
        
        //create path to file and create SplFileObject
        $file = $this->getCurrentFolder() . '/' . $filename;

        if (file_exists($file) && $backup) {
            //backup old file with storage controller
            $tmp = new Default_File_Storage();
            $tmp->setSubFolder('backup');
            $tmp->setSubFolder($this->_subfolder);
            //add timestamp
            $tmp->store(time() . "-" . Default_Helper::generateRandomString() . "-" . basename($file), file_get_contents($file));
            unset($tmp);
            //delete file if already exist and overwrite that motherfucker
            unlink($file);
        }

        //create new file
        $fp = new SplFileObject($file, 'w+');
        $fp->fwrite($data);

        return $fp;
    }

    /**
     * delete a file from storage
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $filename
     * @return boolean
     */
    public function delete($filename = null) {     
        if (!is_null($filename)) {
            //remove file f exists in current folder
            $file = $this->getCurrentFolder() . '/' . $filename;
            if (file_exists($file)) {
                //delete file from filesystem
                return @unlink($file);
            }
        }
        return false;
    }

    /**
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $filename
     * @return boolean
     */
    public function exists($filename = null) {
        if (is_null($filename)) {
            return false;
        }

        $file = $this->getCurrentFolder() . '/' . $filename;
        if (!file_exists($file)) {
            return false;
        }

        return true;
    }
    
    /**
     * copy a file relative from current folder to another folder
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $file
     * @param string $to 
     * @return boolean
     */
    public function copy($file, $to){
        if ( !$this->exists($file) ){
            return false;
        }
        //check if we have the direction dir available, create if not
        $pathTo = $this->getCurrentFolder() . '/' . dirname($to);
        if (!is_dir($pathTo)) {
            //set recurive flag true and create all with mode 0777
            @mkdir($pathTo, 0755, true);
        }
        $currentFolder = $this->getCurrentFolder();
        copy($currentFolder . '/' . $file, $currentFolder . '/' . $to);
        return true;
    }
    
    /**
     * copy a file relative from current folder to another folder
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $file
     * @param string $to 
     */
    public function move($file, $to){
        if ( $this->copy($file, $to) ){
            $this->delete($file);
            return true;
        }
        return false;
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    public function ls(){
        $ls = array();
        $handle = opendir($this->getCurrentFolder());
        while (false !== ($file = readdir($handle))) {
            //ignore all that do not start with 'rep'
            if ($file != "." && $file != "..") {
                $ls[] = $this->getCurrentFolder() . '/' . $file;
            }
        }
        return $ls;
    }

}
