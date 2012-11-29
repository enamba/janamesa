<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_CompanyBudgets.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Company_Budgets extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'company_budgets';

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
        $db->update('company_budgets', $data, 'company_budgets.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('company_budgets', 'company_budgets.id = ' . $id);
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
                    ->from( array("%ftable%" => "company_budgets") );
                    
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
                    ->from( array("c" => "company_budgets") )                           
                    ->where( "c.id = '" . $id ."'");

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
                    ->from( array("c" => "company_budgets") )                           
                    ->where( "c.companyId = " . $companyId );

   return $db->fetchAll($query);
    }

    /**
     * IN USE: MATTES IMPORT JVM
     * get a rows matching Name by given value
     * @param varchar $name
     */
    public static function findByName($name)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "company_budgets") )                           
                    ->where( "c.name = '" . $name . "'");

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Status by given value
     * @param tinyint $status
     */
    public static function findByStatus($status)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "company_budgets") )                           
                    ->where( "c.status = " . $status );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Temp by given value
     * @param tinyint $temp
     */
    public static function findByTemp($temp)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "company_budgets") )                           
                    ->where( "c.temp = " . $temp );

        return $db->fetchRow($query); 
    }
    
    public function getIdByName($id,$name){
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( "company_budgets" )
                    ->where( "companyId = " . $id . " and name = '" . $name ."'");

        return $db->fetchRow($query); 
    }

    /**
     * create budgetTime row
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $budgetId
     * @param int $day
     * @param int $start
     * @param int $end
     * @param int $amount
     * @return int newId
     */
    public function addBudgetTime($budgetId, $day, $start, $end, $amount){

        $table = new Yourdelivery_Model_DbTable_Company_BudgetsTimes();
        $row = $table->createRow();
        $row->budgetId = $budgetId;
        $row->day = $day;
        $row->from = $start;
        $row->until = $end;
        $row->amount = $amount;
        $row->created = NULL;
        return $row->save();
    }

    /**
     * delete budgetTime row
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $budgetId
     * @param int $budgetTimeId
     * @return boolean
     */
    public function removeBudgetTime($budgetId, $budgetTimeId){
        
        $table = new Yourdelivery_Model_DbTable_Company_BudgetsTimes();
        return count($table->delete('id = ' . $budgetTimeId . ' AND budgetId = '.$budgetId)) > 0;
    }

    /**
     * delete budgetTime at speciefied day
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $budgetId
     * @param int $day
     * @return boolean
     */
    public function removeBudgetTimeAtDay($budgetId, $day){
        $table = new Yourdelivery_Model_DbTable_Company_BudgetsTimes();
        return count($table->delete('budgetId = '.$budgetId.' AND `day` = '.$day) ) > 0;
    }

    /**
     * delete all budgetTimes from this budget
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $budgetId
     * @param int $day
     * @return int count deleted rows
     */
    public function removeBudgetTimesAll($budgetId){
        if( is_null($budgetId) ){
            return false;
        }
        $table = new Yourdelivery_Model_DbTable_Company_BudgetsTimes();
        return count($table->delete('budgetId = '.$budgetId)) > 0;
    }

    /**
     * get budget time(s) for specified day
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $budgetId
     * @param int $day
     * @return Zend_DbTable_Rowset
     */
    public function getBudgetTimes($budgetId, $day){
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("bt" => "company_budgets_times") )
                    ->where( "bt.budgetId = '" . $budgetId . "' AND bt.day = '".$day."'");

        return $db->fetchAll($query);        
    }

    /**
     * get all budget Times for budget
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.10.2010
     * @param int $budgetId
     * @return Zend_DbTable_Rowset
     */
    public function getBudgetTimesAll($budgetId){
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("bt" => "company_budgets_times") )
                    ->where( "bt.budgetId = '" . $budgetId . "'");

        $result = $db->fetchAll($query);
        return $result;
    }
}
