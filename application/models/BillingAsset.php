<?php

/**
 * Description of Campagne
 * @package billing
 * @author mlaug
 */
class Yourdelivery_Model_BillingAsset extends Default_Model_Base {

    /**
     * return all billing assets
     * @return SplObjectStorage
     */
    public static function all() {
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->query('select id from billing_assets')->fetchAll();
        $cs = new SplObjectStorage();
        foreach ($result as $c) {
            try {
                $c = new Yourdelivery_Model_BillingAsset($c['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
            $cs->attach($c);
        }
        return $cs;
    }

    /**
     * return a corresponding restaurant
     * @return Yourdelivery_Model_Servicetype_Restaurant
     */
    public function getRestaurant() {
        return new Yourdelivery_Model_Servicetype_Restaurant($this->getRestaurantId());
    }

    /**
     * return a corresponding table
     * @return object
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_BillingAsset();
        }
        return $this->_table;
    }
    
    /**
     * @author mlaug
     * @return double
     */
    public function getBrutto($of = 'service') {
        if ( $of == 'service' ){
            $total = $this->getTotal();
        }
        else{
            $total = $this->getCouriertotal();
        }
        return $total + ($total/100*$this->getMwst());
    }
    
    /**
     * @author mlaug
     * @since 18.03.2011
     * @return double
     */
    public function getCommission($of = 'service'){
        return ($this->getBrutto($of) / 100) * $this->getFee();
    }
    
    /**
     * @author mlaug
     * @since 18.03.2011
     * @return double
     */
    public function getCommissionTax($of = 'service'){
        return ($this->getCommission($of)/100) * $this->config->tax->provision;
    }
    
    /**
     * @author mlaug
     * @since 18.03.2011
     * @return double
     */
    public function getCommissionBrutto($of = 'service'){
        return $this->getCommission($of) + $this->getCommissionTax($of);
    }
    
    /**
     * @author mlaug
     * @since 18.03.2011
     * @return double
     */
    public function getTax($taxtype = ALL_TAX, $of = 'service'){
        if ( $taxtype == ALL_TAX || $this->getMwst() == $taxtype){
            if ( $of == 'service' ){
                return $this->getTotal()/100*$this->getMwst();
            }
            else{
                return $this->getTotalcourier()/100*$this->getMwstcourier();
            }
        }
    }
    
    /**
     * @author mlaug
     * @since 18.03.2011
     * @return double
     */
    public function getItem($taxtype = ALL_TAX, $of = 'service'){
        if ( $taxtype == ALL_TAX || $this->getMwst() == $taxtype){
            if ( $of == 'service' ){
                return $this->getTotal();
            }
            else{
                return $this->getTotalcourier();
            }
        }
    }
    
    /**
     * @author mlaug
     * @param int $paramId
     * @param string $mode
     */
    public function billMe($billId, $mode) {
        $this->getTable()->billMe($billId, $mode);
    }

    /**
     * Get project code, this asset is associated with
     * @author alex
     * @since 16.12.2010
     * @return Yourdelivery_Model_Projectnumbers
     */
    public function getProjectnumber() {
        if (intval($this->getProjectnumberId()) == 0) {
            return null;
        }

        $projectnumber = null;
        try {
            $projectnumber = new Yourdelivery_Model_Projectnumbers($this->getProjectnumberId());
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }

        return $projectnumber;
    }

    /**
     * Get department, this asset is associated with
     * @author alex
     * @since 16.12.2010
     * @return Yourdelivery_Model_Department
     */
    public function getDepartment() {
        if (intval($this->getDepartmentId()) == 0) {
            return null;
        }

        $department = null;
        try {
            $department = new Yourdelivery_Model_Department($this->getDepartmentId());
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }

        return $department;
    }

}

?>
