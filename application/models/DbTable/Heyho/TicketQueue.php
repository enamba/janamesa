<?php

/**
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 07.12.2011
 */
class Yourdelivery_Model_DbTable_Heyho_TicketQueue extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'heyho_ticket_queue';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Get all available messages from table
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 07.12.2011
     * @return array
     */
    public function getTickets() {

        return $this->fetchAll()->toArray();
    }

    /**
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 07.12.2011
     * @return array
     */
    public function updateQueue() {
        $sql = "TRUNCATE TABLE `heyho_ticket_queue`;";

        $config = Zend_Registry::get('configuration');
        
        switch ($config->heyho->queue) {

            default:
            case 'prio':

                $sql .= "INSERT INTO `heyho_ticket_queue`
                        ( `orderId`,
                        `supporter`,
                        `state`,
                        `kind`,
                        `mode`,
                        `payment`,
                        `notifyPayed`,
                        `premium`,
                        `timediff`,
                        `rntAllwaysCall`,
                        `notepad_created`,
                        `prio`)
                        SELECT o.id, COALESCE(o.supporter, 0) as supporter, o.state,o.kind, o.mode, o.payment, r.notifyPayed, if(r.franchiseTypeId=3, 1, 0) as premium, (TIMESTAMPDIFF(MINUTE,`time`,NOW())+1) as timediff, rnt.allwaysCall, rnt.created as notepad_created, 
                        GREATEST(
                                IF (r.franchiseTypeId=3 AND o.mode='rest' AND o.state IN (-4,-3,-1,0,1), 8, 0),                     
                                IF (o.state IN (-3,-1) AND o.kind='comp', 6, 0),
                                IF (o.state IN (-4,-3,-1) AND o.kind='priv' AND o.mode='rest' AND o.payment!='bar', 4, 0),
                                IF (o.state IN (-4,-3,-1) AND o.kind='priv' AND o.mode='rest' AND o.payment='bar', 3, 0),
                                IF (o.state IN (-4,-3,-1,0) AND o.mode!='rest',1,0),
                                IF (o.payment!='bar' AND r.notifyPayed>0 AND o.mode='rest' AND o.state IN (-1,-3,-4,0,1), 3, 0),
                                IF (o.payment!='bar' AND r.notifyPayed>0 AND o.mode!='rest' AND o.state IN (-1,-3,-4,0), 3, 0),
                                IF (rnt.allwaysCall=1 AND (DATE_ADD(rnt.created,INTERVAL 1 DAY) > NOW()) AND o.state IN (-1,-3,-4,0,1) , 3, 0),
                                IF (o.state=-1, 5, 0),
                                IF (o.state=-3, 4, 0),              
                                IF (o.state=-15, 1, 0),
                                IF (o.state=-22, 6, 0),
                                IF (r.notify='phone', 3, 0),
                                IF (TIMESTAMPDIFF(MINUTE,`time`,NOW())>10 AND o.state=0,3,0)
                        ) * (TIMESTAMPDIFF(MINUTE,`time`,NOW())+1) as prio
                        FROM orders o
                        INNER JOIN restaurants r ON r.id=o.restaurantId
                        LEFT JOIN restaurant_notepad_ticket rnt ON rnt.restaurantId=o.restaurantId
                        WHERE o.time > DATE_SUB(CURDATE(), INTERVAL 1 DAY)                                
                        HAVING prio > 0                        
                        ORDER BY prio DESC";
                break;

            case 'all':
                $sql .= "INSERT INTO `heyho_ticket_queue`
                        ( `orderId`,
                        `supporter`,
                        `state`,
                        `kind`,
                        `mode`,
                        `payment`,
                        `notifyPayed`,
                        `premium`,
                        `timediff`,
                        `rntAllwaysCall`,
                        `notepad_created`,
                        `prio`)
                        SELECT o.id, COALESCE(o.supporter, 0) as supporter, o.state,o.kind, o.mode, o.payment, r.notifyPayed, if(r.franchiseTypeId=3, 1, 0) as premium, (TIMESTAMPDIFF(MINUTE,`time`,NOW())+1) as timediff, rnt.allwaysCall, rnt.created as notepad_created, 
                        1 * (TIMESTAMPDIFF(MINUTE,`time`,NOW())+1) as prio
                        FROM orders o
                        INNER JOIN restaurants r ON r.id=o.restaurantId
                        LEFT JOIN restaurant_notepad_ticket rnt ON rnt.restaurantId=o.restaurantId
                        WHERE DATE_ADD(o.time,INTERVAL 1 DAY) > NOW() AND o.state < 2                                                   
                        HAVING prio > 0                        
                        ORDER BY prio DESC";
                break;
        }

        $this->getAdapter()->query($sql);
    }

}
