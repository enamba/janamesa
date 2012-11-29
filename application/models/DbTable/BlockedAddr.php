<?php

/**
 * Database interface for Yourdelivery_Models_DbTable_Billing.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
 */
class Yourdelivery_Model_DbTable_BlockedAddr extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'blocked_ip_addr';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Check if an ip address is blocked
     * 
     * @param string $ip IP-Address to ckeck, wheather is blocked or not
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 13.09.2010
     * 
     * @return boolean 
     */
    public function isBlocked($ip = null) {
        
        // get ip
        if ($ip === null) {
            $ip = Default_Helpers_Web::getClientIp();
        }

        $select = $this->select()->where("ipAddr LIKE ?", $ip)
                ->where("DATE(created) = DATE(NOW())")
                ->orWhere("DATE(created) = SUBDATE(NOW(),1) AND TIME(NOW()) < '03:00:00'");

        // check
        $rows = $this->fetchAll($select);

        if (count($rows) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Block an ip address
     * 
     * @param string $ip IP-Address to set blocked
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.09.2010
     * 
     * @return boolean
     */
    public function block($ip = null) {

        // get ip
        if ($ip === null) {
            $ip = Default_Helpers_Web::getClientIp();
        }

        // check
        $this->createRow(array(
            'ipAddr' => $ip
        ))->save();
        return true;
    }

}
