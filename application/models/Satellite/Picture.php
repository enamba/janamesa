<?php
/**
 * model of picture for satellite
 * @author alex
 * @since 28.04.2011
 */
class Yourdelivery_Model_Satellite_Picture extends Default_Model_Base{

    /**
     * @var string
     */
    private $_picture;
    
    /**
     * Get associated table
     * @author vpriem
     * @since 29.04.2011
     * @return Yourdelivery_Model_DbTable_Satellite_Picture
     */
    public function getTable() {
        
        if ($this->_table === null){
            $this->_table = new Yourdelivery_Model_DbTable_Satellite_Picture();
        }
        return $this->_table;
        
    }
    
    /**
     * Get picture
     * @author vpriem
     * @since 29.04.2011
     * @return string
     */
    public function getPicture() {
        
        if ($this->_picture !== null) {
            return $this->_picture;
        }
        
        $id = $this->getId();
        if (!$id) {
            return "";
        }
        
        $path = "/storage/satellites/" . $this->getSatelliteId() . "/pictures/" . $id;
        foreach (array("jpg", "png", "gif") as $ext) {
            if (file_exists(APPLICATION_PATH . "/.." . $path . "." . $ext)) {
                return $this->_picture = $path . "." . $ext;
            }
        }
        
        return "";        
    }

    /**
     * Set picture
     * @author alex
     * @since 02.05.2011
     */
    public function setPicture($name) {
        if (is_null($this->getId())) {
            return false;
        }

        $data = @file_get_contents($name);
        if ($data !== false) {
            $fileExtension = end(explode(".", basename($name)));
            $this->getStorage()->store($this->getId() . '.' . $fileExtension, $data);
        }
    }
    
    /**
     * get storage object of this picture
     * @author alex
     * @since 10.05.2011
     * @return Default_File_Storage
     */
    public function getStorage() {

        if ($this->_storage === null) {
            $this->_storage = new Default_File_Storage();
            $this->_storage->setSubFolder('satellites/' . $this->getSatelliteId() . '/pictures');
        }

        return $this->_storage;
    }
    
}
