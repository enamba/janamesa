<?php

/**
 * Our personal session handler which uses a fallback to memcache and 
 * tries to relax database in multiple ways (memcache and slave reads)
 * 
 * @author Matthias Laug <laug@lieferando.de>
 * @since 13.12.2011
 */
class Yourdelivery_Session_Handler extends Zend_Session_SaveHandler_DbTable {
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.12.2011
     * @return Zend_Config_Ini
     */
    public function getConfig(){
        return Zend_Registry::get('configuration');
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.12.2011
     * @param string $id 
     */
    public function read($id) {
        //check cache first
        $session = Default_Helpers_Cache::load(md5($this->getConfig()->domain->base . $id));
        if ( $session ){
            return $session;
        }
        //then read from database
        return parent::read($id);
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.12.2011
     * @param string $id
     * @param string $data 
     */
    public function write($id, $data) {
        Default_Helpers_Cache::store(md5($this->getConfig()->domain->base . $this->_getPrimary($id)), $data);
        return parent::write($id, $data);
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.12.2011
     * @param integer $maxlifetime 
     * @return boolean
     */
    public function gc($maxlifetime) {
        //cleanup cache
        $rows = $this->select($this->getAdapter()->quoteIdentifier($this->_modifiedColumn) . ' + '
                    . $this->getAdapter()->quoteIdentifier($this->_lifetimeColumn) . ' < '
                    . $this->getAdapter()->quote(time()));
        foreach($rows as $row){
            Default_Helpers_Cache::remove(md5($this->getConfig()->domain->base . $row->id));
        }
        return parent::gc($maxlifetime);    
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.12.2011
     * @param string $id 
     * @return boolean
     */
    public function destroy($id) {
        Default_Helpers_Cache::remove(md5($this->getConfig()->domain->base . $id));
        return parent::destroy($this->_getPrimary($id));
    }
    
    
}
