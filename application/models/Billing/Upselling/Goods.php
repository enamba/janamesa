<?php
/**
 * @author vpriem
 * @since 27.06.2011
 */
class Yourdelivery_Model_Billing_Upselling_Goods extends Yourdelivery_Model_Billing_Abstract {

    /**
     * @var Yourdelivery_Model_Upselling_Goods
     */
    private $_upsellingGoods;
    
    /**
     * @var int
     */
    private $_voucher;
    
    /**
     * @author vpriem
     * @since 15.06.2011
     */
    public function __construct($id = null, $current = null) {

        parent::__construct($id, $current);
        $this->setPeriod(time(), time());
    }

    /**
     * @author vpriem
     * @since 15.06.2011
     * @return string
     */
    public function getNumber(){
        
        if (isset($this->_data['number'])) {
            $this->number = $this->_data['number'];
        }
        
        if ($this->number === null) {
            $number = $this->getTable()->getNextBillingNumber();
            $this->number = "R-" . date('y') . date('m') . "-" . $this->getUpsellingGoods()->getService()->getCustomerNr() . "-" . $number;
        }
        
        return $this->number;
    }
    
    /**
     * @author vpriem
     * @since 15.06.2011
     * @param boolean $test deprecated
     * @return boolean
     */
    public function create($test = false, $row = null, $orders = null, $assets = null, $createPdf = true) {
        try{
            $upsellingGoods = $this->getUpsellingGoods();
            if (!$upsellingGoods instanceof Yourdelivery_Model_Upselling_Goods) {
                return false;
            }
            
            $service = $upsellingGoods->getService();
            if (!$service instanceof Yourdelivery_Model_Servicetype_Abstract) {
                return false;
            }
            
            //
            if ($upsellingGoods->calculateNetto() <= 0) {
                return false;
            }
            
            // save row
            $hasBalance = false;
            
            if ($row === null || !$row instanceof Zend_Db_Table_Row_Abstract){
                $status = 0;
                
                // check voucher
                $brutto = $upsellingGoods->calculateBrutto();
                $voucher = $this->getVoucher();
                if ($voucher !== null &&  $voucher >= $brutto) {
                    $voucher = $brutto;
                    $status = 2;
                    $hasBalance = true;
                }
                else {
                    $voucher = null;
                }
                
                $row = $this->getTable()->createRow(array(
                    'status'  => $status,
                    'voucher' => (float) $voucher,
                ));
                
                if ($hasBalance) {
                    $row->save(); // save here cause we need the id
                    $service->getBalance()->addBalance(-1 * $brutto, "Warenlieferung aus Rechnung " . $this->getNumber(), false, $row->id);
                }
            }
            elseif ($row->voucher > 0){
                $hasBalance = true;
            }
            
            $row->setFromArray(array(
                'mode'       => "upselling_goods",
                'number'     => $this->getNumber(),
                'refId'      => $upsellingGoods->getId(),
                'amount'     => (float) $upsellingGoods->calculateBrutto(),
                'brutto'     => (float) $upsellingGoods->calculateBrutto(),
                'item1Value' => (float) $upsellingGoods->calculateNetto(),
                'tax1Value'  => (float) $upsellingGoods->calculateTax(),
                'item1Key'   => 19,
            ))->save();
            
            // generate bill
            if ($createPdf) {
                $this->_latex->setTpl('bill/upselling/goods');
                $this->_latex->assign('upsellingGoods', $upsellingGoods);
                $this->_latex->assign('service', $service);
                $this->_latex->assign('bill', $this);
                $this->_latex->assign('hasBalance', $hasBalance);
                
                $customized = $this->getCustomized();
                $this->_latex->assign('header', $customized); //deprecated
                $this->_latex->assign('custom', $customized);
                
                $file = $this->_latex->compile();
                if (file_exists($file)) {
                    $this->_storage = new Default_File_Storage();
                    $this->_storage->setSubFolder('billing');
                    $this->_storage->setSubFolder('upselling');
                    $this->_storage->setSubFolder(substr($this->getNumber(), 2, 4));
                    $this->_storage->store($this->getNumber() . '.pdf', file_get_contents($file), null, true);
                }
                else {
                    return false;
                }
            }
            
            // generate txt
            if ($createPdf) {
                $this->_storage->store($this->getNumber() . '.txt', $upsellingGoods->getAsTxt($this->getNumber()), null, true);
            }
            
            return true;
        }
        catch (Exception $e) {
            $this->logger->crit('Could not generate inventory bill:' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send bill via vull API
     * @author vpriem
     * @since 20.06.2011
     * @return boolean
     */
    public function sendViaVullApi() {
        
        $vull = new Yourdelivery_Sender_Vull();
        $status = $vull
            ->setNr($this->getNumber())
            ->setPdf($this->getPdf())
            ->setTxt($this->getTxt())
            ->send();
        
        
        // db logging
        $dbTable = new Yourdelivery_Model_DbTable_Billing_Sent();
        $dbTable->createRow(array(
            'billingId' => $this->getId(),
            'via' => "vull",
            'status' => $status,
        ))->save();

        return $status;
    }
    
    /**
     * @author vpriem
     * @since 15.06.2011
     */
    public function getOrders() {
        return array();
    }

    /**
     * Get pdf of invoice
     * @author vpriem
     * @since 01.08.2010
     * @return mixed
     */
    public function getPdf() {

        $file = APPLICATION_PATH . '/../storage/billing/upselling/' . substr($this->getNumber(), 2, 4) . '/' . $this->getNumber() . '.pdf';
        if (file_exists($file)) {
            return $file;
        }
        return false;
    }
    
    /**
     * Get txt file
     * @author vpriem
     * @since 20.06.2011
     * @return mixed
     */
    public function getTxt() {

        $file = APPLICATION_PATH . '/../storage/billing/upselling/' . substr($this->getNumber(), 2, 4) . '/' . $this->getNumber() . '.txt';
        if (file_exists($file)) {
            return $file;
        }
        return false;
    }
    
    /**
     * Set reference object
     * @author vpriem
     * @since 15.06.2011
     * @param Yourdelivery_Model_Upselling_Goods $upsellingGoods
     * @return Yourdelivery_Model_Billing_Upselling_Goods
     */
    public function setUpsellingGoods(Yourdelivery_Model_Upselling_Goods $upsellingGoods) {
        
        $this->_upsellingGoods = $upsellingGoods;
        return $this;
    }

    /**
     * Get reference object
     * @author vpriem
     * @since 15.06.2011
     * @return Yourdelivery_Model_Upselling_Goods
     */
    public function getUpsellingGoods() {
        
        if ($this->_upsellingGoods === null) {
            $refId = $this->getRefId();
            if ($refId) {
                $this->_upsellingGoods = new Yourdelivery_Model_Upselling_Goods($refId);
            }
        }
        return $this->_upsellingGoods;
    }  
    
    /**
     * @author vpriem
     * @since 15.06.2011
     * @return Yourdelivery_Model_Servicetype_Abstract
     */
    public function getObject(){
        return $this->getUpsellingGoods()->getService();
    }

    /**
     * Set voucher
     * @author vpriem
     * @since 28.06.2011
     * @param int $voucher
     * @return Yourdelivery_Model_Billing_Upselling_Goods
     */
    public function setVoucher($voucher) {
        
        $this->_voucher = $voucher;
        return $this;
    }

    /**
     * Get voucher
     * @author vpriem
     * @since 28.06.2011
     * @return int
     */
    public function getVoucher() {
        
        return $this->_voucher;
    }
    
}
