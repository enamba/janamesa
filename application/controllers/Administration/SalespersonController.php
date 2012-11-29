<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * salers management
 *
 * @author alex
 */
class Administration_SalespersonController extends Default_Controller_AdministrationBase{

    public function  preDispatch() {
        parent::preDispatch();
        $this->view->assign('navsalespersons', 'active');
    }
    
    /**
     * create new saler
     * @author alex
     */
    public function createAction(){
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/salespersons');
        }

        if ( $request->isPost() ){
            $post = $this->getRequest()->getPost();

            //create new saler
            $form = new Yourdelivery_Form_Administration_Salesperson_Create();
            if($form->isValid($post)) {
                $saler = new Yourdelivery_Model_Salesperson();

                $values = $form->getValues();

                $saler->setData($values);
                $saler->save();
                $this->logger->adminInfo(sprintf("Salesperson %s %s (#%d) was created", $saler->getName(), $saler->getPrename(), $saler->getId()));

                //if password is provided, save this salesperson in admin_access_users table
                // so he has access to the admin backend and can enter his working times
                if (strlen(trim($values['password']))) {
                    $admin = new Yourdelivery_Model_Admin();
                    $values['password'] = md5($values['password']);
                    $values['name'] = $values['prename'] . ' ' . $values['name'];
                    $values['groupId'] = Yourdelivery_Model_DbTable_Admin_Access_Groups::getGroupId('Vertrieb_callcenter');

                    $admin->setData($values);
                    $admin->save();
                }
                
                $this->_redirect('/administration/salespersons');
            }
            else {
                $this->error($form->getMessages());
                $this->view->assign('p', $post);
            }
        }
    }

    /**
     * edit saler
     * @author alex
     */
    public function editAction(){
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/salespersons');
        }

        if(is_null($request->getParam('id'))) {
            $this->error(__b("Diesen Vertriebler gibt es nicht!"));
            $this->_redirect('/administration/salespersons');
        }

        //create salesperson object
        try {
            $salesperson = new Yourdelivery_Model_Salesperson($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("Diesen Vertriebler gibt es nicht!"));
            $this->_redirect('/administration/salespersons');
        }

        if ( $request->isPost() ){
            $post = $this->getRequest()->getPost();

            $form = new Yourdelivery_Form_Administration_Salesperson_Edit();
            if ( $form->isValid($post) ){
                $values = $form->getValues();
                
                //save new data
                $salesperson->setData($values);
                $salesperson->save();
                $this->logger->adminInfo(sprintf("Salesperson %s %s (#%d) was edited", $salesperson->getName(), $salesperson->getPrename(), $salesperson->getId()));
                $this->_redirect('/administration/salespersons');
            }
            else{
                $this->error($form->getMessages());
            }
        }
        
        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());

        $compTable = new Yourdelivery_Model_DbTable_Company();
        $this->view->assign('compIds', $compTable->getDistinctNameId());

        $this->view->assign('salesperson', $salesperson);
    }

    /**
     * delete salesperson
     * @author alex
     */
     public function deleteAction() {
        $this->error(__b("Vertriebler dürfen nicht mehr gelöscht werden!"));
        $this->_redirect('/administration/salespersons');
        
        $request = $this->getRequest();

        if(is_null($request->getParam('id'))) {
            $this->error(__b("Diesen Vertriebler gibt es nicht!"));
            $this->_redirect('/administration/salespersons');
        }

        //create salesperson object
        try {
            $saler = new Yourdelivery_Model_Salesperson($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("Diesen Vertriebler gibt es nicht!"));
            $this->_redirect('/administration/salespersons');
        }

        if(is_null($saler->getId())) {
            $this->error(__b("Diesen Vertriebler gibt es nicht!"));
        } else {
            $saler->getTable()->remove($saler->getId());
            $this->logger->adminInfo(sprintf("Salesperson %s %s (#%d) was deleted", $saler->getName(), $saler->getPrename(), $saler->getId()));
        }
        
        $this->_redirect('/administration/salespersons');
    }


    /**
     * information about the saler
     * @author alex
     */
    public function infoAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id', null);

        if(is_null($id)) {
            $this->error(__b("Diesen Vertriebler gibt es nicht!"));
            $this->_redirect('/administration/salespersons');
        }

        if(!is_null($id)) {
            try {
                $salesperson = new Yourdelivery_Model_Salesperson($id);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                $this->error(__b("Diesen Vertriebler gibt es nicht!"));
                $this->_redirect('/administration/salespersons');
            }

            $this->view->assign('salesperson', $salesperson);
        }
        else {
            $this->_redirect('/administration/salespersons');
        }
    }

    /**
     * add restaurant association
     * @author alex
     */
    public function addrestaurantAction() {
        $request = $this->getRequest();

        //create salesperson object
        try {
            $salesperson = new Yourdelivery_Model_Salesperson($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
            $this->error(__b("Diesen Vertriebler gibt es nicht!"));
            $this->_redirect('/administration/salespersons');
        }

        $restaurantId = $request->getParam('restaurantId', null);

        if ( $request->isPost() ) {
            $post = $this->getRequest()->getPost();

            //associate salesperson with the service
            if ( ($restaurantId != -1) && (!is_null($restaurantId))) {
                //create salesperson relationship
                $salespersonRelTable = new Yourdelivery_Model_DbTable_Salesperson_Restaurant();

                //if salesperson is not registered for this restaurant, add him
                if ( !$salespersonRelTable->isSalespersonFor($salesperson->getId(), $restaurantId) ) {
                    $salespersonRelation = new Yourdelivery_Model_Salesperson_Restaurant();
                    $salespersonRelation->add($salesperson->getId(), $restaurantId);

                    $this->logger->adminInfo(sprintf("Restaurant #%d associated with salesperson #%s", $restaurantId, $salesperson->getId()));
                }
            }
            $this->_redirect('/administration_salesperson/edit/id/' . $salesperson->getId());
        }
    }

    /**
     * remove restaurant assotiation
     * @author alex
     */
    public function removerestaurantAction() {
        $request = $this->getRequest();

        //create salesperson object
        try {
            $salesperson = new Yourdelivery_Model_Salesperson($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
            $this->error(__b("Diesen Vertriebler gibt es nicht!"));
            $this->_redirect('/administration/salespersons');
        }

        $restaurantId = $request->getParam('restaurantId', null);

        if (!is_null($restaurantId)) {
            Yourdelivery_Model_Salesperson_Restaurant::delete($salesperson->getId(),  $restaurantId);
            $this->logger->adminInfo(sprintf("Removed association of restaurant #%d with salesperson #%s", $restaurantId, $salesperson->getId()));
        }
        $this->_redirect('/administration_salesperson/edit/id/' . $salesperson->getId());
    }



    /**
     * test if this mail is already entered in admin_access_users table
     * @author alex
     */
    public function testmailAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $email = $request->getParam('email', null);

            $salesperson = Yourdelivery_Model_Salesperson::getByEmail($email);
            
            // this email is already registered in salespersons table
            if ($salesperson->getId() != 0) {
                echo 1;
            }
            // this email is not registered in salespersons table
            else {
                // registered as admin, but not as saler
                if (Yourdelivery_Model_Salesperson::registeredAsAdmin($email)) {
                    echo 2;
                }
                // neither registered as admin, nor as saler
                else {
                    echo 0;
                }
            }
        }
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }

    /**
     * create admin account for the salesperson so that the salseperson can enter the working times
     * @author alex
     */
    public function registeradminAction() {
        $request = $this->getRequest();
        
        if ( $request->isPost() ){
            $post = $this->getRequest()->getPost();

            //create salesperson object
            try {
                $salesperson = new Yourdelivery_Model_Salesperson($post['id']);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                $this->error(__b("Diesen Vertriebler gibt es nicht!"));
                $this->_redirect('/administration/salespersons');
            }
            
            if (strlen(trim($post['password']))) {
                $values = array();
                
                $admin = new Yourdelivery_Model_Admin();
                $values['password'] = md5($post['password']);
                $values['email'] = $salesperson->getEmail();
                $values['name'] = $salesperson->getPrename() . ' ' . $salesperson->getName();
                $values['groupId'] = Yourdelivery_Model_DbTable_Admin_Access_Groups::getGroupId('Vertrieb_callcenter');

                $admin->setData($values);
                $admin->save();
                
                $this->logger->adminInfo(sprintf("Salesperson #%d was registered as admin #%d", $salesperson->getId(), $admin->getId()));
            }
            else {
                $this->error(__b("Kein Passwort wurde angegeben"));
            }
            
            $this->_redirect('/administration_salesperson/edit/id/' . $salesperson->getId());
        }        
    }
    

}
?>
