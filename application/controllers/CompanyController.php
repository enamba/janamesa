<?php

/**
 * Company Controller
 * @author Jan Oliver Oelerich
 * @copyright Yourdelivery
 */
class CompanyController extends Default_Controller_Base {

    /**
     * Check if customer has the rights to access one company, and set the
     * company session
     *
     * + additional javascript for company backend
     */
    public function preDispatch() {
        parent::preDispatch();

        if (!$this->getCustomer()->isEmployee()) {
            $this->error(__('Sie sind nicht in einer Firma angestellt'));
            $this->_redirect('/order/start');
        }

        if (!$this->getCustomer()->isCompanyAdmin()) {
            $this->error(__('Sie haben auf den Admin Bereich keinen Zugriff'));
            $this->_redirect('/order/start');
        }

        $this->session->company = $this->view->company = $this->getCustomer()->getCompany();

    }

    /**
     *  overview
     */
    public function indexAction() {
        $this->view->assign('comp_nav_index', 'current');
    }

    /**
     * List of all employees + delete function
     */
    public function employeesAction() {

        $this->view->assign('comp_nav_employees_top', 'current');
        $this->view->assign('comp_nav_employees', 'current');

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->from(array('c' => 'customers'), array(
                    'ID' => 'id',
                    'Name' => new Zend_Db_Expr('CONCAT (c.prename, " ", c.name)'),
                    'eMail' => 'email'
                ))
                ->joinLeft(array('cc' => 'customer_company'), 'cc.customerId = c.id', array('RID' => 'cc.id'))
                ->where('cc.companyId = ?', $this->session->company->getId())
                ->where('c.deleted = 0')
                ->order('c.id DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID', array('hidden' => 1));
        $grid->updateColumn('RID', array('hidden' => 1));
        $grid->updateColumn('Name', array('title' => __('Name'), 'decorator' => '<a href="/company/employee/id/{{ID}}" alt="' . __("Bearbeiten") . '">{{Name}}</a>'));
        $grid->updateColumn('eMail', array('title' => __('eMail'), 'decorator' => '<a href="mailto:{{eMail}}">{{eMail}}</a>'));

        // option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')
                ->name(__('Options'))
                ->decorator(
                    '<a href="/company/employee/id/{{ID}}">
                        <img src="/media/images/yd-icons/pencil.png" alt="' . __("Bearbeiten") . '" />
                    </a>
                    <a href="/company/employees/del/{{ID}}" onclick="javascript:return confirm(\"' . __('Vorsicht!! Soll dieser Mitarbeiter wirklich gelöscht werden?') . '\")">
                        <img src="/media/images/yd-icons/cross.png" alt="' . __("Zuordnung löschen") . '" />
                    </a>'
        );
        $grid->addExtraColumns($option);

        // deploy grid to view
        $this->view->grid = $grid->deploy();

        $id = $this->getRequest()->getParam('del', null);
        if (!is_null($id)) {
            $relationTable = new Yourdelivery_Model_DbTable_Customer_Company();
            $rightsTable = new Yourdelivery_Model_DbTable_UserRights();

            try {
                $customer = new Yourdelivery_Model_Customer($id);

                if (!$customer->isEmployee() 
                        || !is_object($this->session->company) 
                        || is_null($customer->getCompany()) 
                        || $this->session->company->getId() != $customer->getCompany()->getId()) {
                    throw new Yourdelivery_Exception_Database_Inconsistency();
                } else {
                    $relationTable->delete(sprintf(
                                    'companyId = %d AND customerId = %d', $this->session->company->getId(), $id
                            ));
                    $rightsTable->delete(sprintf(
                                    'refId = %d AND kind = "c" AND customerId = %d', $this->session->company->getId(), $id
                            ));
                    $this->success(__('Account erfolgreich gelöscht!'));
                    $this->_redirect('/company/employees');
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__('Benutzer konnte nicht gefunden werden'));
            }
        }
    }

    /**
     * Login to a user account + redirection
     * @author mlaug
     * @since 25.11.2010
     */
    public function employeeloginAction() {

        if (!$this->getCustomer()->isCompanyAdmin()) {
            $this->_redirect('/');
        }

        $custId = $this->getRequest()->getParam('id', null);
        if (!is_null($custId)) {
            try {
                $viewCustomer = $this->getCustomer();
                $customer = new Yourdelivery_Model_Customer($custId);

                if (!$customer->isEmployee() || ($customer->getCompany()->getId() != $viewCustomer->getCompany()->getId())) {
                    $this->error(__('Login fehlgeschlagen'));
                    $this->_redirect('/company/employees');
                }

                $this->session->unsetAll();
                $this->session->viewCustomerId = $viewCustomer->getId();
                $this->session->customerId = $custId;
                $this->_redirect($viewCustomer->getStartUrl());
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__('Login fehlgeschlagen'));
                $this->_redirect('/company/employees');
            }
        }
        $this->error(__('Keinen Benutzer angegeben'));
        $this->_redirect('/company/employees');
    }

    /**
     * List of all budgets + delete function
     */
    public function budgetsAction() {

        $this->view->assign('comp_nav_employees_top', 'current');
        $this->view->assign('comp_nav_budgets', 'current');

        /**
         * do not allow budgets if agb are not accepted
         */
        if (!$this->session->company->isAgb()) {
            $this->error(__('Sie dürfen keine Budgets verwalten, bis sie die AGB akzeptiert haben'));
            $this->_redirect('/company/');
        }

        // delete budget
        $id = $this->getRequest()->getParam('del', null);
        if (!is_null($id)) {
            $budget = null;
            try {
                $budget = new Yourdelivery_Model_Budget($id);
                if ($budget->getCompany()->getId() != $this->session->company->getId()) {
                    throw new Yourdelivery_Exception_Database_Inconsistency();
                }

                if ($budget->delete()) {
                    $this->success(__('Gruppe erfolgreich gelöscht! Alle Mitarbeiter, die vorher dieser Gruppe zugeordnet waren, sind jetzt keiner Gruppe zugeordnet.'));
                } else {
                    $this->error(__('Konnte Gruppe nicht löschen'));
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__('Konnte Gruppe nicht löschen'));
            }
            $this->_redirect('/company/budgets');
        }

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $selects = $db
                ->select()
                ->from(array('cb' => 'company_budgets'), array(
                    'ID' => 'cb.id',
                    'Gruppenname' => 'cb.name',
                    'Mitglieder' => 'count(cc.id)',
                ))
                ->joinLeft(array('cc' => 'customer_company'), 'cb.id = cc.budgetId AND cc.companyId = cb.companyId', array())
                ->where('cb.companyId = ?', $this->session->company->getId())
                ->order('COUNT(cc.id) DESC')
                ->group('cb.id');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($selects));
        $grid->updateColumn('ID', array('hidden' => 1));
        $grid->updateColumn('Gruppenname', array('title' => __('Gruppenname'), 'callback' => array('function' => 'budgetUseElma', 'params' => array('{{ID}}'))));
        $grid->updateColumn('Mitglieder', array('title' => __('Mitglieder')));

        // add extra rows
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')
                ->name(__('Option'))
                ->decorator(
                    '<a href="/company/budget/id/{{ID}}">
                        <img src="/media/images/yd-icons/pencil.png" alt="' . __('Bearbeiten') . '" />
                    </a>
                    <a href="/company/budgets/del/{{ID}}" onclick="javascript:return confirm(\"' . __('Soll diese Gruppe wirklich gelöscht werden? Die zugeordneten Mitarbeiter werden danach keiner Gruppe zugeordnet sein.') . '\")">
                        <img src="/media/images/yd-icons/cross.png" alt="' . __('Löschen') . '" />
                    </a>'
        );
        $grid->addExtraColumns($option);

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter(__('Gruppenname'))
                ->addFilter(__('Mitglieder'));
        $grid->addFilters($filters);

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * Settings of one budget
     * If no valid id is given, an empty model is created and eventually
     * saved after submitting the form.
     */
    public function budgetAction() {

        $this->view->assign('comp_nav_employees_top', 'current');
        $this->view->assign('comp_nav_budgets', 'current');

        /**
         * do not allow budgets if agb are not accepted
         */
        if (!$this->session->company->isAgb()) {
            $this->error(__('Sie dürfen keine Budgets verwalten, bis sie die AGB akzeptiert haben'));
            $this->_redirect('/company/');
        }

        $id = $this->getRequest()->getParam('id', null);
        $budget = null;

        try {
            $budget = new Yourdelivery_Model_Budget($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__('Diese Gruppe existiert nicht'));
            $this->_redirect('/company/budgets');
        }

        //this budget is not assigned to this company
        if (!$this->getRequest()->isPost() && !is_null($id)) {
            if ($budget->getCompanyId() != $this->session->company->getId()) {
                $this->error(__('Diese Gruppe existiert nicht'));
                $this->_redirect('/company/budgets');
            }
        }

        // edit budget settings
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if (!isset($post['name']) || empty($post['name'])) {
                $this->error(__('Bitte geben Sie einen Namen für die Gruppe an!'));
            } else {

                $budget->setName($post['name']);
                $budget->setCompany($this->session->company);
                $budget->save();

                $company = $budget->getCompany();
                $comapnyId = null;

                $origLocations = null;
                $origEmployees = null;

                if (is_object($company)) {
                    $companyId = $budget->getCompany()->getId();
                    $origLocations = $company->getLocations();
                    $origEmployees = $company->getEmployees();
                }
                // addresses
                // get all company addresses
                // add selected addresses
                $newLocation = array();
                foreach ($post['addr'] as $key => $addr) {
                    $budget->addLocation($key);
                    $newLocation[] = $key;
                }
                // remove not selected addresses
                foreach ($origLocations as $origLocation) {
                    if (!in_array($origLocation->getId(), $newLocation)) {
                        $budget->removeLocation($origLocation->getId());
                    }
                }
                // employees
                // get all company employees

                $newEmpl = array();
                // add selected members
                foreach ($post['empl'] as $empl) {
                    if ($empl != '') {
                        $budget->addMember($empl);
                        $newEmpl[] = $empl;
                    }
                }
                // remove not selected members
                foreach ($origEmployees as $origEmpl) {
                    if (!in_array($origEmpl->getId(), $newEmpl)) {
                        $budget->removeMember($origEmpl->getId());
                    }
                }
                // budgetTimes
                // remove all budget times
                $budget->removeBudgetTimesAll();
                // add new budget times
                foreach ($post['new'] as $day => $values) {
                    foreach ($post['new'][$day] as $vals) {
                        if ($vals['amount'] > 0) {
                            $budget->addBudgetTime($day, $vals['from'], $vals['until'], priceToInt($vals['amount']));
                        }
                    }
                }

                $this->success(__('Die Gruppe wurde erfolgreich bearbeitet!'));
                $this->_redirect('/company/budget/id/' . $budget->getId());
            }
        }

        $this->view->btimes = $budget->getBudgetTimes();
        $this->view->budget = $budget;
        $this->view->days = $days = array(
            __('Sonntag'),
            __('Montag'),
            __('Dienstag'),
            __('Mittwoch'),
            __('Donnerstag'),
            __('Freitag'),
            __('Samstag')
        );
    }

    /**
     * add new employee by calling add function of Customer_Company model
     */
    public function addemployeeAction() {

        $this->view->assign('comp_nav_employees_top', 'current');
        $this->view->assign('comp_nav_employees', 'current');

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            $form = new Yourdelivery_Form_Company_AddEmployee();

            if ($form->isValid($post)) {
                $values = $form->getValues();

                //add costcenter
                if (!empty($values['costcenter'])) {
                    $costcenter = null;
                    try {
                        $costcenter = new Yourdelivery_Model_Department($values['costcenter']);
                        $values['costcenterId'] = $costcenter->getId();
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $values['costcenterId'] = null;
                    }
                }

                $customer = Yourdelivery_Model_Customer_Company::add(
                                $values, $this->session->company->getId(), intval($values['notify']) > 0
                );

                if (is_object($customer)) {

                    if ($values['admin']) {
                        $customer->makeAdmin($this->session->company);
                    }

                    return $this->_redirect('/company/employees');
                } else {
                    $this->error(__('Der Mitarbeiter konnte leider nicht angelegt werden'));
                }
            } else {
                $this->error($form->getMessages());
            }
        }
    }

    /**
     * Edit and show all the data of one employee
     */
    public function employeeAction() {

        $this->view->assign('comp_nav_employees_top', 'current');
        $this->view->assign('comp_nav_employees', 'current');

        if (is_null($this->getRequest()->getParam('id'))) {
            $this->_redirect('/company/addemployee');
        }

        $customer = new Yourdelivery_Model_Customer($this->getRequest()->getParam('id'));

        if (!$customer->isEmployee() || $customer->getCompany()->getId() != $this->session->company->getId()) {
            $this->error(__('Dieser Nutzer gehört nicht in diese Firma!'));
            $this->_redirect('/company');
        }


        $employee = new Yourdelivery_Model_Customer_Company(
                        $this->getRequest()->getParam('id'),
                        $this->session->company->getId()
        );

        if (is_null($employee)) {
            $this->error(__('Dieser Nutzer gehört nicht in diese Firma!'));
            $this->_redirect('/company');
        }

        // maybe edit data?
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            $email = null;
            if ($post['email'] == $employee->getEmail()) {
                unset($post['email']);
            } else {
                $email = $employee->getEmail();
            }

            $form = new Yourdelivery_Form_Company_EditEmployee();

            if ($form->isValid($post)) {
                $values = $form->getValues();
                
                //print_r($values); die();
                
                //add costcenter
                if (!empty($values['costcenter'])) {
                    $costcenter = null;
                    try {
                        $costcenter = new Yourdelivery_Model_Department($values['costcenter']);
                        $values['costcenterId'] = $costcenter->getId();
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $values['costcenterId'] = null;
                    }
                }

                if (isset($values['newpass']) && !empty($values['newpass'])) {
                    $values['password'] = md5($values['newpass']);
                }

                $allows = array(
                    'allowCater' => 'cater',
                    'allowGreat' => 'great',
                    'allowAlcohol' => 'alcohol',
                    'allowTabaco' => 'tabaco'
                );

                foreach ($allows AS $key => $field) {
                    if (isset($values[$key]) && $values[$key] == 1) {
                        $values[$field] = "1";
                    } else {
                        $values[$field] = "0";
                    }
                    unset($values[$key]);
                }
               // little hack to avoid empty values
                if(empty($values['tel'])) {
                    $values['tel'] = " ";
                }                                
                $employee->update($values, intval($values['notify']) > 0);

                if ($post['admin'] == 0) {
                    $employee->removeAdmin($this->session->company);
                }
                if ($post['admin'] == 1) {
                    $employee->makeAdmin($this->session->company);
                }

                $this->_redirect('/company/employee/id/' . $employee->getId());
            } else {
                $this->error($form->getMessages());
            }
        }

        $this->view->employee = $employee;
    }

    /**
     * Show a list of all addresses of this company
     */
    public function addressesAction() {
        $this->view->assign('comp_nav_employees_top', 'current');
        $this->view->assign('comp_nav_addresses', 'current');

        // delete one?
        $id = $this->getRequest()->getParam('del');
        if (!is_null($id)) {
            $table = new Yourdelivery_Model_DbTable_Company_Locations();
            try {
                $location = new Yourdelivery_Model_Location($id);

                if ($location->getCompanyId() != $this->session->company->getId()) {
                    throw new Yourdelivery_Exception_Database_Inconsistency();
                }

                $location->remove();

                $this->success(__('Adresse erfolgreich gelöscht!'));
                $this->_redirect('/company/addresses');
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__('Konnte Adresse nicht löschen'));
            }
        }
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        $selects = $db->select()->from(array('ca' => 'locations'), array(
                    'ID' => 'ca.id',
                    'Straße, Hausnr' => new Zend_Db_Expr('CONCAT (ca.street," ",ca.hausnr)'),
                    'Plz, Ort' => new Zend_Db_Expr('CONCAT(ci.plz, " ", ci.city)'),
                    'Gruppen' => 'ca.id'
                ))
                ->joinLeft(array('ci' => 'city'), 'ci.id=ca.cityId', array())
                ->where('ca.companyId =' . $this->session->company->getId() . ' AND deleted = 0')
                ->order('ca.id desc');

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($selects));
        $grid->updateColumn('ID', array('hidden' => 1));
        $grid->updateColumn('Straße, Hausnr', array('title' => __('Straße, Hausnr'), 'decorator' => '<a href="/company/address/id/{{ID}}">{{Straße, Hausnr}}</a>'));
        $getGruppen = $this->getCustomer()->getCompany()->getBudgets();
        $grid->updateColumn('Plz, Ort', array('title' => __('Plz, Ort')));
        $grid->updateColumn('Gruppen', array('title' => __('Gruppen'), 'callback' => array('function' => 'Yourdelivery_Helpers_Grid::getBudgetNamesFromLocation', 'params' => array('{{ID}}'))));
        
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')
               ->name(__('Option'))
               ->decorator(
                '<a href="/company/address/id/{{ID}}" title="' . __('Bearbeiten') . '">
                    <img src="/media/images/yd-icons/pencil.png" alt="' . __('Bearbeiten') . '" />
                </a>
                <a href="/company/addresses/del/{{ID}}" onclick="javascript:return confirm(' . __('Soll diese Adresse wirklich gelöscht werden?') . ')" title="' . __('Adresse löschen') . '">
                    <img src="/media/images/yd-icons/cross.png" alt="' . __('Adresse löschen') . '" />
                </a>'
        );

        $grid->addExtraColumns($option);
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter(__('Straße, Hausnr'))
                ->addFilter(__('Plz, Ort'));
        $grid->addFilters($filters);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * Show information and edit data of one address or add a new one
     */
    public function addressAction() {

        $this->view->assign('comp_nav_employees_top', 'current');
        $this->view->assign('comp_nav_addresses', 'current');

        $request = $this->getRequest();
        $locationId = $request->getParam('id', null);
        try {
            $location = new Yourdelivery_Model_Location($locationId);
            if (!is_null($locationId) && !empty($locationId)) {
                if ($location->getCompany()->getId() != $this->session->company->getId()) {
                    throw new Yourdelivery_Exception_Database_Inconsistency();
                }
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__('Adresse konnte nicht gefunden werden'));
            $this->_redirect('/company/addresses');
        }

        if ($request->isPost()) {
            $post = $request->getPost();
            $this->view->p = $post;
            $addrForm = new Yourdelivery_Form_Company_Address();

            if ($addrForm->isValid($post)) {
                if ($addrForm->getValue('cityId') == $location->getCityId() && $addrForm->getValue('plz') != $location->getPlz()) {
                    $this->error(__('Plz ungültig!'));
                    $this->_redirect('/company/address/id/' . $locationId);
                }
                $location->setData($addrForm->getValues());
                $location->setPlz();
                $location->setCompanyName($this->session->company->getName());
                $location->setCompany($this->session->company);
                $location->save();

                // set relation to budgets
                $origBudgets = $location->getBudgets();
                $budgets = array();
                foreach ($addrForm->getValue('budgets') as $budgetId => $budg) {
                    try {
                        $budget = new Yourdelivery_Model_Budget($budgetId);
                        $budget->addLocation($location->getId());
                        $budgets[] = $budget->getId();
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->_logger->err(sprintf('Could not create budget #%d', $budgetId));
                        continue;
                    }
                }
                // remove not selected budgets
                foreach ($origBudgets as $origBudget) {
                    if (!in_array($origBudget->getId(), $budgets)) {
                        try {
                            $budget = new Yourdelivery_Model_Budget($origBudget->getId());
                            $budget->removeLocation($location->getId());
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->_logger->err(sprintf('Could not create budget #%d', $budgetId));
                            continue;
                        }
                    }
                }

                //if no id is given, we create a new adresse
                //create also a privat adress
                if (is_null($locationId) || empty($locationId)) {
                    $paddr = new Yourdelivery_Model_Location();
                    $paddr->setData($addrForm->getValues());
                    $paddr->setCustomer($this->getCustomer());
                    $paddr->setCompanyName($this->getCustomer()->getCompany()->getName());
                    $paddr->save();
                    $this->success(__('Adresse erfolgreich erstellt!'));
                } else {
                    $this->success(__('Adresse erfolgreich bearbeitet!'));
                }
                $this->_redirect('/company/addresses');
            } else {
                $this->error($addrForm->getMessages());
            }
        }

        $this->view->address = $location;
    }

    /**
     * edit settings of the company
     */
    public function settingsAction() {

        $this->view->assign('comp_nav_settings_top', 'current');
        $this->view->assign('comp_nav_settings', 'current');

        $request = $this->getRequest();

        if ($request->isPost()) {
            $this->view->p = $request->getPost();
            $form = new Yourdelivery_Form_Company_Settings();
            if ($form->isValid($request->getPost())) {

                $city = null;
                try {
                    $cityCheck = Yourdelivery_Model_City::getByPlz((integer) $form->getValue('ccontactplz'));
                    $contactPlz = $form->getValue('ccontactplz');
                    if (is_null($cityCheck['id']) && !empty($contactPlz)) {
                        $this->error(__('Diese PLZ existiert nicht'));
                        $this->_redirect('/company/contact');
                    }

                    $city = new Yourdelivery_Model_City($form->getValue('cityId'));
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__('Diese PLZ existiert nicht'));
                    $this->_redirect('/company/contact');
                }

                $val = $form->getValues();

                $val['street'] = $val['cstreet'];
                $val['hausnr'] = $val['chausnr'];
                $val['plz'] = $city->getPlz();
                $val['website'] = $val['cwebsite'];
                $val['steuerNr'] = $val['csteuerNr'];
                $val['industry'] = $val['cindustry'];
                $val['ktoName'] = $val['cktoName'];
                $val['ktoNr'] = $val['cktoNr'];
                $val['ktoBlz'] = $val['cktoBlz'];


                $this->session->company->setData($val);
                $this->session->company->save();
                $this->success(__('Daten erfolgreich gesichert!'));
                $this->_redirect('/company/settings');
            } else {
                $this->error($form->getMessages());
            }
        }
    }

    /**
     * edit contact of company
     */
    public function contactAction() {
        $this->view->assign('comp_nav_settings_top', 'current');
        $this->view->assign('comp_nav_contact', 'current');

        if ($this->getRequest()->isPost()) {
            $contact = new Yourdelivery_Model_Contact($this->getRequest()->getParam('id', null));

            //check if contact is really assinged to company
            $found = false;
            foreach ($contact->getCompanys() as $c) {
                if ($c->id == $this->session->company->getId()) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $this->error(__('Kontaktperson nicht gefunden'));
                $this->_redirect('/company/contacts');
            }

            $post = $this->getRequest()->getPost();
            $form = new Yourdelivery_Form_Company_Contact();
            if ($form->isValid($post)) {

                $val = $form->getValues();

                $city = null;
                try {
                    $cityCheck = Yourdelivery_Model_City::getByPlz((integer) $form->getValue('plz'));
                    $contactPlz = $form->getValue('plz');
                    if (is_null($cityCheck['id']) && !empty($contactPlz)) {
                        $this->error(__('Diese PLZ existiert nicht'));
                        $this->_redirect('/company/contact');
                    }

                    $city = new Yourdelivery_Model_City($form->getValue('cityId'));
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__('Diese PLZ existiert nicht'));
                    $this->_redirect('/company/contact');
                }
                $data = $form->getValues();
                $data['plz'] = $city->getPlz();

                $contact->setData($data);
                $contactId = $contact->save();

                if (is_null($this->getRequest()->getParam('id', null)) || $this->getRequest()->getParam('id', null) == 0) {
                    // there was no contact in company, so we have to create this relation
                    $comp = new Yourdelivery_Model_Company($this->getRequest()->getParam('compId', null));
                    $comp->setContact($contact);
                    $comp->save();
                }
                $this->success(__('Daten erfolgreich gesichert!'));
                $this->_redirect('/company/contact');
            } else {
                $this->view->p = $post;
                $this->error($form->getMessages());
            }
        }
    }

    /**
     * edit billing contact of company
     */
    public function billingcontactAction() {

        $this->view->assign('comp_nav_settings_top', 'current');
        $this->view->assign('comp_nav_billingcontact', 'current');

        $company = $this->session->company;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form = new Yourdelivery_Form_Company_BillingContact();
            if ($form->isValid($data)) {
                $cleanData = $form->getValues();
                $company->getBillingCustomized()
                        ->setData($cleanData)
                        ->save();
                $this->logger->info(sprintf('altered billing data of company #%s by company admin #%s', $company->getId(), $this->getCustomer()->getId()));
                $this->success(__('Daten erfolgreich aktualisiert'));
            } else {
                $this->error($form->getMessage());
            }
        }

        $this->view->customized = $company->getBillingCustomizedData();
        $this->view->company = $company;
    }

    /**
     * Show all billings
     * @author vpriem
     * @since 12.10.2010
     */
    public function billingAction() {

        $this->view->assign('comp_nav_settings_top', 'current');
        $this->view->assign('comp_nav_billing', 'current');

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->from(array('b' => 'billing'), array(
                    'ID' => 'b.id',
                    'Nummer' => 'b.number',
                    'Zeitraum' => new Zend_Db_Expr('CONCAT(DATE_FORMAT(b.from, "%d"), ". - ", DATE_FORMAT(b.until, "%d.%m.%Y"))'),
                    'Status' => 'b.status',
                ))
                ->where('b.refId = ?', $this->session->company->getId())
                ->where('b.status > 0')
                ->where('b.mode = ?','company')
                ->order('b.id DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID', array('hidden' => 1));
        $grid->updateColumn('Nummer', array('title' => __("Nummer")));
        $grid->updateColumn('Zeitraum', array('title' => __("Zeitraum")));
        $grid->updateColumn('Status', array('title' => __("Status"), 'callback' => array('function' => 'translateStatus', 'params' => array('{{Status}}'))));

        // add extra rows
        function gridOptions($id) {
            return '<a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '">' . __("Downloaden") . '</a>';
        }

        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')
               ->name(__('Download'))
               ->callback(array('function' => "gridOptions", 'params' => array('{{ID}}')));
        $grid->addExtraColumns($option);

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters
                ->addFilter(__('Nummer'))
                ->addFilter(__('Zeitraum'))
                ->addFilter(__('Status'));
        $grid->addFilters($filters);

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    public function departmentAction() {

        $request = $this->getRequest();
        $id = $request->getParam('id', null);
        if (is_null($id)) {
            $this->warn(__('Abteilung nicht gefunden'));
            $this->_redirect('/company/departments');
        }

        try {
            $department = new Yourdelivery_Model_Department($id);
            if ($department->getCompany()->getId() != $this->session->company->getId()) {
                throw new Yourdelivery_Exception_Database_Inconsistency('Department not assinged to company');
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->warn(__('Abteilung nicht gefunden'));
            $this->_redirect('/company/departments');
        }

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();
            $form = new Yourdelivery_Form_Company_AddDepartment();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                $department = new Yourdelivery_Model_Department($id);
                $department->setName($values['name']);
                $department->setIdentNr($values['identNr']);
                $department->setBilling($values['billing']);

                //add customer to department
                if (array_key_exists('empl', $post)) {
                    $department->resetEmployees();
                    foreach ($post['empl'] as $empl) {
                        try {
                            $empl = new Yourdelivery_Model_Customer_Company($empl, $this->session->company->getId());
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            continue;
                        }

                        $department->addEmployee($empl);
                    }
                }

                //add costcenter to department
                if (array_key_exists('cost', $post)) {
                    foreach ($post['cost'] as $costcenterId) {
                        try {
                            $costcenter = new Yourdelivery_Model_Department($costcenterId);
                            if ($costcenter->getBilling()) {
                                $costcenter->createLink($department);
                            }
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            continue;
                        }
                    }
                }

                $department->save();
                $this->success(__('Abteilung erfolgreich bearbeitet'));
                $this->_redirect('/company/departments');
            } else {
                $this->error($form->getMessages());
            }
        }

        $costcenters = Yourdelivery_Model_Department::getCostCenters();
        $this->view->assign('costcenters', $costcenters);
        $this->view->assign('department', $department);
    }

    public function addprojectnumbersAction() {

        $this->view->assign('comp_nav_settings_top', 'current');
        $this->view->assign('comp_nav_projectnumbers', 'current');

        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();
            $form = new Yourdelivery_Form_Company_Projectnumber();

            if ($form->isValid($post)) {
                $values = $form->getValues();

                $this->session->company->addProjectNumber(
                        $values['projectnumber'], $values['intern'], $values['comment']
                );
                $this->success(__('Projektnummer erfolgreich erstellt'));
                $this->_redirect('/company/projectnumbers');
            } else {
                $this->error($form->getMessages());
                $this->view->post = $post;
            }
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 23.03.2011
     */
    public function projectAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        if ($this->getCustomer()->isEmployee()) {
            $id = $this->getRequest()->getParam('del', null);
            if (!is_null($id)) {
                $project = null;
                try {
                    $project = new Yourdelivery_Model_Projectnumbers($id);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__('Projectcode konnte nicht gelöscht werden'));
                    $this->_redirect('/company/projectnumbers');
                }

                $project->delete();

                $this->success(__('Projectcode wurde erfolgreich gelöscht'));
                $this->_redirect('/company/projectnumbers');
            }
        }
    }

    public function projectnumbersAction() {

        $this->view->assign('comp_nav_settings_top', 'current');
        $this->view->assign('comp_nav_projectnumbers', 'current');

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $selects = $db
                ->select()
                ->from(array('p' => 'projectnumbers'), array(
                    'ID' => 'p.id',
                    'Projektnummer' => 'p.number',
                    'Kommentar' => 'p.comment',
                ))
                ->joinLeft(array('c' => 'companys'), 'c.id = p.companyId', array())
                ->where('p.companyId = ?', $this->session->company->getId())
                ->where('p.deleted = 0')
                ->order('p.id DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($selects));
        $grid->updateColumn('ID', array('hidden' => 1));
        $grid->updateColumn('Projektnummer', array('title' => _("Projektnummer")));
        $grid->updateColumn('Kommentar', array('title' => _("Kommentar")));

        // add options
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')
               ->name(__('Option'))
               ->decorator(
                    '<a href="/company/project/del/{{ID}}" onclick="javascript:return confirm(\"' . __('Soll dieser Projectcode wirklich gelöscht werden?') . '\")" title="' . __('Projectcode löschen') . '">
                        <img src="/media/images/yd-icons/cross.png" alt="' . __('Projectcode löschen') . '" />
                    </a>'
        );
        $grid->addExtraColumns($option);

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters
                ->addFilter(__('Projektnummer'))
                ->addFilter(__('Kommentar'));
        $grid->addFilters($filters);

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    public function addcostcenterAction() {

        $this->view->assign('comp_nav_settings_top', 'current');
        $this->view->assign('comp_nav_costcenter', 'current');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Company_AddDepartment();
            if ($form->isValid($post)) {
                $values = $form->getValues();
                $department = new Yourdelivery_Model_Department();
                $department->setName($values['name']);
                $department->setIdentNr($values['identNr']);
                $department->setBilling(true);
                $department->setCompany($this->session->company);
                $department->save();

                $this->_redirect('/company/costcenters');
            } else {
                $this->error($form->getMessages());
            }
        }
    }

    public function costcentersAction() {

        $this->view->assign('comp_nav_settings_top', 'current');
        $this->view->assign('comp_nav_costcenter', 'current');

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->from(array('d' => 'department'), array(
                    'ID' => 'd.id',
                    'Name' => 'd.name',
                    'Ident Nummer' => 'd.identNr',
                ))
                ->where('d.companyId = ?', $this->session->company->getId())
                ->where('d.deleted = 0')
                ->order('d.id DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID', array('hidden' => 1));
        $grid->updateColumn('Name', array('title' => __("Name")));
        $grid->updateColumn('Ident Nummer', array('title' => __("Ident Nummer")));

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter(__('Name'))
                ->addFilter(__('Ident Nummer'));
        $grid->addFilters($filters);

        // add header script
        $this->view->headerScript = $grid->getHeaderScript();
        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    public function departmentsAction() {
        $this->_redirect('/company/projectnumbers');
    }

    /**
     * show all orders
     */
    public function ordersAction() {

        $this->view->assign('comp_nav_orders_top', 'current');
        $this->view->assign('comp_nav_orders', 'current');

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->distinct()
                ->from(array('o' => 'orders'), array(
                    'OID' => 'o.id',
                    'Datum' => 'o.time',
                    'Besteller' => new Zend_Db_Expr("CONCAT (cu.prename, ' ', cu.name)"),
                    'Kostenstelle' => 'ocg.costcenterId',
                    'Type' => 'o.mode',
                    'Betrag' => new Zend_Db_Expr('o.total + o.serviceDeliverCost'),
                ))
                ->joinLeft(array('cu' => 'customers'), 'cu.id = o.customerId', array())
                ->joinLeft(array('ocg' => 'order_company_group'), 'o.id = ocg.id', array())
                ->where('o.companyId = ?', $this->session->company->getId())
                ->where('o.kind = "comp"')
                ->where('o.state > 0')
                ->order('o.time DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('OID', array('hidden' => 1));
        $grid->updateColumn('Datum', array('title' => __("Datum")));
        $grid->updateColumn('Besteller', array('title' => __("Besteller")));
        $grid->updateColumn('Betrag', array('title' => __("Betrag"), 'callback' => array('function' => 'intToPrice', 'params' => array('{{Betrag}}')), 'decorator' => '{{Betrag}} ' . __("€")));
        $grid->updateColumn('Type', array('title' => __("Type"), 'callback' => array('function' => 'typeToReadable', 'params' => array('{{Type}}'))));
        $grid->updateColumn('Kostenstelle', array('title' => __("Kostenstelle"), 'callback' => array('function' => 'checkCostcenter', 'params' => array('{{Kostenstelle}}'))));

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters
                ->addFilter(__('Type'))
                ->addFilter(__('Besteller'))
                ->addFilter(__('Datum'))
                ->addFilter(__('Betrag'))
                ->addFilter(__('Kostenstelle'));
        $grid->addFilters($filters);

        // add options
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')
                ->name(__('Options'))
                ->callback(array('function' => 'Yourdelivery_Helpers_Grid::getOrderCouponLink', 'params' => array('{{OID}}')));
        $grid->addExtraColumns($option);

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

}
