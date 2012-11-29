<?php

/**
 * Create a bill for any existing assets
 * for this current interval. the interval will not be defined here
 * since the parent bill will append all needed assets here and create 
 * those
 *
 * @author mlaug
 */
class Yourdelivery_Model_Billing_Restaurant_Assets extends Yourdelivery_Model_Billing_Abstract {

    protected $_assets = null;

    /**
     * @author mlaug
     * @param Yourdelivery_Model_Servicetype_Abstract $service
     * @param int $from
     * @param int $until
     * @param string $mode
     * @param boolean $test
     */
    public function __construct(Yourdelivery_Model_Servicetype_Abstract $service, $from = 0, $until = 0, $mode = null, $test = 0) {
        parent::__construct();
        $this->setPeriod($from, $until, $mode);
        $this->setService($service);
    }

    /**
     * @todo implement for assets
     * @author mlaug
     * @since 05.04.2011
     * @param array $warnings
     * @return boolean
     */
    public function preflyCheck($warnings = array()) {
        return true;
    }

    /**
     * set service
     * @author mlaug
     * @param Yourdelivery_Model_Servicetype_Abstract $service
     */
    public function setService($service = null) {
        switch ($service->getId()) {
            default: {
                    $this->_latex->setTpl('bill/service/standard_asset');
                    break;
                }
        }
        $this->_service = $service;
    }

    public function getService() {
        return $this->_service;
    }

    /**
     * get all assets
     * @author mlaug
     * @since 29.10.2010
     * @return SplObjectStorage
     */
    public function getBillingAssets() {
        if ($this->_assets === null) {
            $assetsTable = new Yourdelivery_Model_DbTable_BillingAsset();
            $select = $assetsTable->select()->where('billRest is Null or billRest<=0');
            $rows = $this->getService()
                            ->getTable()
                            ->getCurrent()
                            ->findDependentRowset('Yourdelivery_Model_DbTable_BillingAsset', null, $select);
            $assets = new SplObjectStorage();

            foreach ($rows as $row) {

                $assetId = (integer) $row['id'];
                if (is_array($this->old_assets) && count($this->old_assets) > 0 && !in_array($assetId, $this->old_assets)) {
                    $this->logger->debug(sprintf('Ignoring asset %d, this has not been in this bill before', $assetId));
                    continue;
                }

                if (strtotime($row['timeUntil']) > $this->until) {
                    $this->logger->debug(sprintf('Ignoring asset %d, not in this time interval %s - %s', $assetId, $row['timeFrom'], $row['timeUntil']));
                    continue;
                }

                $assets->attach(new Yourdelivery_Model_BillingAsset($row['id']));
            }

            $this->_assets = $assets;
        }
        return $this->_assets;
    }

    /**
     * get corresponding billing number, based on starting date,
     * customer number and next billing number
     * @author mlaug
     * @return string
     */
    public function getNumber() {
        if (is_null($this->number)) {
            $number = $this->getTable()->getNextBillingNumber($this->getService(), 'rest');
            $this->number = "A-" . date('y', $this->from) . date('m', $this->from) . "-" . $this->getService()->getCustomerNr() . "-" . $number;
        }
        return $this->number;
    }

    /**
     * @author mlaug
     * @since 18.03.0211
     * @param float $tax 
     */
    public function calculateItem($tax = ALL_TAX) {
        $total = 0;
        foreach ($this->getBillingAssets() as $asset) {
            if ($tax == ALL_TAX || $asset->getMwst() == $tax) {
                $total += $asset->getTotal();
            }
        }
        return $total;
    }

    /**
     * @author mlaug
     * @since 18.03.0211
     * @param float $tax 
     */
    public function calculateTax($tax = ALL_TAX) {
        $total = 0;
        foreach ($this->getBillingAssets() as $asset) {
            if ($tax == ALL_TAX || $asset->getMwst() == $tax) {
                $total += ( $asset->getTotal() / 100) * $asset->getMwst();
            }
        }
        return $total;
    }

    /**
     * @author mlaug
     * @since 18.03.2011
     * @return int
     */
    public function getBrutto() {
        $total = 0;
        foreach ($this->getBillingAssets() as $asset) {
            $total += $asset->getBrutto();
        }
        return $total;
    }

    /**
     * @author mlaug
     * @since 18.03.2011
     * @return int
     */
    public function getCommTotal($tax = ALL_TAX) {
        $total = 0;
        foreach ($this->getBillingAssets() as $asset) {
            if ($tax == ALL_TAX || $asset->getMwst() == $tax) {
                $total += $asset->getCommission();
            }
        }
        return $total;
    }

    /**
     * @author mlaug
     * @since 18.03.2011
     * @return int
     */
    public function getCommTaxTotal($tax = ALL_TAX) {
        $total = 0;
        foreach ($this->getBillingAssets() as $asset) {
            if ($tax == ALL_TAX || $asset->getMwst() == $tax) {
                $total += $asset->getCommissionTax();
            }
        }
        return $total;
    }

    /**
     * @author mlaug
     * @since 18.03.2011
     * @return int
     */
    public function getCommBruttoTotal($tax = ALL_TAX) {
        $total = 0;
        foreach ($this->getBillingAssets() as $asset) {
            if ($tax == ALL_TAX || $asset->getMwst() == $tax) {
                $total += $asset->getCommissionBrutto();
            }
        }
        return $total;
    }

    /**
     * @author mlaug
     * @since 18.03.2011
     * @return int
     */
    public function calculateVoucherAmount() {
        return $this->getBrutto() - $this->getCommBruttoTotal();
    }

    /**
     * generate pdf
     * @author mlaug
     * @param boolean $test
     * @param Zend_Db_Table_Row_Abstract $row
     * @param array $orders
     * @param array $assets
     * $param boolean $createPdf
     * @return boolean
     */
    public function create($test = false, Zend_Db_Table_Row_Abstract $row = null, array $assets = array(), $createPdf = true, $checkForZeroOrder = true) {
        try {

            $this->old_assets = $assets;

            $service = $this->getService();

            if (!is_object($service)) {
                return false;
            }

            if ($this->until >= time()) {
                return false;
            }
            
            if ( !$row instanceof Zend_Db_Table_Row_Abstract){
                $this->logger->warn('did not get valid row object for asset');
                return false;
            }

            if ($checkForZeroOrder && count($this->getBillingAssets()) == 0) {
                $this->info('Für diese Zeitraum wurden keine Bestellungen und keine Rechnungsposten gefunden');
                $this->logger->info('no assets found for service ' . $service->getName());
                return false;
            }

            $this->number = str_replace('R', 'A', $row->number);
            
            $this->logger->debug('Creating asset bill for service ' . $service->getName());
            $this->preflyCheck();

            //generate bill
            $this->_latex->assign('service', $service);
            $this->_latex->assign('bill', $this);
            $customized = $this->getCustomized();
            $this->_latex->assign('header', $customized); //deprecated
            $this->_latex->assign('custom', $customized);

            $file_exists = true;
            if ($createPdf) {
                $file = $this->_latex->compile(true, true);
                $file_exists = file_exists($file);
                if ($file_exists) {
                    $this->_storage = new Default_File_Storage();
                    $this->_storage->setSubFolder('billing');
                    $this->_storage->setSubFolder('service');
                    $this->_storage->setSubFolder(substr($this->getNumber(),2,4));
                    $this->_storage->store($this->getNumber() . '.pdf', file_get_contents($file), null, true);
                }
            }
            
            $row->bill += $this->getBrutto();
                        
            $currentItemStack = 1;
            foreach($this->config->tax->types->toArray() as $taxtype){
                $itemRow = sprintf('item%dValue',$currentItemStack);
                $taxRow = sprintf('tax%dValue',$currentItemStack);
                
                $row->$itemRow += $this->calculateItem($taxtype);
                $row->$taxRow += $this->calculateTax($taxtype);
                
                $currentItemStack++;
            }
            
            $row->brutto += $this->getBrutto();
            $row->voucher += $this->calculateVoucherAmount();
            $row->prov += $this->getCommBruttoTotal();
            $row->save();
            
            foreach ($this->getBillingAssets() as $asset) {
                $asset->billMe($row->id, 'rest');
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->error($e->getMessage());
            $this->logger->crit('Could not generate service bill' . $e->getMessage());
            return false;
        }
    }

    /**
     * @author mlaug
     * @since 18.03.2011
     * @return Yourdelivery_Model_Servicetype_Restaurant
     */
    public function getObject() {
        return $this->getService();
    }

    /**
     * @author mlaug
     * @since 18.03.2011
     * @return SplObjectStorage
     */
    public function getOrders() {
        return $this->getBillingAssets();
    }

}

?>
