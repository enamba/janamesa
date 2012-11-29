<?php
/**
 * CRM controller
 * @author alex
 */
class Administration_CrmController extends Default_Controller_AdministrationBase {
    
    protected $object = null;
    
    public function createticketAction() {
        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();
            
            $form = new Yourdelivery_Form_Administration_Crm_Ticket();
            if ($form->isValid($post)) {
                $admin = $this->session->admin;

                $values = $form->getValues();

                // manage the status of restaurant, and write the changes to the restaurant notepad
                if (strcmp($post['refType'], 'service') == 0) {
                    try {
                        $service = new Yourdelivery_Model_Servicetype_Restaurant($post['refId']);
                    } 
                    catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->error(__b("The referred service cannot be created!"));
                        $this->_redirect('/administration_crm');
                    }
                    
                    $oldOnlineStatus = $service->isOnline();
                    $oldOfflineStatus = $service->getStatus();
                    
                    // manage restaurant status only if something has changed, else it's just a ticket without status change
                    if ( (intval($oldOnlineStatus) != $values['isOnline']) || ($oldOfflineStatus != $values['status']) ) {
                        
                        if ($values['isOnline'] == 1) {
                            $values['status'] = 0;
                        }
                        
                        // check if scheduled date is definde for this statis
                        if (in_array($values['status'], array(2, 4, 5, 7, 12, 13, 14)) && strlen($values['scheduledD'])==0) {
                            $this->error(__b("For this status you must set a scheduled date!"));
                            $this->_redirect('/administration_service_edit/crm/id/' . $service->getId());
                        }

                        $service->setIsOnline($values['isOnline']);
                        $service->setStatus($values['status']);
                        $service->save();

                        $offlineStati = Yourdelivery_Model_Servicetype_Abstract::getStati();

                        //write the reason of status change to the restaurant notepad
                        if ($values['isOnline'] == 0) {
                            if (is_null($admin)) {
                                $this->error(__b("Kein Admin wurde in der Sitzung gefunden, kann die Begründung für die Statusänderung nicht eintragen"));
                            } 
                            else {
                                $comment = new Yourdelivery_Model_Servicetype_RestaurantNotepad();
                                $comment->setMasterAdmin(1);
                                $comment->setAdminId($admin->getId());

                                $comment->setRestaurantId($service->getId());
                                $comment->setComment(__b("[offline status gesetzt: '%s']. Begründung: %s", $offlineStati[$values['status']], trim($values['message'])));
                                $comment->setTime(date("Y-m-d H:i:s", time()));
                                $comment->save();
                            }
                        } 
                        else if (($oldOnlineStatus == 0) && ($service->isOnline() == 1)) {
                            $admin = $this->session->admin;

                            if (is_null($admin)) {
                                $this->error(__b("Kein Admin wurde in der Sitzung gefunden, kann die Begründung für die Statusänderung nicht eintragen"));
                            } 
                            else {
                                $comment = new Yourdelivery_Model_Servicetype_RestaurantNotepad();
                                $comment->setMasterAdmin(1);
                                $comment->setAdminId($admin->getId());

                                $comment->setRestaurantId($service->getId());
                                $comment->setComment(__b("Online gestellt"));
                                $comment->setTime(date("Y-m-d H:i:s", time()));
                                $comment->save();
                            }
                        }

                        //log the online status
                        if ($oldOnlineStatus != $service->isOnline()) {
                            if ($service->isOnline()) {
                                $this->logger->adminInfo(sprintf("Restaurant %s (%s) was set online", $service->getName(), $service->getId()));
                            }
                            else {
                                $this->logger->adminInfo(sprintf("Restaurant %s (%s) was set offline with status %s", $service->getName(), $service->getId(), $offlineStati[$service->getStatus()]));
                            }
                        }

                        // offline state changed
                        if ( ($oldOnlineStatus == 0) && ($service->isOnline() == 0) && ($oldOfflineStatus != $service->getStatus()) ){
                            $this->logger->adminInfo(sprintf("Offline status of %s (%s) changed from %s to %s", $service->getName(), $service->getId(), $offlineStati[$oldOfflineStatus], $offlineStati[$service->getStatus()]));
                        }                        
                    }
                }            
                
                if (strlen($values['scheduledD'])!=0) {
                   $values['scheduled'] = substr($values['scheduledD'], 6, 4) . "-" . substr($values['scheduledD'], 3, 2) . "-" . substr($values['scheduledD'], 0, 2) . " " . substr($values['scheduledT'], 0, 2) . ":" . substr($values['scheduledT'], 3, 2) . ":00";                        
                }

                if (!is_null($admin)) {
                    $values['createdBy'] = $admin->getId();
                }
                else {
                    $values['createdBy'] = 0;
                }

                // if offline status was defined, it means we're creating tickets in batch process
                if (!is_null($values['offlineStatus'])) {
                    $countCreated = 0;
                    foreach (Yourdelivery_Model_Servicetype_Abstract::getAllByStatus($post['offlineStatus']) as $rest) {
                        $values['refType'] = 'service';
                        $values['refId'] = $rest->getId();
                        $ticket = new Yourdelivery_Model_Crm_Ticket();
                        $ticket->setData($values);
                        $ticket->save();
                        
                        $countCreated++;

                        // save the entry in ticket history
                        $ticketHistoryEntry = new Yourdelivery_Model_Crm_Tickethistory();
                        $ticketHistoryEntry->setData($values);
                        $ticketHistoryEntry->setTicketId($ticket->getId());

                        if (!is_null($admin)) {
                            $ticketHistoryEntry->setChangedBy($admin->getId());
                        }
                        else {
                            $ticketHistoryEntry->setChangedBy(0);
                        }
                        $ticketHistoryEntry->save();                

                        $this->logger->adminInfo(sprintf("Ticket #%d was created", $ticket->getId()));
                    }
                    $this->success($countCreated .  __b(" tickets were created"));                            
                }
                else {                    
                    $ticket = new Yourdelivery_Model_Crm_Ticket();
                    $ticket->setData($values);
                    $ticket->save();

                    // save the entry in ticket history
                    $ticketHistoryEntry = new Yourdelivery_Model_Crm_Tickethistory();
                    $ticketHistoryEntry->setData($values);
                    $ticketHistoryEntry->setTicketId($ticket->getId());

                    if (!is_null($admin)) {
                        $ticketHistoryEntry->setChangedBy($admin->getId());
                    }
                    else {
                        $ticketHistoryEntry->setChangedBy(0);
                    }
                    $ticketHistoryEntry->save();                

                    $this->logger->adminInfo(sprintf("Ticket #%d was created", $ticket->getId()));
                    $this->success(__b("Ticket was created"));                           
                }
            }
            else {
                $this->error($form->getMessages());                
            }
            
            switch ($post['refType']) {
                case 'service':
                    $this->_redirect('/administration_service_edit/crm/id/' . $post['refId']);
                case 'company':
                    $this->_redirect('/administration_company_edit/crm/companyid/' . $post['refId']);
                case 'customer':
                    $this->_redirect('/administration_user_edit/crm/userid/' . $post['refId']);
                default:
                    $this->_redirect('/administration_crm');
                    break;
            }
        }
    }    
    
    /**
     * edit crm ticket
     * @author alex
     * @since 12.07.2011
     */
    public function editticketAction(){
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration_crm/tickets');
        }

        $id = $request->getParam('ticketId');

        if (is_null($id)) {
            $this->error(__b("No id was defined"));
            $this->_redirect('/administration_crm');
        }

        //create crm ticket object
        try {
            $ticket = new Yourdelivery_Model_Crm_Ticket($id);
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("This ticket ist non-existant"));
            $this->_redirect('/administration_crm');
        }

        if ( $request->isPost() ){
            $post = $this->getRequest()->getPost();

            $form = new Yourdelivery_Form_Administration_Crm_Ticketedit();
            if ( $form->isValid($post) ){
                $values = $form->getValues();

                if (strlen($values['scheduledD'])!=0) {
                   $values['scheduled'] = substr($values['scheduledD'], 6, 4) . "-" . substr($values['scheduledD'], 3, 2) . "-" . substr($values['scheduledD'], 0, 2) . " " . substr($values['scheduledT'], 0, 2) . ":" . substr($values['scheduledT'], 3, 2) . ":00";                        
                }
                
                //save new data
                $ticket->setData($values);
                $ticket->save();
                $this->success(__b("Changes successfully saved"));
                $this->logger->adminInfo(sprintf("Crm ticket #%d was edited", $ticket->getId()));
                
                // save the entry in ticket history
                $ticketHistoryEntry = new Yourdelivery_Model_Crm_Tickethistory();
                $ticketHistoryEntry->setData($values);
                $ticketHistoryEntry->setTicketId($ticket->getId());
                
                $admin = $this->session->admin;
                if (!is_null($admin)) {
                    $ticketHistoryEntry->setChangedBy($admin->getId());
                }
                else {
                    $ticketHistoryEntry->setChangedBy(0);
                }
                $ticketHistoryEntry->save();                
                
                $this->_redirect('/administration_crm/tickethistory/ticketId/' . $ticket->getId());
            }
            else{
                $this->error($form->getMessages());
                $this->_redirect('/administration_crm/editticket/ticketId/' . $ticket->getId());
            }
        }
        
        if ($ticket->getAssignedToId() != 0) {
            try {
                $assignedAdmin = new Yourdelivery_Model_Admin($ticket->getAssignedToId(), null);
                $this->view->assign('assignedAdmin', $assignedAdmin);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            }
        }
                
        $this->view->assign('ticket', $ticket);
    }
    

    /**
     * get grid wih all tickets
     * @author alex
     * @since 12.07.2011
     */
    public function ticketsAction() {
        $this->view->grid = Yourdelivery_Model_Crm_Ticket::getGrid();
    }    
    
    
    /**
     * get grid wih all tickets assigned to the logged admin
     * @author alex
     * @since 13.07.2011
     */
    public function indexAction() {
        $admin = $this->session->admin;
        if (!is_null($admin)) {
            $this->view->grid = Yourdelivery_Model_Crm_Ticket::getGrid(null, null, $admin->getId());
        }
        else {
            $this->error(__b("Admin information not found. Strange :("));
        }                
    }    
    
    /**
     * delete crm ticket
     * @author alex
     * @since 13.07.2011
     */
     public function deleteticketAction() {
        $request = $this->getRequest();
        $id = $request->getParam('ticketId');

        try {
            $ticket = new Yourdelivery_Model_Crm_Ticket($id);
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("This ticket ist non-existant"));
            $this->_redirect('/administration_crm');
        }
        
        $ticket->getTable()->remove($ticket->getId());
        $this->success(__b("Ticket was deleted"));
        $this->logger->adminInfo(sprintf("CRM Ticket #%d was deleted", $cid));
        
        $this->_redirect('/administration_crm');
    }
    
    
    /**
     * edit crm ticket
     * @author alex
     * @since 12.07.2011
     */
    public function tickethistoryAction(){
        $request = $this->getRequest();

        $id = $request->getParam('ticketId');

        if (is_null($id)) {
            $this->error(__b("No id was defined"));
            $this->_redirect('/administration_crm');
        }

        //create crm ticket object
        try {
            $ticket = new Yourdelivery_Model_Crm_Ticket($id);
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("This ticket ist non-existant"));
            $this->_redirect('/administration_crm');
        }
        
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        $grid->setPagination(20);
        
        //select crm tickets
        $select = $db->select()->from(array('c'=>'crm_ticket_history'),
                                            array(
                                                'ID',
                                                'editor_message',
                                                'topic',
                                                'reasonId',
                                                'message',
                                                'tel',
                                                'email',
                                                'ticket',
                                                'ticketNr',
                                                'closed',
                                                'closed2' => 'closed',
                                                'assignedToId',
                                                'scheduled',
                                                'changedBy',
                                                'created'
                                            ))
                    ->joinLeft(array('admin1'=>'admin_access_users'),'admin1.id=c.assignedToId',array('assignedName'=>'admin1.name'))
                    ->joinLeft(array('admin2'=>'admin_access_users'),'admin2.id=c.changedBy',array('changedAdmin'=>'admin2.name'))
                    ->where('ticketId=' . $ticket->getId())
                    ->order('c.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID', array('hidden' => 1));
        $grid->updateColumn('changedBy', array('hidden' => 1));
        $grid->updateColumn('assignedName', array('hidden' => 1));
        $grid->updateColumn('closed2', array('hidden' => 1));
            
        $grid->updateColumn('editor_message', array('title'=>__b('Grund für Bearbeitung')));
        $grid->updateColumn('topic', array('title'=>__b('Betreff')));
        $grid->updateColumn('reasonId', array('title'=>__b('Grund'), 'callback' => array('function' => 'crmReasonToReadable', 'params' => array('{{reasonId}}'))));
        $grid->updateColumn('message', array('title'=>__b('Aufgabe')));
        $grid->updateColumn('tel', array('title'=>__b('Telefon'), 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{tel}}'))));
        $grid->updateColumn('email', array('title'=>__b('E-Mail'), 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{email}}'))));
        $grid->updateColumn('ticket', array('title'=>__b('Ticket'), 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{ticket}}'))));
        $grid->updateColumn('ticketNr', array('title'=>__b('Ticket Nr')));
        $grid->updateColumn('closed', array('title'=>__b('Status'), 'callback' => array('function' => 'crmOpenClosed', 'params' => array('{{closed}}'))));
        $grid->updateColumn('assignedToId', array('title'=>__b('Zugewiesen'), 'decorator'=>'{{assignedName}}'));        
        $grid->updateColumn('changedAdmin', array('title'=>__b('Bearbeitet von'), 'decorator'=>'{{changedAdmin}} (#{{changedBy}})'));        
        $grid->updateColumn('scheduled', array('title'=>__b('Zu erledigen'), 'callback' => array('function' => 'intCrmTicketScheduledIcon', 'params' => array('{{closed2}}', '{{scheduled}}'))));
        $grid->updateColumn('created', array('title'=>__b('Bearbeitet am')));

        //add filters
        $filters = new Bvb_Grid_Filters();

        //add filters
        $filters->addFilter('ID')
            ->addFilter('refType', array('values' => $types))            
            ->addFilter('topic')
            ->addFilter('reasonId')
            ->addFilter('message')
            ->addFilter('tel', array('values' => $yesNoStates))
            ->addFilter('email', array('values' => $yesNoStates))
            ->addFilter('ticket', array('values' => $yesNoStates))
            ->addFilter('ticketNr')
            ->addFilter('closed', array('values' => $statis));

        $grid->addFilters($filters);


        $this->view->assign('grid', $grid->deploy());
        $this->view->assign('ticket', $ticket);
    }
        
    
    /**
     * create batch crm tickets for all restaurants with certain offline status
     * @author alex
     * @since 14.07.2011
     */
    public function batchticketsAction(){
        $request = $this->getRequest();

        if ( $request->isPost() ){
            $post = $this->getRequest()->getPost();

            $form = new Yourdelivery_Form_Administration_Crm_Ticket();
            if ( $form->isValid($post) ){
                if ($post['offlineStatus'])
                
                
                $values = $form->getValues();

                if (strlen($values['scheduledD'])!=0) {
                   $values['scheduled'] = substr($values['scheduledD'], 6, 4) . "-" . substr($values['scheduledD'], 3, 2) . "-" . substr($values['scheduledD'], 0, 2) . " " . substr($values['scheduledT'], 0, 2) . ":" . substr($values['scheduledT'], 3, 2) . ":00";                        
                }
                
                //save new data
                $ticket->setData($values);
                $ticket->save();
                $this->success(__b("Changes successfully saved"));
                $this->logger->adminInfo(sprintf("Crm ticket #%d was edited", $ticket->getId()));
                
                // save the entry in ticket history
                $ticketHistoryEntry = new Yourdelivery_Model_Crm_Tickethistory();
                $ticketHistoryEntry->setData($values);
                $ticketHistoryEntry->setTicketId($ticket->getId());
                
                $admin = $this->session->admin;
                if (!is_null($admin)) {
                    $ticketHistoryEntry->setChangedBy($admin->getId());
                }
                else {
                    $ticketHistoryEntry->setChangedBy(0);
                }
                $ticketHistoryEntry->save();                
            }
            else{
                $this->error($form->getMessages());
                $this->_redirect('/administration_crm/editticket/ticketId/' . $ticket->getId());
            }
        }
        
        $this->view->assign('batch', 1);
    }
        
}
?>
