<?php
/**
 * @author alex
 * @since 16.11.2010
 */
/**
 * @runTestsInSeparateProcesses 
 */
class AdmingroupTest extends Yourdelivery_Test{

    public function setUp() {
        parent::setUp();
    }

    /**
     * @author alex
     * @since 16.11.2010
     */
    public function testConstruct(){
        //create admin group for full access
        $group = new Yourdelivery_Model_Admin_Access_Group(1);
        $this->assertNotNull($group);
        $this->assertEquals($group->getId(), 1);
        $this->assertEquals($group->getIsAdmin(), "1");        
    }

    /**
     * @author alex
     * @since 16.11.2010
     */
    public function testHasAccess(){
        //create admin group for full access
        $group = new Yourdelivery_Model_Admin_Access_Group(1);

        //test access to resource 1
        $this->assertTrue($group->hasAccess(1));

        //test access to some not existing resource
        $this->assertFalse($group->hasAccess(10000));
    }


    /**
     * @author alex
     * @since 16.11.2010
     */
    public function testAddResource(){
        //create admin group for full access
        $group = new Yourdelivery_Model_Admin_Access_Group(1);

        $randomResource = rand(0, 10000000);

        //test access to some not existing resource
        $this->assertFalse($group->hasAccess($randomResource));

        //test access to some not existing resource
        $group->addResource($randomResource);

        // create new group so that the resources list will be updated
        $group2 = new Yourdelivery_Model_Admin_Access_Group(1);
        //test access to the added resource
        $this->assertTrue($group2->hasAccess($randomResource));
    }


}