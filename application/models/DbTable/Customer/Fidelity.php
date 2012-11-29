<?php

/**
 * Description of Fidelity
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class Yourdelivery_Model_DbTable_Customer_Fidelity extends Default_Model_DbTable_Base{

    protected $_name = "customer_fidelity_points";

    /**
     * the reference array to map on dependent tables
     * @var array
     */
    protected $_referenceMap    = array(
        'Customer' => array(
            'columns'           => 'customerId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Customer',
            'refColumns'        => 'id'
        )
    );
    
    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';


    

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 03.12.2010
     * @param string $email
     * @return Zend_DbTable_Rowset
     */
    public static function findByEmail($email = null){
        if( is_null($email) ){
            return null;
        }
        
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("f" => "customer_fidelity_points") )
                    ->where( "f.email = '" . $email . "'");

        return $db->fetchRow($query);
    }
    
}
