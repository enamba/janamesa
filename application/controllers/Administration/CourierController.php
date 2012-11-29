<?php
/**
 * Couriers management
 * @author alex
 */
class Administration_CourierController extends Default_Controller_AdministrationBase{

    /**
     * Create new courier
     * @author vpriem
     * @since 01.08.2010
     * @return void
     */
    public function createAction(){

        // get request
        $request = $this->getRequest();

        // cancel
        if ($request->getParam('cancel') !== null) {
            $this->_redirect('/administration/couriers');
        }

        // post
        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            // create new courier
            $form = new Yourdelivery_Form_Administration_Courier_Edit();
            if ($form->isValid($post)) {
                $courier = new Yourdelivery_Model_Courier();

                $values = $form->getValues();

                // if we are in Brasil, create city based on plz
                if (strpos($this->config->domain->base, "janamesa") !== false) {        
                    $cityByPLz = Yourdelivery_Model_City::getByPlz($values['plz']);
                    // we take the first one, beacuse we have only one city entry per plz in Brazil
                    $c = $cityByPLz[0];
                    
                    if (is_null($c)) {
                        $this->error(__b("Diese PLZ existiert nicht!"));
                        $this->_redirect('/administration_courier/create');
                    }
                    
                    $values['cityId'] = $c['id'];
                }
                else {
                    $city = new Yourdelivery_Model_City($values['cityId']);
                    $values['plz'] = $city->getPlz();                    
                }                

                $values['notify'] = 0;
                if (is_array($post['notify'])) {
                    foreach ($post['notify'] as $notify) {
                        $values['notify'] += $notify;
                    }
                }

                // save
                $courier->setData($values);
                $courier->save();

                $custNr = Yourdelivery_Model_DbTable_Courier::getActualCustNr() + 1;
                if ($custNr == 1) {
                    $config = Zend_Registry::get('configuration');
                    $custNr = $config->customerNr->courier->initialval;
                }
                $courier->setCustomerNr($custNr);
                $courier->save();

                $this->info(__b("Courier succesfully created"));
                $this->logger->adminInfo(sprintf("Created courier #%d", $courier->getId()));
                $this->_redirect('/administration/couriers');
            }
            else {
                $this->error($form->getMessages());
                $this->view->assign('p', $post);
            }
        }

        $contTable = new Yourdelivery_Model_DbTable_Contact();
        $this->view->assign('contacts', $contTable->getDistinctNameId());
        
    }

    /**
     * Edit courier
     * @author vpriem
     * @since 01.08.2010
     * @return void
     */
    public function editAction(){
        
        // get request
        $request = $this->getRequest();

        // cancel
        if ($request->getParam('cancel') !== null) {
            $this->_redirect('/administration/couriers');
        }

        if(is_null($request->getParam('cid'))) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            $this->_redirect('/administration/couriers');
        }

        // create courier object
        try {
            $courier = new Yourdelivery_Model_Courier($request->getParam('cid'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            $this->_redirect('/administration/couriers');
        }

        // post
        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            $form = new Yourdelivery_Form_Administration_Courier_Edit();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                // if we are in Brasil, create city based on plz
                if (strpos($this->config->domain->base, "janamesa") !== false) {      
                    $cityByPLz = Yourdelivery_Model_City::getByPlz($values['plz']);
                    // we take the first one, beacuse we have only one city entry per plz in Brazil
                    $c = $cityByPLz[0];
                    
                    if (is_null($c)) {
                        $this->error(__b("Diese PLZ existiert nicht!"));
                        $this->_redirect('/administration_courier/edit/cid/' . $courier->getId());
                    }
                    
                    $values['cityId'] = $c['id'];
                }
                else {
                    $city = new Yourdelivery_Model_City($values['cityId']);
                    $values['plz'] = $city->getPlz();                    
                }
               
                $values['notify'] = 0;
                if (is_array($post['notify'])) {
                    foreach ($post['notify'] as $notify) {
                        $values['notify'] += $notify;
                    }
                }

                $values['subvention'] = str_replace(',', '.', $values['subvention']);

                $courier->setData($values);
                $courier->save();
                
                $this->info(__b("Kurier wurde erfolgreich bearbeitet"));
                $this->logger->adminInfo(sprintf("Successfully edited courier %s (%d)",
                            $courier->getName(),
                            $courier->getId()));

                $this->_redirect('/administration_courier/edit/cid/' . $courier->getId());
            }
            else{
                $this->error($form->getMessages());
            }
        }

        // assign contacts
        $contTable = new Yourdelivery_Model_DbTable_Contact();
        $this->view->assign('contacts', $contTable->getDistinctNameId());

        // assign courier
        $this->view->assign('courier', $courier);
    }

    /**
     * Edit dynamic costs
     * @author vpriem
     * @since 17.01.2011
     * @return void
     */
    public function editdynamiccostsAction(){
        // get request
        $request = $this->getRequest();


        if(is_null($request->getParam('cid'))) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            $this->_redirect('/administration/couriers');
        }

        // create courier object
        try {
            $courier = new Yourdelivery_Model_Courier($request->getParam('cid'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            $this->_redirect('/administration/couriers');
        }

        // assign models
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from(array('cc' => 'courier_costmodel'), array(
                    'id',
                    'courierId',
                    __b('Basispreis') => 'startCost',
                    __b('inklKilometer') => 'kmInclusive',
                    __b('Kilometerpreis') => 'kmCost',
                    __b('MwSt') => 'tax',
                    __b('inklMwSt') => 'taxInclusive'
                ))
                ->where("`courierId` = ?", $courier->getId());

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(50);

        $grid->updateColumn('id',             array('hidden' => 1));
        $grid->updateColumn('courierId',      array('hidden' => 1));
        $grid->updateColumn(__b('Basispreis'),     array('callback'=>array('function' => 'intToPrice', 'params' => array('{{' . __b('Basispreis') . '}}'))));
        $grid->updateColumn(__b('Kilometerpreis'), array('callback'=>array('function' => 'intToPrice', 'params' => array('{{' . __b('Kilometerpreis') . '}}'))));
        $grid->updateColumn(__b('MwSt'),           array('callback'=>array('function' => 'intToPrice', 'params' => array('{{' . __b('MwSt') . '}}'))));
        $cols = new Bvb_Grid_Extra_Column();
        $cols->position('right')
            ->name(__b('Optionen'))
            ->decorator(
                '<div>
                    <a href="/administration_courier/deletedynamiccost/id/{{courierId}}/modelId/{{id}}" class="yd-are-you-sure">{' . __b("Löschen") . '}</a><br />
                </div>'
            );
        $grid->addExtraColumns($cols);
        $this->view->grid = $grid->deploy();
        // assign courier
        $this->view->assign('courier', $courier);
    }

    /**
     * Edit plz
     * @author alex
     * @since 17.02.2011
     * @return void
     */
    public function editplzsAction(){

        // get request
        $request = $this->getRequest();

        if(is_null($request->getParam('cid'))) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // create courier object
        try {
            $courier = new Yourdelivery_Model_Courier($request->getParam('cid'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // assign plzs
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());

        $db = Zend_Registry::get('dbAdapter');
        $select = $db
            ->select()
            ->from(array('cp' => 'courier_plz'), array(
                'cp.id',
                'cp.courierId',
                'cp.plz',
                __b('Stadt') => 'c.city',
                __b('Lieferzeit') => 'cp.deliverTime',
                __b('Kosten') => 'cp.delcost',
                __b('Mindestbestellwert') => 'cp.mincost'
            ))
            ->join(array('c' => 'city'), 'cp.cityId = c.id', array())
            ->where("cp.courierId = ?", $courier->getId());
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(50);

        $grid->updateColumn('id',                 array('hidden' => 1));
        $grid->updateColumn('courierId',          array('hidden' => 1));
        $grid->updateColumn(__b('Kosten'),             array('callback'=>array('function' => 'intToPrice', 'params' => array('{{' . __b('Kosten') . '}}'))));
        $grid->updateColumn(__b('Lieferzeit'),         array('decorator' => '{{' . __b('Lieferzeit') . '}} Minuten'));
        $grid->updateColumn(__b('Mindestbestellwert'), array('callback'=>array('function' => 'intToPrice', 'params' => array('{{' . __b('Mindestbestellwert') . '}}'))));
        $cols = new Bvb_Grid_Extra_Column();
        $cols->position('right')
            ->name(__b('Optionen'))
            ->decorator(
                '<div>
                    <a href="/administration_courier/deleterange/id/{{courierId}}/rangeId/{{id}}" class="yd-are-you-sure"><img src="/media/images/yd-backend/del-cat.gif"/></a><br />
                </div>'
            );
        $grid->addExtraColumns($cols);
        
        $this->view->grid = $grid->deploy();
        // assign courier
        $this->view->assign('courier', $courier);
    }
    
    /**
     * Edit courier
     * @author alex
     * @since 17.02.2011
     * @return void
     */
    public function editlocationsAction(){
        // get request
        $request = $this->getRequest();

        if(is_null($request->getParam('cid'))) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // create courier object
        try {
            $courier = new Yourdelivery_Model_Courier($request->getParam('cid'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // assign locations
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from(array('cl' => 'courier_location'), array(
                    'id',
                    'courierId',
                    __b('Kilometer') => 'range',
                    __b('Kosten') => 'cost',
                    __b('Lieferzeit') => 'deliverTime'
                ))
                ->where("`courierId` = ?", $courier->getId())
                ->order('range');

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(50);

        $grid->updateColumn('id',    array('hidden' => 1));
        $grid->updateColumn('courierId',    array('hidden' => 1));
        $grid->updateColumn(__b('Kilometer'),    array('decorator' => '{{' . __b('Kilometer') . '}} Kilometer'));
        $grid->updateColumn(__b('KilometerKosten'),       array('callback'=>array('function' => 'intToPrice', 'params' => array('{{' . __b('Kosten') . '}}'))));
        $grid->updateColumn(__b('Lieferzeit'),   array('decorator' => '{{' . __b('Lieferzeit') . '}} Minuten'));
        $cols = new Bvb_Grid_Extra_Column();
        $cols->position('right')
            ->name(__b('Optionen'))
            ->decorator(
                '<div>
                    <a href="/administration_courier/deletelocation/id/{{courierId}}/locationId/{{id}}" class="yd-are-you-sure">{' . __b("Löschen") . '}</a><br />
                </div>'
            );
        $grid->addExtraColumns($cols);

        $this->view->grid = $grid->deploy();

        // assign courier
        $this->view->assign('courier', $courier);

        // assign grid
        $this->view->assign('gridLocations', $grid);
    }
    
    /**
     * Show courier restaurants association
     * @author vpriem
     * @since 01.08.2010
     * @return void
     */
    public function editrestaurantsAction(){
        // get request
        $request = $this->getRequest();

        if(is_null($request->getParam('cid'))) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // create courier object
        try {
            $courier = new Yourdelivery_Model_Courier($request->getParam('cid'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // assign courier
        $this->view->assign('courier', $courier);

        // assign restaurants
        $db = Zend_Registry::get('dbAdapter');
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());

        $select = $db->select()->from(array('cr' => 'courier_restaurant'), array("restaurantId"))
                ->join(array("r" => "restaurants"), "cr.restaurantId = r.id", array(__b('Restaurant') => "name"))
                ->where("cr.courierId = ?", $courier->getId())
                ->order('r.name');

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(50);

        $grid->updateColumn(__b('Restaurant'), array('decorator' => '<a href="/administration_service_edit/assoc/id/{{restaurantId}}">{{' . Restaurant . '}}</a>'));
        $cols = new Bvb_Grid_Extra_Column();
        $cols->position('right')
            ->name(__b('Optionen'))
            ->decorator(
                '<div>
                    <a href="/administration_courier/deleterestaurant/id/' . $courier->getId() . '/restaurantId/{{restaurantId}}" class="yd-are-you-sure">{' . __b("Löschen") . '}</a><br />
                </div>'
            );
        $grid->addExtraColumns($cols);
        
        $this->view->grid = $grid->deploy();
    }
    
    /**
     * Delete courier
     * @author vpriem
     * @since 01.08.2010
     * @return void
     */
     public function deleteAction() {

        // get request
        $request = $this->getRequest();
        $id           = $request->getParam('cid');

        if(is_null($id)) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // create courier
        try {
            $courier = new Yourdelivery_Model_Courier($id);
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // delete courier
        if ($courier->remove()) {
            $this->success(__b("Der Kurierdienst wurde erfolgreich gelöscht"));
        }
        else {
            $this->error(__b("Der Kurierdienst konnte nicht gelöscht werden"));
        }

        $this->logger->adminInfo(sprintf("Courier #%d was deleted", $id));

        return $this->_redirect('/administration/couriers');
    }

    /**
     * Delete courier
     * @author vpriem
     * @since 01.08.2010
     * @return void
     */
     public function deletedynamiccostAction() {
        // get request
        $request = $this->getRequest();
        $id           = $request->getParam('id');
        $modelId      = $request->getParam('modelId');

        if(is_null($id)) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // create courier
        try {
            $courier = new Yourdelivery_Model_Courier($id);
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // delete courier model
        if ($modelId !== null) {
            if ($courier->removeCostmodel($modelId)) {
                $this->success(__b("Kostenmodell wurde erfolgreich gelöscht"));
                $this->logger->adminInfo(sprintf("Dynamic cost #%d for courier #%d was deleted", $modelId, $courier->getId()));
            }
            else {
                $this->error(__b("Kostenmodell konnte nicht gelöscht werden"));
            }
            return $this->_redirect('/administration_courier/editdynamiccosts/cid/' . $courier->getId());
        }
    }

    /**
     * Delete plz
     * @author vpriem
     * @since 01.08.2010
     * @return void
     */
     public function deleterangeAction() {

        // get request
        $request =  $this->getRequest();
        $id =       $request->getParam('id');
        $rangeId =  $request->getParam('rangeId');

        if(is_null($id)) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // create courier
        try {
            $courier = new Yourdelivery_Model_Courier($id);
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // delete courier plz
        if ($rangeId !== null) {
            if ($courier->removeRange($rangeId)) {
                $this->success(__b("PLZ Zuordnung wurde erfolgreich gelöscht"));
                $this->logger->adminInfo(sprintf("PLZ #%d association with courier #%d was deleted", $rangeId, $courier->getId()));
            }
            else {
                $this->error(__b("PLZ Zuordnung konnte nicht gelöscht werden"));
            }
            return $this->_redirect('/administration_courier/editplzs/cid/' . $courier->getId());
        }
    }

    /**
     * Delete location
     * @author alex
     * @since 17.02.2011
     * @return void
     */
     public function deletelocationAction() {
        // get request
        $request = $this->getRequest();
        $id           = $request->getParam('id');
        $locationId   = $request->getParam('locationId');

        if(is_null($id)) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // create courier
        try {
            $courier = new Yourdelivery_Model_Courier($id);
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // delete courier location
        if ($locationId !== null) {
            if ($courier->removeLocation($locationId)) {
                $this->success(__b("Location wurde erfolgreich gelöscht"));
                $this->logger->adminInfo(sprintf("Location #%d for courier #%d was deleted", $locationId, $courier->getId()));
            }
            else {
                $this->error(__b("Location konnte nicht gelöscht werden"));
            }
            return $this->_redirect('/administration_courier/editlocations/cid/' . $courier->getId());
        }
    }

    /**
     * Delete restaurant association
     * @author alex
     * @since 17.02.2011
     * @return void
     */
     public function deleterestaurantAction() {
        // get request
        $request        = $this->getRequest();
        $id             = $request->getParam('id');
        $restaurantId   = $request->getParam('restaurantId');

        if(is_null($id)) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // create courier
        try {
            $courier = new Yourdelivery_Model_Courier($id);
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
            return $this->_redirect('/administration/couriers');
        }

        // delete courier restaurant
        if ($restaurantId !== null) {
            if (Yourdelivery_Model_Courier_Restaurant::delete($courier->getId(),  $restaurantId)) {
                $this->success(__b("Die Zuordnung zum Dienstleister wurde erfolgreich gelöscht"));
                $this->logger->adminInfo(sprintf("The association of courier %s (#%d) with restaurant #%d was deleted",
                            $courier->getName(),
                            $courier->getId(),
                            $restaurantId));
            }
            else {
                $this->error(__b("Die Zuordnung zum Dienstleister konnte nicht gelöscht werden"));
            }
            return $this->_redirect('/administration_courier/editrestaurants/cid/' . $courier->getId());
        }
    }

    /**
     * Add courier location
     * @author vpriem
     * @since 10.08.2010
     * @return void
     */
    public function addlocationAction(){
        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            // create new courier
            $form = new Yourdelivery_Form_Administration_Courier_Location_Add();
            if ($form->isValid($post)) {
                $courierLocation = new Yourdelivery_Model_Courier_Location();
                $values = $form->getValues();
                $values['cost'] = priceToInt2($values['cost']);
                
                // save
                $courierLocation->setData($values);
                $courierLocation->save();

                $this->logger->adminInfo(sprintf("Added location (range => %s, cost => %s, deliverTime => %s) for courier #%d",
                        $values['range'],
                        $values['cost'],
                        $values['deliverTime'],
                        $values['courierId']));

                return $this->_redirect('/administration_courier/editlocations/cid/' . $values['courierId']);
            }
            else {
                $this->error($form->getMessages());
                return $this->_redirect('/administration_courier/editlocations/cid/' . $post['courierId']);
            }
        }

        // redirect
        return $this->_redirect('/administration/couriers');

    }

    /**
     * Add courier location
     * @author vpriem
     * @since 10.08.2010
     * @return void
     */
    public function addplzAction(){
        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            // create new courier
            $form = new Yourdelivery_Form_Administration_Courier_Plz_Add();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                // create courier
                try {
                    $courier = new Yourdelivery_Model_Courier($values['cid']);
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
                    return $this->_redirect('/administration/couriers');
                }
                
                // add all plz of a city
                if ($values['city']) {
                    $cities = Yourdelivery_Model_City::getByCity($values['city']);
                    foreach ($cities as $city) {
                        if ($courier->addRange($city['id'], $values['deliverTime'], $values['delcost'], $values['mincost'])){
                            $this->logger->adminInfo(sprintf("Added cityId %d to the courier #%d",
                                        $city['id'],
                                        $values['courierId']));
                        }
                        else {
                            $this->logger->adminError(sprintf("cityId %d cannot be added to the courier #%d",
                                        $city['id'],
                                        $values['courierId']));
                        }
                    }
                }
                // save
                else  {
                    try {
                        if ($courier->addRange($values['cityId'], $values['deliverTime'], $values['delcost'], $values['mincost'])){
                            $this->logger->adminInfo(sprintf("cityId %d was added to the courier #%d",
                                        $values['cityId'],
                                        $values['courierId']));
                        }
                        else {
                            $this->logger->adminError(sprintf("cityId %d cannot be added to the courier #%d",
                                        $values['cityId'],
                                        $values['courierId']));
                        }
                    } 
                    catch (Zend_Exception $e) {
                        $this->logger->adminError(sprintf("Error %s on adding cityId %d to the courier #%d",
                                    $e->getMessage(),
                                    $values['cityId'],
                                    $values['courierId']));
                        $this->error($e->getMessage());
                    }
                }
                
                return $this->_redirect('/administration_courier/editplzs/cid/' . $values['cid']);
            }
            else {
                $this->error($form->getMessages());
            }
        }

        // redirect
        return $this->_redirect('/administration/couriers');

    }

    /**
     * Add courier location
     * @author vpriem
     * @since 10.08.2010
     * @return void
     */
    public function addcostAction(){

        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();
            
            // create courier
            try {
                $courier = new Yourdelivery_Model_Courier($values['courierId']);
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("Diesen Kurierdienst gibt es nicht!"));
                return $this->_redirect('/administration/couriers');
            }
            
            $form = new Yourdelivery_Form_Administration_Courier_Costmodel_Add();
            if ($form->isValid($post)) {
                $values = $form->getValues();
                $values['startCost'] = priceToInt2($values['startCost']);
                $values['kmCost'] = priceToInt2($values['kmCost']);
                $values['tax'] = priceToInt2($values['tax']);

                // save
                $table = new Yourdelivery_Model_DbTable_Courier_Costmodel();

                try {
                    $table->createRow($values)->save();
                }
                catch (Zend_Exception $e) {
                    $this->error($e->getMessage());
                    return $this->_redirect('/administration_courier/editdynamiccosts/cid/' . $values['courierId']);
                }

                $this->logger->adminInfo(sprintf("Added dynamic cost (startCost => %s, kmCost => %s, tax => %s) for courier #%d",
                        $values['startCost'],
                        $values['kmCost'],
                        $values['tax'],
                        $values['courierId']));

                return $this->_redirect('/administration_courier/editdynamiccosts/cid/' . $values['courierId']);
            }
            else {
                $this->error($form->getMessages());
            }
        }

        // redirect
        return $this->_redirect('/administration/couriers');

    }
    
    /**
     * @author mlaug
     * @since 07.08.2010
     */
    public function editbillingAction(){
        $request = $this->getRequest();

        //create company object
        try {
            $courier = new Yourdelivery_Model_Courier($request->getParam('cid'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("Dieser Kurier existiert nicht!"));
            return $this->_redirect('/administration_courier');
        }

        if ( $this->getRequest()->isPost() ){
            $data = $this->getRequest()->getPost();
            $form = new Yourdelivery_Form_Administration_Billing_Customized();
            if ( $form->isValid($data) ){
                $cleanData = $form->getValues();
                $courier->getBillingCustomized()->setData($cleanData)->save();
                $this->success(__b("Daten erfolgreich gespeichert"));

                $this->logger->adminInfo(sprintf("Edited billing for courier #%d", $courier->getId()));
            }
            else{
                $this->error($form->getMessage());
            }
        }

        $this->view->customized = $courier->getBillingCustomizedData();
        $this->view->courier = $courier;
    }

}
