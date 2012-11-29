<?php

/**
 * 
 * @author mlaug
 */
class Yourdelivery_Model_Billing_Order extends Yourdelivery_Model_Billing_Abstract {

    /**
     * @var SplObjectStorage
     */
    protected $_orders = null;

    protected $_kind = 'priv';
    
    /**
     * create a bill for one explicit order
     *
     * @param Yourdelivery_Model_Customer_Abstract $company
     * @param Yourdelivery_Model_Order $order
     * @author mlaug
     */
    public function __construct(Yourdelivery_Model_Customer_Abstract $customer) {
        parent::__construct();
        $this->setCustomer($customer);
        $this->_orders = new SplObjectStorage();
    }

    /**
     * @author mlaug
     * @since 26.12.2010
     */
    public function getNumber(){
        return $this->number;
    }
    
    /**
     * create this bill for all appended orders
     * @author mlaug
     * @since 08.12.2010
     */
    public function create() {
        $this->_latex->setTpl('bill/order/standard');
        $this->_latex->assign('bill', $this);
        
        $this->getOrders()->rewind();
        $firstOrder = $this->getOrders()->current();
        $header = array(
            'heading' => $firstOrder->getCustomer()->getFullname(),
            'street' => $firstOrder->getLocation()->getStreet(),
            'hausnr' => $firstOrder->getLocation()->getHausnr(),
            'plz' => $firstOrder->getLocation()->getPlz(),
            'city' => $firstOrder->getLocation()->getOrt()->getOrt(),
            'addition' => $firstOrder->getLocation()->getCompanyName()
        );
        $this->_latex->assign('header',$header);
        $this->_latex->assign('order',$firstOrder);
        
        $billingOrder = Yourdelivery_Model_DbTable_Billing::findByRefIdAndMode($firstOrder->getId(), 'order');
        $billingId = null;
        if ( is_array($billingOrder) ){
            //this bill has been created before
            $this->number = $billingOrder['number'];
            $billingId = $billingOrder['id'];
        }
        else{
            //this bill will be created for the first time
            $table = new Yourdelivery_Model_DbTable_Billing();
            $row = $table->createRow();
            
            if ( $this->_kind == 'priv' ){
                $customerNr = $firstOrder->getCustomer()->getCustomerNr();
            }
            else{
                $customerNr = $firstOrder->getCompany()->getCustomerNr();
            }
            
            $number = sprintf('R-%s-%d-%s',date('ym',time()),$customerNr,$table->getNextBillingNumber());
            if ( Yourdelivery_Model_DbTable_Billing::findByNumber($number) ){
                return true;
            }
            
            $this->number = $row->number = $number;
            $row->refId = $firstOrder->getId();
            $row->mode = 'order';
            $row->amount = $this->calculateBrutto();
            $row->save();
            $billingId = $row->id;                     
        }
        
        $file = $this->_latex->compile();
        
        if ( file_exists($file) && $billingId !== null){
            $this->_storage = new Default_File_Storage();
            $this->_storage->setSubFolder('billing');
            $this->_storage->setSubFolder('orders');
            $this->_storage->setSubFolder(substr($this->getNumber(),2,4));
            $this->_storage->store($this->number . '.pdf', file_get_contents($file), null, true);
            
            foreach($this->_orders as $order){
                $order->billMe($billingId,'order');
            }
            
            $this->file = $file;
            return true;
        }
        
        return false;
            
    }

    /**
     * @author mlaug
     * @since 08.12.2010
     * @return SplObjectStorage
     */
    public function getObject() {
        return $this->getCustomer();
    }

    /**
     * @author mlaug
     * @since 08.12.2010
     * @param Yourdelivery_Model_Order $order
     * @return boolean 
     */
    public function addOrder(Yourdelivery_Model_Order $order) {
        if ($order instanceof Yourdelivery_Model_Order) {
            if ( $this->_orders->count() == 0 ){
                $this->_kind = $order->getKind();
            }
            //do not allow mixup of types
            if ( $order->getKind() != $this->_kind ){
                $this->until = $order->getTime();
                return false;
            }
            $this->_orders->attach($order);
            return true;
        }
        return false;
    }

    /**
     * @author mlaug
     * @since 08.12.2010
     * @return SplObjectStorage
     */
    public function getOrders() {
        return $this->_orders;
    }

    /**
     * @author mlaug
     * @since 08.12.2010
     * @return integer
     */
    public function calculateItem($taxtype = ALL_TAX) {
        $amount = 0;
        foreach ($this->_orders as $order) {
            $amount += $order->getItem($taxtype);
        }
        return $amount;
    }

    /**
     * @author mlaug
     * @since 08.12.2010
     * @return integer
     */
    public function calculateTax($taxtype = ALL_TAX) {
        $amount = 0;
        foreach ($this->_orders as $order) {
            $amount += $order->getTax($taxtype);
        }
        return $amount;
    }

    /**
     * @author mlaug
     * @since 08.12.2010
     * @return integer
     */
    public function calculateBrutto() {

        $amount = 0;
        foreach ($this->_orders as $order) {
            $amount += $order->getTotal()
                    + $order->getServiceDeliverCost()
                    + $order->getCourierCost()
                    - $order->getCourierDiscount();
        }
        return $amount;
    }
    

    /**
     * @author mlaug
     * @since 08.12.2010
     * @return integer
     */
    public function calculateDiscount(){
        $amount = 0;
        foreach ($this->_orders as $order) {
            $amount += $order->getDiscountAmount();
        }
        return $amount;
    }
    

    /**
     * @author mlaug
     * @since 08.12.2010
     * @return integer
     */
    public function calculateCharge(){
        $amount = 0;
        foreach ($this->_orders as $order) {
            $amount += $order->getCharge();
        }
        return $amount;
    }

}

?>
