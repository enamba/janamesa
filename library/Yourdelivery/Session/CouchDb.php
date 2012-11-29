<?php

/**
 * Our personal session handler which uses a fallback to memcache and 
 * tries to relax database in multiple ways (memcache and slave reads)
 * 
 * @author Matthias Laug <laug@lieferando.de>
 * @since 13.12.2011
 */
class Yourdelivery_Session_CouchDb implements Zend_Session_SaveHandler_Interface {

    protected $couch = null;
    protected $config = null;

    public function __construct() {
        // Establish connection and create db if not exists
        $this->couch = new Phly_Couch(array(
                    'host' => $this->getConfig()->couchDb->host,
                    'port' => $this->getConfig()->couchDb->port
                ));
        try {
            $this->couch->dbInfo($this->getConfig()->couchDb->db);
        } catch (Phly_Couch_Exception $e) {
            $this->couch->dbCreate($this->getConfig()->couchDb->db);
            $this->couch->setDb($this->getConfig()->couchDb->db);
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.12.2011
     * @return Zend_Config_Ini
     */
    public function getConfig() {
        if ($this->config === null) {

            $this->config = Zend_Registry::get('configuration');
        }
        return $this->config;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.12.2011
     * @param string $id 
     */
    public function read($id) {
        //check cache first
        $session = Default_Helpers_Cache::load(md5($this->getConfig()->domain->base . $id));
        if ($session) {
            return $session;
        }

        $document = $this->couch->docOpen($id);
        if ($document) {
            return $document->data;
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.12.2011
     * @param string $id
     * @param string $data 
     */
    public function write($id, $data) {
        Default_Helpers_Cache::store(md5($this->getConfig()->domain->base . $id), $data);

        $document = $this->couch->docOpen($id);
        if ($document->getId() != $id) {
            $document = new Phly_Couch_Document($id);
            $document->id = $id;
            $document->lifetime = ini_get('session.gc_maxlifetime');
        }
        $document->data = $data;
        $document->modified = time();
        $this->couch->docSave($document, $id);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.12.2011
     * @param integer $maxlifetime 
     * @return boolean
     */
    public function gc($maxlifetime) {
        
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.12.2011
     * @param string $id 
     * @return boolean
     */
    public function destroy($id) {
        Default_Helpers_Cache::remove(md5($this->getConfig()->domain->base . $id));
        $this->couch->docRemove($id);
    }

    public function close() {
        return true;
    }

    public function open($save_path, $name) {
        return true;
    }

}
