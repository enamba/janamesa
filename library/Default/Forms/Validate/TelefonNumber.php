<?php

/**
 * Description of Unique Telefon Validation
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
class Default_Forms_Validate_TelefonNumber extends Zend_Validate_Abstract {

    /**
     * list of tables to check for customer
     * @var array 
     */
    protected $checkTables = array(
        "rabatt_check",
        "orders_customer",
        "customers"
    );
    
    /**
     * search for telephone (input and norm)
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $number
     * @return boolean 
     */
    public function isValid($number) {        
        //search for that number in the database
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $numberNorm = Default_Helpers_Normalize::telephone($number);
        foreach ($this->checkTables as $table) {
            $select = $db->select()->from($table, array('tel'));
            $select->where('tel = ?', $number);
            $select->orWhere('tel = ?', $numberNorm);
            $result = $db->fetchAll($select);
            if (count($result) > 0) {
                return false;
            }
        }

        $logger = Zend_Registry::get('logger');
        $logger->debug('VALID: successfully validated TEL ' . $number);
        return true;
    }

}
