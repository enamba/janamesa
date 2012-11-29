<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Export
 *
 * @author matthiaslaug
 */
class Yourdelivery_Model_Piwik_Export {

    private $_connection = null;

    /**
     * get a report on a daily base
     * @author mlaug
     * @since 21.06.2011
     * @param integer $day
     * @return array
     */
    public function getDailyReport($day = null) {
        
    }

    /**
     * get a report on a weekly base
     * @author mlaug
     * @since 21.06.2011
     * @param integer $week 
     */
    public function getWeeklyReport($week = null) {
        
    }

    /**
     * get two weeks and compare those
     * get a percentage difference for each channel
     * @author mlaug
     * @since 21.06.2011
     * @param array $currentWeek
     * @param array $preWeek 
     */
    private function compareWeeks($currentWeek, $preWeek) {
        
    }

    /**
     * get the connection to the piwik database
     * @author mlaug
     * @since 21.06.2011
     */
    public function getPiwikConnector() {
        if ($this->_connection === null) {
            $config = Zend_Registry::get('configuration');
            $this->_connection = new Zend_Db_Adapter_Pdo_Mysql(array(
                        'host' => $config->resources->multidb->piwik->host,
                        'username' => $config->resources->multidb->piwik->username,
                        'password' => $config->resources->multidb->piwik->password,
                        'dbname' => $config->resources->multidb->piwik->dbname
                    ));
            $this->_connection->query("SET NAMES 'utf8'");
        }
        return $this->_connection;
    }

}

?>
