<?php
/**
 * Description of Customer
 *
 * @package billing
 * @author mlaug
 */
class Yourdelivery_Model_Billing_Courier extends Yourdelivery_Model_Billing_Abstract{

    /**
     * @author mlaug
     * @param Yourdelivery_Model_Courier $courier
     * @param int $from
     * @param int $until
     * @param string $mode
     * @param boolean $test
     */
    public function __construct(Yourdelivery_Model_Courier $courier, $from = 0, $until = 0,$mode = null, $test = 0){
        parent::__construct();
        $this->setPeriod($from,$until,$mode);
        $this->setCourier($courier);
    }

    /**
     * get corresponding billing number, based on starting date,
     * customer number and next billing number
     * @author mlaug
     * @return string
     */
    public function getNumber(){
        if ( is_null($this->number) ){
            $number = $this->getTable()->getNextBillingNumber($this->getCourier(), 'rest');
            $this->number = "R-".date('y',$this->from).date('m',$this->from)."-".$this->getCourier()->getCustomerNr()."-".$number;
        }
        return $this->number;
    }
    
    /**
     * get voucher number
     * this just replaces an R with a G
     * @author mlaug
     * @return string
     */
    public function getNumberVoucher(){
        return str_replace('R','G',$this->number);
    }

    /**
     * set courier
     * @author mlaug
     * @param Yourdelivery_Model_couriertype_Abstract $courier
     */
    public function setCourier($courier = null){

        switch($courier->getId()){
            default:{
                $this->_latex->setTpl('bill/courier/standard');
                break;
            }
        }

        $this->_courier = $courier;
    }

    /**
     * get courier
     * @author mlaug
     * @return Yourdelivery_Model_couriertype_Abstract $courier
     */
    public function getCourier(){
        return $this->_courier;
    }
   

    /**
     * get all orders in a certian time range
     * @author mlaug
     * @return SplObjectStorage
     */
    public function getOrders(){
        if ( is_null($this->orders) ){
            $db = $this->getTable()->getAdapter();
            $sid = $this->getCourier()->getId();

            //get all orders of courier service
            $sql = sprintf("select id from orders
                            where (billCourier=0 or billCourier is NULL)
                            and courierId = %d",$sid);
            
            $rows = $db->fetchAll($sql);
            $orders = new SplObjectStorage();
            foreach($rows as $o){
                try{
                    $order = new Yourdelivery_Model_Order($o['id']);

                    if ( $order->getMode() == "great" && $order->getState() != 2 &&
                         //check for great which do not provide pfand
                         //kreiner, gela
                         !in_array($order->getCourier()->getId(),array(12123,12115)) ){
                        continue;
                    }

                    if ( $order->getState() < 0 ){
                        continue;
                    }

                    if ( $order->getDeliverTime() > $this->until ){
                        continue;
                    }

                    $orders->attach($order);
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e ){

                }
            }
            $this->orders = $orders;
        }
        return $this->orders;
    }

    /**
     * return the object associated to this bill
     * @return Yourdelivery_Model_couriertype_Abstract
     */
    public function getObject(){
        return $this->getCourier();
    }

    /**
     * get all courier costs
     * @author mlaug
     * @since 29.09.2010
     * @return int
     */
    public function getBrutto(){
        $brutto = 0;
        foreach($this->getOrders() as $order){
            $brutto += $order->getCourierCost() + $order->getCourierDiscount();
        }
        return $brutto;
    }

    /**
     * get tax of order
     * default is 7%
     * @author mlaug
     * @since 29.09.2010
     * @return float
     */
    public function getTax(){
        $brutto = $this->getBrutto();
        return $brutto - ($brutto / (107) * 100);
    }

    /**
     * @author mlaug
     * @return float
     */
    public function calculateCommission(){
        $comm = 0;
        foreach($this->getOrders() as $order){
            $comm += $order->getCommissionCourier();
        }
        return $comm;
    }

    /**
     * @author mlaug
     * @return int
     */
    public function getCommTaxTotal(){
        $comm = 0;
        foreach($this->getOrders() as $order){
            $comm += $order->getCommissionTaxCourier();
        }
        return $comm;
    }

    /**
     * @author mlaug
     * @return float
     */
    public function getCommBruttoTotal(){
        $comm = 0;
        foreach($this->getOrders() as $order){
            $comm += $order->getCommissionBruttoCourier();
        }
        return $comm;
    }

    public function calculateDiscount(){
        $discount = 0;
        foreach($this->getOrders() as $order){
            $discount += $order->getCourierDiscount();
        }
        return $discount;
    }

    /**
     * calculate billing amount, which has to be payed by service
     * @author mlaug
     * @return int
     */
    public function calculateVoucherAmount(){
        return $this->getBrutto() -
               $this->getCommBruttoTotal();
    }

    /**
     *
     * @return int
     */
    public function calculateTax7(){
        $brutto = $this->getBrutto();
        $tax = $brutto - ( $brutto / (1.07) );
        return $tax;
    }

    /**
     *
     * @return int
     */
    public function calculateItem7(){
        return $this->getBrutto() - $this->calculateTax7();
    }

    /**
     * generate pdf
     * @author mlaug
     * @param boolean $test
     * @param Zend_Db_Table_Row_Abstract $row
     * @param array $orders
     * @param array $assets
     * $param boolean $createPdf
     * @todo: implement the switch to not create a pdf
     * @return boolean
     */
    public function create($test = false, $row = null, array $orders = array(), array $assets = array(), $createPdf = true, $checkForZeroOrder = true, $crefo = true) {
        try{
            $courier = $this->getCourier();

            if ( !is_object($courier) ){
                return false;
            }
          
            if ( $this->until >= time()){
                return false;
            }
            
            if ( $checkForZeroOrder && $this->getOrders()->count() == 0 ){
                $this->logger->info('no orders have been found for this interval');
                $this->info('Für diese Zeitraum wurden keine Bestellungen gefunden');
                return false;
            }
       
            //generate bill
            if ( $createPdf ){
                $this->_latex->assign('courier',$courier);
                $this->_latex->assign('bill',$this);
                $customized = $this->getCustomized();
                $this->_latex->assign('header',$customized); //deprecated
                $this->_latex->assign('custom',$customized);
                $file = $this->_latex->compile(true);
            }
            
            if ( !$createPdf || file_exists($file) ){
                
                if ( $createPdf ){
                    $this->_storage = new Default_File_Storage();
                    $this->_storage->setSubFolder('billing');
                    $this->_storage->setSubFolder('courier');
                    $this->_storage->setSubFolder(substr($this->getNumber(),2,4));
                    $this->_storage->store($this->getNumber() . '.pdf', file_get_contents($file), null, true);
                }
                
                //store in database
                if ( is_null($row) || !$row instanceof Zend_Db_Table_Row_Abstract){
                    $row = $this->getTable()->createRow();
                    $row->status = 0;
                }

                $row->mode = 'courier';
                $row->from = date('Y-m-d H:i:s',$this->from);
                $row->until = date('Y-m-d H:i:s',$this->until);
                $row->number = $this->getNumber();
                $row->refId = $this->getCourier()->getId();

                $row->item1Value = (float) $this->calculateItem7();
                $row->tax1Value = (float) $this->calculateTax7();
                $row->item1Key = 7;
                
                $row->amount = 0; //always null because we do not allow cash payment on delivery
                $row->brutto = $this->getBrutto(); //we fee on everything currently
                $row->prov = $this->getCommBruttoTotal(); // we do not differ between percent, item and fee, so everything will be hold here
                $row->voucher = $this->calculateVoucherAmount();

                $this->isVoucher = true;
                
                $voucherFile = '';
                if ( $createPdf ){
                    $this->_latex->setTpl('bill/courier/standard_voucher');
                    $voucherFile = $this->_latex->compile(true);
                }
                
                if ( $createPdf && file_exists($voucherFile) ){
                    
                    if ( $createPdf ){
                        $this->_storage->store($this->getNumberVoucher() . '.pdf', file_get_contents($voucherFile), null, true);                 
                    }
                    
                    //only store if this is not a test
                    if ( !$test ){
                        $billId = $row->save();
                        if ( !$billId ){
                            $this->logger->crit('could not save row');
                            return false;
                        }
                        //append all orders to this bill
                        foreach($this->getOrders() as $order){
                            $order->billMe($billId,'courier');
                        }
                    }
                    
                    return true;
                    
                }
                
                return true;
            }

            $this->logger->crit(sprintf('could not compile pdf, or file %s not found', $voucherFile));
            return false;
        }
        catch ( Exception $e ){
            $this->logger->crit('Could not generate courier bill:' . $e->getMessage());
            return false;
        }
    }



}
