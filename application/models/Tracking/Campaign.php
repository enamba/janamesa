<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Campagne
 *
 * @author mlaug
 */
class Yourdelivery_Model_Tracking_Campaign extends Default_Model_Base {
    
    /**
     * get all tracking campaingns
     * @author mlaug
     * @return SplObjectStorage
     */
    public static function all(){
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->query('select id from tracking_campaign')->fetchAll(PDO::FETCH_ASSOC);
        $cs = new SplObjectStorage();
        foreach($result as $c){
            try{
                $c = new Yourdelivery_Model_Tracking_Campaign($c['id']);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                continue;
            }
            $cs->attach($c);
        }
        return $cs;        
    }

    /**
     * get tracking codes belonging to this campaing
     * @author mlaug
     * @return SplObjectStorage
     */
    public function getCodes($where = null){
        $codes = new SplObjectStorage();
        foreach($this->getTable()->getCodes($where) as $code){
            try{
                $codes->attach(new Yourdelivery_Model_Tracking_Code($code->id));
            }
            catch ( Yourdelivery_Exception_DatabaseInconsistency $e ){
                continue;
            }

        }
        return $codes;
    }

    /**
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Tracking_Campaign
     */
    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Tracking_Campaign();
        }
        return $this->_table;
    }

}
?>
