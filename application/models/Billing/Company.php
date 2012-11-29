<?php

/**
 * Create a billing for a company
 * @package billing
 * @category Billing
 * @author mlaug
 * @method int calculateNetto()
 * @method int calculateNetto19()
 * @method int calculateNetto7()
 * @method int calculateTax()
 * @method int calculateTax7()
 * @method int calculateTax19()
 * @method int calculateOpenAmount()
 * @method int calculateBrutto()
 * @method int calculateAlreadyPayed()
 * @method int calculateBudget()
 * @method int calculateDiscount()
 * @method int calculatePfand()
 * @method int calculatePrivateAmount()
 */
class Yourdelivery_Model_Billing_Company extends Yourdelivery_Model_Billing_Abstract {

    /**
     * mode of this bill
     * @var string
     */
    protected $_mode = 'company';

    /**
     * company model
     * @var Yourdelivery_Model_Company
     */
    protected $_company = null;

    /**
     * csv eporter
     * @var Default_Exporter_Csv
     */
    protected $_csv = null;

    /**
     * may be set to get only orders, assigned to this costcenter
     * @var Yourdelivery_Model_Department
     */
    public $_onlyCostcenter = null;

    /**
     * may be set to get only orders, assigned to this project
     * @var Yourdelivery_Model_Projectnumbers
     */
    public $_onlyProject = null;

    /**
     * all informations of on ecurrent loop (all, project, costcenter)
     * @var array
     */
    protected $_attachment = null;

    /**
     * all information of an current loop, but not formated
     * @var array
     */
    protected $_attachmentRaw = null;

    /**
     * flag if this bill should be listed verbose
     * @var boolean
     */
    public $verbose = false;

    /**
     * bucket for current orders
     * @var SplObjectStorage
     */
    public $orders = null;
    public $assets = null;

    /**
     * create a bill based on a company
     * set the time period using a starting and an ending point
     * may be used to test, without marking orders billed using the $test flag
     *
     * instead of defining the period, may use mode to create a period (month, 2 weeks)
     *
     * @param Yourdelivery_Model_Company $company
     * @param int $from
     * @param int $until
     * @param string $mode
     * @param boolean $test
     * @author mlaug
     */
    public function __construct(Yourdelivery_Model_Company $company, $from = 0, $until = 0, $mode = null, $test = 0) {

        parent::__construct();
        $this->setPeriod($from, $until, $mode);
        $this->setCompany($company);
        $this->hasBeenWarnedAbout = array();
    }

    /**
     * check all orders, which would be associated with this bill
     * to verify, that they are not corrupted. if we find an error
     * we send out an email to the admins
     * @author mlaug
     * @since 20.03.2011
     */
    public function preflyCheck() {
        $warnings = array();
        foreach ($this->getOrders() as $order) {

            $orderId = $order->getId();

            $addition = null;
            foreach ($order->getCompanyGroupMembers() as $b) {

                $customer = $b[0];
                $budget = (integer) $b[1] + (integer) $b[6];

                $payment = $b[2];
                $privAmount = (integer) $b[3];
                $codeId = (integer) $b[4];
                $costcenterId = (integer) $b[5];
                $project = $b[7];
                $addition = !empty($b[8]) ? $b[8] : $addition;
                $companyId = $b[9];
                unset($b);


                $brutto = $budget;
                if ($order->getCustomer()->getId() == $customer->getId()) {
                    //put not courier discont here!!!
                    $discount = $order->getDiscountAmount();
                    $brutto += $discount;
                } else {
                    $discount = 0;
                }

                $entire_brutto = $order->getTotal()
                        + $order->getServiceDeliverCost()
                        + $order->getCourierCost()
                        - $order->getCourierDiscount();

                $perc = $brutto / $entire_brutto;

                if ($perc > 1) {
                    $warnings[] = sprintf('Ratio over 1 in %d', $order->getId());
                }
            }
        }
        parent::preflyCheck($warnings);
    }

    /**
     * capsulate all calculation methods in the call method 
     * this should help to create more overview and use dynamic progamming style
     * more effecient
     * @author mlaug
     * @since 19.08.2010
     * @param string $func
     * @param array $args
     * @return int
     */
    public function __call($func, $args) {

        if (array_key_exists($func, $this->_remember)) {
            return $this->_remember[$func];
        }

        $total = 0;
        $attachment = $this->getAttachment($this->getOnlyProject(), $this->getOnlyCostcenter(), true);
        switch ($func) {
            default: {
                    return parent::__call($func, $args);
                }

            /**
             * @author mlaug
             * calculate those and remove the cent error 
             */
            case 'calculateTax':
            case 'calculateTax19':
            case 'calculateTax7':
            case 'calculateNetto':
            case 'calculateNetto19':
            case 'calculateItem19':
            case 'calculateNetto7':
            case 'calculateItem7': {

                    $netto7 = 0;
                    foreach ($attachment as $a) {
                        $netto7 += $a['Netto_7'];
                    }
                    $netto19 = 0;
                    foreach ($attachment as $a) {
                        $netto19 += $a['Netto_19'];
                    }

                    $tax19 = 0;
                    foreach ($attachment as $a) {
                        $tax19 += $a['Steuern_19'];
                    }
                    $tax7 = 0;
                    foreach ($attachment as $a) {
                        $tax7 += $a['Steuern_7'];
                    }


                    //match up the numbers in the final calculation
                    $netto7 = round($netto7);
                    $netto19 = round($netto19);

                    $tax7 = round($tax7);
                    $tax19 = round($tax19);

                    $netto = $netto19 + $netto7;
                    $tax = $tax7 + $tax19;

                    $brutto = $this->getBrutto();

                    //balancing
                    if (($tax7 + $tax19 + $netto19 + $netto7) != $brutto) {
                        $diff = $brutto - $tax7 - $tax19 - $netto19 - $netto7;
                        if ($netto7 > 0) {
                            $netto7 += $diff;
                        } else {
                            $netto19 += $diff;
                        }
                        $netto += $diff;
                    }

                    $this->_remember['calculateTax'] = $tax;
                    $this->_remember['calculateTax7'] = $tax7;
                    $this->_remember['calculateTax19'] = $tax19;
                    $this->_remember['calculateNetto'] = $netto;
                    $this->_remember['calculateNetto7'] = $netto7;
                    $this->_remember['calculateNetto19'] = $netto19;
                    $this->_remember['calculateItem7'] = $netto7;
                    $this->_remember['calculateItem19'] = $netto19;

                    return $this->_remember[$func];
                }

            case 'calculateBudget': {
                    foreach ($attachment as $a) {
                        $total += $a['Budget'];
                    }
                    break;
                }

            case 'calculatePrivateAmount': {
                    foreach ($attachment as $a) {
                        $total += $a['Privat_bezahlt'];
                    }
                    break;
                }

            case 'calculatePfand': {
                    foreach ($attachment as $a) {
                        $total += $a['Pfand'];
                    }
                    break;
                }

            case 'calculatePfandNetto': {
                    foreach ($attachment as $a) {
                        $total += $a['PfandNetto'];
                    }
                    break;
                }

            case 'calculatePfandSteuern': {
                    foreach ($attachment as $a) {
                        $total += $a['PfandSteuern'];
                    }
                    break;
                }

            case 'calculatePfandBrutto': {
                    return $this->calculatePfandNetto() + $this->calculatePfandSteuern();
                }

            case 'calculateDiscount': {
                    foreach ($attachment as $a) {
                        $total += $a['Discount'];
                    }
                    break;
                }

            case 'getBrutto':
            case 'calculateBrutto':
            case 'calculateBruttoSumme': {
                    foreach ($attachment as $a) {
                        $total += $a['Brutto_Summe'];
                    }
                    break;
                }

            case 'calculateAlreadyPayed': {
                    $total = $this->calculatePfand() + $this->calculateDiscount();
                    break;
                }

            case 'calculateOpenAmount': {
                    $total = $this->getBrutto() - $this->calculateAlreadyPayed();
                    break;
                }
        }

        $this->_remember[$func] = $total;
        return $total;
    }

    /**
     * get reference company
     * @author mlaug
     * @return Yourdelivery_Model_Company
     */
    public function getCompany() {
        return $this->_company;
    }

    /**
     * set company
     * @author mlaug
     * @param Yourdelivery_Model_Company $company
     */
    public function setCompany(Yourdelivery_Model_Company $company) {
        $this->_company = $company;
    }

    /**
     * return the object associated to this bill
     * @author mlaug
     * @return Yourdelivery_Model_Comapany
     */
    public function getObject() {
        return $this->getCompany();
    }

    /**
     * get corresponding billing number, based on starting date,
     * customer number and next billing number
     * @author mlaug
     * @param Yourdelivery_Model_Project|Yourdelivery_Model_Department $addition
     * @return string
     */
    public function getNumber($addition=null) {

        if (is_null($addition)) {
            $addition = $this->getOnlyProject();
            if (is_null($addition)) {
                $addition = $this->getOnlyCostcenter();
            }
        }

        if (is_object($addition)) {
            $add = "-" . $addition->getId();
        } else {
            $add = "";
        }

        if (is_null($this->number)) {
            $nextNumber = $this->getTable()->getNextBillingNumber($this->getCompany(), 'comp');
            $this->number = "R-" . date('y', $this->from) . date('m', $this->from) . "-" . $this->getCompany()->getCustomerNr() . "-" . $nextNumber;
        }

        return $this->number . $add;
    }

    /**
     * get all assets
     * @author mlaug
     * @since 29.10.2010
     * @return array
     */
    public function getBillingAssets() {

        $assets = array();

        //if any project or costcenter is set we do not get our assets
        if (is_object($this->getOnlyCostcenter()) || is_object($this->getOnlyProject())) {
            return $assets;
        }

        if ($this->assets instanceof SplObjectStorage) {
            return $this->assets;
        }

        $assetsTable = new Yourdelivery_Model_DbTable_BillingAsset();
        $select = $assetsTable->select()->where('billCompany is Null or billCompany<=0');
        $rows = $this->getCompany()
                ->getTable()
                ->getCurrent()
                ->findDependentRowset('Yourdelivery_Model_DbTable_BillingAsset', null, $select)
                ->rewind();

        foreach ($rows as $row) {

            $assetId = (integer) $row['id'];
            if (is_array($this->old_assets) && count($this->old_assets) > 0 && !in_array($assetId, $this->old_assets)) {
                $this->logger->debug(sprintf('Ignoring asset %d, this has not been in this bill before', $assetId));
                continue;
            }

            $asset = new Yourdelivery_Model_BillingAsset($assetId);
            //must be in between time slot
            if (strtotime($asset->getTimeFrom()) > $this->until) {
                $this->logger->debug(sprintf('Ignoring asset %d, wrong time', $assetId));
                continue;
            }

            $assets[] = $asset;
            if ($this->getCompany()->getBillMode() == Yourdelivery_Model_Billing::BILL_PER_TRANSACTION) {
                break;
            }
        }
        $this->assets = $assets;
        return $assets;
    }

    /**
     * this is kind of sad, since we need this function as a helper
     * to get the correct orders for the long attachment ...
     * @author mlaug
     * @since 27.10.2011
     * @return array
     */
    public function getOrdersByAttachment($orderByCustomer = false) {
        $attachment = $this->getAttachment($this->getOnlyProject(), $this->getOnlyCostcenter(), true);
        if ($orderByCustomer) {
            usort($attachment, array("self", "_orderByCustomerName"));
        }
        $orders = array();
        foreach ($attachment as $a) {
            try {
                $orderId = (integer) $a['ID'];
                if ($orderId <= 0) {
                    continue;
                }
                // Only one row per order
                if (!$orders['#'.$a['ID']]) {
                    $orders['#'.$a['ID']] = new Yourdelivery_Model_Order($orderId);
                    if ($orders['#'.$a['ID']]->getCustomer()->getFullname() != $a['Mitarbeiter']) {
                        $orders['#'.$a['ID']]->setEmployees($a['Mitarbeiter']);
                    }
                } elseif ($orders['#'.$a['ID']]->getCustomer()->getFullname() != $a['Mitarbeiter']) {
                    $employees = ($orders['#'.$a['ID']]->getEmployees()? $orders['#'.$a['ID']]->getEmployees() . ', ' : '') . $a['Mitarbeiter'];
                    $orders['#'.$a['ID']]->setEmployees($employees);
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
        return $orders;
    }
    
    /**
     * Helper to usort by "Sortierung"
     * @author Jens Naie <naie@lieferando.de>
     * @since 31.07.2012
     * @return int
     */
    private static function _orderByCustomerName($a, $b) {
        $al = strtolower($a['Sortierung']);
        $bl = strtolower($b['Sortierung']);
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1; 
    }

    /**
     * get all orders in a certian time range
     * @author mlaug
     * @param boolean $all
     * @return array
     */
    public function getOrders() {

        if (is_null($this->orders)) {
            $db = $this->getTable()->getAdapter();

            $table = new Yourdelivery_Model_DbTable_Order();
            $select = $table->select('id')
                    ->where('companyId=?', $this->getCompany()->getId())
                    ->where('(billCompany=0 OR billCompany IS NULL)')
                    ->where('state > 0');

            $rows = $select->query();
            $orders = array();
            foreach ($rows as $o) {
                try {
                    $orderId = (integer) $o['id'];
                    if (is_array($this->old_orders) && count($this->old_orders) > 0 && !in_array($orderId, $this->old_orders)) {
                        $this->logger->debug(sprintf('Ignoring order %d, this has not been in this bill before', $orderId));
                        continue;
                    }

                    try {
                        $order = new Yourdelivery_Model_Order($orderId);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->logger->debug(sprintf('Ignoring order %d, could not be initialized', $orderId));
                        continue;
                    }

                    //check if we have bill payment
                    if ($order->getPayment() != 'bill') {
                        $this->logger->debug(sprintf('Ignoring order %d, payment is not bill', $orderId));
                        continue;
                    }

                    //if great, must be confirmed
                    if ($order->getService()->isAcceptsPfand() && $order->getState() != 2) {
                        $this->logger->info(sprintf('Ignoring order %d because of state %d and need no pfand yet set', $orderId, $order->getState()));
                        continue;
                    }

                    //must not be cancled
                    if ($order->getState() < 0) {
                        $this->logger->debug(sprintf('Ignoring order %d, wrong Status %d', $orderId, $order->getState()));
                        continue;
                    }

                    //must be in between time slot
                    if ($order->getDeliverTime() > $this->until) {
                        $this->logger->debug(sprintf('Ignoring order %d, wrong time', $orderId));
                        continue;
                    }

                    if ($this->getCompany()->getBillMode() == Yourdelivery_Model_Billing::BILL_PER_TRANSACTION) {
                        //check if no billing assets are found
                        if (count($this->getBillingAssets()) == 0) {
                            $orders[] = $order;
                        }
                        break; //but break in every case
                    } else {
                        $orders[] = $order;
                    }
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->logger->crit(sprintf('Something went wrong checking an order: %s', $e->getMessage()));
                }
            }

            $this->orders = $orders;
        }

        return $this->orders;
    }

    /**
     * get costcenters and add no costcenters string
     * @since 01.09.2010
     * @author mlaug
     * @return array
     */
    public function getCostcenters() {
        $costs = $this->getCompany()->getCostcenters();
        $list = array();
        foreach ($costs as $cost) {
            $list[] = $cost;
        }
        array_unshift($list, 'nocostcenter');
        return $list;
    }

    /**
     * get projects and add no projects string
     * @author mlaug
     * @return array
     */
    public function getProjectNumbers() {
        $projects = $this->getCompany()->getProjectNumbers();
        $list = array();
        foreach ($projects as $project) {
            $list[] = $project;
        }
        array_unshift($list, 'noproject');
        return $list;
    }

    /**
     * set current project or 'noproject'
     * @author mlaug
     * @param mixed string|Yourdelivery_Model_Projectnumbers $project
     */
    public function setOnlyProject($project) {
        $this->_onlyProject = $project;
        $this->_attachment = null;
        $this->_attachmentRaw = null;
        $this->_remember = array();
    }

    /**
     * @author mlaug
     * @return Yourdelivery_Model_Project
     */
    public function getOnlyProject() {
        return $this->_onlyProject;
    }

    /**
     * set current project
     * @author mlaug
     * @param mixed string|Yourdelivery_Model_Department $costcenter
     */
    public function setOnlyCostcenter($costcenter) {
        $this->_onlyCostcenter = $costcenter;
        $this->_attachment = null;
        $this->_attachmentRaw = null;
        $this->_remember = array();
    }

    /**
     * @author mlaug
     * @return Yourdelivery_Model_Department
     */
    public function getOnlyCostcenter() {
        return $this->_onlyCostcenter;
    }

    /**
     * check if any orders apply
     * @since 06.10.2010
     * @author mlaug
     * @return boolean
     */
    public function hasOrders() {
        return count(
                        $this->getAttachment(
                                $this->getOnlyProject(), $this->getOnlyCostcenter(), true)
                ) > 0 ? true : false;
    }

    /**
     * get the list of the current order with all its information
     * @author mlaug
     * @param Yourdelivery_Model_Project $check_project
     * @param Yourdelivery_Model_Project $check_costcenter
     * @return array
     */
    public function getAttachment($check_project=null, $check_costcenter=null, $raw=false) {

        //may be this has been cached
        if ($raw && !is_null($this->_attachmentRaw)) {
            return $this->_attachmentRaw;
        }

        if (!$raw && !is_null($this->_attachment)) {
            return $this->_attachment;
        }

        $rows = array();

        $assets = $this->getBillingAssets();
        foreach ($assets as $asset) {

            $usesTaxes = $asset->getMwst();
            $budget = 0;
            $netto7 = 0;
            $netto19 = 0;
            $tax7 = 0;
            $tax19 = 0;
            $brutto = 0;

            //calculate amounts for this asset (netto and brutto)
            switch ($usesTaxes) {
                case 7: {
                        $netto19 = 0;
                        $tax19 = 0;
                        $netto7 = $asset->getTotal();
                        $tax7 = $netto7 * 0.07;
                        $brutto = $budget = $netto7 + $tax7;
                        break;
                    }

                case 19: {
                        $netto7 = 0;
                        $tax7 = 0;
                        $netto19 = $asset->getTotal();
                        $tax19 = $netto19 * 0.19;
                        $brutto = $budget = $netto19 + $tax19;
                        break;
                    }
            }


            $rows[] = array(
                'ID' => null,
                'Nr' => null,
                'Mitarbeiter' => $asset->getCompany()->getName(),
                'Sortierung' => 0,
                'Bestellart' => 'Rechnungsposten',
                'Dienstleister' => $asset->getService()->getName(),
                'DienstleisterKundennummer' => $asset->getService()->getCustomerNr(),
                'Bestellung_um' => date(__('d.m.Y'), strtotime($asset->getTimeFrom())),
                'Lieferung_um' => date(__('d.m.Y'), strtotime($asset->getTimeFrom())),
                'Budget' => $raw ? $budget : intToPrice($budget),
                'Privat_bezahlt' => 0,
                'Netto_7' => $raw ? $netto7 : intToPrice($netto7, 5),
                'Netto_19' => $raw ? $netto19 : intToPrice($netto19, 5),
                'Netto_Summe' => $raw ? ($netto7 + $netto19) : intToPrice(($netto7 + $netto19), 5),
                'Steuern_7' => $raw ? $tax7 : intToPrice($tax7, 5),
                'Steuern_19' => $raw ? $tax19 : intToPrice($tax19, 5),
                'Steuern_Summe' => $raw ? ($tax7 + $tax19) : intToPrice(($tax7 + $tax19), 5),
                'Brutto_Summe' => $raw ? ($brutto) : intToPrice(($brutto), 2),
                'Pfand' => 0,
                'PfandNetto' => 0,
                'PfandSteuern' => 0,
                'Discount' => 0,
                'Projekt' => null,
                'Kostenstelle' => null,
                'Rechnungsnummer' => $this->getNumber()
            );
        }

        $orders = $this->getOrders();
        foreach ($orders as $order) {

            $orderId = $order->getId();

            $addition = null;
            foreach ($order->getCompanyGroupMembers() as $b) {

                $customer = $b[0];
                $budget = (integer) $b[1] + (integer) $b[6];

                $payment = $b[2];
                $privAmount = (integer) $b[3];
                $codeId = (integer) $b[4];
                $costcenterId = (integer) $b[5];
                $project = $b[7];
                $addition = !empty($b[8]) ? $b[8] : $addition;
                $companyId = $b[9];
                unset($b);

                //check for company (buggy before: FUCK)
                if ($companyId != $this->getCompany()->getId()) {
                    continue;
                }

                if (is_object($project)) {
                    $projectnumber = $project->getNumber() . ' ' . $addition;
                } else {
                    $projectnumber = "";
                }

                $dep = null;
                if (!is_null($costcenterId) && !empty($costcenterId) && $costcenterId > 0) {
                    try {
                        $dep = new Yourdelivery_Model_Department($costcenterId);
                        $costcenter = $dep->getName() . " " . $addition;
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $costcenter = null;
                    }
                } else {
                    $costcenter = "";
                }

                //check for corresponding costcenter
                if (!is_null($check_costcenter) && is_object($check_costcenter)) {
                    if ($costcenterId != $check_costcenter->getId()) {
                        continue;
                    }
                }

                //check for corresponding project
                if (!is_null($check_project) && is_object($check_project)) {
                    if ($project->getId() != $check_project->getId()) {
                        continue;
                    }
                }

                //check for projects, if no project should be
                if (is_string($check_project) && $check_project == 'noproject' && is_object($project) && $project->isPersistent()) {
                    continue;
                }

                //check for costcenters if no costcenter should be
                if (is_string($check_costcenter) && $check_costcenter == 'nocostcenter' && is_object($dep) && $dep->isPersistent()) {
                    continue;
                }

                $pfand = $order->getPfand();
                $pfandSteuern = $pfand - ( $pfand / (100 + 19) * 100 );
                $pfandNetto = $pfand - $pfandSteuern;


                $netto19 = 0;
                $netto7 = 0;
                $tax19 = 0;
                $tax7 = 0;

                $brutto = $budget;
                if ($order->getCustomer()->getId() == $customer->getId()) {
                    //put not courier discont here!!!
                    $discount = $order->getDiscountAmount();
                    $brutto += $discount;
                } else {
                    $discount = 0;
                }

                $entire_brutto = $order->getTotal()
                        + $order->getServiceDeliverCost()
                        + $order->getCourierCost()
                        - $order->getCourierDiscount();

                $perc = $brutto / $entire_brutto;

                $entire_netto19 = $order->getItem(19);
                $entire_netto7 = $order->getItem(7);
                $entire_tax19 = $order->getTax(19);
                $entire_tax7 = $order->getTax(7);

                $netto19 = ($entire_netto19 * $perc);
                $netto7 = ($entire_netto7 * $perc);
                $tax19 = ($entire_tax19 * $perc);
                $tax7 = ($entire_tax7 * $perc);
                $check_brutto = $netto19 + $netto7 + $tax19 + $tax7;

                if (round($check_brutto) != round($brutto) && !in_array($order->getId(), $this->hasBeenWarnedAbout)) {
                    $this->hasBeenWarnedAbout[] = $order->getId();
                    $msg = sprintf('Daten inkonsistenz bei Budget in Bestellung
                                         %d: %d <=> %d by Ratio: %f resultierend aus %d/%d (%d,%d,%d,%d)', $order->getId(), $check_brutto, $brutto, $perc, $budget, $entire_brutto, $entire_netto7, $entire_tax7, $entire_netto19, $entire_tax19);
                    Yourdelivery_Sender_Email::quickSend('Please check: ' . $this->getNumber(), $msg, null, 'springer@lieferando.de');
                }

                $rows[] = array(
                    'ID' => $order->getId(),
                    'Nr' => $order->getNr(),
                    'Mitarbeiter' => $customer->getFullname(),
                    'Sortierung' => $customer->getName().$customer->getPrename().$customer->getId(),
                    'Bestellart' => $order->getModeReadable(),
                    'Dienstleister' => $order->getService()->getName(),
                    'DienstleisterKundennummer' => $order->getService()->getCustomerNr(),
                    'Bestellung_um' => date(__('H:i d.m.Y'), $order->getTime()),
                    'Lieferung_um' => $order->getTime() >= $order->getDeliverTime() ? __("sofort") : date(__('H:i d.m.Y'), $order->getDeliverTime()),
                    'Budget' => $raw ? $budget : intToPrice($budget),
                    'Privat_bezahlt' => $raw ? $privAmount : intToPrice($privAmount),
                    'Netto_7' => $raw ? $netto7 : intToPrice($netto7, 5),
                    'Netto_19' => $raw ? $netto19 : intToPrice($netto19, 5),
                    'Netto_Summe' => $raw ? ($netto7 + $netto19) : intToPrice(($netto7 + $netto19), 5),
                    'Steuern_7' => $raw ? $tax7 : intToPrice($tax7, 5),
                    'Steuern_19' => $raw ? $tax19 : intToPrice($tax19, 5),
                    'Steuern_Summe' => $raw ? ($tax7 + $tax19) : intToPrice(($tax7 + $tax19), 5),
                    'Brutto_Summe' => $raw ? ($brutto) : intToPrice(($brutto), 2),
                    'Pfand' => $raw ? $pfand : intToPrice($pfand),
                    'PfandNetto' => $raw ? $pfandNetto : intToPrice($pfandNetto),
                    'PfandSteuern' => $raw ? $pfandSteuern : intToPrice($pfandSteuern),
                    'Discount' => $raw ? $discount : intToPrice($discount),
                    'Projekt' => $projectnumber,
                    'Kostenstelle' => $costcenter,
                    'Rechnungsnummer' => $this->getNumber($dep)
                );
            }
        }

        if ($raw) {
            $this->_attachmentRaw = $rows;
        } else {
            $ths->_attachment = $rows;
        }

        return $rows;
    }

    /**
     * create csv from all orders
     * @author mlaug
     * @return string
     */
    public function createCSV() {

        $this->_attachment = null;
        $this->_attachmentRaw = null;

        //prepare CSV
        $this->_csv = new Default_Exporter_Csv();
        $this->_csv->addCol('ID');
        $this->_csv->addCol('Nr');
        $this->_csv->addCol('Mitarbeiter');
        $this->_csv->addCol('Sortierung');
        $this->_csv->addCol('Bestellart');
        $this->_csv->addCol('Dienstleister');
        $this->_csv->addCol('DienstleisterKundennummber');
        $this->_csv->addCol('Bestellung_um');
        $this->_csv->addCol('Lieferung_um');

        $this->_csv->addCol('Budget');
        $this->_csv->addCol('Privat_bezahlt');

        $this->_csv->addCol('Netto_7');
        $this->_csv->addCol('Netto_19');
        $this->_csv->addCol('Netto_Summe');

        $this->_csv->addCol('Steuern_7');
        $this->_csv->addCol('Steuern_19');
        $this->_csv->addCol('Steuern_Summe');

        $this->_csv->addCol('Brutto_Summe');

        $this->_csv->addCol('Pfand');
        $this->_csv->addCol('PfandNetto');
        $this->_csv->addCol('PfandSteuern');
        $this->_csv->addCol('Discount');

        $this->_csv->addCol('Projekt');
        $this->_csv->addCol('Kostenstelle');
        $this->_csv->addCol('Rechnungsnummer');


        foreach ($this->getAttachment() as $c) {
            $this->_csv->addRow($c);
        }

        return $this->_csv->save();
    }

    /**
     * Generate the bill for a given company. We will check if this is just a prefly check ($test)
     * and if this bill will be regenerated. If so, we will provide the current row, where the
     * bill is stored in and all the orders and assets which have been billed before. Only those 
     * will be used for a regenerated bill, to avoid systematic change in bill!
     * @author mlaug
     * @param boolean $test
     * @param Zend_Db_Table_Row_Abstract $row
     * @param array $orders
     * @param array $assets
     * $param boolean $createPdf
     * @return boolean
     */
    public function create($test = false, $row = null, array $orders = array(), array $assets = array(), $createPdf = true, $checkForZeroOrder = true, $crefo = true) {
        try {

            //reset for once
            $this->orders = null;
            $this->old_orders = $orders;
            $this->old_assets = $assets;

            $company = $this->getCompany();

            //reset costcenters and projects to get total amounts
            $this->setOnlyCostcenter(null);
            $this->setOnlyProject(null);

            if (!is_object($company)) {
                return false;
            }

            if ($this->until >= time()) {
                $this->logger->info(sprintf('Current time does not match billing interval Until: %s Current: %s', date('d.m.Y', $this->until), date('d.m.Y', time())));
                return false;
            }

            if ($checkForZeroOrder && count($this->getOrders()) == 0 && count($this->getBillingAssets()) == 0) {
                $this->logger->info(sprintf('No orders found for company %s #%d', $company->getName(), $company->getId()));
                $this->info(__('Für diese Zeitraum wurden keine Bestellungen gefunden'));
                return false;
            }

            $this->logger->debug(sprintf('Creating bill for company %s #%d', $company->getName(), $company->getId()));
            $this->preflyCheck();

            //generate bill
            $customized = $this->getCustomized();
            $this->_latex->assign('comp', $company);
            $this->_latex->assign('bill', $this);
            $this->_latex->assign('header', $customized); //deprecated
            $this->_latex->assign('custom', $customized);
            $this->_latex->assign('crefo', $crefo);

            switch ($customized['template']) {

                default: {
                        $this->_latex->setTpl('bill/company/standard');
                        break;
                    }

                case 'standard': {
                        $this->_latex->setTpl('bill/company/standard');
                        break;
                    }

                case 'simple': {
                        $this->_latex->setTpl('bill/company/simple');
                        break;
                    }

                case 'project': {
                        $this->_latex->setTpl('bill/company/project');
                        break;
                    }

                case 'costcenter': {
                        $this->_latex->setTpl('bill/company/costcenter');
                        break;
                    }
            }

            if ($customized['verbose']) {
                $this->verbose = true;
            }

            //compile pdf
            $foundFile = true;
            if ($createPdf) {
                $file = $this->_latex->compile(true, ($customized['template'] != 'simple'));
                $foundFile = file_exists($file);
            }

            $this->setOnlyCostcenter(null);
            $this->setOnlyProject(null);
                    
            $this->_storage = new Default_File_Storage();
            $this->_storage->setSubFolder('billing');
            $this->_storage->setSubFolder('company');
            $this->_storage->setSubFolder(substr($this->getNumber(),2,4));

            if (!$foundFile) {
                $this->logger->err('Could not create pdf');
                return false;
            }

            if ($createPdf) {
                $this->_storage->store($this->getNumber() . '.pdf', file_get_contents($file), null, true);
                $csvfile = $this->createCSV();
                $this->_storage->store($this->getNumber() . '.csv', file_get_contents($csvfile), null, true);
            }

            //store in database
            if (is_null($row) || !$row instanceof Zend_Db_Table_Row_Abstract) {
                $row = $this->getTable()->createRow();
                $row->status = 0; //do not reset status, so only set here
            }

            $row->mode = 'company';
            $row->from = date('Y-m-d H:i:s', $this->from);
            $row->until = date('Y-m-d H:i:s', $this->until);
            $row->number = $this->getNumber();

            //base values (should be the sum of all sub billings, if avilable)
            $row->brutto = (integer) $this->getBrutto();
            $row->item1Value = (float) $this->calculateItem7();
            $row->item2Value = (float) $this->calculateItem19();
            $row->tax1Value = (float) $this->calculateTax7();
            $row->tax2Value = (float) $this->calculateTax19();
            $row->item1Key = 7;
            $row->item2Key = 19;

            $row->discount = (integer) $this->calculateDiscount();
            $row->pfand = (integer) $this->calculatePfand();
            $row->amount = (integer) $this->calculateOpenAmount();

            //set reference id
            $row->refId = $company->getId();

            //default values (only used for restaurant)
            $row->prov = 0;
            $row->voucher = 0;

            $billId = $row->save();
            if (!$billId) {
                return false;
            }

            try {

                $sub = new Yourdelivery_Model_DbTable_Billing_Sub();

                //create sub billings for each costcenter
                $this->_storage->setSubFolder($this->getNumber(), false);

                //create seperate bills for each costcenter
                foreach ($this->getCostcenters() as $c) {

                    $this->setOnlyCostcenter($c);

                    //next one, please
                    if (!$this->hasOrders()) {
                        continue;
                    }

                    $row = $sub->createRow();

                    $cId = is_object($c) ? (integer) $c->getId() : 0;
                    if ($createPdf) {
                        if ($customized['costcenterSub']) {
                            $this->_latex->setTpl('bill/company/costcenter/single');

                            //compile and save in a subdirectory
                            $cFile = $this->_latex->compile(true, true);
                            if (file_exists($cFile)) {
                                //only create if we need it
                                $this->_storage->createCurrentFolder();
                                $this->_storage->store($this->getNumber() . '.pdf', file_get_contents($cFile), null, true);
                            } else {
                                $save = false;
                            }
                        }
                    }

                    $row->brutto = (integer) $this->getBrutto();
                    $row->item1Value = (float) $this->calculateItem7();
                    $row->item2Value = (float) $this->calculateItem19();
                    $row->tax1Value = (float) $this->calculateTax7();
                    $row->tax2Value = (float) $this->calculateTax19();
                    $row->item1Key = 7;
                    $row->item2Key = 19;

                    $row->discount = (integer) $this->calculateDiscount();
                    $row->pfand = (integer) $this->calculatePfand();
                    $row->amount = (integer) $this->calculateOpenAmount();

                    $row->billingId = $billId;
                    $row->costcenterId = $cId;
                    $row->save();

                    unset($row);
                }


                // create seperate bills for each costcenter
                // one costcenter is "nocostcenter"
                if (count($this->getCostcenters()) == 1) {
                    foreach ($this->getProjectNumbers() as $p) {

                        $this->setOnlyProject($p);

                        //next one, please
                        if (!$this->hasOrders()) {
                            continue;
                        }

                        $row = $sub->createRow();

                        $pId = is_object($p) ? (integer) $p->getId() : 0;

                        //compile and save in a subdirectory
                        if ($createPdf) {
                            if ($customized['projectSub']) {
                                $this->_latex->setTpl('bill/company/project/single');
                                $cFile = $this->_latex->compile(true, true);
                                if (file_exists($cFile)) {
                                    //only create if we need it
                                    $this->_storage->createCurrentFolder();
                                    $this->_storage->store($this->getNumber() . '.pdf', file_get_contents($cFile), null, true);
                                }
                            }
                        }

                        $row->brutto = (integer) $this->getBrutto();
                        $row->item1Value = (float) $this->calculateItem7();
                        $row->item2Value = (float) $this->calculateItem19();
                        $row->tax1Value = (float) $this->calculateTax7();
                        $row->tax2Value = (float) $this->calculateTax19();
                        $row->item1Key = 7;
                        $row->item2Key = 19;

                        $row->amount = (integer) $this->calculateOpenAmount();
                        $row->discount = (integer) $this->calculateDiscount();
                        $row->pfand = (integer) $this->calculatePfand();

                        $row->billingId = $billId;
                        $row->projectId = $pId;
                        $row->save();

                        unset($row);
                    }
                }
            } catch (Exception $e) {
                $this->logger->err('Could not create sub billing: ' . $e->getMessage());
                return false;
            }

            $this->setOnlyCostcenter(null);
            $this->setOnlyProject(null);

            //append all orders to this bill
            $orders = $this->getOrders();
            foreach ($orders as $order) {
                $order->billMe($billId, 'company');
            }

            $assets = $this->getBillingAssets();
            foreach ($assets as $asset) {
                $asset->billMe($billId, 'company');
            }

            return true;
        } catch (Exception $e) {
            if (APPLICATION_ENV == "production") {
                $this->logger->err($e->getMessage() . $e->getTraceAsString());
                Yourdelivery_Sender_Email::error($e->getMessage() . $e->getTraceAsString(), true);
                return false;
            } else {
                throw $e;
            }
        }
    }

}
