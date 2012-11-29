<?php
/**
 * @author vpriem
 */
class GoogleGeocodingTest extends Yourdelivery_Test{

    public function setUp(){
        
        $this->markTestSkipped(
              "GoogleApi has a query limit, that we don't want to reach - we skipp this test"
            );
    }
    /**
     * @author vpriem
     */
    public function testOk(){

        $dbTable = new Yourdelivery_Model_DbTable_Geocoding();
        $dbTable->delete();
        
        $address = "Chausseestraße 86, 10115";
        $hash = md5($address.'00');
        
        $geo = new Default_Api_Google_Geocoding();
        $this->assertTrue($geo->ask($address));
        $this->assertTrue(is_object($geo->getResponse()));
        $this->assertTrue(is_array($geo->getResults()));
        $this->assertTrue(is_object($geo->getLatLng()));
        $this->assertEquals($geo->getStatus(), "OK");
        $this->assertEquals((integer) $geo->getLat(), 52);
        $this->assertEquals((integer) $geo->getLng(), 13);
        $this->assertEquals($geo->getType(), "ROOFTOP");
        $this->assertTrue($geo->isRequested());

        $this->assertTrue($geo->ask($address));
        $this->assertTrue($geo->isCached());

        $dbTable = new Yourdelivery_Model_DbTable_Geocoding();
        $row = $dbTable->findByHash($hash);
        $this->assertTrue(is_object($row));
        $this->assertEquals($hash, $row->hash);
        $this->assertEquals($address, $row->address);
        $this->assertEquals($geo->getStatus(), $row->status);
        $this->assertEquals($geo->getType(), $row->type);

    }
    
    public function testGetPostalCode(){
        $dbTable = new Yourdelivery_Model_DbTable_Geocoding();
        $dbTable->delete();
        $geo = new Default_Api_Google_Geocoding();
        $this->assertTrue($geo->ask(null,'52.5166667','13.4'));
        $this->assertEquals($geo->getStatus(), "OK");
        $this->assertTrue($geo->isRequested());
        $this->assertEquals('10178',$geo->getPlz());
    }

    /**
     * @author vpriem
     */
    public function testZeroResults(){

        $geo = new Default_Api_Google_Geocoding();
        $this->assertFalse($geo->ask("sdfgghrgjkuudfsdf 666, 101110011"));
        $this->assertEquals($geo->getStatus(), "ZERO_RESULTS");
        $this->assertNull($geo->getLat());
        $this->assertNull($geo->getLng());
        $this->assertNull($geo->getType());
        $this->assertTrue($geo->isRequested());

    }

    
}
