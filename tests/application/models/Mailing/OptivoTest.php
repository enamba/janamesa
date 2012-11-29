<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OptivoTest
 *
 * @author daniel
 */
class OptivoTest extends Yourdelivery_Test{
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 27.07.2012 
     */
    public function testMailing() {
        
        $mailing = new Yourdelivery_Model_Mailing_Optivo();
        
        
        $mailing->setData(array(
            'name' => 'test_'.time(),
            'start' => date(DATETIME_DB),
            'end' => date(DATETIME_DB, strtotime('tomorrow')),
            'mailingId' => md5(time()),
            'status' => 1,
            'customerOrderCount' => '0;1;2',
            'invertCity' => 0,
            'parameters' => 'UserPrename'
        ));
        
        
        $id = $mailing->save();
        $this->assertTrue(is_numeric($id));
        
        
        $cityId = $this->getRandomCityId();
        
        $mailing->setCitys(array($cityId));
        
        
        $mailing2 = new Yourdelivery_Model_Mailing_Optivo($mailing->getId());
        
        
        $citys = $mailing2->getCitys();
        
        $cityArr = array();
        foreach ($citys as $city){
            $cityArr[] = $city->getId();
        }
                
        $this->assertTrue(in_array($cityId, $cityArr));
        
        
        $this->assertEquals(3, count($mailing2->getOrderCountAsArray()));
        $this->assertTrue($mailing2->hasParameter('UserPrename'));
        
    }
    
    
}

?>
