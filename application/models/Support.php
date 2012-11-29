<?php
/**
 * Description of Support
 * @package backend
 * @subpackage support
 * @author mlaug
 */
class Yourdelivery_Model_Support extends Default_Model_Base{

    /**
     *
     * @author mlaug
     * @return array
     */
    public static function all(){

        $db = Zend_Registry::get('dbAdapter');
        return $db->fetchAll(
            "SELECT *
            FROM `support_number`"
        );

    }

    /**
     *
     * @author mlaug
     * @return array
     */
    public static function allActive(){

        $db = Zend_Registry::get('dbAdapter');
        return $db->fetchAll(
            "SELECT *
            FROM `support_number` 
            WHERE `active` = 1"
        );

    }

    /**
     * change status of supporter and send out sms
     * to inform him/her that the handy status has been changed
     * @author mlaug
     * @since 24.08.2010
     * @param boolean $bool
     */
    public function setActive ($bool) {

        $config = Zend_Registry::get('configuration');
        $this->_data['active'] = (boolean) $bool;
        $sms = new Yourdelivery_Sender_Sms();
        $sms->send($this->getNumber(), $bool ? __('Dein Handy befindet sich nun in den Fesseln der %s-IT', $config->domain->base) : __('Dein Handy wurde deaktiviert, Danke schön für den Support :)'));

    }

    /**
     *
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Support
     */
    public function getTable() {

        if ($this->_table === null){
            $this->_table = new Yourdelivery_Model_DbTable_Support();
        }
        return $this->_table;

    }

}
