<?php

/**
 * Tracking for Backend User
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 30.09.2011
 */
class Yourdelivery_Model_DbTable_Admin_Access_Tracking extends Default_Model_DbTable_Base {

    /**
     * Table Name
     * @var string
     */
    protected $_name = 'admin_access_tracking';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     *  
     * @author vpriem
     * @since 07.10.2011
     * @modified daniel
     * @return array
     */
    public function getStats($from = false, $until = false, $userId = false, $groups =false) {

        $sql = "SELECT aau.name, aat.adminId, aat.action, COUNT(*) `count`
                    FROM `admin_access_users` aau 
                    INNER JOIN `admin_access_tracking` aat ON aat.adminId = aau.id ";

        if ($from && $until) {

            $date_from = date_create($from);
            $date_until = date_create($until);

            $date_until->modify("+1 day");
            $date_until->modify("-1 sec");

            $sql .= " where aat.time between '" . $date_from->format(DATETIME_DB) . "' and '" . $date_until->format(DATETIME_DB) . "' ";

            if ($userId) {
                $sql .= " and aat.adminId = " . $userId . " ";
            }
            
            if(is_array($groups) && count($groups) > 0 && !empty($groups[0])) {
                $sql .= " and aau.groupId IN(".implode(",", $groups).") ";
            }
        }

        $sql .= " GROUP BY aau.id, aat.action";

        return $this->getAdapter()->fetchAll($sql);
    }
           
    /**
     *
     * @param integer $userId
     * @return Zend_Db_Table_Rowset
     */
    public function getByUser($userId, $from, $until) {

        $select = $this->select()->where('adminId=?', $userId);

        if ($from && $until) {

            $date_from = date_create($from);
            $date_until = date_create($until);

            $date_until->modify("+1 day");
            $date_until->modify("-1 sec");

            $select->where("time between '" . $date_from->format(DATETIME_DB) . "' and '" . $date_until->format(DATETIME_DB) . "'");
        }

        return $this->fetchAll($select);
    }

}
