<?php
/**
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Billing_Balance extends Default_Model_DbTable_Base {

    protected $_referenceMap = array(
        'Billing' => array(
            'columns' => 'billingId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Billing',
            'refColumns' => 'id'
        ),
        'Company' => array(
            'columns' => 'companyId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Company',
            'refColumns' => 'id'
        ),
        'Service' => array(
            'columns' => 'restaurantId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Restaurant',
            'refColumns' => 'id'
        )
    );
    
    /**
     * Table name
     * @param string
     */
    protected $_name = 'billing_balance';
    
    /**
     * Primary key name
     * @param string
     */
    protected $_primary = 'id';

    /**
     * get the current balance of a service. we can define a upper boundary
     * so that we only get those transactions made before a certian $maxDate
     * @author mlaug
     * @param int $serviceId
     * @param date $maxDate
     * @return string 
     */
    public function getBalanceOfService($serviceId, $maxDate = null) {
        $select = $this->select()
                    ->from('billing_balance', array('SUM(amount)'))
                    ->where("restaurantId = ?", $serviceId);
        
        if ( $maxDate !== null ){
            $select->where("affects < ?", date(DATETIME_DB,$maxDate));
        }
        
        return $select
                    ->query()
                    ->fetchColumn();
    }

    /**
     * get the current balance of a company. we can define a upper boundary
     * so that we only get those transactions made before a certian $maxDate
     * @author mlaug
     * @param int $companyId
     * @param date $maxDate
     * @return string 
     */
    public function getBalanceOfCompany($companyId, $maxDate = null) {
        $select = $this->select()
                    ->from('billing_balance', array('SUM(amount)'))
                    ->where("companyId = ?", $companyId);
        
        if ( $maxDate !== null ){
            $select->where("affects < ?", date(DATETIME_DB,$maxDate));
        }
        
        return $select->query()
                      ->fetchColumn();
    }
    
    /**
     * get the current list of balance transaction of a company. we can define a upper boundary
     * so that we only get those transactions made before a certian $maxDate
     * @author mlaug
     * @param int $company
     * @param date $maxDate
     * @return array 
     */
    public function getListOfTransactionsOfCompany($companyId, $maxDate = null) {
        $select = $this->select()
                    ->where("companyId = ?", $companyId);
        
        if ( $maxDate !== null ){
            $select->where("affects < ?", date(DATETIME_DB,$maxDate));
        }
        
        return $select->query()
                      ->fetchAll();
    }
    
    /**
     * get the current list of balance transaction of a service. we can define a upper boundary
     * so that we only get those transactions made before a certian $maxDate
     * @author mlaug
     * @param int $serviceId
     * @param date $maxDate
     * @return array 
     */
    public function getListOfTransactionsOfService($serviceId, $maxDate = null) {
        $select = $this->select()
                    ->where("restaurantId = ?", $serviceId);
        
        if ( $maxDate !== null ){
            $select->where("affects < ?", date(DATETIME_DB,$maxDate));
        }
        
        return $select
                    ->query()
                    ->fetchAll();
    }

}
