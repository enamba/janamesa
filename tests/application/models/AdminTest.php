<?php
/**
 * @author alex
 * @since 16.11.2010
 */
/**
 * @runTestsInSeparateProcesses 
 */
class AdminTest extends Yourdelivery_Test{

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
     * @author Alex Vait
     * @since 16.11.2010
     * @updated 29.08.2012
     */
    public function testAdmin(){
        
        $email = time() . "@" .  time() . ".de";
        
        $admin = new Yourdelivery_Model_Admin();
        $admin->setData(array(
            'email' => $email,
            'name' => "Samson Tiffy",
            'password' => md5("123"),
        ));
        $admin->save();        
        
        // create test access group
        $group = new Yourdelivery_Model_Admin_Access_Group();
        $group->setName(Default_Helper::generateRandomString(5) . '-Group');
        $group->setIsAdmin(1);
        $group->save();
        
        // assign this group to the admin
        $userGroupNn = new Yourdelivery_Model_Admin_Access_UserGroupNn();
        $userGroupNn->setData(
                array(
                    'userId' => $admin->getId(),
                    'groupId' => $group->getId()
                )
            );
        $userGroupNn->save();
         
        //assert User has Group
        $this->assertTrue($admin->hasGroup($group->getName()));
        
        // group is admin, so the user must have the admin rights too
        $this->assertTrue($admin->getId() > 0);
        $this->assertTrue($admin->isAdmin());
        
        // group is not admin group anymore
        $group->setIsAdmin(0);
        $group->save();
        
        // create refreshed user without admin rights
        $admin2 = new Yourdelivery_Model_Admin($admin->getId());
        $this->assertFalse($admin2->isAdmin());

        // cleanup
        Yourdelivery_Model_DbTable_Admin_Access_Groups::remove($group->getId());
                      
        // creating user with this email returns user with the same id
        $admin3 = new Yourdelivery_Model_Admin(null, $email);
        $this->assertEquals($admin3->getId(), $admin->getId());
                
        //group has been removed
        $this->assertFalse($admin3->hasGroup($group->getName()));
    }                      
}