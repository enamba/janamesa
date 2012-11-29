<?php
/**
 * Description of Department
 * @package company
 * @subpackage department
 * @author mlaug
 */
class Yourdelivery_Model_Department extends Default_Model_Base {

    /**
     * store all employees associated with this department
     * @var SplObjectStorage
     */
    protected $_employees = null;

    /**
     * all children
     * @var SplObjectStorage
     */
    protected $_childs = null;

    /**
     * all costcenters
     * @var SplObjectStorage
     */
    protected $_costcenters = null;

    /**
     * get all costcenters
     * @author mlaug
     * @return SplObjectStorage
     */
    public static function getCostCenters(){
        $db = Zend_Registry::get('dbAdapter');
        $sql = "select id from department where billing=1";
        $items = new SplObjectStorage();
        foreach($db->fetchAll($sql) as $item){
            $costcenter = new Yourdelivery_Model_Department($item['id']);
            $items->attach($costcenter);
        }
        return $items;
    }

    /**
     * get all departments
     * @author mlaug
     * @return SplObjectStorage
     */
    public static function getDepartments(){
        $db = Zend_Registry::get('dbAdapter');
        $sql = "select id from department where billing=0";
        $items = new SplObjectStorage();
        foreach($db->fetchAll($sql) as $item){
            $costcenter = new Yourdelivery_Model_Department($item['id']);
            $items->attach($costcenter);
        }
        return $items;
    }

    /**
     * get all employees from this department
     * @author mlaug
     * @return SplObjectStorage
     */
    public function getEmployees(){
        if ( is_null($this->_employees) ){
            $empl = $this->getTable()->getEmployees();
            $empls = new SplObjectStorage();
            foreach($empl as $e){
                try{
                    $empls->attach(new Yourdelivery_Model_Customer_Company($e->customerId, $this->getCompanyId()));
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e){
                    continue;
                }
            }
            $this->_employees = $empls;
        }
        return $this->_employees;

    }

    /**
     * remove all employees from this department
     * @author mlaug
     */
    public function resetEmployees(){
        $this->getTable()->resetEmployees();
    }

    /**
     * check if a employee is already in this department
     * @author mlaug
     * @param int $id
     * @return boolean
     */
    public function hasEmployee($id){
        foreach($this->getEmployees() as $empl){
            if ( $empl->getId() == $id ){
                return true;
            }
        }
        return false;
    }

    /**
     * add an employee to this department
     * @author mlaug
     * @param int $empl
     * @return boolean
     */
    public function addEmployee($empl){

        if ( !$empl instanceof Yourdelivery_Model_Customer_Company ){
            return false;
        }

        $this->getTable()
             ->addEmployee($empl->getId());

    }

    /**
     * associated a new project number to this department
     * @author mlaug
     * @param string $nr
     * @return int
     */
    public function addProjectNumber( $nr = null, $intern = false, $comment = ""){

        if ( is_null($nr) ){
            return false;
        }

        return $this->getTable()->addProjectNumber($nr, $intern, $comment);

    }

    /**
     * create link betweek
     * @author mlaug
     * @param Yourdelivery_Model_Department $parent
     * @return mixed Yourdelivery_Model_Department_Link | boolean
     */
    public function createLink($parent = null){
        if ( is_null($this->getId()) ){
            return false;
        }

        $link = new Yourdelivery_Model_Department_Link($parent, $this);
        return $link;
    }

    /**
     * we overwrite this one to check if this element has any parent
     * if not this will get billing if he wants or not :) beat it
     * @author mlaug
     * @return boolean
     */
    public function getBilling(){

        if ( $this->_data['billing'] == 1){
            return true;
        }
        else{
            $parent = $this->getParent();
            if ( is_null($parent)  ){
                return true;
            }
        }
        return false;
    }

    /**
     * get all cost centers assigned to this department
     * @author mlaug
     * @return SplObjectStorage
     */
    public function getCostCenter(){
        if ( is_null($this->_costcenters) ){
            $childs = $this->getChilds();
            $costcenters = new SplObjectStorage();
            foreach($childs as $child){
                if ( $child->getBilling() == 1){
                    $costcenters->attach($child);
                }
            }
            $this->_costcenters = $costcenters;
        }
        return $this->_costcenters;
    }

    /**
     * check if department has assinged cost centers
     * @author mlaug
     * @param int $id
     * @return boolean
     */
    public function hasCostCenter($id){
        foreach($this->getCostCenter() as $c){
            if ( $c->getId() == $id){
                return true;
            }
        }
        return false;
    }

    /**
     * get all child elements of departments
     * only one level deeper
     * @author mlaug
     * @return SplObjectStorage
     */
    public function getChilds(){
        if ( is_null($this->_childs) ){
            $childRows = $this->getTable()->getChilds();
            if ( count($childRows) > 0 ){
                $childs = new SplObjectStorage();
                foreach($childRows as $row){
                    $childs->attach(new Yourdelivery_Model_Department($row['childId']));
                }
                $this->_childs = $childs;
            }
            else{
                return new SplObjectStorage();
            }
        }
        return $this->_childs;
    }

    /**
     * check if department has children
     * @author mlaug
     * @return boolean
     */
    public function hasChilds(){
        $childs = $this->getChilds();
        if ( $childs->count() > 0 ){
            return true;
        }
        return false;
    }

    /**
     * get all project numbers assciated with this department
     * @author mlaug
     * @return SplObjectStorage
     */
    public function getProjectNumbers(){

        $numbers = new SplObjectStorage();
        foreach($this->getTable()->getProjectNumbers() as $number){
            $numbers->attach(new Yourdelivery_Model_Projectnumbers($number['projectnumbersId']));
        }
        return $numbers;

    }

    /**
     * get table
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Department
     */
    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Department();
        }
        return $this->_table;
    }
    
    public function  __toString() {
        return $this->getId();
    }

}
?>
