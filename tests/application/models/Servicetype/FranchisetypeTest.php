<?php
/**
 * Description of Franchisetype
 *
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses
 */
class FranchisetypeTest extends Yourdelivery_Test {

    public function testCreateFranchisetype(){
        $franchise = new Yourdelivery_Model_Servicetype_Franchise();
        $id = $franchise->setFranchise('myFranchise');
        $this->assertGreaterThan(0, $id);
        $this->assertEquals($id, $franchise->setFranchise('myFranchise'));
    }

}

?>
