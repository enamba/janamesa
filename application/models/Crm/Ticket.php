<?php
/**
 * Crm ticket model
 * @package crm
 * @author alex
 * @since 12.07.2011
 */
class Yourdelivery_Model_Crm_Ticket extends Default_Model_Base{

    static $reasons = array(
            0 => array('Faxnummer fehlt', 'Back Office'),
            1 => array('Umbau/Umzug', 'Back Office'),
            2 => array('momentan kein Fahrer', 'Back Office'),
            3 => array('akzeptiert keine Bargeldlose Bezahlung', 'Back Office'),
            4 => array('defizientes Faxgerät', 'Back Office'),
            5 => array('Pipeline', 'Back Office'),
            6 => array('ready to check', 'Back Office'),
            7 => array('wartet auf Kurierdienst', 'Back Office'),
            8 => array('gekündigt', 'Back Office'),
            9 => array('Urlaub', 'Back Office'),
            10 => array('warten mit Freischaltung', 'Back Office'),
            11 => array('Inhaberwechsel', 'Back Office'),
            12 => array('keinen Lieferservice mehr', 'Back Office'),
            13 => array('Karte fehlt', 'Back Office'),
            14 => array('Liefergebiete fehlen', 'Back Office'),
            15 => array('neu angehen', 'Back Office'),
            16 => array('Betrieb aufgegeben', 'Back Office'),
            17 => array('Karten update', 'Back Office'),
            18 => array('Vertrag fehlt', 'Back Office'),
            19 => array('zur internen Nutzung', 'Back Office'),
            20 => array('nur für Support', 'Back Office'),
            21 => array('Karte hochgeladen', 'Back Office'),
            22 => array('Kartenüberprüfung', 'Back Office'),
            23 => array('weitergeleitet an Vertrieb', 'Back Office'),
            24 => array('hat offene Fragen', 'Back Office'),
            25 => array('sonstiges', 'Back Office'),
                                               
            100 => array('Wünscht Vertriebler vor Ort', 'Vertrieb'),
            101 => array('Karte fehlt', 'Vertrieb'),
            102 => array('sonstiges', 'Vertrieb'),                
                
            200 => array('möchte Webseite', 'Upselling'),
            201 => array('möchte Druckkostenbeteiligung', 'Upselling'),
            202 => array('möchte Flyer', 'Upselling'),
            203 => array('möchte Equipment', 'Upselling'),
            204 => array('hat Fragen zur Abrechnung', 'Upselling'),
            205 => array('hat offene Fragen', 'Upselling'),
            206 => array('sonstiges', 'Upselling'),
                                
            300 => array('wartet auf Geld', 'Buchhaltung'),
            301 => array('keine Abrechnung bekommen', 'Buchhaltung'),
            302 => array('Rechnungserklärung', 'Buchhaltung'),
            303 => array('Rücküberweisung', 'Buchhaltung'),
            304 => array('möchte Gutschrift', 'Buchhaltung'),
            305 => array('DL wieder Online schalten', 'Buchhaltung'),
            306 => array('hat offene Fragen', 'Buchhaltung'),
            307 => array('sonstiges', 'Buchhaltung'),                
                
            400 => array('Logo neu', 'Graphik'),
            401 => array('Logo update', 'Graphik'),
            402 => array('Kategoriebilder ändern', 'Graphik'),
            403 => array('Speisebilder neu', 'Graphik'),
            404 => array('Webseite erstellen', 'Graphik'),                
                
            500 => array('Auslieferungsproblem', 'Support'),
            501 => array('Bewertungen', 'Support'),
            502 => array('Beschwerde', 'Support'),
            503 => array('hat offene Fragen', 'Support'),
            504 => array('sonstiges', 'Support')
        );
    
    
    /**
     * get reasons of crm ticket
     * @author alex
     * @since 12.07.2011
     * @return array
     */
    static function getReasons($department = null) {
        $result = array();
        
        foreach (self::$reasons as $key => $r) {
            if (!is_null($department) && strcmp($r[1], $department)) {
                continue;
            }
            
            $result[$key] = array('reason' => $r[0], 'department' => $r[1]);
        }
        return $result;
    }
    
    /**
     * Get table
     * @author alex
     * @since 12.07.2011
     * @return Yourdelivery_Model_DbTable_Crm_Ticket
     */
    public function getTable(){
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Crm_Ticket();
        }
        return $this->_table;
    }

    /**
     * Get grid with all crm calls
     * @author alex
     * @since 27.06.2011
     * @return  Bvb_Grid_data
     */
    public static function getGrid($type, $refId, $assignedToId = null){
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->export = array();
        $grid->setPagination(20);
        
        $wherestr = '1';
        if (!is_null($refId)) {
            switch ($type) {
                case 'service':
                    $wherestr = "c.refType='service' AND refId=" . $refId;
                    break;
                case 'company':
                    $wherestr = "c.refType='company' AND refId=" . $refId;
                    break;
                case 'customer':
                    $wherestr = "c.refType='customer' AND refId=" . $refId;
                    break;
                default:
                    $wherestr = '1';
                    break;
            }
        }
            
        if (!is_null($assignedToId)) {
            $wherestr .= " AND assignedToId=" . $assignedToId;
        }       
        
        //select crm tickets
        $select = $db->select()->from(array('c'=>'crm_ticket'),
                                            array(
                                                'ID',
                                                'refType',
                                                'refId',
                                                'topic',
                                                'reasonId',
                                                'message',
                                                'tel',
                                                'email',
                                                'ticket',
                                                'ticketNr',
                                                'closed',
                                                'closed2' => 'closed',
                                                'assignedToId',
                                                'scheduled',
                                                'createdBy'
                                            ))
                    ->joinLeft(array('admin1'=>'admin_access_users'),'admin1.id=c.createdBy',array('createdAdmin'=>'admin1.name'))
                    ->joinLeft(array('admin2'=>'admin_access_users'),'admin2.id=c.assignedToId',array('assignedName'=>'admin2.name'))
                    ->where($wherestr)
                    ->order('c.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('createdAdmin', array('hidden' => 1));
        $grid->updateColumn('assignedName', array('hidden' => 1));
        $grid->updateColumn('refType', array('hidden' => 1));
        $grid->updateColumn('closed2', array('hidden' => 1));
            
        if (!is_null($refId)) {
            $grid->updateColumn('refId', array('hidden' => 1));
        }
        else {
            //$grid->updateColumn('refType',array('title'=>'Typ'));
            $grid->updateColumn('refId',array('title'=>'Referenz', 'callback'=>array('function'=>'crmReferenceLink','params'=>array('{{refType}}', '{{refId}}'))));                       
        }
                
        $grid->updateColumn('topic', array('title'=>'Betreff'));
        $grid->updateColumn('reasonId', array('title'=>'Grund', 'callback' => array('function' => 'crmReasonToReadable', 'params' => array('{{reasonId}}'))));
        $grid->updateColumn('message', array('title'=>'Aufgabe'));
        $grid->updateColumn('tel', array('title'=>'Telefon', 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{tel}}'))));
        $grid->updateColumn('email', array('title'=>'E-Mail', 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{email}}'))));
        $grid->updateColumn('ticket', array('title'=>'Ticket', 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{ticket}}'))));
        $grid->updateColumn('ticketNr', array('title'=>'Ticket Nr'));
        $grid->updateColumn('closed', array('title'=>'Status', 'callback' => array('function' => 'crmOpenClosed', 'params' => array('{{closed}}'))));
        $grid->updateColumn('assignedToId', array('title'=>'Zugewiesen', 'decorator'=>'{{assignedName}}'));        
        $grid->updateColumn('createdBy', array('title'=>'Erstellt', 'decorator'=>'{{createdAdmin}} (#{{createdBy}})'));        
        $grid->updateColumn('scheduled', array('title'=>'Zu erledigen', 'callback' => array('function' => 'intCrmTicketScheduledIcon', 'params' => array('{{closed2}}', '{{scheduled}}'))));

        //add filters
        $filters = new Bvb_Grid_Filters();

        $statis = array(
            '' => 'All',
            '0' => 'Offen',
            '1' => 'Geschlossen'
        );
        
        $types = array(
            '' => 'All',
            'customer' => 'customer',
            'company' => 'company',
            'service' => 'service'
        );

        $yesNoStates = array(
            '0' => 'Nein',
            '1' => 'Ja',
            '' => 'Alle'
        );

        //add filters
        $filters->addFilter('ID')
            ->addFilter('refType', array('values' => $types))            
            ->addFilter('topic')
            ->addFilter('reasonId')
            ->addFilter('message')
            ->addFilter('tel', array('values' => $yesNoStates))
            ->addFilter('email', array('values' => $yesNoStates))
            ->addFilter('ticket', array('values' => $yesNoStates))
            ->addFilter('ticketNr')
            ->addFilter('closed', array('values' => $statis));

        $grid->addFilters($filters);
        
        // add extra rows
        $option = new Bvb_Grid_Extra_Column();
        $option
            ->position('right')
            ->name('Options')
            ->decorator(
                '<div>
                    <a href="/administration_crm/tickethistory/ticketId/{{ID}}">Verlauf</a><br />
                    <a href="/administration_crm/editticket/ticketId/{{ID}}">bearbeiten</a><br />
                    <a href="/administration_crm/deleteticket/ticketId/{{ID}}" onclick="javascript:return confirm(\'Vorsicht!! Soll dieser Ticket wirklich gel&ouml;scht werden?\')">löschen</a>
                </div>'
            );
        $grid->addExtraColumns($option);

        return $grid->deploy();        
    }
    
    /**
     * Get scheduled time as unix timestamp
     * @author alex
     * @since 12.07.2011
     * @return timestamp
     */
    public function getScheduledAsTimestamp(){
        $scheduled = $this->getScheduled();
        
        if (intval($scheduled) == 0) {
            return 0;
        }
        
        return strtotime($scheduled);
    }

    /**
     * Get reason as text
     * @author alex
     * @since 13.07.2011
     * @return string
     */
    public function getReason(){
        $reason = Yourdelivery_Model_Crm_Ticket::$reasons[$this->getReasonId()];
        return $reason[0];
    }

    /**
     * Get reason as text for any reasonId
     * @author alex
     * @since 13.07.2011
     * @return string
     */
    public static function getReasonAsText($reasonId){
        $reason = self::$reasons[$reasonId];
        return $reason[0];
    }
    
    
    /**
     * get tickets for certain admin
     * @author alex
     * @since 13.07.2011
     * @return array
     */
    static function getTickets($adminId, $isClosed) {
        if (is_null($adminId)) {
            return null;            
        }
        
        $tickets = new SplObjectStorage();
        
        foreach (Yourdelivery_Model_DbTable_Crm_Ticket::getTickets($adminId, $isClosed) as $t) {
            try {
                $ticket = new Yourdelivery_Model_Crm_Ticket($t['id']);
                $tickets->attach($ticket);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            }            
        }
        
        return $tickets;
    }
    
    /**
     * get reference object of this ticket
     * @author alex
     * @since 13.07.2011
     */
    public function getReference() {
        switch ($this->getRefType()) {
            default: 
                return null;
            case 'service': 
                try{
                    $reference = new Yourdelivery_Model_Servicetype_Restaurant($this->getRefId());
                    return $reference;
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    return null;
                }
            case 'company': 
                try{
                    $reference = new Yourdelivery_Model_Company($this->getRefId());
                    return $reference;
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    return null;
                }
            case 'customer': 
                try{
                    $reference = new Yourdelivery_Model_Customer($this->getRefId());
                    return $reference;
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    return null;
                }
        }
    }
    
}
