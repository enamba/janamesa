<?php

/**
 * Description of Code
 *
 * @author mlaug
 */
class Yourdelivery_Model_Tracking_Code extends Default_Model_Base {

    /**
     * click count
     * @var int
     */
    protected $customerTrackedCount = null;

    /**
     * orders count
     * @var int
     */
    protected $ordersTrackedCount = null;

    /**
     * orders sum
     * @var array
     */
    protected $ordersSum = null;

    /**
     * orders sum per keywords set
     * @var array
     */
    protected $ordersSumPerKeywords = null;


    public function __construct($id = null) {
        if(is_null($id)){
            return $this;
        }

        parent::__construct($id);
        $this->populate();
    }

    /**
     * @author mlaug
     * @param int $q
     * @return array
     */
    public static function findByPostfix($q = null){

        if ( is_null($q) ){
            return false;
        }

        $db = Zend_Registry::get('dbAdapter');
        return $db->query('select id from tracking_code where postfix="' . $q . '"')->fetchColumn();
    }

    public function setUsage(){

    }

    /**
     * @author mlaug
     * @return string
     */
    public function getLink(){
        $postfix = $this->getPostfix();
        $host = $this->config->hostname;
        return $host . "/q/" . $postfix;
    }

    /**
     * get the landing page associated to this tracking code
     * @author mlaug
     * @return null
     * @deprecated 
     */
    public function getLandingpage(){
        return null;
    }

    /**
     * redirect link for this code
     * @return
     */
    public function getRedirectLink(){
        $postfix = $this->getPostfix();
        $host = $this->config->hostname;
        $url = $host . $this->getRedirect();
        if( preg_match('/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}((:[0-9]{1,5})?\/.*)?$/i',$url)){
            return $url;
        }
        return "FEHLERHAFT";
    }

    /**
     * get associated table
     * @return Yourdelivery_Model_DbTable_Tracking_Code
     */
    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Tracking_Code();
        }
        return $this->_table;
    }

    /**
     * get all tracking codes
     * @return SplObjectStorage
     */
    public static function all(){
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->query('select id from tracking_code')->fetchAll();
        $cs = new SplObjectStorage();
        foreach($result as $c){
            try{
                $c = new Yourdelivery_Model_Tracking_Code($c['id']);
                $c->populate();
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                continue;
            }
            $cs->attach($c);
        }
        return $cs;
    }

    /**
     * populate this traking code objekt with statistics
     * @return
     */
    public function populate(){
        $db = Zend_Registry::get('dbAdapter');
        
        $sql = sprintf("select sum(total+serviceDeliverCost) as sum from orders inner join (select ct.orderId as id from tracking_code tc inner join customer_tracked ct on ct.trackingCodeId=tc.id where tc.id=%d and ct.orderId is not Null)t on orders.id=t.id", $this->getId());
        try{
            $result = $db->fetchRow($sql);
        }
        catch ( Zend_Db_Statement_Exception $e ){
            return 'error';
        }

        $this->ordersSum = $result->sum;

        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        try{
            $sql = 'select count(id) as countId from customer_tracked where trackingCodeId=' . $this->getId();
            $result = $db->fetchRow($sql);
        }
        catch ( Zend_Db_Statement_Exception $e ){
            $db->setFetchMode(Zend_Db::FETCH_ASSOC);
            return 'error';
        }

        $this->customerTrackedCount = $result->countId;

        $sql = 'select count(orderId) as countOrds from customer_tracked where orderId and trackingCodeId=' . $this->getId();
        try{
            $result = $db->fetchRow($sql);
        }
        catch ( Zend_Db_Statement_Exception $e ){
            $db->setFetchMode(Zend_Db::FETCH_ASSOC);
            return 'error';
        }

        $this->ordersTrackedCount = $result->countOrds;
        $this->ordersSumPerKeywords = $this->calculateCustomerTrackedOrdersSumPerKW();
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        
    }

   /**
     * get tracked count
     * @var int
     */
    public function getCustomerTrackedCount(){
        return $this->customerTrackedCount;
    }

    /**
     * get tracked orders count
     * @var int
     */
    public function getCustomerTrackedOrdersCount(){
        return $this->ordersTrackedCount;
    }


   /**
     * get tracked orders sum
     * @var int
     */
    public function getCustomerTrackedOrdersSumPerKW(){
        return $this->ordersSumPerKeywords;
    }


   /**
     * get tracked orders sum
     * @var int
     */
    public function getCustomerTrackedOrdersSum(){
        return $this->ordersSum;
    }

   /**
     * calculate tracked orders sum
     * @var int
     */
    public function calculateCustomerTrackedOrdersSumPerKW(){
        $result = array();

        $db = Zend_Registry::get('dbAdapter');
        $sql = 'select distinct engine, keywords from customer_tracked where trackingCodeId=' . $this->getId(). ' group by keywords order by count(orderId) desc';
        $kws = $db->query($sql)->fetchAll();
        if (!count($kws)){
            return NULL;
        }

        foreach($kws as $k){
            $sql = 'select distinct orderId, engine from customer_tracked where keywords=\'' . $k->keywords . '\' and trackingCodeId=' . $this->getId();
            $ords = $db->query($sql)->fetchAll();

            $sql2 = 'select count(id) as count from customer_tracked where keywords=\'' . $k->keywords . '\' and trackingCodeId=' . $this->getId();
            $count = $db->fetchRow($sql2);

            $sum = 0;
            foreach($ords as $order){
                if ($order->orderId) {

                    $sql = 'select (total+serviceDeliverCost) as total from orders where id=' . $order->orderId;
                    $res = $db->fetchRow($sql);
                    $sum += intval($res->total);
                }
            }

            if($k->keywords == 'UNKNOWN') {
                $index = 'unbekannt';
            }
            else if($k->keywords) {
                $index = $k->keywords;
            }
            else {
                $index = '<i>leere Eingabe</i>';
            }

            if($k->engine == 'UNKNOWN') {
                $engine = 'unbekannt';
            }
            else {
                $engine = $k->engine;
            }

            $data = array();
            $data['sum'] = $sum;
            $data['engine'] = $engine;
            $data['count'] =  $count->count;

            $result[$index] = $data;
        }

        return $result;
    }


    /*
     * calculate all profit from selected code
     */
    public static function calculateOrdersSum($id){
        $result = array();
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("select  sum(total+serviceDeliverCost) as sum from orders inner join (select ct.orderId as id from tracking_code tc inner join customer_tracked ct on ct.trackingCodeId=tc.id where tc.id=%d and ct.orderId is not Null)t on orders.id=t.id", $id);
        try{
            $result = $db->fetchRow($sql);
        }
        catch ( Zend_Db_Statement_Exception $e ){
            return 0;
        }

        return $result['sum'];
    }


    /*
     * calculate all profit from selected code
     */
    public static function calculateOrdersSumOverTime($start, $end){
        $result = array();
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("select sum(total+serviceDeliverCost) as sum from orders inner join (select ct.orderId as id from tracking_code tc inner join customer_tracked ct on ct.trackingCodeId=tc.id where ct.orderId is not Null)t on orders.id=t.id where orders.time between from_unixtime(%d) and from_unixtime(%d)", $start, $end);
        try{
            $result = $db->fetchRow($sql);
        }
        catch ( Zend_Db_Statement_Exception $e ){
            return 0;
        }

        return $result['sum'];
    }


    /*
     * export to CSV
     */
    public function exportCsv() {
        $csv = new Default_Exporter_Csv();
        $csv->addCol('Id');
        $csv->addCol('Name');
        $csv->addCol('Description');
        $csv->addCol('Postfix');
        $csv->addCol('Link');
        $csv->addCol('Redirect');
        $csv->addCol('Landing page');

        $csv->addRow(
            array(
                'Id' => $this->getId(),
                'Name'  => $this->getName(),
                'Description' => $this->getDesc(),
                'Postfix' => $this->getPostfix(),
                'Link' => $this->getLink(),
                'Redirect' => $this->getRedirectLink(),
                'Landing page' => $this->getLandingpageId()
            )
        );

        $csv->save();
    }
}
?>
