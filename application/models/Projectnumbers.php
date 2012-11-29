<?php
/**
 * Description of Projectnumbers
 * @package company
 * @subpackage department
 * @author mlaug
 */
class Yourdelivery_Model_Projectnumbers extends Default_Model_Base {

    /**
     * get all projectcodes of company
     * @author mlaug
     * @param Yourdelivery_Model_Company
     * @return array
     */
    public static function allByCompany($company = null){
        if ( is_null($company) ){
            return array();
        }
        $db = Zend_Registry::get('dbAdapter');
        return $db->query('select number from projectnumbers where deleted=0 and companyId=' . $company)->fetchAll();
    }

    /**
     * get a project
     * @author mlaug
     * @param string $number
     * @param Yourdelivery_Model_Company $company
     * @return Yourdelivery_Model_Projectnumbers
     */
    public static function findByNumber($number = null, $company = null){
        if ( is_null($number) || is_null($company) || !is_object($company) ){
            return false;
        }

        $db = Zend_Registry::get('dbAdapter');
        $result = $db->query('select id from projectnumbers where number="' . $number . '" and deleted=0 and companyId=' . $company->getId())->fetch();
        if ( $result === false ){
            return false;
        }

        try{
            $project = new Yourdelivery_Model_Projectnumbers($result['id']);
        }
        catch ( Yourdelivery_Exception_DatabaseInconsistency $e ){
            return false;
        }
        return $project;
    }

    /**
     * mark a project number as deleted
     * @author mlaug
     */
    public function delete(){
        $this->setDeleted(true);
        $this->save();
    }

    /**
     * get associated table
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Projectnumbers
     */
    public function getTable() {

        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Projectnumbers();
        }
        return $this->_table;

    }
    
    public function  __toString() {
        return $this->getId();
    }
}
?>
