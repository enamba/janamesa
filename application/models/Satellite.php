<?php

/**
 * Satellite model
 * @since 27.04.2011
 * @author Alex Vait <vait@lieferando.de>
 */
class Yourdelivery_Model_Satellite extends Default_Model_Base {

    /**
     * @var Yourdelivery_Model_Servicetype_Restaurant
     */
    protected $_service = null;

    /**
     * @var array
     */
    protected $_cssProperties = null;

    /**
     * Find satellite by domain
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 29.04.2011
     * @param string $domain
     * @return Zend_Db_Table_Row_Abstract
     */
    public function loadByDomain($domain = null) {

        if ($domain === null) {
            $domain = Default_Helpers_Web::getHostname();
        }

        // get table
        $table = $this->getTable();

        // get the row
        $row = $table->findByDomain($domain);
        if ($row !== null) {
            $this->load($row['id']);
            return true;
        }
        return false;
    }

    /**
     * Get associated table
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 29.04.2011
     * @return Yourdelivery_Model_DbTable_Satellite
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Satellite();
        }
        return $this->_table;
    }

    /**
     * Get service of satellite
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 29.04.2011
     * @return Yourdelivery_Model_Servicetype_Abstract
     */
    public function getService() {
        if ($this->_service !== null) {
            return $this->_service;
        }

        $restaurantId = (integer) $this->getRestaurantId();
        $service = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        
        if ($service->isCatering()) {
            $service = new Yourdelivery_Model_Servicetype_Cater($restaurantId);
            
        } elseif ($service->isGreat() || $service->isFruit()) {
            $service = new Yourdelivery_Model_Servicetype_Great($restaurantId);
            
        }
        
        return $this->_service = $service;
    }

    /**
     * Set a new logo/background image for this satellite
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 28.04.2011
     * @param string $name
     * @return boolean
     */
    public function setImg($name, $newname) {
        if (is_null($this->getId())) {
            return false;
        }

        $data = file_get_contents($name);
        // if file_get_contents failed $data is 'false'
        if ($data !== false) {
            $fileExtension = end(explode(".", basename($name)));
            $this->getStorage()->store($newname . '.' . $fileExtension, $data);
        }
    }

    /**
     * Add a new picture. The corresponding description can be found in satellites_pictures
     * 
     * @author Alex Vait <vait@lieferando.de> Vait <vait@lieferando.de>
     * @since 28.04.2011
     */
    public function addPicture($name, $subfolder, $newname) {
        if (is_null($this->getId())) {
            return false;
        }

        $data = @file_get_contents($name);
        if ($data !== false) {
            $storage = $this->getStorage();

            if (!is_null($subfolder)) {
                $storage->setSubFolder($subfolder);
            }

            if (is_null($newname)) {
                $newname = basename($name);
            }

            // remove all files with this name, independent of file extension
            foreach (glob($storage->getCurrentFolder() . "/" . $newname . "*") as $filename) {
                unlink($filename);
            }

            $storage->store($newname . '.' . pathinfo($name, PATHINFO_EXTENSION), $data);
        }
    }

    /**
     * get storage object of this satellite
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 28.04.2011
     * @return Default_File_Storage
     */
    public function getStorage() {

        if ($this->_storage === null) {
            $this->_storage = new Default_File_Storage();
            $this->_storage->resetSubFolder();
            $this->_storage->setSubFolder('satellites/' . $this->getId());
        }

        return $this->_storage;
    }

    /**
     * Returns logo image of this satellite
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 28.04.2011
     * @return string
     */
    public function getLogo() {

        $id = $this->getId();
        if ($id === null) {
            return "";
        }

        $path = "/storage/satellites/" . $id . "/logo";
        foreach (array("jpg", "png", "gif") as $ext) {
            if (file_exists(APPLICATION_PATH . "/.." . $path . "." . $ext)) {
                return $path . "." . $ext;
            }
        }

        return "";
    }

    /**
     * Returns background image of this satellite
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 28.04.2011
     * @return string
     */
    public function getBackground() {

        $id = $this->getId();
        if ($id === null) {
            return "";
        }

        $path = "/storage/satellites/" . $id . "/background";
        foreach (array("jpg", "png", "gif") as $ext) {
            if (file_exists(APPLICATION_PATH . "/.." . $path . "." . $ext)) {
                return $path . "." . $ext;
            }
        }

        return "";
    }

    /**
     * Returns image with specified name, belonging to this satellite
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.06.2011
     * @return string
     */
    public function getImg($imgName) {

        $id = $this->getId();
        if ($id === null) {
            return "";
        }

        $path = "/storage/satellites/" . $id . "/" . $imgName;
        foreach (array("jpg", "png", "gif") as $ext) {
            if (file_exists(APPLICATION_PATH . "/.." . $path . "." . $ext)) {
                return $path . "." . $ext;
            }
        }

        return "";
    }

    /**
     * Returns certification image of this satellite
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.05.2011
     * @return string
     */
    public function getCertificationImg() {

        $id = $this->getId();
        if ($id === null) {
            return "";
        }

        $path = "/storage/satellites/" . $id . "/certification";
        foreach (array("jpg", "png", "gif") as $ext) {
            if (file_exists(APPLICATION_PATH . "/.." . $path . "." . $ext)) {
                return $path . "." . $ext;
            }
        }

        return "";
    }

    /**
     * Returns image from 'random' directory of this satellite
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 28.04.2011
     * @return string
     */
    public function getRandomPictures() {

        $pictures = array();

        $id = $this->getId();
        if ($id === null) {
            return $pictures;
        }

        $path = '/storage/satellites/' . $id . '/random';

        $handler = @opendir(APPLICATION_PATH . '/..' . $path);
        if (!$handler) {
            return $pictures;
        }

        // open directory and read the filenames
        while ($file = readdir($handler)) {
            // if file isn't this directory or its parent, add it to the result
            if ($file == "." || $file == "..") {
                continue;
            }
            if (!is_file(APPLICATION_PATH . '/..' . $path . "/" . $file)) {
                continue;
            }
            $pictures[] = $path . "/" . basename($file);
        }
        closedir($handler);

        return $pictures;
    }

    /**
     * get all pictures associated with this satellite
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 28.04.2011
     * @return SplObjectStorage
     */
    public function getPictures() {

        $spl = new SplObjectStorage();

        $pictures = $this->getTable()->getPictures();
        foreach ($pictures as $p) {
            try {
                $picture = new Yourdelivery_Model_Satellite_Picture($p['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            $spl->attach($picture);
        }
        return $spl;
    }

    /**
     * remove picture associated with this service
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 28.04.2011
     */
    public function removePicture($picId) {
        if (is_null($picId) || ($picId == 0)) {
            return false;
        }

        try {
            $picture = new Yourdelivery_Model_Satellite_Picture($picId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return false;
        }

        if ($picture->getSatelliteId() != $this->getId()) {
            return false;
        }

        $picTmp = $picture->getPicture();

        unlink(APPLICATION_PATH . '/../' . $picture->getPicture());
        Yourdelivery_Model_DbTable_Satellite_Picture::remove($picId);
    }

    /**
     * remove random picture associated with this service
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.05.2011
     */
    public function removeRandomPicture($pic) {
        if (strlen(trim($pic)) == 0) {
            return false;
        }

        return unlink(APPLICATION_PATH . '/../' . $pic);
    }

    /**
     * Get css template
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 02.05.2011
     * @return array
     */
    public function setCssTemplate($name) {

        parent::setCssTemplate($name);
        if ($name !== null) {
            $this->setCssProperties($this->getCssProperties());
        }
    }

    /**
     * Get css properties
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 02.05.2011
     * @return array
     */
    private function _getCssPlaceholders() {

        $css = array();

        $content = @file_get_contents(APPLICATION_PATH . '/../public/media/css/satellites/color.css');
        if ($content !== false) {
            if (preg_match_all('`(%[a-z0-9_]+%)`', $content, $matches)) {
                $css = array_fill_keys($matches[1], "transparent");
            }
        }

        return $css;
    }

    /**
     * Get css properties
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 02.05.2011
     * @return array
     */
    public function getCssProperties() {

        $css = array();

        $id = $this->getId();
        if ($id === null) {
            return $css;
        }

        $cssTemplate = $this->getCssTemplate();
        if ($cssTemplate !== null) {
            $css = @parse_ini_file(APPLICATION_PATH . '/../storage/satellites/css/color-' . basename($cssTemplate) . '.ini');
            if ($css === false) {
                $css = array();
            }
        } else {
            $css = $this->getTable()->getCssProperties();
        }


        $placeholders = $this->_getCssPlaceholders();
        $css = array_intersect_key($css, $placeholders);
        return array_merge($placeholders, $css);
    }

    /**
     * Set css properties
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 02.05.2011
     * @param array $properties 
     * @return array
     */
    public function setCssProperties(array $properties) {

        $this->_cssProperties = $properties;
    }

    /**
     * Save
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 02.05.2011
     * @return mixed boolean|int
     */
    public function save() {

        if (is_array($this->_cssProperties)) {
            $this->getTable()->editCssProperties($this->_cssProperties);

            $content = @file_get_contents(APPLICATION_PATH . '/../public/media/css/satellites/color.css');
            if ($content !== false) {
                $content = str_replace(array_keys($this->_cssProperties), array_values($this->_cssProperties), $content);
                $this->setCss($content);
            }
        }

        return parent::save();
    }

    /**
     * satellite premium 
     * 
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 15.03.2012
     * @return boolen
     */
    public function isPremium() {
        return $this->getPremium() == 1 ? true : false;
    }

}
