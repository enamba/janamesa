<?php

/**
 * Test the access right management in admin backend
 * 
 * @author Alex Vait <vait@lieferando.de>
 * @since 29.08.2012
 * 
 */

/**
 * @runTestsInSeparateProcesses 
 */
class Administration_UserAccessTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 29.08.2012
     * 
     * create test user, assign him to test groups and test the access rights
     */
    public function testAccessRights() {
        // create test access resources for group 1
        $resources1 = array();
        for ($i = 0; $i < 5; $i++) {
            $rc = new Yourdelivery_Model_Admin_Resource();
            $rc->setAction(Default_Helper::generateRandomString(5) . time());
            $rc->save();
            $resources1[$rc->getId()] = $rc->getAction();
        }
        
        // create access group 1
        $group1 = new Yourdelivery_Model_Admin_Access_Group();
        $group1->setName(Default_Helper::generateRandomString(5) . '-Group');
        $group1->save();

        // set access recources for group 1
        foreach ($resources1 as $rcId => $rcAction) {
            $group1->addResource($rcId);
        }
        
        // test that all resources were set correctly
        foreach ($resources1 as $rcId => $rcAction) {
            $this->assertTrue($group1->hasAccess($rcId));
        }
        
        // create test access resources for group 2
        $resources2 = array();
        for ($i = 0; $i < 5; $i++) {
            $rc = new Yourdelivery_Model_Admin_Resource();
            $rc->setAction(Default_Helper::generateRandomString(5) . time());
            $rc->save();
            $resources2[$rc->getId()] = $rc->getAction();
        }
        
        // create access group 2
        $group2 = new Yourdelivery_Model_Admin_Access_Group();
        $group2->setName(Default_Helper::generateRandomString(5) . '-Group');
        $group2->save();
        
        // set access recources for group 1
        foreach ($resources2 as $rcId => $rcAction) {
            $group2->addResource($rcId);
        }

        // test that all resources were set correctly
        foreach ($resources2 as $rcId => $rcAction) {
            $this->assertTrue($group2->hasAccess($rcId));
        }
        
        // create new user and assign him to the first group
        $post = array(
            'name' => Default_Helper::generateRandomString(5) . ' ' . Default_Helper::generateRandomString(5),
            'email' => Default_Helper::generateRandomString(10) . '@test.de',
            'password' => md5(Default_Helper::generateRandomString(10)),
            'groupIds' => array($group1->getId())
            );

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/administration_adminrights/create');        
        
        $headers = $this->getResponse()->getHeaders();
        // read the URL of response
        $url = $headers[0]['value'];
        
        // extract the user id from url
        $userId = substr(strrchr($url, "/"), 1);
        $admin = new Yourdelivery_Model_Admin($userId);        
        $this->assertEquals($admin->getId(), $userId);
        
        // test if all rights of the group 1 are assigned to this user
        foreach ($resources1 as $rcAction) {
            $this->assertTrue($admin->hasAccessToResource($rcAction));            
        }

        // test if no rights of the group 2 are assigned to this user
        foreach ($resources2 as $rcAction) {
            $this->assertFalse($admin->hasAccessToResource($rcAction));            
        }
        
        // update user and assign him only to group 2
        $newName = Default_Helper::generateRandomString(5) . ' ' . Default_Helper::generateRandomString(5);
        $newEmail = Default_Helper::generateRandomString(10) . '@test.de';
        $newPassword = Default_Helper::generateRandomString(10);
        
        $post = array(
            'id' => $admin->getId(),
            'name' => $newName,
            'email' => $newEmail,
            'password' => $newPassword,
            'groupIds' => array($group2->getId())
            );

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/administration_adminrights/edit');
        
        $updatedAdmin = new Yourdelivery_Model_Admin($admin->getId());
        
        // test updated user data
        $this->assertEquals($updatedAdmin->getName(), $newName);
        $this->assertEquals($updatedAdmin->getEmail(), $newEmail);
        $this->assertEquals($updatedAdmin->getPassword(), md5($newPassword));
        
        // test if all rights of the group 2 are assigned to this user
        foreach ($resources2 as $rcAction) {
            $this->assertTrue($updatedAdmin->hasAccessToResource($rcAction));            
        }

        // test if no rights of the group 1 are assigned to this user
        foreach ($resources1 as $rcAction) {
            $this->assertFalse($updatedAdmin->hasAccessToResource($rcAction));            
        }        
        
        // cleanup
        foreach ($resources1 as $rcId => $rcAction) {
            Yourdelivery_Model_DbTable_Admin_Access_Resources::remove($rcId);
        }
        
        foreach ($resources2 as $rcId => $rcAction) {
            Yourdelivery_Model_DbTable_Admin_Access_Resources::remove($rcId);
        }
        
        Yourdelivery_Model_DbTable_Admin_Access_Groups::remove($group1->getId());
        Yourdelivery_Model_DbTable_Admin_Access_Groups::remove($group2->getId());
        Yourdelivery_Model_DbTable_Admin_Access_Users::remove($admin->getId());
    }
}