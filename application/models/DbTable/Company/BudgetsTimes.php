<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_CompanyBudgetsTimes.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Company_BudgetsTimes extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'company_budgets_times';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    
    /**
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public static function edit($id, $data)
    {        
        $db = Zend_Registry::get('dbAdapter');
        $db->update('company_budgets_times', $data, 'company_budgets_times.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('company_budgets_times', 'company_budgets_times.id = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order=null, $limit=0, $from=0)
    {
        $db = Zend_Registry::get('dbAdapter');
        
        $query = $db->select()
                    ->from( array("%ftable%" => "company_budgets_times") );
                    
        if($order != null)
        {
            $query->order($order);
        }

        if($limit != 0)
        {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
    }
    
        /**
     * get a rows matching Id by given value
     * @param int $id
     */
    public static function findById($id)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "company_budgets_times") )                           
                    ->where( "c.id = " . $id );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching BudgetId by given value
     * @param int $budgetId
     */
    public static function findByBudgetId($budgetId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "company_budgets_times") )                           
                    ->where( "c.budgetId = " . $budgetId );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching CompanyId by given value
     * @param int $companyId
     */
    public static function findByCompanyId($companyId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "company_budgets_times") )                           
                    ->where( "c.companyId = " . $companyId );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Day by given value
     * @param int $day
     */
    public static function findByDay($day)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "company_budgets_times") )                           
                    ->where( "c.day = " . $day );

        return $db->fetchRow($query); 
    }
    
}
