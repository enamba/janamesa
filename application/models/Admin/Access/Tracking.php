<?php

/**
 * Tracking for Backend User
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 30.09.2011
 */
class Yourdelivery_Model_Admin_Access_Tracking extends Default_Model_Base{

    /**
     * Constants used to track to user  actions 
     */
    const MODEL_TYPE_ORDER = "order";
    const MODEL_TYPE_SERVICE = "service";
    const MODEL_TYPE_RABATT = "rabatt";

    const ORDER_STORNO = "order_storno";
    const ORDER_CONFIRM = "order_confirm";
    const ORDER_CONFIRM_ORALY = "order_confirm_oraly";
    const ORDER_CONFIRM_FAX_WENT_THROUGH = "order_confirm_fax_went_through";
    const ORDER_FAX_RESEND= "order_fax_resend";
    const ORDER_FAKE = "order_fake";
    const ORDER_PULL = "order_pull";
    const ORDER_PUSH = "order_push";
    const ORDER_COMMENT = "order_comment";

    const TICKET_TIMEOUT = "ticket_timeout";
    const TICKET_LOCATION = "ticket_location";
    const TICKET_CHANGECARD = "ticket_changecard";
    const TICKET_BILL = "ticket_bill";
    const TICKET_PAYMENT = "ticket_payment";
    
    const SERVICE_COMMENT = "service_comment";
    const SERVICE_CREATE = "service_create";
    const SERVICE_EDIT = "service_edit";
    const SERVICE_STATUS_CHANGE = "service_status_change";
    
    const RABATT_CREATE = "rabatt_create";
    const RABATT_EDIT = "rabatt_edit";
    const RABATT_DISABLE = "rabatt_disable";
    
    /**
     * Map actions
     * @var array
     */
    protected static $_actions = array(
        self::ORDER_STORNO => "Bestellung storniert",
        self::ORDER_CONFIRM => "Bestellung bestätigt",
        self::ORDER_CONFIRM_ORALY => "Bestellung mündlich durchgegeben",
        self::ORDER_CONFIRM_FAX_WENT_THROUGH => "Fax ging durch",
        self::ORDER_FAX_RESEND => "Fax erneut verschicken",
        self::ORDER_FAKE => "Bestellung als Fake markiert",
        self::ORDER_PULL => "Ticket genommen",
        self::ORDER_PUSH => "Ticket zurückgeben",
        self::ORDER_COMMENT => "Kommentar zur Bestellung",
        self::TICKET_TIMEOUT => "Ticket Timeout",
        self::TICKET_LOCATION => "DL anrufen wegen Liefergebieten",
        self::TICKET_BILL => "DL anrufen wegen Abbrechnung",
        self::TICKET_PAYMENT => "DL anrufen wegen bargeldloser Zahlung",
        self::TICKET_CHANGECARD => "DL anrufen wegen Kartenänderung",
        self::SERVICE_COMMENT => "Kommentar zum Dienstleister",
        self::RABATT_CREATE => "Rabattaktion erstellt",
        self::RABATT_EDIT => "Rabattaktion bearbeitet",
        self::RABATT_DISABLE => "Rabatcode deaktiviert",
        self::SERVICE_CREATE => "Dienstleister erstellt",
        self::SERVICE_EDIT => "Dienstleister bearbeitet",
        self::SERVICE_STATUS_CHANGE => "Dienstleister deaktiviert/aktiviert"
    );
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 30.09.2011
     * @return array
     */
    public static function getActions() {
        
        return self::$_actions;
    }
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 30.09.2011
     * @param string $key
     * @return string 
     */
    public static function getAction($key) {
        
        if (key_exists($key, self::$_actions)) {
            return self::$_actions[$key];
        }
        
        return false;
    }
    
    /**
     * Get related table
     * @return Yourdelivery_Model_DbTable_Admin_Access_Tracking
     */
    public function getTable() {
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Admin_Access_Tracking();
        }
        
        return $this->_table;
    }
    
   /**
     *  
     * @author vpriem
     * @since 07.10.2011
     * @return array
     */
    public function getStats($from= false, $until =false, $userId = false, $groups = false) {
        
        return $this->getTable()
                    ->getStats($from, $until, $userId,$groups);
    }
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.10.2011
     * @param int $userId
     * @return array
     */
    public function getUserStats($userId,$from, $until) {
        
        $stats = $this->getTable()->getByUser($userId, $from, $until);
        
        $actions = self::$_actions;
        $action_model_ids = array();
        
        
        foreach($actions as $key => $action) {
            
            $action_model_ids[$key] = array();
            
            
            foreach($stats as $stat) {
                if($key == $stat['action'] && !in_array($stat['modelId'], $action_model_ids[$key])) {
                 $action_model_ids[$key]['modelType'] =  $stat['modelType'] ;
                 $action_model_ids[$key][] = $stat['modelId'];
                }
            }
            
        }
        
        return $action_model_ids;       
        
    }
    
}