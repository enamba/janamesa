<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MailingTransaction
 *
 * @author daniel
 */
class Yourdelivery_Model_DbTable_Mailing_Optivo   extends Default_Model_DbTable_Base {
    
    /**
     * Table name
     */
    protected $_name = 'mailing_optivo';
    
    /**
     * Primary key name
     */
    protected $_primary = 'id';
    
    
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Mailing_Optivo_City'
    );
}

