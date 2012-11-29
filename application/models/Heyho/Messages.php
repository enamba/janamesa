<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 16.11.2010
 */
class Yourdelivery_Model_Heyho_Messages extends Default_Model_Base {

    /**
     * Get all available messages
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     * @return array
     */
    public function getMessages($current) {

        $rows = $this->getTable()
                ->getMessages();
        $messages = array();
        foreach ($rows as $row) {
            try {
                if ($current == $row->id) {
                    continue;
                }
                $messages[] = new Yourdelivery_Model_Heyho_Messages($row->id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
        return $messages;
    }

    /**
     * Get the prio of the message
     * 
     * @author @author Matthias Laug <laug@lieferando.de>
     * @since 17.11.2011
     * @return integer
     */
    public function getPrio() {

        return 1;
    }

    /**
     * @author @author Matthias Laug <laug@lieferando.de>
     * @since 17.11.2011
     * @return array
     */
    public function getCallbacks() {

        return array_unique(explode(',', $this->_data['callbackAvailable']));
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     * @param string $callback
     * @return void
     */
    public function addCallbackAvailable($callback) {

        $callbacks = array();
        if ($this->getCallbackAvailable()) {
            $callbacks = explode(",", $this->getCallbackAvailable());
        }
        $callbacks[] = $callback;
        $this->setCallbackAvailable(implode(",", array_unique($callbacks)));
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     * @param string $callback
     * @return void
     */
    public function addCallbackTriggered($callback) {

        $callbacks = array();
        if ($this->getCallbackTriggered()) {
            $callbacks = explode(",", $this->getCallbackTriggered());
        }
        $callbacks[] = $callback;
        $this->setCallbackTriggered(implode(",", array_unique($callbacks)));
    }

    /**
     * Check if this ticket is already locked
     * then lock it
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.11.2011
     * @param integer $adminId
     * @return boolean
     */
    public function isLocked($adminId) {

        if (!$this->getAdminId()) {
            $this->setAdminId($adminId);
            $this->save();
            return false;
        }

        if ($this->getAdminId() == $adminId) {
            return false;
        }

        return true;
    }

    /**
     * Release this message from this user
     * @since 23.11.2011
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function release() {
        
        $this->setAdminId(null);
        $this->addCallbackTriggered("release");
        $this->save();
    }

    /**
     * Close this message from this user
     * @since 21.02.2012
     * @author Vincent Priem <priem@lieferando.de>
     */
    public function close() {
        
        $this->setAdminId(null);
        $this->setState(1);
        $this->addCallbackTriggered("close");
        $this->save();
    }
    
    /**
     * Get table
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 16.11.2010
     * @return Yourdelivery_Model_DbTable_Heyho_Messages
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Heyho_Messages();
        }

        return $this->_table;
    }

}
