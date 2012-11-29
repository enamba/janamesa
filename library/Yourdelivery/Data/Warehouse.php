<?php

/**
 * Take a query and create table
 *
 * @author mlaug
 */
class Yourdelivery_Data_Warehouse implements Yourdelivery_Data_Interface {

    private $_view = null;
    private $_select = null;
    private $_engine =  null;  
    private $_defaultEngine = "INNODB";
    private $_engineTypes = array('MEMORY', 'INNODB');

    /**
     *
     * @param string $select
     * @return void
     */
    public function setSelect($select) {
        
    }

    /**
     *
     * @return string
     */
    public function getSelect() {
        
    }

    /**
     *
     * @param string $view
     * @return void
     */
    public function setView($view) {
        $this->_view = $view;
    }

    /**
     *
     * @return string
     */
    public function getView() {
        return $this->_view;
    }
    
    /**
     *     
     * @return string
     */
    public function getEngine() {
        return $this->_engine;
    }
    
    /**
     *
     * @param string $engine 
     */
    public function setEngine($engine) {
        
        if(in_array($engine, $this->_engineTypes)){
             $this->_engine = $engine;
        }else{
            $logger = Zend_Registry::get('logger');
            $logger->err(sprintf('DATA WAREHOUSE - REGENERATE: engine %s not supported, using default engine %s', $engine, $this->_engine));
        }               
    }
    
    
    private function resetEngine() {
        $this->_engine = $this->_defaultEngine;
    }
    
    /**
     *
     * @return boolean
     */
    public function regenerate() {
        
        if($this->_engine === null) {
            $this->resetEngine();
        }
        
        
        $logger = Zend_Registry::get('logger');
        $view = $this->getView();
        if ($view === null) {
            $logger->debug('DATA WAREHOUSE - REGENERATE: did not get view');
            return false;
        }
                      
        $db = Zend_Registry::get('dbAdapter');
        
        // check, if view / table exists
        try {
            $db->query(sprintf('SELECT * FROM `%s` LIMIT 1', $view));
        } catch (Exception $e) {

            $logger->err(sprintf('DATA WAREHOUSE: Try to regenerate `data_%s`, but `%s` does not exist', $view, $view));
            return false;
        }
        
        $db->beginTransaction();
        $db->query(sprintf('DROP TABLE IF EXISTS `data_%s`', $view));
        $db->query(sprintf('CREATE TABLE  `data_%s` ENGINE=%s SELECT * FROM `%s`', $view, $this->_engine, $view));
        $fields = $db->query(sprintf('SHOW FULL FIELDS FROM `data_%s`', $view));

        $indizes = array(
            'cid', 'plz', 'email'
        );

        foreach ($fields as $field) {
            if (in_array($field['Field'], $indizes) || strtolower(substr($field['Field'],-2)) == 'id' ) {
                try {
                    $db->query(sprintf('ALTER TABLE `data_%s` ADD INDEX(`%s`)', $view, $field['Field']));
                } catch (Exception $e) {
                    $logger->err(sprintf('DATA WAREHOUSE: Could not regenerate, because query "%s" was interupted', sprintf('ALTER TABLE `data_%s` ADD INDEX(`%s`)', $view, $field['Field']) ));
                    $db->rollback();
                    return false;
                }
            }
        }

        // check, if regeneration was successfully
        try {
            $db->query(sprintf('SELECT * FROM `data_%s` LIMIT 1', $view));
        } catch (Exception $e) {
            $logger = Zend_Registry::get('logger');
            $logger->err(sprintf('DATA WAREHOUSE: Regeneration of `data_%s` was not successfully', $view));
            $db->rollback();
            return false;
        }
        
        $db->commit();
        
       $this->resetEngine();
        
        return true;
    }

}
