<?php

/**
 * @author alex
 */
class Administration_Service_PrinterController extends Default_Controller_AdministrationBase {

    /**
     * Table with all gprs printer
     * @author alex
     * @since 24.05.2011
     */
    public function indexAction() {

        // build select
        $db = Zend_Registry::get('dbAdapter');

        if (substr($this->config->domain->base, -3) == '.pl') {
            // Pyszne.pl version
            $fields = array(
                __b('ID') => 'p.id',
                __b('Typ') => 'p.type',
                __b('Restaurants') => 'p.id',
                __b('Seriennummer') => 'p.serialNumber',
                __b('SIM Nummer') => 'p.simNumber',
                __b('Firmware') => 'p.firmware',
                'p.signal',
                'p.paperout',
                __b('IsOnline') => new Zend_Db_Expr("(((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(p.updated)) < 360) AND p.online > 0)")
            );
            $restaurantsCallback = 'restaurantsForPrinterWithOpenings';
        } else {
            // Global Version
            $fields = array(
                __b('ID') => 'p.id',
                __b('Typ') => 'p.type',
                __b('Seriennummer') => 'p.serialNumber',
                __b('SIM Nummer') => 'p.simNumber',
                __b('SIM PIN1') => 'p.simPin1',
                __b('SIM PIN2') => 'p.simPin2',
                __b('SIM PUK1') => 'p.simPuk1',
                __b('SIM PUK2') => 'p.simPuk2',
                __b('Firmware') => 'p.firmware',
                __b('Upgrade') => 'p.upgrade',
                __b('StateName') => 'ps.state',
                __b('State') => 'ps.id',
                'p.signal',
                'p.paperout',
                __b('IsOnline') => new Zend_Db_Expr("(((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(p.updated)) < 360) AND p.online > 0)"),
                __b('Restaurants') => 'p.id'
            );
            $restaurantsCallback = 'restaurantsForPrinter';
        }
        $select = $db
                ->select()
                ->from(array('p' => 'printer_topup'), $fields)
                ->joinLeft(array('ps' => 'printer_states'), 'ps.id = p.stateId', array())
                ->order('p.created DESC');

        // build grid
        $grid = Default_Helper::getTableGrid('printer');
        $grid->setExport(array());
        $grid->setPagination(20);

        // update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('StateName'), array('hidden' => 1));
        $grid->updateColumn(__b('State'), array('searchType' => 'equal', 'callback' => array('function' => 'getPrinterStatesHistory', 'params' => array('{{' . __b('ID') . '}}', '{{' . __b('StateName') . '}}'))));
        $grid->updateColumn(__b('IsOnline'), array('callback' => array('function' => 'printerIsOnline', 'params' => array('{{' . __b('IsOnline') . '}}'))));
        $grid->updateColumn(__b('Restaurants'), array('callback' => array('function' => $restaurantsCallback, 'params' => array('{{' . __b('ID') . '}}'))));
        $grid->updateColumn('signal', array('title' => __b('Signal'), 'callback' => array('function' => 'printerSignal', 'params' => array('{{signal}}'))));
        $grid->updateColumn(__b('Upgrade'), array('decorator' => '<div id="yd-printer-upgrade-{{ID}}">{{Upgrade}}</div>'));

        $grid->updateColumn('paperout', array('title' => __b('Papier'), 'callback' => array(
                'function' => function ($paperout) {
                    if ($paperout == 1) {
                        return '<font color="#FF0000"><b>' . __b('Kein') . '<b></font>';
                    }
                    return '<font color="#008000">' . __b('Ok') . '</font>';
                },
                'params' => array('{{paperout}}')
                )));

        $printerStates = array('' => __b('Alle'));
        foreach (Yourdelivery_Model_DbTable_Printer_Topup::getStates() as $s) {
            $printerStates[$s['id']] = $s['state'];
        }
                
        // add filters
        $filters = new Bvb_Grid_Filters();

        $filters->addFilter(__b('ID'))
                ->addFilter(__b('Typ'))
                ->addFilter(__b('Seriennummer'))
                ->addFilter(__b('SIM Nummer'))
                ->addFilter(__b('Firmware'))
                ->addFilter(__b('State'), array('values' => $printerStates));

        $grid->addFilters($filters);

        // option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')
                ->name(__b('Optionen'))
                ->decorator(
                        '<div>
                    <a href="/administration_service_printer/edit/id/{{' . __b('ID') . '}}">' . __b("Editieren") . '</a><br/>
                    <a href="/administration_service_printer/delete/id/{{' . __b('ID') . '}}" class="yd-are-you-sure">' . __b("Löschen") . '</a>
                </div>'
        );
        
        $optionCheckbox = new Bvb_Grid_Extra_Column();
        $optionCheckbox->position('left')->name('')->callback(array('function' => 'idCheckbox', 'params' => array('{{ID}}')));
        
        $grid->addExtraColumns($option, $optionCheckbox);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * Create new printer
     * @author alex
     * @since 18.10.2011
     */
    public function createAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration_service_printer');
        }

        if ($request->isPost()) {
            $post = $request->getPost();

            if (!is_numeric($post['id'])) {
                $this->error(__b("Nur Ziffern sind als id erlaubt!"));
                $this->_redirect('/administration_service_printer/create');                
            }
                
            try {
                $printer = Yourdelivery_Model_Printer_Abstract::factory($post['id']);
                $this->error(__b("Printer #%s already exists", $post['id']));
                $this->_redirect('/administration_service_printer/create');
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }

            // validate the form (initialized for Poland in soft mode - PIN/PUK fields not required)
            $softMode = (substr($this->config->domain->base, -3) == '.pl');
            $form = new Yourdelivery_Form_Administration_Service_PrinterEdit($softMode);
            if ($form->isValid($post)) {
                $values = $form->getValues();

                // use db table cause we use the id
                $printerTable = new Yourdelivery_Model_DbTable_Printer_Topup();
                $printerTable->createRow($values)
                             ->save();
                
                try {
                    $printer = Yourdelivery_Model_Printer_Abstract::factory($values['id']);
                } 
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__b("Printer #%s cannot be created", $post['id']));
                    $this->_redirect('/administration_service_printer');
                }

                // save the initial state in state history table
                try {
                    $printerStatesHistoryTable = new Yourdelivery_Model_DbTable_Printer_StatesHistory();
                    $printerStatesHistoryTable
                            ->createRow(
                                    array(
                                        'printerId' => $printer->getId(),
                                        'stateId' => $values['stateId']
                                        )
                                    )
                            ->save();
                } 
                catch (Exception $e) {
                    $this->error(__b("Status konnte nicht historisiert werden"));
                }
                
                $restaurants = $post['restaurantId'];
                if (is_array($restaurants)) {
                    foreach ($restaurants as $rid) {
                        $printer->addRestaurant($rid);
                        $this->logger->adminInfo(sprintf("GPRS printer association of printer #%d with restaurant #%d was created", $printer->getId(), $rid));
                    }
                }

                $this->logger->adminInfo(sprintf("Printer #%d was created", $printer->getId()));
                $this->success(__b("Printer was created"));
                $this->_redirect('/administration_service_printer/edit/id/' . $printer->getId());
            } else {
                $this->error($form->getMessages());
            }
        }

        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());
    }

    /**
     * Edit GPRS printer
     * @author alex
     * @since 24.05.2011
     */
    public function editAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration_service_printer');
        }

        $id = $request->getParam('id');
        if ($id === null) {
            $this->error(__b("This printer does not exist!"));
            $this->_redirect('/administration_service_printer');
        }

        //create rating object
        try {
            $printer = Yourdelivery_Model_Printer_Abstract::factory($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("This printer does not exist!"));
            $this->_redirect('/administration_service_printer');
        }

        if ($request->isPost()) {
            // validate the form (initialized for Poland in soft mode - PIN/PUK fields not required)
            $softMode = (substr($this->config->domain->base, -3) == '.pl');
            $form = new Yourdelivery_Form_Administration_Service_PrinterEdit($softMode);
            $post = $request->getPost();

            // validate the form
            if ($form->isValid($post)) {
                $values = $form->getValues();

                $oldStateId = $printer->getStateId();
                
                $printer->setData($values);
                $printer->save();

                $restaurants = $post['restaurantId'];
                if (is_array($restaurants)) {
                    foreach ($restaurants as $rid) {
                        try {
                            $printer->addRestaurant($rid);
                            $this->logger->adminInfo(sprintf("GPRS printer association of printer #%d with restaurant #%d was created", $printer->getId(), $rid));
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            continue;
                        }
                    }
                }

                // if state changed, save the new state in state history table
                if ($oldStateId != $values['stateId']) {
                    try {
                        $printerStatesHistoryTable = new Yourdelivery_Model_DbTable_Printer_StatesHistory();
                        $printerStatesHistoryTable
                                ->createRow(
                                        array(
                                            'printerId' => $printer->getId(),
                                            'stateId' => $values['stateId']
                                            )
                                        )
                                ->save();
                    } 
                    catch (Exception $e) {
                        $this->error(__b("Status konnte nicht historisiert werden"));
                    }                   
                }
                
                $this->logger->adminInfo(sprintf("GPRS printer #%d was edited", $printer->getId()));
                $this->success(__b("Printer was edited!"));
                $this->_redirect('/administration_service_printer/edit/id/' . $printer->getId());
            } else {
                $this->error($form->getMessages());
                $this->_redirect('/administration_service_printer/edit/id/' . $printer->getId());
            }
        }

        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameIdForPrinter($printer->getId()));

        $this->view->assign('printer', $printer);
    }

    /**
     * delete GPRS printer
     * @since 24.05.2011
     * @author alex
     */
    public function deleteAction() {

        $request = $this->getRequest();
        $id = $request->getParam('id');

        try {
            $printer = Yourdelivery_Model_Printer_Abstract::factory($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("This printer does not exist!"));
            $this->_redirect('/administration_service_printer');
        }

        $printer->getTable()
                ->getCurrent()
                ->delete();

        $this->logger->adminInfo(sprintf("GPRS printer #%d was deleted", $printer->getId()));
        $this->success(__b("Printer was deleted!"));
        $this->_redirect('/administration_service_printer');
    }

    /**
     * delete association of gprs printer with restaurant
     * @since 24.05.2011
     * @author alex
     */
    public function deleteassocAction() {

        $request = $this->getRequest();
        $id = $request->getParam('id');

        try {
            $assoc = new Yourdelivery_Model_Servicetype_Printer($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("This printer does not exist!"));
            $this->_redirect('/administration_service_printer');
        }

        $assoc->getTable()
                ->getCurrent()
                ->delete();

        $this->logger->adminInfo(sprintf("Association fo restaurant #%d wiht printer #%d was deleted", $assoc->getRestaurantId(), $assoc->getPrinterId()));
        $this->_redirect('/administration_service_printer/edit/id/' . $assoc->getPrinterId());
    }

    /**
     * Table with restaurants, having printer association
     * @author Alex Vait <vait@lieferando.de>
     * @since 28.03.2012
     */
    public function restaurantsAction() {

        // build select
        $db = Zend_Registry::get('dbAdapter');

        $select = $db
                ->select()
                ->from(array('r' => 'restaurants'), array(
                    __b('Id') => 'r.id',
                    __b('Restaurant') => 'r.name',
                    __b('Versandart') => 'r.notify',
                    __b('StateName') => 'ps.state',
                    __b('State') => 'ps.id'
                ))
                ->join(array('rp' => 'restaurant_printer_topup'), 'rp.restaurantId = r.id', array())
                ->join(array('p' => 'printer_topup'), 'rp.printerId = p.id', array(
                    __b('PrinterId') => 'p.id',
                    __b('PrinterOnline') => new Zend_Db_Expr("(((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(p.updated)) < 360) AND p.online > 0)"),
                    __b('Signal') => 'p.signal',
                ))
                ->joinLeft(array('ps' => 'printer_states'), 'ps.id = p.stateId', array())
                ->order('r.id');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(20);

        // update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $grid->updateColumn(__b('PrinterOnline'), array('callback' => array('function' => 'printerIsOnline', 'params' => array('{{' . __b('PrinterOnline') . '}}'))));
        $grid->updateColumn(__b('Signal'), array('callback' => array('function' => 'printerSignal', 'params' => array('{{' . __b('Signal') . '}}'))));
        $grid->updateColumn(__b('PrinterId'), array('decorator' => '<a href="/administration_service_printer/edit/id/{{' . __b('PrinterId') . '}}">{{' . __b('PrinterId') . '}}</a>'));
        $grid->updateColumn(__b('Versandart'), array('searchType' => 'equal','callback' => array('function' => 'translateNotificationKind', 'params' => array('{{' . __b('Versandart') . '}}'))));
        $grid->updateColumn(__b('Restaurant'), array('decorator' => '<a href="/administration_service_edit/index/id/{{' . __b('Id') . '}}">{{' . __b('Restaurant') . '}}</a>'));
        $grid->updateColumn(__b('StateName'), array('hidden' => 1));
        $grid->updateColumn(__b('State'), array('searchType' => 'equal', 'callback' => array('function' => 'getPrinterStatesHistory', 'params' => array('{{' . __b('PrinterId') . '}}', '{{' . __b('StateName') . '}}'))));

        $isOnline = array(
            '1' => __b('online'),
            '0' => __b('offline'),
            '' => __b('Alle')
        );

        $printerStates = array('' => __b('Alle'));
        foreach (Yourdelivery_Model_DbTable_Printer_Topup::getStates() as $s) {
            $printerStates[$s['id']] = $s['state'];
        }
        
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('Id')
                ->addFilter(__b('Versandart'), array('values' => Yourdelivery_Model_Servicetype_Abstract::getNotificationKinds()))
                ->addFilter(__b('PrinterOnline'), array('values' => $isOnline))
                ->addFilter(__b('PrinterId'))
                ->addFilter(__b('Restaurant'))
                ->addFilter(__b('State'), array('values' => $printerStates));

        $grid->addFilters($filters);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

}