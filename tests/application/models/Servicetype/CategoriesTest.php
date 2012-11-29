<?php
/**
 * @author vpriem
 * @since 21.03.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Servicetype_CategoriesTest extends Yourdelivery_Test {

    /**
     * @author vpriem
     * @since 21.03.2011
     */
    public function testGetTable(){       
        $model = new Yourdelivery_Model_Servicetype_Categories();
        $table = $model->getTable();
        $this->assertTrue($table instanceof Yourdelivery_Model_DbTable_Restaurant_Categories);
        $this->assertTrue($table === $model->getTable());
    }

    /**
     * @author vpriem
     * @since 21.03.2011
     */
    public function testGetCategoriesByCityId(){
         
        $cityId = $this->getRandomCityId();
        
        //with out any parameter
        $cats = Yourdelivery_Model_Servicetype_Categories::getCategoriesByCityId($cityId);
        $this->assertTrue(is_array($cats));
        $count = count($cats);
        
        //with an numeric parameter
        $cats = Yourdelivery_Model_Servicetype_Categories::getCategoriesByCityId($cityId, 1);
        $this->assertTrue(is_array($cats));
        $this->assertEquals($count,count($cats));
        
        //with an string parameter
        $cats = Yourdelivery_Model_Servicetype_Categories::getCategoriesByCityId($cityId, 'rest');
        $this->assertTrue(is_array($cats));
        $this->assertEquals($count,count($cats));

    }

}