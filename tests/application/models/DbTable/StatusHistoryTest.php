<?php
/**
 * Description of StatusHistoryTest
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class StatusHistoryTest extends Yourdelivery_Test {
    
    public function testLogStatusChange() {
        
        $stati = Yourdelivery_Model_Servicetype_Abstract::getStati();
        $newOfflineStatus = array_rand($stati);
        $currentOfflineStatus = array_rand($stati);
        while($currentOfflineStatus == $newOfflineStatus) {
             $currentOfflineStatus = array_rand($stati);
        }
    
        $db = Zend_Registry::get('dbAdapter');
        
        $resultNew = $db->fetchAll('SELECT * FROM restaurant_status_history WHERE status='.$newOfflineStatus.' and DATE(created) = DATE(NOW())');
        $resultCurrent = $db->fetchAll('SELECT * FROM restaurant_status_history WHERE status='.$currentOfflineStatus.' and DATE(created) = DATE(NOW())');
        
        
        Yourdelivery_Model_DbTable_Restaurant_StatusHistory::logStatusChange((int)$newOfflineStatus, (int)$currentOfflineStatus);
        
        $resultNewAfter = $db->fetchAll('SELECT * FROM restaurant_status_history WHERE status='.$newOfflineStatus.' and DATE(created) = DATE(NOW())');
        $resultCurrentAfter = $db->fetchAll('SELECT * FROM restaurant_status_history WHERE status='.$currentOfflineStatus.' and DATE(created) = DATE(NOW())');
        
        
        $this->assertEquals(count($resultNewAfter) ,1);
        $this->assertEquals(count($resultCurrentAfter),  1);
        
        if(count($resultNew) > 0) {
            $this->assertEquals($resultNew[0]['addCount'] +1 , $resultNewAfter[0]['addCount']);
        }else{
            $this->assertEquals(1 , $resultNewAfter[0]['addCount']);
        }
        
        if(count($resultCurrent) > 0) {
            $this->assertEquals($resultCurrent[0]['delCount'] +1 , $resultCurrentAfter[0]['delCount']);
        }else{
            $this->assertEquals(1 , $resultCurrentAfter[0]['delCount']);
        }
                        
        
    }
    
    
}

?>
