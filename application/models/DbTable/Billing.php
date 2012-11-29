<?php

/**
 * Database interface for Yourdelivery_Models_DbTable_Billing.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug <haferkorn@lieferano.de>
 */
class Yourdelivery_Model_DbTable_Billing extends Default_Model_DbTable_Base {

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'billing';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Billing_Customized_Single',
        'Yourdelivery_Model_DbTable_Billing_Admonation',
        'Yourdelivery_Model_DbTable_Billing_Sent',
        'Yourdelivery_Model_DbTable_Billing_Balance'
    );

    /**
     * reset id, remove voucher, but do not delete row
     * just return it to be modified!
     * 
     * @param int    $id    billingId
     * @param string $mode  billingMode
     * @param int    $refId reference to companyId / restaurantId
     * 
     * @return Zend_Db_Table_Row_Abstract
     * 
     * @author mlaug
     * 
     * @todo: rebuild with Zend_Select
     * @todo: add billing asset 
     */
    public static function resetBill($id, $mode, $refId) {

        $db = Zend_Registry::get('dbAdapter');

        $assoc_orders = array();
        $assoc_assets = array();

        switch ($mode) {
            default:
                return false;

            case 'courier':
                //get all associated orders
                $sql = sprintf("select id from orders where billCourier=%d", $id);
                $assoc_orders = array_keys($db->fetchAssoc($sql));

                $sql = sprintf("update orders set billCourier=NULL where billCourier=%d", $id);
                $db->query($sql);

                //reset assets
                $sql = sprintf("update billing_assets set billCourier=NULL where billCourier=%d", $id);
                $db->query($sql);

                break;

            case 'rest':
                //get all associated orders
                $sql = sprintf("select id from orders where billRest=%d", $id);
                $assoc_orders = array_keys($db->fetchAssoc($sql));

                //get all associated orders
                $sql = sprintf("select id from billing_assets where billRest=%d", $id);
                $assoc_assets = array_keys($db->fetchAssoc($sql));

                //get old billing assets
                $sql = sprintf("update orders set billRest=NULL where billRest=%d", $id);
                $db->query($sql);

                //reset assets
                $sql = sprintf("update billing_assets set billRest=NULL where billRest=%d", $id);
                $db->query($sql);

                //reset balance
                $db->query(sprintf('delete from billing_balance where billingId=%d or comment="reset billing %d"', $id, $id));
                break;

            case 'company':

                //get all associated orders
                $sql = sprintf("select id from orders where billCompany=%d", $id);
                $assoc_orders = array_keys($db->fetchAssoc($sql));

                //get all associated orders
                $sql = sprintf("select id from billing_assets where billCompany=%d", $id);
                $assoc_assets = array_keys($db->fetchAssoc($sql));

                $sql = sprintf("update orders set billCompany=NULL where billCompany=%d", $id);
                $db->query($sql);

                //reset assets
                $sql = sprintf("update billing_assets set billCompany=NULL where billCompany=%d", $id);
                $db->query($sql);

                $sql = sprintf("select sum(amount) from billing_balance where billingId=%d", $id);
                $amount = (-1) * (integer) $db->fetchOne($sql);
                if ($amount != 0) {
                    $sql = sprintf("insert into billing_balance (restaurantId,amount,`comment`) values(%d,%d,'reset billing %d'", $refId, $amount, $id);
                    $db->query($sql);
                }
                break;

            case 'inventory':
            case 'upselling_goods':
                // nothing to do
                break;
        }

        //do not remove base bill row, just update it
        $table = new Yourdelivery_Model_DbTable_Billing();
        $current = $table->find($id)->current();
        if (is_object($current)) {
            return array(true, $current, $assoc_orders, $assoc_assets);
        }
        return array(false, null, null, null);
    }

    /**
     * get a rows matching Id by given value
     * 
     * @param int $id billingRowId
     * 
     * @return Zend_Db_Table_Row_Abstract
     */
    public static function findById($id) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("b" => "billing"))
                ->where("b.id = " . $id);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching RefId by given value
     * 
     * @param int    $refId reference to company or restaurant
     * @param string $mode  billingMode
     * 
     * @return Zend_Db_Table_Row_Abstract
     */
    public static function findByRefIdAndMode($refId, $mode) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("b" => "billing"))
                ->where("b.refId = ?", $refId)
                ->where("b.mode = ?", $mode);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching RefId by given value
     * 
     * @param string $number billingNumber
     * 
     * @return Zend_Db_Table_Row_Abstract
     */
    public static function findByNumber($number) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("b" => "billing"))
                ->where("b.number = ?", $number);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching RefId by given value
     * 
     * @param int    $refId     reference to company or restaurant
     * @param string $startTime timestamp
     * @param string $endTime   timestamp
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public static function findByRefIdAndTime($refId, $startTime, $endTime) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("b" => "billing"))
                ->where("b.refId = " . $refId . " and  b.timeUntil >= " . $startTime . " and b.timeUntil <= " . $endTime);

        return $db->fetchAll($query);
    }

    /**
     * get all rows matching Mode and Time by given value
     * 
     * @param string $mode      billing mode (comp / rest)
     * @param string $startTime timestamp
     * @param string $endTime   timestamp
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public static function findByModeAndTime($mode, $startTime, $endTime) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("b" => "billing"))
                ->where("b.mode = '" . $mode . "' and  b.timeUntil >= " . $startTime . " and b.timeUntil <= " . $endTime);

        return $db->fetchAll($query);
    }

    /**
     * get a rows matching Status by given value
     * 
     * @param int $status state of bill
     * 
     * @return Zend_Db_Table_Row_Abstract
     */
    public static function findByStatus($status) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("b" => "billing"))
                ->where("b.status = " . $status);

        return $db->fetchRow($query);
    }

    /**
     * get last time period
     * 
     * @param int    $ref  reference to company or restaurant
     * @param string $mode billing mode (comp / rest)
     * 
     * @author mlaug
     * 
     * @return integer
     */
    public function getLastUntil($ref, $mode) {
        if (is_null($mode)) {
            return false;
        }
        if (!is_object($ref)) {
            return false;
        }
        $sql = sprintf("select max(timeUntil) from billing where mode='%s' and refId=%d", $mode, $ref->getId());
        return $this->getAdapter()
                        ->query($sql)
                        ->fetch();
    }

    /**
     * get next billing number
     * 
     * @param object $ref  Yourdelivery_Model_Company | Yourdelivery_Model_ServicetypeAbstract
     * @param srting $mode billing mode (comp / rest)
     * 
     * @author mlaug
     * 
     * @return string
     */
    public function getNextBillingNumber($ref = null, $mode = null) {

        $sql = "select number from billing";
        $all = $this->getAdapter()
                ->query($sql)
                ->fetchAll();

        $next = 0;
        foreach ($all as $nr) {
            $parts = explode('-', $nr['number']);
            $dyn = intval($parts[3]);
            if ($dyn > $next) {
                $next = $dyn;
            }
        }

        //ingore those
        $next++;
        while (in_array($next, array(1001, 1002, 1003, 1004, 1005, 1006))) {
            $next++;
        }

        if ($next > 0 && $next < 10) {
            $next = "00000" . $next;
        } elseif ($next >= 10 && $next < 100) {
            $next = "0000" . $next;
        } elseif ($next >= 100 && $next < 1000) {
            $next = "000" . $next;
        } elseif ($next >= 1000 && $next < 10000) {
            $next = "00" . $next;
        } elseif ($next >= 10000 && $next < 100000) {
            $next = "0" . $next;
        }

        return $next;
    }

    /**
     * ?????
     * 
     * @param integer $oldId previous bill
     * 
     * @author mlaug
     * @since 06.08.2010
     * 
     * @return Zend_Db_Row_Abstract | null
     * 
     * @todo write a comment
     */
    public function getCustomized($oldId) {
        $row = $this->find($oldId);
        if ($row->count() == 1) {
            return $row->current()
                            ->findDependentRowset('Yourdelivery_Model_DbTable_Billing_Customized_Single')
                            ->current();
        }
        return null;
    }

    /**
     * Get count of billings of certain type and state
     * 
     * @param string $mode  billing mode (comp / rest)
     * @param string $state actual state of bill
     * 
     * @author alex
     * @since 19.08.2010
     * 
     * @return integer
     */
    public static function getBillingsCount($mode, $state = null) {
        $db = Zend_Registry::get('dbAdapter');

        /*
         *  $state:
         *  0 - nicht verschickt
         *  1 - nicht bezahlt
         *  2 - bezahlt
         */
        $stateQuery = "";
        if (!is_null($state)) {
            $stateQuery = " and status = " . $state;
        }

        $billCond = "";
        if (strcmp($mode, 'rest') == 0) {
            $billCond = " and number like 'R-%'";
        }

        $sql = sprintf("select count(id) from billing where mode='%s' and refId > 0 %s %s", $mode, $billCond, $stateQuery);
        $result = $db->query($sql)->fetchColumn();

        return $result;
    }

    /**
     * Get date of the first billings of certain type
     * 
     * @param string $mode billingMode (comp / rest)
     * 
     * @author alex
     * @since 05.04.2011
     * 
     * @return string
     */
    public static function getDateOfFirstBilling($mode) {
        $db = Zend_Registry::get('dbAdapter');

        $sql = sprintf("select min(billing.from) as mindate from billing where billing.from>='2009-04-01' and mode='%s'", $mode);
        $result = $db->fetchRow($sql);

        return $result['mindate'];
    }

    /**
     * Get date of the last billings of certain type
     * 
     * @param string $mode billingMode (comp / rest)
     * 
     * @author alex
     * @since 05.04.2011
     * 
     * @return string
     */
    public static function getDateOfLastBilling($mode) {
        $db = Zend_Registry::get('dbAdapter');

        $sql = sprintf("select max(billing.from) as maxdate from billing where billing.from>='2009-04-01' and billing.from<NOW() and mode='%s'", $mode);
        $result = $db->fetchRow($sql);

        return $result['maxdate'];
    }

    /**
     * get all cost center ids, associated with this billing
     * 
     * @since 04.01.2010
     * @author alex
     * 
     * @return Zend_Db_Rowset_Abstract
     */
    public function getCostcenters() {
        return $this->getAdapter()->fetchAll('select costcenterId, amount from billing_sub where billingId=' . $this->getId());
    }
    
    /**
     * get history of status
     * @author Alex Vait <vait@lieferando.de>
     * @since 19.06.2012
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getStateHistory() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array('bs' => 'billing_status'))
                ->joinLeft(array('au' => 'admin_access_users'), 'au.id=bs.adminId', array('au.name'))
                ->where('bs.billingId = ?', $this->getId())
                ->order('bs.created DESC')
                ->limit(2);
        
        return $db->fetchAll($query);
    }    

}
