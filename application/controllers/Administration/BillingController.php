<?php

/**
 * Billing management
 * @author mlaug
 * @since 01.08.2010, 19.08.2010 (alex)
 */
class Administration_BillingController extends Default_Controller_AdministrationBase {

    public function preDispatch() {
        parent::preDispatch();
        $this->view->assign('navbilling', 'active');
    }

    public function informationAction() {

        $billing = new Yourdelivery_Model_DbTable_Billing();
        $this->view->nextBillingNumber = $billing->getNextBillingNumber();

        $this->view->compAll = Yourdelivery_Model_DbTable_Billing::getBillingsCount('company');
        $this->view->compNotDelivered = Yourdelivery_Model_DbTable_Billing::getBillingsCount('company', 0);
        $this->view->compNotPayed = Yourdelivery_Model_DbTable_Billing::getBillingsCount('company', 1);
        $this->view->compPayed = Yourdelivery_Model_DbTable_Billing::getBillingsCount('company', 2);

        $this->view->serviceAll = Yourdelivery_Model_DbTable_Billing::getBillingsCount('rest');
        $this->view->serviceNotDelivered = Yourdelivery_Model_DbTable_Billing::getBillingsCount('rest', 0);
        $this->view->serviceNotPayed = Yourdelivery_Model_DbTable_Billing::getBillingsCount('rest', 1);
        $this->view->servicePayed = Yourdelivery_Model_DbTable_Billing::getBillingsCount('rest', 2);

        $companyTable = new Yourdelivery_Model_DbTable_Company;
        $restaurantTable = new Yourdelivery_Model_DbTable_Restaurant;
        $courierTable = new Yourdelivery_Model_DbTable_Courier();
        $billingTable = new Yourdelivery_Model_DbTable_Billing();

        $restbillingData = array();
        $compbillingData = array();

        $companyData = $companyTable->getDistinctNameId();
        $restaurantData = $restaurantTable->getDistinctNameId();
        $courierData = $courierTable->getDistinctNameId();

        $this->view->assign('compTable', $companyData);
        $this->view->assign('restTable', $restaurantData);
        $this->view->assign('courierTable', $courierData);

        $zip = new ZipArchive();
        $filename = APPLICATION_PATH . '/../storage/' . time() . '.zip';

        $billStorage = APPLICATION_PATH . '/../storage/billing/';

        $request = $this->getRequest();

        //post request
        if ($request->isPost()) {
            $startTime = strtotime($_POST['startdate']);
            $endTime = strtotime($_POST['enddate']);
            $restaurantList = $_POST['restaurantList'];
            $companyList = $_POST['companyList'];

            $error = '';
            //list all the company list
            $compbillingData = $this->checklist('company', $companyList, $startTime, $endTime);
            //list all the restaurant list
            $restbillingData = $this->checklist('rest', $restaurantList, $startTime, $endTime);

            //create the zip file
            if ($zip->open($filename, ZIPARCHIVE::CREATE) !== TRUE) {
                die(__b("Datei konnte nicht erstellt werden"));
            }

            if (!is_null($compbillingData) && (count($compbillingData) > 0)) {
                //check file for company bill
                foreach ($compbillingData as $compBillList) {
                    //check the csv file for the each billing number
                    if (is_file($billStorage . $compBillList . '.csv')) {
                        $zip->addFile($billStorage . $compBillList . '.csv', $compBillList . '.csv');
                    } else {
                        $error .= $compBillList . '.csv<br />';
                    }

                    //check the pdf file for the each billing number
                    if (is_file($billStorage . $compBillList . '.pdf')) {
                        $zip->addFile($billStorage . $compBillList . '.pdf', $compBillList . '.pdf');
                    } else {
                        $error .= $compBillList . '.pdf<br />';
                    }
                }
            }

            if (!is_null($restbillingData) && (count($restbillingData) > 0)) {
                //check file from restaurant bill
                foreach ($restbillingData as $restBillList) {
                    if (is_file($billStorage . $restBillList . '.csv')) {
                        $zip->addFile($billStorage . $restBillList . '.csv', $restBillList . '.csv');
                    } else {
                        $error .= $restBillList . '.csv<br />';
                    }

                    //check the pdf file for the each billing number
                    if (is_file($billStorage . $restBillList . '.pdf')) {
                        $zip->addFile($billStorage . $restBillList . '.pdf', $restBillList . '.pdf');
                    } else {
                        $error .= $restBillList . '.pdf<br />';
                    }
                }
            }

            $zip->close();
            $this->session->file = $filename;
            $this->session->error = $error;
        }


        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');

        //set path so the sorting and filtering will stay when we change a billing state
        $path = $this->getRequest()->getPathInfo();
        $this->session->preresetbillpath = $path;

        #$grid->export = array('pdf','csv');
        $grid->setExport(array());
        $grid->setPagination(20);
        //select orders
        $select = $db->select()
                ->from(array('b' => 'billing'), array(
                    'from' => 'b.from',
                    'until' => 'b.until',
                    'company' => new Zend_Db_Expr('SUM(INSTR(mode, "company"))'),
                    'restaurant' => new Zend_Db_Expr('SUM(INSTR(mode, "rest"))')
                ))
                ->where("b.from > 0 and b.until > 0")
                ->order('until DESC')
                ->group(array('from', 'until'));

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        //add filters
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('from')
                ->addFilter('until');
        $grid->addFilters($filters);

        // set field titles
        $grid->updateColumn('from', array('title' => __b('Von'), 'callback' => array('function' => 'dateYMD', 'params' => array('{{from}}'))))
             ->updateColumn('until', array('title' => __b('Bis'), 'callback' => array('function' => 'dateYMD', 'params' => array('{{until}}'))))
             ->updateColumn('company', array('title' => __b('Anzahl Firmenrechnungen')))
             ->updateColumn('restaurant', array('title' => __b('Anzahl Dienstleisterrechnungen')));
        
        //add option field
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Optionen'))->decorator(
                '<a href="/administration_billing/preinterval/from/{{from}}/until/{{until}}/type/dat_service" class="yd-get-billing-interval" id="yd-get-billing-interval-{{from}}-{{until}}-1">' . __b('Dienstleister Überweisung (DAT)') . '</a><br />
                 <a href="/administration_billing/preinterval/from/{{from}}/until/{{until}}/type/dat_service_debit" class="yd-get-billing-interval" id="yd-get-billing-interval-{{from}}-{{until}}-2">' . __b('Dienstleister Lastschrift (DAT)') . '</a>'
        );
        $grid->addExtraColumns($option);
        
        $this->view->grid = $grid->deploy();
    }

    /**
     * @author mlaug
     * @since 15.11.2010
     */
    public function preintervalAction() {

        $from = date('Y-m-d 00:00:00', strtotime($this->getRequest()->getParam('from')));
        $until = date('Y-m-d 23:59:59', strtotime($this->getRequest()->getParam('until')));
        $type = $this->getRequest()->getParam('type', 'csv');

        $db = Zend_Registry::get('dbAdapter');

        $result = array();
        switch ($type) {

            case 'dat_service_debit':
                $select = $db->select()
                        ->from('billing')
                        ->where('`until` = ?', $until)
                        ->where('`from` = ?', $from);
                $select->where('mode="rest"')
                        ->where('status=1')
                        ->where('amount > 0');
                $rows = $db->query($select);
                foreach ($rows as $row) {
                    try {
                        $billing = new Yourdelivery_Model_Billing($row['id']);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        continue;
                    }

                    if (!$billing->getObject() instanceof Yourdelivery_Model_Servicetype_Abstract) {
                        continue;
                    }

                    $rev = $billing->getCustomized();
                    $kto = $billing->getObject()->getKtoBlz();
                    $blz = $billing->getObject()->getKtoNr();

                    $inhaber = $billing->getObject()->getKtoName();
                    if ($inhaber == "") {
                        $inhaber = $billing->getObject()->getName();
                    }

                    $receiver = array(
                        'name' => $inhaber,
                        'account_number' => str_replace(' ', '', $blz),
                        'bank_code' => str_replace(' ', '', $kto)
                    );

                    $amount = intToPrice(round($billing->getAmount()), 2, '.');

                    $result[] = array(
                        'id' => $billing->getId(),
                        'receiver' => $receiver,
                        'amount' => $amount,
                        'allow' => $billing->getObject()->getDebit(),
                        'usage' => array(
                            'first' => $billing->getNumber(),
                            'second' => $billing->getObject()->getName()
                        )
                    );
                }
                $this->view->type = DTA_DEBIT;
                break;


            case 'dat_service':

                $select = $db->select()
                        ->from('billing')
                        ->where('`until` = ?', $until)
                        ->where('`from` = ?', $from);
                $select->where('mode="rest"')
                        ->where('status=1')
                        ->where('voucher > 0');
                $rows = $db->query($select);

                foreach ($rows as $row) {
                    try {
                        $billing = new Yourdelivery_Model_Billing($row['id']);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        continue;
                    }

                    if (!$billing->getObject() instanceof Yourdelivery_Model_Servicetype_Abstract) {
                        continue;
                    }

                    $rev = $billing->getCustomized();
                    $kto = $billing->getObject()->getKtoBlz();
                    $blz = $billing->getObject()->getKtoNr();

                    $inhaber = $billing->getObject()->getKtoName();
                    if ($inhaber == "") {
                        $inhaber = $billing->getObject()->getName();
                    }

                    $receiver = array(
                        'name' => $inhaber,
                        'account_number' => str_replace(' ', '', $blz),
                        'bank_code' => str_replace(' ', '', $kto)
                    );

                    $amount = intToPrice(round($billing->getVoucher()), 2, '.');

                    $result[] = array(
                        'id' => $billing->getId(),
                        'receiver' => $receiver,
                        'amount' => $amount,
                        'allow' => $billing->getStatus() < 2,
                        'usage' => array(
                            'first' => $billing->getNumber(),
                            'second' => $billing->getObject()->getName()
                        )
                    );
                }
                $this->view->type = DTA_CREDIT;
                break;
        }

        $this->view->result = $result;
    }

    /**
     * provide an interval of billings as csv
     * @since 15.11.2010
     * @author mlaug
     */
    public function dtaexportAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            $postData = $request->getPost();

            $filename = 'DTAUS01';
            $file = tempnam('/tmp', 'yd');
            $fp = fopen($file, 'w');

            $dta = new Default_Banking_DTA($postData['type']);
            $dta->setAccountFileSender(array(
                'name' => 'yd. Yourdelivery GmbH',
                'bank_code' => '10070124',
                'account_number' => '112132600'
            ));

            foreach ($postData['item'] as $exchange) {

                if (!$exchange['import']) {
                    continue;
                }

                $result = $dta->addExchange(
                        $exchange['receiver'], $exchange['amount'], $exchange['usage']
                );
                if ($result === false) {
                    //$this->warn(sprintf('Could not add %s with %d to DTA file'),
                    //        $exchange['receiver']['name'],$exchange['amount']));
                }
            }

            fwrite($fp, $dta->getFileContent());

            $this->getResponse()
                    ->setHeader("Content-Description", "File Transfer", true)
                    ->setHeader("Content-Disposition: attachment", ";filename=" . $filename, true)
                    ->setHeader("Content-Type", "text", true);

            readfile($file);
        } else {
            $this->error(__b("Could not export DTA file"));
            $this->_redirect('/administration_billing/information');
        }
    }

    /*
     * function to list all the requested restaurant/company for downloading bill
     * @param array $billingData
     */

    private function checklist($mode, $list, $startTime, $endTime) {
        $billingTable = new Yourdelivery_Model_DbTable_Billing();
        $billingData = array();

        if (is_null($list)) {
            return null;
        }

        foreach ($list as $dataList) {
            if (strtolower($dataList) == 'all') {
                $billingData = array();
                $tempData = $billingTable->findByModeAndTime($mode, $startTime, $endTime);
                foreach ($tempData as $data) {
                    array_push($billingData, $data['number']);
                }
                return $billingData;
            }
            $tempData = $billingTable->findByRefIdAndTime($dataList, $startTime, $endTime);
            foreach ($tempData as $data) {
                array_push($billingData, $data['number']);
            }
            return $billingData;
        }
    }

    /**
     * edit an already existing order
     * @author mlaug
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id', null);
        if (is_null($id)) {
            $this->_redirect('/administration_billing/');
        }

        try {
            $bill = new Yourdelivery_Model_Billing($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->_redirect('/administration_billing');
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form = new Yourdelivery_Form_Administration_Billing_Customized();
            if ($form->isValid($data)) {
                $cleanData = $form->getValues();
                $cleanData['billingId'] = $bill->getId();
                $bill->getSingleCustomized()
                        ->setData($cleanData)
                        ->save();
                $this->success('Data successfully saved');
            } else {
                $this->error($form->getMessage());
            }
        }

        //append bill to view
        $this->view->bill = $bill;
        $this->view->customized = $bill->getCustomized();

        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        $grid->setPagination(20);
        $select = $bill->getOrdersSelect()
                ->join(array('r' => 'restaurants'), 'o.restaurantId=r.id', array(
            'Dienstleister' => 'r.name',
            'DienstleisterId' => 'r.id',
                ));
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID', array('decorator' => '#{{ID}}'));
        $grid->updateColumn('StatusForOpt', array('hidden' => 1));
        $grid->updateColumn('DienstleisterId', array('hidden' => 1));
        $grid->updateColumn(__b('eMail'), array('decorator' => '<a href="mailto:{{' . __b('eMail') . '}}">{{' . __b('eMail') . '}}</a>'));
        $grid->updateColumn(__b('Preis'), array('callback' => array('function' => 'intToPrice', 'params' => array('{{' . __b('Preis') . '}}'))));
        $grid->updateColumn(__b('Status'), array('class' => 'status', 'callback' => array('function' => 'intToStatusOrders', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn(__b('Payment'), array('callback' => array('function' => 'Default_Helpers_Grid_Order::payment', 'params' => array('{{Payment}}', '{{ID}}'))));
        $grid->updateColumn(__b('Typ'), array('callback' => array('function' => 'modeToReadable', 'params' => array('{{' . __b('Typ') . '}}'))));
        $grid->updateColumn(__b('Kundentyp'), array('callback' => array('function' => 'kindToReadable', 'params' => array('{{' . Kundentyp . '}}'))));
        $grid->updateColumn(__b('Dienstleister'), array('decorator' => '<a href="/administration_service_edit/index/id/{{DienstleisterId}}">{{' . __b('Dienstleister') . '}}</a>'));
        $grid->updateColumn(__b('Name'), array('callback' => array('function' => 'getRegisteredCustomerLink', 'params' => array('{{' . __b('Name') . '}}', '{{' . __b('eMail') . '}}'))));

        $option = new Bvb_Grid_Extra_Column();
        $option
                ->position('right')
                ->name(__b('Optionen'))
                ->callback(array('function' => 'optionsForOrders', 'params' => array('{{ID}}')));
        $grid->addExtraColumns($option);

        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all company billings
     */
    public function companyAction() {

        $billing_interval = $this->session->compbillinginterval;
        if (is_null($billing_interval)) {
            $billing_interval = 'all';
        }

        /*
         * check if the billing interval is within the searched interval
         */
        $billing_cond = '';
        if (strcmp($billing_interval, 'all') != 0) {
            $separator = strpos($billing_interval, '_');
            $interval_from = substr($billing_interval, 0, $separator);
            $interval_until = substr($billing_interval, $separator + 1);
            $billing_cond = sprintf(" AND (b.from >= '%s') AND (b.until <= '%s  23:59:59')", $interval_from, $interval_until);
        }

        //set path so the sorting and filtering will stay when we change a billing state
        $path = $this->getRequest()->getPathInfo();
        $this->session->preresetbillpath = $path;

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()
                ->from(array('b' => 'billing'), array(
                    'ID' => 'id',
                    __b('Nummer') => 'number',
                    __b('Betrag') => 'amount',
                    __b('Gutschrift') => 'voucher',
                    __b('Von') => 'from',
                    __b('Bis') => 'until',
                    __b('Status') => 'status',
                ))
                ->join(array('c' => 'companys'), 'b.refId = c.id', array(
                    'CID' => 'c.id',
                    __b('Firma') => 'c.name'
                ))
                ->joinLeft(array('cont' => 'contacts'), 'c.billingContactId = cont.id', array(
                    __b('EMAIL') => 'cont.email',
                    __b('FAX') => 'cont.fax'
                ))
                ->where('b.mode = "company"' . $billing_cond)
                ->order('b.id DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(15);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID', array('decorator' => '#{{ID}}'));
        $grid->updateColumn('ID', array('searchType' => '='));
        $grid->updateColumn('CID', array('searchType' => 'equal', 'hidden' => 1));
        $grid->updateColumn(__b('EMAIL'), array('hidden' => 1));
        $grid->updateColumn(__b('FAX'), array('hidden' => 1));
        $grid->updateColumn(__b('Gutschrift'), array('hidden' => 1));
        $grid->updateColumn(__b('Betrag'), array('callback' => array('function' => 'billTotalAndVoucher', 'params' => array('{{' . __b('Betrag') . '}}', '{{' . __b('Gutschrift') . '}}'))));
        $grid->updateColumn(__b('Firma'), array('decorator' => '<a href="/administration_company_edit/index/companyid/{{CID}}">{{' . __b('Firma') . '}}</a>'));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'billingStatusDropdown', 'params' => array('{{ID}}', 'company', $path))));
        $grid->updateColumn(__b('Von'), array('callback' => array('function' => 'sqlTimeToDMY', 'params' => array('{{' . __b('Von') . '}}'))));
        $grid->updateColumn(__b('Bis'), array('callback' => array('function' => 'sqlTimeToDMY', 'params' => array('{{' . __b('Bis') . '}}'))));
        $grid->updateColumn(__b('Nummer'), array('callback' => array(
                'function' => function ($number, $id) {
                    try {
                        $bill = new Yourdelivery_Model_Billing($id);
                        $html = $number;
                        
                        if ( $bill->getPdf() ){
                            $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '/pdf">' . __b("Rechnung herunterladen") . '</a>';
                        }
                        
                        if ( $bill->getAssetPdf() ){
                            $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '/asset">' . __b("Zusatzrechnung herunterladen") . '</a>';
                        }
                        
                        if ( $bill->getCsv() ){
                            $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '/csv">' . __b("CSV herunterladen") . '</a>';
                        }
                        
                        if ( $bill->getPdf() ){
                            $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '">' . __b("Alles als ZIP herunterladen") . '</a>';
                        }
                        
                        return $html;
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        
                    }
                }, 'params' => array('{{' . __b('Nummer') . '}}', '{{ID}}')
                )));

        $statis = Yourdelivery_Model_Billing_Abstract::getStatusse();
        $statis[''] = __b('Alle');

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters
                ->addFilter(__b('Status'), array('values' => $statis))
                ->addFilter('ID')
                ->addFilter(__b('Nummer'))
                ->addFilter(__b('Von'))
                ->addFilter(__b('Bis'))
                ->addFilter(__b('Firma'));
        $grid->addFilters($filters);

        // add extra rows
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Options'))->callback(array('function' => 'optionsForBillingCompany', 'params' => array('{{ID}}')));

        $idCheckbox = new Bvb_Grid_Extra_Column();
        $idCheckbox->position('right')->name('')->callback(array('function' => 'idCheckbox', 'params' => array('{{ID}}')));

        //add extra rows
        $grid->addExtraColumns($option, $idCheckbox);

        $this->view->interval = $billing_interval;
        $this->view->allintervals = Yourdelivery_Model_Billing::getBillingsIntervals('company');

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all restaurant billings
     */
    public function serviceAction() {

        $billing_interval = $this->session->restbillinginterval;
        if (is_null($billing_interval)) {
            $billing_interval = 'all';
        }

        /*
         * check if the billing intervall is within the searched interval
         */
        $billing_cond = '';
        if (strcmp($billing_interval, 'all') != 0) {
            $separator = strpos($billing_interval, '_');
            $interval_from = substr($billing_interval, 0, $separator);
            $interval_until = substr($billing_interval, $separator + 1);
            $billing_cond = sprintf(" AND (b.from >= '%s') AND (b.until <= '%s  23:59:59')", $interval_from, $interval_until);
        }

        //set path so the sorting and filtering will stay when we change a billing state
        $path = $this->getRequest()->getPathInfo();
        $this->session->preresetbillpath = $path;

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->from(array('b' => 'billing'), array(
                    'ID' => 'id',
                    __b('Nummer') => 'number',
                    __b('Betrag') => 'amount',
                    __b('Gutschrift') => 'voucher',
                    __b('Von') => 'from',
                    __b('Bis') => 'until',
                    __b('Status') => 'status',
                ))
                ->join(array('r' => 'restaurants'), 'b.refId = r.id', array(
                    __b('Restaurant') => 'r.name',
                    'RestaurantId' => 'r.id',
                ))
                ->joinLeft(array('cont' => 'contacts'), 'r.contactId = cont.id', array(
                    __b('EMAIL') => 'cont.email',
                    __b('FAX') => 'cont.fax'
                ))
                ->where('b.mode = "rest"' . $billing_cond) //get only billings
                ->order('b.id DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(15);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID', array('decorator' => '#{{ID}}'));
        $grid->updateColumn('ID', array('searchType' => '='));
        $grid->updateColumn('RestaurantId', array('searchType' => 'equal', 'hidden' => 1));
        $grid->updateColumn(__b('EMAIL'), array('hidden' => 1));
        $grid->updateColumn(__b('FAX'), array('hidden' => 1));
        $grid->updateColumn(__b('Gutschrift'), array('hidden' => 1));
        $grid->updateColumn(__b('Betrag'), array('callback' => array('function' => 'billTotalAndVoucher', 'params' => array('{{' . __b('Betrag') . '}}', '{{' . __b('Gutschrift') . '}}'))));
        $grid->updateColumn(__b('Restaurant'), array('decorator' => '<a href="/administration_service_edit/index/id/{{RestaurantId}}">{{' . __b('Restaurant') . '}}</a>'));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'billingStatusDropdown', 'params' => array('{{ID}}', 'service', $path))));
        $grid->updateColumn(__b('Von'), array('callback' => array('function' => 'sqlTimeToDMY', 'params' => array('{{' . __b('Von') . '}}'))));
        $grid->updateColumn(__b('Bis'), array('callback' => array('function' => 'sqlTimeToDMY', 'params' => array('{{' . __b('Bis') . '}}'))));
        $grid->updateColumn(__b('Nummer'), array('callback' => array(
                'function' => function ($number, $id) {
                    try {
                        $bill = new Yourdelivery_Model_Billing($id);
                        $html = $number;
                        
                        if ( $bill->getPdf() ){
                            $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '/pdf">' . __b("Rechnung herunterladen") . '</a>';
                        }
                        
                        if ( $bill->getVoucherPdf() ){
                            $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '/voucher">' . __b("Gutschrift herunterladen") . '</a>';
                        }
                        
                        if ( $bill->getAssetPdf() ){
                            $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '/asset">' . __b("Zusatzrechnung herunterladen") . '</a>';
                        }
                        
                        if ( $bill->getPdf() ){
                            $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '">' . __b("Alles als ZIP herunterladen") . '</a>';
                        }
                        
                        return $html;
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        
                    }
                }, 'params' => array('{{' . __b('Nummer') . '}}', '{{ID}}')
                )));

        $statis = Yourdelivery_Model_Billing_Abstract::getStatusse();
        $statis[''] = __b('Alle');

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters
                ->addFilter(__b('Status'), array('values' => $statis))
                ->addFilter('ID')
                ->addFilter(__b('Nummer'))
                ->addFilter(__b('Von'))
                ->addFilter(__b('Bis'))
                ->addFilter(__b('Restaurant'));
        $grid->addFilters($filters);

        // add extra rows
        $option = new Bvb_Grid_Extra_Column();
        $option
                ->position('right')
                ->name(__b('Optionen'))
                ->callback(array('function' => 'optionsForBillingRestaurant', 'params' => array('{{ID}}')));

        $idCheckbox = new Bvb_Grid_Extra_Column();
        $idCheckbox->position('right')->name('')->callback(array('function' => 'idCheckbox', 'params' => array('{{ID}}')));

        //add extra rows
        $grid->addExtraColumns($option, $idCheckbox);

        $this->view->interval = $billing_interval;
        $this->view->allintervals = Yourdelivery_Model_Billing::getBillingsIntervals('rest');

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all courier billings
     */
    public function courierAction() {

        $billing_interval = $this->session->courierbillinginterval;
        if (is_null($billing_interval)) {
            $billing_interval = 'all';
        }

        /*
         * check if the billing intervall is within the searched interval
         */
        $billing_cond = '';
        if (strcmp($billing_interval, 'all') != 0) {
            $separator = strpos($billing_interval, '_');
            $interval_from = substr($billing_interval, 0, $separator);
            $interval_until = substr($billing_interval, $separator + 1);
            $billing_cond = sprintf(" AND (b.from >= '%s') AND (b.until <= '%s 23:59:59')", $interval_from, $interval_until);
        }

        //set path so the sorting and filtering will stay when we change a billing state
        $path = $this->getRequest()->getPathInfo();
        $this->session->preresetbillpath = $path;

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->from(array('b' => 'billing'), array(
                    'ID' => 'id',
                    __b('Nummer') => 'number',
                    __b('Betrag') => 'amount',
                    __b('Gutschrift') => 'voucher',
                    __b('Von') => 'from',
                    __b('Bis') => 'until',
                    __b('Status') => 'status',
                ))
                ->join(array('c' => 'courier'), 'b.refId = c.id', array(
                    'CID' => 'c.id',
                    __b('Courier') => 'c.name',
                    __b('EMAIL') => 'c.email',
                    __b('FAX') => 'c.fax',
                ))
                ->where('b.mode = "courier"' . $billing_cond)
                ->order('b.id DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(15);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID', array('decorator' => '#{{ID}}'));
        $grid->updateColumn('ID', array('searchType' => '='));
        $grid->updateColumn('CID', array('searchType' => 'equal', 'hidden' => 1));
        $grid->updateColumn(__b('EMAIL'), array('hidden' => 1));
        $grid->updateColumn(__b('FAX'), array('hidden' => 1));
        $grid->updateColumn(__b('Gutschrift'), array('hidden' => 1));
        $grid->updateColumn(__b('Betrag'), array('callback' => array('function' => 'billTotalAndVoucher', 'params' => array('{{' . __b('Betrag') . '}}', '{{' . __b('Gutschrift') . '}}'))));
        $grid->updateColumn(__b('Courier'), array('decorator' => '<a href="/administration_courier/edit/cid/{{CID}}">{{' . __b('Courier') . '}}</a>'));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'billingStatusDropdown', 'params' => array('{{ID}}', 'courier', $path))));
        $grid->updateColumn(__b('Von'), array('callback' => array('function' => 'sqlTimeToDMY', 'params' => array('{{' . __b('Von') . '}}'))));
        $grid->updateColumn(__b('Bis'), array('callback' => array('function' => 'sqlTimeToDMY', 'params' => array('{{' . __b('Bis') . '}}'))));
        $grid->updateColumn(__b('Nummer'), array('callback' => array(
                'function' => function ($number, $id) {
                    $html = $number;
                    $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '">' . __b("ZIP herunterladen") . '</a>';
                    return $html;
                }, 'params' => array('{{' . __b('Nummer') . '}}', '{{ID}}')
                )));

        $statis = Yourdelivery_Model_Billing_Abstract::getStatusse();
        $statis[''] = __b('Alle');

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters
                ->addFilter(__b('Status'), array('values' => $statis))
                ->addFilter('ID')
                ->addFilter(__b('Nummer'))
                ->addFilter(__b('Von'))
                ->addFilter(__b('Bis'))
                ->addFilter(__b('Courier'));
        $grid->addFilters($filters);

        // add extra rows
        $option = new Bvb_Grid_Extra_Column();
        $option
                ->position('right')
                ->name(__b('Optionen'))
                ->callback(array('function' => 'optionsForBillingCourier', 'params' => array('{{ID}}')));

        $idCheckbox = new Bvb_Grid_Extra_Column();
        $idCheckbox->position('right')->name('')->callback(array('function' => 'idCheckbox', 'params' => array('{{ID}}')));

        //add extra rows
        $grid->addExtraColumns($option, $idCheckbox);

        $this->view->interval = $billing_interval;
        $this->view->allintervals = Yourdelivery_Model_Billing::getBillingsIntervals('courier');

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * delete billing
     */
    public function deletebillAction() {
        $id = $this->getRequest()->getParam('id', null);
        $mode = $this->getRequest()->getParam('mode', null);
        $path = $this->session->preresetbillpath;


        $db = Zend_Registry::get('dbAdapter');
        $db->beginTransaction();
        try {
            list( $ret, $row, $_ ) = Yourdelivery_Model_DbTable_Billing::resetBill($id, $mode);
            if ($ret) {
                $db->commit();
            } else {
                $db->rollback();
            }
            $row->delete();
            $this->success(__b("Billing succuessfully deleted"));
        } catch (Exception $e) {
            $this->error(__b("Failed to delete bill"));
            $db->rollback();
        }

        if (isset($path)) {
            $this->_redirect($path);
        }

        switch ($mode) {
            case 'company': {
                    $this->_redirect('/administration_billing/company');
                    break;
                }
            case 'rest': {
                    $this->_redirect('/administration_billing/service');
                    break;
                }
        }
    }

    /**
     * remove billing customization (if any) and therefor
     * reset to company/service default
     * @author mlaug
     * @since 07.08.2010
     */
    public function resetstandardAction() {

        $id = $this->getRequest()->getParam('id', null);
        $mode = $this->getRequest()->getParam('mode', null);
        $redirect = '/administration_billing/edit/id/' . $id;

        if (is_null($id)) {
            $this->error(__b("Could NOT find billing"));
            //if path with sorting and filtering is set, use it
            $this->_redirect($redirect);
        }

        try {
            $bill = new Yourdelivery_Model_Billing($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Could NOT find billing"));
            //if path with sorting and filtering is set, use it
            $this->_redirect($redirect);
        }

        $customized = $bill->getSingleCustomized();

        if ($customized->isPersistent()) {
            $customized->getTable()
                    ->getCurrent()
                    ->delete();
        }

        $this->success(__b("Company defaults successfully restored"));
        $this->_redirect($redirect);
    }

    /**
     * reset billing
     */
    public function resetbillAction() {
        //alter php settings to avoid running out of memory
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 1200);

        $id = $this->getRequest()->getParam('id', null);
        $mode = $this->getRequest()->getParam('mode', null);
        $crefo = $this->getRequest()->getParam('crefo', true);
        $path = $this->session->preresetbillpath;

        if (is_null($id)) {
            $this->error(__b("Could NOT find billing"));
            //if path with sorting and filtering is set, use it
            if (isset($path)) {
                $this->_redirect($path);
            }

            switch ($mode) {
                case 'company': {
                        $this->_redirect('/administration_billing/company');
                        break;
                    }
                case 'rest': {
                        $this->_redirect('/administration_billing/service');
                        break;
                    }
                case 'courier': {
                        $this->_redirect('/administration_billing/courier');
                        break;
                    }
            }
        }

        $ret = Yourdelivery_Model_Billing::rebuild($id, true, $crefo);
        if ($ret) {
            $this->success(__b("Bill successfully re-generated"));
        } else {
            $this->error(__b("Bill could NOT be re-generated"));
        }
        //if path with sorting and filtering is set, use it
        if (isset($path)) {
            $this->_redirect($path);
        }

        switch ($mode) {
            case 'company': {
                    $this->_redirect('/administration_billing/company');
                    break;
                }
            case 'rest': {
                    $this->_redirect('/administration_billing/service');
                    break;
                }
            case 'courier': {
                    $this->_redirect('/administration_billing/courier');
                    break;
                }
        }
    }

    /**
     * Change status of the bill
     * @author Alex Vait <vait@lieferando.de>
     * @since 19.06.2012
     */
    public function changestatusAction() {
        $request = $this->getRequest();

        $billingId = $request->getParam('id', false);
        $mode = $request->getParam('mode', 'company');
        $path = $request->getParam('path', '/administration_billing/' . $mode);
        $status = $request->getParam('status', 0);

        if ($billingId) {
            $billing = new Yourdelivery_Model_Billing($billingId);
            
            $admin = $this->session->admin;
            if (!is_null($admin)) {
                $adminId = $admin->getId();                
            }
                    
            if ($status == Yourdelivery_Model_Billing_Abstract::SENT) {
                $billing->sendManually($adminId);
            } else {
                $billing->setStatus($status, $adminId);
            }
        }

        $this->_redirect($path);
    }

    /**
     * set interval of company billings that will be shown in billing table
     * @author alex
     * @since 30.08.2010
     */
    public function changeintervalcompAction() {
        $request = $this->getRequest();
        $interval = $request->getParam('interval', null);

        if ($request->isPost()) {
            $post = $request->getPost();
            $interval = date(DATE_DB, strtotime($post['fromD'])) . "_" . date(DATE_DB, strtotime($post['untilD']));
        }

        if (is_null($interval)) {
            $this->session->compbillinginterval = 'all';
        } else {
            $this->session->compbillinginterval = $interval;
        }

        $this->_redirect('/administration_billing/company');
    }

    /**
     * set interval of restaurant billings that will be shown in billing table
     * @author alex
     * @since 30.08.2010
     */
    public function changeintervalrestAction() {
        $request = $this->getRequest();
        $interval = $request->getParam('interval', null);

        if ($request->isPost()) {
            $post = $request->getPost();
            $interval = date(DATE_DB, strtotime($post['fromD'])) . "_" . date(DATE_DB, strtotime($post['untilD']));
        }

        if (is_null($interval)) {
            $this->session->restbillinginterval = 'all';
        } else {
            $this->session->restbillinginterval = $interval;
        }

        $this->_redirect('/administration_billing/service');
    }

    /**
     * set interval of courier billings that will be shown in billing table
     * @author alex
     * @since 30.09.2010
     */
    public function changeintervalcourierAction() {
        $request = $this->getRequest();
        $interval = $request->getParam('interval', null);

        if ($request->isPost()) {
            $post = $request->getPost();
            $interval = date(DATE_DB, strtotime($post['fromD'])) . "_" . date(DATE_DB, strtotime($post['untilD']));
        }

        if (is_null($interval)) {
            $this->session->courierbillinginterval = 'all';
        } else {
            $this->session->courierbillinginterval = $interval;
        }

        $this->_redirect('/administration_billing/courier');
    }

    /**
     * get all intervals of restaurant billings
     * @author alex
     * @since 30.08.2010
     */
    public function getintervalsrestAction() {
        $interval = $this->getRequest()->getParam('interval', null);

        if (is_null($interval)) {
            $this->session->restbillinginterval = 'all';
        } else {
            $this->session->restbillinginterval = $interval;
        }

        $this->_redirect('/administration_billing/service');
    }

    public function confirmbillsAction() {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->error(__b("No bills selected"));
            $this->_redirect('/administration_billing/csvcompare');
        }

        $count = 0;
        $billIds = $request->getParam('billId', array());
        if (!is_array($billIds)) {
            $billIds = array($billIds);
        }

        foreach ($billIds as $billId) {
            try {
                $bill = new Yourdelivery_Model_Billing($billId);
                if ($bill->getStatus() == Yourdelivery_Model_Billing::UNPAYED) {
                    $this->logger->adminDebug(sprintf("marking %s (%s) as payed", $bill->getNumber(), $billId));
                    $bill->setStatus(Yourdelivery_Model_Billing::PAYED); //will be saved, too
                    $count++;
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->adminErr(sprintf("coult not find bill %s", $billId));
            }
        }

        $this->success(__b("%s bills marked as payed", $count));
        $this->_redirect('/administration_billing/csvcompare');
    }

    /**
     * @since 28.10.2010
     * @author mlaug
     */
    public function csvcompareAction() {
        $request = $this->getRequest();
        $file = $request->getParam('file', null);

        if (file_exists($file)) {

            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            echo file_get_contents($file);
        } else {
            $this->view->result = null;

            if ($request->isPost()) {
                $form = new Yourdelivery_Form_Administration_Billing_Csvcompare();
                if ($form->isValid($request->getParams())) {
                    //upload new image
                    if ($form->csv->isUploaded()) {

                        if (!is_dir(APPLICATION_PATH . '/../storage/csvcompare/in/')) {
                            $this->logger->debug('creating in directory for csvcompare');
                            mkdir(APPLICATION_PATH . '/../storage/csvcompare/in/', 0777, true);
                        }

                        $this->logger->adminDebug('uploading csv file for bill comparison');
                        $adapter = new Zend_File_Transfer_Adapter_Http();
                        $adapter->setDestination(APPLICATION_PATH . '/../storage/csvcompare/in/');
                        $adapter->receive();
                    } else {
                        $this->error(__b("Konnte Datei nicht hochladen"));
                    }
                } else {
                    $this->error($form->getMessages());
                }
            }

            $this->view->out = array_reverse(glob(APPLICATION_PATH . '/../storage/csvcompare/out/*.html'));
            $this->view->in = array_reverse(glob(APPLICATION_PATH . '/../storage/csvcompare/in/*.csv'));
        }
    }

    public function balancebillAction() {
        $id = (integer) $this->getRequest()->getParam('id', null);
        $mode = $this->getRequest()->getParam('mode', null);
        $path = $this->session->preresetbillpath;

        if ($mode == 'rest' && $id > 0) {
            try {
                $bill = new Yourdelivery_Model_Billing($id);
                if ($bill->doBalance()) {
                    $this->success(__b("bill has been balanced, will be chared in next billing interval"));
                } else {
                    $this->error(__b("bill could not be balanced"));
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("bill could not be balanced, has not been found"));
            }
        }

        if (isset($path)) {
            $this->_redirect($path);
        }
    }

    /**
     * Tells whether locale overriding is forbidden within whole controller
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 6.07.2012
     *
     * @return boolean
     */
    protected function _isLocaleFrozen() {
        return true;
    }
}
