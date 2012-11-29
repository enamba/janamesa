<?php

/**
 * Description of FidelityTransaction
 *
 * @package customer
 * @subpackage fidelity
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class Yourdelivery_Model_Customer_FidelityTransaction extends Default_Model_Base {

    /**
     * get associated table
     * @return Yourdelivery_Model_DbTAble_Customer_FidelityTransaction
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Customer_FidelityTransaction();
        }
        return $this->_table;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     * 
     * @return integer
     */
    public function getPointsUntil() {
        $row = $this->getTable()->getPointsUntil($this->getEmail(), $this->getCreated());
        return (integer) $row['sum'];
    }

    public function getOrder() {
        $order = null;
        switch ($this->getAction()) {
            case 'order':
            case 'rate_low':
            case 'rate_high':
                try {
                    $order = new Yourdelivery_Model_Order((integer) $this->getTransactionData());
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    
                }
                break;

            default:
                break;
        }

        return $order;
    }

    /**
     * get Description of an action
     * 
     * @return STRING
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.01.2012
     */
    public function getDescription() {
        $message = null;
        $action = $this->getAction();
        $state = $this->getStatus() == 0 ? true : false;

        switch ($action) {
            case 'order':
                if ($state) {
                    $message = __('Deine Bestellung vom %s.', date('d.m.Y', $this->getCreated()));
                } else {
                    $message = __('Deine Bestellung vom %s wurde storniert.', date('d.m.Y', $this->getCreated()));
                }
                break;
            case 'accountimage':
                if ($state) {
                    $message = __('Du hast Dein Profilbild hochgeladen.');
                } else {
                    if(!is_null($this->getUpdated()) && $this->getUpdated() > 0){
                        $message = __('Du hast am %s Dein Profilbild entfernt.', date('d.m.Y', $this->getUpdated()));
                    }else{
                        $message = __('Du hast Dein Profilbild entfernt.');
                    }
                }
                break;
            case 'manual':
                $message = __('Die Punkte wurden manuell am %s ergänzt.', date('d.m.Y', $this->getCreated()));
                break;
            case 'rate_high':
            case 'rate_low':
                if ($state) {
                    if (!is_null($this->getOrder())) {
                        $message = __('Du hast Deine Bestellung vom %s am %s bewertet.', date('d.m.Y', $this->getOrder()->getTime()),date('d.m.Y', $this->getCreated()));
                    } else {
                        $message = __('Für Deine Bewertung am %s', date('d.m.Y', $this->getCreated()));
                    }
                } else {
                    $updated = $this->getUpdated();
                    if(is_null($updated) || $updated <= 0){
                        $message = __('Deine Bewertung vom %s wurde offline gestellt.', date('d.m.Y', $this->getCreated()));
                    }else{
                        $message = __('Deine Bewertung vom %s wurde am %s offline gestellt.', date('d.m.Y', $this->getCreated()), date('d.m.Y', $updated));
                    }
                }
                break;
            case 'register':
            case 'registeraftersale':
                if ($state) {
                    $message = __('Du hast Dich am %s registriert.', date('d.m.Y', $this->getCreated()));
                } else {
                    $message = __('Die Punkte für Deine Registrierung wurden am %s entfernt', date('d.m.Y', $this->getCreated()));
                }
                break;
            case 'usage':
                if ($state) {
                    $message = __('Du hast Deine Punkte am %s eingelöst.', date('d.m.Y', $this->getCreated()));
                } else {
                    if(!is_null($this->getUpdated()) && $this->getUpdated() > 0){
                        $message = __('Die Einlösung deiner Punkte wurde am %s storniert.', date('d.m.Y', $this->getUpdated()));
                    }else{
                        $message = __('Die Einlösung deiner Punkte wurde storniert.');
                    }
                }
                break;
            default:
                break;
        }

        $message .= ' ' . __('Punktestand vorher: %d', $this->getPointsUntil());
        return $message;
    }
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param array $transaction
     * @return boolean
     */
    public static function isRatingDeleted(array $transaction) {
                        
        if(!in_array($transaction['action'], array('rate_high', 'rate_low'))) {           
            throw new Yourdelivery_Exception('Only for Rating Data!!!');
        }
                
        if($transaction['status'] == 0) {
            return false;
        }else{
            $rating = Yourdelivery_Model_DbTable_Restaurant_Ratings::findByOrderId($transaction['transactionData']);     
            if($rating && $rating['status'] == -1)  {
                return true;
            }else{
                return false;
            }
        }
                        
    }
    
}

