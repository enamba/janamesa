<?php

/**
 * Paypal Transaction Db Table
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 28.09.2011
 */
class Yourdelivery_Model_DbTable_Paypal_BlackWhiteList extends Zend_Db_Table_Abstract {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'paypal_black_white_list';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    
    /**
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 28.09.2011
     */
    public function getByPayerId($payerId) {

        return $this->fetchAll(
                        $this->select()
                                ->where("`payerId` = ?", $payerId)
        );
    }
    
    /**
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 28.09.2011
     */
    public function isWhite($payerId) {

        $rows = $this->fetchAll(
                $this->select()
                        ->where("`payerId` = ?", $payerId)
                        ->where("white = 1")
        );

        return count($rows) > 0;
    }

    /**
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 28.09.2011
     */
    public function isBlack($payerId) {
        $rows = $this->fetchAll(
                $this->select()
                        ->where("`payerId` = ?", $payerId)
                        ->where("white = 0")
        );

        return count($rows) > 0;
    }
    
    /**
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 28.09.2011
     */
    public function addToBlacklist($payerId) {


        $rows = $this->getByPayerId($payerId);

        if ($payerId == $rows[0]->payerId && "0" === $rows[0]->white) {
            return true;
        }

        try {

            if (1 == $rows[0]->white) {
                $rows[0]->white = 0;
                return $rows[0]->save();
            }

            return $this->insert(array('payerId' => $payerId,                                        
                                                     'white' => 0,
                                                     'comment' => 'added to Blacklist'
                    ));
        } catch (Exception $e) {
                                    
            $log = Zend_Registry::get('logger');
            $log->error('Add To Blacklist fehlgeschlagen, PayerId: ' . $payerId);

            return false;
        }
    }
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.10.2011
     * @param string $payerId
     * @return boolean
     */
    public function addToWhitelist($payerId) {


        $rows = $this->getByPayerId($payerId);

        if ($payerId == $rows[0]->payerId && "1" === $rows[0]->white) {
            return true;
        }

        try {

            if (0 == $rows[0]->white) {
                $rows[0]->white = 1;
                return $rows[0]->save();
            }

            return $this->insert(array('payerId' => $payerId,                                        
                                                     'white' => 1,
                                                     'comment' => 'added to Whitelist'
                    ));
        } catch (Exception $e) {
                                    
            $log = Zend_Registry::get('logger');
            $log->error('Add To Whitelist fehlgeschlagen, PayerId: ' . $payerId);

            return false;
        }
    }
    
    /**
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 28.09.2011
     */
    public function remove($payerId) {
        
        
        $log = Zend_Registry::get('logger');
        $log->debug('Remove PayerId '.$payerId. " from Blacklist...");
        $where = $this->getAdapter()->quoteInto("payerId = ?",$payerId);
        
        $this->delete($where);
        
    }
}
