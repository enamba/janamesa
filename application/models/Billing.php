<?php

/**
 * Description of Billing
 * @package billing
 * @author mlaug
 */
class Yourdelivery_Model_Billing extends Yourdelivery_Model_Billing_Abstract {

    /**
     *
     * @param string
     */
    protected $_mode = null;
    /**
     * reference for billing
     * @var mixed Yourdelivery_Model_Company|Yourdelivery_Model_Restaurant|Yourdelivery_Model_Customer
     */
    protected $_reference = null;

    /**
     * get all invoices from services
     * @author mlaug
     * @return SplObjectStorage
     */
    public static function allService() {
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->query('select id from billing where mode="rest" and number like "R-%" ')->fetchAll();
        $billings = new SplObjectStorage();
        foreach ($result as $c) {
            $billing = new Yourdelivery_Model_Billing_Restaurant($c['id']);
            $billings->attach($billing);
        }
        return $billings;
    }

    /**
     * get all invoices from company
     * @author mlaug
     * @return SplObjectStorage
     */
    public static function allCompany() {
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->query('select id from billing where mode="company" and number like "R-%" ')->fetchAll();
        $billings = new SplObjectStorage();
        foreach ($result as $c) {
            $billing = new Yourdelivery_Model_Billing_Company($c['id']);
            $billings->attach($billing);
        }
        return $billings;
    }

    /**
     * get all unpayed invoices
     * @author mlaug
     * @return array
     */
    public static function allUnpayed() {
        $db = Zend_Registry::get('dbAdapter');
        return array_merge(
                $db->fetchAll('select b.id as uid,b.number,b.amount,b.voucher,r.*, CONCAT(ct.name," ",ct.prename) as contact_name from billing b inner join restaurants r on r.id=b.refId left join contacts ct on ct.id = r.contactId  where b.status=1 and mode="rest"'), 
                $db->fetchAll('select b.id as uid,b.number,b.amount,b.voucher,c.*, CONCAT(ct.name," ",ct.prename) as contact_name from billing b inner join companys c on c.id=b.refId left join contacts ct on ct.id = c.contactId where b.status=1 and mode="company"'), 
                $db->fetchAll('select b.id as uid,b.number,b.amount,b.voucher,c.*, CONCAT(ct.name," ",ct.prename) as contact_name from billing b inner join courier c on c.id=b.refId left join contacts ct on ct.id = c.contactId where b.status=1 and mode="courier"')
        );
    }

    /**
     * Get distinct billings intervals of certains type
     * @author alex
     * @since 05.04.2011
     * @return array
     */
    public static function getBillingsIntervals($mode) {
        $db = Zend_Registry::get('dbAdapter');

        $intervals = array();

        //convert dates of first and last billing to timestamp
        $firstTimestamp = strtotime(Yourdelivery_Model_DbTable_Billing::getDateOfFirstBilling($mode));
        $lastTimestamp = strtotime(Yourdelivery_Model_DbTable_Billing::getDateOfLastBilling($mode));

        // start with first of month
        $loopTimestamp = strtotime(date('Y', $firstTimestamp) . "-" . date('m', $firstTimestamp) . "-01");

        // add first half of month only if the first billing time is before 15-th
        if (date('d', $firstTimestamp) < 15) {
            $intervals[date('Y-m-01', $loopTimestamp) . '_' . date('Y-m-15', $loopTimestamp)] = date('01.m.Y', $loopTimestamp) . '-' . date('15.m.Y', $loopTimestamp);
        }
        // add second half of month
        $intervals[date('Y-m-16', $loopTimestamp) . '_' . date('Y-m-t', $loopTimestamp)] = date('16.m.Y', $loopTimestamp) . '-' . date('t.m.Y', $loopTimestamp);

        $loopTimestamp = strtotime('+1 month', $loopTimestamp);
        while ($loopTimestamp <= $lastTimestamp) {
            $intervals[date('Y-m-01', $loopTimestamp) . '_' . date('Y-m-15', $loopTimestamp)] = date('01.m.Y', $loopTimestamp) . '-' . date('15.m.Y', $loopTimestamp);

            // add second half of month only if the last billing time is after 16-th and now is not the last billing month
            if ((date('Y-m', $loopTimestamp) < date('Y-m', $lastTimestamp)) || (date('d', $lastTimestamp) >= 16)) {
                $intervals[date('Y-m-16', $loopTimestamp) . '_' . date('Y-m-t', $loopTimestamp)] = date('16.m.Y', $loopTimestamp) . '-' . date('t.m.Y', $loopTimestamp);
            }

            $loopTimestamp = strtotime('+1 month', $loopTimestamp);
        }

        return $intervals;
    }
    
    /**
     * get commulated amount of the last $countLastBills vouchers
     * @author mlaug
     * @since 14.06.2011
     * @param integer $refId
     * @param integer $countLastBills
     * @return integer
     */
    public static function getVoucherAmounts($refId, $countLastBills = 1){
        $db = Zend_Registry::get('dbAdapter');
        return (integer) $db->fetchOne(
            "SELECT SUM(`voucher`) 
            FROM (
                SELECT `voucher`
                FROM `billing`
                WHERE `refId` = ? AND `mode` = 'rest'
                ORDER BY `from` DESC 
                LIMIT " . ((integer) $countLastBills) . "
            ) AS t", $refId
        );
    }

    /**
     * rebuild an billing
     * @since 07.08.2010
     * @author mlaug
     * @param int $id
     * @return boolean
     */
    public static function rebuild($id, $rebuildPdf = true, $crefo = true) {

        $logger = Zend_Registry::get('logger');

        if ($id === null) {
            $logger->warn('no bill id provided to reset');
            return false;
        }

        try {
            $bill = new Yourdelivery_Model_Billing($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $logger->crit(sprintf('no bill found with the id %s', $id));
            return false;
        }

        $refId = $bill->getRefId();
        $from = $bill->getTimeFrom(); //will get `from` with strtotime conversion
        $until = $bill->getTimeUntil(); //will get `until` with strtotime conversion
        $number = $bill->getNumber();
        $mode = $bill->getMode();
        unset($bill);

        switch ($mode) {

            default: 
                $logger->warn('no valid mode "%s" to reset bill', $mode);
                return false;

            case 'rest': 
                try {
                    $service = new Yourdelivery_Model_Servicetype_Restaurant($refId);
                    $bill = new Yourdelivery_Model_Billing_Restaurant($service, $from, $until);
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    return false;
                }
                break;

            case 'company': 
                try {
                    $company = new Yourdelivery_Model_Company($refId);
                    $bill = new Yourdelivery_Model_Billing_Company($company, $from, $until);
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    return false;
                }
                break;

            case 'courier': 
                try {
                    $courier = new Yourdelivery_Model_Courier($refId);
                    $bill = new Yourdelivery_Model_Billing_Courier($courier, $from, $until);
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    return false;
                }
                break;
            
            case 'upselling_goods': 
                try {
                    $bill = new Yourdelivery_Model_Billing_Upselling_Goods($id);
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    return false;
                }
                break;
        }

        //reload data
        $bill->setOldId((int) $id);

        $db = Zend_Registry::get('dbAdapter');
        $db->beginTransaction();
        list($ret, $row, $orders, $assets) = Yourdelivery_Model_DbTable_Billing::resetBill($id, $mode, $refId);
        if (!$ret || $row->id != $id) {
            $logger->err(sprintf('could not reset bill %s', $id));
            $db->rollback();
            return false;
        }

        $bill->setNumber($number);
        //return $bill;
        $ret = $bill->create(false, $row, $orders, $assets, $rebuildPdf, false, $crefo);
        if (!$ret) {
            $logger->crit(sprintf('could not reset bill %s, failed to create', $id));
            $db->rollback();
            return false;
        }
        $db->commit();
        return true;
    }

    /**
     * Create billing from hash for downloading
     * @author vpriem
     * @since 08.10.2010
     * @return Yourdelivery_Model_Billing|boolean
     */
    public static function createFromHash($hash) {

        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow(
            "SELECT `id`
            FROM `billing`
            WHERE MD5(CONCAT(?, `id`, ?)) = ?
            LIMIT 1", array(SALT, SALT, $hash)
        );

        if ($row['id']) {
            try {
                return new Yourdelivery_Model_Billing($row['id']);
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
        }
        return false;
    }

    /**
     * Get hash for downloading
     * @author vpriem
     * @since 08.10.2010
     * @return string
     */
    public function getHash() {
        return Default_Helpers_Crypt::hash($this->getId());
    }

    /**
     * get all files associated with this bill
     * @author mlaug
     * @since 05.06.2011
     * @return array
     */
    public function getAllFiles() {
        return array_merge(
            $this->getAllPdfFiles(), $this->getAllCsvFiles()
        );
    }

    /**
     * get all pdf files
     * @author mlaug
     * @since 05.06.2011
     */
    public function getAllPdfFiles() {
        $pdfs = array();
        $pdfs[] = $this->getPdf();
        $pdfs = array_merge($pdfs, $this->getAdditionalFiles(true, false));
        return array_filter($pdfs,function($f){ return file_exists($f); });
    }

    /**
     * get all csv files
     * @author mlaug
     * @since 05.06.2011
     * @return array
     */
    public function getAllCsvFiles() {
        $csvs = array();
        $csvs[] = $this->getCsv();
        $csvs = array_merge($csvs, $this->getAdditionalFiles(false, true));
        return array_filter($csvs,function($f){ return file_exists($f); });
    }

    /**
     * overwrite pdf (sent out files )
     * @author mlaug
     * @since 30.08.2010
     * @param string $pdf
     * @return boolean
     */
    public function setPdf($pdf) {
        if (file_exists($pdf)) {
            $this->_data['pdf'] = $pdf;
            return true;
        }
        return false;
    }

    /**
     * Get pdf of invoice
     * @author vpriem
     * @since 01.08.2010
     * @return mixed
     */
    public function getPdf() {

        //check for overwrite (sent files)
        if (isset($this->_data['pdf'])) {
            return $this->_data['pdf'];
        }

        $sheet = $this->getNumber() . '.pdf';
        $storage = $this->getStorage();
        
        if ($storage->exists($sheet)) {
            return $storage->getCurrentFolder() . '/' . $sheet;
        }
        
        return false;
    }

    /**
     * get one explicit sub pdf
     * @author vpriem
     * @since 09.03.2013
     * @param int $id costcenterid
     * @return mixed
     */
    public function getSubPdf($id) {

        $sheet = $this->getNumber() . '-' . ((integer) $id) . '.pdf';
        $storage = $this->getStorage();
        $storage->setSubFolder($this->getNumber(), false);
        if (file_exists($sheet)) {
            return $storage->getCurrentFolder() . '/' . $sheet;
        }
        return false;
    }

    /**
     * Get additional pdf of invoice
     * @author vpriem
     * @since 12.08.2010
     * @return array
     */
    public function getAdditionalFiles($pdf = true, $csv = true) {

        $sheets = array();

        $storage = $this->getStorage();
        if ($storage->exists($this->getNumber())) {
            $storage->setSubFolder($this->getNumber());
            $files = $storage->ls();
            foreach ($files as $file) {
                if ($pdf && preg_match("`\.pdf$`", $file)) {
                    $sheets[] = $file;
                }
                if ($csv && preg_match("`\.csv$`", $file)) {
                    $sheets[] = $file;
                }
            }
        }

        if ($this->getVoucherPdf()) {
            $sheets[] = $this->getVoucherPdf();
        }

        if ($this->getAssetPdf()) {
            $sheets[] = $this->getAssetPdf();
        }
        
        return $sheets;
    }

    /**
     * Get pdf of asset
     * @author mlaug
     * @since 11.03.2011
     * @return mixed
     */
    public function getAssetPdf() {
        $sheet = str_replace('R', 'A', $this->getNumber() . '.pdf');
        $storage = $this->getStorage();
        if ($storage->exists($sheet)) {
            return $storage->getCurrentFolder() . '/' . $sheet;
        }
        return false;
    }

    /**
     * Get pdf of voucher
     * @author mlaug
     * @since 01.08.2010
     * @return mixed
     */
    public function getVoucherPdf() {

        $sheet = str_replace('R', 'G', $this->getNumber() . '.pdf');
        $storage = $this->getStorage();
        if ($storage->exists($sheet)) {
            return $storage->getCurrentFolder() . '/' . $sheet;
        }
        return false;
    }

    /**
     * Get csv of invoice
     * @author vpriem
     * @since 01.08.2010
     * @return mixed
     */
    public function getCsv() {

        $sheet = $this->getNumber() . '.csv';
        $storage = $this->getStorage();
        if ($storage->exists($sheet)) {
            return $storage->getCurrentFolder() . '/' . $sheet;
        }
        return false;
    }

    /**
     * get timestamp of starting date
     * @author mlaug
     * @return int
     */
    public function getTimeFrom() {
        return strtotime($this->_data['from']);
    }

    /**
     * get timestamp of ending date
     * @author mlaug
     * @return int
     */
    public function getTimeUntil() {
        return strtotime($this->_data['until']);
    }

    /**
     * get the associated reference for billing
     * @author mlaug
     * @return mixed Yourdelivery_Model_Company|Yourdelivery_Model_Restaurant|Yourdelivery_Model_Customer
     */
    public function getObject() {
        if (is_null($this->_reference)) {
            try {
                switch ($this->getMode()) {
                    case self::BILLING_COMPANY: {
                            $this->_reference = new Yourdelivery_Model_Company($this->getRefId());
                            break;
                        }
                    case self::BILLING_CUSTOMER: {
                            $this->_reference = new Yourdelivery_Model_Customer($this->getRefId());
                            break;
                        }
                    case self::BILLING_COURIER: {
                            $this->_reference = new Yourdelivery_Model_Courier($this->getRefId());
                            break;
                        }
                    case self::BILLING_RESTAURANT: {
                            $this->_reference = new Yourdelivery_Model_Servicetype_Restaurant($this->getRefId());
                        }
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return false;
            }
        }

        return $this->_reference;
    }

    /**
     * get the associated reference for billing
     * @deprecated
     * @author mlaug
     * @return mixed Yourdelivery_Model_Company|Yourdelivery_Model_Restaurant|Yourdelivery_Model_Customer
     */
    public function getReference() {
        return $this->getObject();
    }

    /**
     * get select statement for orders
     * @author mlaug
     * @return Zend_Db_Select
     */
    public function getOrdersSelect() {
        $field = "";
        switch ($this->getMode()) {
            case Yourdelivery_Model_Billing::BILLING_COMPANY: {
                    $field = 'billCompany';
                    break;
                }
            case Yourdelivery_Model_Billing::BILLING_RESTAURANT: {
                    $field = 'billRest';
                    break;
                }
            case Yourdelivery_Model_Billing::BILLING_COURIER: {
                    $field = 'billCourier';
                }
        }

        $where = $field . "=" . $this->getId();
        $select = $this->getTable()->getAdapter()->select();
        $select->from(array('o' => 'orders'), array(
                    'ID' => 'o.id',
                    'Eingang' => 'o.time',
                    'Lieferzeit' => 'o.deliverTime',
                    'Status' => 'o.state',
                    'StatusForOpt' => 'o.state',
                    'Typ' => 'o.mode',
                    'Payment' => 'o.payment',
                    'Preis' => new Zend_Db_Expr('o.total+o.serviceDeliverCost'),
                ))
                ->where($where);
        return $select;
    }

    /**
     * get all orders of this billing
     * @author mlaug
     * @return SplObjectStorage
     */
    public function getOrders() {
        if (is_null($this->orders)) {
            $all = $this->getTable()
                    ->getAdapter()
                    ->query($this->getOrdersSelect());
            $obj = new SplObjectStorage();
            foreach ($all AS $order) {
                $obj->attach(new Yourdelivery_Model_Order($order['ID']));
            }
            $this->orders = $obj;
        }
        return $this->orders;
    }

    /**
     * change status of invoice
     * @author mlaug
     * @param int $status
     */
    public function setStatus($status, $adminId = null) {
        if (array_key_exists($status, $this->getStatusse())) {
            $this->_data['status'] = $status;
            parent::save();
            
            //if we change the status to storno, we mark this order!!!
            if ( $status == self::STORNO ){
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
                foreach($this->getAllPdfFiles() as $pdf){            
                    if ( file_exists($pdf) ){
                        $pdfEdit = Zend_Pdf::load($pdf);
                        $page = &$pdfEdit->pages[0];
                        $page->setFont($font, 36)
                             ->setFillColor(Zend_Pdf_Color_Html::color('#9999cc'))
                             ->drawText('STORNO', 260, 550);
                        $pdfEdit->save($pdf);
                    }
                }
            }
            
            // save an entry in billing status history             
            $statusHistoryEntry = new Yourdelivery_Model_Billing_Status();
            $statusHistoryEntry->setBillingId($this->getId());
            $statusHistoryEntry->setAdminId($adminId);
            $statusHistoryEntry->setStatus($status);
            $statusHistoryEntry->save();
        }
    }

    /**
     * overwrite this create method, so that from this object no
     * bill can be created
     * @author mlaug
     * @return boolean
     */
    public function create() {
        return true;
    }

    /**
     * Send bill via fax
     * @author vpriem
     * @param string $to
     * @param int $status
     * @return boolean
     */
    public function sendViaFax($to) {

        $fax = new Yourdelivery_Sender_Fax();

        // send also additional pdf
        $pdfs = $this->getAllPdfFiles();

        $merger = new Yourdelivery_Pdf_Merger();
        foreach ($pdfs as $p) {
            $merger->addPdf($p);
        }
        $mergedFile = $merger->merge();

        $service = 'retarus';
        return (boolean) $this->_sendVia('fax', $fax->send($to, $mergedFile, $service, 'billing'), $to);
    }

    /**
     * Send bill via email
     * @author vpriem
     * @param string $to
     * @param int $status
     * @return boolean
     */
    public function sendViaEmail($to, $csv = true, $sign = false) {

        //create template
        if ($this->getMode() == "rest") {
            $email = new Yourdelivery_Sender_Email_Template('billr');
        } elseif ($this->getMode() == "upselling_goods") {
            $email = new Yourdelivery_Sender_Email_Template('upselling_goods');
        } else {
            $email = new Yourdelivery_Sender_Email_Template('billc');
        }

        $email->setFrom($this->config->locale->email->accounting);
        $email->setSubject(__('%s: Neue Rechnung', $this->config->domain->base));
        $email->assign('from', $this->getTimeFrom());
        $email->assign('until', $this->getTimeUntil());
        $email->addTo($to);

        $pdfFiles = $this->getAllPdfFiles();
        foreach ($pdfFiles as $file) {
            $email->attachPdf($file);
        }

        if ($csv) {
            $csvFiles = $this->getAllCsvFiles();
            foreach ($csvFiles as $file) {
                $email->attachCsv($file);
            }
        }
        
        //sign this pdfs
        if ( $sign === true ){
            $pdfFiles = $this->getAllPdfFiles();
            foreach ($pdfFiles as $file) {
                Yourdelivery_Sender_Email::verifyPdf($to, $file);
            }
        }

        return (boolean) $this->_sendVia('email', $email->send(), $to);
    }

    /**
     * Send bill via post
     * @author vpriem
     * @param boolean $ende
     * @return boolean
     */
    public function sendViaPost($ende = false) {
        
        $pdfMerger = new Yourdelivery_Pdf_Merger();

        // merge all pdf together
        $pdfFiles = $this->getAllPdfFiles();
        foreach ($pdfFiles as $pdfFile) {
            $pdfMerger->addPdf($pdfFile);
        }
        $pdf = $pdfMerger->merge();

        // send it out
        $post = new Yourdelivery_Sender_Post();
        $res = $post->send($pdf);
        
        // log transaction
        return (boolean) $this->_sendVia('post', $res);
    }

    /**
     * Send bill manually
     * @author vpriem
     * @since 13.09.2010
     * @return boolean
     */
    public function sendManually($adminId = null) {
        return (boolean) $this->_sendVia('manually', 1, null, $adminId);
    }

    /**
     * Send bill
     * @author alex
     * @param int $status
     * @return void
     */
    private function _sendVia($via, $status = 1, $to = null, $adminId = null) {
        
        $table = new Yourdelivery_Model_DbTable_Billing_Sent();

        $row = $table->createRow(array(
            'billingId' => $this->getId(),
            'via' => $via,
            'status' => $status
        ));

        if ($to !== null) {
            $row->to = $to;
        }

        $id = $row->save();

        //only change the state to unpayed, if not already set to payed
        if ($status) {
            if ($this->getStatus() < self::PAYED) {
                $this->setStatus(self::UNPAYED, $adminId);
            }
        }

        //send out to credit reform if this is a company bill
        if ($this->getMode() == "company") {
            $email = new Yourdelivery_Sender_Email();
            $email->addTo('gerbig@lieferando.de');
            //ignore razorfish, bbdo, freshfields
            $ignore = array(1147, 1225, 1587, 1216, 1274, 1722, 1235, 1297);
            $id = (integer) $this->getRefId();
            if (!in_array($id, $ignore)) {
                $email->addCc('a.neumann@berlin.crefo-factoring.de');
            }
            $email->setSubject(sprintf('Rechnung wurde via %s an %s am %s versand', $via, $this->getReference()->getName(), date('d.m.Y H:i:s')));
            $email->setBodyText('Rechnung im Anhang');
            
            //attach all pdf files
            $pdfFiles = $this->getAllPdfFiles();
            foreach ($pdfFiles as $file) {
                $email->attachPdf($file);
            }
            
            $email->send();
        }

        $this->storeSendFiles($id);

        return $status;
    }

    /**
     * Store send file
     * @author mlaug
     * @since 01.08.2010, 12.08.2010 (vpriem
     * @return void
     */
    protected function storeSendFiles($id) {

        // set storage
        $storage = new Default_File_Storage();
        $storage->setStorage(APPLICATION_PATH . '/../storage/');
        $storage->setSubFolder('billing/sendOut/');
        $storage->setSubFolder(substr($this->getNumber(), 2, 4));
        $storage->setTimeStampFolder();
        $storage->setSubFolder($id);
        
        $files = $this->getAllFiles();
        foreach ($files as $file) {
            $storage->store(basename($file), file_get_contents($file));
        }
    }

    /**
     * get the a item value
     * @author mlaug
     * @since 24.03.2011
     * @param double $taxtype
     * @return double 
     */
    public function getTax($taxtype = ALL_TAX) {
        $value1 = $this->getItem1Value();
        $value2 = $this->getItem2Value();
        if ($taxtype == ALL_TAX) {
            return $value1 + $value2;
        } elseif ($taxtype == $this->getItem1Key()) {
            return $value1;
        } elseif ($taxtype == $this->getItem1Key()) {
            return $value2;
        } else {
            return 0;
        }
    }

    /**
     * get the a tax value
     * @author mlaug
     * @since 24.03.2011
     * @param double $taxtype
     * @return double 
     */
    public function getItem($taxtype = ALL_TAX) {
        $value1 = $this->getTax1Value();
        $value2 = $this->getTax2Value();
        if ($taxtype == ALL_TAX) {
            return $value1 + $value2;
        } elseif ($taxtype == $this->getItem1Key()) {
            return $value1;
        } elseif ($taxtype == $this->getItem1Key()) {
            return $value2;
        } else {
            return 0;
        }
    }

    /**
     * get all cost center ids, associated with this billing
     * @since 04.01.2010
     * @author alex
     * @return array
     */
    public function getCostcenters() {
        return $this->getTable()->getCostcenters();
    }

    /**
     * create a balance according to invoice amount
     * @author mlaug
     * @since 02.11.2011
     */
    public function doBalance(){
        if ( $this->getStatus() > 1 ){
            return false;
        }
        
        $amount = (integer) (-1) * $this->getAmount();
        
        if ( $amount >= 0 ){
            return false;
        }
        
        $balance = new Yourdelivery_Model_Billing_Balance();
        $balance->setObject($this->getObject());
        $this->setStatus(self::PAYED);
        return (boolean) $balance->addBalance($amount, __('Verrechnet mit Rechnung %s', $this->getNumber()), false, $this->getId()) && 
                         $this->save();
    }
    
    /**
     * get the storage according to the current billing type
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.01.2012
     * @return Default_File_Storage 
     */
    public function getStorage(){
        $storage = new Default_File_Storage();
        $storage->setSubFolder('billing');     
        switch($this->getMode()){
            default:
                return $storage;
            case 'company':
                $storage->setSubFolder('company');
                break;
            case 'order':
                $storage->setSubFolder('order');
                break;
            case 'rest':
                $storage->setSubFolder('service');
                break;
            case 'courier':
                $storage->setSubFolder('courier');
                break;
            case 'upselling_goods':
                $storage->setSubFolder('upselling');
                break;
        }
        $storage->setSubFolder(substr($this->getNumber(),2,4));
        return $storage;
    }
}
