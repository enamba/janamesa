<?php

/**
 * management of users with admin rights
 *
 * @author Alex Vait <vait@lieferando.de>
 */
class Administration_AdminrightsController extends Default_Controller_AdministrationBase {

    /**
     * create new admin
     *
     * @author Alex Vait <vait@lieferando.de>
     * @return _redirect | null
     */
    public function createAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/admins');
        }

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            //create new admin
            $form = new Yourdelivery_Form_Administration_Adminrights_Create();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                //create admin object
                try {
                    $testAdmin = new Yourdelivery_Model_Admin(null, $values['email']);
                    if (!is_null($testAdmin) && ($testAdmin->getId() > 0)) {
                        $this->error(__b("Benutzer mit dieser E-mail ist bereits eingetragen mit id %d bei der Admingruppe '%s'", $testAdmin->getId(), $testAdmin->getGroup()->getName()));
                        return $this->_redirect('/administration_adminrights/create');
                    }
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

                }

                $admin = new Yourdelivery_Model_Admin();
                $values['password'] = md5($values['password']);

                $admin->setData($values);

                try {
                    $admin->save();
                } 
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error($e->getMessage());
                    return $this->_redirect('/administration_adminrights/create');
                }                

                foreach ($post['groupIds'] as $grId) {
                    try {
                        $userGroupNn = new Yourdelivery_Model_Admin_Access_UserGroupNn();
                        $userGroupNn->setData(
                                array(
                                    'userId' => $admin->getId(),
                                    'groupId' => $grId
                                )
                            );
                        $userGroupNn->save();
                    } 
                    catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    }                    
                }
                
                $this->success(__b("Benutzer wurde erfolgreich angelegt"));
                $this->logger->adminInfo(sprintf("Benutzer %s (#%d) wurde wurde angelegt", $admin->getEmail(), $admin->getId()));
                
                $this->_redirect('/administration_adminrights/edit/id/' . $admin->getId());
            } 
            else {
                $this->error($form->getMessages());
            }
        }

        $this->view->assign('groups', Yourdelivery_Model_DbTable_Admin_Access_Groups::getDistinctId());
    }

    /**
     * edit admin
     *
     * @author Alex Vait <vait@lieferando.de>
     * @return _redirect | null
     */
    public function editAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/admins');
        }

        if (is_null($request->getParam('id'))) {
            $this->error(__b("This admin is non-existant"));
            return $this->_redirect('/administration/admins');
        }

        //create admin object
        try {
            $admin = new Yourdelivery_Model_Admin($request->getParam('id'));
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("This admin is non-existant"));
            return $this->_redirect('/administration/admins');
        }

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            $form = new Yourdelivery_Form_Administration_Adminrights_Edit();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                if (strlen($post['password'])) {
                    $values['password'] = md5($post['password']);
                }

                //save data
                $admin->setData($values);
                $admin->save();
                                
                // remove all access rights to set new rights
                Yourdelivery_Model_DbTable_Admin_Access_UserGroupNn::removeAllForUser($admin->getId());
                
                $emptyGroupVal = '-1';
                
                // if the empty group was selected and it was the only one, don't set any access rights - it means the user 
                // has no access to the backend anymore;
                // if any other group was selected additionally, so it was an input error, then ignore the empty entry and set right normally
                if (!in_array($emptyGroupVal, $post['groupIds']) || count($post['groupIds'])>1) {
                    // if there is an empty group entry, remove it
                    if (($emptyIndex = array_search($emptyGroupVal, $post['groupIds'])) !== false) {
                        unset($post['groupIds'][$emptyIndex]);                        
                    }
                    
                    // iterate over groups
                    foreach ($post['groupIds'] as $grId) {
                        try {
                            $userGroupNn = new Yourdelivery_Model_Admin_Access_UserGroupNn();
                            $userGroupNn->setData(
                                    array(
                                        'userId' => $admin->getId(),
                                        'groupId' => $grId
                                    )
                                );
                            $userGroupNn->save();
                        }
                        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        }
                    }                    
                }
                
                $this->success(__b("Benutzer wurde erfolgreich bearbeitet"));
                $this->logger->adminInfo(sprintf("Benutzer %s (#%d) wurde wurde bearbeitet", $admin->getEmail(), $admin->getId()));
                
                return $this->_redirect('/administration_adminrights/edit/id/' . $admin->getId());
            } else {
                $this->view->assign('p', $post);
                $this->error($form->getMessages());
            }
        }

        $assignedGroups = array();
        foreach ($admin->getGroups() as $gr) {
            $assignedGroups[] = $gr->getId();
        }
        
        
        $this->view->assign('assignedGroups', $assignedGroups);
        $this->view->assign('admin', $admin);
    }

    /**
     * edit admin group
     *
     * @author Alex Vait <vait@lieferando.de>
     * @return _redirect | null
     */
    public function editgroupAction() {
        $request = $this->getRequest();

        if (is_null($request->getParam('id'))) {
            $this->error(__b("This group is non-existant"));
            return $this->_redirect('/administration/admingroups');
        }

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/admingroups');
        }

        if ($request->getParam('id') == 1) {
            $this->error(__b("It is not allowed to change admin group settings"));
            return $this->_redirect('/administration/admingroups');
        }

        //create admin group object
        try {
            $admingroup = new Yourdelivery_Model_Admin_Access_Group($request->getParam('id'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("This group is non-existant"));
            return $this->_redirect('/administration/admingroups');
        }

        $resTable = new Yourdelivery_Model_DbTable_Admin_Access_Resources();

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();
            $form = new Yourdelivery_Form_Administration_Adminrights_EditGroup();

            if ($form->isValid($post)) {
                $values = $form->getValues();
                //save new data
                $admingroup->setData($values);
                $admingroup->save();

                //remove all resources assocations and add those, which were checked
                $admingroup->clearResources();
                foreach ($resTable->getDistinctId() as $res) {
                    if (isset($post['resource' . $res['id']])) {
                        $admingroup->addResource($res['id']);
                    }
                }

                return $this->_redirect('/administration/admingroups');
            } else {
                // WTF ???
                $this->error($form->getMessages());
                return $this->_redirect('/administration_adminrights/editgroup/id/' . $admingroup->getId());
            }
        }

        $this->view->assign('resources', $resTable->getDistinctId());
        $this->view->assign('group', $admingroup);
    }

    /**
     * create admin group
     *
     * @author Alex Vait <vait@lieferando.de>
     * @return _redirect | null
     */
    public function creategroupAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/admingroups');
        }

        //create admin group object
        try {
            $admingroup = new Yourdelivery_Model_Admin_Access_Group();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return $this->_redirect('/administration/admingroups');
        }

        $resTable = new Yourdelivery_Model_DbTable_Admin_Access_Resources();

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            $form = new Yourdelivery_Form_Administration_Adminrights_EditGroup();
            if ($form->isValid($post)) {
                $values = $form->getValues();
                //save data
                $admingroup->setData($values);
                $admingroup->save();

                for ($n = 1; $n <= count($resTable->getDistinctId()); $n++) {
                    if (isset($post['resource' . $n])) {
                        $admingroup->addResource($n);
                    }
                }

                return $this->_redirect('/administration/admingroups');
            } else {
                $this->view->assign('p', $post);
                $this->error($form->getMessages());
            }
        }

        $this->view->assign('resources', $resTable->getDistinctId());
    }

    /**
     * change the description of admin group
     *
     * @author Alex Vait <vait@lieferando.de>
     * @return null
     */
    public function savegroupdescAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

            //create admin resource object
            try {
                $adminrcs = new Yourdelivery_Model_Admin_Resource($post['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                echo(__b("This ressource is non-existant"));
                return;
            }

            $adminrcs->setDescription($post['description']);
            $adminrcs->save();
            echo 'ok';
        }
    }

}

?>