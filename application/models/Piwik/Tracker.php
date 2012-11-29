<?php

/**
 * Description of Tracker
 *
 * @author matthiaslaug
 */
class Yourdelivery_Model_Piwik_Tracker extends Piwik_Tracker{

    /**
     * @author mlaug
     * @since 22.06.2011
     * @return Yourdelivery_Model_Piwik_Tracker 
     */
    static function getInstance(){
        $config = Zend_Registry::get('configuration');
        $instance = new Yourdelivery_Model_Piwik_Tracker($config->piwik->id, $config->piwik->url);
        return $instance;
    }
    
    /**
     * track a custom variable
     * @author mlaug
     * @since 06.06.2011
     * @param integer $id
     * @param string $token
     * @param integer $index
     * @param string $name
     * @param string $value 
     */
    static function trackCustomVariable($index, $name, $value, $scope = 'visit') {
        $logger = Zend_Registry::get('logger');
        try {
            $t = self::getInstance();
            $t->setCustomVariable($index, $name, $value, $scope);
            $t->setUrl('/user/login');
            $result = $t->doTrackPageView('login');
            $logger->debug(sprintf('PIWIK: tracking customer variable %s at value %s', $name, $value));
        } catch (Exception $e) {
            $logger->warn(sprintf('PIWIK: failed tracking customer variable %s at value %s: %s', $name, $value, $e->getMessage()));
        }
    }
    
    /**
     *
     * @param string $name
     * @param integer $revenue 
     */
    static function trackGoal($name, $revenue = 0){
        $logger = Zend_Registry::get('logger');
        try {
            $t = self::getInstance();
            $result = $t->doTrackGoalAndCreate($name, $revenue);
            $logger->debug(sprintf('PIWIK: tracking goal %s with revenue %d', $name, $revenue));
        } catch (Exception $e) {
            $logger->warn(sprintf('PIWIK: failed tracking customer variable %s at value %s: %s', $name, $value, $e->getMessage()));
        }
    }
    
    /**
     * track a goal but create relation if not existent
     * @author mlaug
     * @since 08.06.2011
     * @param string $name
     * @param integer $revenue
     * @return boolean 
     */
    public function doTrackGoalAndCreate($name, $revenue = 0) {      
        $goalId = $this->createGoal($name);
        if ( $goalId <= 0 ){
            return false;
        }
        $this->doTrackGoal($goalId, $revenue);
        return $goalId;
    }
    
    /**
     * create a goal
     * @author mlaug
     * @since 08.06.2011
     * @param string $name
     * @param string $pattern
     * @param string $matchAttr
     * @param string $patternType
     * @param boolean $caseSensitive
     * @param integer $revenue
     * @param boolean $allowMultipleConversionsPerVisit
     * @return integer 
     */
    public function createGoal($name, $pattern = '/', $matchAttr = 'manually', $patternType = 'contains', $caseSensitive = false, $revenue = 0, $allowMultipleConversionsPerVisit = false){       
        $goalId = false;  
        $table = new Yourdelivery_Model_DbTable_Piwik_Goals();
        $row = $table->fetchRow('goalName="' . $name . '"');
        if ( !$row ){
        
            $url  = self::$URL . '?idSite=' . $this->idSite;
            $url .= '&module=API';
            $url .= '&method=Goals.addGoal';
            $url .= '&matchAttribute=' . $matchAttr;
            $url .= '&patternType=' . $patternType;
            $url .= '&pattern=' . $pattern;
            $url .= '&name=' . $name;
            $url .= '&token_auth=26cfe78ff004fbd2af689c90020fa59e';
            $ret = $this->sendRequest($url);
            
            try{
                $xml = new DOMDocument();
                $xml->loadXML($ret);
                if ( $xml->getElementsByTagName('error')->length > 0 ){
                    return false;
                }
                $goalId = (integer) $xml->getElementsByTagName('result')->item(0)->nodeValue;
            }
            catch ( Exception $e ){
                return false;
            }
            
            $table->createRow(array(
                'goalId' => $goalId,
                'goalName' => $name
            ))->save();
        }
        else{
            $goalId = (integer) $row->goalId;
            if ( $goalId <= 0 ){
                return false;
            }
        } 
        
        return $goalId;
    }
    
    /**
     * @author mlaug
     * @since 08.06.2011
     * @param integer $idGoal
     * @return boolean
     */
    public function deleteGoal($idGoal){
        $url  = self::$URL . '?idSite=' . $this->idSite;
        $url .= '&module=API';
        $url .= '&method=Goals.deleteGoal';
        $url .= '&idGoal=' . $idGoal;
        $url .= '&token_auth=26cfe78ff004fbd2af689c90020fa59e';
        $ret = $this->sendRequest($url);
        try{
            $xml = new DOMDocument();
            $xml->loadXML($ret);
            if ( $xml->getElementsByTagName('error')->length > 0 ){
                return false;
            }
            if ( $xml->getElementsByTagName('success')->length > 0 ){
                $table = new Yourdelivery_Model_DbTable_Piwik_Goals();
                $table->delete('goalId='.$idGoal);
                return true;
            }
            return false;
        }
        catch ( Exception $e ){
            return false;
        }
    }

}

?>
